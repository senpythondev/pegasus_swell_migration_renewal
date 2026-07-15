<?php
// Code Snippets(無料版) PHPスニペット原本: pega-css-21-global-deco / scope=global / active
// 実体はDB(wp04447asnippets)。この原本はバージョン管理・再投入用。
// pega-css-21-global-deco — CSS3層 ③ 横断: 装飾(deco)
// 読込順: pega-css-20-global-layout の後（依存チェーンで順序保証）。
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
PEGA_LAYER_EOF;
	wp_register_style( 'pega-css-21-global-deco', false, array( 'pega-css-20-global-layout' ), null );
	wp_enqueue_style( 'pega-css-21-global-deco' );
	wp_add_inline_style( 'pega-css-21-global-deco', pega_css_min( $css ) );
}, 12 );
