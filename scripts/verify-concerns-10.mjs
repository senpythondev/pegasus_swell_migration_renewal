#!/usr/bin/env node
/**
 * Work 2: concerns section screenshots at 4 widths
 */
import { chromium } from 'playwright';
import { mkdirSync } from 'fs';
import { join } from 'path';

const BASE = process.env.PEGA_URL || 'http://pegasus:4CkYn2kHCoO5vh@220.158.21.114:8080';
const OUT = process.env.PEGA_OUT || join(process.cwd(), 'docs', 'work2-verify');
const WIDTHS = [1440, 1024, 768, 425];

mkdirSync(OUT, { recursive: true });

async function main() {
	const browser = await chromium.launch();
	const page = await browser.newPage({ ignoreHTTPSErrors: true });

	for (const w of WIDTHS) {
		await page.setViewportSize({ width: w, height: 900 });
		await page.goto(`${BASE}/swell-page/`, { waitUntil: 'networkidle', timeout: 60000 });
		await page.locator('.wave-section img').first().waitFor({ timeout: 15000 }).catch(() => {});
		await page.evaluate(async () => {
			const imgs = [...document.querySelectorAll('.wave-section img')];
			await Promise.all(
				imgs.map((img) =>
					img.complete
						? Promise.resolve()
						: new Promise((res) => {
								img.onload = res;
								img.onerror = res;
							})
				)
			);
		});
		await page.waitForTimeout(500);
		const section = page.locator('.wave-section').first();
		if (await section.count()) {
			await section.scrollIntoViewIfNeeded();
			await page.waitForTimeout(400);
			await section.screenshot({ path: join(OUT, `concerns-${w}.png`) });
			console.log('saved concerns-' + w + '.png');
		}
		await page.screenshot({ path: join(OUT, `full-${w}.png`), fullPage: true });
		console.log('saved full-' + w + '.png');
	}

	await browser.close();
}

main().catch((e) => {
	console.error(e);
	process.exit(1);
});
