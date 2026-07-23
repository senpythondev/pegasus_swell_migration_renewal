#!/usr/bin/env node
/**
 * Verify PC/SP toggle on swell-page (4929)
 */
import { chromium } from 'playwright';
import { mkdirSync } from 'fs';
import { join } from 'path';

const BASE = process.env.PEGA_URL || 'http://pegasus:4CkYn2kHCoO5vh@220.158.21.114:8080';
const OUT = process.env.PEGA_OUT || join(process.cwd(), 'docs', 'pc-sp-verify');
const WIDTHS = [1440, 1024, 768, 425];

mkdirSync(OUT, { recursive: true });

function isVisible(el) {
	if (!el) return false;
	const st = getComputedStyle(el);
	const r = el.getBoundingClientRect();
	return st.display !== 'none' && st.visibility !== 'hidden' && r.height > 0 && r.width > 0;
}

async function check(page) {
	return page.evaluate(() => {
		const vis = (s) => {
			const el = document.querySelector(s);
			if (!el) return { sel: s, found: false, visible: false };
			const st = getComputedStyle(el);
			const r = el.getBoundingClientRect();
			return {
				sel: s,
				found: true,
				visible: st.display !== 'none' && st.visibility !== 'hidden' && r.height > 0,
			};
		};
		return {
			achievement: vis('.pega-stats .achivement'),
			mobileImg: vis('.pega-stats .mobile-img'),
			gemini: vis('figure:has(.wp-image-5098)'),
			mobileGirl: vis('.pega-service-intro .mobile-girl'),
			girl: vis('.pega-service-intro .girl'),
			headerMobile: vis('.pega-header .mobile'),
			headerTel: vis('.pega-header .pega-header__tel'),
		};
	});
}

async function main() {
	const browser = await chromium.launch();
	const page = await browser.newPage({ ignoreHTTPSErrors: true });
	const results = {};

	for (const w of WIDTHS) {
		await page.setViewportSize({ width: w, height: 900 });
		await page.goto(`${BASE}/swell-page/`, { waitUntil: 'networkidle', timeout: 60000 });
		await page.waitForTimeout(600);
		results[w] = await check(page);
		await page.screenshot({ path: join(OUT, `swell-page-${w}.png`), fullPage: true });
		console.log(`saved swell-page-${w}.png`);
	}

	await browser.close();

	let ok = true;
	for (const w of [1440, 1024]) {
		const r = results[w];
		if (r.mobileImg.visible || r.mobileGirl.visible || r.headerMobile.visible) {
			console.error(`FAIL PC ${w}: SP elements visible`, r);
			ok = false;
		}
		if (!r.achievement.visible || !r.girl.visible) {
			console.error(`FAIL PC ${w}: PC elements hidden`, r);
			ok = false;
		}
	}
	for (const w of [768, 425]) {
		const r = results[w];
		if (r.achievement.visible || r.girl.visible || r.headerTel.visible) {
			console.error(`FAIL SP ${w}: PC elements visible`, r);
			ok = false;
		}
		if (!r.mobileImg.visible || !r.mobileGirl.visible) {
			console.error(`FAIL SP ${w}: SP elements hidden`, r);
			ok = false;
		}
		if (r.gemini.visible) {
			console.error(`FAIL SP ${w}: gemini wave should be hidden`, r);
			ok = false;
		}
	}

	console.log(JSON.stringify(results, null, 2));
	process.exit(ok ? 0 : 1);
}

main().catch((e) => {
	console.error(e);
	process.exit(1);
});
