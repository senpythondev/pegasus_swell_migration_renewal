<?php
/**
 * 4941 お悩み07-10: heading の JSON typography と innerHTML style を同期
 * 原因: pega-fix-concerns-split-4941.php が attrs から fontSize だけ削除し innerHTML に残した
 *
 * Usage:
 *   docker cp scripts/pega-fix-concerns-heading-sync-4941.php pegasus_wp:/tmp/
 *   docker exec pegasus_wp wp eval-file /tmp/pega-fix-concerns-heading-sync-4941.php --allow-root
 */

if ( ! function_exists( 'parse_blocks' ) ) {
	echo "WordPress not loaded\n";
	exit( 1 );
}

$targets = [ 'お悩み07', 'お悩み08', 'お悩み09', 'お悩み10' ];

function pega_sync_heading( array &$block ): bool {
	if ( ( $block['blockName'] ?? '' ) !== 'core/heading' ) {
		return false;
	}
	if ( ! isset( $block['attrs']['style'] ) || ! is_array( $block['attrs']['style'] ) ) {
		$block['attrs']['style'] = [];
	}
	if ( ! isset( $block['attrs']['style']['typography'] ) || ! is_array( $block['attrs']['style']['typography'] ) ) {
		$block['attrs']['style']['typography'] = [];
	}

	$typo = &$block['attrs']['style']['typography'];
	$changed = false;

	if ( empty( $typo['fontSize'] ) ) {
		$typo['fontSize'] = '15px';
		$changed           = true;
	}
	if ( empty( $typo['fontWeight'] ) ) {
		$typo['fontWeight'] = '700';
		$changed             = true;
	}
	if ( empty( $typo['textAlign'] ) && str_contains( $block['attrs']['className'] ?? '', 'has-text-align-center' ) ) {
		$typo['textAlign'] = 'center';
		$changed            = true;
	}
	if ( empty( $block['attrs']['style']['color']['text'] ) ) {
		$block['attrs']['style']['color']['text'] = '#0b3d8a';
		$changed                                   = true;
	}

	// save() が生成する style 属性値（検証で確認済み）
	$expected_style = 'color:#0b3d8a;font-size:15px;font-weight:700';
	$content        = '';
	if ( preg_match( '/>([^<]*)<\/h4>/', $block['innerHTML'] ?? '', $m ) ) {
		$content = $m[1];
	}

	$new_html = "\n<h4 class=\"wp-block-heading has-text-align-center has-text-color\" style=\"{$expected_style}\">{$content}</h4>\n";
	if ( ( $block['innerHTML'] ?? '' ) !== $new_html ) {
		$block['innerHTML'] = $new_html;
		$changed             = true;
	}
	if ( ! empty( $block['innerContent'] ) ) {
		foreach ( $block['innerContent'] as &$part ) {
			if ( is_string( $part ) && str_contains( $part, '<h4' ) ) {
				if ( $part !== $new_html ) {
					$part    = $new_html;
					$changed = true;
				}
				break;
			}
		}
		unset( $part );
	}

	return $changed;
}

function pega_walk( array &$blocks, array $targets, int &$count ): bool {
	$changed = false;
	foreach ( $blocks as &$block ) {
		$name = $block['attrs']['metadata']['name'] ?? '';
		if ( str_contains( $block['attrs']['className'] ?? '', 'wave-box' ) && in_array( $name, $targets, true ) ) {
			foreach ( $block['innerBlocks'] as &$child ) {
				if ( pega_sync_heading( $child ) ) {
					++$count;
					$changed = true;
				}
			}
			unset( $child );
		}
		if ( ! empty( $block['innerBlocks'] ) && pega_walk( $block['innerBlocks'], $targets, $count ) ) {
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
$count   = 0;
$changed = pega_walk( $blocks, $targets, $count );

if ( ! $changed ) {
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

echo "POST $post_id: synced $count heading(s)\n";
echo "Done.\n";
