<?php
/*
 * ▼ペガサス様 編集ガイド ─ 横断レイアウト調整（複数セクション共通の調整）
 *
 *   色や余白を変えたい時は、下の  $css = <<<'CSS'  から  CSS;  までの間を書き換えて
 *   「変更を保存」を押してください。
 *   ・これは複数のセクションに共通で効く「横断」設定です（全セクション共通の幅・余白）。
 *     オフにすると、この共通調整が全セクションから外れます（各スニペット自体は壊れません）。
 *   ・色そのものの共通設定は「pega-css-00-tokens」にあります。
 */
add_action( 'wp_enqueue_scripts', function () {
	$css = <<<'CSS'
/* =========================================================
   TOP PAGE UNIFIED REFINEMENT — 余白・幅・装飾・階層の統一
   ========================================================= */

/* 調整1: ページトップボタンを追従CTAの上へ（追従CTA位置は維持）
   ※ pega-has-sticky-cta は body、home は #body_wrap に付与 */
body.pega-has-sticky-cta .home .p-fixBtnWrap {
	transition: bottom 0.3s ease, opacity 0.25s, visibility 0.25s;
}

@media (max-width: 959px) {
	body.pega-has-sticky-cta .home .p-fixBtnWrap {
		bottom: calc(78px + env(safe-area-inset-bottom, 0px));
	}
}

@media (min-width: 960px) {
	body.pega-has-sticky-cta .home .p-fixBtnWrap {
		bottom: calc(220px + 0.5em);
		right: calc(2.5em + (100vw - 100%));
	}
}

.home .pega-concerns > .wp-block-group__inner-container {
	max-width: var(--pega-wrap-wide);
}

.home .pega-bridge > .wp-block-group__inner-container,
.home .pega-reasons > .wp-block-group__inner-container,
.home .pega-voices > .wp-block-group__inner-container,
.home .pega-posts > .wp-block-group__inner-container,
.home .pega-homeworker > .wp-block-group__inner-container,
.home .pega-closing > .wp-block-group__inner-container {
	max-width: var(--pega-wrap-content) !important;
	margin-left: auto !important;
	margin-right: auto !important;
	width: 100% !important;
}

.home .pega-bridge__intro,
.home .pega-homeworker__intro,
.home .pega-reasons__intro,
.home .pega-voices__intro,
.home .pega-posts__intro {
	padding: var(--pega-section-py) 0 var(--pega-section-py-sm);
}

.home .pega-bridge__pillars-wrap,
.home .pega-bridge__details-wrap,
.home .pega-reasons__main-wrap,
.home .pega-reasons__extras-wrap,
.home .pega-voices__wrap,
.home .pega-posts__wrap,
.home .pega-homeworker__hero,
.home .pega-homeworker__videos,
.home .pega-homeworker__cta-wrap {
	padding-left: 0;
	padding-right: 0;
}

.home .pega-bridge__pillars-wrap,
.home .pega-bridge__details-wrap,
.home .pega-reasons__main-wrap,
.home .pega-reasons__extras-wrap,
.home .pega-voices__wrap,
.home .pega-posts__wrap {
	padding-bottom: var(--pega-section-py);
}

.home .pega-bridge__pillars-wrap {
	padding-top: 0;
}

.home .pega-homeworker__videos {
	padding-bottom: var(--pega-section-py-sm);
}

.home .pega-voices {
	padding-left: var(--pega-section-px);
	padding-right: var(--pega-section-px);
}

.home .pega-commute__wrap {
	max-width: var(--pega-wrap-content);
	padding-left: var(--pega-section-px);
	padding-right: var(--pega-section-px);
	margin-left: auto;
	margin-right: auto;
}

.home .pega-closing__wrap {
	padding: var(--pega-section-py-sm) 0;
}

/* 調整7: カード縦長の改善 */
.home .pega-bridge__pillar {
	min-height: 0;
}

.home .pega-bridge__pillar > .wp-block-group__inner-container {
	grid-template-rows: auto auto;
	min-height: 0;
}

.home .pega-bridge__pillar-img {
	height: auto;
	max-height: 200px;
	aspect-ratio: 4 / 3;
}

.home .pega-bridge__pillar-body,
.home .pega-bridge__pillar-body > .wp-block-group__inner-container {
	padding: var(--pega-card-padding);
}

.home .pega-bridge__detail,
.home .pega-reasons__card-body,
.home .pega-reasons__card-body > .wp-block-group__inner-container,
.home .pega-homeworker__strength,
.home .pega-concerns__item {
	padding: var(--pega-card-padding);
}

.home .pega-reasons__card-body > .wp-block-group__inner-container {
	max-width: none !important;
	width: 100%;
	min-width: 0;
}

.home .pega-voices__card {
	padding: var(--pega-card-padding);
}

.home .pega-posts-card__date {
	padding: 0 var(--pega-card-padding) var(--pega-card-padding);
}

.home .pega-reasons__card-img img {
	height: clamp(160px, 36vw, 200px);
}

.home .pega-reasons__card--featured .pega-reasons__card-img img {
	height: clamp(180px, 40vw, 220px);
}

@media (min-width: 768px) and (max-width: 959px) {
	.home .pega-reasons__card--featured .pega-reasons__card-img img {
		width: 100%;
		height: clamp(220px, 42vw, 300px);
	}
}

@media (min-width: 960px) {
	.home .pega-bridge__pillar {
		min-height: 0;
	}
}

/* =========================================================
   LAYOUT FIXES — 幅統一・位置・カード内余白・ボーダー
   ========================================================= */

/* WordPress constrained の contentSize を上書きし、全セクション同一幅に */
.home .wp-block-group[class*="pega-"] > .wp-block-group__inner-container {
	max-width: var(--pega-wrap-content) !important;
	margin-left: auto !important;
	margin-right: auto !important;
	width: 100%;
}

/* ヒーローはフル幅のまま（キャッチコピー右配置を維持） */
.home .pega-hero-wrap .wp-block-group__inner-container,
.home .pega-hero-wrap .wp-block-group[class*="pega-"] > .wp-block-group__inner-container {
	max-width: none !important;
	margin-left: 0 !important;
	margin-right: 0 !important;
	width: 100% !important;
}

/* constrained ブロック自体の幅制限を解除（padding 二重化を防ぐ） */
.home :is(
	.pega-bridge__intro,
	.pega-bridge__pillars-wrap,
	.pega-bridge__details-wrap,
	.pega-reasons__intro,
	.pega-reasons__main-wrap,
	.pega-reasons__extras-wrap,
	.pega-voices__intro,
	.pega-voices__wrap,
	.pega-posts__intro,
	.pega-posts__wrap,
	.pega-homeworker__intro,
	.pega-homeworker__hero,
	.pega-homeworker__videos,
	.pega-homeworker__cta-wrap,
	.pega-closing__wrap,
	.pega-commute__wrap
) {
	max-width: none !important;
	width: 100% !important;
	margin-left: 0 !important;
	margin-right: 0 !important;
}

.home .pega-concerns > .wp-block-group__inner-container,
.home .pega-commute__wrap,
.home .pega-closing__wrap {
	max-width: var(--pega-wrap-content);
	margin-left: auto;
	margin-right: auto;
	width: 100%;
}

.home .pega-commute {
	padding: var(--pega-section-py-sm) 0;
}

.home .pega-commute__wrap {
	padding-left: var(--pega-section-px);
	padding-right: var(--pega-section-px);
}

.home .pega-commute__banner {
	padding: var(--pega-card-padding);
}

/* フッター下端の余白を除去（画面下端との隙間のみ） */
.home #footer.l-footer:has(.pega-footer) {
	margin-bottom: 0 !important;
	padding-bottom: 0 !important;
}

/* swell-page (4929) — 実績バー・波型装飾（rev5239 ブロックCSS復旧） */
.page-id-4929 .pega-stats.u-mb-ctrl {
	padding: 10px !important;
}
.page-id-4929 .pega-stats .achivement .wp-block-columns {
	display: flex !important;
}
.page-id-4929 figure:has(.wp-image-5098),
.page-id-4929 .wp-block-image.wp-image-5098 {
	margin: 0 !important;
	padding: 0 !important;
}

/* swell-page (4929) — デスクトップ帯域(961px+) 実績バー調整（項目2・3・4・5） */
@media (min-width: 961px) {
	.page-id-4929 .pega-stats.u-mb-ctrl {
		padding: 0 10px 10px !important;
		background: transparent !important;
	}

	.page-id-4929 .pega-stats .achivement {
		padding: 10px 18px 8px !important;
	}

	.page-id-4929 .pega-stats .achivement .box-achievement > .wp-block-columns {
		gap: 6px !important;
		justify-content: center;
		width: fit-content;
		max-width: 100%;
		margin-left: auto;
		margin-right: auto;
	}

	.page-id-4929 .pega-stats .achivement .box-achievement .wp-block-columns > .wp-block-column {
		flex-basis: auto !important;
		flex-grow: 0;
		width: auto !important;
	}

	.page-id-4929 .pega-stats .achivement .box-achievement .wp-block-image {
		margin: 0 !important;
	}

	.page-id-4929 .pega-stats .achivement .box-achievement .wp-block-image img {
		display: block;
		width: auto;
		max-height: 64px;
		height: auto;
	}

	.page-id-4929 .pega-stats .achivement .box-achievement .wp-block-column.is-vertically-aligned-center > p.has-text-align-left:first-of-type {
		font-size: 13px !important;
		line-height: 1.2 !important;
	}

	.page-id-4929 .pega-stats .achivement .box-achievement .number-text {
		font-size: 38px !important;
		line-height: 1 !important;
	}

	.page-id-4929 .pega-stats .achivement .box-achievement .wp-block-column.is-vertically-aligned-center > p.has-text-align-left + p {
		font-size: 24px !important;
		line-height: 1.05 !important;
	}

	.page-id-4929 .pega-stats .achivement .box-achievement .wp-block-column.is-vertically-aligned-center p + p {
		margin-top: 0 !important;
	}

	.page-id-4929 .pega-stats .achivement .bottom-text {
		margin-top: 6px !important;
		padding-bottom: 0;
		font-size: 10px !important;
		line-height: 1.3 !important;
	}

	/* 項目4: 末尾の区切り線を除去 */
	.page-id-4929 .pega-stats .achivement .box-achievement:last-child {
		border-right: none !important;
	}

	/* 項目5: 各区画を水平中央揃え */
	.page-id-4929 .pega-stats .achivement .box-achievement {
		display: flex;
		justify-content: center;
		align-items: center;
	}

	/* FV 下端へ実績バーを約半分重ねる（高さ127px基準 → 50%） */
	.page-id-4929 .pega-stats .achivement {
		margin-top: -64px;
		position: relative;
		z-index: 5;
	}

	.page-id-4929 figure:has(.wp-image-5098),
	.page-id-4929 .wp-block-image.wp-image-5098 {
		position: relative;
		z-index: 4;
	}
}

/* swell-page (4929) — SP 実績バー（.achivement 統一・3区画横並び / 区内縦積み） */
@media (max-width: 960px) {
	.page-id-4929 .pega-stats.u-mb-ctrl {
		padding: 24px 12px 32px;
	}

	.page-id-4929 .pega-stats .achivement {
		margin-top: 0;
		padding: 20px 8px 16px;
		border-radius: 10px;
	}

	.page-id-4929 .pega-stats .achivement > .wp-block-columns {
		/* WP 782px 未満で columns が column 化するため !important */
		flex-direction: row !important;
		flex-wrap: nowrap;
		gap: 0;
		margin-bottom: 0;
	}

	.page-id-4929 .pega-stats .achivement .box-achievement {
		flex: 1 1 33.33%;
		flex-basis: 33.33%;
		max-width: 33.33%;
		min-width: 0;
		padding: 0 2px;
	}

	.page-id-4929 .pega-stats .achivement .box-achievement > .wp-block-columns {
		flex-direction: column;
		align-items: center;
		gap: 4px;
	}

	.page-id-4929 .pega-stats .achivement .box-achievement .wp-block-columns > .wp-block-column {
		flex-basis: 100%;
		width: 100%;
	}

	.page-id-4929 .pega-stats .achivement .box-achievement .wp-block-image {
		margin: 0 auto;
	}

	.page-id-4929 .pega-stats .achivement .box-achievement .wp-block-image img {
		display: block;
		width: auto;
		max-height: 36px;
		height: auto;
		margin: 0 auto;
	}

	.page-id-4929 .pega-stats .achivement .box-achievement .wp-block-column.is-vertically-aligned-center > p.has-text-align-left:first-of-type {
		font-size: 10px;
		line-height: 1.2;
		text-align: center;
		margin-bottom: 0;
	}

	.page-id-4929 .pega-stats .achivement .box-achievement .number-text {
		font-size: 22px;
		line-height: 1;
	}

	.page-id-4929 .pega-stats .achivement .box-achievement .wp-block-column.is-vertically-aligned-center > p.has-text-align-left + p {
		font-size: 14px;
		line-height: 1.1;
		text-align: center;
	}

	.page-id-4929 .pega-stats .achivement .bottom-text {
		margin-top: 10px;
		font-size: 9px;
		line-height: 1.3;
		text-align: center;
	}
}
CSS;
	wp_register_style( 'pega-css-20-global-layout', false, array( 'child_style' ), null );
	wp_enqueue_style( 'pega-css-20-global-layout' );
	wp_add_inline_style( 'pega-css-20-global-layout', $css );
}, 30 );
