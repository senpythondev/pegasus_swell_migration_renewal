<?php
/**
 * リストビュー用 metadata.name の整理（4941）
 * Usage: docker cp + wp eval-file
 */

if ( ! function_exists( 'parse_blocks' ) ) {
	echo "WordPress not loaded\n";
	exit( 1 );
}

function pega_set_name( array &$block, string $name ): bool {
	$current = $block['attrs']['metadata']['name'] ?? '';
	if ( $current === $name ) {
		return false;
	}
	if ( ! isset( $block['attrs'] ) || ! is_array( $block['attrs'] ) ) {
		$block['attrs'] = [];
	}
	if ( ! isset( $block['attrs']['metadata'] ) || ! is_array( $block['attrs']['metadata'] ) ) {
		$block['attrs']['metadata'] = [];
	}
	$block['attrs']['metadata']['name'] = $name;
	return true;
}

function pega_walk( array &$blocks, callable $fn ): int {
	$count = 0;
	foreach ( $blocks as &$block ) {
		if ( $fn( $block ) ) {
			++$count;
		}
		if ( ! empty( $block['innerBlocks'] ) ) {
			$count += pega_walk( $block['innerBlocks'], $fn );
		}
	}
	return $count;
}

$post_id = 4941;
$post    = get_post( $post_id );
if ( ! $post ) {
	echo "POST $post_id not found\n";
	exit( 1 );
}

$blocks = parse_blocks( $post->post_content );
$count  = pega_walk(
	$blocks,
	function ( array &$block ) {
		$type = $block['blockName'] ?? '';
		$ref  = (int) ( $block['attrs']['ref'] ?? 0 );
		$id   = (int) ( $block['attrs']['id'] ?? 0 );

		if ( 'core/block' === $type && 5210 === $ref ) {
			return pega_set_name( $block, 'ヘッダー' );
		}
		if ( 'core/block' === $type && 5008 === $ref ) {
			return pega_set_name( $block, '在宅支援' );
		}
		if ( 'core/image' === $type && 5098 === $id ) {
			return pega_set_name( $block, '波型装飾' );
		}
		return false;
	}
);

if ( 0 === $count ) {
	echo "POST $post_id: no changes\n";
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
	echo 'ERROR: ' . $result->get_error_message() . "\n";
	exit( 1 );
}

echo "POST $post_id: renamed $count metadata.name(s)\n";
