<?php
// Code Snippets(無料版) PHPスニペット原本: pega-css-20-global-layout / scope=global / active
// 実体はDB(wp04447asnippets)。この原本はバージョン管理・再投入用。
// pega-css-20-global-layout — CSS3層 ③ 横断: レイアウト(layout)
// 読込順: pega-css-10-header の後（依存チェーンで順序保証）。
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
   pega-global-layout — 横断レイアウト調整（③ / site-css / priority 30）
   -----------------------------------------------------------
   CSS3層移行 Step3。子テーマの「UNIFIED REFINEMENT + LAYOUT FIXES」から、
   構造寄り(幅統一・余白トークン適用・constrained上書き・位置・カード内余白)を集約。
   横断=最終上書きの役割のため、section スニペット(優先度10)より後(30)に読み込む。
   ※値は元の子テーマと完全一致(既に var(--pega-*) 化済)。ソース順を厳密保持。
   ========================================================= */

/* =========================================================
   TOP PAGE UNIFIED REFINEMENT — 余白・幅・装飾・階層の統一
   ========================================================= */

/* 調整1: ページトップボタンを追従CTAの上へ（追従CTA位置は維持）
   ※ pega-has-sticky-cta は body、home は #body_wrap に付与 */
body.pega-has-sticky-cta .home .p-fixBtnWrap {
	transition: bottom 0.3s ease, opacity 0.25s, visibility 0.25s;
}

@media (max-width: 959px) {
	body.pega-has-sticky-cta .home .p-fixBtnWrap {
		bottom: calc(78px + env(safe-area-inset-bottom, 0px));
	}
}

@media (min-width: 960px) {
	body.pega-has-sticky-cta .home .p-fixBtnWrap {
		bottom: calc(220px + 0.5em);
		right: calc(2.5em + (100vw - 100%));
	}
}

.home .pega-concerns > .wp-block-group__inner-container {
	max-width: var(--pega-wrap-wide);
}

.home .pega-bridge > .wp-block-group__inner-container,
.home .pega-reasons > .wp-block-group__inner-container,
.home .pega-voices > .wp-block-group__inner-container,
.home .pega-posts > .wp-block-group__inner-container,
.home .pega-homeworker > .wp-block-group__inner-container,
.home .pega-closing > .wp-block-group__inner-container {
	max-width: var(--pega-wrap-content) !important;
	margin-left: auto !important;
	margin-right: auto !important;
	width: 100% !important;
}

.home .pega-bridge__intro,
.home .pega-homeworker__intro,
.home .pega-reasons__intro,
.home .pega-voices__intro,
.home .pega-posts__intro {
	padding: var(--pega-section-py) 0 var(--pega-section-py-sm);
}

.home .pega-bridge__pillars-wrap,
.home .pega-bridge__details-wrap,
.home .pega-reasons__main-wrap,
.home .pega-reasons__extras-wrap,
.home .pega-voices__wrap,
.home .pega-posts__wrap,
.home .pega-homeworker__hero,
.home .pega-homeworker__videos,
.home .pega-homeworker__cta-wrap {
	padding-left: 0;
	padding-right: 0;
}

.home .pega-bridge__pillars-wrap,
.home .pega-bridge__details-wrap,
.home .pega-reasons__main-wrap,
.home .pega-reasons__extras-wrap,
.home .pega-voices__wrap,
.home .pega-posts__wrap {
	padding-bottom: var(--pega-section-py);
}

.home .pega-bridge__pillars-wrap {
	padding-top: 0;
}

.home .pega-homeworker__videos {
	padding-bottom: var(--pega-section-py-sm);
}

.home .pega-voices {
	padding-left: var(--pega-section-px);
	padding-right: var(--pega-section-px);
}

.home .pega-commute__wrap {
	max-width: var(--pega-wrap-content);
	padding-left: var(--pega-section-px);
	padding-right: var(--pega-section-px);
	margin-left: auto;
	margin-right: auto;
}

.home .pega-closing__wrap {
	padding: var(--pega-section-py-sm) 0;
}

/* 調整7: カード縦長の改善 */
.home .pega-bridge__pillar {
	min-height: 0;
}

