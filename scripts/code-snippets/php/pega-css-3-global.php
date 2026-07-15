<?php
// Code Snippets(無料版) PHPスニペット原本: pega-css-3-global / scope=global / active
// ※実体はDB(wp04447asnippets)。この原本はバージョン管理・再投入用。
// pega-css-3-global — CSS3層③ 横断調整（layout→deco→motion / 最終上書き）
// セクションの後に読み込み（pega-css-sections 依存）。
if ( ! function_exists( 'pega_css_min' ) ) {
	function pega_css_min( $css ) {
		// 安全なミニファイ（見た目に無影響）: コメント除去→行頭インデント除去→空行圧縮→末尾;除去
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
/* =========================================================
   pega-global-deco — 横断装飾調整（③ / site-css / priority 31）
   -----------------------------------------------------------
   CSS3層移行 Step3。子テーマの「UNIFIED REFINEMENT + LAYOUT FIXES」から、
   装飾寄り(カード枠・影・左アクセント線・色の柔らかさ・タイトル装飾リセット・
   ホバー抑制)を集約。layout(30)の後(31)＝装飾が最終確定。
   ※値は元と完全一致(唯一の生色 #fff=白カード背景はトークン無しのため維持)。ソース順厳密保持。
   ========================================================= */



/* 調整2・3: セクション余白・コンテンツ幅の統一 */
.home .pega-concerns {
	padding: var(--pega-section-py) var(--pega-section-px);
	background: var(--pega-soft-bg);
}

.home .pega-bridge {
	background: var(--pega-soft-bg);
	padding-left: var(--pega-section-px);
	padding-right: var(--pega-section-px);
}

.home .pega-homeworker {
	background: #fff;
	padding-left: var(--pega-section-px);
	padding-right: var(--pega-section-px);
}

.home .pega-reasons {
	background: var(--pega-soft-blue);
	padding-left: var(--pega-section-px);
	padding-right: var(--pega-section-px);
}

.home .pega-posts {
	background: var(--pega-soft-bg);
	padding-left: var(--pega-section-px);
	padding-right: var(--pega-section-px);
}

.home .pega-closing {
	background: var(--pega-soft-blue);
	padding-left: var(--pega-section-px);
	padding-right: var(--pega-section-px);
}

/* 調整6: セクションタイトル階層 */
.home .pega-concerns__title,
.home .pega-bridge__title,
.home .pega-homeworker__title,
.home .pega-reasons__title,
.home .pega-voices__title,
.home .pega-posts__title,
.home .pega-closing__title {
	margin: 0 0 clamp(16px, 2.5vw, 24px);
	color: var(--pega-text);
	font-weight: 800;
	font-size: var(--pega-h2);
	line-height: 1.45;
	letter-spacing: 0.02em;
}

.home .pega-concerns__lead,
.home .pega-bridge__text,
.home .pega-homeworker__lead,
.home .pega-voices__lead,
.home .pega-posts__lead,
.home .pega-closing__lead {
	font-size: var(--pega-lead);
	line-height: 1.85;
	color: var(--pega-text-sub);
}

.home .pega-bridge__pillar-title,
.home .pega-bridge__detail-title,
.home .pega-reasons__card-title,
.home .pega-posts__block-title,
.home .pega-homeworker__strength strong {
	font-size: var(--pega-h3);
	font-weight: 700;
	line-height: 1.5;
	color: var(--pega-text);
}

/* 調整4・5: 装飾削減・色の柔らかさ */
.home .pega-concerns__item,
.home .pega-bridge__pillar,
.home .pega-bridge__detail,
.home .pega-reasons__card,
.home .pega-voices__card,
.home .pega-posts-card,
.home .pega-homeworker__strength {
	background: #fff;
	border: 1px solid var(--pega-border);
	border-radius: var(--pega-card-radius);
	box-shadow: var(--pega-card-shadow);
}

.home .pega-concerns__item p::before {
	width: 6px;
	height: 6px;
	background: var(--pega-base);
	opacity: 0.35;
	box-shadow: none;
}

.home .pega-concerns__item--featured,
.home .pega-concerns__item--featured p {
	background: #fff;
	border-color: var(--pega-border-strong);
	box-shadow: var(--pega-card-shadow);
	color: var(--pega-text);
	font-weight: 600;
}

.home .pega-bridge__pillar--self .pega-bridge__pillar-img::after,
.home .pega-bridge__pillar--skill .pega-bridge__pillar-img::after,
.home .pega-bridge__pillar--support .pega-bridge__pillar-img::after {
	display: none;
}

.home .pega-bridge__pillar--self .pega-bridge__pillar-img img,
.home .pega-bridge__pillar--skill .pega-bridge__pillar-img img,
.home .pega-bridge__pillar--support .pega-bridge__pillar-img img,
.home .pega-reasons__card--1 .pega-reasons__card-img img,
.home .pega-reasons__card--2 .pega-reasons__card-img img,
.home .pega-reasons__card--3 .pega-reasons__card-img img {
	border-bottom: none;
}

.home .pega-bridge__pillar--self .pega-bridge__pillar-label,
.home .pega-bridge__pillar--skill .pega-bridge__pillar-label,
.home .pega-bridge__pillar--support .pega-bridge__pillar-label,
.home .pega-bridge__detail--rhythm .pega-bridge__detail-title,
.home .pega-bridge__detail--skill .pega-bridge__detail-title,
.home .pega-bridge__detail--practice .pega-bridge__detail-title,
.home .pega-reasons__card--1 .pega-reasons__card-label,
.home .pega-reasons__card--2 .pega-reasons__card-label,
.home .pega-reasons__card--3 .pega-reasons__card-label {
	color: var(--pega-text-sub);
}

.home .pega-bridge__detail--rhythm,
.home .pega-bridge__detail--skill,
.home .pega-bridge__detail--practice {
	border-top: none;
	border-left: 3px solid var(--pega-base);
}

.home .pega-bridge__detail--skill {
	border-left-color: var(--pega-accent);
}

.home .pega-bridge__detail--practice {
	border-left-color: var(--pega-second);
}

.home .pega-homeworker__strength {
	border-left: 3px solid var(--pega-base);
}

.home .pega-homeworker__strength--2 {
	border-left-color: var(--pega-accent);
}

.home .pega-homeworker__strength--3 {
	border-left-color: var(--pega-second);
}

.home .pega-homeworker__strength--4 {
	border-left-color: var(--pega-base-ink);
}

.home .pega-homeworker__highlight {
	background: var(--pega-soft-blue);
	border: 1px solid var(--pega-border);
}

.home .pega-homeworker__eyebrow,
.home .pega-voices__group-label {
	color: var(--pega-text-sub);
	letter-spacing: 0.08em;
}

.home .pega-voices__group-label--grad {
	color: var(--pega-text-sub);
}

.home .pega-voices__card--user,
.home .pega-voices__card--grad {
	border-left: none;
	background: #fff;
}

.home .pega-voices__card::before,
.home .pega-voices__card--grad::before {
	display: none;
}

.home .pega-bridge__closing {
	background: var(--pega-soft-blue);
	border-top: 1px solid var(--pega-border);
	border-bottom: 1px solid var(--pega-border);
}

.home .pega-bridge__closing-title {
	color: var(--pega-text);
	text-shadow: none;
}

.home .pega-bridge__closing-lead {
	color: var(--pega-text-sub);
}

.home .pega-bridge__closing-photo {
	box-shadow: var(--pega-card-shadow);
	border: 1px solid var(--pega-border);
}

.home .pega-bridge__closing-photo img {
	box-shadow: none;
}

.home .pega-reasons__card--featured {
	border-color: var(--pega-border-strong);
	box-shadow: var(--pega-card-shadow);
}

.home .pega-closing__panel {
	background: #fff;
	border: 1px solid var(--pega-border);
	box-shadow: var(--pega-card-shadow);
}

/* SWELL post_content 既定 h2（青帯）を解除 */
.home .post_content :is(
	.pega-concerns__title,
	.pega-bridge__title,
	.pega-homeworker__title,
	.pega-reasons__title,
	.pega-voices__title,
	.pega-posts__title,
	.pega-closing__title
) {
	background: transparent !important;
	padding: 0 !important;
	border: none !important;
	box-shadow: none !important;
	color: var(--pega-text) !important;
}

.home .post_content :is(
	.pega-concerns__title,
	.pega-bridge__title,
	.pega-homeworker__title,
	.pega-reasons__title,
	.pega-voices__title,
	.pega-posts__title,
	.pega-closing__title
)::before {
	display: none !important;
	content: none !important;
	border: none !important;
	box-shadow: none !important;
}

/* SWELL post_content 既定 h3/h4 装飾（下線・左線）を解除 — padding は維持 */
.home .post_content :is(
	.pega-bridge__pillar-title,
	.pega-bridge__detail-title,
	.pega-bridge__closing-title,
	.pega-reasons__card-title,
	.pega-reasons__extra-title,
	.pega-homeworker__videos-title,
	.pega-homeworker__video-heading,
	.pega-voices__card-title,
	.pega-posts__block-title,
	.pega-posts-card__title,
	.pega-commute__title
) {
	border: none !important;
	background: transparent !important;
}

.home .post_content :is(
	.pega-bridge__pillar-title,
	.pega-bridge__detail-title,
	.pega-bridge__closing-title,
	.pega-reasons__card-title,
	.pega-reasons__extra-title,
	.pega-homeworker__videos-title,
	.pega-homeworker__video-heading,
	.pega-voices__card-title,
	.pega-posts__block-title,
	.pega-posts-card__title,
	.pega-commute__title
)::before {
	display: none !important;
	content: none !important;
	border: none !important;
	background: none !important;
}

.home .pega-posts-card__title {
	padding: var(--pega-card-padding) var(--pega-card-padding) clamp(8px, 1.5vw, 10px);
	text-align: center;
}

.home .pega-bridge__detail-text,
.home .pega-reasons__card-text,
.home .pega-voices__quote p {
	line-height: 1.75;
	text-align: left;
}

@media (min-width: 960px) {

	.home .pega-bridge__pillar:hover,
	.home .pega-bridge__detail:hover,
	.home .pega-concerns__item:hover,
	.home .pega-reasons__card:hover,
	.home .pega-posts-card:hover {
		transform: none;
		box-shadow: var(--pega-card-shadow);
	}
}

/* カードタイトル中央揃え */
.home :is(
	.pega-bridge__pillar-title,
	.pega-bridge__detail-title,
	.pega-reasons__card-title,
	.pega-homeworker__strength strong,
	.pega-posts-card__title,
	.pega-posts-card__title a
) {
	text-align: center;
}

/* カード枠線 — 左アクセント + やや濃い全体枠（border 一括指定の後に left を指定） */
.home :is(
	.pega-concerns__item,
	.pega-bridge__pillar,
	.pega-bridge__detail,
	.pega-reasons__card,
	.pega-reasons__extra,
	.pega-voices__card,
	.pega-posts-card,
	.pega-homeworker__strength,
	.pega-commute__banner
) {
	border: 1px solid var(--pega-border-strong);
	box-shadow: var(--pega-card-shadow);
}

.home .pega-concerns__item,
.home .pega-bridge__pillar,
.home .pega-bridge__detail,
.home .pega-reasons__card,
.home .pega-voices__card--user,
.home .pega-posts-card,
.home .pega-homeworker__strength {
	border-left-width: 3px;
	border-left-style: solid;
	border-left-color: var(--pega-base);
}

.home .pega-bridge__detail--skill,
.home .pega-homeworker__strength--2 {
	border-left-color: var(--pega-accent);
}

.home .pega-bridge__detail--practice,
.home .pega-homeworker__strength--3 {
	border-left-color: var(--pega-second);
}

.home .pega-homeworker__strength--4 {
	border-left-color: var(--pega-base-ink);
}

.home .pega-concerns__item--featured {
	border-left-color: var(--pega-accent);
}
/* =========================================================
   pega-global-motion — 控えめなスクロール出現（③ / site-css / priority 32）
   構造は子テーマに書かない。JS は pega-inview.js（フロントのみ）。
   ========================================================= */

/* transition は両状態で維持（is-visible 付与時に 0s へ落ちるのを防ぐ） */
html.pega-motion-js .pega-animate {
	transition:
		opacity var(--pega-motion-duration) var(--pega-motion-ease),
		transform var(--pega-motion-duration) var(--pega-motion-ease);
}

html.pega-motion-js .pega-animate:not(.is-visible) {
	opacity: 0;
	transform: translateY(var(--pega-motion-offset));
	will-change: opacity, transform;
}

html.pega-motion-js .pega-animate.is-visible {
	opacity: 1;
	transform: translateY(0);
}

/* 通所バナー：フェードのみ（スライドなし） */
html.pega-motion-js .pega-animate.pega-animate--fade:not(.is-visible) {
	opacity: 0;
	transform: none;
}

html.pega-motion-js .pega-animate.pega-animate--fade.is-visible {
	opacity: 1;
	transform: none;
}

@media (prefers-reduced-motion: reduce) {
	html.pega-motion-js .pega-animate,
	html.pega-motion-js .pega-animate.is-visible,
	.pega-animate,
	.pega-animate.is-visible {
		opacity: 1 !important;
		transform: none !important;
		transition: none !important;
	}

	/* hover 浮き上がり：reduced-motion 時のみ停止 */
	.home .pega-bridge__pillar:hover,
	.home .pega-bridge__detail:hover,
	.home .pega-concerns__item:hover,
	.home .pega-reasons__card:hover,
	.home .pega-reasons__extra:hover,
	.home .pega-posts-card:hover,
	.pega-sticky-cta,
	.pega-sticky-cta.is-visible {
		transform: none !important;
	}
}
PEGA_LAYER_EOF;
	wp_register_style( 'pega-css-global', false, array( 'pega-css-sections' ), null );
	wp_enqueue_style( 'pega-css-global' );
	wp_add_inline_style( 'pega-css-global', pega_css_min( $css ) );
}, 12 );
