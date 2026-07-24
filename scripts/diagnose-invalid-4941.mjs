#!/usr/bin/env node
import { chromium } from 'playwright';
import { execSync } from 'child_process';

const BASE = process.env.PEGA_URL || 'http://pegasus:4CkYn2kHCoO5vh@220.158.21.114:8080';

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
	const { auth, logged_in, hash } = getWpCookies();
	const url = new URL(BASE.replace(/\/\/[^@]+@/, '//'));
	const browser = await chromium.launch();
	const ctx = await browser.newContext();
	await ctx.addCookies([
		{ name: `wordpress_${hash}`, value: auth, domain: url.hostname, path: '/' },
		{ name: `wordpress_logged_in_${hash}`, value: logged_in, domain: url.hostname, path: '/' },
	]);
	const page = await ctx.newPage();
	await page.goto(`${BASE}/wp-admin/post.php?post=4941&action=edit`, { waitUntil: 'domcontentloaded', timeout: 90000 });
	await page.waitForTimeout(7000);

	const invalid = await page.evaluate(() => {
		const out = [];
		const walk = (list, path = '') => {
			for (const b of list) {
				const cls = b.attributes?.className || '';
				const meta = b.attributes?.metadata?.name || '';
				const ref = b.attributes?.ref || null;
				const cur = path
					? `${path} > ${b.name}${cls ? '.' + cls.replace(/ /g, '.') : ''}`
					: `${b.name}${cls ? '.' + cls.replace(/ /g, '.') : ''}`;
				if (b.isValid === false) {
					const attrIssue = (b.validationIssues || []).find((v) => v.args?.[0]?.includes('Expected attribute'));
					out.push({
						path: cur,
						meta,
						ref,
						issues: (b.validationIssues || []).length,
						attrMismatch: attrIssue
							? { attr: attrIssue.args[1], expected: attrIssue.args[2], actual: attrIssue.args[3] }
							: null,
					});
				}
				if (b.innerBlocks?.length) walk(b.innerBlocks, cur);
			}
		};
		walk(window.wp.data.select('core/block-editor').getBlocks());
		return out;
	});

	console.log(JSON.stringify(invalid, null, 2));
	await browser.close();
}

main().catch((e) => { console.error(e); process.exit(1); });
