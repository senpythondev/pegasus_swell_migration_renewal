<?php
/*
 * ▼ペガサス様 編集ガイド ─ 「FV（ヒーロー）」の装飾
 *   色や背景を変える時は、下の CSS を書き換えて保存。
 *   本文・画像・ボタンはSWELL標準ブロック側で編集。
 */
add_action( 'wp_enqueue_scripts', function () {
	$css = <<<'CSS'
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
	font-family: var(--pega-font);
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

/* swell-page (4929) — FVバッジ（rev5239 ブロックCSS復旧） */
.page-id-4929 .main-text .wp-block-group.u-mb-0 > .wp-block-paragraph.u-mb-10:first-of-type {
	width: fit-content;
}

/* swell-page (4929) — デスクトップ帯域(1025px+) FV CTA 約10%拡大（項目1） */
@media (min-width: 1025px) {
	.page-id-4929 .main-text .wp-block-paragraph.has-text-color[style*="border-left"] {
		margin-bottom: 8px !important;
	}

	.page-id-4929 .main-text .wp-block-buttons {
		gap: 13px !important;
		margin-top: 24px !important;
	}

	.page-id-4929 .main-text .btn-apply .wp-block-button__link,
	.page-id-4929 .main-text .btn-contact .wp-block-button__link {
		font-size: 15px !important;
		padding: 15px 31px !important;
	}
}
CSS;
	wp_register_style( 'pega-css-09-hero', false, array( 'child_style' ), null );
	wp_enqueue_style( 'pega-css-09-hero' );
	wp_add_inline_style( 'pega-css-09-hero', $css );
}, 21 );
