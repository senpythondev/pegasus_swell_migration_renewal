<?php
/**
 * 4941 FV・実績バー: 指定 inline style をブロック属性/innerHTML から除去（parse_blocks 使用）
 *
 * Usage:
 *   docker cp scripts/pega-strip-inline-fv-stats-4941.php pegasus_wp:/tmp/
 *   docker exec pegasus_wp wp eval-file /tmp/pega-strip-inline-fv-stats-4941.php --allow-root
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

function pega_unset_path( array &$arr, array $path ): bool {
	$ref   = &$arr;
	$found = true;
	foreach ( $path as $i => $key ) {
		if ( ! is_array( $ref ) || ! array_key_exists( $key, $ref ) ) {
			$found = false;
			break;
		}
		if ( $i === count( $path ) - 1 ) {
			unset( $ref[ $key ] );
		} else {
			$ref = &$ref[ $key ];
		}
	}
	if ( ! $found ) {
		return false;
	}
	// 空配列を掃除
	$ref = &$arr;
	foreach ( $path as $i => $key ) {
		if ( $i === count( $path ) - 1 ) {
			break;
		}
		if ( isset( $ref[ $key ] ) && is_array( $ref[ $key ] ) && [] === $ref[ $key ] ) {
			unset( $ref[ $key ] );
		}
		$ref = &$ref[ $key ];
	}
	return true;
}

function pega_strip_style_props( string $html, array $remove_props ): string {
	if ( ! str_contains( $html, 'style="' ) ) {
		return $html;
	}
	$remove_props = array_map( 'strtolower', $remove_props );
	$out          = preg_replace_callback(
		'/\sstyle="([^"]*)"/',
		static function ( array $m ) use ( $remove_props ): string {
			$decls = array_filter( array_map( 'trim', explode( ';', $m[1] ) ) );
			$keep  = [];
			foreach ( $decls as $decl ) {
				if ( '' === $decl || ! str_contains( $decl, ':' ) ) {
					continue;
				}
				$prop = strtolower( trim( explode( ':', $decl, 2 )[0] ) );
				if ( ! in_array( $prop, $remove_props, true ) ) {
					$keep[] = $decl;
				}
			}
			if ( [] === $keep ) {
				return '';
			}
			return ' style="' . implode( ';', $keep ) . '"';
		},
		$html,
		1
	);
	return is_string( $out ) ? $out : $html;
}

function pega_patch_html( array &$block, array $remove_props ): bool {
	$changed = false;
	if ( ! empty( $block['innerHTML'] ) ) {
		$patched = pega_strip_style_props( $block['innerHTML'], $remove_props );
		if ( $patched !== $block['innerHTML'] ) {
			$block['innerHTML'] = $patched;
			$changed             = true;
		}
	}
	if ( ! empty( $block['innerContent'] ) ) {
		foreach ( $block['innerContent'] as &$part ) {
			if ( is_string( $part ) && str_contains( $part, 'style="' ) ) {
				$patched = pega_strip_style_props( $part, $remove_props );
				if ( $patched !== $part ) {
					$part    = $patched;
					$changed = true;
				}
			}
		}
		unset( $part );
	}
	if ( $changed && empty( $block['innerBlocks'] ) && ! empty( $block['innerContent'][0] ) && is_string( $block['innerContent'][0] ) ) {
		$block['innerContent'][0] = $block['innerHTML'];
	}
	return $changed;
}

function pega_remove_column_width( array &$block ): bool {
	if ( ! pega_is( $block, 'column' ) ) {
		return false;
	}
	$changed = false;
	if ( isset( $block['attrs']['width'] ) ) {
		unset( $block['attrs']['width'] );
		$changed = true;
	}
	if ( pega_patch_html( $block, [ 'flex-basis' ] ) ) {
		$changed = true;
	}
	return $changed;
}

function pega_apply_strip( array &$blocks ): array {
	$counts = [
		'stats_section'   => 0,
		'achivement'      => 0,
		'box_col_width'   => 0,
		'inner_col_width' => 0,
		'h1_font'         => 0,
		'buttons_gap'     => 0,
		'contact_pad'     => 0,
	];

	$walk = function ( array &$list, bool $in_achivement = false, bool $in_box = false ) use ( &$walk, &$counts ) {
		foreach ( $list as &$block ) {
			$cls         = pega_class( $block );
			$is_ach      = $in_achivement || ( pega_is( $block, 'group' ) && str_contains( $cls, 'achivement' ) );
			$is_box      = $in_box || ( pega_is( $block, 'column' ) && str_contains( $cls, 'box-achievement' ) );

			if ( pega_is( $block, 'group' ) && str_contains( $cls, 'pega-stats' ) ) {
				$c = 0;
				$c += (int) pega_unset_path( $block['attrs'], [ 'style', 'spacing', 'padding' ] );
				$c += (int) pega_unset_path( $block['attrs'], [ 'style', 'color', 'background' ] );
				$c += (int) pega_patch_html(
					$block,
					[
						'padding-top',
						'padding-right',
						'padding-bottom',
						'padding-left',
						'background-color',
					]
				);
				if ( $c ) {
					++$counts['stats_section'];
				}
			}

			if ( pega_is( $block, 'group' ) && str_contains( $cls, 'achivement' ) ) {
				$c = 0;
				$c += (int) pega_unset_path( $block['attrs'], [ 'style', 'spacing', 'padding' ] );
				$c += (int) pega_unset_path( $block['attrs'], [ 'style', 'spacing', 'blockGap' ] );
				$c += (int) pega_patch_html(
					$block,
					[
						'padding-top',
						'padding-right',
						'padding-bottom',
						'padding-left',
					]
				);
				if ( $c ) {
					++$counts['achivement'];
				}
			}

			if ( pega_is( $block, 'column' ) && str_contains( $cls, 'box-achievement' ) ) {
				if ( pega_remove_column_width( $block ) ) {
					++$counts['box_col_width'];
				}
			}

			if ( $is_box && pega_is( $block, 'column' ) && ! str_contains( $cls, 'box-achievement' ) && isset( $block['attrs']['width'] ) ) {
				if ( pega_remove_column_width( $block ) ) {
					++$counts['inner_col_width'];
				}
			}

			if ( pega_is( $block, 'heading' ) && 1 === (int) ( $block['attrs']['level'] ?? 0 )
				&& str_contains( $block['innerHTML'] ?? '', 'しあわせに働くために' )
			) {
				$c = 0;
				$c += (int) pega_unset_path( $block['attrs'], [ 'style', 'typography', 'fontSize' ] );
				$c += (int) pega_patch_html( $block, [ 'font-size' ] );
				if ( $c ) {
					++$counts['h1_font'];
				}
			}

			if ( pega_is( $block, 'buttons' ) && empty( $cls ) ) {
				$c = 0;
				$c += (int) pega_unset_path( $block['attrs'], [ 'style', 'spacing', 'blockGap' ] );
				if ( $c ) {
					++$counts['buttons_gap'];
				}
			}

			if ( pega_is( $block, 'button' ) && str_contains( $cls, 'btn-contact' ) ) {
				$c = 0;
				$c += (int) pega_unset_path( $block['attrs'], [ 'style', 'spacing', 'padding' ] );
				$c += (int) pega_patch_html(
					$block,
					[
						'padding-top',
						'padding-right',
						'padding-bottom',
						'padding-left',
					]
				);
				if ( $c ) {
					++$counts['contact_pad'];
				}
			}

			if ( ! empty( $block['innerBlocks'] ) ) {
				$walk( $block['innerBlocks'], $is_ach, $is_box );
			}
		}
		unset( $block );
	};

	$walk( $blocks );
	return $counts;
}

$post_id = 4941;
$post    = get_post( $post_id );
if ( ! $post ) {
	echo "Post $post_id not found\n";
	exit( 1 );
}

$backup_dir = WP_CONTENT_DIR . '/pega-block-backups';
if ( ! is_dir( $backup_dir ) ) {
	wp_mkdir_p( $backup_dir );
}
$backup = $backup_dir . '/post-4941-' . gmdate( 'Ymd-His' ) . '-pre-inline-strip.txt';
file_put_contents( $backup, $post->post_content );
echo "Backup: $backup\n";

$blocks = parse_blocks( $post->post_content );
$counts = pega_apply_strip( $blocks );

if ( ! array_filter( $counts ) ) {
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

echo 'POST ' . $post_id . ': ' . wp_json_encode( $counts ) . "\n";

$verify = parse_blocks( get_post( $post_id )->post_content );
$issues = [];
$check  = function ( array $list ) use ( &$check, &$issues ) {
	foreach ( $list as $block ) {
		$cls = pega_class( $block );
		if ( str_contains( $cls, 'pega-stats' ) && ! empty( $block['attrs']['style']['spacing']['padding'] ) ) {
			$issues[] = 'pega-stats padding remains in JSON';
		}
		if ( str_contains( $cls, 'achivement' ) && ! empty( $block['attrs']['style']['spacing']['padding'] ) ) {
			$issues[] = 'achivement padding remains in JSON';
		}
		if ( ! empty( $block['innerBlocks'] ) ) {
			$check( $block['innerBlocks'] );
		}
	}
};
$check( $verify );
echo $issues ? 'WARN: ' . implode( '; ', $issues ) . "\n" : "JSON verify: OK\n";
echo "Done.\n";
