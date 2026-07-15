<?php
// Code Snippets(無料版) PHPスニペット原本: pega-css-00-tokens / scope=global / active
// 実体はDB(wp04447asnippets)。この原本はバージョン管理・再投入用。
// pega-css-00-tokens — CSS3層 ① 設計トークン（色/余白/フォント等・運用の主入口）
// 読込順: child_style の後（依存チェーンで順序保証）。
if ( ! function_exists( 'pega_css_min' ) ) {
	function pega_css_min( $css ) {
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
   pega-tokens — ペガサス 設計トークン（運用調整の主入口）
   Code Snippets Pro (site-css / priority 1) に集約。
   色・余白・角丸・影・フォント等はここで調整する。
   ※子テーマ swell_child/style.css にも同一の :root をフォールバックとして一時保持。
   ========================================================= */
:root {
	--pega-base:        #1067D6;
	--pega-second:      #FD757C;
	--pega-accent:      #8DC73C;
	--pega-orange:      #FF8C42;   /* 友本様指定オレンジ（要所アクセント） */
	--pega-orange-ink:  #E8722A;   /* 濃いオレンジ（文字・hover用） */
	--pega-orange-soft: rgba(255, 140, 66, 0.1);  /* 淡いオレンジ面 */
	--pega-orange-line: rgba(255, 159, 90, 0.85); /* 下線・枠アクセント用 */
	--pega-base-ink:    #0a4aa8;
	--pega-base-dark:   #0b3d8a;
	--pega-text:        #1A1A1A;
	--pega-text-sub:    #4a5568;
	--pega-font:        "Noto Sans JP", "Hiragino Sans", "Hiragino Kaku Gothic ProN", "Yu Gothic", Meiryo, sans-serif;
	--pega-header-h:    72px;

	/* 柔らかい派生色（広い面・背景用） */
	--pega-soft-bg:     #f7f9fc;
	--pega-soft-blue:   #eef3f9;
	--pega-soft-green:  #f4f8ef;
	--pega-border:      rgba(16, 103, 214, 0.14);
	--pega-border-strong: rgba(16, 103, 214, 0.24);
	--pega-card-shadow: 0 2px 8px rgba(16, 103, 214, 0.08);
	--pega-card-radius: 12px;
	--pega-card-padding: clamp(18px, 3vw, 24px);

	/* SWELL 準拠レイアウト — 全セクション同一幅 */
	--pega-section-px:  clamp(16px, 4vw, var(--swl-pad_container, 48px));
	--pega-section-py:  clamp(56px, 7vw, 80px);
	--pega-section-py-sm: clamp(32px, 4vw, 48px);
	--pega-wrap-content: min(960px, 100%);
	--pega-wrap-narrow: var(--pega-wrap-content);
	--pega-wrap-wide:   var(--pega-wrap-content);
	--pega-wrap-banner: var(--pega-wrap-content);

	/* 見出し階層（SWELL post_content 基準） */
	--pega-h2:          clamp(24px, 4vw, 1.75rem);
	--pega-h3:          clamp(18px, 2.6vw, 1.25rem);
	--pega-lead:        clamp(15px, 2.1vw, 17px);
	--pega-body:        clamp(14px, 2vw, 16px);

	/* 控えめモーション（pega-global-motion） */
	--pega-motion-duration: 0.65s;
	--pega-motion-ease:     cubic-bezier(0.25, 0.1, 0.25, 1);
	--pega-motion-offset:   14px;
}

@media (min-width: 960px) {
	:root {
		--pega-header-h: 84px;
	}
}
PEGA_LAYER_EOF;
	wp_register_style( 'pega-css-00-tokens', false, array( 'child_style' ), null );
	wp_enqueue_style( 'pega-css-00-tokens' );
	wp_add_inline_style( 'pega-css-00-tokens', pega_css_min( $css ) );
}, 12 );
