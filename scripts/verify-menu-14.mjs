#!/usr/bin/env node
import { chromium } from 'playwright';

const BASE = process.env.PEGA_URL || 'http://pegasus:4CkYn2kHCoO5vh@220.158.21.114:8080';
const WIDTHS = [1440, 1024, 768, 425];

const browser = await chromium.launch({ headless: true });
const ctx = await browser.newContext({
	httpCredentials: { username: 'pegasus', password: '4CkYn2kHCoO5vh' },
});

for (const w of WIDTHS) {
	const page = await ctx.newPage({ viewport: { width: w, height: 900 } });
	await page.goto(`${BASE}/homeworker/?v=${Date.now()}`, { waitUntil: 'networkidle', timeout: 90000 });

	const pc = await page.evaluate(() =>
		[...document.querySelectorAll('#gnav .c-gnav > li')].map((li) => ({
			t: li.querySelector(':scope > a')?.textContent?.trim(),
			children: [...li.querySelectorAll(':scope .sub-menu > li > a, :scope > ul.sub-menu > li > a')].map((a) =>
				a.textContent.trim()
			),
		}))
	);

	let sp = [];
	if (w <= 960) {
		const openBtn = page.locator('.l-header__menuBtn.sp_, .c-spMenu__openBtn, .p-spMenu__openBtn').first();
		if (await openBtn.count()) {
			await openBtn.click({ force: true });
			await page.waitForTimeout(800);
		}
		sp = await page.evaluate(() =>
			[...document.querySelectorAll('#sp_menu .c-spMenuNav > li, .p-spMenu__body .c-spMenuNav > li')].map((li) =>
				li.querySelector(':scope > a')?.textContent?.trim()
			).filter(Boolean)
		);
	}

	console.log(JSON.stringify({ w, pcTop: pc.map((x) => x.t), services: pc.find((x) => x.t?.includes('サービス'))?.children, voices: pc.find((x) => x.t?.includes('実績'))?.children, sp }));
	await page.close();
}

await ctx.close();
await browser.close();
