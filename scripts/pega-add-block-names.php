<?php
/**
 * 同期パターン / swell-page 用: ブロック metadata.name 付与
 * Usage: wp eval-file pega-add-block-names.php --allow-root --path=/var/www/html
 */

if ( ! function_exists( 'parse_blocks' ) ) {
	echo "WordPress not loaded\n";
	exit( 1 );
}

function pega_set_name( array &$block, string $name ): void {
	if ( ! isset( $block['attrs'] ) || ! is_array( $block['attrs'] ) ) {
		$block['attrs'] = [];
	}
	if ( ! isset( $block['attrs']['metadata'] ) || ! is_array( $block['attrs']['metadata'] ) ) {
		$block['attrs']['metadata'] = [];
	}
	$block['attrs']['metadata']['name'] = $name;
}

function pega_class( array $block ): string {
	return $block['attrs']['className'] ?? '';
}

function pega_is( array $block, string $type ): bool {
	$name = $block['blockName'] ?? '';
	return $name === $type || str_ends_with( $name, '/' . $type );
}

function pega_name_tree( array &$blocks, callable $namer, int $depth = 0 ): void {
	foreach ( $blocks as &$block ) {
		$namer( $block, $depth );
		if ( ! empty( $block['innerBlocks'] ) ) {
			pega_name_tree( $block['innerBlocks'], $namer, $depth + 1 );
		}
	}
}

function pega_name_post_5210( array &$blocks ): void {
	foreach ( $blocks as &$root ) {
		if ( pega_is( $root, 'group' ) && str_contains( pega_class( $root ), 'pega-header' ) ) {
			pega_set_name( $root, 'ヘッダー' );
		}
		if ( ! isset( $root['innerBlocks'][0]['innerBlocks'] ) ) {
			continue;
		}
		pega_set_name( $root['innerBlocks'][0], 'ヘッダー：メインレイアウト' );
		$flex_children = &$root['innerBlocks'][0]['innerBlocks'];
		if ( isset( $flex_children[0] ) ) {
			pega_set_name( $flex_children[0], 'ヘッダー：ロゴ・タグライン' );
			if ( isset( $flex_children[0]['innerBlocks'][0] ) ) {
				pega_set_name( $flex_children[0]['innerBlocks'][0], 'ヘッダー：ロゴ' );
			}
		}
		if ( isset( $flex_children[1] ) ) {
			pega_set_name( $flex_children[1], 'ヘッダー：電話・問い合わせ' );
			if ( isset( $flex_children[1]['innerBlocks'][0] ) && str_contains( pega_class( $flex_children[1]['innerBlocks'][0] ), 'pega-header__tel' ) ) {
				pega_set_name( $flex_children[1]['innerBlocks'][0], 'ヘッダー：電話番号' );
			}
		}
	}
}

function pega_name_post_5008( array &$blocks ): void {
	foreach ( $blocks as &$root ) {
		if ( ! pega_is( $root, 'group' ) || ! str_contains( pega_class( $root ), 'pega-service-intro' ) ) {
			continue;
		}
		pega_set_name( $root, '在宅支援' );
		pega_name_tree(
			$root['innerBlocks'],
			function ( array &$block ) {
				if ( pega_is( $block, 'group' ) && str_contains( pega_class( $block ), 'u-mb-10' ) ) {
					pega_set_name( $block, '在宅支援：セクション見出し' );
					return;
				}
				if ( pega_is( $block, 'cover' ) && str_contains( pega_class( $block ), 'mobile-girl' ) ) {
					pega_set_name( $block, '在宅支援：人物画像' );
					if ( ! empty( $block['innerBlocks'][0] ) && pega_is( $block['innerBlocks'][0], 'group' ) ) {
						pega_set_name( $block['innerBlocks'][0], '在宅支援：人物画像（テキスト）' );
					}
					return;
				}
				if ( pega_is( $block, 'group' ) && str_contains( pega_class( $block ), 'u-mb-0' ) && ! str_contains( pega_class( $block ), 'pega-service-intro' ) && ! str_contains( pega_class( $block ), 'u-mb-10' ) ) {
					pega_set_name( $block, '在宅支援：リード文エリア' );
					return;
				}
				if ( pega_is( $block, 'columns' ) && str_contains( pega_class( $block ), 'girl' ) ) {
					pega_set_name( $block, '在宅支援：リード文' );
					return;
				}
				if ( pega_is( $block, 'columns' ) && str_contains( pega_class( $block ), 'supports' ) ) {
					pega_set_name( $block, '在宅支援：3つの強み' );
					$i = 0;
					foreach ( $block['innerBlocks'] as &$col ) {
						$i++;
						if ( $i <= 3 ) {
							pega_set_name( $col, '在宅支援：強み' . $i );
						}
					}
				}
			}
		);
	}
}

