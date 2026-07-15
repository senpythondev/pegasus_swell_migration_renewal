<?php
/*
 * ▼ペガサス様 編集ガイド ─ 「フッター」の装飾
 *
 *   色や背景・枠・影を変えたい時は、下の  $css = <<<'CSS'  から  CSS;  までの間を
 *   書き換えて「変更を保存」を押してください。
 *   ・このスニペットは、フッターというこの1コンポーネントだけの見た目です。
 *     オフにすると、フッターの装飾だけが外れます（レイアウト骨格や他の場所には影響しません）。
 *   ・色そのものの共通設定は「pega-css-00-tokens」にあります。
 */
add_action( 'wp_enqueue_scripts', function () {
	$css = <<<'CSS'
.pega-footer {
	color: rgba(255, 255, 255, 0.92);
}
#footer.l-footer:has(.pega-footer) {
	background: transparent;
	border: none;
	font-family: var(--pega-font);
}
#footer.l-footer:has(.pega-footer) .l-footer__inner.pega-footer__inner {
	background: var(--pega-base-dark);
	border: none;
}
.pega-footer__commute {
	background: #dce8f5;
	border-top: none;
	border-bottom: 3px solid var(--pega-base-dark);
}
.pega-footer__commute-title {
	font-weight: 700;
	font-size: clamp(15px, 2.4vw, 17px);
	color: var(--pega-base-ink);
	letter-spacing: 0.02em;
}
.pega-footer__commute-text {
	font-size: clamp(13px, 2.1vw, 14px);
	line-height: 1.75;
	color: var(--pega-text-sub);
}
.pega-footer__commute-btn {
	font-family: var(--pega-font);
	font-size: 14px;
	font-weight: 700;
	line-height: 1.4;
	text-decoration: none;
	border-radius: 999px;
	border: 2px solid transparent;
	transition: filter 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease, background 0.2s ease;
}
.pega-footer__commute-btn--access {
	background: var(--pega-base) !important;
	color: #fff !important;
	box-shadow: 0 4px 16px rgba(16, 103, 214, 0.3);
	border-color: rgba(255, 255, 255, 0.2);
}
.pega-footer__commute-btn--access:hover {
	filter: brightness(1.06);
	box-shadow: 0 6px 20px rgba(16, 103, 214, 0.38);
	transform: translateY(-1px);
	color: #fff !important;
}
.pega-footer__logo .c-headLogo__link {
	background: transparent;
}
.pega-footer__logo .c-headLogo__img {
	filter: brightness(0) invert(1);
	opacity: 0.95;
}
.pega-footer__office {
	font-style: normal;
	font-size: 13px;
	line-height: 1.85;
	color: rgba(255, 255, 255, 0.78);
}
.pega-footer__office-name {
	font-weight: 700;
	font-size: 13px;
	color: rgba(255, 255, 255, 0.55);
	letter-spacing: 0.06em;
	border-bottom: 1px solid rgba(255, 255, 255, 0.12);
}
.pega-footer__office-line a {
	color: rgba(255, 255, 255, 0.88);
	text-decoration: none;
	border-bottom: 1px solid rgba(255, 255, 255, 0.25);
	transition: color 0.2s ease, border-color 0.2s ease;
}
.pega-footer__office-line a:hover {
	color: #fff;
	border-color: rgba(255, 255, 255, 0.5);
}
.pega-footer__nav-heading {
	font-weight: 700;
	font-size: 13px;
	color: rgba(255, 255, 255, 0.55);
	letter-spacing: 0.06em;
	border-bottom: 1px solid rgba(255, 255, 255, 0.12);
}
.pega-footer__nav-list a {
	font-size: 13px;
	line-height: 1.55;
	color: rgba(255, 255, 255, 0.82);
	text-decoration: none;
	transition: color 0.2s ease;
}
.pega-footer__nav-list a:hover {
	color: #fff;
}
.pega-footer__meta {
	border-top: 1px solid rgba(255, 255, 255, 0.12);
}
.pega-footer__sns {
	color: rgba(255, 255, 255, 0.82);
	text-decoration: none;
	font-size: 13px;
	transition: color 0.2s ease;
}
.pega-footer__sns:hover {
	color: #fff;
}
.pega-footer__copyright {
	font-size: 12px;
	line-height: 1.6;
	color: rgba(255, 255, 255, 0.5);
}
CSS;
	wp_register_style( 'pega-css-11-footer', false, array( 'child_style' ), null );
	wp_enqueue_style( 'pega-css-11-footer' );
	wp_add_inline_style( 'pega-css-11-footer', $css );
}, 23 );
