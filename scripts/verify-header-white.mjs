#!/usr/bin/env node
/**
 * 白背景ヘッダー + CTA段階縮退 + 賢い追従 検証
 */
import { chromium } from 'playwright';
import { mkdirSync, writeFileSync } from 'fs';
import { join } from 'path';

const BASE = process.env.PEGA_URL || 'http://pegasus:4CkYn2kHCoO5vh@220.158.21.114:8080';
const OUT = process.env.PEGA_VERIFY_OUT || 'docs/header-white-verify';
const WIDTHS = [1440, 1024, 768, 425];

mkdirSync(OUT, { recursive: true });

const browser = await chromium.launch();
const page = await browser.newPage();

async function inspectHeader(path, label) {
	const results = {};
	for (const w of WIDTHS) {
		await page.setViewportSize({ width: w, height: 900 });
		await page.goto(`${BASE}${path}`, { waitUntil: 'domcontentloaded', timeout: 60000 });
		await page.waitForTimeout(1200);
		const data = await page.evaluate(() => {
			const header = document.querySelector('#header');
			const gnav = document.querySelector('#header .l-header__gnav');
			const ctas = [...document.querySelectorAll('.pega-header__cta-item')].filter(
				(el) => getComputedStyle(el).display !== 'none' && el.offsetParent !== null
			);
			const contactInfo = document.querySelector('.pega-header-contact__info');
			const telIcon = document.querySelector('.pega-header-contact__tel-icon');
			const menuBtn = document.querySelector('#header .l-header__menuBtn');
			const content = document.querySelector('#content');
			const fv = document.querySelector('.main-text.wp-block-cover, .pega-hero-wrap, .pega-hero-fv-bg');
			const fixHeader = document.querySelector('#fix_header');
			const bodyWrap = document.getElementById('body_wrap');
			let fvGap = null;
			if (header && fv) {
				const hr = header.getBoundingClientRect();
				const fr = fv.getBoundingClientRect();
				fvGap = Math.round(fr.top - hr.bottom);
			}
			return {
				hasPegaTop: bodyWrap?.classList.contains('pega-top') ?? false,
				headerBg: header ? getComputedStyle(header).backgroundColor : null,
				gnavDisplay: gnav ? getComputedStyle(gnav).display : null,
				ctaCount: ctas.length,
				ctaLabels: ctas.map((el) => el.textContent.trim()),
				contactInfoVisible: contactInfo ? contactInfo.offsetHeight > 0 && getComputedStyle(contactInfo).display !== 'none' : false,
				telIconVisible: telIcon ? telIcon.offsetHeight > 0 && getComputedStyle(telIcon).display !== 'none' : false,
				menuVisible: menuBtn ? menuBtn.offsetHeight > 0 && getComputedStyle(menuBtn).display !== 'none' : false,
				fixHeaderHidden: fixHeader ? getComputedStyle(fixHeader).display === 'none' : true,
				contentPadTop: content ? getComputedStyle(content).paddingTop : null,
				fvGap,
			};
		});
		results[w] = data;
		if (label === 'swell-page') {
			await page.locator('#header').screenshot({ path: join(OUT, `header-${w}.png`) }).catch(() => {});
			await page.screenshot({ path: join(OUT, `page-${w}.png`), fullPage: false });
		}
		if (label === 'about' && w === 1440) {
			await page.locator('#header').screenshot({ path: join(OUT, 'about-header-1440.png') }).catch(() => {});
		}
	}
	return results;
}

async function testHideScroll() {
	await page.setViewportSize({ width: 1440, height: 900 });
	await page.goto(`${BASE}/swell-page/`, { waitUntil: 'domcontentloaded', timeout: 60000 });
	await page.waitForTimeout(800);
	await page.evaluate(() => window.scrollTo(0, 400));
	await page.waitForTimeout(400);
	const hiddenDown = await page.evaluate(() => document.getElementById('body_wrap')?.classList.contains('pega-headhide'));
	await page.evaluate(() => window.scrollTo(0, 0));
	await page.waitForTimeout(400);
	const shownUp = await page.evaluate(() => !document.getElementById('body_wrap')?.classList.contains('pega-headhide'));
	return { hiddenDown, shownUp };
}

async function testSpMenu() {
	await page.setViewportSize({ width: 425, height: 900 });
	await page.goto(`${BASE}/swell-page/`, { waitUntil: 'domcontentloaded', timeout: 60000 });
	await page.waitForTimeout(1000);
	await page.locator('#header .l-header__menuBtn [data-onclick="toggleMenu"]').first().click({ force: true });
	await page.waitForTimeout(800);
	const menu = await page.evaluate(() => {
		const items = [...document.querySelectorAll('#sp_menu .c-spnav > li > a .ttl, #sp_menu .c-spnav > li > a')].map((el) => el.textContent.trim()).filter(Boolean);
		return { open: !!document.querySelector('#sp_menu.is-opened, #sp_menu.opened'), topItems: items.slice(0, 8) };
	});
	await page.screenshot({ path: join(OUT, 'sp-menu-open-425.png') });
	return menu;
}

const swell = await inspectHeader('/swell-page/', 'swell-page');
const about = await inspectHeader('/about/', 'about');
const hideScroll = await testHideScroll();
const spMenu = await testSpMenu();

await browser.close();

const report = { swell, about, hideScroll, spMenu, out: OUT };
writeFileSync(join(OUT, 'report.json'), JSON.stringify(report, null, 2));
console.log(JSON.stringify(report, null, 2));