function pega_name_post_4941( array &$blocks ): void {
	$wave_idx    = 0;
	$achieve_idx = 0;

	pega_name_tree(
		$blocks,
		function ( array &$block, int $depth ) use ( &$wave_idx, &$achieve_idx ) {
			$cls  = pega_class( $block );
			$name = $block['attrs']['metadata']['name'] ?? '';

			if ( pega_is( $block, 'block' ) ) {
				$ref = (int) ( $block['attrs']['ref'] ?? 0 );
				if ( 5210 === $ref ) {
					pega_set_name( $block, 'ヘッダー' );
				} elseif ( 5008 === $ref ) {
					pega_set_name( $block, '在宅支援' );
				}
				return;
			}

			if ( pega_is( $block, 'group' ) ) {
				if ( $depth === 0 && ( $name === 'FV' || in_array( $name, [ '02 - FV / Hero', 'FV / Hero' ], true ) || ( str_contains( $name, 'Hero' ) && ! str_contains( $name, '：' ) ) ) ) {
					pega_set_name( $block, 'FV' );
					return;
				}
				if ( str_contains( $cls, 'pega-stats' ) ) {
					pega_set_name( $block, '実績バー' );
					return;
				}
				if ( str_contains( $cls, 'achivement' ) ) {
					pega_set_name( $block, '実績バー：実績数値' );
					return;
				}
				if ( str_contains( $cls, 'wave-box' ) ) {
					$wave_idx++;
					pega_set_name( $block, sprintf( 'お悩みカード%02d', $wave_idx ) );
					return;
				}
				if ( str_contains( $cls, 'wave-section' ) || str_contains( $name, 'Concerns' ) || $name === '04 - Concerns / お悩み' ) {
					pega_set_name( $block, 'お悩み' );
					return;
				}
				if ( $depth === 0 && str_contains( $cls, 'u-mb-ctrl' ) && ! str_contains( $cls, 'pega-stats' ) && ! str_contains( $cls, 'wave' ) ) {
					pega_set_name( $block, 'FV' );
					return;
				}
				if ( str_contains( $cls, 'alignfull' ) ) {
					pega_set_name( $block, 'FV：レイアウト' );
					return;
				}
				if ( $depth >= 2 && str_contains( $cls, 'u-mb-ctrl' ) && ! str_contains( $cls, 'pega-stats' ) && ! str_contains( $cls, 'wave' ) && ! str_contains( $cls, 'main-text' ) ) {
					pega_set_name( $block, 'FV：コンテンツ' );
					return;
				}
			}

			if ( pega_is( $block, 'cover' ) && str_contains( $cls, 'main-text' ) ) {
				pega_set_name( $block, 'FV：背景・コピー' );
				return;
			}

			if ( pega_is( $block, 'image' ) && (int) ( $block['attrs']['id'] ?? 0 ) === 5098 ) {
				pega_set_name( $block, '波型装飾' );
				return;
			}

			if ( pega_is( $block, 'column' ) ) {
				if ( str_contains( $cls, 'box-achievement' ) ) {
					$achieve_idx++;
					pega_set_name( $block, '実績：項目' . $achieve_idx );
					return;
				}
				if ( str_contains( $cls, 'text-box' ) ) {
					pega_set_name( $block, 'お悩み：締め見出し' );
				}
			}
		}
	);
}

function pega_apply_names( int $post_id, callable $fn ): bool {
	$post = get_post( $post_id );
	if ( ! $post ) {
		echo "Post $post_id not found\n";
		return false;
	}
	$blocks      = parse_blocks( $post->post_content );
	$fn( $blocks );
	$new_content = serialize_blocks( $blocks );
	$result      = wp_update_post(
		[
			'ID'           => $post_id,
			'post_content' => $new_content,
		],
		true
	);
	if ( is_wp_error( $result ) ) {
		echo "Error updating $post_id: " . $result->get_error_message() . "\n";
		return false;
	}
	echo "Updated post $post_id ({$post->post_title})\n";
	return true;
}

$ok = true;
$ok = pega_apply_names( 5210, 'pega_name_post_5210' ) && $ok;
$ok = pega_apply_names( 5008, 'pega_name_post_5008' ) && $ok;
$ok = pega_apply_names( 4941, 'pega_name_post_4941' ) && $ok;

foreach ( [ 5210, 5008, 4941 ] as $id ) {
	$c     = get_post( $id )->post_content;
	$count = preg_match_all( '/"metadata":\s*\{\s*"name":\s*"[^"]+"/', $c );
	echo "Post $id metadata.name count: $count\n";
}

exit( $ok ? 0 : 1 );
