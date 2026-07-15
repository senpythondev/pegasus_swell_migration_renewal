#!/usr/bin/env node
/**
 * 全セクション pega-animate 検証（スクロール実機相当）
 */
import { chromium } from 'playwright';

const URL = process.env.PEGA_URL || 'http://pegasus:4CkYn2kHCoO5vh@220.158.21.114:8080/';
const WIDTH = Number(process.env.PEGA_WIDTH || 1440);

const TARGETS = [
	{ name: 'concerns-intro', sel: '.pega-concerns__intro.pega-animate', fadeOnly: false },
	{ name: 'bridge-intro', sel: '.pega-bridge__intro.pega-animate', fadeOnly: false },
	{ name: 'homeworker-intro', sel: '.pega-homeworker__intro.pega-animate', fadeOnly: false },
	{ name: 'homeworker-hero', sel: '.pega-homeworker__hero.pega-animate', fadeOnly: false },
	{ name: 'reasons-intro', sel: '.pega-reasons__intro.pega-animate', fadeOnly: false },
	{ name: 'reasons-main', sel: '.pega-reasons__main-wrap.pega-animate', fadeOnly: false },
	{ name: 'commute-banner', sel: '.pega-commute__banner.pega-animate--fade', fadeOnly: true },
	{ name: 'voices-intro', sel: '.pega-voices__intro.pega-animate', fadeOnly: false },
	{ name: 'voices-wrap', sel: '.pega-voices__wrap.pega-animate', fadeOnly: false },
	{ name: 'posts-intro', sel: '.pega-posts__intro.pega-animate', fadeOnly: false },
	{ name: 'posts-wrap', sel: '.pega-posts__wrap.pega-animate', fadeOnly: false },
	{ name: 'closing-panel', sel: '.pega-closing__panel.pega-animate', fadeOnly: false },
];

function readState(node) {
	const cs = getComputedStyle(node);
	const m = cs.transform.match(/matrix\([^,]+,[^,]+,[^,]+,[^,]+,[^,]+,\s*([^)]+)\)/);
	return {
		visible: node.classList.contains('is-visible'),
		opacity: parseFloat(cs.opacity),
		ty: m ? parseFloat(m[1]) : 0,
		transition: cs.transitionDuration,
	};
}

async function verifyTarget(page, target, reducedMotion) {
	const el = page.locator(target.sel).first();
	if (!(await el.count())) {
		return { ...target, ok: false, error: 'not found' };
	}

	await page.evaluate(() => window.scrollTo(0, 0));
	await page.waitForTimeout(300);

	const before = await el.evaluate(readState);

	// 要素の上端をビューポート下寄りに置き、IO を発火させる
	await el.evaluate((node) => {
		const rect = node.getBoundingClientRect();
		const y = window.scrollY + rect.top - window.innerHeight * 0.55;
		window.scrollTo({ top: Math.max(0, y), behavior: 'instant' });
	});
	await page.waitForTimeout(100);

	const triggered = await el.evaluate(readState);

	// アニメーション途中を rAF で捕捉
	const mid = await el.evaluate(async (node) => {
		function snap() {
			const cs = getComputedStyle(node);
			const m = cs.transform.match(/matrix\([^,]+,[^,]+,[^,]+,[^,]+,[^,]+,\s*([^)]+)\)/);
			return {
				visible: node.classList.contains('is-visible'),
				opacity: parseFloat(cs.opacity),
				ty: m ? parseFloat(m[1]) : 0,
				transition: cs.transitionDuration,
			};
		}
		return new Promise((resolve) => {
			let best = snap();
			const start = performance.now();
			function tick() {
				const s = snap();
				if (s.visible && (s.opacity < 1 || (s.ty > 0 && s.ty < 14))) {
					best = s;
				}
				if (performance.now() - start < 400) {
					requestAnimationFrame(tick);
				} else {
					resolve(best);
				}
			}
			requestAnimationFrame(tick);
		});
	});

	await page.waitForTimeout(700);
	const after = await el.evaluate(readState);

	let ok = false;
	if (reducedMotion) {
		ok =
			before.visible &&
			before.opacity === 1 &&
			before.transition === '0s' &&
			after.visible &&
			after.opacity === 1;
	} else {
		const hasTransition = after.transition.includes('0.65');
		const animated =
			triggered.visible &&
			after.visible &&
			after.opacity === 1 &&
			after.ty === 0 &&
			hasTransition &&
			(before.opacity < 1 || !before.visible);
		const sawMid =
			(mid.visible && mid.opacity > 0 && mid.opacity < 1) ||
			(mid.visible && mid.ty > 0 && mid.ty < 14) ||
			(triggered.visible && triggered.opacity > 0 && triggered.opacity < 1);
		ok = animated && (sawMid || target.fadeOnly);
		if (target.fadeOnly) {
			ok = ok && Math.abs(before.ty) < 0.5 && Math.abs(after.ty) < 0.5;
		}
	}

	return { ...target, ok, before, triggered, mid, after };
}

async function run(reducedMotion = false) {
	const browser = await chromium.launch({ headless: true });
	const context = await browser.newContext({
		viewport: { width: WIDTH, height: 900 },
		reducedMotion: reducedMotion ? 'reduce' : 'no-preference',
	});
	const page = await context.newPage();
	await page.goto(URL, { waitUntil: 'networkidle', timeout: 90000 });

	const totalAnimate = await page.locator('.pega-animate').count();
	const results = [];
	for (const t of TARGETS) {
		results.push(await verifyTarget(page, t, reducedMotion));
	}

	await browser.close();
	return { width: WIDTH, reducedMotion, totalAnimate, results };
}

const normal = await run(false);
const reduced = await run(true);
const pass = normal.results.filter((r) => r.ok).length;
const total = normal.results.length;

console.log(
	JSON.stringify(
		{
			summary: {
				width: WIDTH,
				pass: `${pass}/${total}`,
				totalAnimateElements: normal.totalAnimate,
				reducedAllPass: reduced.results.every((r) => r.ok),
			},
			normal: normal.results.map(({ name, ok, fadeOnly, before, triggered, mid, after, error }) => ({
				name,
				ok,
				fadeOnly,
				error,
				before,
				triggered,
				mid,
				after,
			})),
			reduced: reduced.results.map(({ name, ok, error }) => ({ name, ok, error })),
		},
		null,
		2,
	),
);

process.exit(pass === total && reduced.results.every((r) => r.ok) ? 0 : 1);
