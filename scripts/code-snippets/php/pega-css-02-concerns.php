<?php
/*
 * ▼ペガサス様 編集ガイド ─ 「悩み共感」セクションの装飾
 *
 *   色や余白を変えたい時は、下の  $css = <<<'CSS'  から  CSS;  までの間を書き換えて
 *   「変更を保存」を押してください。
 *   ・このスニペットは、この1セクションだけの見た目です。オフにすると、このセクションの
 *     装飾だけが外れます（他のセクションやサイト全体には影響しません）。
 *   ・色そのものの共通設定は「pega-css-00-tokens」にあります。
 */
add_action( 'wp_enqueue_scripts', function () {
	$css = <<<'CSS'
.pega-concerns {
	background: linear-gradient(180deg, #fff 0%, var(--pega-soft-blue) 100%);
	padding: clamp(48px, 8vw, 88px) clamp(20px, 4vw, 40px);
	font-family: var(--pega-font);
}

.pega-concerns__title {
	margin: 0 0 clamp(16px, 2.5vw, 24px);
	color: var(--pega-base);
	font-weight: 800;
	font-size: clamp(22px, 4.5vw, 32px);
	line-height: 1.55;
	letter-spacing: 0.04em;
}

.pega-concerns__title::after {
	content: "";
	display: block;
	width: 48px;
	height: 3px;
	margin: clamp(12px, 2vw, 16px) auto 0;
	border-radius: 2px;
	background: linear-gradient(90deg, var(--pega-base), var(--pega-second));
	opacity: 0.45;
}

.pega-concerns__lead {
	margin: 0 auto clamp(32px, 5vw, 48px);
	max-width: 36em;
	color: var(--pega-text-sub);
	font-size: clamp(15px, 2.2vw, 17px);
	line-height: 1.85;
}

/* リスト全体：淡い青の面で視覚的フック（セクション内のみ） */
.pega-concerns .pega-concerns__list > .wp-block-group__inner-container {
	background: var(--pega-soft-blue);
	border-radius: calc(var(--pega-card-radius) + 4px);
	padding: clamp(14px, 2.5vw, 20px);
}

.pega-concerns__item {
	margin: 0;
	padding: clamp(16px, 2.5vw, 20px) clamp(18px, 3vw, 24px);
	background: #fff;
	border: 1px solid rgba(16, 103, 214, 0.1);
	border-radius: var(--pega-card-radius);
	box-shadow: 0 2px 10px rgba(16, 103, 214, 0.05);
	transition: box-shadow 0.2s ease, border-color 0.2s ease;
}

/* アイコン + テキスト（横並び・中央揃え・折り返し安全） */
.pega-concerns .pega-concerns__card-body {
	display: flex;
	align-items: center;
	gap: clamp(12px, 2vw, 16px);
	min-width: 0;
}

.pega-concerns .pega-concerns__icon {
	flex-shrink: 0;
	display: flex;
	align-items: center;
	justify-content: center;
	width: clamp(36px, 6vw, 44px);
	height: clamp(36px, 6vw, 44px);
	border-radius: 10px;
	background: var(--pega-soft-blue);
	color: var(--pega-base);
}

.pega-concerns .pega-concerns__icon svg {
	display: block;
	width: clamp(18px, 3vw, 22px);
	height: clamp(18px, 3vw, 22px);
}

.pega-concerns .pega-concerns__icon svg path,
.pega-concerns .pega-concerns__icon svg circle,
.pega-concerns .pega-concerns__icon svg rect {
	stroke: currentColor;
	fill: none;
	stroke-width: 1.5;
	stroke-linecap: round;
	stroke-linejoin: round;
}

.pega-concerns .pega-concerns__text {
	margin: 0;
	flex: 1;
	min-width: 0;
	background: transparent;
	color: var(--pega-text);
	font-size: clamp(15px, 2.1vw, 16px);
	font-weight: 500;
	line-height: 1.75;
	text-wrap: pretty;
}

/* 「か。」だけが孤立して改行されるのを防ぐ */
.pega-concerns .pega-concerns__keep {
	white-space: nowrap;
}

/* 旧ドット装飾は非表示（global-deco 互換） */
.pega-concerns__item p::before {
	display: none;
}

/* 注目カード：アイコン色のみ差別化（本文の白背景強調は付けない） */
.pega-concerns .pega-concerns__item--featured .pega-concerns__icon {
	background: var(--pega-soft-green);
	color: var(--pega-accent);
}

/* ── swell-page お悩み（wave-section / 4941） ── */
.wave-section .wp-block-columns {
	gap: clamp(12px, 2vw, 20px);
}

.wave-section .wp-block-column {
	display: flex;
}

.wave-section .wave-box {
	flex: 1;
	width: 100%;
}

.wave-section .wave-box .wp-block-image {
	display: flex;
	justify-content: center;
	margin-left: auto;
	margin-right: auto;
}

.wave-section .wave-box .wp-block-image img {
	width: 75px;
	height: auto;
}

.wave-section .wave-box h4 {
	margin-bottom: 0.35em;
}

.wave-section .wave-box__desc-spacer {
	visibility: hidden;
	min-height: calc(12px * 1.7);
	margin: 0;
}

@media (min-width: 782px) {
	.wave-section .wave-cards-row-center {
		max-width: calc(50% + 10px);
		margin-left: auto;
		margin-right: auto;
	}
}

@media (max-width: 768px) and (min-width: 426px) {
	.wave-section .wp-block-columns {
		display: grid !important;
		grid-template-columns: repeat(2, minmax(0, 1fr));
	}

	.wave-section .wp-block-columns > .wp-block-column {
		width: auto !important;
		flex-basis: auto !important;
		margin-left: 0 !important;
	}

	.wave-section .wave-cards-row-center {
		max-width: none;
	}
}

@media (max-width: 425px) {
	.wave-section .wp-block-columns {
		display: flex !important;
		flex-direction: column;
	}

	.wave-section .wp-block-columns > .wp-block-column {
		width: 100% !important;
	}
}

/* swell-page (4929) — デスクトップ帯域(1025px+) お悩みカード高さ統一（項目6） */
@media (min-width: 1025px) {
	.page-id-4929 .wave-section .wp-block-columns.are-vertically-aligned-center {
		align-items: stretch !important;
	}

	.page-id-4929 .wave-section .wp-block-columns > .wp-block-column {
		display: flex !important;
		align-self: stretch !important;
	}

	.page-id-4929 .wave-section .wp-block-column.is-vertically-aligned-center {
		align-self: stretch !important;
	}

	.page-id-4929 .wave-section .wave-box {
		display: flex;
		flex-direction: column;
		flex: 1 1 auto;
		width: 100%;
		height: 100%;
		box-sizing: border-box;
	}
}
CSS;
	wp_register_style( 'pega-css-02-concerns', false, array( 'child_style' ), null );
	wp_enqueue_style( 'pega-css-02-concerns' );
	wp_add_inline_style( 'pega-css-02-concerns', $css );
}, 14 );
