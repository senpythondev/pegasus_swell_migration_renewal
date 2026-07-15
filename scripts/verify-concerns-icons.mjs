#!/usr/bin/env node
/**
 * 悩み共感セクション アイコン・背景 検証
 * - 条件1: concerns 以外への影響なし
 * - 条件2: 1440/1024/768/425 で文字・レイアウト
 * - アニメーション維持
 */
import { chromium } from 'playwright';

const URL = process.env.PEGA_URL || 'http://pegasus:4CkYn2kHCoO5vh@220.158.21.114:8080/';
const WIDTHS = [1440, 1024, 768, 425];

const OTHER_SECTIONS = [
	{ name: 'hero', sel: '.pega-hero__title' },
	{ name: 'bridge-intro', sel: '.pega-bridge__intro' },
	{ name: 'homeworker', sel: '.pega-homeworker__intro' },
	{ name: 'reasons', sel: '.pega-reasons__intro' },
	{ name: 'commute', sel: '.pega-commute__banner' },
	{ name: 'voices', sel: '.pega-voices__intro' },
	{ name: 'posts', sel: '.pega-posts__intro' },
	{ name: 'closing', sel: '.pega-closing__panel' },
];

async function checkConcerns(page) {
	const intro = page.locator('.pega-concerns__intro.pega-animate').first();
	const icons = page.locator('.pega-concerns .pega-concerns__icon');
	const texts = page.locator('.pega-concerns .pega-concerns__text');
	const listInner = page.locator('.pega-concerns .pega-concerns__list > .wp-block-group__inner-container').first();

	const iconCount = await icons.count();
	const textCount = await texts.count();

	// scroll to concerns
	await intro.scrollIntoViewIfNeeded();
	await page.waitForTimeout(100);

	const layoutIssues = await page.evaluate(() => {
		const issues = [];
		document.querySelectorAll('.pega-concerns .pega-concerns__card-body').forEach((body, i) => {
			const icon = body.querySelector('.pega-concerns__icon');
			const text = body.querySelector('.pega-concerns__text');
			if (!icon || !text) {
				issues.push({ i, type: 'missing-parts' });
				return;
			}
			const ir = icon.getBoundingClientRect();
			const tr = text.getBoundingClientRect();
			const br = body.getBoundingClientRect();
			if (tr.right > br.right + 1) issues.push({ i, type: 'text-overflow-right', delta: tr.right - br.right });
			if (tr.bottom > br.bottom + 2) issues.push({ i, type: 'text-overflow-bottom', delta: tr.bottom - br.bottom });
			if (ir.right > tr.left + 2) issues.push({ i, type: 'overlap', gap: tr.left - ir.right });
			if (tr.width < 80) issues.push({ i, type: 'text-too-narrow', width: tr.width });
		});
		return issues;
	});

	const listBg = await listInner.evaluate((el) => getComputedStyle(el).backgroundColor);
	const hasIcons = iconCount === 10 && textCount === 10;

	// animation check
	await page.evaluate(() => window.scrollTo(0, 0));
	await page.waitForTimeout(200);
	const beforeAnim = await intro.evaluate((n) => ({
		visible: n.classList.contains('is-visible'),
		opacity: parseFloat(getComputedStyle(n).opacity),
	}));
	await intro.evaluate((n) => n.scrollIntoView({ block: 'center' }));
	await page.waitForTimeout(700);
	const afterAnim = await intro.evaluate((n) => ({
		visible: n.classList.contains('is-visible'),
		opacity: parseFloat(getComputedStyle(n).opacity),
		transition: getComputedStyle(n).transitionDuration,
	}));

	return {
		iconCount,
		textCount,
		hasIcons,
		layoutIssues,
		listBg,
		animationOk: beforeAnim.opacity < 1 && afterAnim.visible && afterAnim.opacity === 1,
		beforeAnim,
		afterAnim,
	};
}

async function checkOtherSections(page) {
	const results = {};
	for (const s of OTHER_SECTIONS) {
		const el = page.locator(s.sel).first();
		const exists = (await el.count()) > 0;
		if (!exists) {
			results[s.name] = { ok: false, error: 'missing' };
			continue;
		}
		await el.scrollIntoViewIfNeeded();
		const snap = await el.evaluate((node) => {
			const cs = getComputedStyle(node);
			const r = node.getBoundingClientRect();
			return {
				opacity: parseFloat(cs.opacity),
				display: cs.display,
				visibility: cs.visibility,
				width: r.width,
				height: r.height,
				hasAnimate: node.classList.contains('pega-animate'),
				hasConcernsClass: [...node.classList].some((c) => c.startsWith('pega-concerns')),
			};
		});
		await page.waitForTimeout(snap.hasAnimate ? 700 : 100);
		const afterScroll = snap.hasAnimate
			? await el.evaluate((node) => parseFloat(getComputedStyle(node).opacity))
			: snap.opacity;
		results[s.name] = {
			ok: afterScroll > 0 && snap.width > 0 && snap.height > 0 && !snap.hasConcernsClass,
			...snap,
			afterScrollOpacity: afterScroll,
		};
	}
	const leakedIcons = await page.evaluate(
		() =>
			[...document.querySelectorAll('.pega-concerns__icon')].filter((el) => !el.closest('.pega-concerns'))
				.length,
	);
	return { sections: results, leakedIcons };
}

async function runWidth(width) {
	const browser = await chromium.launch({ headless: true });
	const page = await browser.newPage({ viewport: { width, height: 900 } });
	await page.goto(URL, { waitUntil: 'networkidle', timeout: 90000 });

	const concerns = await checkConcerns(page);
	const other = await checkOtherSections(page);

	await browser.close();
	return { width, concerns, other };
}

const all = [];
for (const w of WIDTHS) {
	all.push(await runWidth(w));
}

const summary = {
	widths: WIDTHS,
	allIcons: all.every((r) => r.concerns.hasIcons),
	allLayoutOk: all.every((r) => r.concerns.layoutIssues.length === 0),
	allAnimationOk: all.every((r) => r.concerns.animationOk),
	otherSectionsOk: all.every((r) => Object.values(r.other.sections).every((s) => s.ok)),
	noIconLeak: all.every((r) => r.other.leakedIcons === 0),
};

console.log(JSON.stringify({ summary, results: all }, null, 2));
const pass =
	summary.allIcons &&
	summary.allLayoutOk &&
	summary.allAnimationOk &&
	summary.otherSectionsOk &&
	summary.noIconLeak;
process.exit(pass ? 0 : 1);
