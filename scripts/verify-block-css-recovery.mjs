#!/usr/bin/env node
/**
 * Verify block CSS recovery on swell-page + other pages + editor save
 */
import { chromium } from 'playwright';
import { execSync } from 'child_process';
import { mkdirSync } from 'fs';
import { join } from 'path';

const BASE = process.env.PEGA_URL || 'http://pegasus:4CkYn2kHCoO5vh@220.158.21.114:8080';
const OUT = process.env.PEGA_OUT || join(process.cwd(), 'docs', 'block-css-recovery');
const WIDTHS = [1440, 1024, 768, 425];

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

async function main() {
	const browser = await chromium.launch();
	const ctx = await browser.newContext({ ignoreHTTPSErrors: true });
	const page = await ctx.newPage();

	for (const w of WIDTHS) {
		await page.setViewportSize({ width: w, height: 900 });
		await page.goto(`${BASE}/swell-page/`, { waitUntil: 'networkidle', timeout: 60000 });
		await page.waitForTimeout(600);
		await page.screenshot({ path: join(OUT, `swell-page-${w}.png`), fullPage: true });
		console.log('saved swell-page-' + w + '.png');
	}

	const styles = await page.evaluate(() => {
		const q = (s) => document.querySelector(s);
		const cs = (el) => (el ? getComputedStyle(el) : null);
		return {
			fvBadge: cs(q('.page-id-4929 .main-text .wp-block-group.u-mb-0 > .wp-block-paragraph.u-mb-10')),
			stats: cs(q('.page-id-4929 .pega-stats')),
			achCols: cs(q('.page-id-4929 .pega-stats .achivement > .wp-block-columns')),
			wave: cs(q('.page-id-4929 .wp-image-5098')),
			intro: cs(q('.page-id-4929 .pega-service-intro')),
			badge: cs(q('.page-id-4929 .pega-service-intro .pega-badge')),
			supports: cs(q('.page-id-4929 .pega-service-intro .supports')),
		};
	});
	console.log('1440 styles sample:', JSON.stringify({
		fvBadgeWidth: styles.fvBadge?.width,
		statsPadding: styles.stats?.padding,
		achColsDisplay: styles.achCols?.display,
		waveMargin: styles.wave?.margin,
		introPadding: styles.intro?.padding,
		badgeDisplay: styles.badge?.display,
		supportsMarginTop: styles.supports?.marginTop,
	}, null, 2));

	for (const path of ['/', '/about/']) {
		await page.goto(`${BASE}${path}`, { waitUntil: 'networkidle' });
		const leak = await page.evaluate(() => ({
			statsPad: getComputedStyle(document.querySelector('.pega-stats') || document.body).padding,
			introPad: getComputedStyle(document.querySelector('.pega-service-intro') || document.body).padding,
		}));
		console.log(`other ${path}:`, leak);
	}

	await injectWpLogin(ctx);
	await page.goto(`${BASE}/wp-admin/post.php?post=4941&action=edit`, { waitUntil: 'domcontentloaded', timeout: 90000 });
	await page.waitForTimeout(5000);
	const updateBtn = page.locator('button:has-text("更新"), button:has-text("保存")').first();
	if (await updateBtn.count()) {
		await updateBtn.click();
		await page.waitForTimeout(3000);
	}
	const jsonErr = await page.locator('.components-notice.is-error').count();
	console.log('editor JSON errors:', jsonErr);
	await page.screenshot({ path: join(OUT, 'editor-4941-after-save.png'), fullPage: false });

	await browser.close();
	process.exit(jsonErr > 0 ? 1 : 0);
}

main().catch((e) => { console.error(e); process.exit(1); });
