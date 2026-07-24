<?php
/**
 * 5008 在宅支援: .girl を PC/SP 共通に（mobile-girl 削除・pc_only 除去）
 */

if ( ! function_exists( 'parse_blocks' ) ) {
	echo "WordPress not loaded\n";
	exit( 1 );
}

function pega_hw_class( array $block ): string {
	return $block['attrs']['className'] ?? '';
}

function pega_hw_is( array $block, string $type ): bool {
	$name = $block['blockName'] ?? '';
	return $type === $name || str_ends_with( $name, '/' . $type );
}

function pega_hw_remove_class_from_opener( string $html, string $remove ): string {
	if ( ! preg_match( '/^(\s*<\w+[^>]*\sclass=")([^"]*)(")/s', $html, $m ) ) {
		return $html;
	}
	$parts = array_values( array_filter( preg_split( '/\s+/', trim( $m[2] ) ) ?: [] ) );
	$parts = array_values( array_diff( $parts, [ $remove ] ) );
	return $m[1] . implode( ' ', $parts ) . $m[3] . substr( $html, strlen( $m[0] ) );
}

function pega_hw_strip_device_class( array &$block, string $device ): bool {
	$cls = pega_hw_class( $block );
	if ( ! str_contains( $cls, $device ) ) {
		return false;
	}
	$parts = array_values( array_filter( preg_split( '/\s+/', trim( $cls ) ) ?: [] ) );
	$parts = array_values( array_diff( $parts, [ $device ] ) );
	$block['attrs']['className']     = implode( ' ', $parts );
	$block['innerHTML']              = pega_hw_remove_class_from_opener( $block['innerHTML'] ?? '', $device );
	if ( ! empty( $block['innerContent'] ) ) {
		foreach ( $block['innerContent'] as &$part ) {
			if ( is_string( $part ) && str_contains( $part, 'class="' ) ) {
				$part = pega_hw_remove_class_from_opener( $part, $device );
			}
		}
		unset( $part );
	}
	return true;
}

function pega_hw_remove_inner_block_slot( array &$block, int $remove_index ): void {
	array_splice( $block['innerBlocks'], $remove_index, 1 );
	$null_positions = [];
	foreach ( $block['innerContent'] as $i => $part ) {
		if ( is_null( $part ) ) {
			$null_positions[] = $i;
		}
	}
	if ( ! isset( $null_positions[ $remove_index ] ) ) {
		return;
	}
	$drop   = $null_positions[ $remove_index ];
	$new_ic = $block['innerContent'];
	unset( $new_ic[ $drop ] );
	if ( isset( $new_ic[ $drop ] ) && is_string( $new_ic[ $drop ] ) && '' === trim( $new_ic[ $drop ] ) ) {
		unset( $new_ic[ $drop ] );
	} elseif ( $drop > 0 && isset( $new_ic[ $drop - 1 ] ) && is_string( $new_ic[ $drop - 1 ] ) && '' === trim( $new_ic[ $drop - 1 ] ) ) {
		unset( $new_ic[ $drop - 1 ] );
	}
	$block['innerContent'] = array_values( $new_ic );
}

$post_id = 5008;
$post    = get_post( $post_id );
if ( ! $post ) {
	echo "Post $post_id not found\n";
	exit( 1 );
}

$blocks   = parse_blocks( $post->post_content );
$removed  = 0;
$stripped = 0;

$walk = function ( array &$list ) use ( &$walk, &$removed, &$stripped ) {
	foreach ( $list as &$block ) {
		if ( pega_hw_is( $block, 'group' ) && str_contains( pega_hw_class( $block ), 'pega-service-intro' ) ) {
			foreach ( $block['innerBlocks'] as $idx => $child ) {
				if ( pega_hw_is( $child, 'cover' ) && str_contains( pega_hw_class( $child ), 'mobile-girl' ) ) {
					pega_hw_remove_inner_block_slot( $block, $idx );
					++$removed;
					break;
				}
			}
		}
		if ( pega_hw_is( $block, 'columns' ) && str_contains( pega_hw_class( $block ), 'girl' ) ) {
			if ( pega_hw_strip_device_class( $block, 'pc_only' ) ) {
				++$stripped;
			}
		}
		if ( ! empty( $block['innerBlocks'] ) ) {
			$walk( $block['innerBlocks'] );
		}
	}
	unset( $block );
};

$walk( $blocks );

if ( 0 === $removed && 0 === $stripped ) {
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
	echo "POST $post_id: ERROR " . $result->get_error_message() . "\n";
	exit( 1 );
}

echo "POST $post_id: removed mobile-girl=$removed, stripped pc_only=$stripped\n";
echo "Done.\n";
