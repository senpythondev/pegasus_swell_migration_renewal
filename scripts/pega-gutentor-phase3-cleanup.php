<?php
/**
 * 段階3: has-custom-css 除去 + Gutentor メタ削除（4941 / 5008）
 *
 * Usage:
 *   docker cp scripts/pega-gutentor-phase3-cleanup.php pegasus_wp:/tmp/
 *   docker exec pegasus_wp wp eval-file /tmp/pega-gutentor-phase3-cleanup.php --allow-root
 */

if ( ! function_exists( 'parse_blocks' ) ) {
	echo "WordPress not loaded\n";
	exit( 1 );
}

function pega_strip_has_custom_css( string $class_name ): string {
	$parts = array_filter(
		preg_split( '/\s+/', trim( $class_name ) ) ?: [],
		static fn( $c ) => $c !== '' && $c !== 'has-custom-css'
	);
	return implode( ' ', $parts );
}

function pega_clean_block( array &$block, int &$count ): void {
	if ( ! empty( $block['attrs']['className'] ) ) {
		$new = pega_strip_has_custom_css( $block['attrs']['className'] );
		if ( $new !== $block['attrs']['className'] ) {
			if ( $new === '' ) {
				unset( $block['attrs']['className'] );
			} else {
				$block['attrs']['className'] = $new;
			}
			++$count;
		}
	}

	if ( ! empty( $block['innerHTML'] ) && str_contains( $block['innerHTML'], 'has-custom-css' ) ) {
		$block['innerHTML'] = preg_replace( '/\s*has-custom-css/', '', $block['innerHTML'] );
		++$count;
	}

	if ( ! empty( $block['innerContent'] ) ) {
		foreach ( $block['innerContent'] as &$part ) {
			if ( is_string( $part ) && str_contains( $part, 'has-custom-css' ) ) {
				$part = preg_replace( '/\s*has-custom-css/', '', $part );
				++$count;
			}
		}
		unset( $part );
	}

	if ( ! empty( $block['innerBlocks'] ) ) {
		foreach ( $block['innerBlocks'] as &$child ) {
			pega_clean_block( $child, $count );
		}
		unset( $child );
	}
}

function pega_process_post( int $post_id ): void {
	$post = get_post( $post_id );
	if ( ! $post ) {
		echo "Post $post_id not found\n";
		return;
	}

	$blocks  = parse_blocks( $post->post_content );
	$count   = 0;
	foreach ( $blocks as &$block ) {
		pega_clean_block( $block, $count );
	}
	unset( $block );

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

	delete_post_meta( $post_id, 'gutentor_dynamic_css' );
	delete_post_meta( $post_id, 'gutentor_css_info' );

	$remaining = substr_count( get_post( $post_id )->post_content, 'has-custom-css' );
	echo "POST $post_id: stripped has-custom-css ($count touches), remaining in content: $remaining\n";
	echo "POST $post_id: deleted gutentor_dynamic_css / gutentor_css_info meta\n";
}

foreach ( [ 4941, 5008 ] as $post_id ) {
	pega_process_post( $post_id );
}

echo "Done.\n";
