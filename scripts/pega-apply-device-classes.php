<?php
/**
 * SWELL 標準デバイス制限 (pc_only / sp_only) を対象ブロックへ付与
 *
 * Usage: docker exec pegasus_wp wp eval-file /var/www/html/../scripts/pega-apply-device-classes.php --allow-root
 *   (mount: scripts/ をコンテナから参照できるよう wp eval で直接 eval する)
 *
 * 実際: wp eval-file は wp_data 内から実行するため、docker exec で eval 内包
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
	return $name === $type || str_ends_with( $name, '/' . $type );
}

/**
 * className に pc_only / sp_only を付与（既存クラスは維持、重複・競合は解消）
 */
function pega_add_device_class( array &$block, string $device ): bool {
	$allowed = [ 'pc_only', 'sp_only' ];
	if ( ! in_array( $device, $allowed, true ) ) {
		return false;
	}
	$other   = 'pc_only' === $device ? 'sp_only' : 'pc_only';
	$current = pega_class( $block );
	$parts   = array_filter( preg_split( '/\s+/', trim( $current ) ) ?: [] );
	$parts   = array_values( array_diff( $parts, [ $other ] ) );
	if ( in_array( $device, $parts, true ) ) {
		pega_sync_device_to_html( $block );
		return false;
	}
	$parts[] = $device;
	if ( ! isset( $block['attrs'] ) || ! is_array( $block['attrs'] ) ) {
		$block['attrs'] = [];
	}
	$block['attrs']['className'] = implode( ' ', $parts );
	pega_sync_device_to_html( $block );
	return true;
}

/**
 * 静的ブロックは innerHTML の class がそのまま出力されるため、attrs と HTML を同期する。
 */
function pega_sync_device_to_html( array &$block ): void {
	$cls     = pega_class( $block );
	$devices = array_values(
		array_filter(
			[ 'pc_only', 'sp_only' ],
			static fn( $d ) => str_contains( $cls, $d )
		)
	);
	if ( empty( $devices ) ) {
		return;
	}

	$patch = static function ( string $html ) use ( $devices ): string {
		if ( ! preg_match( '/^(\s*<\w+[^>]*\sclass=")([^"]*)(")/s', $html, $m ) ) {
			return $html;
		}
		$parts = array_filter( preg_split( '/\s+/', $m[2] ) ?: [] );
		$parts = array_values( array_diff( $parts, [ 'pc_only', 'sp_only' ] ) );
		foreach ( $devices as $d ) {
			$parts[] = $d;
		}
		return $m[1] . implode( ' ', $parts ) . $m[3] . substr( $html, strlen( $m[0] ) );
	};

	if ( ! empty( $block['innerHTML'] ) ) {
		$block['innerHTML'] = $patch( $block['innerHTML'] );
	}
	if ( ! empty( $block['innerContent'] ) ) {
		foreach ( $block['innerContent'] as &$part ) {
			if ( is_string( $part ) && str_contains( $part, 'class="' ) ) {
				$part = $patch( $part );
			}
		}
		unset( $part );
	}
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

function pega_apply_4941( array &$blocks ): int {
	return pega_walk(
		$blocks,
		function ( array &$block ) {
			$cls = pega_class( $block );
			if ( pega_is( $block, 'group' ) && str_contains( $cls, 'achivement' ) ) {
				return pega_add_device_class( $block, 'pc_only' );
			}
			if ( pega_is( $block, 'image' ) && str_contains( $cls, 'mobile-img' ) ) {
				return pega_add_device_class( $block, 'sp_only' );
			}
			if ( pega_is( $block, 'image' ) && (int) ( $block['attrs']['id'] ?? 0 ) === 5098 ) {
				return pega_add_device_class( $block, 'pc_only' );
			}
			return false;
		}
	);
}

function pega_apply_5008( array &$blocks ): int {
	return pega_walk(
		$blocks,
		function ( array &$block ) {
			$cls = pega_class( $block );
			if ( pega_is( $block, 'columns' ) && str_contains( $cls, 'girl' ) ) {
				return pega_add_device_class( $block, 'pc_only' );
			}
			if ( pega_is( $block, 'cover' ) && str_contains( $cls, 'mobile-girl' ) ) {
				return pega_add_device_class( $block, 'sp_only' );
			}
			return false;
		}
	);
}

function pega_apply_5210( array &$blocks ): int {
	$count = 0;
	pega_walk(
		$blocks,
		function ( array &$block ) use ( &$count ) {
			if ( ! pega_is( $block, 'group' ) || ! str_contains( pega_class( $block ), 'pega-header__tel' ) ) {
				return false;
			}
			foreach ( $block['innerBlocks'] as &$child ) {
				if ( ! pega_is( $child, 'paragraph' ) ) {
					continue;
				}
				$cls = pega_class( $child );
				if ( str_contains( $cls, 'mobile' ) ) {
					if ( pega_add_device_class( $child, 'sp_only' ) ) {
						++$count;
					}
				} else {
					if ( pega_add_device_class( $child, 'pc_only' ) ) {
						++$count;
					}
				}
			}
			return false;
		}
	);
	return $count;
}

function pega_sync_all_device_html( array &$blocks ): int {
	$count = 0;
	pega_walk(
		$blocks,
		function ( array &$block ) use ( &$count ) {
			$cls = pega_class( $block );
			if ( str_contains( $cls, 'pc_only' ) || str_contains( $cls, 'sp_only' ) ) {
				pega_sync_device_to_html( $block );
				++$count;
			}
			return false;
		}
	);
	return $count;
}

function pega_update_post( int $post_id, callable $apply ): void {
	$post = get_post( $post_id );
	if ( ! $post ) {
		echo "POST $post_id not found\n";
		return;
	}
	$blocks = parse_blocks( $post->post_content );
	$n      = $apply( $blocks );
	$synced = pega_sync_all_device_html( $blocks );
	if ( 0 === $n && 0 === $synced ) {
		echo "POST $post_id: no changes\n";
		return;
	}
	$content = serialize_blocks( $blocks );
	$result  = wp_update_post(
		[
			'ID'           => $post_id,
			'post_content' => $content,
		],
		true
	);
	if ( is_wp_error( $result ) ) {
		echo "POST $post_id: ERROR " . $result->get_error_message() . "\n";
		exit( 1 );
	}
	echo "POST $post_id: updated $n block(s), synced $synced html\n";
}

pega_update_post( 4941, 'pega_apply_4941' );
pega_update_post( 5008, 'pega_apply_5008' );
pega_update_post( 5210, 'pega_apply_5210' );

echo "Done.\n";
