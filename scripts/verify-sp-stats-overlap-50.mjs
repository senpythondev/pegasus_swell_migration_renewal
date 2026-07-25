#!/usr/bin/env node
import { chromium } from 'playwright';
import { mkdirSync } from 'fs';
import { join } from 'path';

const BASE = process.env.PEGA_URL || 'http://pegasus:4CkYn2kHCoO5vh@220.158.21.114:8080';
const OUT = join(process.cwd(), 'docs', 'sp-stats-overlap-50-verify');
mkdirSync(OUT, { recursive: true });

function overlapRects(a, b) {
	if (!a || !b) return 0;
	return Math.max(0, Math.min(a.b, b.b) - Math.max(a.y, b.y));
}

async function clipScreenshot(page, outPath) {
	const fv = await page.$('.main-text');
	const ach = await page.$('.achivement');
	if (!fv || !ach) return;
	const fvR = await fv.boundingBox();
	const achR = await ach.boundingBox();
	if (!fvR || !achR) return;
	const y = Math.max(0, fvR.y - 20);
	const h = achR.y + achR.height - y + 20;
	await page.screenshot({
		path: outPath,
		clip: { x: 0, y, width: page.viewportSize().width, height: Math.min(h, 900 - y) },
	});
}

const browser = await chromium.launch();
const results = [];

for (const w of [1440, 768, 425, 375]) {
	const page = await browser.newPage({ viewport: { width: w, height: 900 } });
	await page.goto(`${BASE}/swell-page/`, { waitUntil: 'networkidle', timeout: 90000 });
	const m = await page.evaluate(() => {
		const fv = document.querySelector('.main-text');
		const h1 = document.querySelector('.main-text h1');
		const btns = document.querySelector('.main-text .wp-block-buttons');
		const ach = document.querySelector('.achivement');
		function rect(el) {
			if (!el) return null;
			const r = el.getBoundingClientRect();
			return { y: Math.round(r.y), b: Math.round(r.bottom), h: Math.round(r.height) };
		}
		const achR = rect(ach);
		const fvR = rect(fv);
		const h1R = rect(h1);
		const btnR = rect(btns);
		const mt = ach ? parseFloat(getComputedStyle(ach).marginTop) : null;
		function overlap(a, b) {
			if (!a || !b) return 0;
			return Math.max(0, Math.min(a.b, b.b) - Math.max(a.y, b.y));
		}
		const overlapFv = overlap(fvR, achR);
		return {
			w: innerWidth,
			achH: achR?.h,
			achMT: mt,
			overlapFv,
			overlapRatioPct: achR?.h ? Math.round((overlapFv / achR.h) * 100) : null,
			gapBtnAch: achR && btnR ? Math.round(achR.y - btnR.b) : null,
			overlapH1: overlap(h1R, achR),
			overlapBtn: overlap(btnR, achR),
		};
	});
	results.push(m);
	console.log(JSON.stringify(m));
	await clipScreenshot(page, join(OUT, `overlap-${w}.png`));
	await page.close();
}

await browser.close();

const pc = results.find((r) => r.w === 1440);
const spOk = results
	.filter((r) => r.w <= 960)
	.every((r) => r.overlapRatioPct >= 45 && r.overlapRatioPct <= 55 && r.overlapH1 === 0);
process.exit(pc?.achMT === -64 && spOk ? 0 : 1);
