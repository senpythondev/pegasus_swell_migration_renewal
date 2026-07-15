#!/usr/bin/env node
/** スクロール中の transition 中間値をフレーム単位で記録 */
import { chromium } from 'playwright';

const URL = process.env.PEGA_URL || 'http://pegasus:4CkYn2kHCoO5vh@220.158.21.114:8080/';

async function captureFrames(label) {
	const browser = await chromium.launch({ headless: true });
	const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });
	await page.goto(URL, { waitUntil: 'networkidle', timeout: 60000 });

	const intro = page.locator('.pega-concerns__intro.pega-animate').first();
	const box = await intro.boundingBox();
	const targetY = Math.max(0, (box?.y ?? 1200) - 200);

	const frames = [];
	for (let i = 0; i <= 12; i++) {
		const y = Math.round((targetY * i) / 12);
		await page.evaluate((scrollY) => window.scrollTo(0, scrollY), y);
		await page.waitForTimeout(80);
		const sample = await intro.evaluate((el) => ({
			scrollY: window.scrollY,
			hasVisible: el.classList.contains('is-visible'),
			opacity: getComputedStyle(el).opacity,
			transform: getComputedStyle(el).transform,
		}));
		frames.push(sample);
	}

	await browser.close();
	console.log(`=== ${label} ===`);
	console.log(JSON.stringify(frames, null, 2));
}

await captureFrames('motion-frames');
