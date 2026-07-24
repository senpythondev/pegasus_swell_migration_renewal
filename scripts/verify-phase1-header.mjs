#!/usr/bin/env node
/**
 * 段階1: テンプレート復帰後の確認（4929 + 他ページ）
 */
import { chromium } from 'playwright';
import { mkdirSync } from 'fs';
import { join } from 'path';

const BASE = process.env.PEGA_URL || 'http://pegasus:4CkYn2kHCoO5vh@220.158.21.114:8080';
const OUT = process.env.PEGA_VERIFY_OUT || 'docs/phase1-header-verify';
const WIDTHS = [1440, 1280, 1024, 961, 768, 425];

mkdirSync(OUT, { recursive: true });

const browser = await chromium.launch();
const page = await browser.newPage();

async function inspectPage(path) {
	const results = {};
	for (const w of WIDTHS) {
		await page.setViewportSize({ width: w, height: 1200 });
		await page.goto(`${BASE}${path}`, { waitUntil: 'domcontentloaded', timeout: 60000 });
		await page.waitForTimeout(1500);
		const data = await page.evaluate(() => {
			const themeHeader = document.querySelector('#header, .l-header');
			const themeFooter = document.querySelector('#footer, .l-footer');
			const pageHeader = document.querySelector('header.pega-header');
			const breadcrumb = document.querySelector('.p-breadcrumb, .c-breadcrumb, .breadcrumb');
			const pageTitle = document.querySelector('.l-mainContent__title, .c-pageTitle');
			const ach = document.querySelector('.pega-stats .achivement');
			const fv = document.querySelector('.main-text.wp-block-cover');
			return {
				themeHeaderVisible: themeHeader ? getComputedStyle(themeHeader).display !== 'none' && themeHeader.offsetHeight > 0 : false,
				themeFooterVisible: themeFooter ? getComputedStyle(themeFooter).display !== 'none' && themeFooter.offsetHeight > 0 : false,
				pageHeaderVisible: pageHeader ? getComputedStyle(pageHeader).display !== 'none' && pageHeader.offsetHeight > 0 : false,
				breadcrumbVisible: breadcrumb ? breadcrumb.offsetHeight > 0 : false,
				pageTitleVisible: pageTitle ? pageTitle.offsetHeight > 0 : false,
				statsMT: ach ? getComputedStyle(ach).marginTop : null,
				statsW: ach ? Math.round(ach.getBoundingClientRect().width) : null,
				fvW: fv ? Math.round(fv.getBoundingClientRect().width) : null,
				viewportW: innerWidth,
			};
		});
		results[w] = data;
		if (path === '/swell-page/') {
			await page.screenshot({ path: join(OUT, `swell-page-${w}.png`), fullPage: true });
		}
	}
	return results;
}

const swell = await inspectPage('/swell-page/');
const home = await inspectPage('/');
const about = await inspectPage('/about/');

await browser.close();

console.log('=== SWELL-PAGE RESTORE CHECK ===');
for (const w of WIDTHS) {
	console.log(JSON.stringify({ w, ...swell[w] }));
}
console.log('=== HOME ===');
console.log(JSON.stringify({ w: 1440, ...home[1440] }));
console.log('=== ABOUT ===');
console.log(JSON.stringify({ w: 1440, ...about[1440] }));
