#!/usr/bin/env node
/**
 * Verify list view section names on post 4941 editor
 */
import { chromium } from 'playwright';
import { execSync } from 'child_process';
import { mkdirSync } from 'fs';
import { join } from 'path';

const BASE = process.env.PEGA_URL || 'http://pegasus:4CkYn2kHCoO5vh@220.158.21.114:8080';
const OUT = process.env.PEGA_OUT || join(process.cwd(), 'docs', 'listview-names-verify');
mkdirSync(OUT, { recursive: true });

function getWpCookies() {
	const raw = execSync(
		`docker exec pegasus_wp wp --allow-root eval '
$user_id = 1; $expiration = time() + 3600;
$manager = WP_Session_Tokens::get_instance($user_id);
$token = $manager->create($expiration);
echo json_encode(["auth"=>wp_generate_auth_cookie($user_id,$expiration,"auth",$token),"logged_in"=>wp_generate_auth_cookie($user_id,$expiration,"logged_in",$token),"hash"=>COOKIEHASH]);
'`,
		{ encoding: 'utf8' }
	).trim();
	return JSON.parse(raw);
}

async function main() {
	const browser = await chromium.launch();
	const ctx = await browser.newContext({ ignoreHTTPSErrors: true });
	await ctx.addCookies((() => {
		const { auth, logged_in, hash } = getWpCookies();
		const url = new URL(BASE.replace(/\/\/[^@]+@/, '//'));
		return [
			{ name: `wordpress_${hash}`, value: auth, domain: url.hostname, path: '/' },
			{ name: `wordpress_logged_in_${hash}`, value: logged_in, domain: url.hostname, path: '/' },
		];
	})());
	const page = await ctx.newPage();

	await page.goto(`${BASE}/swell-page/`, { waitUntil: 'networkidle' });
	const pubBefore = await page.screenshot({ path: join(OUT, 'public-before.png'), fullPage: false });

	await page.goto(`${BASE}/wp-admin/post.php?post=4941&action=edit`, { waitUntil: 'domcontentloaded', timeout: 90000 });
	await page.waitForTimeout(5000);

	const listBtn = page.locator('button[aria-label="List view"], button[aria-label="リストビュー"]');
	if (await listBtn.count()) {
		await listBtn.first().click();
		await page.waitForTimeout(1500);
	}
	await page.screenshot({ path: join(OUT, 'editor-listview-4941.png'), fullPage: false });

	const names = await page.evaluate(() => {
		const items = [...document.querySelectorAll('.block-editor-list-view-tree .block-editor-list-view-block__title, .block-editor-list-view-block__menu-item .block-editor-list-view-block__title')];
		return items.map((el) => el.textContent?.trim()).filter(Boolean);
	});
	console.log('list view names:', names.filter((n) => ['ヘッダー', '在宅支援', '波型装飾', '画像', 'ペガサス ヘッダー', 'ペガサス サービス紹介セクション'].some((k) => n.includes(k) || k.includes(n))));

	const updateBtn = page.locator('button:has-text("更新"), button:has-text("保存")').first();
	if (await updateBtn.count()) {
		await updateBtn.click();
		await page.waitForTimeout(3000);
	}
	const jsonErr = await page.locator('.components-notice.is-error').count();
	console.log('JSON errors:', jsonErr);

	await page.goto(`${BASE}/swell-page/`, { waitUntil: 'networkidle' });
	await page.screenshot({ path: join(OUT, 'public-after.png'), fullPage: false });

	await browser.close();

	const required = ['ヘッダー', '在宅支援', '波型装飾'];
	const ok = jsonErr === 0 && required.every((r) => names.some((n) => n === r || n.includes(r)));
	const bad = ['画像', 'ペガサス ヘッダー', 'ペガサス サービス紹介セクション'].filter((b) => names.some((n) => n === b));
	if (bad.length) {
		console.error('unexpected names still present:', bad);
		process.exit(1);
	}
	process.exit(ok ? 0 : 1);
}

main().catch((e) => { console.error(e); process.exit(1); });
