<?php
// Code Snippets(無料版) PHPスニペット原本: pega-css-05-reasons / scope=global / active
// 実体はDB(wp04447asnippets)。この原本はバージョン管理・再投入用。
// pega-css-05-reasons — CSS3層 ② セクション: 選ばれる理由(reasons)
// 読込順: pega-css-04-homeworker の後（依存チェーンで順序保証）。
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
   pega-sec-reasons — 選ばれている理由の装飾（③ / site-css / priority 10）
   構造（幅・grid/flex・card/featured/extraの箱組み・画像サイズ・@media骨格）は子テーマに残置。
   横断の最終調整は pega-global-*（優先度30/31）が担当。section固有の中間色・角丸・左線色は維持。
   ========================================================= */

.pega-reasons {
	background: linear-gradient(180deg, #eef4fb 0%, #e4edf8 55%, #dce8f5 100%);
	padding: 0;
	font-family: var(--pega-font);
	border-top: 1px solid rgba(16, 103, 214, 0.08);
}
.pega-reasons__title {
	margin: 0;
	color: var(--pega-text);
	font-weight: 800;
	font-size: clamp(24px, 4.8vw, 34px);
	line-height: 1.45;
	letter-spacing: 0.03em;
}
.pega-reasons__card {
	width: 100%;
	height: 100%;
	margin: 0;
	padding: 0;
	background: #fff;
	border: 1px solid rgba(16, 103, 214, 0.12);
	border-radius: 16px;
	box-shadow: 0 4px 18px rgba(16, 103, 214, 0.07);
	overflow: hidden;
	transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.pega-reasons__card--featured {
	border-color: rgba(16, 103, 214, 0.22);
	box-shadow: 0 6px 24px rgba(16, 103, 214, 0.1);
}
.pega-reasons__card--1 .pega-reasons__card-img img { border-bottom: 3px solid var(--pega-base); }
.pega-reasons__card--2 .pega-reasons__card-img img { border-bottom: 3px solid var(--pega-accent); }
.pega-reasons__card--3 .pega-reasons__card-img img { border-bottom: 3px solid var(--pega-second); }
.pega-reasons__card-label {
	margin: 0 0 6px;
	font-size: 12px;
	font-weight: 700;
	letter-spacing: 0.12em;
}
.pega-reasons__card--1 .pega-reasons__card-label { color: var(--pega-base); }
.pega-reasons__card--2 .pega-reasons__card-label { color: #5a8a1e; }
.pega-reasons__card--3 .pega-reasons__card-label { color: #c94a50; }
.pega-reasons__card--featured .pega-reasons__card-title {
	font-size: clamp(17px, 2.6vw, 20px);
}
.pega-reasons__card-title {
	margin: 0 0 clamp(10px, 2vw, 14px);
	color: var(--pega-text);
	font-weight: 800;
	font-size: clamp(16px, 2.4vw, 18px);
	line-height: 1.5;
	word-break: normal;
	overflow-wrap: break-word;
	text-wrap: pretty;
}
.pega-reasons__card-text {
	margin: 0;
	color: var(--pega-text-sub);
	font-size: clamp(14px, 2.05vw, 15px);
	line-height: 1.85;
	width: 100%;
	max-width: none;
	text-align: left;
	word-break: normal;
	overflow-wrap: break-word;
	text-wrap: pretty;
}
.pega-reasons__extra {
	width: 100%;
	height: 100%;
	margin: 0;
	padding: clamp(22px, 4vw, 28px) clamp(20px, 3.5vw, 26px);
	background: #fff;
	border: 1px solid rgba(16, 103, 214, 0.1);
	border-radius: 14px;
	box-shadow: 0 4px 16px rgba(16, 103, 214, 0.06);
}
.pega-reasons__extra--support {
	border-left: 4px solid var(--pega-base);
}
.pega-reasons__extra--aftercare {
	border-left: 4px solid var(--pega-accent);
}
.pega-reasons__extra-title {
	margin: 0 0 clamp(10px, 2vw, 14px);
	font-weight: 800;
	font-size: clamp(16px, 2.4vw, 18px);
	line-height: 1.5;
}
.pega-reasons__extra--support .pega-reasons__extra-title { color: var(--pega-base-ink); }
.pega-reasons__extra--aftercare .pega-reasons__extra-title { color: #5a8a1e; }
/* アフターケア見出し：PC(960px+)の2列時のみ1行固定 */
@media (min-width: 960px) {
	.pega-reasons__extra--aftercare .pega-reasons__extra-title {
		white-space: nowrap;
	}
}
.pega-reasons__extra-text {
	margin: 0;
	color: var(--pega-text-sub);
	font-size: clamp(14px, 2.05vw, 15px);
	line-height: 1.85;
}
@media (min-width: 768px) {
	.pega-reasons__card--featured .pega-reasons__card-img img {
		height: 100%;
		min-height: 260px;
		border-bottom: none;
		border-right: 3px solid var(--pega-base);
	}
}
@media (min-width: 768px) and (max-width: 959px) {
	.pega-reasons__card--featured .pega-reasons__card-img img {
		height: clamp(220px, 42vw, 300px);
		min-height: 0;
		border-bottom: 3px solid var(--pega-base);
		border-right: none;
	}
}
@media (min-width: 960px) {
	.pega-reasons__card:hover {
		transform: translateY(-3px);
		box-shadow: 0 10px 28px rgba(16, 103, 214, 0.12);
	}
	.pega-reasons__card--featured:hover {
		box-shadow: 0 12px 32px rgba(16, 103, 214, 0.14);
	}
	.pega-reasons__extra:hover {
		transform: translateY(-2px);
		box-shadow: 0 8px 22px rgba(16, 103, 214, 0.1);
	}
	.pega-reasons__extra {
		transition: transform 0.2s ease, box-shadow 0.2s ease;
	}
}
PEGA_LAYER_EOF;
	wp_register_style( 'pega-css-05-reasons', false, array( 'pega-css-04-homeworker' ), null );
	wp_enqueue_style( 'pega-css-05-reasons' );
	wp_add_inline_style( 'pega-css-05-reasons', pega_css_min( $css ) );
}, 12 );
