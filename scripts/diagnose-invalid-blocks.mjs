#!/usr/bin/env node
/**
 * Diagnose invalid block warnings in editor (4941 / nested 5008 / 5210)
 * Does NOT click Attempt recovery.
 */
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

async function injectLogin(ctx) {
	const { auth, logged_in, hash } = getWpCookies();
	const url = new URL(BASE.replace(/\/\/[^@]+@/, '//'));
	await ctx.addCookies([
		{ name: `wordpress_${hash}`, value: auth, domain: url.hostname, path: '/' },
		{ name: `wordpress_logged_in_${hash}`, value: logged_in, domain: url.hostname, path: '/' },
	]);
}

async function diagnosePost(page, postId, label) {
	await page.goto(`${BASE}/wp-admin/post.php?post=${postId}&action=edit`, {
		waitUntil: 'domcontentloaded',
		timeout: 90000,
	});
	await page.waitForTimeout(6000);

	const data = await page.evaluate(() => {
		const wp = window.wp;
		const editor = wp?.data?.select('core/block-editor');
		const blocks = editor?.getBlocks?.() || [];
		const invalid = [];

		function walk(list, path = '') {
			for (const b of list) {
				const name = b.name || '(empty)';
				const cls = b.attributes?.className || '';
				const meta = b.attributes?.metadata?.name || '';
				const cur = path ? `${path} > ${name}${cls ? '.' + cls.replace(/ /g, '.') : ''}` : `${name}${cls ? '.' + cls.replace(/ /g, '.') : ''}`;
				const validation = b.validationIssues || [];
				const isValid = typeof b.isValid === 'function' ? b.isValid() : b.isValid;
				const clientId = b.clientId;

				if (validation.length || isValid === false) {
					const issues = validation.map((v) => ({
						type: v.type,
						message: v.message,
						args: v.args,
						html: v.__unstableHtml || v.html,
					}));
					invalid.push({
						clientId,
						path: cur,
						meta,
						blockName: name,
						isValid,
						validationIssues: issues,
					});
				}
				if (b.innerBlocks?.length) walk(b.innerBlocks, cur);
			}
		}
		walk(blocks);

		// DOM-level warnings (fallback)
		const domWarnings = [...document.querySelectorAll('.block-editor-block-list__block')].flatMap((el) => {
			const warn = el.querySelector('.block-editor-warning, .components-notice.is-warning, [class*="block-validation"]');
			const text = warn?.textContent?.trim() || '';
			if (!text && !el.classList.contains('is-warning') && !el.classList.contains('has-warning')) return [];
			const title = el.querySelector('.block-editor-list-view-block__title, [data-block]')?.getAttribute('data-block') || '';
			return [{
				domText: text.slice(0, 200),
				classes: el.className.slice(0, 120),
				dataBlock: title,
			}];
		});

		return {
			totalBlocks: blocks.length,
			invalidCount: invalid.length,
			invalid,
			domWarnings: domWarnings.slice(0, 20),
		};
	});

	console.log(`\n=== POST ${postId} (${label}) ===`);
	console.log(JSON.stringify(data, null, 2));
	return data;
}

async function main() {
	const browser = await chromium.launch();
	const ctx = await browser.newContext({ ignoreHTTPSErrors: true });
	await injectLogin(ctx);
	const page = await ctx.newPage();

	await diagnosePost(page, 5008, '5008 サービス紹介');
	await diagnosePost(page, 5210, '5210 ヘッダー');
	await diagnosePost(page, 4941, '4941 完全版');

	await browser.close();
}

main().catch((e) => {
	console.error(e);
	process.exit(1);
});
