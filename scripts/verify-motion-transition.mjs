#!/usr/bin/env node
import { chromium } from 'playwright';

const URL = process.env.PEGA_URL || 'http://pegasus:4CkYn2kHCoO5vh@220.158.21.114:8080/';

const browser = await chromium.launch({ headless: true });
const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });
await page.goto(URL, { waitUntil: 'networkidle', timeout: 60000 });

await page.evaluate(() => window.scrollTo(0, 0));
await page.waitForTimeout(200);

const intro = page.locator('.pega-concerns__intro.pega-animate').first();
const box = await intro.boundingBox();
const triggerY = Math.max(0, (box?.y ?? 1100) - 280);
await page.evaluate((y) => window.scrollTo(0, y), triggerY);

const samples = [];
for (let i = 0; i < 16; i++) {
	await page.waitForTimeout(50);
	const s = await intro.evaluate((el) => {
		const cs = getComputedStyle(el);
		const m = cs.transform.match(/matrix\([^,]+,[^,]+,[^,]+,[^,]+,[^,]+,\s*([^)]+)\)/);
		return {
			hasVisible: el.classList.contains('is-visible'),
			opacity: parseFloat(cs.opacity),
			ty: m ? parseFloat(m[1]) : 0,
			transition: cs.transitionDuration,
		};
	});
	samples.push({ i, ...s });
}

await browser.close();
const mid = samples.filter((s) => s.hasVisible && s.opacity > 0 && s.opacity < 1);
console.log(JSON.stringify({ samples, midFrames: mid.length, animated: mid.length > 0 }, null, 2));
