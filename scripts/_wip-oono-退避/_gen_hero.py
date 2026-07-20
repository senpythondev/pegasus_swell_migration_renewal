#!/usr/bin/env python3
"""Generate scripts/top_page/hero.html from design assets."""

LAUREL_L = (
    '<svg class="pega-trust__laurel" width="20" height="52" viewBox="0 0 20 52" fill="none" '
    'xmlns="http://www.w3.org/2000/svg">'
    '<path d="M10 1C6.5 8 3 16 4.5 26c.8 4.5 2.5 9 4 13.5 1-4 2.5-8.5 3.5-13.5 1.2-9.5-2.5-17.5-6-15.5z" fill="currentColor" opacity=".92"/>'
    '<ellipse cx="7" cy="14" rx="2.2" ry="4.8" fill="currentColor" transform="rotate(-22 7 14)"/>'
    '<ellipse cx="5.5" cy="22" rx="2" ry="5" fill="currentColor" transform="rotate(-12 5.5 22)"/>'
    '<ellipse cx="6" cy="30" rx="1.8" ry="4.6" fill="currentColor" transform="rotate(2 6 30)"/>'
    '<ellipse cx="7.5" cy="38" rx="1.6" ry="4.2" fill="currentColor" transform="rotate(14 7.5 38)"/>'
    "</svg>"
)
LAUREL_R = LAUREL_L.replace('pega-trust__laurel"', 'pega-trust__laurel pega-trust__laurel--right"')

ICONS = {
    "founding": (
        '<svg class="pega-trust__icon" width="34" height="34" viewBox="0 0 34 34" fill="none" '
        'xmlns="http://www.w3.org/2000/svg">'
        '<path d="M6 14h22v16H6V14z" fill="currentColor"/>'
        '<path d="M4 14 17 5l13 9" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>'
        '<rect x="12" y="19" width="4" height="11" fill="#fff" opacity=".95"/>'
        '<rect x="18" y="19" width="4" height="11" fill="#fff" opacity=".95"/>'
        "</svg>"
    ),
    "jobs": (
        '<svg class="pega-trust__icon" width="34" height="34" viewBox="0 0 34 34" fill="none" '
        'xmlns="http://www.w3.org/2000/svg">'
        '<circle cx="12" cy="12" r="4" fill="currentColor"/>'
        '<circle cx="22" cy="12" r="4" fill="currentColor"/>'
        '<path d="M5 26c0-4 3.5-7 7-7s7 3 7 7M15 26c0-3.5 2.8-6 6.5-6H25c3.5 0 6.5 2.8 6.5 6" '
        'stroke="currentColor" stroke-width="2" stroke-linecap="round"/>'
        "</svg>"
    ),
    "rate": (
        '<svg class="pega-trust__icon" width="34" height="34" viewBox="0 0 34 34" fill="none" '
        'xmlns="http://www.w3.org/2000/svg">'
        '<rect x="6" y="18" width="5" height="10" rx="1" fill="currentColor"/>'
        '<rect x="14" y="13" width="5" height="15" rx="1" fill="currentColor"/>'
        '<rect x="22" y="8" width="5" height="20" rx="1" fill="currentColor"/>'
        '<path d="M8 10h16l4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>'
        "</svg>"
    ),
}

BADGE_ICON = (
    '<svg class="pega-hero__badge-icon" width="16" height="16" viewBox="0 0 16 16" fill="none" '
    'xmlns="http://www.w3.org/2000/svg" aria-hidden="true">'
    '<path d="M2 7.5 8 3l6 4.5V13a1 1 0 0 1-1 1h-3.5V10H6.5v4H3a1 1 0 0 1-1-1V7.5Z" fill="currentColor"/>'
    "</svg>"
)


def trust_visual(kind: str) -> str:
    return (
        f'<span class="pega-trust__visual" aria-hidden="true">'
        f"{LAUREL_L}{ICONS[kind]}{LAUREL_R}"
        f"</span>"
    )


def trust_item(label: str, num: str, suffix: str, kind: str) -> str:
    visual = trust_visual(kind)
    body = (
        f'<span class="pega-trust__body"><span class="pega-trust__label">{label}</span>'
        f'<span class="pega-trust__num">{num}<span class="pega-trust__suffix">{suffix}</span></span></span>'
    )
    return f'<p class="pega-trust__item">{visual}{body}</p>'


