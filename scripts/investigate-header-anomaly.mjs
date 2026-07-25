#!/usr/bin/env node
/**
 * Investigate header anomaly on /swell-page/ — logged-in admin view
 */
import { chromium } from 'playwright';
import { execSync } from 'child_process';
import { writeFileSync, mkdirSync } from 'fs';
import { join } from 'path';

const BASE = process.env.PEGA_URL || 'http://pegasus:4CkYn2kHCoO5vh@220.158.21.114:8080';
const OUT = join(process.cwd(), 'docs', 'header-anomaly-investigate');
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

async function analyze(page, label) {
	return page.evaluate((label) => {
		const pick = (sel) => [...document.querySelectorAll(sel)];
		const headers = pick('header, #header, .l-header, .l-fixHeader, [class*="header"]').map((el) => ({
			tag: el.tagName,
			id: el.id || null,
			cls: (el.className || '').toString().slice(0, 120),
			rect: (() => {
				const r = el.getBoundingClientRect();
				return { y: Math.round(r.y), h: Math.round(r.height), w: Math.round(r.width) };
			})(),
		}));

		const adminBar = document.getElementById('wpadminbar');
		const adminBarLinks = adminBar
			? [...adminBar.querySelectorAll('a')].slice(0, 8).map((a) => a.textContent.trim())
			: [];

		// Unstyled link clusters (not inside header/nav/admin bar)
		const suspicious = [];
		for (const a of document.querySelectorAll('a')) {
			const t = a.textContent.trim();
			if (!t) continue;
			if (
				/WordPress\.org|Documentation|Learn WordPress|SWELL Settings|Cache clear|Howdy|Log Out|Edit Page|Blog Parts|Ad Tags|List of icons|アクティベート/i.test(
					t
				)
			) {
				const chain = [];
				let n = a;
				for (let i = 0; i < 6 && n; i++) {
					chain.push(`${n.tagName}${n.id ? '#' + n.id : ''}.${(n.className || '').toString().slice(0, 60)}`);
					n = n.parentElement;
				}
				suspicious.push({ text: t.slice(0, 60), chain });
			}
		}

		const navMenus = pick('nav, .l-header__nav, .p-globalNav, .c-gnav').map((el) => ({
			cls: (el.className || '').toString().slice(0, 80),
			linkCount: el.querySelectorAll('a').length,
			firstLinks: [...el.querySelectorAll('a')]
				.slice(0, 4)
				.map((a) => a.textContent.trim()),
			rect: (() => {
				const r = el.getBoundingClientRect();
				return { y: Math.round(r.y), h: Math.round(r.height) };
			})(),
		}));

		const bodyTop = document.body.innerHTML.slice(0, 2500);

		return {
			label,
			url: location.href,
			bodyClasses: document.body.className,
			bodyWrapClasses: document.querySelector('#body_wrap')?.className?.slice(0, 200) || null,
			adminBarPresent: !!adminBar,
			adminBarDisplay: adminBar ? getComputedStyle(adminBar).display : null,
			adminBarPosition: adminBar ? getComputedStyle(adminBar).position : null,
			adminBarRect: adminBar
				? (() => {
						const r = adminBar.getBoundingClientRect();
						return { y: Math.round(r.y), h: Math.round(r.height), w: Math.round(r.width) };
					})()
				: null,
			adminBarLinkSample: adminBarLinks,
			headerLikeCount: headers.filter((h) => h.rect.h > 20).length,
			headers: headers.filter((h) => h.rect.h > 20).slice(0, 12),
			navMenus,
			suspiciousLinkCount: suspicious.length,
			suspiciousLinks: suspicious.slice(0, 20),
			hasAdminBarCss: [...document.querySelectorAll('link[rel=stylesheet]')].some((l) =>
				l.href.includes('admin-bar')
			),
			hasAdminBarInline: !!document.querySelector('#admin-bar-inline-css'),
			bodyTopSnippet: bodyTop.replace(/\s+/g, ' ').slice(0, 500),
		};
	}, label);
}

const { auth, logged_in, hash } = getWpCookies();
const browser = await chromium.launch();

for (const { w, label } of [
	{ w: 1440, label: 'pc-logged-in' },
	{ w: 425, label: 'sp-logged-in' },
]) {
	const context = await browser.newContext({
		viewport: { width: w, height: 900 },
		httpCredentials: { username: 'pegasus', password: 'pegasus' },
	});
	await context.addCookies([
		{ name: `wordpress_${hash}`, value: auth, domain: '220.158.21.114', path: '/' },
		{ name: `wordpress_logged_in_${hash}`, value: logged_in, domain: '220.158.21.114', path: '/' },
	]);
	const page = await context.newPage();
	await page.goto(`${BASE}/swell-page/`, { waitUntil: 'networkidle', timeout: 90000 });
	const data = await analyze(page, label);
	writeFileSync(join(OUT, `${label}.json`), JSON.stringify(data, null, 2));
	await page.screenshot({ path: join(OUT, `${label}.png`), fullPage: false });
	console.log(`\n=== ${label} (${w}px) ===`);
	console.log(JSON.stringify(data, null, 2));
	await context.close();
}

// Logged-out comparison
{
	const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });
	await page.goto(`${BASE}/swell-page/`, { waitUntil: 'networkidle', timeout: 90000 });
	const data = await analyze(page, 'pc-logged-out');
	writeFileSync(join(OUT, 'pc-logged-out.json'), JSON.stringify(data, null, 2));
	console.log(`\n=== pc-logged-out ===`);
	console.log(JSON.stringify(data, null, 2));
	await page.close();
}

await browser.close();
