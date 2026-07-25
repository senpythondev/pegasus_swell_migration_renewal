#!/usr/bin/env node
/**
 * Compare theme #header vs 5210 pattern render — investigation only
 */
import { chromium } from 'playwright';
import { execSync } from 'child_process';
import { mkdirSync, writeFileSync } from 'fs';
import { join } from 'path';

const BASE = process.env.PEGA_URL || 'http://pegasus:4CkYn2kHCoO5vh@220.158.21.114:8080';
const OUT = join(process.cwd(), 'docs', 'header-5210-investigate');
mkdirSync(OUT, { recursive: true });

// Render 5210 HTML via WP
const html5210 = execSync(
	`docker exec pegasus_wp wp --allow-root eval 'echo json_encode(apply_filters("the_content", get_post(5210)->post_content));'`,
	{ encoding: 'utf8' }
).trim();
const rendered5210 = JSON.parse(html5210);

const shell = `<!DOCTYPE html><html><head>
<link rel="stylesheet" href="${BASE.replace(/\/\/[^@]+@/, '//')}/wp-content/themes/swell/build/css/main.css" />
<link rel="stylesheet" href="${BASE.replace(/\/\/[^@]+@/, '//')}/wp-content/themes/swell_child/style.css" />
<style>body{margin:0;font-family:sans-serif}</style>
</head><body>${rendered5210}</body></html>`;
writeFileSync(join(OUT, '5210-preview.html'), shell);

async function analyzeThemeHeader(page, label) {
	return page.evaluate((label) => {
		const h = document.querySelector('#header');
		if (!h) return { label, error: 'no #header' };
		const gnav = h.querySelector('#gnav');
		const contact = h.querySelector('.pega-header-contact');
		const links = gnav ? [...gnav.querySelectorAll('a')].map((a) => a.textContent.trim()) : [];
		const topLinks = links.filter((t) => !t.includes('\t') && t.length < 30);
		const cs = (el, props) => {
			if (!el) return null;
			const s = getComputedStyle(el);
			const o = {};
			for (const p of props) o[p] = s[p];
			const r = el.getBoundingClientRect();
			o._h = Math.round(r.height);
			o._w = Math.round(r.width);
			return o;
		};
		return {
			label,
			bodyClasses: document.querySelector('#body_wrap')?.className?.slice(0, 120),
			header: cs(h, ['backgroundColor', 'height', 'paddingTop', 'paddingBottom']),
			headBarVisible: !!h.querySelector('.l-header__bar') && getComputedStyle(h.querySelector('.l-header__bar')).display !== 'none',
			gnavVisible: gnav ? getComputedStyle(gnav).display : null,
			gnavLinkCount: links.length,
			gnavTopItems: [...gnav?.querySelectorAll(':scope > .c-gnav > li > a') || []].map((a) => a.textContent.trim()),
			contact: contact
				? {
						display: getComputedStyle(contact).display,
						hours: contact.querySelector('.pega-header-contact__hours')?.textContent?.trim(),
						tel: contact.querySelector('.pega-header-contact__tel a')?.textContent?.trim(),
						btn: contact.querySelector('.pega-header-contact__link')?.textContent?.trim(),
					}
				: null,
			spMenuBtn: !!h.querySelector('.l-header__menuBtn'),
			customBtn: !!h.querySelector('.l-header__customBtn'),
			htmlSnippet: h.outerHTML.slice(0, 1200),
		};
	}, label);
}

async function analyze5210(page) {
	return page.evaluate(() => {
		const h = document.querySelector('.pega-header');
		const cs = (el) => {
			const s = getComputedStyle(el);
			const r = el.getBoundingClientRect();
			return {
				bg: s.backgroundColor,
				h: Math.round(r.height),
				w: Math.round(r.width),
				pad: `${s.paddingTop}/${s.paddingRight}/${s.paddingBottom}/${s.paddingLeft}`,
			};
		};
		const btns = [...document.querySelectorAll('.pega-header .wp-block-button__link')].map((a) => ({
			text: a.textContent.trim(),
			href: a.getAttribute('href'),
			style: a.getAttribute('style')?.slice(0, 100),
		}));
		return {
			header: cs(h),
			tagline: document.querySelector('.pega-header__tagline')?.textContent?.trim(),
			telSp: document.querySelector('.pega-header__tel .sp_only')?.textContent?.trim(),
			telPcHours: document.querySelector('.pega-header__tel .pc_only')?.textContent?.trim(),
			buttons: btns,
			hasGnav: !!document.querySelector('.pega-header nav, .pega-header .c-gnav'),
			menuItemCount: document.querySelectorAll('.pega-header nav a, .pega-header .menu a').length,
		};
	});
}

const browser = await chromium.launch();
for (const { path, w, label } of [
	{ path: '/', w: 1440, label: 'home-1440' },
	{ path: '/about/', w: 1440, label: 'about-1440' },
	{ path: '/', w: 768, label: 'home-768' },
]) {
	const page = await browser.newPage({ viewport: { width: w, height: 900 } });
	await page.goto(`${BASE}${path}`, { waitUntil: 'networkidle', timeout: 90000 });
	const data = await analyzeThemeHeader(page, label);
	writeFileSync(join(OUT, `${label}.json`), JSON.stringify(data, null, 2));
	await page.screenshot({ path: join(OUT, `theme-${label}.png`) });
	console.log(`\n=== theme ${label} ===\n`, JSON.stringify(data, null, 2));
	await page.close();
}

for (const w of [1440, 768]) {
	const page = await browser.newPage({ viewport: { width: w, height: 200 } });
	await page.goto(`file://${join(OUT, '5210-preview.html')}`, { waitUntil: 'networkidle' });
	const data = await analyze5210(page);
	writeFileSync(join(OUT, `5210-${w}.json`), JSON.stringify(data, null, 2));
	await page.screenshot({ path: join(OUT, `5210-${w}.png`) });
	console.log(`\n=== 5210 ${w}px ===\n`, JSON.stringify(data, null, 2));
	await page.close();
}

await browser.close();
