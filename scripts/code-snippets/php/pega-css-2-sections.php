<?php
// Code Snippets(無料版) PHPスニペット原本: pega-css-2-sections / scope=global / active
// ※実体はDB(wp04447asnippets)。この原本はバージョン管理・再投入用。
// pega-css-2-sections — CSS3層② 各セクション装飾（commute/concerns/bridge/homeworker/reasons/voices/posts/closing/hero/header）
// トークンの後・横断の前に読み込み（pega-css-tokens 依存）。
if ( ! function_exists( 'pega_css_min' ) ) {
	function pega_css_min( $css ) {
		// 安全なミニファイ（見た目に無影響）: コメント除去→行頭インデント除去→空行圧縮→末尾;除去
		$css = preg_replace( '~/\*.*?\*/~s', '', $css );
		$css = preg_replace( '~^[ \t]+~m', '', $css );
		$css = preg_replace( '~\n\s*\n~', "\n", $css );
		$css = preg_replace( '~;\s*}~', '}', $css );
		return trim( $css );
	}
}
add_action( 'wp_enqueue_scripts', function () {
	$css = <<<'PEGA_LAYER_EOF'
/* =========================================================
   pega-sec-commute — 通所案内バナー（③装飾レイヤー / site-css / priority 10）
   -----------------------------------------------------------
   CSS3層移行 Step2（commuteパイロット）で子テーマから外出しした「装飾」CSS。
   構造（grid/flex骨格・幅・@media・inner-container余白）は子テーマ style.css に残す。
   commute は「控えめサブ動線」として意図的に中間色（グレー系）を使うため、
   青系カードトークン（--pega-card-shadow 等）ではなく元のハードコード値を維持
   （＝見た目を一切変えない）。値が完全一致する border-radius のみ var() 化。
   ========================================================= */

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

/* =========================================================
   pega-sec-concerns — 悩み共感セクションの装飾（③ / site-css / priority 10）
   構造（幅・grid・inner余白・@media骨格）は子テーマ style.css に残置。
   横断の最終調整は pega-global-*（優先度30/31）が担当。
   border-radius:12px のみ --pega-card-radius 参照（値一致）。他はsection固有値を維持。
   ========================================================= */

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
/* =========================================================
   pega-sec-bridge — 課題→解決ブリッジの装飾（③ / site-css / priority 10）
   構造（幅・grid/flex骨格・pillar/detailの箱組み・画像サイズ・@media骨格・
   ::after位置指定基底）は子テーマ style.css に残置。
   横断の最終調整は pega-global-*（優先度30/31）が担当。
   bridge固有の角丸(16px/14px)・中間色は維持（値一致なし）。
   ========================================================= */

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
/* =========================================================
   pega-sec-homeworker — 在宅型就労移行支援 紹介の装飾（③ / site-css / priority 10）
   構造（幅・grid/flex・hero/videoの箱組み・画像/embed比率・@media骨格）は子テーマに残置。
   横断の最終調整は pega-global-*（優先度30/31）が担当。
   section固有の角丸(16px)・中間色・左線色は維持。border-radius:12pxのみ--pega-card-radius化。
   ========================================================= */

.pega-homeworker {
	background: #fff;
	padding: 0;
	font-family: var(--pega-font);
	border-top: 1px solid rgba(16, 103, 214, 0.08);
}
.pega-homeworker__eyebrow {
	margin: 0 0 10px;
	color: var(--pega-accent);
	font-size: 13px;
	font-weight: 700;
	letter-spacing: 0.14em;
}

/* 主役セクションのラベル：緑ドット＋淡いオレンジの輪（控えめアクセント） */
.pega-homeworker__eyebrow::before {
	content: "";
	display: inline-block;
	width: 6px;
	height: 6px;
	margin-right: 8px;
	vertical-align: middle;
	border-radius: 50%;
	background: var(--pega-accent);
	border: none;
	box-shadow: 0 0 0 2px var(--pega-orange-soft);
}
.pega-homeworker__title {
	margin: 0 0 clamp(18px, 3vw, 24px);
	color: var(--pega-text);
	font-weight: 800;
	font-size: clamp(24px, 5vw, 36px);
	line-height: 1.45;
	letter-spacing: 0.03em;
}
.pega-homeworker__lead {
	margin: 0 auto clamp(24px, 4vw, 32px);
	max-width: 720px;
	color: var(--pega-text-sub);
	font-size: clamp(15px, 2.2vw, 17px);
	line-height: 1.9;
}
.pega-homeworker__highlight {
	margin: 0 auto clamp(32px, 5vw, 44px);
	max-width: 720px;
	padding: clamp(14px, 3vw, 18px) clamp(18px, 3.5vw, 24px);
	background: linear-gradient(135deg, rgba(16, 103, 214, 0.08) 0%, rgba(141, 199, 60, 0.1) 100%);
	border: 1px solid rgba(16, 103, 214, 0.14);
	border-radius: var(--pega-card-radius);
}
.pega-homeworker__highlight p {
	margin: 0;
	color: var(--pega-base-ink);
	font-size: clamp(14px, 2.1vw, 16px);
	font-weight: 700;
	line-height: 1.75;
	text-align: center;
}
.pega-homeworker__photo {
	margin: 0 !important;
	overflow: hidden;
	border-radius: 16px;
	box-shadow: 0 8px 28px rgba(16, 103, 214, 0.12);
}
.pega-homeworker__strength {
	margin: 0;
	padding: clamp(14px, 2.5vw, 18px) clamp(16px, 3vw, 20px);
	background: #f8fafc;
	border: 1px solid rgba(16, 103, 214, 0.1);
	border-radius: var(--pega-card-radius);
	border-left: 4px solid var(--pega-base);
}
.pega-homeworker__strength--2 { border-left-color: var(--pega-accent); }
.pega-homeworker__strength--3 { border-left-color: var(--pega-second); }
.pega-homeworker__strength--4 { border-left-color: var(--pega-base-ink); }
.pega-homeworker__strength p {
	margin: 0;
	color: var(--pega-text-sub);
	font-size: clamp(14px, 2vw, 15px);
	line-height: 1.75;
}
.pega-homeworker__strength strong {
	display: block;
	margin-bottom: 4px;
	color: var(--pega-text);
	font-size: clamp(15px, 2.2vw, 16px);
	font-weight: 700;
	line-height: 1.5;
}
.pega-homeworker__videos {
	padding: clamp(32px, 6vw, 48px) clamp(20px, 4vw, 40px);
	background: linear-gradient(180deg, #f3f7fc 0%, #eef4fb 100%);
}
.pega-homeworker__videos-title {
	margin: 0 0 clamp(24px, 4vw, 32px);
	color: var(--pega-text);
	font-weight: 800;
	font-size: clamp(20px, 3.8vw, 28px);
	line-height: 1.5;
}
.pega-homeworker__video-featured {
	margin: 0 0 clamp(28px, 5vw, 40px);
	padding: clamp(20px, 3.5vw, 28px);
	background: #fff;
	border: 2px solid var(--pega-base);
	border-radius: 16px;
	box-shadow: 0 6px 24px rgba(16, 103, 214, 0.1);
}
.pega-homeworker__video-label {
	margin: 0 0 8px;
	color: var(--pega-base);
	font-size: 12px;
	font-weight: 700;
	letter-spacing: 0.12em;
}
.pega-homeworker__video-heading {
	margin: 0 0 clamp(12px, 2vw, 16px);
	color: var(--pega-text);
	font-weight: 700;
	font-size: clamp(15px, 2.3vw, 18px);
	line-height: 1.55;
}
.pega-homeworker__video-featured .pega-homeworker__video-heading {
	font-size: clamp(16px, 2.5vw, 20px);
}
.pega-homeworker__video-card {
	height: 100%;
	margin: 0;
	padding: clamp(16px, 3vw, 20px);
	background: #fff;
	border: 1px solid rgba(16, 103, 214, 0.1);
	border-radius: 14px;
	box-shadow: 0 4px 16px rgba(16, 103, 214, 0.06);
}
.pega-homeworker__video-card .pega-homeworker__video-heading {
	font-size: clamp(14px, 2.1vw, 16px);
}
.pega-homeworker__cta .swell-block-button__link {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	min-width: min(100%, 320px);
	padding: clamp(14px, 2.5vw, 18px) clamp(28px, 5vw, 40px);
	border-radius: 999px;
	background: var(--pega-base) !important;
	color: #fff !important;
	font-size: clamp(15px, 2.2vw, 17px);
	font-weight: 700;
	text-decoration: none;
	box-shadow: 0 4px 16px rgba(16, 103, 214, 0.28);
	transition: filter 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
}
.pega-homeworker__cta .swell-block-button__link:hover {
	filter: brightness(1.06);
	transform: translateY(-1px);
	box-shadow: 0 6px 22px rgba(16, 103, 214, 0.34);
}
/* =========================================================
   pega-sec-reasons — 選ばれている理由の装飾（③ / site-css / priority 10）
   構造（幅・grid/flex・card/featured/extraの箱組み・画像サイズ・@media骨格）は子テーマに残置。
   横断の最終調整は pega-global-*（優先度30/31）が担当。section固有の中間色・角丸・左線色は維持。
   ========================================================= */

.pega-reasons {
	background: linear-gradient(180deg, #eef4fb 0%, #e4edf8 55%, #dce8f5 100%);
	padding: 0;
	font-family: var(--pega-font);
	border-top: 1px solid rgba(16, 103, 214, 0.08);
}
.pega-reasons__title {
	margin: 0;
	color: var(--pega-text);
	font-weight: 800;
	font-size: clamp(24px, 4.8vw, 34px);
	line-height: 1.45;
	letter-spacing: 0.03em;
}
.pega-reasons__card {
	width: 100%;
	height: 100%;
	margin: 0;
	padding: 0;
	background: #fff;
	border: 1px solid rgba(16, 103, 214, 0.12);
	border-radius: 16px;
	box-shadow: 0 4px 18px rgba(16, 103, 214, 0.07);
	overflow: hidden;
	transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.pega-reasons__card--featured {
	border-color: rgba(16, 103, 214, 0.22);
	box-shadow: 0 6px 24px rgba(16, 103, 214, 0.1);
}
.pega-reasons__card--1 .pega-reasons__card-img img { border-bottom: 3px solid var(--pega-base); }
.pega-reasons__card--2 .pega-reasons__card-img img { border-bottom: 3px solid var(--pega-accent); }
.pega-reasons__card--3 .pega-reasons__card-img img { border-bottom: 3px solid var(--pega-second); }
.pega-reasons__card-label {
	margin: 0 0 6px;
	font-size: 12px;
	font-weight: 700;
	letter-spacing: 0.12em;
}
.pega-reasons__card--1 .pega-reasons__card-label { color: var(--pega-base); }
.pega-reasons__card--2 .pega-reasons__card-label { color: #5a8a1e; }
.pega-reasons__card--3 .pega-reasons__card-label { color: #c94a50; }
.pega-reasons__card--featured .pega-reasons__card-title {
	font-size: clamp(17px, 2.6vw, 20px);
}
.pega-reasons__card-title {
	margin: 0 0 clamp(10px, 2vw, 14px);
	color: var(--pega-text);
	font-weight: 800;
	font-size: clamp(16px, 2.4vw, 18px);
	line-height: 1.5;
	word-break: normal;
	overflow-wrap: break-word;
	text-wrap: pretty;
}
.pega-reasons__card-text {
	margin: 0;
	color: var(--pega-text-sub);
	font-size: clamp(14px, 2.05vw, 15px);
	line-height: 1.85;
	width: 100%;
	max-width: none;
	text-align: left;
	word-break: normal;
	overflow-wrap: break-word;
	text-wrap: pretty;
}
.pega-reasons__extra {
	width: 100%;
	height: 100%;
	margin: 0;
	padding: clamp(22px, 4vw, 28px) clamp(20px, 3.5vw, 26px);
	background: #fff;
	border: 1px solid rgba(16, 103, 214, 0.1);
	border-radius: 14px;
	box-shadow: 0 4px 16px rgba(16, 103, 214, 0.06);
}
.pega-reasons__extra--support {
	border-left: 4px solid var(--pega-base);
}
.pega-reasons__extra--aftercare {
	border-left: 4px solid var(--pega-accent);
}
.pega-reasons__extra-title {
	margin: 0 0 clamp(10px, 2vw, 14px);
	font-weight: 800;
	font-size: clamp(16px, 2.4vw, 18px);
	line-height: 1.5;
}
.pega-reasons__extra--support .pega-reasons__extra-title { color: var(--pega-base-ink); }
.pega-reasons__extra--aftercare .pega-reasons__extra-title { color: #5a8a1e; }
/* アフターケア見出し：PC(960px+)の2列時のみ1行固定 */
@media (min-width: 960px) {
	.pega-reasons__extra--aftercare .pega-reasons__extra-title {
		white-space: nowrap;
	}
}
.pega-reasons__extra-text {
	margin: 0;
	color: var(--pega-text-sub);
	font-size: clamp(14px, 2.05vw, 15px);
	line-height: 1.85;
}
@media (min-width: 768px) {
	.pega-reasons__card--featured .pega-reasons__card-img img {
		height: 100%;
		min-height: 260px;
		border-bottom: none;
		border-right: 3px solid var(--pega-base);
	}
}
@media (min-width: 768px) and (max-width: 959px) {
	.pega-reasons__card--featured .pega-reasons__card-img img {
		height: clamp(220px, 42vw, 300px);
		min-height: 0;
		border-bottom: 3px solid var(--pega-base);
		border-right: none;
	}
}
@media (min-width: 960px) {
	.pega-reasons__card:hover {
		transform: translateY(-3px);
		box-shadow: 0 10px 28px rgba(16, 103, 214, 0.12);
	}
	.pega-reasons__card--featured:hover {
		box-shadow: 0 12px 32px rgba(16, 103, 214, 0.14);
	}
	.pega-reasons__extra:hover {
		transform: translateY(-2px);
		box-shadow: 0 8px 22px rgba(16, 103, 214, 0.1);
	}
	.pega-reasons__extra {
		transition: transform 0.2s ease, box-shadow 0.2s ease;
	}
}
/* =========================================================
   pega-sec-voices — 利用者・卒業生の声の装飾（③ / site-css / priority 10）
   構造（幅・grid/flex・card箱組み・引用符無効化・cite非表示・@media骨格）は子テーマに残置。
   横断の最終調整は pega-global-*（優先度30/31）が担当。section固有の中間色・角丸は維持。
   ========================================================= */

.pega-voices {
	background: #fff;
	padding: 0;
	font-family: var(--pega-font);
	border-top: 1px solid rgba(16, 103, 214, 0.08);
}
.pega-voices__title {
	margin: 0 0 clamp(12px, 2vw, 16px);
	color: var(--pega-base-ink);
	font-weight: 800;
	font-size: clamp(22px, 3.6vw, 28px);
	line-height: 1.45;
	letter-spacing: 0.03em;
}
.pega-voices__lead {
	margin: 0;
	color: var(--pega-text-sub);
	font-size: clamp(14px, 2.1vw, 16px);
	line-height: 1.8;
}
.pega-voices__policy-img img {
	display: block;
	width: 100%;
	max-width: 570px;
	height: auto;
	margin: 0 auto;
	border-radius: 10px;
	opacity: 0.94;
}
.pega-voices__group-label {
	margin: 0 0 clamp(14px, 2.5vw, 18px);
	color: var(--pega-base);
	font-size: 12px;
	font-weight: 700;
	letter-spacing: 0.14em;
	text-transform: none;
}
.pega-voices__group-label--grad {
	margin-top: clamp(28px, 5vw, 36px);
	color: #5a8a1e;
}
.pega-voices__card {
	margin: 0;
	padding: clamp(20px, 3.5vw, 26px) clamp(20px, 3.5vw, 24px) clamp(22px, 3.5vw, 26px);
	background: #fafbfd;
	border: 1px solid rgba(16, 103, 214, 0.1);
	border-radius: 14px;
	box-shadow: 0 2px 12px rgba(16, 103, 214, 0.05);
	position: relative;
}
.pega-voices__card--user {
	border-left: 4px solid var(--pega-base);
}
.pega-voices__card--grad {
	background: #fafcf7;
	border-color: rgba(141, 199, 60, 0.18);
	border-left: 4px solid var(--pega-accent);
}
.pega-voices__card::before {
	content: "\201C";
	position: absolute;
	top: clamp(12px, 2vw, 16px);
	right: clamp(16px, 3vw, 20px);
	color: rgba(16, 103, 214, 0.12);
	font-size: clamp(36px, 6vw, 48px);
	font-weight: 800;
	line-height: 1;
	pointer-events: none;
}
.pega-voices__card--grad::before {
	color: rgba(141, 199, 60, 0.18);
}
.pega-voices__meta {
	margin: 0 0 clamp(10px, 2vw, 12px);
	color: var(--pega-text-sub);
	font-size: clamp(12px, 1.9vw, 13px);
	font-weight: 700;
	letter-spacing: 0.04em;
}
.pega-voices__quote {
	margin: 0;
	padding: 0 !important;
	border: none;
	background: transparent !important;
}
.pega-voices__quote p {
	margin: 0;
	color: var(--pega-text);
	font-size: clamp(14px, 2.05vw, 15px);
	line-height: 1.85;
	font-style: normal;
}
.pega-voices__cta .swell-block-button__link {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	padding: 12px 24px;
	border-radius: 999px;
	background: var(--pega-base) !important;
	color: #fff !important;
	border: none;
	font-size: clamp(14px, 2vw, 15px);
	font-weight: 700;
	text-decoration: none;
	box-shadow: 0 4px 14px rgba(16, 103, 214, 0.22);
	transition: filter 0.2s ease, box-shadow 0.2s ease;
}
.pega-voices__cta .swell-block-button__link:hover {
	filter: brightness(1.06);
	box-shadow: 0 6px 18px rgba(16, 103, 214, 0.28);
}
.pega-voices__cta-sub {
	margin: 0;
	font-size: clamp(13px, 1.9vw, 14px);
	line-height: 1.6;
}
.pega-voices__cta-sub a {
	color: var(--pega-base-ink);
	text-decoration: underline;
	text-underline-offset: 3px;
}
.pega-voices__cta-sub a:hover {
	color: var(--pega-base);
}
@media (min-width: 960px) {
	.pega-voices__card:hover {
		transform: translateY(-2px);
		box-shadow: 0 8px 22px rgba(16, 103, 214, 0.1);
		transition: transform 0.2s ease, box-shadow 0.2s ease;
	}
	.pega-voices__card--grad:hover {
		box-shadow: 0 8px 22px rgba(141, 199, 60, 0.12);
	}
}

/* =========================================================
   pega-sec-posts — コラム・ブログ(動的Query Loop)の装飾（③ / site-css / priority 10）
   構造（幅・grid・card箱組み・thumb画像比率・Query Loopの骨格・@media）は子テーマに残置。
   横断の最終調整は pega-global-*（優先度30/31）が担当。card固有角丸14px・中間色は維持。
   ========================================================= */

.pega-posts {
	background: linear-gradient(180deg, #f4f7fb 0%, #eef3f9 100%);
	padding: 0;
	font-family: var(--pega-font);
	border-top: 1px solid rgba(16, 103, 214, 0.08);
}
.pega-posts__title {
	margin: 0 0 clamp(12px, 2vw, 16px);
	color: var(--pega-base-ink);
	font-weight: 800;
	font-size: clamp(22px, 3.6vw, 28px);
	line-height: 1.45;
	letter-spacing: 0.03em;
}
.pega-posts__lead {
	margin: 0;
	color: var(--pega-text-sub);
	font-size: clamp(14px, 2.1vw, 16px);
	line-height: 1.8;
}
.pega-posts__block-title {
	margin: 0 0 clamp(16px, 3vw, 20px);
	color: var(--pega-base-ink);
	font-weight: 800;
	font-size: clamp(17px, 2.6vw, 20px);
	line-height: 1.5;
}
.pega-posts__block--staff .pega-posts__block-title {
	color: #5a8a1e;
}
.pega-posts-card {
	height: 100%;
	margin: 0;
	padding: 0;
	background: #fff;
	border: 1px solid rgba(16, 103, 214, 0.1);
	border-radius: 14px;
	overflow: hidden;
	box-shadow: 0 2px 12px rgba(16, 103, 214, 0.05);
	transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.pega-posts-card__title {
	margin: 0;
	padding: clamp(14px, 2.5vw, 16px) clamp(14px, 2.5vw, 16px) clamp(8px, 1.5vw, 10px);
	font-size: clamp(14px, 2.05vw, 15px);
	font-weight: 700;
	line-height: 1.6;
	flex: 1 1 auto;
}
.pega-posts-card__title a {
	color: var(--pega-text);
	text-decoration: none;
	display: -webkit-box;
	-webkit-line-clamp: 2;
	-webkit-box-orient: vertical;
	overflow: hidden;
}
.pega-posts-card__title a:hover {
	color: var(--pega-base);
}
.pega-posts-card__date {
	margin: 0;
	padding: 0 clamp(14px, 2.5vw, 16px) clamp(14px, 2.5vw, 16px);
	color: #6b7280;
	font-size: clamp(12px, 1.8vw, 13px);
	line-height: 1.5;
}
.pega-posts__empty {
	margin: 0;
	padding: clamp(20px, 3vw, 24px);
	color: var(--pega-text-sub);
	font-size: 14px;
	text-align: center;
	background: rgba(255, 255, 255, 0.7);
	border-radius: var(--pega-card-radius);
}
.pega-posts__more-link {
	margin: 0;
	font-size: clamp(13px, 1.9vw, 14px);
	font-weight: 600;
}
.pega-posts__more-link a {
	color: var(--pega-base-ink);
	text-decoration: none;
	border-bottom: 1px solid rgba(16, 103, 214, 0.25);
	padding-bottom: 2px;
	transition: color 0.2s ease, border-color 0.2s ease;
}
.pega-posts__more-link a:hover {
	color: var(--pega-base);
	border-color: rgba(16, 103, 214, 0.45);
}
@media (min-width: 960px) {
	.pega-posts-card:hover {
		transform: translateY(-2px);
		box-shadow: 0 8px 22px rgba(16, 103, 214, 0.1);
	}
}

/* =========================================================
   pega-sec-closing — CTA(まとめ)の装飾（③ / site-css / priority 10）
   構造（幅・panel/actions/sub箱組み・grid/flex・@media骨格）は子テーマに残置。
   横断の最終調整は pega-global-*（優先度30/31）が担当。section固有の中間色・角丸は維持。
   ========================================================= */

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
/* =========================================================
   pega-sec-hero — ヒーロー(FV)+信頼指標バーの装飾（③ / site-css / priority 10）
   -----------------------------------------------------------
   セレクタ単位でレイヤーを固定（base と全@media上書きを同一レイヤーに）し、
   font-size/min-height/padding/背景位置 等の上書き連鎖を保全（見た目不変）。
   ここには「見た目」＝背景画像・右寄せ暗幕グラデ・見出し色/影・2段CTAボタン配色・
   信頼指標の色/枠 を集約。full-wide の高さ(min-height)は背景画像と一体のため deco 側。
   構造（コピー右配置・flex/grid骨格・inner余白・SWELL :before無効化・幅）は子テーマ残置。
   ========================================================= */

.pega-hero-wrap .swell-block-fullWide,
.pega-hero-wrap .wp-block-loos-full-wide,
.pega-hero-wrap .pega-hero-fv-bg {
	position: relative;
	min-height: min(88vh, 820px);
	display: flex;
	align-items: center;
	overflow: hidden;
	margin: 0 !important;
	padding-top: 0 !important;
	padding-bottom: 0 !important;
	background-color: #dce8f5;
	background-image: url("/wp-content/uploads/2026/07/pega-hero-fv-20260712.jpg") !important;
	background-size: cover !important;
	background-repeat: no-repeat !important;
	background-position: 24% center;
}
/* モバイル横〜小タブレット：右側を多く見せる（position のみ・cover は維持） */
@media (min-width: 600px) and (max-width: 767px) {
	.pega-hero-wrap .swell-block-fullWide,
	.pega-hero-wrap .wp-block-loos-full-wide,
	.pega-hero-wrap .pega-hero-fv-bg {
		background-position: 12% center;
	}
}
/* タブレット：右側を多く見せ、コピー用スペースを確保 */
@media (min-width: 768px) and (max-width: 1023px) {
	.pega-hero-wrap .swell-block-fullWide,
	.pega-hero-wrap .wp-block-loos-full-wide,
	.pega-hero-wrap .pega-hero-fv-bg {
		background-position: 10% center;
	}
}
@media (min-width: 1024px) and (max-width: 1199px) {
	.pega-hero-wrap .swell-block-fullWide,
	.pega-hero-wrap .wp-block-loos-full-wide,
	.pega-hero-wrap .pega-hero-fv-bg {
		background-position: 14% center;
	}
}
@media (min-width: 1200px) {
	.pega-hero-wrap .swell-block-fullWide,
	.pega-hero-wrap .wp-block-loos-full-wide,
	.pega-hero-wrap .pega-hero-fv-bg {
		background-position: 16% center;
	}
}
@media (min-width: 1440px) {
	.pega-hero-wrap .swell-block-fullWide,
	.pega-hero-wrap .wp-block-loos-full-wide,
	.pega-hero-wrap .pega-hero-fv-bg {
		background-position: 16% center;
	}
}
.pega-hero-wrap .swell-block-fullWide.has-bg-img::after,
.pega-hero-wrap .wp-block-loos-full-wide.has-bg-img::after {
	content: "";
	position: absolute;
	inset: 0;
	z-index: 1;
	pointer-events: none;
	/* ヘッダー青(--pega-base)と調和する、明るい画像向けの右寄せスクリーン */
	background:
		linear-gradient(180deg, rgba(16, 103, 214, 0.2) 0%, rgba(16, 103, 214, 0.05) 10%, transparent 20%),
		linear-gradient(
			270deg,
			rgba(16, 103, 214, 0.56) 0%,
			rgba(16, 103, 214, 0.34) 24%,
			rgba(255, 255, 255, 0.08) 44%,
			transparent 62%
		);
}
.pega-hero__title {
	margin: 0 0 14px;
	color: #fff;
	font-weight: 800;
	font-size: 33px;
	line-height: 1.42;
	letter-spacing: 0.03em;
	text-align: center;
	line-break: strict;
	word-break: normal;
	overflow-wrap: normal;
	text-wrap: pretty;
	hanging-punctuation: allow-end first;
	text-shadow: 0 2px 14px rgba(8, 40, 100, 0.32), 0 1px 4px rgba(0, 0, 0, 0.18);
}
.pega-hero__eyebrow {
	margin: 0 0 12px;
	color: rgba(255, 255, 255, 0.96);
	font-weight: 700;
	font-size: 22px;
	line-height: 1.65;
	letter-spacing: 0.05em;
	text-align: center;
	line-break: strict;
	word-break: normal;
	overflow-wrap: normal;
	text-wrap: pretty;
	hanging-punctuation: allow-end first;
	text-shadow: 0 1px 10px rgba(8, 40, 100, 0.28);
}
.pega-hero__desc {
	margin: 0 auto 36px;
	color: rgba(255, 255, 255, 0.92);
	font-weight: 500;
	font-size: 20px;
	line-height: 1.8;
	max-width: 32em;
	text-align: center;
	line-break: strict;
	word-break: normal;
	overflow-wrap: normal;
	text-wrap: pretty;
	hanging-punctuation: allow-end first;
	text-shadow: 0 1px 10px rgba(8, 40, 100, 0.26);
}
@media (min-width: 600px) {
	.pega-hero__title { font-size: 39px; }
	.pega-hero__eyebrow { font-size: 24px; }
	.pega-hero__desc { font-size: 21px; margin-bottom: 32px; }
}
@media (min-width: 768px) and (max-width: 959px) {
	.pega-hero__title {
		font-size: 20px;
		line-height: 1.35;
		letter-spacing: 0;
		text-wrap: wrap;
		white-space: nowrap;
	}
	.pega-hero__eyebrow { font-size: 20px; letter-spacing: 0.02em; }
	.pega-hero__desc { font-size: 14px; line-height: 1.7; letter-spacing: 0; }
}
@media (min-width: 960px) {
	.pega-hero-wrap .swell-block-fullWide,
	.pega-hero-wrap .wp-block-loos-full-wide,
	.pega-hero-wrap .pega-hero-fv-bg {
		min-height: min(90vh, 860px);
	}
	.pega-hero__title { font-size: 51px; margin-bottom: 18px; }
	.pega-hero__eyebrow { font-size: 28px; margin-bottom: 14px; }
	.pega-hero__desc { font-size: 22px; margin-bottom: 0; }
}
@media (min-width: 960px) and (max-width: 1199px) {
	.pega-hero__desc {
		font-size: 16px;
		line-height: 1.7;
		letter-spacing: 0;
		text-wrap: wrap;
	}
}
@media (min-width: 1200px) {
	.pega-hero__title { font-size: 57px; }
	.pega-hero__eyebrow { font-size: 30px; }
	.pega-hero__desc { font-size: 23px; }
}
@media (min-width: 1440px) {
	.pega-hero__title {
		font-size: 56px;
		line-height: 1.38;
		letter-spacing: 0.02em;
		text-wrap: pretty;
	}
	.pega-hero__eyebrow { font-size: 33px; }
	.pega-hero__desc { font-size: 25px; max-width: none; }
}
@media (min-width: 1600px) {
	.pega-hero__title { font-size: 67px; }
	.pega-hero__eyebrow { font-size: 34px; }
	.pega-hero__desc { font-size: 26px; }
}
@media (min-width: 1920px) {
	.pega-hero__title { font-size: 71px; }
	.pega-hero__eyebrow { font-size: 35px; }
	.pega-hero__desc { font-size: 27px; }
}
@media (min-width: 1900px) {
	.pega-hero__desc {
		font-size: 24px;
		line-height: 1.75;
		letter-spacing: 0;
		text-wrap: wrap;
		white-space: nowrap;
	}
}
.pega-hero__actions .swell-block-button__link {
	display: flex;
	align-items: center;
	justify-content: center;
	gap: 0.35em;
	width: 100%;
	min-height: 52px;
	min-width: 44px;
	padding: 16px 28px;
	border-radius: 999px;
	font-family: var(--pega-font);
	font-weight: 700;
	font-size: 15px;
	line-height: 1.4;
	text-decoration: none;
	border: 2px solid transparent;
	white-space: nowrap;
	box-sizing: border-box;
	transition: transform 0.15s ease, box-shadow 0.15s ease, filter 0.15s ease;
}
.pega-hero__actions .swell-block-button:first-of-type .swell-block-button__link {
	background: var(--pega-base);
	color: #fff;
	box-shadow: 0 4px 18px rgba(16, 103, 214, 0.35);
	border-color: rgba(255, 255, 255, 0.2);
}
.pega-hero__actions .swell-block-button:first-of-type .swell-block-button__link:hover {
	box-shadow: 0 8px 24px rgba(16, 103, 214, 0.45);
}
.pega-hero__actions .swell-block-button:last-of-type .swell-block-button__link {
	background: var(--pega-accent);
	color: #fff;
	box-shadow: 0 4px 18px rgba(141, 199, 60, 0.42);
	border-color: rgba(255, 255, 255, 0.25);
}
.pega-hero__actions .swell-block-button:last-of-type .swell-block-button__link:hover {
	box-shadow: 0 8px 24px rgba(141, 199, 60, 0.52);
}
.pega-hero__actions .swell-block-button__link:hover {
	transform: translateY(-2px);
	filter: brightness(1.04);
}
.pega-hero__actions .swell-block-button__link:focus-visible {
	outline: 3px solid #fff;
	outline-offset: 3px;
}
@media (min-width: 960px) {
	.pega-hero__actions .swell-block-button__link { font-size: 16px; padding: 16px 32px; }
}
@media (min-width: 1280px) {
	.pega-hero__actions .swell-block-button__link { font-size: 16px; }
}
@media (min-width: 1440px) {
	.pega-hero__actions .swell-block-button__link {
		font-size: 16px;
		padding: 14px 26px;
		min-height: 48px;
	}
}
.pega-trust {
	position: relative;
	z-index: 3;
	background: #fff;
	border-top: 1px solid rgba(16, 103, 214, 0.1);
	box-shadow: 0 4px 20px rgba(16, 103, 214, 0.06);
}
.pega-trust__inner > .wp-block-column + .wp-block-column .pega-trust__item {
	border-left: 1px solid rgba(16, 103, 214, 0.12);
}
.pega-trust__num {
	display: inline-block;
	position: relative;
	padding-bottom: 12px;
	color: var(--pega-base);
	font-family: var(--pega-font);
	font-weight: 800;
	font-size: 34px;
	line-height: 1;
	letter-spacing: -0.02em;
}

/* 実績数字：数字幅いっぱいの青→オレンジ下線 */
.pega-trust__num::after {
	content: "";
	position: absolute;
	left: 0;
	right: 0;
	bottom: 0;
	width: 100%;
	height: 3px;
	border-radius: 999px;
	background: linear-gradient(90deg, var(--pega-base) 0%, var(--pega-orange) 100%);
	opacity: 0.9;
}
.pega-trust__suffix {
	font-size: 0.46em;
	font-weight: 700;
	margin-left: 2px;
}
.pega-trust__label {
	color: var(--pega-text-sub);
	font-family: var(--pega-font);
	font-weight: 600;
	font-size: 13px;
	line-height: 1.65;
	max-width: 18em;
}
@media (min-width: 600px) {
	.pega-trust__num { font-size: 42px; }
	.pega-trust__label { font-size: 14px; }
}
@media (min-width: 960px) {
	.pega-trust__num { font-size: 48px; }
	.pega-trust__label { font-size: 15px; }
}
@media (max-width: 599px) {
.pega-hero-wrap .swell-block-fullWide,
.pega-hero-wrap .wp-block-loos-full-wide {
		min-height: min(86vh, 720px);
		background-position: center 32%;
	}
	.pega-hero__title {
		font-size: 27px;
		line-height: 1.4;
		letter-spacing: 0.02em;
	}
	.pega-hero__eyebrow {
		font-size: 20px;
		line-height: 1.55;
	}
	.pega-hero__desc {
		font-size: 17px;
		line-height: 1.75;
		max-width: none;
	}
	.pega-hero-wrap .swell-block-fullWide.has-bg-img::after,
	.pega-hero-wrap .wp-block-loos-full-wide.has-bg-img::after {
		background:
			linear-gradient(180deg, rgba(16, 103, 214, 0.18) 0%, transparent 12%),
			linear-gradient(
				180deg,
				rgba(12, 58, 140, 0.68) 0%,
				rgba(12, 58, 140, 0.46) 48%,
				rgba(12, 58, 140, 0.18) 78%,
				transparent 100%
			);
	}
	.pega-hero__actions .swell-block-button__link {
		width: 100%;
		display: flex;
		box-sizing: border-box;
		padding: 15px 20px;
	}
	.pega-trust__inner > .wp-block-column + .wp-block-column .pega-trust__item {
		border-left: none;
		border-top: 1px solid rgba(16, 103, 214, 0.12);
	}
}
@media (max-width: 425px) {
	.pega-hero__title {
		font-size: 22px;
		line-height: 1.38;
		letter-spacing: 0;
		text-wrap: wrap;
	}
	.pega-hero__eyebrow {
		font-size: 18px;
	}
	.pega-hero__desc {
		font-size: 16px;
	}
	.pega-hero__actions .swell-block-button__link {
		padding: 14px 18px;
		min-height: 48px;
	}
}
/* =========================================================
   pega-header-deco — ヘッダー(トップ)の装飾（③ / site-css / priority 10）
   -----------------------------------------------------------
   SWELL内部クラス(#header・.l-header__inner・.c-gnav・.c-headLogo・.p-spMenu 等)への
   「見た目」上書き＝色・背景・グラデ・影・枠・角丸・drop-shadow・フォント を集約。
   構造(display/flex/grid/order/position/z-index/幅/余白・SPメニュー機能上書き
   [display:block/overflow:visible/min-height:0]・ハンバーガーの order 並べ替え)は
   子テーマ style.css に残置。
   ※base(.pega-top #header…高詳細度)と@media(bare #header…低詳細度)は詳細度差で勝敗が
     決まり読込順に非依存＝レイヤー分離でも反転しない。
   ※スコープ改名(.home→.pega-top)と SWELL上書き一覧は別コミット/別ドキュメントで管理。
   ========================================================= */

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
PEGA_LAYER_EOF;
	wp_register_style( 'pega-css-sections', false, array( 'pega-css-tokens' ), null );
	wp_enqueue_style( 'pega-css-sections' );
	wp_add_inline_style( 'pega-css-sections', pega_css_min( $css ) );
}, 12 );
