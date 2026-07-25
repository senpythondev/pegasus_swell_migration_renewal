#!/usr/bin/env node
/**
 * Compare headers and top spacing: / vs /swell-page/
 * Investigation only — no changes.
 */
import { chromium } from 'playwright';
import { writeFileSync, mkdirSync } from 'fs';
import { join } from 'path';

const BASE = process.env.PEGA_URL || 'http://pegasus:4CkYn2kHCoO5vh@220.158.21.114:8080';
const OUT = join(process.cwd(), 'docs', 'header-compare-investigate');
mkdirSync(OUT, { recursive: true });

async function analyze(page, path) {
	return page.evaluate((path) => {
		const bodyWrap = document.querySelector('#body_wrap');
		const header = document.querySelector('#header');
		const fixHeader = document.querySelector('#fix_header');
		const fv =
			document.querySelector('.main-text.wp-block-cover') ||
			document.querySelector('.pega-hero-wrap') ||
			document.querySelector('.pega-canvas > .wp-block-group.alignfull');

		function outer(el, max = 8000) {
			if (!el) return null;
			return el.outerHTML.slice(0, max);
		}

		function cs(el, props) {
			if (!el) return null;
			const s = getComputedStyle(el);
			const o = {};
			for (const p of props) o[p] = s[p];
			const r = el.getBoundingClientRect();
			o._rect = { x: Math.round(r.x), y: Math.round(r.y), w: Math.round(r.width), h: Math.round(r.height) };
			return o;
		}

		// Gap between header bottom and FV top
		const headerBottom = header?.getBoundingClientRect().bottom ?? 0;
		const fvTop = fv?.getBoundingClientRect().top ?? 0;
		const gapPx = Math.round(fvTop - headerBottom);

		// Elements between header and FV in DOM order
		const between = [];
		if (header && fv) {
			let n = header.nextElementSibling;
			while (n && n !== fv && !n.contains(fv)) {
				const r = n.getBoundingClientRect();
				between.push({
					tag: n.tagName,
					id: n.id || null,
					cls: (n.className || '').toString().slice(0, 100),
					h: Math.round(r.height),
					y: Math.round(r.y),
					display: getComputedStyle(n).display,
					text: (n.textContent || '').trim().slice(0, 80),
				});
				n = n.nextElementSibling;
			}
		}

		// First 3 direct children of .pega-canvas (4929)
		const canvasKids = [...(document.querySelector('.pega-canvas')?.children || [])].slice(0, 5).map((el) => ({
			tag: el.tagName,
			cls: (el.className || '').toString().slice(0, 100),
			h: Math.round(el.getBoundingClientRect().height),
		}));

		// pega-header block remnants
		const pegaHeaderBlocks = [...document.querySelectorAll('.pega-header, [class*="pega-header"]')].map(
			(el) => ({
				cls: (el.className || '').toString().slice(0, 80),
				h: Math.round(el.getBoundingClientRect().height),
				visible: el.getBoundingClientRect().height > 0,
			})
		);

		// Rules from inline stylesheets mentioning pega-header or pega-top #header
		const pegaHeaderRules = [];
		for (const sheet of document.styleSheets) {
			let rules;
			try {
				rules = sheet.cssRules;
			} catch {
				continue;
			}
			for (const rule of rules) {
				if (!rule.selectorText) continue;
				if (
					rule.selectorText.includes('pega-header') ||
					rule.selectorText.includes('pega-top #header') ||
					rule.selectorText.includes('p-spMenu') ||
					rule.selectorText.includes('#sp_menu')
				) {
					pegaHeaderRules.push({
						sel: rule.selectorText.slice(0, 120),
						css: rule.cssText.slice(0, 150),
						href: sheet.href || 'inline',
					});
				}
			}
		}

		// Which pega-css-10 rules match header/sp menu on THIS page
		const matchedOnHeader = pegaHeaderRules
			.filter((r) => {
				try {
					return header && header.matches(r.sel.split(',')[0].trim());
				} catch {
					return false;
				}
			})
			.map((r) => r.sel);

		const spMenu = document.querySelector('#sp_menu');
		const matchedOnSpMenu = pegaHeaderRules
			.filter((r) => {
				try {
					return spMenu && spMenu.matches(r.sel.split(',')[0].trim());
				} catch {
					return false;
				}
			})
			.map((r) => r.sel);

		return {
			path,
			url: location.href,
			bodyWrapClasses: bodyWrap?.className || null,
			hasPegaTop: bodyWrap?.classList.contains('pega-top') || document.body.classList.contains('pega-top'),
			pageIdClass: bodyWrap?.className?.match(/page-id-\d+|home|blog/)?.[0] || null,
			templateClass: bodyWrap?.className?.match(/page-template-[^\s]+/)?.[0] || null,
			headerExists: !!header,
			headerClass: header?.className || null,
			fixHeaderClass: fixHeader?.className || null,
			headerHtmlLen: header?.outerHTML.length || 0,
			headerStructure: {
				hasHeadBar: !!header?.querySelector('.l-header__bar'),
				hasGnav: !!header?.querySelector('#gnav'),
				hasLogo: !!header?.querySelector('.l-header__logo'),
				hasPegaContact: !!header?.querySelector('.pega-header-contact'),
				hasSpMenuBtn: !!header?.querySelector('.l-header__menuBtn'),
				gnavLinkCount: header?.querySelectorAll('#gnav a').length || 0,
				childOrder: [...(header?.children || [])].map((c) => `${c.tagName}.${(c.className || '').toString().slice(0, 40)}`),
			},
			headerComputed: cs(header, ['height', 'paddingTop', 'paddingBottom', 'marginBottom', 'position']),
			fixHeaderComputed: cs(fixHeader, ['display', 'visibility', 'opacity', 'height']),
			fvSelector: fv?.className?.toString().slice(0, 80) || null,
			fvComputed: cs(fv, ['marginTop', 'paddingTop']),
			gapHeaderToFv: gapPx,
			betweenHeaderAndFv: between,
			canvasFirstChildren: canvasKids,
			pegaHeaderRemnants: pegaHeaderBlocks.filter((b) => b.visible || b.cls.includes('pega-header')),
			pegaCss10RuleCount: pegaHeaderRules.length,
			pegaCss10RulesSample: pegaHeaderRules.slice(0, 15),
			matchedPegaCss10OnHeader: matchedOnHeader,
			matchedPegaCss10OnSpMenu: matchedOnSpMenu,
			mainContentTop: cs(document.querySelector('#main_content, #content'), ['paddingTop', 'marginTop']),
			breadcrumb: cs(document.querySelector('#breadcrumb'), ['display', 'height']),
		};
	}, path);
}

