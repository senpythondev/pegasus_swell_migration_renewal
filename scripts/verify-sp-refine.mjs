#!/usr/bin/env node
/**
 * SP refinement verification + overlap checks
 */
import { chromium } from 'playwright';
import { mkdirSync } from 'fs';
import { join } from 'path';

const BASE = process.env.PEGA_URL || 'http://pegasus:4CkYn2kHCoO5vh@220.158.21.114:8080';
const OUT = 'docs/sp-refine-verify';
const PC_BASELINE = {
	1440: { statsPad: '0px 10px 10px', achPad: '10px 18px 8px', achMT: '-64px', achH: 127, achW: 900, cols: [272, 272, 272] },
	1280: { statsPad: '0px 10px 10px', achPad: '10px 18px 8px', achMT: '-64px', achH: 127, achW: 900, cols: [272, 272, 272] },
	1024: { statsPad: '0px 10px 10px', achPad: '10px 18px 8px', achMT: '-64px', achH: 127, achW: 900, cols: [272, 272, 272] },
	961: { statsPad: '0px 10px 10px', achPad: '10px 18px 8px', achMT: '-64px', achH: 127, achW: 900, cols: [272, 272, 272] },
};
const WIDTHS = [1440, 1280, 1024, 961, 960, 768, 600, 425, 375];

mkdirSync(OUT, { recursive: true });

const browser = await chromium.launch();
const page = await browser.newPage();
let failed = false;

for (const w of WIDTHS) {
	await page.setViewportSize({ width: w, height: 900 });
	await page.goto(`${BASE}/swell-page/`, { waitUntil: 'networkidle', timeout: 60000 });

	const data = await page.evaluate(() => {
		const cs = (el) => (el ? getComputedStyle(el) : null);
		const rr = (el) => (el ? el.getBoundingClientRect() : null);
		const ach = document.querySelector('.pega-stats .achivement');
		const stats = document.querySelector('.pega-stats.u-mb-ctrl');
		const h1 = document.querySelector('.main-text h1');
		const cover = document.querySelector('.main-text.wp-block-cover');
		const btns = document.querySelector('.main-text .wp-block-buttons');
		const btnLinks = [...document.querySelectorAll('.main-text .wp-block-buttons .wp-block-button__link')];
		const boxes = [...document.querySelectorAll('.achivement .box-achievement')];
		const girlImg = document.querySelector('.pega-service-intro .girl figure.wp-block-image');
		const girlText = document.querySelector('.pega-service-intro .girl .wp-block-column:last-child');
		const cols = boxes.map((el) => Math.round(el.getBoundingClientRect().width));
		const colTops = boxes.map((el) => Math.round(el.getBoundingClientRect().top));
		const colTopSpread = colTops.length ? Math.max(...colTops) - Math.min(...colTops) : 0;
		const lastBorder = boxes[2] ? cs(boxes[2]).borderRightWidth : null;
		const cr = cover?.getBoundingClientRect();
		const faceLeft = cr ? cr.left + cr.width * 0.42 : null;
		const hr = h1?.getBoundingClientRect();
		return {
			statsPad: cs(stats)?.padding,
			achPad: cs(ach)?.padding,
			achMT: cs(ach)?.marginTop,
			achH: Math.round(rr(ach)?.height || 0),
			achW: Math.round(rr(ach)?.width || 0),
			cols,
			colOneRow: colTopSpread <= 2 && cols.length === 3,
			lastBorderRight: lastBorder,
			h1Font: cs(h1)?.fontSize,
			h1Right: hr ? Math.round(hr.right) : null,
			faceLeft: faceLeft ? Math.round(faceLeft) : null,
			h1Overlap: hr && faceLeft ? hr.right > faceLeft : null,
			btnFlexDir: cs(btns)?.flexDirection,
			btnWidths: btnLinks.map((el) => Math.round(el.getBoundingClientRect().width)),
			btnHeights: btnLinks.map((el) => Math.round(el.getBoundingClientRect().height)),
			girlBg: cs(girlImg)?.backgroundImage.includes('woman-top-pc'),
			girlTextAlign: cs(girlText)?.textAlign,
		};
	});

	const base = PC_BASELINE[w];
	const pcMatch = base
		? data.statsPad === base.statsPad &&
			data.achPad === base.achPad &&
			data.achMT === base.achMT &&
			data.achH === base.achH &&
			data.achW === base.achW &&
			JSON.stringify(data.cols) === JSON.stringify(base.cols)
		: null;

	if (base && !pcMatch) {
		console.error(`PC REGRESSION at ${w}`);
		failed = true;
	}
	if (w <= 960) {
		if (!data.colOneRow) {
			console.error(`Stats not 1 row at ${w}`, data.cols);
			failed = true;
		}
		if (data.lastBorderRight !== '0px') {
			console.error(`Last border not removed at ${w}:`, data.lastBorderRight);
			failed = true;
		}
		if (w <= 960 && data.h1Overlap) {
			console.error(`H1 overlaps face at ${w}`);
			failed = true;
		}
		if (data.btnFlexDir !== 'column') {
			console.error(`Buttons not column at ${w}:`, data.btnFlexDir);
			failed = true;
		}
		if (data.btnWidths.length === 2 && data.btnWidths[0] !== data.btnWidths[1]) {
			console.error(`Button width mismatch at ${w}:`, data.btnWidths);
			failed = true;
		}
	}

	await page.screenshot({ path: join(OUT, `swell-page-${w}.png`), fullPage: true });
	console.log(
		JSON.stringify({
			w,
			pcMatch,
			h1Font: data.h1Font,
			h1Overlap: data.h1Overlap,
			cols: data.cols,
			colOneRow: data.colOneRow,
			lastBorder: data.lastBorderRight,
			btnDir: data.btnFlexDir,
			btnW: data.btnWidths,
			btnH: data.btnHeights,
			girlBg: data.girlBg,
			girlAlign: data.girlTextAlign,
		})
	);
}

await browser.close();
process.exit(failed ? 1 : 0);
