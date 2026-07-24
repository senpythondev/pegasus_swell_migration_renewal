<?php
/**
 * 4941: 5210 ヘッダー参照を削除（5210 パターン自体は残す）
 * 4929: テンプレートを page-pega-fullwidth.php に切替
 *
 * Usage:
 *   docker cp scripts/pega-gutentor-phase2-switch.php pegasus_wp:/tmp/
 *   docker exec pegasus_wp wp eval-file /tmp/pega-gutentor-phase2-switch.php --allow-root
 */

if ( ! function_exists( 'parse_blocks' ) ) {
	echo "WordPress not loaded\n";
	exit( 1 );
}

function pega_remove_header_ref( array &$blocks ): bool {
	$changed = false;
	foreach ( $blocks as $index => $block ) {
		if ( ( $block['blockName'] ?? '' ) === 'core/block'
			&& (int) ( $block['attrs']['ref'] ?? 0 ) === 5210
		) {
			array_splice( $blocks, $index, 1 );
			return true;
		}
		if ( ! empty( $block['innerBlocks'] ) && pega_remove_header_ref( $block['innerBlocks'] ) ) {
			return true;
		}
	}
	return $changed;
}

$post_id = 4941;
$post    = get_post( $post_id );
if ( ! $post ) {
	echo "Post $post_id not found\n";
	exit( 1 );
}

$blocks  = parse_blocks( $post->post_content );
$removed = pega_remove_header_ref( $blocks );

if ( ! $removed ) {
	echo "POST $post_id: 5210 ref not found (already removed?)\n";
} else {
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
	echo "POST $post_id: removed 5210 header ref\n";
}

$page_id = 4929;
$tpl     = wp_update_post(
	[
		'ID' => $page_id,
	],
	true
);
if ( is_wp_error( $tpl ) ) {
	echo "POST $page_id: ERROR " . $tpl->get_error_message() . "\n";
	exit( 1 );
}

update_post_meta( $page_id, '_wp_page_template', 'page-pega-fullwidth.php' );
echo "POST $page_id: template -> page-pega-fullwidth.php\n";

// 検証: 5210 参照が残っていないこと
$verify = parse_blocks( get_post( $post_id )->post_content );
foreach ( $verify as $block ) {
	if ( ( $block['blockName'] ?? '' ) === 'core/block' && (int) ( $block['attrs']['ref'] ?? 0 ) === 5210 ) {
		echo "VERIFY FAIL: 5210 ref still in 4941\n";
		exit( 1 );
	}
}

echo "VERIFY OK: 5210 ref removed, template updated\n";
echo "Done.\n";
