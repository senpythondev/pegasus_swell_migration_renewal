<?php
/*
 * ▼ペガサス様 編集ガイド ─ 「CTA（まとめ）」セクションの装飾
 *
 *   色や余白を変えたい時は、下の  $css = <<<'CSS'  から  CSS;  までの間を書き換えて
 *   「変更を保存」を押してください。
 *   ・このスニペットは、この1セクションだけの見た目です。オフにすると、このセクションの
 *     装飾だけが外れます（他のセクションやサイト全体には影響しません）。
 *   ・色そのものの共通設定は「pega-css-00-tokens」にあります。
 */
add_action( 'wp_enqueue_scripts', function () {
	$css = <<<'CSS'
.pega-closing {
	background: linear-gradient(180deg, #eef3f9 0%, #e4edf8 55%, #dce8f5 100%);
	padding: 0;
	font-family: var(--pega-font);
	border-top: 1px solid rgba(16, 103, 214, 0.08);
}
.pega-closing__panel {
	margin: 0;
	padding: clamp(36px, 5vw, 48px) clamp(24px, 4vw, 40px);
	background: rgba(255, 255, 255, 0.88);
	border: 1px solid rgba(16, 103, 214, 0.12);
	border-radius: 20px;
	box-shadow: 0 8px 32px rgba(16, 103, 214, 0.08);
}
.pega-closing__title {
	margin: 0 0 clamp(16px, 3vw, 22px);
	color: var(--pega-base-ink);
	font-weight: 800;
	font-size: clamp(22px, 3.8vw, 30px);
	line-height: 1.55;
	letter-spacing: 0.03em;
}
.pega-closing__lead {
	margin: 0 0 clamp(28px, 5vw, 36px);
	color: var(--pega-text-sub);
	font-size: clamp(15px, 2.2vw, 17px);
	line-height: 2;
}
.pega-closing__btn .swell-block-button__link {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	width: 100%;
	min-height: 56px;
	padding: clamp(16px, 2.8vw, 20px) clamp(28px, 4.5vw, 36px);
	border-radius: 999px;
	font-family: var(--pega-font);
	font-size: clamp(16px, 2.3vw, 17px);
	font-weight: 700;
	line-height: 1.45;
	text-decoration: none;
	border: 2px solid transparent;
	transition: filter 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
}
.pega-closing__btn--primary .swell-block-button__link {
	background: var(--pega-accent) !important;
	color: #fff !important;
	box-shadow: 0 6px 22px rgba(141, 199, 60, 0.42);
	border-color: rgba(255, 255, 255, 0.3);
	font-size: clamp(17px, 2.5vw, 18px);
	min-height: 60px;
}
.pega-closing__btn--primary .swell-block-button__link:hover {
	filter: brightness(1.06);
	box-shadow: 0 8px 26px rgba(141, 199, 60, 0.5);
}
.pega-closing__btn--contact .swell-block-button__link {
	background: var(--pega-second) !important;
	color: #fff !important;
	box-shadow: 0 6px 20px rgba(253, 117, 124, 0.36);
	border-color: rgba(255, 255, 255, 0.25);
	min-height: 56px;
}
.pega-closing__btn--contact .swell-block-button__link:hover {
	filter: brightness(1.05);
	box-shadow: 0 8px 24px rgba(253, 117, 124, 0.42);
}
/* 資料請求ボタン：白地＋オレンジ枠全周（3ボタンとして明確・均一なアウトライン） */
.pega-closing__btn--download .swell-block-button__link {
	background: #fff !important;
	color: var(--pega-orange-ink) !important;
	border: 2px solid var(--pega-orange) !important;
	box-shadow: 0 4px 14px rgba(255, 140, 66, 0.14);
	min-height: 56px;
}
.pega-closing__btn--download .swell-block-button__link:hover {
	background: rgba(255, 140, 66, 0.08) !important;
	color: var(--pega-orange-ink) !important;
	border-color: var(--pega-orange-ink) !important;
	box-shadow: 0 6px 18px rgba(255, 140, 66, 0.2);
}
.pega-closing__btn .swell-block-button__link:hover {
	transform: translateY(-1px);
}
.pega-closing__btn .swell-block-button__link:focus-visible {
	outline: 3px solid rgba(16, 103, 214, 0.35);
	outline-offset: 3px;
}
.pega-closing__tel {
	margin: clamp(22px, 4vw, 28px) 0 0;
	color: #6b7280;
	font-size: clamp(13px, 1.9vw, 14px);
	line-height: 1.7;
}
.pega-closing__tel a {
	color: var(--pega-base-ink);
	text-decoration: none;
	border-bottom: 1px solid rgba(16, 103, 214, 0.2);
	padding-bottom: 1px;
}
.pega-closing__tel a:hover {
	color: var(--pega-base);
	border-color: rgba(16, 103, 214, 0.4);
}
CSS;
	wp_register_style( 'pega-css-08-closing', false, array( 'child_style' ), null );
	wp_enqueue_style( 'pega-css-08-closing' );
	wp_add_inline_style( 'pega-css-08-closing', $css );
}, 20 );
