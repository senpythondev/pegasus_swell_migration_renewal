#!/usr/bin/env node
/**
 * 悩み共感 intro アニメーション検証（実スクロール + computed style）
 */
import { chromium } from 'playwright';

const URL = process.env.PEGA_URL || 'http://pegasus:4CkYn2kHCoO5vh@220.158.21.114:8080/';
const WIDTH = Number(process.env.PEGA_WIDTH || 1440);

async function sample(el) {
	if (!el) return null;
	return el.evaluate((node) => {
		const cs = getComputedStyle(node);
		const r = node.getBoundingClientRect();
		return {
			className: node.className,
			opacity: cs.opacity,
			transform: cs.transform,
			transition: cs.transitionProperty,
			top: Math.round(r.top),
			visible: r.top < window.innerHeight && r.bottom > 0,
		};
	});
}

async function run(reducedMotion = false) {
	const browser = await chromium.launch({ headless: true });
	const context = await browser.newContext({
		viewport: { width: WIDTH, height: 900 },
		reducedMotion: reducedMotion ? 'reduce' : 'no-preference',
	});
	const page = await context.newPage();

	const logs = [];
	page.on('pageerror', (e) => logs.push(`PAGEERROR: ${e.message}`));
	page.on('console', (msg) => {
		if (msg.type() === 'error') logs.push(`CONSOLE: ${msg.text()}`);
	});

	await page.goto(URL, { waitUntil: 'networkidle', timeout: 60000 });

	const intro = page.locator('.pega-concerns__intro.pega-animate').first();
	await intro.waitFor({ state: 'attached', timeout: 15000 });

	const htmlClass = await page.evaluate(() => document.documentElement.className);
	const hasInview = await page.evaluate(() => !!document.querySelector('script[src*="pega-inview"]'));
	const hasMotionCss = await page.evaluate(() => {
		return [...document.styleSheets].some((ss) => {
			try {
				return ss.href && ss.href.includes('code-snippets');
			} catch {
				return false;
			}
		});
	});

	const beforeScroll = await sample(intro);
	await page.evaluate(() => window.scrollTo(0, 0));
	await page.waitForTimeout(300);
	const atTop = await sample(intro);

	// 悩み共感が画面中央付近に入るまでスクロール
	await intro.scrollIntoViewIfNeeded();
	await page.waitForTimeout(100);
	const onApproach = await sample(intro);

	// 発火待ち（transition 0.6s + 余裕）
	await page.waitForTimeout(900);
	const afterVisible = await sample(intro);
	const hasVisibleClass = await intro.evaluate((el) => el.classList.contains('is-visible'));

	// 中間フレーム（transition中）を狙う
	await page.evaluate(() => window.scrollTo(0, 0));
	await page.waitForTimeout(400);
	await intro.scrollIntoViewIfNeeded();
	await page.waitForTimeout(350);
	const midTransition = await sample(intro);

	await browser.close();

	return {
		reducedMotion,
		htmlClass,
		hasInview,
		hasMotionCss,
		hasVisibleClass,
		beforeScroll,
		atTop,
		onApproach,
		midTransition,
		afterVisible,
		logs,
	};
}

const normal = await run(false);
const reduced = await run(true);

console.log(JSON.stringify({ normal, reduced }, null, 2));