const browser = await chromium.launch();
for (const { path, w, label } of [
	{ path: '/', w: 1440, label: 'home-1440' },
	{ path: '/swell-page/', w: 1440, label: 'swell-page-1440' },
	{ path: '/', w: 425, label: 'home-425' },
	{ path: '/swell-page/', w: 425, label: 'swell-page-425' },
]) {
	const page = await browser.newPage({ viewport: { width: w, height: 900 } });
	await page.goto(`${BASE}${path}`, { waitUntil: 'networkidle', timeout: 90000 });
	const data = await analyze(page, path);
	const headerHtml = await page.evaluate(() => document.querySelector('#header')?.outerHTML || '');
	writeFileSync(join(OUT, `${label}.json`), JSON.stringify(data, null, 2));
	writeFileSync(join(OUT, `header-html-${label}.html`), headerHtml.slice(0, 12000));
	await page.screenshot({ path: join(OUT, `${label}-top.png`) });
	console.log(`\n=== ${label} ===`);
	console.log(JSON.stringify(data, null, 2));
	await page.close();
}

// /about/ for pega-css-10 cross-page impact
{
	const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });
	await page.goto(`${BASE}/about/`, { waitUntil: 'networkidle' });
	const data = await analyze(page, '/about/');
	writeFileSync(join(OUT, 'about-1440.json'), JSON.stringify(data, null, 2));
	console.log(`\n=== about-1440 ===`);
	console.log(JSON.stringify({ ...data, pegaCss10RulesSample: data.pegaCss10RulesSample?.slice(0, 8) }, null, 2));
	await page.close();
}

await browser.close();
