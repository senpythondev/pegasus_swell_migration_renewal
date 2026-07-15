<?php
// Code Snippets(無料版) PHPスニペット原本: pega-css-07-posts / scope=global / active
// 実体はDB(wp04447asnippets)。この原本はバージョン管理・再投入用。
// pega-css-07-posts — CSS3層 ② セクション: コラム(posts)
// 読込順: pega-css-06-voices の後（依存チェーンで順序保証）。
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
   pega-sec-posts — コラム・ブログ(動的Query Loop)の装飾（③ / site-css / priority 10）
   構造（幅・grid・card箱組み・thumb画像比率・Query Loopの骨格・@media）は子テーマに残置。
   横断の最終調整は pega-global-*（優先度30/31）が担当。card固有角丸14px・中間色は維持。
   ========================================================= */

.pega-posts {
	background: linear-gradient(180deg, #f4f7fb 0%, #eef3f9 100%);
	padding: 0;
	font-family: var(--pega-font);
	border-top: 1px solid rgba(16, 103, 214, 0.08);
}
.pega-posts__title {
	margin: 0 0 clamp(12px, 2vw, 16px);
	color: var(--pega-base-ink);
	font-weight: 800;
	font-size: clamp(22px, 3.6vw, 28px);
	line-height: 1.45;
	letter-spacing: 0.03em;
}
.pega-posts__lead {
	margin: 0;
	color: var(--pega-text-sub);
	font-size: clamp(14px, 2.1vw, 16px);
	line-height: 1.8;
}
.pega-posts__block-title {
	margin: 0 0 clamp(16px, 3vw, 20px);
	color: var(--pega-base-ink);
	font-weight: 800;
	font-size: clamp(17px, 2.6vw, 20px);
	line-height: 1.5;
}
.pega-posts__block--staff .pega-posts__block-title {
	color: #5a8a1e;
}
.pega-posts-card {
	height: 100%;
	margin: 0;
	padding: 0;
	background: #fff;
	border: 1px solid rgba(16, 103, 214, 0.1);
	border-radius: 14px;
	overflow: hidden;
	box-shadow: 0 2px 12px rgba(16, 103, 214, 0.05);
	transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.pega-posts-card__title {
	margin: 0;
	padding: clamp(14px, 2.5vw, 16px) clamp(14px, 2.5vw, 16px) clamp(8px, 1.5vw, 10px);
	font-size: clamp(14px, 2.05vw, 15px);
	font-weight: 700;
	line-height: 1.6;
	flex: 1 1 auto;
}
.pega-posts-card__title a {
	color: var(--pega-text);
	text-decoration: none;
	display: -webkit-box;
	-webkit-line-clamp: 2;
	-webkit-box-orient: vertical;
	overflow: hidden;
}
.pega-posts-card__title a:hover {
	color: var(--pega-base);
}
.pega-posts-card__date {
	margin: 0;
	padding: 0 clamp(14px, 2.5vw, 16px) clamp(14px, 2.5vw, 16px);
	color: #6b7280;
	font-size: clamp(12px, 1.8vw, 13px);
	line-height: 1.5;
}
.pega-posts__empty {
	margin: 0;
	padding: clamp(20px, 3vw, 24px);
	color: var(--pega-text-sub);
	font-size: 14px;
	text-align: center;
	background: rgba(255, 255, 255, 0.7);
	border-radius: var(--pega-card-radius);
}
.pega-posts__more-link {
	margin: 0;
	font-size: clamp(13px, 1.9vw, 14px);
	font-weight: 600;
}
.pega-posts__more-link a {
	color: var(--pega-base-ink);
	text-decoration: none;
	border-bottom: 1px solid rgba(16, 103, 214, 0.25);
	padding-bottom: 2px;
	transition: color 0.2s ease, border-color 0.2s ease;
}
.pega-posts__more-link a:hover {
	color: var(--pega-base);
	border-color: rgba(16, 103, 214, 0.45);
}
@media (min-width: 960px) {
	.pega-posts-card:hover {
		transform: translateY(-2px);
		box-shadow: 0 8px 22px rgba(16, 103, 214, 0.1);
	}
}

PEGA_LAYER_EOF;
	wp_register_style( 'pega-css-07-posts', false, array( 'pega-css-06-voices' ), null );
	wp_enqueue_style( 'pega-css-07-posts' );
	wp_add_inline_style( 'pega-css-07-posts', pega_css_min( $css ) );
}, 12 );
