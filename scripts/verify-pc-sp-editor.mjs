#!/usr/bin/env node
/**
 * Verify SWELL device control in block editor (post 4941) + other pages unaffected
 */
import { chromium } from 'playwright';
import { execSync } from 'child_process';
import { mkdirSync } from 'fs';
import { join } from 'path';

const BASE = process.env.PEGA_URL || 'http://pegasus:4CkYn2kHCoO5vh@220.158.21.114:8080';
const OUT = process.env.PEGA_OUT || join(process.cwd(), 'docs', 'pc-sp-verify');
const BASE_HOST = new URL(BASE).host;

mkdirSync(OUT, { recursive: true });

function getWpCookies() {
	const raw = execSync(
		`docker exec pegasus_wp wp --allow-root eval '
$user_id = 1;
$expiration = time() + 3600;
$manager = WP_Session_Tokens::get_instance($user_id);
$token = $manager->create($expiration);
echo json_encode([
  "auth" => wp_generate_auth_cookie($user_id, $expiration, "auth", $token),
  "logged_in" => wp_generate_auth_cookie($user_id, $expiration, "logged_in", $token),
  "hash" => COOKIEHASH,
]);
'`,
		{ encoding: 'utf8' }
	).trim();
	return JSON.parse(raw);
}

async function injectWpLogin(context) {
	const { auth, logged_in, hash } = getWpCookies();
	const baseUrl = BASE.replace(/\/\/[^@]+@/, '//');
	const url = new URL(baseUrl);
	await context.addCookies([
		{
			name: `wordpress_${hash}`,
			value: auth,
			domain: url.hostname,
			path: '/',
		},
		{
			name: `wordpress_logged_in_${hash}`,
			value: logged_in,
			domain: url.hostname,
			path: '/',
		},
	]);
}

async function editorDeviceCheck(page) {
	return page.evaluate(() => {
		const doc = document.querySelector('[name="editor-canvas"]')?.contentDocument || document;
		const root = doc.querySelector('.editor-styles-wrapper') || doc.body;
		const badge = (text) => [...root.querySelectorAll('*')].some((el) => {
			const before = getComputedStyle(el, '::before').content;
			return before && before.includes(text);
		});
		const opacity = (sel) => {
			const el = root.querySelector(sel);
			if (!el) return null;
			return parseFloat(getComputedStyle(el).opacity);
		};
		const visible = (sel) => {
			const el = root.querySelector(sel);
			if (!el) return false;
			const st = getComputedStyle(el);
			return st.display !== 'none' && el.getBoundingClientRect().height > 0;
		};
		return {
			pcBadge: badge('PCのみ'),
			spBadge: badge('SPのみ'),
			achPc: opacity('.achivement.pc_only'),
			achSp: opacity('.mobile-img.sp_only'),
			achPcVisible: visible('.achivement.pc_only'),
			achSpVisible: visible('.mobile-img.sp_only'),
		};
	});
}

async function main() {
	const browser = await chromium.launch();
	const ctx = await browser.newContext({ ignoreHTTPSErrors: true });
	await injectWpLogin(ctx);
	const page = await ctx.newPage();

	// Other pages: no pc_only/sp_only leakage issues
	for (const path of ['/', '/about/']) {
		await page.setViewportSize({ width: 1440, height: 900 });
		await page.goto(`${BASE}${path}`, { waitUntil: 'networkidle', timeout: 60000 });
		await page.waitForTimeout(500);
		const slug = path === '/' ? 'home' : 'about';
		await page.screenshot({ path: join(OUT, `other-${slug}-1440.png`), fullPage: false });
		const leak = await page.evaluate(() => ({
			pcOnly: document.querySelectorAll('.pc_only').length,
			spOnly: document.querySelectorAll('.sp_only').length,
		}));
		console.log(`other page ${path}:`, leak);
	}

	// Editor desktop
	await page.setViewportSize({ width: 1440, height: 900 });
	await page.goto(`${BASE}/wp-admin/post.php?post=4941&action=edit`, {
		waitUntil: 'domcontentloaded',
		timeout: 90000,
	});
	await page.waitForTimeout(5000);
	if (!page.url().includes('post.php')) {
		console.error('Editor login failed:', page.url());
		await browser.close();
		process.exit(1);
	}
	const desktopCheck = await editorDeviceCheck(page);
	console.log('editor desktop:', desktopCheck);
	await page.screenshot({ path: join(OUT, 'editor-4941-desktop.png'), fullPage: false });

	// Editor mobile viewport
	await page.setViewportSize({ width: 375, height: 812 });
	await page.waitForTimeout(2000);
	const mobileCheck = await editorDeviceCheck(page);
	console.log('editor mobile:', mobileCheck);
	await page.screenshot({ path: join(OUT, 'editor-4941-mobile.png'), fullPage: false });

	// Save post (JSON integrity)
	const saveBtn = page.locator('button.editor-post-publish-button, button.editor-post-save-draft').first();
	if (await saveBtn.count()) {
		// Use update/save if pattern allows
		const updateBtn = page.locator('button:has-text("更新"), button:has-text("保存")').first();
		if (await updateBtn.count()) {
			await updateBtn.click();
			await page.waitForTimeout(3000);
		}
	}
	const jsonErr = await page.locator('.components-notice.is-error').count();
	console.log('editor JSON errors:', jsonErr);
	if (jsonErr > 0) {
		const msg = await page.locator('.components-notice.is-error').first().textContent();
		console.error('ERROR notice:', msg);
	}

	let ok = true;
	if (!desktopCheck.achPcVisible || !desktopCheck.achSpVisible) {
		console.error('FAIL editor: blocks not visible');
		ok = false;
	}
	if (desktopCheck.achPc != null && desktopCheck.achSp != null) {
		if (desktopCheck.achSp < desktopCheck.achPc) {
			console.log('OK desktop: SP side dimmer than PC side');
		}
	}
	if (mobileCheck.achPc != null && mobileCheck.achSp != null && mobileCheck.achPc < mobileCheck.achSp) {
		console.log('OK mobile: PC side dimmer than SP side');
	} else if (mobileCheck.achPc != null && mobileCheck.achSp != null) {
		console.error('FAIL mobile dimming', mobileCheck);
		ok = false;
	}
	if (jsonErr > 0) ok = false;

	await browser.close();
	process.exit(ok ? 0 : 1);
}

main().catch((e) => {
	console.error(e);
	process.exit(1);
});
