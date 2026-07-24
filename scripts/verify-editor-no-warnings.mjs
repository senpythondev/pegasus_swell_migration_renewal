#!/usr/bin/env node
import { chromium } from 'playwright';
import { execSync } from 'child_process';

const BASE = process.env.PEGA_URL || 'http://pegasus:4CkYn2kHCoO5vh@220.158.21.114:8080';

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

const { auth, logged_in, hash } = getWpCookies();
const browser = await chromium.launch();
const context = await browser.newContext({
	httpCredentials: { username: 'pegasus', password: 'pegasus' },
});
await context.addCookies([
	{ name: `wordpress_${hash}`, value: auth, domain: '220.158.21.114', path: '/' },
	{ name: `wordpress_logged_in_${hash}`, value: logged_in, domain: '220.158.21.114', path: '/' },
]);

for (const id of [4941, 5008]) {
	const page = await context.newPage();
	await page.goto(`${BASE}/wp-admin/post.php?post=${id}&action=edit`, {
		waitUntil: 'domcontentloaded',
		timeout: 90000,
	});
	await page.waitForTimeout(8000);
	const r = await page.evaluate(() => ({
		url: location.href,
		invalidBlocks: [...document.querySelectorAll('.block-editor-warning')]
			.map((el) => el.textContent.trim())
			.slice(0, 5),
		notices: [...document.querySelectorAll('.components-notice__content')]
			.map((el) => el.textContent.trim())
			.filter((t) => /unexpected|invalid|復元|エラー/i.test(t))
			.slice(0, 5),
	}));
	console.log(JSON.stringify({ postId: id, ...r }));
	await page.close();
}

await browser.close();
