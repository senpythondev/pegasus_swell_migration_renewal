#!/usr/bin/env node
/**
 * Verify SP stats bar overlap + readability on /swell-page/
 */
import { chromium } from 'playwright';
import { execSync } from 'child_process';
import { mkdirSync } from 'fs';
import { join } from 'path';

const BASE = process.env.PEGA_URL || 'http://pegasus:4CkYn2kHCoO5vh@220.158.21.114:8080';
const OUT = join(process.cwd(), 'docs', 'sp-stats-overlap-verify');
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

async function measure(page) {
	return page.evaluate(() => {
		const fv = document.querySelector('.main-text');
		const h1 = document.querySelector('.main-text h1');
		const btns = document.querySelector('.main-text .wp-block-buttons');
		const ach = document.querySelector('.achivement');
		const cols = [...document.querySelectorAll('.achivement .box-achievement')];

		function rect(el) {
			if (!el) return null;
			const r = el.getBoundingClientRect();
			return { x: Math.round(r.x), y: Math.round(r.y), w: Math.round(r.width), h: Math.round(r.height), b: Math.round(r.bottom) };
		}

		function overlap(a, b) {
			if (!a || !b) return 0;
			const top = Math.max(a.y, b.y);
			const bottom = Math.min(a.b, b.b);
			return Math.max(0, bottom - top);
		}

		const achR = rect(ach);
		const h1R = rect(h1);
		const fvR = rect(fv);
		const btnR = rect(btns);

		const colRects = cols.map(rect);
		const colHeights = colRects.map((r) => r?.h);
		const colHeightSpread = colHeights.length ? Math.max(...colHeights) - Math.min(...colHeights) : 0;

		return {
			w: window.innerWidth,
			achMT: ach ? getComputedStyle(ach).marginTop : null,
			achZ: ach ? getComputedStyle(ach).zIndex : null,
			fvR,
			h1R,
			btnR,
			achR,
			gapFvAch: achR && fvR ? achR.y - fvR.b : null,
			overlapFvAch: overlap(fvR, achR),
			overlapH1Ach: overlap(h1R, achR),
			overlapBtnAch: overlap(btnR, achR),
			h1Visible:
				h1 &&
				getComputedStyle(h1).visibility !== 'hidden' &&
				getComputedStyle(h1).opacity !== '0' &&
				h1R &&
				h1R.h > 0,
			colHeights,
			colHeightSpread,
			readabilityOk:
				overlap(h1R, achR) === 0 &&
				(overlap(btnR, achR) < 20 || !btnR) &&
				colHeightSpread <= 8,
		};
	});
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
	const m = await measure(page);
	results.push(m);
	await clipScreenshot(page, join(OUT, `overlap-${w}.png`));
	console.log(JSON.stringify(m));
	await page.close();
}

// Editor check
const { auth, logged_in, hash } = getWpCookies();
const ctx = await browser.newContext({ httpCredentials: { username: 'pegasus', password: 'pegasus' } });
await ctx.addCookies([
	{ name: `wordpress_${hash}`, value: auth, domain: '220.158.21.114', path: '/' },
	{ name: `wordpress_logged_in_${hash}`, value: logged_in, domain: '220.158.21.114', path: '/' },
]);
const ep = await ctx.newPage();
await ep.goto(`${BASE}/wp-admin/post.php?post=4941&action=edit`, { waitUntil: 'domcontentloaded', timeout: 90000 });
await ep.waitForTimeout(8000);
const editor = await ep.evaluate(() => ({
	invalidBlocks: [...document.querySelectorAll('.block-editor-warning')].map((el) => el.textContent.trim()),
	notices: [...document.querySelectorAll('.components-notice__content')]
		.map((el) => el.textContent.trim())
		.filter((t) => /unexpected|invalid|復元|エラー/i.test(t)),
}));
console.log('editor', JSON.stringify(editor));
await ctx.close();
await browser.close();

const pc = results.find((r) => r.w === 1440);
const spOk = results.filter((r) => r.w <= 960).every((r) => r.overlapFvAch > 40 && r.overlapH1Ach === 0);
process.exit(pc?.achMT === '-64px' && spOk && editor.invalidBlocks.length === 0 ? 0 : 1);
