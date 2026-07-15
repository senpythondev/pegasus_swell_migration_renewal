<?php
// Code Snippets(無料版) PHPスニペット原本: pega-css-09-hero / scope=global / active
// 実体はDB(wp04447asnippets)。この原本はバージョン管理・再投入用。
// pega-css-09-hero — CSS3層 ② セクション: ヒーロー+信頼指標(hero)
// 読込順: pega-css-08-closing の後（依存チェーンで順序保証）。
if ( ! function_exists( 'pega_css_min' ) ) {
	function pega_css_min( $css ) {
		$css = preg_replace( '~/\*.*?\*/~s', '', $css );
		$css = preg_replace( '~^[ \t]+~m', '', $css );
		$css = preg_replace( '~\n\s*\n~', "\n", $css );
		$css = preg_replace( '~;\s*}~', '}', $css );
		return trim( $css );
	}
}
add_action( 'wp_enqueue_scripts', function () {
	$css = <<<'PEGA_LAYER_EOF'
/* =========================================================
   pega-sec-hero — ヒーロー(FV)+信頼指標バーの装飾（③ / site-css / priority 10）
   -----------------------------------------------------------
   セレクタ単位でレイヤーを固定（base と全@media上書きを同一レイヤーに）し、
   font-size/min-height/padding/背景位置 等の上書き連鎖を保全（見た目不変）。
   ここには「見た目」＝背景画像・右寄せ暗幕グラデ・見出し色/影・2段CTAボタン配色・
   信頼指標の色/枠 を集約。full-wide の高さ(min-height)は背景画像と一体のため deco 側。
   構造（コピー右配置・flex/grid骨格・inner余白・SWELL :before無効化・幅）は子テーマ残置。
   ========================================================= */

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
	background-color: #dce8f5;
	background-image: url("/wp-content/uploads/2026/07/pega-hero-fv-20260712.jpg") !important;
	background-size: cover !important;
	background-repeat: no-repeat !important;
	background-position: 24% center;
}
/* モバイル横〜小タブレット：右側を多く見せる（position のみ・cover は維持） */
@media (min-width: 600px) and (max-width: 767px) {
	.pega-hero-wrap .swell-block-fullWide,
	.pega-hero-wrap .wp-block-loos-full-wide,
	.pega-hero-wrap .pega-hero-fv-bg {
		background-position: 12% center;
	}
}
/* タブレット：右側を多く見せ、コピー用スペースを確保 */
@media (min-width: 768px) and (max-width: 1023px) {
	.pega-hero-wrap .swell-block-fullWide,
	.pega-hero-wrap .wp-block-loos-full-wide,
	.pega-hero-wrap .pega-hero-fv-bg {
		background-position: 10% center;
	}
}
@media (min-width: 1024px) and (max-width: 1199px) {
	.pega-hero-wrap .swell-block-fullWide,
	.pega-hero-wrap .wp-block-loos-full-wide,
	.pega-hero-wrap .pega-hero-fv-bg {
		background-position: 14% center;
	}
}
@media (min-width: 1200px) {
	.pega-hero-wrap .swell-block-fullWide,
	.pega-hero-wrap .wp-block-loos-full-wide,
	.pega-hero-wrap .pega-hero-fv-bg {
		background-position: 16% center;
	}
}
@media (min-width: 1440px) {
	.pega-hero-wrap .swell-block-fullWide,
	.pega-hero-wrap .wp-block-loos-full-wide,
	.pega-hero-wrap .pega-hero-fv-bg {
		background-position: 16% center;
	}
}
.pega-hero-wrap .swell-block-fullWide.has-bg-img::after,
.pega-hero-wrap .wp-block-loos-full-wide.has-bg-img::after {
	content: "";
	position: absolute;
	inset: 0;
	z-index: 1;
	pointer-events: none;
	/* ヘッダー青(--pega-base)と調和する、明るい画像向けの右寄せスクリーン */
	background:
		linear-gradient(180deg, rgba(16, 103, 214, 0.2) 0%, rgba(16, 103, 214, 0.05) 10%, transparent 20%),
		linear-gradient(
			270deg,
			rgba(16, 103, 214, 0.56) 0%,
			rgba(16, 103, 214, 0.34) 24%,
			rgba(255, 255, 255, 0.08) 44%,
			transparent 62%
		);
}
.pega-hero__title {
	margin: 0 0 14px;
	color: #fff;
	font-weight: 800;
	font-size: 33px;
	line-height: 1.42;
	letter-spacing: 0.03em;
	text-align: center;
	line-break: strict;
	word-break: normal;
	overflow-wrap: normal;
	text-wrap: pretty;
	hanging-punctuation: allow-end first;
	text-shadow: 0 2px 14px rgba(8, 40, 100, 0.32), 0 1px 4px rgba(0, 0, 0, 0.18);
}
.pega-hero__eyebrow {
	margin: 0 0 12px;
	color: rgba(255, 255, 255, 0.96);
	font-weight: 700;
	font-size: 22px;
	line-height: 1.65;
	letter-spacing: 0.05em;
	text-align: center;
	line-break: strict;
	word-break: normal;
	overflow-wrap: normal;
	text-wrap: pretty;
	hanging-punctuation: allow-end first;
	text-shadow: 0 1px 10px rgba(8, 40, 100, 0.28);
}
.pega-hero__desc {
	margin: 0 auto 36px;
	color: rgba(255, 255, 255, 0.92);
	font-weight: 500;
	font-size: 20px;
	line-height: 1.8;
	max-width: 32em;
	text-align: center;
	line-break: strict;
	word-break: normal;
	overflow-wrap: normal;
	text-wrap: pretty;
	hanging-punctuation: allow-end first;
	text-shadow: 0 1px 10px rgba(8, 40, 100, 0.26);
}
@media (min-width: 600px) {
	.pega-hero__title { font-size: 39px; }
	.pega-hero__eyebrow { font-size: 24px; }
	.pega-hero__desc { font-size: 21px; margin-bottom: 32px; }
}
@media (min-width: 768px) and (max-width: 959px) {
	.pega-hero__title {
		font-size: 20px;
		line-height: 1.35;
		letter-spacing: 0;
		text-wrap: wrap;
		white-space: nowrap;
	}
	.pega-hero__eyebrow { font-size: 20px; letter-spacing: 0.02em; }
	.pega-hero__desc { font-size: 14px; line-height: 1.7; letter-spacing: 0; }
}
@media (min-width: 960px) {
	.pega-hero-wrap .swell-block-fullWide,
	.pega-hero-wrap .wp-block-loos-full-wide,
	.pega-hero-wrap .pega-hero-fv-bg {
		min-height: min(90vh, 860px);
	}
	.pega-hero__title { font-size: 51px; margin-bottom: 18px; }
	.pega-hero__eyebrow { font-size: 28px; margin-bottom: 14px; }
	.pega-hero__desc { font-size: 22px; margin-bottom: 0; }
}
@media (min-width: 960px) and (max-width: 1199px) {
	.pega-hero__desc {
		font-size: 16px;
		line-height: 1.7;
		letter-spacing: 0;
		text-wrap: wrap;
	}
}
@media (min-width: 1200px) {
	.pega-hero__title { font-size: 57px; }
	.pega-hero__eyebrow { font-size: 30px; }
	.pega-hero__desc { font-size: 23px; }
}
@media (min-width: 1440px) {
	.pega-hero__title {
		font-size: 56px;
		line-height: 1.38;
		letter-spacing: 0.02em;
		text-wrap: pretty;
	}
	.pega-hero__eyebrow { font-size: 33px; }
	.pega-hero__desc { font-size: 25px; max-width: none; }
}
@media (min-width: 1600px) {
	.pega-hero__title { font-size: 67px; }
	.pega-hero__eyebrow { font-size: 34px; }
	.pega-hero__desc { font-size: 26px; }
}
@media (min-width: 1920px) {
	.pega-hero__title { font-size: 71px; }
	.pega-hero__eyebrow { font-size: 35px; }
	.pega-hero__desc { font-size: 27px; }
}
@media (min-width: 1900px) {
	.pega-hero__desc {
		font-size: 24px;
		line-height: 1.75;
		letter-spacing: 0;
		text-wrap: wrap;
		white-space: nowrap;
	}
}
.pega-hero__actions .swell-block-button__link {
	display: flex;
	align-items: center;
	justify-content: center;
	gap: 0.35em;
	width: 100%;
	min-height: 52px;
	min-width: 44px;
	padding: 16px 28px;
	border-radius: 999px;
	font-family: var(--pega-font);
	font-weight: 700;
	font-size: 15px;
	line-height: 1.4;
	text-decoration: none;
	border: 2px solid transparent;
	white-space: nowrap;
	box-sizing: border-box;
	transition: transform 0.15s ease, box-shadow 0.15s ease, filter 0.15s ease;
}
.pega-hero__actions .swell-block-button:first-of-type .swell-block-button__link {
	background: var(--pega-base);
	color: #fff;
	box-shadow: 0 4px 18px rgba(16, 103, 214, 0.35);
	border-color: rgba(255, 255, 255, 0.2);
}
.pega-hero__actions .swell-block-button:first-of-type .swell-block-button__link:hover {
	box-shadow: 0 8px 24px rgba(16, 103, 214, 0.45);
}
.pega-hero__actions .swell-block-button:last-of-type .swell-block-button__link {
	background: var(--pega-accent);
	color: #fff;
	box-shadow: 0 4px 18px rgba(141, 199, 60, 0.42);
	border-color: rgba(255, 255, 255, 0.25);
}
.pega-hero__actions .swell-block-button:last-of-type .swell-block-button__link:hover {
	box-shadow: 0 8px 24px rgba(141, 199, 60, 0.52);
}
.pega-hero__actions .swell-block-button__link:hover {
	transform: translateY(-2px);
	filter: brightness(1.04);
}
.pega-hero__actions .swell-block-button__link:focus-visible {
	outline: 3px solid #fff;
	outline-offset: 3px;
}
@media (min-width: 960px) {
	.pega-hero__actions .swell-block-button__link { font-size: 16px; padding: 16px 32px; }
}
@media (min-width: 1280px) {
	.pega-hero__actions .swell-block-button__link { font-size: 16px; }
}
@media (min-width: 1440px) {
	.pega-hero__actions .swell-block-button__link {
		font-size: 16px;
		padding: 14px 26px;
		min-height: 48px;
	}
}
.pega-trust {
	position: relative;
	z-index: 3;
	background: #fff;
	border-top: 1px solid rgba(16, 103, 214, 0.1);
	box-shadow: 0 4px 20px rgba(16, 103, 214, 0.06);
}
.pega-trust__inner > .wp-block-column + .wp-block-column .pega-trust__item {
	border-left: 1px solid rgba(16, 103, 214, 0.12);
}
.pega-trust__num {
	display: inline-block;
	position: relative;
	padding-bottom: 12px;
	color: var(--pega-base);
	font-family: var(--pega-font);
	font-weight: 800;
	font-size: 34px;
	line-height: 1;
	letter-spacing: -0.02em;
}

/* 実績数字：数字幅いっぱいの青→オレンジ下線 */
.pega-trust__num::after {
	content: "";
	position: absolute;
	left: 0;
	right: 0;
	bottom: 0;
	width: 100%;
	height: 3px;
	border-radius: 999px;
	background: linear-gradient(90deg, var(--pega-base) 0%, var(--pega-orange) 100%);
	opacity: 0.9;
}
.pega-trust__suffix {
	font-size: 0.46em;
	font-weight: 700;
	margin-left: 2px;
}
.pega-trust__label {
	color: var(--pega-text-sub);
	font-family: var(--pega-font);
	font-weight: 600;
	font-size: 13px;
	line-height: 1.65;
	max-width: 18em;
}
@media (min-width: 600px) {
	.pega-trust__num { font-size: 42px; }
	.pega-trust__label { font-size: 14px; }
}
@media (min-width: 960px) {
	.pega-trust__num { font-size: 48px; }
	.pega-trust__label { font-size: 15px; }
}
@media (max-width: 599px) {
.pega-hero-wrap .swell-block-fullWide,
.pega-hero-wrap .wp-block-loos-full-wide {
		min-height: min(86vh, 720px);
		background-position: center 32%;
	}
	.pega-hero__title {
		font-size: 27px;
		line-height: 1.4;
		letter-spacing: 0.02em;
	}
	.pega-hero__eyebrow {
		font-size: 20px;
		line-height: 1.55;
	}
	.pega-hero__desc {
		font-size: 17px;
		line-height: 1.75;
		max-width: none;
	}
	.pega-hero-wrap .swell-block-fullWide.has-bg-img::after,
	.pega-hero-wrap .wp-block-loos-full-wide.has-bg-img::after {
		background:
			linear-gradient(180deg, rgba(16, 103, 214, 0.18) 0%, transparent 12%),
			linear-gradient(
				180deg,
				rgba(12, 58, 140, 0.68) 0%,
				rgba(12, 58, 140, 0.46) 48%,
				rgba(12, 58, 140, 0.18) 78%,
				transparent 100%
			);
	}
	.pega-hero__actions .swell-block-button__link {
		width: 100%;
		display: flex;
		box-sizing: border-box;
		padding: 15px 20px;
	}
	.pega-trust__inner > .wp-block-column + .wp-block-column .pega-trust__item {
		border-left: none;
		border-top: 1px solid rgba(16, 103, 214, 0.12);
	}
}
@media (max-width: 425px) {
	.pega-hero__title {
		font-size: 22px;
		line-height: 1.38;
		letter-spacing: 0;
		text-wrap: wrap;
	}
	.pega-hero__eyebrow {
		font-size: 18px;
	}
	.pega-hero__desc {
		font-size: 16px;
	}
	.pega-hero__actions .swell-block-button__link {
		padding: 14px 18px;
		min-height: 48px;
	}
}
PEGA_LAYER_EOF;
	wp_register_style( 'pega-css-09-hero', false, array( 'pega-css-08-closing' ), null );
	wp_enqueue_style( 'pega-css-09-hero' );
	wp_add_inline_style( 'pega-css-09-hero', pega_css_min( $css ) );
}, 12 );
