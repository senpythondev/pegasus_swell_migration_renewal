#!/usr/bin/env node
/**
 * FV・実績バー inline 除去の before/after 比較 + エディタ検証
 */
import { chromium } from 'playwright';
import { execSync } from 'child_process';
import { mkdirSync, readFileSync, existsSync } from 'fs';
import { join } from 'path';
import { createHash } from 'crypto';

const BASE = process.env.PEGA_URL || 'http://pegasus:4CkYn2kHCoO5vh@220.158.21.114:8080';
const OUT = join(process.cwd(), 'docs', 'inline-strip-verify');
const WIDTHS = [1440, 1280, 1024, 961, 768, 425, 375];
const phase = process.argv[2] || 'after';

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

async function clipFvStats(page, outPath) {
	const fv = await page.$('.main-text');
	const ach = await page.$('.achivement');
	if (!fv || !ach) return false;
	const fvR = await fv.boundingBox();
	const achR = await ach.boundingBox();
	if (!fvR || !achR) return false;
	const y = Math.max(0, fvR.y - 12);
	const h = Math.min(900 - y, achR.y + achR.height - y + 24);
	await page.screenshot({
		path: outPath,
		clip: { x: 0, y, width: page.viewportSize().width, height: h },
	});
	return true;
}

function md5(path) {
	return createHash('md5').update(readFileSync(path)).digest('hex');
}

const browser = await chromium.launch();
const hashes = {};

for (const w of WIDTHS) {
	const page = await browser.newPage({ viewport: { width: w, height: 900 } });
	await page.goto(`${BASE}/swell-page/?v=${Date.now()}`, { waitUntil: 'networkidle', timeout: 90000 });
	const outPath = join(OUT, `${phase}-${w}.png`);
	await clipFvStats(page, outPath);
	if (existsSync(outPath)) hashes[w] = md5(outPath);
	await page.close();
}

if (phase === 'after') {
	const diffs = [];
	for (const w of WIDTHS) {
		const before = join(OUT, `before-${w}.png`);
		const after = join(OUT, `after-${w}.png`);
		if (existsSync(before) && existsSync(after)) {
			const same = md5(before) === md5(after);
			console.log(JSON.stringify({ w, match: same, before: md5(before).slice(0, 8), after: md5(after).slice(0, 8) }));
			if (!same) diffs.push(w);
		}
	}
	if (diffs.length) {
		console.error('VISUAL DIFF at widths:', diffs.join(', '));
	}
}

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

const device = await ep.evaluate(() => ({
	pcOnlyJson: document.body.innerText.includes('pc_only'),
	spOnlyJson: document.body.innerText.includes('sp_only'),
}));
console.log('device-classes-in-editor-source', JSON.stringify(device));

await ctx.close();
await browser.close();
