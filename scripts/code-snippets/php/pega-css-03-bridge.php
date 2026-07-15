<?php
/*
 * ▼ペガサス様 編集ガイド ─ 「課題→解決ブリッジ」セクションの装飾
 *
 *   色や余白を変えたい時は、下の  $css = <<<'CSS'  から  CSS;  までの間を書き換えて
 *   「変更を保存」を押してください。
 *   ・このスニペットは、この1セクションだけの見た目です。オフにすると、このセクションの
 *     装飾だけが外れます（他のセクションやサイト全体には影響しません）。
 *   ・色そのものの共通設定は「pega-css-00-tokens」にあります。
 */
add_action( 'wp_enqueue_scripts', function () {
	$css = <<<'CSS'
.pega-bridge {
	background: linear-gradient(180deg, #f3f7fc 0%, #e8f0fa 55%, #dce8f8 100%);
	padding: 0;
	font-family: var(--pega-font);
}
.pega-bridge__title {
	margin: 0 0 clamp(24px, 4vw, 36px);
	color: var(--pega-text);
	font-weight: 800;
	font-size: clamp(22px, 4.5vw, 30px);
	line-height: 1.55;
	letter-spacing: 0.03em;
}
.pega-bridge__text {
	margin: 0 0 clamp(18px, 3vw, 24px);
	color: var(--pega-text-sub);
	font-size: clamp(15px, 2.2vw, 17px);
	line-height: 1.9;
}
.pega-bridge__pillar {
	width: 100%;
	margin: 0;
	padding: 0;
	background: #fff;
	border: 1px solid rgba(16, 103, 214, 0.12);
	border-radius: 16px;
	box-shadow: 0 4px 18px rgba(16, 103, 214, 0.07);
	text-align: center;
	overflow: hidden;
	transition: transform 0.2s ease, box-shadow 0.2s ease;
	height: 100%;
	min-height: clamp(300px, 52vw, 380px);
}
.pega-bridge__pillar--self .pega-bridge__pillar-img::after {
	background: linear-gradient(180deg, rgba(16, 103, 214, 0.06) 0%, rgba(16, 103, 214, 0.12) 100%);
}
.pega-bridge__pillar--skill .pega-bridge__pillar-img::after {
	background: linear-gradient(180deg, rgba(141, 199, 60, 0.05) 0%, rgba(141, 199, 60, 0.11) 100%);
}
.pega-bridge__pillar--support .pega-bridge__pillar-img::after {
	background: linear-gradient(180deg, rgba(253, 117, 124, 0.05) 0%, rgba(253, 117, 124, 0.1) 100%);
}
.pega-bridge__pillar-img img {
	display: block;
	width: 100%;
	height: 100%;
	object-fit: cover;
	object-position: center center;
	border-radius: 0;
}
.pega-bridge__pillar--self .pega-bridge__pillar-img img {
	border-bottom: 3px solid var(--pega-base);
}
.pega-bridge__pillar--skill .pega-bridge__pillar-img img {
	border-bottom: 3px solid var(--pega-accent);
}
.pega-bridge__pillar--support .pega-bridge__pillar-img img {
	border-bottom: 3px solid var(--pega-second);
}
.pega-bridge__pillar-label {
	margin: 0 0 6px;
	padding: 0;
	color: var(--pega-text-sub);
	font-size: 14px;
	font-weight: 700;
	letter-spacing: 0.1em;
	line-height: 1.3;
	text-transform: none;
	min-height: 1.3em;
	flex-shrink: 0;
	white-space: nowrap;
}
.pega-bridge__pillar--self .pega-bridge__pillar-label { color: var(--pega-base); }
.pega-bridge__pillar--skill .pega-bridge__pillar-label { color: #5a8a1e; }
.pega-bridge__pillar--support .pega-bridge__pillar-label { color: #c94a50; }
.pega-bridge__pillar-title {
	margin: 0;
	padding: 0;
	color: var(--pega-text);
	font-weight: 700;
	font-size: clamp(14px, 2.2vw, 16px);
	line-height: 1.45;
	min-height: 2.9em;
	display: flex;
	align-items: center;
	justify-content: center;
	width: 100%;
	word-break: keep-all;
	overflow-wrap: normal;
	text-wrap: pretty;
}
@media (min-width: 600px) and (max-width: 1100px) {
	.pega-bridge__pillar-title {
		font-size: 14px;
		letter-spacing: 0;
		padding-inline: 2px;
	}
}
.pega-bridge__closing {
	padding: clamp(36px, 6vw, 56px) clamp(20px, 4vw, 40px);
	background: linear-gradient(
		135deg,
		var(--pega-base) 0%,
		var(--pega-base-ink) 100%
	);
}
.pega-bridge__closing-photo {
	margin: 0 auto clamp(20px, 4vw, 32px) !important;
	max-width: min(560px, 100%);
	width: 100%;
	overflow: hidden;
	border-radius: 14px;
	box-shadow:
		0 8px 28px rgba(0, 0, 0, 0.22),
		0 0 0 3px rgba(255, 255, 255, 0.18);
}
.pega-bridge__closing-title {
	margin: 0 0 clamp(16px, 3vw, 24px);
	width: 100%;
	max-width: 720px;
	color: #fff;
	font-weight: 800;
	font-size: clamp(22px, 4.8vw, 34px);
	line-height: 1.6;
	letter-spacing: 0.04em;
	text-align: center !important;
	text-shadow: 0 2px 12px rgba(0, 0, 0, 0.18);
}
.pega-bridge__closing-lead {
	margin: 0 0 clamp(14px, 2.5vw, 18px);
	width: 100%;
	max-width: 680px;
	color: rgba(255, 255, 255, 0.94);
	font-size: clamp(15px, 2.2vw, 17px);
	line-height: 1.9;
	text-align: center !important;
}
.pega-bridge__detail {
	width: 100%;
	height: 100%;
	margin: 0;
	padding: clamp(22px, 4vw, 28px) clamp(20px, 3.5vw, 26px);
	background: #fff;
	border: 1px solid rgba(16, 103, 214, 0.1);
	border-radius: 16px;
	box-shadow: 0 4px 18px rgba(16, 103, 214, 0.06);
	transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.pega-bridge__detail--rhythm {
	border-top: 4px solid var(--pega-base);
}
.pega-bridge__detail--skill {
	border-top: 4px solid var(--pega-accent);
}
.pega-bridge__detail--practice {
	border-top: 4px solid var(--pega-second);
}
.pega-bridge__detail-title {
	margin: 0 0 clamp(12px, 2vw, 16px);
	font-size: clamp(17px, 2.6vw, 20px);
	font-weight: 800;
	line-height: 1.5;
	letter-spacing: 0.02em;
}
.pega-bridge__detail--rhythm .pega-bridge__detail-title { color: var(--pega-base-ink); }
.pega-bridge__detail--skill .pega-bridge__detail-title { color: #5a8a1e; }
.pega-bridge__detail--practice .pega-bridge__detail-title { color: #c94a50; }
.pega-bridge__detail-text {
	margin: 0;
	color: var(--pega-text-sub);
	font-size: clamp(14px, 2.1vw, 16px);
	line-height: 1.9;
}
@media (min-width: 960px) {
	.pega-bridge__pillar:hover {
		transform: translateY(-3px);
		box-shadow: 0 10px 28px rgba(16, 103, 214, 0.12);
	}
	.pega-bridge__detail:hover {
		transform: translateY(-2px);
		box-shadow: 0 8px 24px rgba(16, 103, 214, 0.1);
	}
}
CSS;
	wp_register_style( 'pega-css-03-bridge', false, array( 'child_style' ), null );
	wp_enqueue_style( 'pega-css-03-bridge' );
	wp_add_inline_style( 'pega-css-03-bridge', $css );
}, 15 );
