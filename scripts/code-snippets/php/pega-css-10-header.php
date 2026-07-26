<?php
/*
 * ▼ペガサス様 編集ガイド ─ 「ヘッダー」セクションの装飾
 *
 *   色や余白を変えたい時は、下の  $css = <<<'CSS'  から  CSS;  までの間を書き換えて
 *   「変更を保存」を押してください。
 *   ・このスニペットは、この1セクションだけの見た目です。オフにすると、このセクションの
 *     装飾だけが外れます（他のセクションやサイト全体には影響しません）。
 *   ・色そのものの共通設定は「pega-css-00-tokens」にあります。
 */
add_action( 'wp_enqueue_scripts', function () {
	$css = <<<'CSS'
.pega-top #header {
	background: #fff;
	border-bottom: 1px solid var(--pega-border);
	box-shadow: 0 1px 8px rgba(8, 40, 100, 0.06);
	margin-bottom: 0;
	padding-bottom: 0;
}
.pega-top #header .l-header__inner {
	background: transparent;
	max-width: none;
	padding-top: 10px;
	padding-bottom: 10px;
	border-bottom: none;
}
.pega-top #header .l-header__logo {
	background: transparent;
	padding: 0;
	margin-right: clamp(12px, 2vw, 28px);
	flex-shrink: 0;
}
.pega-top #header .c-headLogo__link {
	display: inline-flex;
	align-items: center;
	background: transparent;
	padding: 0;
	border-radius: 0;
	font-family: var(--pega-font);
	font-weight: 700;
	color: var(--pega-text);
}
.pega-top #header .c-headLogo__img {
	display: block;
	max-height: 46px;
	width: auto;
	max-width: min(240px, 32vw);
	object-fit: contain;
	filter: none;
}
.pega-header-contact {
	display: none;
	flex-direction: row;
	align-items: center;
	justify-content: flex-end;
	gap: clamp(8px, 1.5vw, 12px);
	margin: 0;
	font-family: var(--pega-font);
	flex-shrink: 1;
	min-width: 0;
}
.pega-header-contact__hours,
.pega-header-contact__tel {
	margin: 0;
	font-size: 10px;
	line-height: 1.45;
	color: var(--pega-text-sub);
	white-space: nowrap;
}
.pega-header-contact__label {
	display: inline-block;
	margin-right: 4px;
	padding: 1px 5px;
	border-radius: 4px;
	background: rgba(16, 103, 214, 0.08);
	color: var(--pega-base);
	font-size: 9px;
	font-weight: 700;
	letter-spacing: 0.06em;
}
.pega-header-contact__tel a {
	color: var(--pega-base);
	font-weight: 700;
	font-size: clamp(14px, 2.2vw, 18px);
	text-decoration: none;
	letter-spacing: 0.03em;
}
.pega-header-contact__tel a:hover {
	text-decoration: underline;
}
.pega-header-contact__tel-icon {
	width: 44px;
	height: 44px;
	border-radius: 50%;
	background: var(--pega-base);
	color: #fff;
	text-decoration: none;
}
.pega-header-contact__tel-icon .icon-phone {
	font-size: 18px;
	line-height: 1;
	color: #fff;
}
.pega-header__cta-item {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	padding: 7px 14px;
	border-radius: 999px;
	font-family: var(--pega-font);
	font-size: 11px;
	font-weight: 700;
	line-height: 1.35;
	text-decoration: none;
	white-space: nowrap;
	border: 1px solid transparent;
	transition: filter 0.15s ease, box-shadow 0.15s ease;
}
.pega-header__cta-item:hover {
	filter: brightness(1.04);
}
.pega-header__cta-item--apply {
	background: rgba(253, 117, 124, 0.12);
	border-color: rgba(253, 117, 124, 0.28);
	color: var(--pega-second);
}
.pega-header__cta-item--download {
	background: rgba(141, 199, 60, 0.14);
	border-color: rgba(141, 199, 60, 0.28);
	color: var(--pega-accent);
}
.pega-header__cta-item--contact {
	background: rgba(16, 103, 214, 0.08);
	border-color: rgba(16, 103, 214, 0.2);
	color: var(--pega-base);
}
.pega-top #header .l-header__menuBtn .c-iconBtn {
	background: var(--pega-base);
	border-radius: 8px;
	color: #fff;
}
.pega-top #header .l-header__menuBtn .c-iconBtn__icon {
	color: #fff;
}
.pega-top #header .l-header__customBtn .c-iconBtn,
.pega-top #header .l-header__customBtn .c-iconBtn__icon {
	color: var(--pega-base);
}
.pega-top #header .l-header__menuBtn.sp_ {
	display: flex !important;
}
@media (min-width: 601px) and (max-width: 959px) {
	.pega-header-contact {
		display: flex;
	}
}
@media (min-width: 960px) {
	.pega-top #header .c-headLogo__img {
		max-height: 48px;
		max-width: min(260px, 28vw);
	}
}
@media (min-width: 1440px) {
	.pega-top #header .c-headLogo__img {
		max-height: 50px;
		max-width: 260px;
	}
	.pega-header__cta-item {
		font-size: 12px;
		padding: 8px 16px;
	}
}
#sp_menu.p-spMenu {
	color: var(--pega-text) !important;
}
.p-spMenu__inner {
	background-color: #f8f9fb !important;
	color: var(--pega-text) !important;
	box-shadow: 0 0 24px rgba(8, 40, 100, 0.16) !important;
}
.p-spMenu__inner::before {
	display: block !important;
	content: "" !important;
	background: linear-gradient(
		180deg,
		rgba(255, 255, 255, 0.98) 0%,
		rgba(248, 249, 251, 0.96) 100%
	) !important;
	opacity: 1 !important;
}
.p-spMenu__body,
.p-spMenu__nav,
.p-spMenu .c-listMenu.c-spnav {
	background: transparent !important;
	color: var(--pega-text) !important;
}
.p-spMenu .c-widget__title.-spmenu {
	margin: 0 -4vw 1.25em;
	padding: 14px 4vw;
	background: var(--pega-base) !important;
	color: #fff !important;
	font-family: var(--pega-font);
	font-weight: 700;
	font-size: 15px;
	letter-spacing: 0.14em;
	text-align: center;
	border-bottom: none;
}
.p-spMenu .c-spnav a,
.p-spMenu .c-listMenu a,
.p-spMenu a {
	color: var(--pega-text) !important;
	background: transparent !important;
	border-bottom-color: rgba(26, 26, 26, 0.08) !important;
	font-family: var(--pega-font);
	font-weight: 500;
}
.p-spMenu .c-spnav a:hover,
.p-spMenu .c-listMenu a:hover {
	background: rgba(16, 103, 214, 0.08) !important;
	color: var(--pega-base) !important;
}
.p-spMenu .c-spnav .current-menu-item > a,
.p-spMenu .c-listMenu .current-menu-item > a {
	color: var(--pega-base-ink) !important;
	background: rgba(141, 199, 60, 0.12) !important;
	box-shadow: inset 3px 0 0 var(--pega-accent);
}
.p-spMenu .c-submenuToggleBtn {
	color: var(--pega-base) !important;
}
.p-spMenu .c-submenuToggleBtn.is-opened {
	color: var(--pega-base-ink) !important;
}
.p-spMenu .sub-menu {
	position: relative;
	transform: none;
	width: auto;
	box-shadow: none;
	padding-left: 0;
	background: #f0f2f5 !important;
}
.p-spMenu .sub-menu a {
	font-size: 0.92em;
	color: var(--pega-text-sub) !important;
	border-bottom-color: rgba(26, 26, 26, 0.06) !important;
}
.p-spMenu .sub-menu a:hover {
	color: var(--pega-base) !important;
	background: rgba(16, 103, 214, 0.06) !important;
}
.p-spMenu__closeBtn .c-iconBtn,
.p-spMenu__closeBtn .c-iconBtn__icon {
	color: var(--pega-base) !important;
}
.p-spMenu__overlay {
	background: rgba(8, 30, 70, 0.22) !important;
	opacity: 1 !important;
	backdrop-filter: blur(8px) saturate(1.05);
	-webkit-backdrop-filter: blur(8px) saturate(1.05);
}
CSS;
	wp_register_style( 'pega-css-10-header', false, array( 'child_style' ), null );
	wp_enqueue_style( 'pega-css-10-header' );
	wp_add_inline_style( 'pega-css-10-header', $css );
}, 22 );
