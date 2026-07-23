#!/usr/bin/env node
/**
 * Work 1 verification: swell-page public + editor list view screenshots
 */
import { chromium } from 'playwright';
import { mkdirSync } from 'fs';
import { join } from 'path';

const BASE = process.env.PEGA_URL || 'http://pegasus:4CkYn2kHCoO5vh@220.158.21.114:8080';
const OUT = process.env.PEGA_OUT || join(process.cwd(), 'docs', 'work1-verify');
const WIDTHS = [1440, 1024, 768, 425];
const WP_USER = process.env.WP_USER || 'pegasus';
const WP_APP_PASS = process.env.WP_APP_PASS || '';

mkdirSync(OUT, { recursive: true });

async function shot(page, name) {
	const path = join(OUT, name);
	await page.screenshot({ path, fullPage: true });
	console.log('saved', path);
}

async function main() {
	const browser = await chromium.launch();
	const ctx = await browser.newContext({ ignoreHTTPSErrors: true });
	const page = await ctx.newPage();

	for (const w of WIDTHS) {
		await page.setViewportSize({ width: w, height: 900 });
		await page.goto(`${BASE}/swell-page/`, { waitUntil: 'networkidle', timeout: 60000 });
		await page.waitForTimeout(800);
		await shot(page, `public-${w}.png`);
	}

	if (WP_APP_PASS) {
		await page.goto(`${BASE}/wp-login.php`, { waitUntil: 'domcontentloaded' });
		await page.fill('#user_login', WP_USER);
		await page.fill('#user_pass', WP_APP_PASS);
		await page.click('#wp-submit');
		await page.waitForTimeout(5000);
		const adminOk = page.url().includes('wp-admin');
		if (!adminOk) {
			console.log('WP login skipped (app password may not work on login form):', page.url());
		} else {

		await page.setViewportSize({ width: 1440, height: 900 });
		await page.goto(`${BASE}/wp-admin/post.php?post=4941&action=edit`, {
			waitUntil: 'domcontentloaded',
			timeout: 60000,
		});
		await page.waitForTimeout(3000);

		// Switch to list view if available
		const listBtn = page.locator('button[aria-label="List view"], button[aria-label="リストビュー"]');
		if (await listBtn.count()) {
			await listBtn.first().click();
			await page.waitForTimeout(1000);
		}
		await shot(page, 'editor-listview-4941.png');

		await page.goto(`${BASE}/wp-admin/post.php?post=5210&action=edit`, {
			waitUntil: 'domcontentloaded',
			timeout: 60000,
		});
		await page.waitForTimeout(2000);
		if (await listBtn.count()) await listBtn.first().click();
		await page.waitForTimeout(1000);
		await shot(page, 'editor-listview-5210.png');

		await page.goto(`${BASE}/wp-admin/post.php?post=5008&action=edit`, {
			waitUntil: 'domcontentloaded',
			timeout: 60000,
		});
		await page.waitForTimeout(2000);
		if (await listBtn.count()) await listBtn.first().click();
		await page.waitForTimeout(1000);
		await shot(page, 'editor-listview-5008.png');
		}
	} else {
		console.log('Skip editor screenshots: set WP_APP_PASS');
	}

	await browser.close();
}

main().catch((e) => {
	console.error(e);
	process.exit(1);
});
