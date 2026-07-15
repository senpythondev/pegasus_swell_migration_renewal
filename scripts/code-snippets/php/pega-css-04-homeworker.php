<?php
// Code Snippets(無料版) PHPスニペット原本: pega-css-04-homeworker / scope=global / active
// 実体はDB(wp04447asnippets)。この原本はバージョン管理・再投入用。
// pega-css-04-homeworker — CSS3層 ② セクション: 在宅紹介(homeworker)
// 読込順: pega-css-03-bridge の後（依存チェーンで順序保証）。
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
   pega-sec-homeworker — 在宅型就労移行支援 紹介の装飾（③ / site-css / priority 10）
   構造（幅・grid/flex・hero/videoの箱組み・画像/embed比率・@media骨格）は子テーマに残置。
   横断の最終調整は pega-global-*（優先度30/31）が担当。
   section固有の角丸(16px)・中間色・左線色は維持。border-radius:12pxのみ--pega-card-radius化。
   ========================================================= */

.pega-homeworker {
	background: #fff;
	padding: 0;
	font-family: var(--pega-font);
	border-top: 1px solid rgba(16, 103, 214, 0.08);
}
.pega-homeworker__eyebrow {
	margin: 0 0 10px;
	color: var(--pega-accent);
	font-size: 13px;
	font-weight: 700;
	letter-spacing: 0.14em;
}

/* 主役セクションのラベル：緑ドット＋淡いオレンジの輪（控えめアクセント） */
.pega-homeworker__eyebrow::before {
	content: "";
	display: inline-block;
	width: 6px;
	height: 6px;
	margin-right: 8px;
	vertical-align: middle;
	border-radius: 50%;
	background: var(--pega-accent);
	border: none;
	box-shadow: 0 0 0 2px var(--pega-orange-soft);
}
.pega-homeworker__title {
	margin: 0 0 clamp(18px, 3vw, 24px);
	color: var(--pega-text);
	font-weight: 800;
	font-size: clamp(24px, 5vw, 36px);
	line-height: 1.45;
	letter-spacing: 0.03em;
}
.pega-homeworker__lead {
	margin: 0 auto clamp(24px, 4vw, 32px);
	max-width: 720px;
	color: var(--pega-text-sub);
	font-size: clamp(15px, 2.2vw, 17px);
	line-height: 1.9;
}
.pega-homeworker__highlight {
	margin: 0 auto clamp(32px, 5vw, 44px);
	max-width: 720px;
	padding: clamp(14px, 3vw, 18px) clamp(18px, 3.5vw, 24px);
	background: linear-gradient(135deg, rgba(16, 103, 214, 0.08) 0%, rgba(141, 199, 60, 0.1) 100%);
	border: 1px solid rgba(16, 103, 214, 0.14);
	border-radius: var(--pega-card-radius);
}
.pega-homeworker__highlight p {
	margin: 0;
	color: var(--pega-base-ink);
	font-size: clamp(14px, 2.1vw, 16px);
	font-weight: 700;
	line-height: 1.75;
	text-align: center;
}
.pega-homeworker__photo {
	margin: 0 !important;
	overflow: hidden;
	border-radius: 16px;
	box-shadow: 0 8px 28px rgba(16, 103, 214, 0.12);
}
.pega-homeworker__strength {
	margin: 0;
	padding: clamp(14px, 2.5vw, 18px) clamp(16px, 3vw, 20px);
	background: #f8fafc;
	border: 1px solid rgba(16, 103, 214, 0.1);
	border-radius: var(--pega-card-radius);
	border-left: 4px solid var(--pega-base);
}
.pega-homeworker__strength--2 { border-left-color: var(--pega-accent); }
.pega-homeworker__strength--3 { border-left-color: var(--pega-second); }
.pega-homeworker__strength--4 { border-left-color: var(--pega-base-ink); }
.pega-homeworker__strength p {
	margin: 0;
	color: var(--pega-text-sub);
	font-size: clamp(14px, 2vw, 15px);
	line-height: 1.75;
}
.pega-homeworker__strength strong {
	display: block;
	margin-bottom: 4px;
	color: var(--pega-text);
	font-size: clamp(15px, 2.2vw, 16px);
	font-weight: 700;
	line-height: 1.5;
}
.pega-homeworker__videos {
	padding: clamp(32px, 6vw, 48px) clamp(20px, 4vw, 40px);
	background: linear-gradient(180deg, #f3f7fc 0%, #eef4fb 100%);
}
.pega-homeworker__videos-title {
	margin: 0 0 clamp(24px, 4vw, 32px);
	color: var(--pega-text);
	font-weight: 800;
	font-size: clamp(20px, 3.8vw, 28px);
	line-height: 1.5;
}
.pega-homeworker__video-featured {
	margin: 0 0 clamp(28px, 5vw, 40px);
	padding: clamp(20px, 3.5vw, 28px);
	background: #fff;
	border: 2px solid var(--pega-base);
	border-radius: 16px;
	box-shadow: 0 6px 24px rgba(16, 103, 214, 0.1);
}
.pega-homeworker__video-label {
	margin: 0 0 8px;
	color: var(--pega-base);
	font-size: 12px;
	font-weight: 700;
	letter-spacing: 0.12em;
}
.pega-homeworker__video-heading {
	margin: 0 0 clamp(12px, 2vw, 16px);
	color: var(--pega-text);
	font-weight: 700;
	font-size: clamp(15px, 2.3vw, 18px);
	line-height: 1.55;
}
.pega-homeworker__video-featured .pega-homeworker__video-heading {
	font-size: clamp(16px, 2.5vw, 20px);
}
.pega-homeworker__video-card {
	height: 100%;
	margin: 0;
	padding: clamp(16px, 3vw, 20px);
	background: #fff;
	border: 1px solid rgba(16, 103, 214, 0.1);
	border-radius: 14px;
	box-shadow: 0 4px 16px rgba(16, 103, 214, 0.06);
}
.pega-homeworker__video-card .pega-homeworker__video-heading {
	font-size: clamp(14px, 2.1vw, 16px);
}
.pega-homeworker__cta .swell-block-button__link {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	min-width: min(100%, 320px);
	padding: clamp(14px, 2.5vw, 18px) clamp(28px, 5vw, 40px);
	border-radius: 999px;
	background: var(--pega-base) !important;
	color: #fff !important;
	font-size: clamp(15px, 2.2vw, 17px);
	font-weight: 700;
	text-decoration: none;
	box-shadow: 0 4px 16px rgba(16, 103, 214, 0.28);
	transition: filter 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
}
.pega-homeworker__cta .swell-block-button__link:hover {
	filter: brightness(1.06);
	transform: translateY(-1px);
	box-shadow: 0 6px 22px rgba(16, 103, 214, 0.34);
}
PEGA_LAYER_EOF;
	wp_register_style( 'pega-css-04-homeworker', false, array( 'pega-css-03-bridge' ), null );
	wp_enqueue_style( 'pega-css-04-homeworker' );
	wp_add_inline_style( 'pega-css-04-homeworker', pega_css_min( $css ) );
}, 12 );
