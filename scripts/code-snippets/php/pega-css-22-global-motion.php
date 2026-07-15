<?php
/*
 * ▼ペガサス様 編集ガイド ─ スクロール出現アニメーション（控えめ）
 *
 *   下の  $css = <<<'CSS'  から  CSS;  までの間が CSS（見た目）です。
 *   色や余白を変えたい時は、その中を書き換えて「変更を保存」を押してください。
 *   ・このスニペット単体をオフにしても、他の部分には影響しません。
 *     （この設定の見た目だけが外れます）
 *   ・色などの共通設定は「pega-css-00-tokens」にまとまっています。
 *   これはスクロール時のふわっと表示の設定です。
 */
add_action( 'wp_enqueue_scripts', function () {
	$css = <<<'CSS'
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
CSS;
	wp_register_style( 'pega-css-22-global-motion', false, array( 'child_style' ), null );
	wp_enqueue_style( 'pega-css-22-global-motion' );
	wp_add_inline_style( 'pega-css-22-global-motion', $css );
}, 32 );
