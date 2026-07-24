<?php
/**
 * 4941: 5210ヘッダー参照を段階1用ラッパーで一時非表示（削除しない）
 *
 * Usage:
 *   docker cp scripts/pega-phase1-hide-header-4941.php pegasus_wp:/tmp/
 *   docker exec pegasus_wp wp eval-file /tmp/pega-phase1-hide-header-4941.php --allow-root
 */

if ( ! function_exists( 'parse_blocks' ) ) {
	echo "WordPress not loaded\n";
	exit( 1 );
}

function pega_wrap_header_ref( array &$blocks ): bool {
	foreach ( $blocks as $index => &$block ) {
		if ( ( $block['blockName'] ?? '' ) === 'core/block'
			&& (int) ( $block['attrs']['ref'] ?? 0 ) === 5210
		) {
			if ( str_contains( $block['attrs']['className'] ?? '', 'pega-phase1-header-hidden' ) ) {
				return false;
			}
			$header_block     = $block;
			$blocks[ $index ] = [
				'blockName'    => 'core/group',
				'attrs'        => [
					'className' => 'pega-phase1-header-hidden',
					'metadata'  => [
						'name' => 'ヘッダー（段階1非表示）',
					],
					'layout'    => [
						'type' => 'constrained',
					],
				],
				'innerBlocks'  => [ $header_block ],
				'innerHTML'    => "\n<div class=\"wp-block-group pega-phase1-header-hidden\"></div>\n",
				'innerContent' => [
					"\n<div class=\"wp-block-group pega-phase1-header-hidden\">",
					null,
					"</div>\n",
				],
			];
			return true;
		}
	}
	unset( $block );
	return false;
}

$post_id = 4941;
$post    = get_post( $post_id );
if ( ! $post ) {
	echo "Post $post_id not found\n";
	exit( 1 );
}

$blocks  = parse_blocks( $post->post_content );
$changed = pega_wrap_header_ref( $blocks );

if ( ! $changed ) {
	echo "POST $post_id: no changes (already wrapped or header ref not found)\n";
	exit( 0 );
}

$result = wp_update_post(
	[
		'ID'           => $post_id,
		'post_content' => serialize_blocks( $blocks ),
	],
	true
);

if ( is_wp_error( $result ) ) {
	echo "POST $post_id: ERROR " . $result->get_error_message() . "\n";
	exit( 1 );
}

echo "POST $post_id: wrapped 5210 header ref for phase1 hide\n";
echo "Done.\n";
