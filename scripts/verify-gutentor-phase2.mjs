#!/usr/bin/env node
/**
 * Gutentor除去 段階2 確認
 */
import { chromium } from 'playwright';
import { mkdirSync } from 'fs';
import { join } from 'path';

const BASE = process.env.PEGA_URL || 'http://pegasus:4CkYn2kHCoO5vh@220.158.21.114:8080';
const OUT = 'docs/gutentor-phase2-verify';
const WIDTHS = [1440, 1280, 1024, 961, 768, 425];
const PC_BASELINE = {
	1440: { statsMT: '-64px', achW: 900, fvW: 1440, cols: [272, 272, 272] },
	1280: { statsMT: '-64px', achW: 900, fvW: 1280, cols: [272, 272, 272] },
	1024: { statsMT: '-64px', achW: 900, fvW: 1024, cols: [272, 272, 272] },
	961: { statsMT: '-64px', achW: 900, fvW: 961, cols: [272, 272, 272] },
};

mkdirSync(OUT, { recursive: true });

const browser = await chromium.launch();
const page = await browser.newPage();
let failed = false;

for (const w of WIDTHS) {
	await page.setViewportSize({ width: w, height: 1200 });
	await page.goto(`${BASE}/swell-page/`, { waitUntil: 'domcontentloaded', timeout: 60000 });
	await page.waitForTimeout(1500);

	const data = await page.evaluate(() => {
		const ach = document.querySelector('.pega-stats .achivement');
		const fv = document.querySelector('.main-text.wp-block-cover');
		const wave = document.querySelector('figure:has(.wp-image-5098), .wp-block-image.wp-image-5098');
		const themeHeader = document.querySelector('#header, .l-header');
		const themeFooter = document.querySelector('#footer, .l-footer');
		const pageHeader = document.querySelector('header.pega-header');
		const breadcrumb = document.querySelector('#breadcrumb');
		const pageTitle = document.querySelector('.c-pageTitle, .l-mainContent__title, .p-pageHead');
		const stickyCta = document.querySelector('#pega-sticky-cta');
		const fixBtn = document.querySelector('.p-fixBtnWrap');
		const canvas = document.querySelector('.pega-canvas');
		const postContent = document.querySelector('.post_content');
		const boxes = [...document.querySelectorAll('.achivement .box-achievement')];
		return {
			url: location.pathname,
			templateClass: document.querySelector('#body_wrap')?.className.includes('page-template-page-pega-fullwidth'),
			themeHeaderVisible: themeHeader ? themeHeader.offsetHeight > 0 && getComputedStyle(themeHeader).display !== 'none' : false,
			themeFooterVisible: themeFooter ? themeFooter.offsetHeight > 0 && getComputedStyle(themeFooter).display !== 'none' : false,
			pageHeaderVisible: pageHeader ? pageHeader.offsetHeight > 0 : false,
			breadcrumbVisible: breadcrumb ? breadcrumb.offsetHeight > 0 && getComputedStyle(breadcrumb).display !== 'none' : false,
			pageTitleVisible: pageTitle ? pageTitle.offsetHeight > 0 : false,
			stickyCtaPresent: !!stickyCta,
			stickyCtaVisible: stickyCta ? stickyCta.offsetHeight > 0 : false,
			fixBtnVisible: fixBtn ? fixBtn.offsetHeight > 0 : false,
			hasPegaCanvas: !!canvas,
			hasPostContent: !!postContent,
			statsMT: ach ? getComputedStyle(ach).marginTop : null,
			achW: ach ? Math.round(ach.getBoundingClientRect().width) : null,
			fvW: fv ? Math.round(fv.getBoundingClientRect().width) : null,
			waveVisible: wave ? wave.offsetHeight > 0 : false,
			cols: boxes.map((el) => Math.round(el.getBoundingClientRect().width)),
		};
	});

	const base = PC_BASELINE[w];
	if (base) {
		if (data.statsMT !== base.statsMT || Math.abs(data.fvW - base.fvW) > 3 || data.achW !== base.achW) {
			console.error(`PC layout mismatch at ${w}`, { data, expected: base });
			failed = true;
		}
		if (JSON.stringify(data.cols) !== JSON.stringify(base.cols)) {
			console.error(`PC cols mismatch at ${w}`, data.cols, base.cols);
			failed = true;
		}
	}
	if (data.pageHeaderVisible) {
		console.error(`5210 header still visible at ${w}`);
		failed = true;
	}
	if (!data.themeHeaderVisible || !data.themeFooterVisible) {
		console.error(`Theme H/F missing at ${w}`, data);
		failed = true;
	}
	if (data.breadcrumbVisible || data.pageTitleVisible) {
		console.error(`Breadcrumb/title visible at ${w}`, data);
		failed = true;
	}
	if (!data.hasPegaCanvas || data.hasPostContent) {
		console.error(`Canvas wrapper issue at ${w}`, data);
		failed = true;
	}
	if (data.url !== '/swell-page/') {
		console.error(`URL changed: ${data.url}`);
		failed = true;
	}

	await page.screenshot({ path: join(OUT, `swell-page-${w}.png`), fullPage: true });
	console.log(JSON.stringify({ w, ...data }));
}

await page.setViewportSize({ width: 1440, height: 1200 });
await page.goto(`${BASE}/swell-page/`, { waitUntil: 'domcontentloaded' });
await page.locator('#header, .l-header').first().screenshot({ path: join(OUT, 'theme-header-1440.png') }).catch(() => {});
await page.locator('#footer, .l-footer').first().screenshot({ path: join(OUT, 'theme-footer-1440.png') }).catch(() => {});

for (const path of ['/', '/about/']) {
	await page.goto(`${BASE}${path}`, { waitUntil: 'domcontentloaded' });
	await page.waitForTimeout(1000);
	const d = await page.evaluate(() => ({
		breadcrumbHidden4929rule: true,
		pageHeader5210: document.querySelectorAll('header.pega-header').length,
	}));
	console.log('OTHER', path, JSON.stringify(d));
}

await browser.close();
process.exit(failed ? 1 : 0);
