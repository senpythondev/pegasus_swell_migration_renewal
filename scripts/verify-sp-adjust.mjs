#!/usr/bin/env node
/**
 * SP adjustment verification + screenshots
 */
import { chromium } from 'playwright';
import { mkdirSync } from 'fs';
import { join } from 'path';
import { execSync } from 'child_process';

const BASE = process.env.PEGA_URL || 'http://pegasus:4CkYn2kHCoO5vh@220.158.21.114:8080';
const OUT = 'docs/sp-adjust-verify';
const PC_BASELINE = {
	1440: { statsPad: '0px 10px 10px', achPad: '10px 18px 8px', achMT: '-64px', achH: 127, achW: 900, cols: [272, 272, 272] },
	1280: { statsPad: '0px 10px 10px', achPad: '10px 18px 8px', achMT: '-64px', achH: 127, achW: 900, cols: [272, 272, 272] },
	1024: { statsPad: '0px 10px 10px', achPad: '10px 18px 8px', achMT: '-64px', achH: 127, achW: 900, cols: [272, 272, 272] },
	961: { statsPad: '0px 10px 10px', achPad: '10px 18px 8px', achMT: '-64px', achH: 127, achW: 900, cols: [272, 272, 272] },
};
const WIDTHS = [1440, 1280, 1024, 961, 960, 768, 425, 375];

mkdirSync(OUT, { recursive: true });

const browser = await chromium.launch();
const page = await browser.newPage();
const results = [];

for (const w of WIDTHS) {
	await page.setViewportSize({ width: w, height: 900 });
	await page.goto(`${BASE}/swell-page/`, { waitUntil: 'networkidle', timeout: 60000 });

	const data = await page.evaluate(() => {
		const cs = (el) => (el ? getComputedStyle(el) : null);
		const rr = (el) => (el ? el.getBoundingClientRect() : null);
		const ach = document.querySelector('.pega-stats .achivement');
		const stats = document.querySelector('.pega-stats.u-mb-ctrl');
		const h1 = document.querySelector('.main-text h1');
		const cover = document.querySelector('.main-text.wp-block-cover');
		const girl = document.querySelector('.pega-service-intro .girl');
		const cols = [...document.querySelectorAll('.achivement .box-achievement')].map((el) =>
			Math.round(el.getBoundingClientRect().width)
		);
		const girlCols = girl
			? [...girl.querySelectorAll(':scope > .wp-block-column')].map((el) => ({
					w: Math.round(el.getBoundingClientRect().width),
					h: Math.round(el.getBoundingClientRect().height),
					top: Math.round(el.getBoundingClientRect().top),
				}))
			: [];
		const fvBg = cs(cover)?.backgroundImage || '';
		return {
			mobileImg: !!document.querySelector('.mobile-img'),
			mobileGirl: !!document.querySelector('.mobile-girl'),
			achVisible: !!ach && cs(ach).display !== 'none',
			bottomText: document.querySelector('.bottom-text')?.textContent?.trim() || '',
			statsPad: cs(stats)?.padding,
			achPad: cs(ach)?.padding,
			achMT: cs(ach)?.marginTop,
			achH: Math.round(rr(ach)?.height || 0),
			achW: Math.round(rr(ach)?.width || 0),
			cols,
			h1Font: cs(h1)?.fontSize,
			fvBgImage: fvBg.includes('woman-top-sp') ? 'woman-top-sp' : fvBg.includes('FV06') || fvBg === 'none' ? 'pc-cover-img' : fvBg.slice(0, 40),
			coverBefore: cs(cover)?.backgroundImage !== 'none' || !!document.querySelector('.main-text::before'),
			girlVisible: !!girl && cs(girl).display !== 'none',
			girlCols,
			supportsMT: cs(document.querySelector('.pega-service-intro .supports'))?.marginTop,
		};
	});

	const base = PC_BASELINE[w];
	const pcMatch = base
		? data.statsPad === base.statsPad &&
			data.achPad === base.achPad &&
			data.achMT === base.achMT &&
			data.achH === base.achH &&
			data.achW === base.achW &&
			JSON.stringify(data.cols) === JSON.stringify(base.cols)
		: null;

	await page.screenshot({ path: join(OUT, `swell-page-${w}.png`), fullPage: true });
	results.push({ w, pcMatch, ...data });
	console.log(JSON.stringify({ w, pcMatch, mobileImg: data.mobileImg, achVisible: data.achVisible, cols: data.cols, h1Font: data.h1Font, fvBg: data.fvBgImage, girlVisible: data.girlVisible }, null, 0));
}

// editor JSON check
try {
	const raw = execSync(
		`docker exec pegasus_wp wp --allow-root eval '
$user_id=1; $expiration=time()+3600;
$manager=WP_Session_Tokens::get_instance($user_id);
$token=$manager->create($expiration);
echo json_encode(["auth"=>wp_generate_auth_cookie($user_id,$expiration,"auth",$token),"logged_in"=>wp_generate_auth_cookie($user_id,$expiration,"logged_in",$token),"hash"=>COOKIEHASH]);
'`,
		{ encoding: 'utf8' }
	).trim();
	const { auth, logged_in, hash } = JSON.parse(raw);
	const url = new URL(BASE.replace(/\/\/[^@]+@/, '//'));
	await page.context().addCookies([
		{ name: `wordpress_${hash}`, value: auth, domain: url.hostname, path: '/' },
		{ name: `wordpress_logged_in_${hash}`, value: logged_in, domain: url.hostname, path: '/' },
	]);
	await page.goto(`${BASE}/wp-admin/post.php?post=4929&action=edit`, { waitUntil: 'domcontentloaded', timeout: 90000 });
	await page.waitForTimeout(5000);
	const jsonErr = await page.locator('text=JSON').count();
	console.log('editorJsonErrorsApprox:', jsonErr);
} catch (e) {
	console.log('editorCheckSkipped:', e.message);
}

await browser.close();

const pcFails = results.filter((r) => r.pcMatch === false);
if (pcFails.length) {
	console.error('PC REGRESSION:', pcFails.map((r) => r.w));
	process.exit(1);
}
