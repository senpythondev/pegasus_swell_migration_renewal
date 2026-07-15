<?php
// Code Snippets(無料版) PHPスニペット原本: pega-css-22-global-motion / scope=global / active
// 実体はDB(wp04447asnippets)。この原本はバージョン管理・再投入用。
// pega-css-22-global-motion — CSS3層 ③ 横断: アニメーション(motion)
// 読込順: pega-css-21-global-deco の後（依存チェーンで順序保証）。
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
	wp_register_style( 'pega-css-22-global-motion', false, array( 'pega-css-21-global-deco' ), null );
	wp_enqueue_style( 'pega-css-22-global-motion' );
	wp_add_inline_style( 'pega-css-22-global-motion', pega_css_min( $css ) );
}, 12 );