.home .pega-bridge__pillar > .wp-block-group__inner-container {
	grid-template-rows: auto auto;
	min-height: 0;
}

.home .pega-bridge__pillar-img {
	height: auto;
	max-height: 200px;
	aspect-ratio: 4 / 3;
}

.home .pega-bridge__pillar-body,
.home .pega-bridge__pillar-body > .wp-block-group__inner-container {
	padding: var(--pega-card-padding);
}

.home .pega-bridge__detail,
.home .pega-reasons__card-body,
.home .pega-reasons__card-body > .wp-block-group__inner-container,
.home .pega-homeworker__strength,
.home .pega-concerns__item {
	padding: var(--pega-card-padding);
}

.home .pega-reasons__card-body > .wp-block-group__inner-container {
	max-width: none !important;
	width: 100%;
	min-width: 0;
}

.home .pega-voices__card {
	padding: var(--pega-card-padding);
}

.home .pega-posts-card__date {
	padding: 0 var(--pega-card-padding) var(--pega-card-padding);
}

.home .pega-reasons__card-img img {
	height: clamp(160px, 36vw, 200px);
}

.home .pega-reasons__card--featured .pega-reasons__card-img img {
	height: clamp(180px, 40vw, 220px);
}

@media (min-width: 768px) and (max-width: 959px) {
	.home .pega-reasons__card--featured .pega-reasons__card-img img {
		width: 100%;
		height: clamp(220px, 42vw, 300px);
	}
}

@media (min-width: 960px) {
	.home .pega-bridge__pillar {
		min-height: 0;
	}
}

/* =========================================================
   LAYOUT FIXES — 幅統一・位置・カード内余白・ボーダー
   ========================================================= */

/* WordPress constrained の contentSize を上書きし、全セクション同一幅に */
.home .wp-block-group[class*="pega-"] > .wp-block-group__inner-container {
	max-width: var(--pega-wrap-content) !important;
	margin-left: auto !important;
	margin-right: auto !important;
	width: 100%;
}

/* ヒーローはフル幅のまま（キャッチコピー右配置を維持） */
.home .pega-hero-wrap .wp-block-group__inner-container,
.home .pega-hero-wrap .wp-block-group[class*="pega-"] > .wp-block-group__inner-container {
	max-width: none !important;
	margin-left: 0 !important;
	margin-right: 0 !important;
	width: 100% !important;
}

/* constrained ブロック自体の幅制限を解除（padding 二重化を防ぐ） */
.home :is(
	.pega-bridge__intro,
	.pega-bridge__pillars-wrap,
	.pega-bridge__details-wrap,
	.pega-reasons__intro,
	.pega-reasons__main-wrap,
	.pega-reasons__extras-wrap,
	.pega-voices__intro,
	.pega-voices__wrap,
	.pega-posts__intro,
	.pega-posts__wrap,
	.pega-homeworker__intro,
	.pega-homeworker__hero,
	.pega-homeworker__videos,
	.pega-homeworker__cta-wrap,
	.pega-closing__wrap,
	.pega-commute__wrap
) {
	max-width: none !important;
	width: 100% !important;
	margin-left: 0 !important;
	margin-right: 0 !important;
}

.home .pega-concerns > .wp-block-group__inner-container,
.home .pega-commute__wrap,
.home .pega-closing__wrap {
	max-width: var(--pega-wrap-content);
	margin-left: auto;
	margin-right: auto;
	width: 100%;
}

.home .pega-commute {
	padding: var(--pega-section-py-sm) 0;
}

.home .pega-commute__wrap {
	padding-left: var(--pega-section-px);
	padding-right: var(--pega-section-px);
}

.home .pega-commute__banner {
	padding: var(--pega-card-padding);
}

/* フッター下端の余白を除去（画面下端との隙間のみ） */
.home #footer.l-footer:has(.pega-footer) {
	margin-bottom: 0 !important;
	padding-bottom: 0 !important;
}
PEGA_LAYER_EOF;
	wp_register_style( 'pega-css-20-global-layout', false, array( 'pega-css-10-header' ), null );
	wp_enqueue_style( 'pega-css-20-global-layout' );
	wp_add_inline_style( 'pega-css-20-global-layout', pega_css_min( $css ) );
}, 12 );