INPAGE_CSS = r"""
/* FV背景 — SP/PC出し分け（明るい写真・青被りなし） */
.pega-hero-wrap .swell-block-fullWide,
.pega-hero-wrap .wp-block-loos-full-wide,
.pega-hero-wrap .pega-hero-fv-bg {
	position: relative;
	min-height: min(88vh, 820px);
	display: flex;
	align-items: center;
	overflow: hidden;
	margin: 0 !important;
	padding-top: 0 !important;
	padding-bottom: 0 !important;
	background-color: #eef3f9;
	background-image: url("/wp-content/uploads/2026/07/woman-top-sp.jpg") !important;
	background-size: cover !important;
	background-repeat: no-repeat !important;
	background-position: center 18%;
}
@media (min-width: 768px) {
	.pega-hero-wrap .swell-block-fullWide,
	.pega-hero-wrap .wp-block-loos-full-wide,
	.pega-hero-wrap .pega-hero-fv-bg {
		background-image: url("/wp-content/uploads/2026/07/woman-top-pc.jpg") !important;
		background-position: 72% center;
		min-height: min(86vh, 780px);
	}
}
@media (min-width: 1200px) {
	.pega-hero-wrap .swell-block-fullWide,
	.pega-hero-wrap .wp-block-loos-full-wide,
	.pega-hero-wrap .pega-hero-fv-bg {
		min-height: min(88vh, 820px);
		background-position: 68% center;
	}
}
.pega-hero-wrap .swell-block-fullWide.has-bg-img::after,
.pega-hero-wrap .wp-block-loos-full-wide.has-bg-img::after {
	display: none !important;
	content: none !important;
}
@media (max-width: 767px) {
	.pega-hero-wrap .swell-block-fullWide,
	.pega-hero-wrap .wp-block-loos-full-wide,
	.pega-hero-wrap .pega-hero-fv-bg {
		min-height: min(92vh, 760px);
	}
	.pega-hero__content::before {
		content: "";
		position: absolute;
		inset: auto 0 0;
		height: 68%;
		background: linear-gradient(to top, rgba(255, 255, 255, 0.94) 0%, rgba(255, 255, 255, 0.62) 52%, transparent 100%);
		pointer-events: none;
		z-index: -1;
	}
}

/* タイポ・バッジ（イメージ.png：左寄せ・白地枠バッジ） */
@media (min-width: 768px) {
	.pega-hero__copy .has-text-align-center,
	.pega-hero__title.has-text-align-center {
		text-align: left;
	}
}
.pega-hero__badge {
	display: inline-flex;
	align-items: center;
	gap: 6px;
	margin: 0 0 16px;
	padding: 8px 16px;
	border-radius: 999px;
	background: #fff;
	color: var(--pega-base);
	border: 2px solid var(--pega-base);
	font-weight: 700;
	font-size: 13px;
	line-height: 1.2;
	letter-spacing: 0.06em;
	box-shadow: 0 2px 10px rgba(16, 103, 214, 0.12);
}
.pega-hero__badge-icon {
	flex: 0 0 auto;
	display: block;
}
.pega-hero__title {
	margin: 0 0 10px;
	color: var(--pega-base-dark);
	font-weight: 800;
	font-size: 28px;
	line-height: 1.45;
	letter-spacing: 0.04em;
	text-align: center;
	line-break: strict;
	text-wrap: pretty;
}
.pega-hero__eyebrow {
	margin: 0 0 14px;
	color: var(--pega-base-dark);
	font-weight: 700;
	font-size: 20px;
	line-height: 1.55;
	letter-spacing: 0.04em;
	text-align: center;
	text-wrap: pretty;
}
.pega-hero__accent {
	color: var(--pega-base);
	font-weight: 800;
}
.pega-hero__desc {
	margin: 0 0 28px;
	color: var(--pega-text-sub);
	font-weight: 500;
	font-size: 15px;
	line-height: 1.85;
	max-width: 28em;
	text-align: center;
	text-wrap: pretty;
}
@media (min-width: 768px) {
	.pega-hero__title { font-size: clamp(32px, 3.2vw, 44px); margin-bottom: 12px; }
	.pega-hero__eyebrow { font-size: clamp(22px, 2.2vw, 30px); margin-bottom: 16px; }
	.pega-hero__desc { font-size: clamp(15px, 1.35vw, 17px); margin-bottom: 28px; max-width: 22em; }
	.pega-hero__badge { font-size: 14px; margin-bottom: 18px; }
}
@media (min-width: 1440px) {
	.pega-hero__title { font-size: 46px; }
	.pega-hero__eyebrow { font-size: 32px; }
	.pega-hero__desc { font-size: 17px; }
}
@media (max-width: 425px) {
	.pega-hero__title { font-size: 24px; }
	.pega-hero__eyebrow { font-size: 18px; }
	.pega-hero__desc { font-size: 14px; }
	.pega-hero__badge { font-size: 12px; padding: 7px 14px; }
}

/* CTA：緑・コーラル＋アイコン */
.pega-hero__actions .swell-block-button__link {
	display: flex;
	align-items: center;
	justify-content: center;
	gap: 0.45em;
	width: 100%;
	min-height: 52px;
	padding: 14px 22px;
	border-radius: 999px;
	font-family: var(--pega-font);
	font-weight: 700;
	font-size: 14px;
	line-height: 1.4;
	text-decoration: none;
	border: 2px solid transparent;
	box-sizing: border-box;
	transition: transform 0.15s ease, box-shadow 0.15s ease, filter 0.15s ease;
}
.pega-hero__btn--primary .swell-block-button__link {
	background: var(--pega-accent) !important;
	color: #fff !important;
	box-shadow: 0 4px 16px rgba(141, 199, 60, 0.38);
}
.pega-hero__btn--secondary .swell-block-button__link {
	background: var(--pega-second) !important;
	color: #fff !important;
	box-shadow: 0 4px 16px rgba(253, 117, 124, 0.34);
}
.pega-hero__actions .swell-block-button__link:hover {
	transform: translateY(-2px);
	filter: brightness(1.04);
}
.pega-hero__btn--primary .swell-block-button__link span::before {
	content: "";
	display: inline-block;
	width: 1.15em;
	height: 1.15em;
	margin-right: 0.15em;
	vertical-align: -0.2em;
	background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='2'%3E%3Crect x='3' y='5' width='18' height='12' rx='1'/%3E%3Cpath d='M8 19h8'/%3E%3C/svg%3E") center/contain no-repeat;
}
.pega-hero__btn--secondary .swell-block-button__link span::before {
	content: "";
	display: inline-block;
	width: 1.15em;
	height: 1.15em;
	margin-right: 0.15em;
	vertical-align: -0.2em;
	background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='2'%3E%3Ccircle cx='9' cy='8' r='3'/%3E%3Ccircle cx='17' cy='9' r='2.5'/%3E%3Cpath d='M3 19c0-3 2.5-5 6-5s6 2 6 5M13 19c0-2.5 2-4 5-4'/%3E%3C/svg%3E") center/contain no-repeat;
}

/* 実績バー — 青地・白文字・月桂樹＋アイコン */
.pega-trust {
	position: relative;
	z-index: 3;
	background: var(--pega-base);
	color: #fff;
	border-top: none;
	box-shadow: none;
	padding-bottom: 0;
}
.pega-trust::after {
	content: "";
	display: block;
	height: 28px;
	background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1440 56' preserveAspectRatio='none'%3E%3Cpath fill='%23f7f9fc' d='M0 28C180 56 360 0 540 28s360 28 540 0 360-28 540 0v28H0z'/%3E%3C/svg%3E") center bottom / 100% 100% no-repeat;
}
.pega-trust__inner > .wp-block-column + .wp-block-column {
	border-left: 1px solid rgba(255, 255, 255, 0.28);
}
.pega-trust__item {
	color: #fff;
}
.pega-trust__item.wp-block-paragraph {
	display: flex;
	margin: 0;
}
.pega-trust__visual {
	display: flex;
	align-items: center;
	gap: 2px;
	color: #fff;
}
.pega-trust__laurel {
	flex: 0 0 auto;
	display: block;
}
.pega-trust__laurel--right {
	transform: scaleX(-1);
}
.pega-trust__icon {
	flex: 0 0 auto;
	display: block;
}
.pega-trust__label {
	display: block;
	color: rgba(255, 255, 255, 0.95);
	font-weight: 700;
	font-size: 13px;
	line-height: 1.3;
	letter-spacing: 0.06em;
}
.pega-trust__num {
	display: block;
	color: #fff;
	font-family: "Yu Mincho", "Hiragino Mincho ProN", "Noto Serif JP", serif;
	font-weight: 800;
	font-size: clamp(34px, 4.5vw, 48px);
	line-height: 1;
	letter-spacing: -0.02em;
}
.pega-trust__suffix {
	font-size: 0.42em;
	font-weight: 700;
	margin-left: 1px;
}
.pega-trust__note {
	margin: 0;
	padding: 6px 16px 14px;
	color: rgba(255, 255, 255, 0.82);
	font-size: 11px;
	line-height: 1.5;
	text-align: center;
}
@media (max-width: 767px) {
	.pega-trust__inner > .wp-block-column + .wp-block-column {
		border-left: none;
		border-top: 1px solid rgba(255, 255, 255, 0.24);
	}
	.pega-trust__laurel { width: 16px; height: 44px; }
	.pega-trust__icon { width: 28px; height: 28px; }
	.pega-trust__num { font-size: 36px; }
}
@media (min-width: 768px) {
	.pega-trust__label { font-size: 14px; }
}

/* 悩みセクションとの繋がり（波形下を concerns 背景色へ） */
.home section.pega-hero-wrap + section.pega-concerns {
	margin-top: 0 !important;
	padding-top: clamp(36px, 5vw, 52px);
}
"""

