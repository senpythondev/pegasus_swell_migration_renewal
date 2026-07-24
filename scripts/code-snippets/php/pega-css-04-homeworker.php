<?php
/*
 * ▼ペガサス様 編集ガイド ─ 「在宅型就労移行支援 紹介」セクションの装飾
 *
 *   色や余白を変えたい時は、下の  $css = <<<'CSS'  から  CSS;  までの間を書き換えて
 *   「変更を保存」を押してください。
 *   ・このスニペットは、この1セクションだけの見た目です。オフにすると、このセクションの
 *     装飾だけが外れます（他のセクションやサイト全体には影響しません）。
 *   ・色そのものの共通設定は「pega-css-00-tokens」にあります。
 */
add_action( 'wp_enqueue_scripts', function () {
	$css = <<<'CSS'
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

/* swell-page (4929) — 在宅支援 .pega-service-intro（rev5240 ブロックCSS復旧） */
.page-id-4929 .pega-service-intro {
	padding: 40px 0;
}
.page-id-4929 .pega-service-intro > .wp-block-group.u-mb-0:not(.has-background) {
	padding: 0;
}
.page-id-4929 .pega-service-intro .supports {
	/* SWELL .u-mb-ctrl + * { margin-top:0!important } を上書き（青ボックス直後の columns） */
	margin-top: 80px !important;
}

/* サービスCTA: ブロック width50% を解除し1行表示（PC/SP共通） */
.page-id-4929 .pega-service-intro .girl .wp-block-button.has-custom-width {
	width: auto;
	max-width: 100%;
}

.page-id-4929 .pega-service-intro .girl .pega-btn-primary .wp-block-button__link {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	gap: 0.35em;
	white-space: nowrap;
	text-align: center;
	box-sizing: border-box;
}

/* swell-page (4929) — デスクトップ帯域(961px+) 在宅支援レイアウト */
@media (min-width: 961px) {
	.page-id-4929 .pega-service-intro .wp-block-group__inner-container > .wp-block-group.u-mb-10,
	.page-id-4929 .pega-service-intro .wp-block-group.u-mb-10:not(.has-background) {
		display: flex;
		flex-direction: column;
		align-items: center;
		gap: 24px;
		margin-bottom: 48px;
		padding: 20px;
	}

	.page-id-4929 .pega-service-intro .wp-block-group.u-mb-10:not(.has-background) .pega-badge,
	.page-id-4929 .pega-service-intro .wp-block-group.u-mb-10:not(.has-background) .is-style-section_ttl,
	.page-id-4929 .pega-service-intro .wp-block-group.u-mb-10:not(.has-background) .sub-heading {
		margin-top: 0;
		margin-bottom: 0;
	}

	.page-id-4929 .pega-service-intro .wp-block-group.u-mb-10:not(.has-background) .pega-badge {
		display: block;
		width: fit-content;
		margin-left: auto;
		margin-right: auto;
	}

	.page-id-4929 .pega-service-intro .wp-block-group.u-mb-0.has-background {
		padding: 40px 36px;
		margin-bottom: 48px;
	}

	.page-id-4929 .pega-service-intro .girl.wp-block-columns {
		width: 100%;
		max-width: 100%;
		margin-left: 0;
		margin-right: 0;
		align-items: stretch;
		gap: 32px;
		margin-bottom: 0;
	}

	.page-id-4929 .pega-service-intro .girl .wp-block-column:first-child {
		flex-basis: 50%;
		width: 50%;
		min-width: 0;
		box-sizing: border-box;
		padding: 12px 8px;
		border-radius: 12px;
		display: flex;
		align-items: center;
	}

	.page-id-4929 .pega-service-intro .girl .wp-block-column:last-child {
		flex-basis: 50%;
		width: 50%;
		display: flex;
		flex-direction: column;
		justify-content: center;
		gap: 16px;
		padding: 8px 0;
	}

	.page-id-4929 .pega-service-intro .girl .wp-block-column:last-child p {
		margin-top: 0;
		margin-bottom: 0;
	}

	.page-id-4929 .pega-service-intro .girl .wp-block-column:last-child .wp-block-buttons {
		margin-top: 4px;
	}

	.page-id-4929 .pega-service-intro .girl .wp-block-column:first-child figure.wp-block-image {
		float: none;
		display: block;
		width: 100%;
		height: 100%;
		min-height: 0;
		margin: 0;
		border-radius: 10px;
		overflow: hidden;
	}

	/* ブロックエディタの inline width/height — 詳細度では打ち消せない */
	.page-id-4929 .pega-service-intro .girl .wp-block-column:first-child figure.wp-block-image img {
		display: block;
		width: 100% !important;
		height: 100% !important;
		max-width: none;
		max-height: none;
		object-fit: cover;
		object-position: center 22%;
	}

	.page-id-4929 .pega-service-intro .girl .pega-btn-primary .wp-block-button__link {
		width: auto;
		min-width: 240px;
		min-height: 50px;
		padding: 14px 32px;
		line-height: 1.4;
	}

	.page-id-4929 .pega-service-intro .supports {
		margin-top: 56px !important;
	}
}

/* swell-page (4929) — SP 在宅支援（タイトル余白・girl 縦積み） */
@media (max-width: 960px) {
	.page-id-4929 .pega-service-intro {
		padding: 32px 16px;
	}

	.page-id-4929 .pega-service-intro .wp-block-group.u-mb-10:not(.has-background) {
		display: flex;
		flex-direction: column;
		align-items: center;
		gap: 16px;
		margin-bottom: 32px;
		padding: 0 4px;
	}

	.page-id-4929 .pega-service-intro .wp-block-group.u-mb-10:not(.has-background) .pega-badge,
	.page-id-4929 .pega-service-intro .wp-block-group.u-mb-10:not(.has-background) .is-style-section_ttl,
	.page-id-4929 .pega-service-intro .wp-block-group.u-mb-10:not(.has-background) .sub-heading {
		margin-top: 0;
		margin-bottom: 0;
		text-align: center;
	}

	.page-id-4929 .pega-service-intro .wp-block-group.u-mb-0.has-background {
		padding: 28px 20px;
		margin-bottom: 32px;
	}

	.page-id-4929 .pega-service-intro .girl.wp-block-columns {
		flex-direction: column;
		gap: 24px;
		margin-bottom: 0;
	}

	.page-id-4929 .pega-service-intro .girl .wp-block-column:first-child,
	.page-id-4929 .pega-service-intro .girl .wp-block-column:last-child {
		flex-basis: 100%;
		width: 100%;
	}

	.page-id-4929 .pega-service-intro .girl .wp-block-column:first-child figure.wp-block-image {
		width: 100%;
		margin: 0;
		border-radius: 12px;
		overflow: hidden;
		box-shadow: 0 8px 24px rgba(16, 103, 214, 0.12);
		aspect-ratio: 16 / 10;
		background: url("/wp-content/uploads/2026/07/woman-top-pc.jpg") center 22% / cover no-repeat;
	}

	.page-id-4929 .pega-service-intro .girl .wp-block-column:first-child figure.wp-block-image img {
		opacity: 0;
		display: block;
		width: 100%;
		height: 100%;
		min-height: clamp(160px, 45vw, 240px);
		object-fit: cover;
	}

	.page-id-4929 .pega-service-intro .girl .wp-block-column:last-child {
		display: flex;
		flex-direction: column;
		align-items: center;
		gap: 12px;
		padding: 0;
		text-align: center;
	}

	.page-id-4929 .pega-service-intro .girl .wp-block-column:last-child p {
		text-align: center;
		width: 100%;
	}

	.page-id-4929 .pega-service-intro .girl .wp-block-column:last-child .wp-block-buttons {
		justify-content: center;
		width: 100%;
	}

	.page-id-4929 .pega-service-intro .girl .wp-block-column:last-child .wp-block-button {
		margin-left: auto;
		margin-right: auto;
	}

	.page-id-4929 .pega-service-intro .girl .pega-btn-primary .wp-block-button__link {
		min-height: 48px;
		padding: 14px clamp(18px, 5vw, 32px);
		font-size: clamp(13px, 3.5vw, 14px);
		line-height: 1.35;
	}

	.page-id-4929 .pega-service-intro .supports {
		margin-top: 40px !important;
	}
}
CSS;
	wp_register_style( 'pega-css-04-homeworker', false, array( 'child_style' ), null );
	wp_enqueue_style( 'pega-css-04-homeworker' );
	wp_add_inline_style( 'pega-css-04-homeworker', $css );
}, 16 );
