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
	position: relative;
	top: auto;
	left: auto;
	right: auto;
	z-index: 20;
	background: var(--pega-base);
	backdrop-filter: none;
	-webkit-backdrop-filter: none;
	border-bottom: 1px solid rgba(255, 255, 255, 0.12);
	box-shadow: 0 2px 12px rgba(8, 40, 100, 0.14);
	margin-bottom: 0;
	padding-bottom: 0;
}
.pega-top #header .l-header__inner {
	background: transparent;
	max-width: none;
	padding-top: 12px;
	padding-bottom: 12px;
	border-bottom: none;
}
.pega-top #header .l-header__logo {
	background: transparent;
	padding: 0;
	margin-right: clamp(16px, 2.5vw, 36px);
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
	color: #fff;
}
.pega-top #header .c-headLogo__img {
	display: block;
	max-height: 46px;
	width: auto;
	max-width: min(240px, 32vw);
	object-fit: contain;
	/* カラーPNGロゴを白抜き相当に（青ヘッダー上の視認性） */
	filter: brightness(0) invert(1) drop-shadow(0 1px 3px rgba(0, 0, 0, 0.18));
}
.pega-top #header .c-gnav > li > a .ttl {
	font-family: var(--pega-font);
	font-weight: 600;
	color: #fff;
}
.pega-top #header .c-gnav > li > a::after {
	background: var(--pega-accent);
}
.pega-top #header .c-iconBtn,
.pega-top #header .c-iconBtn__icon {
	color: #fff !important;
}
.pega-top #header .c-gnav > li.menu-item-1206 > a .ttl {
	color: var(--pega-accent) !important;
	font-weight: 700;
}
.pega-top #header .c-gnav .sub-menu {
	background: rgba(255, 255, 255, 0.97);
	border: 1px solid rgba(16, 103, 214, 0.14);
	border-radius: 8px;
	box-shadow: 0 10px 28px rgba(8, 40, 100, 0.14);
	overflow: hidden;
	padding: 6px 0;
}
.pega-top #header .c-gnav .sub-menu a,
.pega-top #header .c-gnav .sub-menu a .ttl {
	color: var(--pega-text) !important;
	font-weight: 500;
}
.pega-top #header .c-gnav .sub-menu a:hover,
.pega-top #header .c-gnav .sub-menu a:hover .ttl {
	background: rgba(16, 103, 214, 0.08) !important;
	color: var(--pega-base) !important;
}
.pega-header-contact {
	display: none;
	flex-direction: row;
	align-items: center;
	justify-content: flex-end;
	gap: clamp(8px, 2vw, 14px);
	margin: 0 0 0 auto;
	font-family: var(--pega-font);
	flex-shrink: 1;
	min-width: 0;
}
.pega-header-contact__hours,
.pega-header-contact__tel {
	margin: 0;
	font-size: 10px;
	line-height: 1.45;
	color: rgba(255, 255, 255, 0.92);
	white-space: nowrap;
}
.pega-header-contact__label {
	display: inline-block;
	margin-right: 4px;
	padding: 1px 5px;
	border-radius: 4px;
	background: rgba(255, 255, 255, 0.16);
	font-size: 9px;
	font-weight: 700;
	letter-spacing: 0.06em;
}
.pega-header-contact__tel a {
	color: #fff;
	font-weight: 700;
	font-size: clamp(12px, 3.2vw, 14px);
	text-decoration: none;
	letter-spacing: 0.03em;
}
.pega-header-contact__tel a:hover {
	text-decoration: underline;
}
.pega-header-contact__link {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	flex-shrink: 0;
	margin: 0;
	padding: 8px 12px;
	border-radius: 999px;
	background: var(--pega-accent);
	color: #fff;
	font-size: 11px;
	font-weight: 700;
	text-decoration: none;
	line-height: 1.3;
	white-space: nowrap;
	box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
}
.pega-header-contact__link:hover {
	filter: brightness(1.06);
}
@media (max-width: 600px) {
	.pega-header-contact {
		display: none !important;
	}
}
@media (min-width: 601px) and (max-width: 959px) {
	.pega-header-contact {
		display: flex;
	}
}
@media (min-width: 960px) and (max-width: 1349px) {
	.pega-top #header .l-header__logo {
		flex: 1;
		flex-direction: row;
		flex-wrap: nowrap;
		align-items: center;
		justify-content: space-between;
		margin-right: 0;
		min-width: 0;
	}
	.pega-top #header .c-headLogo__link {
		flex-shrink: 0;
		max-width: min(200px, 28vw);
	}
}
@media (max-width: 380px) {
	.pega-header-contact__hours {
		font-size: 9px;
	}
	.pega-header-contact__link {
		padding: 7px 10px;
		font-size: 10px;
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
#header .l-header__menuBtn .c-iconBtn,
#header .l-header__menuBtn .c-iconBtn__icon,
#header .l-header__customBtn .c-iconBtn,
#header .l-header__customBtn .c-iconBtn__icon {
	color: #fff !important;
}
@media (min-width: 960px) and (max-width: 1349px) {
	.pega-top #header .l-header__inner {
		display: flex !important;
		align-items: center;
	}
.pega-top #header .l-header__logo {
		flex: 1;
		display: flex;
		flex-direction: row;
		justify-content: space-between;
		align-items: center;
		margin-right: 0;
		min-width: 0;
	}
	.pega-top #header .c-headLogo__link {
		max-width: min(300px, 54vw);
	}
}
@media (min-width: 1350px) {
	.pega-top #header .c-gnav > li > a {
		font-size: 12.5px;
		padding-left: 0.6em;
		padding-right: 0.6em;
		white-space: nowrap;
	}
	.pega-top #header .c-headLogo__link {
		font-size: 14px;
	}
}
@media (min-width: 1440px) {
	.pega-top #header .c-gnav > li > a {
		font-size: 13.5px;
		padding-left: 0.8em;
		padding-right: 0.8em;
	}
}
@media (min-width: 1600px) {
	.pega-top #header .c-gnav > li > a {
		font-size: 14px;
		padding-left: 1em;
		padding-right: 1em;
	}
}
@media (min-width: 1440px) {
	.pega-top #header .c-headLogo__img {
		max-height: 50px;
		max-width: 260px;
	}
}
@media (min-width: 1920px) {
	.pega-top #header .c-gnav > li > a {
		font-size: 15px;
		padding-left: 1.2em;
		padding-right: 1.2em;
	}
}
CSS;
	wp_register_style( 'pega-css-10-header', false, array( 'child_style' ), null );
	wp_enqueue_style( 'pega-css-10-header' );
	wp_add_inline_style( 'pega-css-10-header', $css );
}, 22 );
