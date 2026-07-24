<?php
/**
 * 5008 在宅支援: align 修正スクリプトで壊れた innerHTML / innerContent を復旧
 *
 * Usage:
 *   docker cp scripts/pega-repair-homeworker-5008.php pegasus_wp:/tmp/
 *   docker exec pegasus_wp wp eval-file /tmp/pega-repair-homeworker-5008.php --allow-root
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

function pega_set_leaf_html( array &$block, string $html ): bool {
	$old = $block['innerHTML'] ?? '';
	if ( $old === $html ) {
		return false;
	}
	$block['innerHTML'] = $html;
	if ( ! empty( $block['innerContent'] ) ) {
		foreach ( $block['innerContent'] as &$part ) {
			if ( is_string( $part ) ) {
				$part = $html;
				break;
			}
		}
		unset( $part );
	}
	return true;
}

function pega_fix_opener( string $html ): string {
	$html = str_replace( 'has-custom-css">>', "has-custom-css\">\n", $html );
	$html = str_replace( 'pc_only">>', "pc_only\">\n", $html );
	return $html;
}

function pega_repair( array &$blocks ): int {
	$changes = 0;
	foreach ( $blocks as &$root ) {
		if ( ! pega_is( $root, 'group' ) || ! str_contains( pega_class( $root ), 'pega-service-intro' ) ) {
			continue;
		}
		foreach ( $root['innerBlocks'] as &$block ) {
			$cls = pega_class( $block );

			if ( pega_is( $block, 'group' ) && str_contains( $cls, 'u-mb-10' ) && ! str_contains( $cls, 'has-background' ) ) {
				if ( ! empty( $block['innerHTML'] ) ) {
					$fixed = pega_fix_opener( $block['innerHTML'] );
					if ( $fixed !== $block['innerHTML'] ) {
						$block['innerHTML'] = $fixed;
						++$changes;
					}
				}
				if ( ! empty( $block['innerContent'] ) ) {
					foreach ( $block['innerContent'] as &$part ) {
						if ( is_string( $part ) ) {
							$fixed = pega_fix_opener( $part );
							if ( $fixed !== $part ) {
								$part = $fixed;
								++$changes;
							}
						}
					}
					unset( $part );
				}

				foreach ( $block['innerBlocks'] as &$child ) {
					$child_cls = pega_class( $child );
					if ( pega_is( $child, 'heading' ) && str_contains( $child_cls, 'is-style-section_ttl' ) ) {
						if ( pega_set_leaf_html(
							$child,
							"\n<h2 class=\"wp-block-heading has-text-align-center is-style-section_ttl has-text-color\" style=\"color:#0b3d8a;font-size:36px;font-weight:700;letter-spacing:1px\"><span style=\"\">ペガサス</span>の在宅型就労移行支援</h2>\n"
						) ) {
							++$changes;
						}
					}
					if ( pega_is( $child, 'paragraph' ) && str_contains( $child_cls, 'sub-heading' ) ) {
						if ( pega_set_leaf_html(
							$child,
							"\n<p class=\"has-text-align-center has-text-color u-mb-ctrl u-mb-0 sub-heading\" style=\"color:#475569;font-size:14px;line-height:2\"><strong>ご自宅で「働くための訓練」を行い、在宅勤務という目標を応援します。<br>あなたらしい働き方で、「働きたい」という気持ちを、私たちが支えます。</strong></p>\n"
						) ) {
							++$changes;
						}
					}
				}
				unset( $child );
			}

			if ( pega_is( $block, 'columns' ) && str_contains( $cls, 'girl' ) ) {
				if ( ! empty( $block['innerHTML'] ) ) {
					$fixed = pega_fix_opener( $block['innerHTML'] );
					if ( $fixed !== $block['innerHTML'] ) {
						$block['innerHTML'] = $fixed;
						++$changes;
					}
				}
				if ( ! empty( $block['innerContent'] ) ) {
					foreach ( $block['innerContent'] as &$part ) {
						if ( is_string( $part ) ) {
							$fixed = pega_fix_opener( $part );
							if ( $fixed !== $part ) {
								$part = $fixed;
								++$changes;
							}
						}
					}
					unset( $part );
				}
			}
		}
		unset( $block );
	}
	unset( $root );
	return $changes;
}

$post_id = 5008;
$post    = get_post( $post_id );
if ( ! $post ) {
	echo "Post $post_id not found\n";
	exit( 1 );
}

$blocks  = parse_blocks( $post->post_content );
$changes = pega_repair( $blocks );

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

echo "POST $post_id: repaired $changes issue(s)\n";
echo "Done.\n";
