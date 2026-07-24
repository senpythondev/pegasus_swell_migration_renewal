#!/usr/bin/env node
/**
 * FV break diagnosis — DOM, computed styles, overlap
 */
import { chromium } from 'playwright';
import { writeFileSync, mkdirSync } from 'fs';
import { join } from 'path';

const BASE = process.env.PEGA_URL || 'http://pegasus:4CkYn2kHCoO5vh@220.158.21.114:8080';
const OUT = join(process.cwd(), 'docs', 'fv-diagnose');
mkdirSync(OUT, { recursive: true });

function chain(el) {
	const parts = [];
	let n = el;
	while (n && n !== document.documentElement) {
		const cls = (n.className && typeof n.className === 'string') ? '.' + n.className.trim().split(/\s+/).slice(0, 6).join('.') : '';
		parts.push(`${n.tagName.toLowerCase()}${n.id ? '#' + n.id : ''}${cls}`);
		n = n.parentElement;
	}
	return parts;
}

async function diagnose(page) {
	return page.evaluate(() => {
		const bodyWrap = document.querySelector('#body_wrap');
		const canvas = document.querySelector('.pega-canvas');
		const fv = document.querySelector('.main-text.wp-block-cover, .main-text');
		const h1 = document.querySelector('.main-text h1, .main-text .wp-block-heading');
		const stats = document.querySelector('.pega-stats, .achivement');
		const ach = document.querySelector('.achivement');

		function cs(el, props) {
			if (!el) return null;
			const s = getComputedStyle(el);
			const o = {};
			for (const p of props) o[p] = s[p];
			const r = el.getBoundingClientRect();
			o._rect = { x: Math.round(r.x), y: Math.round(r.y), w: Math.round(r.width), h: Math.round(r.height) };
			return o;
		}

		function chain(el) {
			const parts = [];
			let n = el;
			while (n && n !== document.documentElement) {
				const cls = (n.className && typeof n.className === 'string') ? '.' + n.className.trim().split(/\s+/).slice(0, 8).join('.') : '';
				parts.push(`${n.tagName.toLowerCase()}${n.id ? '#' + n.id : ''}${cls}`);
				n = n.parentElement;
			}
			return parts;
		}

		const fvAlignfull = fv?.classList.contains('alignfull');
		const styleSheetHits = [];
		for (const sheet of [...document.styleSheets]) {
			let rules;
			try { rules = sheet.cssRules; } catch { continue; }
			for (const rule of rules) {
				if (!rule.selectorText) continue;
				if (rule.selectorText.includes('pega-canvas') || rule.selectorText.includes('page-pega-fullwidth') || rule.selectorText.includes('page-id-4929')) {
					if (fv && (rule.selectorText.includes('main-text') || rule.selectorText.includes('alignfull') || rule.selectorText.includes('pega-canvas'))) {
						styleSheetHits.push({ sel: rule.selectorText, css: rule.cssText.slice(0, 120) });
					}
				}
			}
		}

		// overlap: stats vs fv
		const fvR = fv?.getBoundingClientRect();
		const statsR = stats?.getBoundingClientRect();
		const achR = ach?.getBoundingClientRect();

		return {
			bodyWrapClasses: bodyWrap?.className || null,
			bodyClasses: document.body.className,
			hasHomeClass: bodyWrap?.classList.contains('home') || document.body.classList.contains('home'),
			hasPageTemplateClass: bodyWrap?.className?.includes('page-template-page-pega-fullwidth'),
			canvasChain: canvas ? chain(canvas).slice(0, 12) : null,
			fvChain: fv ? chain(fv).slice(0, 15) : null,
			fvAlignfull,
			fvClasses: fv?.className || null,
			fvComputed: cs(fv, ['width', 'maxWidth', 'marginLeft', 'marginRight', 'position', 'overflow', 'minHeight', 'display', 'left', 'transform']),
			h1Exists: !!h1,
			h1Text: h1?.textContent?.trim().slice(0, 80) || null,
			h1Computed: cs(h1, ['display', 'visibility', 'opacity', 'color', 'fontSize', 'position', 'left', 'top', 'zIndex', 'height', 'overflow', 'clipPath', 'transform']),
			h1Chain: h1 ? chain(h1).slice(0, 8) : null,
			statsComputed: cs(stats, ['marginTop', 'position', 'zIndex']),
			achComputed: cs(ach, ['marginTop', 'position', 'zIndex']),
			overlap: fvR && statsR ? { fvBottom: Math.round(fvR.bottom), statsTop: Math.round(statsR.top), gap: Math.round(statsR.top - fvR.bottom) } : null,
			achOverlap: fvR && achR ? { fvBottom: Math.round(fvR.bottom), achTop: Math.round(achR.top), achMT: cs(ach, ['marginTop'])?.marginTop } : null,
			parentOverflow: (() => {
				const res = [];
				let n = fv;
				while (n && n !== document.documentElement) {
					const s = getComputedStyle(n);
					if (s.overflow !== 'visible' || s.overflowX !== 'visible' || s.maxWidth !== 'none' && s.maxWidth !== '100%') {
						res.push({ tag: n.tagName, cls: n.className?.slice?.(0, 80), overflow: s.overflow, overflowX: s.overflowX, maxWidth: s.maxWidth, width: s.width });
					}
					n = n.parentElement;
				}
				return res;
			})(),
			styleSheetHitCount: styleSheetHits.length,
			styleSheetHits: styleSheetHits.slice(0, 8),
			loadedChildStyle: [...document.querySelectorAll('link[rel=stylesheet]')].map(l => l.href).filter(h => h.includes('swell_child') || h.includes('child')),
		};
	});
}

const browser = await chromium.launch();
const widths = [1440, 768, 425];
for (const w of widths) {
	const page = await browser.newPage({ viewport: { width: w, height: 900 } });
	await page.goto(`${BASE}/swell-page/`, { waitUntil: 'networkidle', timeout: 60000 });
	const data = await diagnose(page);
	writeFileSync(join(OUT, `dom-${w}.json`), JSON.stringify(data, null, 2));
	await page.screenshot({ path: join(OUT, `current-${w}.png`), fullPage: false });
	// FV crop
	const fvBox = await page.evaluate(() => {
		const fv = document.querySelector('.main-text');
		if (!fv) return null;
		const r = fv.getBoundingClientRect();
		return { x: Math.max(0, r.x), y: Math.max(0, r.y), width: r.width, height: Math.min(r.height, 700) };
	});
	if (fvBox) {
		await page.screenshot({ path: join(OUT, `fv-crop-${w}.png`), clip: fvBox });
	}
	console.log(`=== ${w}px ===`);
	console.log(JSON.stringify(data, null, 2));
	await page.close();
}
await browser.close();
