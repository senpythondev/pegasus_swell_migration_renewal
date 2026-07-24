#!/usr/bin/env node
/**
 * 段階1復元後: 段階1前(baseline)と一致するか確認
 */
import { chromium } from 'playwright';
import { mkdirSync } from 'fs';
import { join } from 'path';

const BASE = process.env.PEGA_URL || 'http://pegasus:4CkYn2kHCoO5vh@220.158.21.114:8080';
const OUT = 'docs/restore-pre-phase1-verify';
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
		const boxes = [...document.querySelectorAll('.achivement .box-achievement')];
		const themeHeader = document.querySelector('#header, .l-header');
		const themeFooter = document.querySelector('#footer, .l-footer');
		const pageHeader = document.querySelector('header.pega-header');
		const breadcrumb = document.querySelector('.p-breadcrumb, .c-breadcrumb, .breadcrumb');
		const pageTitle = document.querySelector('.l-mainContent__title, .c-pageTitle');
		return {
			themeHeaderVisible: themeHeader ? themeHeader.offsetHeight > 0 && getComputedStyle(themeHeader).display !== 'none' : false,
			themeFooterVisible: themeFooter ? themeFooter.offsetHeight > 0 && getComputedStyle(themeFooter).display !== 'none' : false,
			pageHeaderVisible: pageHeader ? pageHeader.offsetHeight > 0 && getComputedStyle(pageHeader).display !== 'none' : false,
			breadcrumbVisible: breadcrumb ? breadcrumb.offsetHeight > 0 : false,
			pageTitleVisible: pageTitle ? pageTitle.offsetHeight > 0 : false,
			statsMT: ach ? getComputedStyle(ach).marginTop : null,
			achW: ach ? Math.round(ach.getBoundingClientRect().width) : null,
			fvW: fv ? Math.round(fv.getBoundingClientRect().width) : null,
			cols: boxes.map((el) => Math.round(el.getBoundingClientRect().width)),
		};
	});

	const base = PC_BASELINE[w];
	if (base) {
		if (data.statsMT !== base.statsMT || data.achW !== base.achW || Math.abs(data.fvW - base.fvW) > 2) {
			console.error(`PC mismatch at ${w}`, data, 'expected', base);
			failed = true;
		}
		if (JSON.stringify(data.cols) !== JSON.stringify(base.cols)) {
			console.error(`PC cols mismatch at ${w}`, data.cols, base.cols);
			failed = true;
		}
	}
	if (data.themeHeaderVisible || data.themeFooterVisible) {
		console.error(`Theme H/F visible at ${w}`);
		failed = true;
	}
	if (!data.pageHeaderVisible) {
		console.error(`Page header 5210 not visible at ${w}`);
		failed = true;
	}
	if (data.breadcrumbVisible || data.pageTitleVisible) {
		console.error(`Breadcrumb/title visible at ${w}`);
		failed = true;
	}

	await page.screenshot({ path: join(OUT, `swell-page-${w}.png`), fullPage: true });
	console.log(JSON.stringify({ w, ...data }));
}

await browser.close();
process.exit(failed ? 1 : 0);
