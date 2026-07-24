#!/usr/bin/env node
import { chromium } from 'playwright';
import { execSync } from 'child_process';
import { mkdirSync } from 'fs';
import { join } from 'path';

const BASE = process.env.PEGA_URL || 'http://pegasus:4CkYn2kHCoO5vh@220.158.21.114:8080';
const OUT = process.env.PEGA_OUT || join(process.cwd(), 'docs', 'homeworker-align-fix');
const WIDTHS = [1440, 1366, 1280, 1025, 1024, 768, 425];

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

async function injectWpLogin(context) {
	const { auth, logged_in, hash } = getWpCookies();
	const url = new URL(BASE.replace(/\/\/[^@]+@/, '//'));
	await context.addCookies([
		{ name: `wordpress_${hash}`, value: auth, domain: url.hostname, path: '/' },
		{ name: `wordpress_logged_in_${hash}`, value: logged_in, domain: url.hostname, path: '/' },
	]);
}

function cs(el) {
	if (!el) return null;
	const s = getComputedStyle(el);
	const r = el.getBoundingClientRect();
	return {
		x: Math.round(r.x),
		w: Math.round(r.width),
		cx: Math.round(r.x + r.width / 2),
		display: s.display,
		width: s.width,
		margin: s.margin,
		left: s.left,
		float: s.float,
		overflow: s.overflow,
	};
}

async function main() {
	const browser = await chromium.launch();
	const ctx = await browser.newContext({ ignoreHTTPSErrors: true });
	const page = await ctx.newPage();

	for (const w of WIDTHS) {
		await page.setViewportSize({ width: w, height: 900 });
		await page.goto(`${BASE}/swell-page/`, { waitUntil: 'networkidle', timeout: 60000 });
		await page.waitForTimeout(500);
		const sel = '.page-id-4929 .pega-service-intro';
		if (await page.locator(sel).count()) {
			await page.locator(sel).screenshot({ path: join(OUT, `intro-${w}.png`) });
		}
		await page.screenshot({ path: join(OUT, `full-${w}.png`), fullPage: true });
	}

	const m = await page.evaluate(() => {
		const q = (s) => document.querySelector(s);
		const cs = (el) => {
			if (!el) return null;
			const s = getComputedStyle(el);
			const r = el.getBoundingClientRect();
			return {
				x: Math.round(r.x), w: Math.round(r.width), cx: Math.round(r.x + r.width / 2),
				display: s.display, width: s.width, margin: s.margin, left: s.left, float: s.float, overflow: s.overflow,
			};
		};
		const inner = q('.page-id-4929 .pega-service-intro .wp-block-group__inner-container');
		const heading = q('.page-id-4929 .pega-service-intro .wp-block-group__inner-container > .wp-block-group.u-mb-10')
			|| q('.page-id-4929 .pega-service-intro .wp-block-group.u-mb-10:not(.has-background)');
		return {
			viewport: window.innerWidth,
			inner: cs(inner),
			heading: cs(heading),
			badge: cs(q('.page-id-4929 .pega-service-intro .pega-badge')),
			h2: cs(q('.page-id-4929 .pega-service-intro .is-style-section_ttl')),
			sub: cs(q('.page-id-4929 .pega-service-intro .sub-heading')),
			blue: cs(q('.page-id-4929 .pega-service-intro .wp-block-group.u-mb-0.has-background')),
			girl: cs(q('.page-id-4929 .pega-service-intro .girl')),
			figure: cs(q('.page-id-4929 .pega-service-intro .girl figure')),
			img: cs(q('.page-id-4929 .pega-service-intro .girl img')),
			supports: cs(q('.page-id-4929 .pega-service-intro .supports')),
		};
	});
	console.log('STEP1_METRICS', JSON.stringify(m, null, 2));

	await injectWpLogin(ctx);
	await page.goto(`${BASE}/wp-admin/post.php?post=4941&action=edit`, { waitUntil: 'domcontentloaded', timeout: 90000 });
	await page.waitForTimeout(5000);
	await page.screenshot({ path: join(OUT, 'editor-4941.png'), fullPage: false });
	const updateBtn = page.locator('button:has-text("更新"), button:has-text("保存")').first();
	if (await updateBtn.count()) {
		await updateBtn.click();
		await page.waitForTimeout(3000);
	}
	const jsonErr = await page.locator('.components-notice.is-error').count();
	console.log('editor JSON errors:', jsonErr);

	await browser.close();
	process.exit(jsonErr > 0 ? 1 : 0);
}

main().catch((e) => { console.error(e); process.exit(1); });
