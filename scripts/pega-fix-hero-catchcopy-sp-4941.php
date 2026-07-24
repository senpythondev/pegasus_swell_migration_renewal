<?php
/**
 * 4941 FV h1: SP向け改行（1文目2行・2文目1行）のため HTML を同期
 *
 * Usage:
 *   docker cp scripts/pega-fix-hero-catchcopy-sp-4941.php pegasus_wp:/tmp/
 *   docker exec pegasus_wp wp eval-file /tmp/pega-fix-hero-catchcopy-sp-4941.php --allow-root
 */

if ( ! function_exists( 'parse_blocks' ) ) {
	echo "WordPress not loaded\n";
	exit( 1 );
}

$old_inner = 'しあわせに働くために、生きている。<br>在宅で、<span style="color:hsl(201.71deg 71.03% 58.04%)">自分のペースで。</span>';
$new_inner = 'しあわせに働くために、<br class="pega-br-sp">生きている。<br><span class="pega-hero-catch-sub">在宅で、<span style="color:hsl(201.71deg 71.03% 58.04%)">自分のペースで。</span></span>';

function pega_replace_in_block( array &$block, string $old, string $new ): bool {
	$changed = false;
	if ( ! empty( $block['innerHTML'] ) && str_contains( $block['innerHTML'], $old ) ) {
		$block['innerHTML'] = str_replace( $old, $new, $block['innerHTML'] );
		$changed             = true;
	}
	if ( ! empty( $block['innerContent'] ) ) {
		foreach ( $block['innerContent'] as &$part ) {
			if ( is_string( $part ) && str_contains( $part, $old ) ) {
				$part    = str_replace( $old, $new, $part );
				$changed = true;
			}
		}
		unset( $part );
	}
	return $changed;
}

function pega_walk_fv_h1( array &$blocks, string $old, string $new ): bool {
	$changed = false;
	foreach ( $blocks as &$block ) {
		if ( ( $block['blockName'] ?? '' ) === 'core/heading'
			&& (int) ( $block['attrs']['level'] ?? 0 ) === 1
			&& str_contains( $block['innerHTML'] ?? '', 'しあわせに働くために' )
		) {
			if ( pega_replace_in_block( $block, $old, $new ) ) {
				$changed = true;
			}
		}
		if ( ! empty( $block['innerBlocks'] ) && pega_walk_fv_h1( $block['innerBlocks'], $old, $new ) ) {
			$changed = true;
		}
	}
	unset( $block );
	return $changed;
}

$post_id = 4941;
$post    = get_post( $post_id );
if ( ! $post ) {
	echo "Post $post_id not found\n";
	exit( 1 );
}

$blocks  = parse_blocks( $post->post_content );
$changed = pega_walk_fv_h1( $blocks, $old_inner, $new_inner );

if ( ! $changed ) {
	echo "POST $post_id: no changes (already synced or h1 not found)\n";
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

echo "POST $post_id: FV h1 catchcopy updated for SP line breaks\n";
echo "Done.\n";
