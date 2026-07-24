<?php
/**
 * 4941 実績バー: PC/SP を .achivement 1本に統合
 * - .mobile-img (SP画像) を削除
 * - .achivement から pc_only を除去（JSON + innerHTML 同期）
 *
 * Usage:
 *   docker cp scripts/pega-merge-stats-bar-4941.php pegasus_wp:/tmp/
 *   docker exec pegasus_wp wp eval-file /tmp/pega-merge-stats-bar-4941.php --allow-root
 */

if ( ! function_exists( 'parse_blocks' ) ) {
	echo "WordPress not loaded\n";
	exit( 1 );
}

function pega_class( array $block ): string {
	return $block['attrs']['className'] ?? '';
}

function pega_is( array $block, string $type ): bool {
	$name = $block['blockName'] ?? '';
	return $type === $name || str_ends_with( $name, '/' . $type );
}

function pega_remove_class_from_opener( string $html, string $remove ): string {
	if ( ! preg_match( '/^(\s*<\w+[^>]*\sclass=")([^"]*)(")/s', $html, $m ) ) {
		return $html;
	}
	$parts = array_values( array_filter( preg_split( '/\s+/', trim( $m[2] ) ) ?: [] ) );
	$parts = array_values( array_diff( $parts, [ $remove ] ) );
	return $m[1] . implode( ' ', $parts ) . $m[3] . substr( $html, strlen( $m[0] ) );
}

function pega_strip_device_class( array &$block, string $device ): bool {
	$cls = pega_class( $block );
	if ( ! str_contains( $cls, $device ) ) {
		return false;
	}
	$parts = array_values( array_filter( preg_split( '/\s+/', trim( $cls ) ) ?: [] ) );
	$parts = array_values( array_diff( $parts, [ $device ] ) );
	if ( ! isset( $block['attrs'] ) || ! is_array( $block['attrs'] ) ) {
		$block['attrs'] = [];
	}
	$block['attrs']['className'] = implode( ' ', $parts );

	if ( ! empty( $block['innerHTML'] ) ) {
		$block['innerHTML'] = pega_remove_class_from_opener( $block['innerHTML'], $device );
	}
	if ( ! empty( $block['innerContent'] ) ) {
		foreach ( $block['innerContent'] as &$part ) {
			if ( is_string( $part ) && str_contains( $part, 'class="' ) ) {
				$part = pega_remove_class_from_opener( $part, $device );
			}
		}
		unset( $part );
	}
	return true;
}

function pega_columns_has_mobile_img( array $block ): bool {
	if ( ! pega_is( $block, 'columns' ) ) {
		return false;
	}
	foreach ( $block['innerBlocks'] as $col ) {
		foreach ( $col['innerBlocks'] ?? [] as $child ) {
			if ( pega_is( $child, 'image' ) && str_contains( pega_class( $child ), 'mobile-img' ) ) {
				return true;
			}
		}
	}
	return false;
}

function pega_remove_inner_block_slot( array &$block, int $remove_index ): void {
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

function pega_merge_stats( array &$blocks ): array {
	$removed_img = 0;
	$stripped_pc = 0;

	$walk = function ( array &$list ) use ( &$walk, &$removed_img, &$stripped_pc ) {
		foreach ( $list as &$block ) {
			if ( pega_is( $block, 'group' ) && str_contains( pega_class( $block ), 'achivement' ) ) {
				if ( pega_strip_device_class( $block, 'pc_only' ) ) {
					++$stripped_pc;
				}
			}

			if ( pega_is( $block, 'group' ) && str_contains( pega_class( $block ), 'pega-stats' ) ) {
				foreach ( $block['innerBlocks'] as $idx => $child ) {
					if ( pega_columns_has_mobile_img( $child ) || ( pega_is( $child, 'image' ) && str_contains( pega_class( $child ), 'mobile-img' ) ) ) {
						pega_remove_inner_block_slot( $block, $idx );
						++$removed_img;
						break;
					}
				}
			}

			if ( ! empty( $block['innerBlocks'] ) ) {
				$walk( $block['innerBlocks'] );
			}
		}
		unset( $block );
	};

	$walk( $blocks );
	return [ $removed_img, $stripped_pc ];
}

$post_id = 4941;
$post    = get_post( $post_id );
if ( ! $post ) {
	echo "Post $post_id not found\n";
	exit( 1 );
}

$backup_dir = '/var/www/html/../docs/block-backups';
// ホスト側バックアップは実行前に手動/別途。コンテナ内では post_content 長のみログ。
$blocks = parse_blocks( $post->post_content );
list( $removed_img, $stripped_pc ) = pega_merge_stats( $blocks );

if ( 0 === $removed_img && 0 === $stripped_pc ) {
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

echo "POST $post_id: removed mobile-img blocks=$removed_img, stripped pc_only=$stripped_pc\n";
echo "Done.\n";
