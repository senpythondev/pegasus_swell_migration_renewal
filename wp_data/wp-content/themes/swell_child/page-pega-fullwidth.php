<?php
/**
 * Template Name: ペガサス全幅(ヘッダー・フッターあり)
 *
 * Gutentor キャンバスの代替。テーマ H/F あり・post_content なし・alignfull 全幅。
 * 本番反映: docker cp scripts/templates/page-pega-fullwidth.php pegasus_wp:/var/www/html/wp-content/themes/swell_child/
 *
 * @package swell_child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'swell_is_show_ttltop', '__return_false' );
add_filter( 'swell_is_show_sidebar', '__return_false' );

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<main id="main_content" class="l-mainContent l-article pega-fullwidth-main">
		<div class="pega-canvas">
			<?php the_content(); ?>
		</div>
	</main>
	<?php
endwhile;

get_footer();