trust_cols = f'''<!-- wp:column -->
<div class="wp-block-column"><!-- wp:paragraph {{"className":"pega-trust__item"}} -->
{trust_item("創業", "10", "年+", "founding")}
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:paragraph {{"className":"pega-trust__item"}} -->
{trust_item("就職実績", "100", "名+", "jobs")}
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:paragraph {{"className":"pega-trust__item"}} -->
{trust_item("継続率", "90", "%+", "rate")}
<!-- /wp:paragraph --></div>
<!-- /wp:column -->'''

hero = f'''<!-- wp:group {{"tagName":"section","align":"full","className":"pega-hero-wrap","metadata":{{"name":"FV（ヒーロー）"}}}} -->
<section class="wp-block-group alignfull pega-hero-wrap"><!-- wp:html {{"metadata":{{"name":"FV用CSS/カスタムHTML"}}}} -->
<style id="pega-hero-inpage">
{INPAGE_CSS.strip()}
</style>
<!-- /wp:html -->

<!-- wp:loos/full-wide {{"bgImageID":4917,"bgImageUrl":"http://220.158.21.114:8080/wp-content/uploads/2026/07/woman-top-pc.jpg","bgColor":"transparent","bgOpacity":0,"contentSize":"full","pcPadding":"0","spPadding":"0","bgFocalPoint":{{"x":0.68,"y":0.42}}}} -->
<div class="wp-block-loos-full-wide swell-block-fullWide pc-py-0 sp-py-0 has-bg-img alignfull pega-hero-fv-bg" style="background-color:transparent;background-position:68% 42%;background-image:url(http://220.158.21.114:8080/wp-content/uploads/2026/07/woman-top-pc.jpg)" data-bg="http://220.158.21.114:8080/wp-content/uploads/2026/07/woman-top-pc.jpg"><div class="swell-block-fullWide__inner l-full"><!-- wp:group {{"className":"pega-hero__content","layout":{{"type":"default"}}}} -->
<div class="wp-block-group pega-hero__content"><!-- wp:group {{"className":"pega-hero__copy","layout":{{"type":"default"}}}} -->
<div class="wp-block-group pega-hero__copy"><!-- wp:paragraph {{"align":"center","className":"pega-hero__badge"}} -->
<p class="has-text-align-center pega-hero__badge">{BADGE_ICON}在宅100%</p>
<!-- /wp:paragraph -->

<!-- wp:heading {{"textAlign":"center","level":1,"className":"pega-hero__title"}} -->
<h1 class="wp-block-heading has-text-align-center pega-hero__title">しあわせに働くために、<br class="pega-br-pc">生きている。</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {{"align":"center","className":"pega-hero__eyebrow"}} -->
<p class="has-text-align-center pega-hero__eyebrow">在宅で、<span class="pega-hero__accent">自分のペースで</span>。</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {{"align":"center","className":"pega-hero__desc"}} -->
<p class="has-text-align-center pega-hero__desc">「人が怖い」「通勤がつらい」方のための、<br class="pega-br-pc">在宅型就労移行支援。</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {{"className":"pega-hero__actions","layout":{{"type":"flex","flexWrap":"wrap","justifyContent":"left"}}}} -->
<div class="wp-block-group pega-hero__actions"><!-- wp:loos/button {{"hrefUrl":"/homeworker/","content":"在宅型を詳しく見る ＞","btnSize":"l","className":"pega-hero__btn pega-hero__btn--primary"}} -->
<div class="swell-block-button -size-l pega-hero__btn pega-hero__btn--primary" data-align="" data-id=""><a href="/homeworker/" class="swell-block-button__link"><span>在宅型を詳しく見る ＞</span></a></div>
<!-- /wp:loos/button -->

<!-- wp:loos/button {{"hrefUrl":"/application/","content":"個別説明会に申し込む（無料）→","btnSize":"l","className":"pega-hero__btn pega-hero__btn--secondary"}} -->
<div class="swell-block-button -size-l pega-hero__btn pega-hero__btn--secondary" data-align="" data-id=""><a href="/application/" class="swell-block-button__link"><span>個別説明会に申し込む（無料）→</span></a></div>
<!-- /wp:loos/button --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div></div>
<!-- /wp:loos/full-wide -->

<!-- wp:group {{"className":"pega-trust","layout":{{"type":"constrained"}}}} -->
<div class="wp-block-group pega-trust"><!-- wp:columns {{"className":"pega-trust__inner"}} -->
<div class="wp-block-columns pega-trust__inner">{trust_cols}</div>
<!-- /wp:columns -->

<!-- wp:paragraph {{"align":"center","className":"pega-trust__note"}} -->
<p class="has-text-align-center pega-trust__note">※2024年12月時点 自社調べ</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->
'''

out = "/home/oono/Wordpress/scripts/top_page/hero.html"
with open(out, "w", encoding="utf-8") as f:
    f.write(hero)
print(f"Wrote {out} ({len(hero)} bytes)")
