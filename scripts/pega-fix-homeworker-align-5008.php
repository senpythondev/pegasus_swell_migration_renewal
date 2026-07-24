<?php
/**
 * 5008 在宅支援: alignwide / alignfull / alignleft を除去（JSON + innerHTML 同期）
 *
 * Usage:
 *   docker cp scripts/pega-fix-homeworker-align-5008.php pegasus_wp:/tmp/
 *   docker exec pegasus_wp wp eval-file /tmp/pega-fix-homeworker-align-5008.php --allow-root
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

function pega_set_class( array &$block, string $class ): void {
	if ( ! isset( $block['attrs'] ) || ! is_array( $block['attrs'] ) ) {
		$block['attrs'] = [];
	}
	$block['attrs']['className'] = trim( $class );
}

function pega_remove_align_attr( array &$block ): bool {
	if ( empty( $block['attrs']['align'] ) ) {
		return false;
	}
	unset( $block['attrs']['align'] );
	return true;
}

function pega_remove_classes( string $class, array $remove ): string {
	$parts = array_filter( preg_split( '/\s+/', trim( $class ) ) ?: [] );
	$parts = array_values( array_diff( $parts, $remove ) );
	return implode( ' ', $parts );
}

function pega_patch_html_classes( string $html, array $remove ): string {
	if ( ! preg_match( '/class="([^"]*)"/', $html, $m, PREG_OFFSET_CAPTURE ) ) {
		return $html;
	}
	$new = pega_remove_classes( $m[1][0], $remove );
	if ( $new === $m[1][0] ) {
		return $html;
	}
	$start = $m[1][1];
	return substr( $html, 0, $start ) . $new . substr( $html, $start + strlen( $m[1][0] ) );
}

function pega_sync_align_classes( array &$block, array $remove ): bool {
	$changed = false;
	$cls     = pega_class( $block );
	$new_cls = pega_remove_classes( $cls, $remove );
	if ( $new_cls !== $cls ) {
		pega_set_class( $block, $new_cls );
		$changed = true;
	}

	if ( ! empty( $block['innerHTML'] ) ) {
		$patched = pega_patch_html_classes( $block['innerHTML'], $remove );
		if ( $patched !== $block['innerHTML'] ) {
			$block['innerHTML'] = $patched;
			$changed             = true;
		}
	}

	if ( ! empty( $block['innerContent'] ) ) {
		foreach ( $block['innerContent'] as &$part ) {
			if ( is_string( $part ) && str_contains( $part, 'class="' ) ) {
				$patched = pega_patch_html_classes( $part, $remove );
				if ( $patched !== $part ) {
					$part    = $patched;
					$changed = true;
				}
			}
		}
		unset( $part );
	}

	// 静的 image / heading / paragraph は innerHTML と innerContent[0] を一致させる
	if ( $changed && empty( $block['innerBlocks'] ) && ! empty( $block['innerContent'][0] ) && is_string( $block['innerContent'][0] ) ) {
		$block['innerContent'][0] = $block['innerHTML'];
	}

	return $changed;
}

function pega_update_blue_box_padding( array &$block ): bool {
	if ( ! pega_is( $block, 'group' ) || ! str_contains( pega_class( $block ), 'u-mb-0' ) ) {
		return false;
	}
	if ( ! str_contains( $block['innerHTML'] ?? '', 'eff6ff' ) && ( $block['attrs']['style']['color']['background'] ?? '' ) !== '#eff6ff' ) {
		return false;
	}

	$padding = [
		'top'    => '40px',
		'right'  => '36px',
		'bottom' => '40px',
		'left'   => '36px',
	];
	if ( ! isset( $block['attrs']['style'] ) || ! is_array( $block['attrs']['style'] ) ) {
		$block['attrs']['style'] = [];
	}
	$block['attrs']['style']['spacing']['padding'] = $padding;

	$changed = false;
	$replace = static function ( string $html ) use ( $padding, &$changed ): string {
		$new = preg_replace(
			'/padding-top:\d+px;padding-right:\d+px;padding-bottom:\d+px;padding-left:\d+px/',
			'padding-top:' . $padding['top'] . ';padding-right:' . $padding['right'] . ';padding-bottom:' . $padding['bottom'] . ';padding-left:' . $padding['left'],
			$html,
			1,
			$count
		);
		if ( $count ) {
			$changed = true;
			return is_string( $new ) ? $new : $html;
		}
		return $html;
	};

	if ( ! empty( $block['innerHTML'] ) ) {
		$block['innerHTML'] = $replace( $block['innerHTML'] );
	}
	if ( ! empty( $block['innerContent'] ) ) {
		foreach ( $block['innerContent'] as &$part ) {
			if ( is_string( $part ) ) {
				$part = $replace( $part );
			}
		}
		unset( $part );
	}

	return $changed;
}

function pega_repair_corrupted_columns_opener( array &$block ): bool {
	if ( ! pega_is( $block, 'columns' ) || ! str_contains( pega_class( $block ), 'girl' ) ) {
		return false;
	}
	$changed = false;
	$fix     = static function ( string $html ) use ( &$changed ): string {
		if ( str_contains( $html, 'pc_only">>' ) ) {
			$changed = true;
			return str_replace( 'pc_only">>', 'pc_only">', $html );
		}
		return $html;
	};
	if ( ! empty( $block['innerHTML'] ) ) {
		$block['innerHTML'] = $fix( $block['innerHTML'] );
	}
	if ( ! empty( $block['innerContent'] ) ) {
		foreach ( $block['innerContent'] as &$part ) {
			if ( is_string( $part ) ) {
				$part = $fix( $part );
			}
		}
		unset( $part );
	}
	return $changed;
}

function pega_walk( array &$blocks, callable $fn ): int {
	$count = 0;
	foreach ( $blocks as &$block ) {
		$count += (int) $fn( $block );
		if ( ! empty( $block['innerBlocks'] ) ) {
			$count += pega_walk( $block['innerBlocks'], $fn );
		}
	}
	unset( $block );
	return $count;
}

function pega_apply_5008( array &$blocks ): int {
	$count = 0;
	foreach ( $blocks as &$root ) {
		if ( ! pega_is( $root, 'group' ) || ! str_contains( pega_class( $root ), 'pega-service-intro' ) ) {
			continue;
		}

		$count += pega_walk(
			$root['innerBlocks'],
			function ( array &$block ) use ( &$count ): bool {
				$changed = false;
				$cls     = pega_class( $block );

				if ( pega_is( $block, 'group' ) && str_contains( $cls, 'u-mb-0' ) && str_contains( $block['innerHTML'] ?? '', 'eff6ff' ) ) {
					$changed = pega_update_blue_box_padding( $block ) || $changed;
				}

				if ( pega_is( $block, 'group' ) && str_contains( $cls, 'u-mb-10' ) && ! str_contains( $cls, 'has-background' ) ) {
					$changed = pega_remove_align_attr( $block ) || $changed;
					$changed = pega_sync_align_classes( $block, [ 'alignwide', 'alignfull' ] ) || $changed;
					foreach ( $block['innerBlocks'] as &$child ) {
						$child_cls = pega_class( $child );
						if ( pega_is( $child, 'heading' ) && str_contains( $child_cls, 'is-style-section_ttl' ) ) {
							$changed = pega_remove_align_attr( $child ) || $changed;
							$changed = pega_sync_align_classes( $child, [ 'alignwide', 'alignfull' ] ) || $changed;
						}
						if ( pega_is( $child, 'paragraph' ) && str_contains( $child_cls, 'sub-heading' ) ) {
							$changed = pega_remove_align_attr( $child ) || $changed;
							$changed = pega_sync_align_classes( $child, [ 'alignwide', 'alignfull' ] ) || $changed;
						}
					}
					unset( $child );
				}

				if ( pega_is( $block, 'columns' ) && str_contains( $cls, 'girl' ) ) {
					$changed = pega_repair_corrupted_columns_opener( $block ) || $changed;
					$changed = pega_remove_align_attr( $block ) || $changed;
					$changed = pega_sync_align_classes( $block, [ 'alignwide', 'alignfull' ] ) || $changed;
					foreach ( $block['innerBlocks'] as &$col ) {
						foreach ( $col['innerBlocks'] ?? [] as &$img_block ) {
							if ( ! pega_is( $img_block, 'image' ) ) {
								continue;
							}
							$changed = pega_remove_align_attr( $img_block ) || $changed;
							$changed = pega_sync_align_classes(
								$img_block,
								[ 'alignleft', 'alignright', 'aligncenter', 'alignwide', 'alignfull' ]
							) || $changed;
						}
						unset( $img_block );
					}
					unset( $col );
				}

				if ( pega_is( $block, 'image' ) && 4981 === (int) ( $block['attrs']['id'] ?? 0 ) ) {
					$changed = pega_remove_align_attr( $block ) || $changed;
					$changed = pega_sync_align_classes(
						$block,
						[ 'alignleft', 'alignright', 'aligncenter', 'alignwide', 'alignfull' ]
					) || $changed;
				}

				return $changed;
			}
		);
	}
	unset( $root );
	return $count;
}

$post_id = 5008;
$post    = get_post( $post_id );
if ( ! $post ) {
	echo "Post $post_id not found\n";
	exit( 1 );
}

$blocks  = parse_blocks( $post->post_content );
$changes = pega_apply_5008( $blocks );

if ( 0 === $changes ) {
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

echo "POST $post_id: applied $changes change(s)\n";
echo "Done.\n";
