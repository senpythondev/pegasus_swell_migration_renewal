<?php
/*
 * ▼ペガサス様 編集ガイド ─ 「追従CTA（スクロール追従ボタン）」の装飾
 *
 *   ボタンの色や背景・枠・角丸・影を変えたい時は、下の  $css = <<<'CSS'  から  CSS;  までの間を
 *   書き換えて「変更を保存」を押してください。
 *   ・このスニペットは、追従CTAというこの1コンポーネントだけの見た目です。
 *     オフにすると、追従CTAの装飾だけが外れます。表示・非表示の動作や位置は、
 *     子テーマ側（構造）に残してあるので保持されます。
 *   ・色そのものの共通設定は「pega-css-00-tokens」にあります。
 */
add_action( 'wp_enqueue_scripts', function () {
	$css = <<<'CSS'
.pega-sticky-cta {
	font-family: var(--pega-font);
}
.pega-sticky-cta__close {
	border: none;
	border-radius: 50%;
	background: rgba(16, 103, 214, 0.08);
	color: #6b7280;
	font-size: 18px;
	line-height: 1;
	transition: background 0.2s ease, color 0.2s ease;
}
.pega-sticky-cta__close:hover {
	background: rgba(16, 103, 214, 0.14);
	color: var(--pega-base-ink);
}
.pega-sticky-cta__btn {
	border-radius: 999px;
	font-size: 13px;
	font-weight: 700;
	line-height: 1.35;
	text-decoration: none;
	text-align: center;
	border: 2px solid transparent;
	transition: filter 0.2s ease, box-shadow 0.2s ease;
}
.pega-sticky-cta__btn--primary {
	background: var(--pega-accent) !important;
	color: #fff !important;
	box-shadow: 0 3px 12px rgba(141, 199, 60, 0.32);
}
.pega-sticky-cta__btn--primary:hover {
	filter: brightness(1.05);
	color: #fff !important;
}
.pega-sticky-cta__btn--contact {
	background: var(--pega-second) !important;
	color: #fff !important;
	box-shadow: 0 3px 12px rgba(253, 117, 124, 0.28);
}
.pega-sticky-cta__btn--contact:hover {
	filter: brightness(1.04);
	color: #fff !important;
}
@media (max-width: 959px) {
	.pega-sticky-cta {
		background: rgba(255, 255, 255, 0.97);
		border-top: 1px solid rgba(16, 103, 214, 0.1);
		box-shadow: 0 -4px 18px rgba(16, 103, 214, 0.08);
	}
}
@media (min-width: 960px) {
	.pega-sticky-cta {
		background: rgba(255, 255, 255, 0.96);
		border: 1px solid rgba(16, 103, 214, 0.12);
		border-radius: 14px;
		box-shadow: 0 6px 24px rgba(16, 103, 214, 0.1);
	}
	.pega-sticky-cta__btn {
		font-size: 14px;
	}
}
CSS;
	wp_register_style( 'pega-css-12-sticky-cta', false, array( 'child_style' ), null );
	wp_enqueue_style( 'pega-css-12-sticky-cta' );
	wp_add_inline_style( 'pega-css-12-sticky-cta', $css );
}, 24 );
