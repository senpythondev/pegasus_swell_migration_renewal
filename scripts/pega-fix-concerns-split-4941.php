<?php
/**
 * 4941 お悩みカード 07〜10: 見出し+説明文の2段構成に統一（4865 原文を分割のみ）
 * Usage: docker cp ... && wp eval-file pega-fix-concerns-split-4941.php --allow-root --path=/var/www/html
 */

if ( ! function_exists( 'parse_blocks' ) ) {
	echo "WordPress not loaded\n";
	exit( 1 );
}

$splits = [
	'お悩み07' => [ 'title' => '病気・障害を', 'desc' => 'うまく説明できない。' ],
	'お悩み08' => [ 'title' => '最後に働いてから', 'desc' => 'ブランクがある。' ],
	'お悩み09' => [ 'title' => '自己PRができない。', 'desc' => 'そもそも自分の強みがわからない。' ],
	'お悩み10' => [ 'title' => '就職活動って何から', 'desc' => '始めてよいかわからない。' ],
];

function pega_esc( string $text ): string {
	return htmlspecialchars( $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
}

function pega_desc_paragraph( string $desc ): array {
	return [
		'blockName'    => 'core/paragraph',
		'attrs'        => [
			'className' => 'has-contrast-2-color has-text-color',
			'style'     => [
				'typography' => [
					'fontSize'   => '12px',
					'lineHeight' => '1.7',
					'textAlign'  => 'center',
				],
				'color'      => [ 'text' => '#0b3d8a' ],
			],
		],
		'innerBlocks'  => [],
		'innerHTML'    => "\n<p class=\"has-text-align-center has-contrast-2-color has-text-color\" style=\"color:#0b3d8a;font-size:12px;line-height:1.7\">" . pega_esc( $desc ) . "</p>\n",
		'innerContent' => [ "\n<p class=\"has-text-align-center has-contrast-2-color has-text-color\" style=\"color:#0b3d8a;font-size:12px;line-height:1.7\">" . pega_esc( $desc ) . "</p>\n" ],
	];
}

function pega_update_wave_boxes( array &$blocks, array $splits, int &$count ): bool {
	$changed = false;
	foreach ( $blocks as &$block ) {
		$name = $block['attrs']['metadata']['name'] ?? '';
		if ( str_contains( $block['attrs']['className'] ?? '', 'wave-box' ) && isset( $splits[ $name ] ) ) {
			$split = $splits[ $name ];
			foreach ( $block['innerBlocks'] as &$child ) {
				if ( ( $child['blockName'] ?? '' ) === 'core/heading' ) {
					$title                           = $split['title'];
					$child['innerHTML']              = "\n<h4 class=\"wp-block-heading has-text-align-center has-text-color\" style=\"color:#0b3d8a;font-size:15px;font-weight:700\">" . pega_esc( $title ) . "</h4>\n";
					$child['innerContent']           = [ $child['innerHTML'] ];
					$child['attrs']['style']['typography']['fontWeight'] = '700';
					unset( $child['attrs']['style']['typography']['fontSize'] );
					$changed = true;
				}
				if ( ( $child['blockName'] ?? '' ) === 'core/paragraph' && str_contains( $child['attrs']['className'] ?? '', 'wave-box__desc-spacer' ) ) {
					$child = pega_desc_paragraph( $split['desc'] );
					$changed = true;
				}
			}
			unset( $child );
			$count++;
		}
		if ( ! empty( $block['innerBlocks'] ) && pega_update_wave_boxes( $block['innerBlocks'], $splits, $count ) ) {
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

$blocks = parse_blocks( $post->post_content );
$count  = 0;
if ( ! pega_update_wave_boxes( $blocks, $splits, $count ) ) {
	echo "No wave-box cards updated\n";
	exit( 1 );
}

$result = wp_update_post(
	[
		'ID'           => $post_id,
		'post_content' => serialize_blocks( $blocks ),
	],
	true
);

if ( is_wp_error( $result ) ) {
	echo 'Update failed: ' . $result->get_error_message() . "\n";
	exit( 1 );
}

echo "Updated $count cards in post $post_id\n";
foreach ( $splits as $name => $split ) {
	echo "$name: {$split['title']} | {$split['desc']}\n";
}

exit( 0 );
