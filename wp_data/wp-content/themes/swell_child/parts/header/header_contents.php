<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$SETTING      = SWELL_Theme::get_setting();
$header_class = SWELL_Theme::get_header_class();

if ( $SETTING['info_bar_pos'] === 'head_top' ) SWELL_Theme::get_parts( 'parts/header/info_bar' );
?>
<header id="header" class="l-header <?=esc_attr( $header_class )?>" data-spfix="<?=$SETTING['fix_header_sp'] ? '1' : '0'?>">
	<?php if ( SWELL_Theme::is_use( 'head_bar' ) ) SWELL_Theme::get_parts( 'parts/header/head_bar' ); ?>
	<div class="l-header__inner l-container">
		<div class="l-header__logo">
			<?php echo SWELL_PARTS::head_logo(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php if ( $SETTING['phrase_pos'] === 'head_wrap' ) : ?>
				<div class="c-catchphrase u-thin"><?=esc_html( SWELL_Theme::site_data( 'catchphrase' ) )?></div>
			<?php endif; ?>
			<?php if ( function_exists( 'pega_render_header_contact' ) ) pega_render_header_contact(); ?>
		</div>
		<nav id="gnav" class="l-header__gnav c-gnavWrap">
			<?php
				SWELL_Theme::pluggable_parts( 'gnav', [
					'use_search' => 'head_menu' === $SETTING['search_pos'],
				] );
			?>
		</nav>
		<?php
			\SWELL_Theme::outuput_widgets( 'head_box', [
				'before' => '<div class="w-header pc_"><div class="w-header__inner">',
				'after'  => '</div></div>',
			] );

			SWELL_Theme::get_parts( 'parts/header/sp_btns' );
		?>
	</div>
	<?php
	if ( SWELL_Theme::is_use( 'sp_head_nav' ) ) :
		SWELL_Theme::get_parts( 'parts/header/sp_head_nav' );
	endif;
	?>
</header>
<?php

if ( SWELL_Theme::is_use( 'fix_header' ) ) SWELL_Theme::get_parts( 'parts/header/fix_header', $header_class );

if ( $SETTING['info_bar_pos'] === 'head_bottom' ) SWELL_Theme::get_parts( 'parts/header/info_bar' );
