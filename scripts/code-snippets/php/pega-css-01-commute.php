<?php
/*
 * ▼ペガサス様 編集ガイド ─ 「通所案内バナー」セクションの装飾
 *
 *   色や余白を変えたい時は、下の  $css = <<<'CSS'  から  CSS;  までの間を書き換えて
 *   「変更を保存」を押してください。
 *   ・このスニペットは、この1セクションだけの見た目です。オフにすると、このセクションの
 *     装飾だけが外れます（他のセクションやサイト全体には影響しません）。
 *   ・色そのものの共通設定は「pega-css-00-tokens」にあります。
 */
add_action( 'wp_enqueue_scripts', function () {
	$css = <<<'CSS'
.pega-commute {
	background: #f8f7f4;
	padding: 0;
	font-family: var(--pega-font);
	border-top: 1px solid rgba(0, 0, 0, 0.06);
}

.pega-commute__banner {
	margin: 0;
	padding: clamp(20px, 3.5vw, 28px) clamp(20px, 3.5vw, 28px);
	background: #fff;
	border: 1px solid rgba(0, 0, 0, 0.08);
	border-radius: var(--pega-card-radius);
	box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
}

.pega-commute__thumb img {
	display: block;
	width: 100%;
	height: clamp(48px, 10vw, 60px);
	object-fit: cover;
	object-position: center 20%;
	border-radius: 8px;
	border: 1px solid rgba(0, 0, 0, 0.08);
	box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
}

.pega-commute__title {
	margin: 0 0 clamp(8px, 1.5vw, 10px);
	color: var(--pega-text);
	font-weight: 700;
	font-size: clamp(15px, 2.2vw, 17px);
	line-height: 1.55;
	letter-spacing: 0.02em;
}

.pega-commute__text {
	margin: 0 0 clamp(6px, 1.2vw, 8px);
	color: var(--pega-text-sub);
	font-size: clamp(14px, 2vw, 15px);
	line-height: 1.75;
}

.pega-commute__note {
	margin: 0;
	color: #6b7280;
	font-size: clamp(13px, 1.9vw, 14px);
	line-height: 1.7;
}

.pega-commute__cta .swell-block-button__link {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	padding: 10px 18px;
	border-radius: 8px;
	background: #f3f2ef !important;
	color: var(--pega-text) !important;
	border: 1px solid rgba(0, 0, 0, 0.12);
	font-size: clamp(13px, 1.9vw, 14px);
	font-weight: 600;
	text-decoration: none;
	box-shadow: none;
	transition: background 0.2s ease, border-color 0.2s ease;
	white-space: nowrap;
}

.pega-commute__cta .swell-block-button__link:hover {
	background: #ebeae6 !important;
	border-color: rgba(0, 0, 0, 0.18);
	transform: none;
	filter: none;
	box-shadow: none;
}
CSS;
	wp_register_style( 'pega-css-01-commute', false, array( 'child_style' ), null );
	wp_enqueue_style( 'pega-css-01-commute' );
	wp_add_inline_style( 'pega-css-01-commute', $css );
}, 13 );
