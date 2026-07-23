<?php
/**
 * 4941 お悩みセクション: 8枚(複製) → 10項目（4865 文言 + クライアントアイコン）
 * Usage: wp eval-file pega-update-concerns-4941.php --allow-root --path=/var/www/html
 */

if ( ! function_exists( 'parse_blocks' ) ) {
	echo "WordPress not loaded\n";
	exit( 1 );
}

/** 4865 原文（keep 分割は 4865 HTML と同一） */
$items = [
	[ 'title' => '通勤はきつい。在宅で仕事が', 'desc' => 'できないだろうか。', 'img' => 5264 ],
	[ 'title' => '新しい職場で人間関係は', 'desc' => 'うまくやっていけるか。', 'img' => 5257 ],
	[ 'title' => '朝起きて時間通りに', 'desc' => '出勤できるのだろうか。', 'img' => 5255 ],
	[ 'title' => '障害を抱えても', 'desc' => '働き続けられるか。', 'img' => 5261 ],
	[ 'title' => '病気が再発してまた', 'desc' => '休職にならないだろうか。', 'img' => 5256 ],
	[ 'title' => 'イライラを発散できず人に', 'desc' => 'あたったりしないだろうか。', 'img' => 5262 ],
	[ 'title' => '病気・障害を', 'desc' => 'うまく説明できない。', 'img' => 5263 ],
	[ 'title' => '最後に働いてから', 'desc' => 'ブランクがある。', 'img' => 5259 ],
	[ 'title' => '自己PRができない。', 'desc' => 'そもそも自分の強みがわからない。', 'img' => 5258 ],
	[ 'title' => '就職活動って何から', 'desc' => '始めてよいかわからない。', 'img' => 5260 ],
];

function pega_find_wave_section( array $blocks ): ?array {
	foreach ( $blocks as &$block ) {
		if ( str_contains( $block['attrs']['className'] ?? '', 'wave-section' ) ) {
			return $block;
		}
		if ( ! empty( $block['innerBlocks'] ) ) {
			$found = pega_find_wave_section( $block['innerBlocks'] );
			if ( $found ) {
				return $found;
			}
		}
	}
	return null;
}

function pega_find_wave_section_ref( array &$blocks ): ?array {
	foreach ( $blocks as &$block ) {
		if ( str_contains( $block['attrs']['className'] ?? '', 'wave-section' ) ) {
			return [ &$blocks, array_search( $block, $blocks, true ) ];
		}
		if ( ! empty( $block['innerBlocks'] ) ) {
			$found = pega_find_wave_section_ref( $block['innerBlocks'] );
			if ( $found ) {
				return $found;
			}
		}
	}
	return null;
}

function pega_find_template_wave_box( array $blocks ): ?array {
	foreach ( $blocks as $block ) {
		if ( str_contains( $block['attrs']['className'] ?? '', 'wave-box' ) ) {
			return $block;
		}
		if ( ! empty( $block['innerBlocks'] ) ) {
			$found = pega_find_template_wave_box( $block['innerBlocks'] );
			if ( $found ) {
				return $found;
			}
		}
	}
	return null;
}

function pega_find_closing_columns( array $section_blocks ): ?array {
	foreach ( $section_blocks as $block ) {
		if ( ( $block['blockName'] ?? '' ) !== 'core/columns' ) {
			continue;
		}
		foreach ( $block['innerBlocks'] ?? [] as $col ) {
			foreach ( $col['innerBlocks'] ?? [] as $child ) {
				if ( ( $child['blockName'] ?? '' ) === 'core/heading' ) {
					return $block;
				}
			}
		}
	}
	return null;
}

function pega_esc( string $text ): string {
	return htmlspecialchars( $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
}

function pega_build_wave_box( array $template, int $num, string $title, string $desc, int $img_id, string $meta_name ): array {
	$url = wp_get_attachment_url( $img_id );
	if ( ! $url ) {
		throw new RuntimeException( "Attachment $img_id not found" );
	}

	$num_str   = sprintf( '%02d', $num );
	$box       = json_decode( wp_json_encode( $template ), true );
	$box['attrs']['metadata']['name'] = $meta_name;

	$inner = [];

	$inner[] = [
		'blockName'    => 'core/paragraph',
		'attrs'        => [
			'className' => 'has-text-color u-mb-ctrl u-mb-0',
			'style'     => [
				'typography' => [
					'fontSize'   => '16px',
					'fontWeight' => '700',
					'textAlign'  => 'center',
				],
				'color'      => [ 'text' => '#2563eb' ],
			],
		],
		'innerBlocks'  => [],
		'innerHTML'    => "\n<p class=\"has-text-align-center has-text-color u-mb-ctrl u-mb-0\" style=\"color:#2563eb;font-size:16px;font-weight:700\">{$num_str}</p>\n",
		'innerContent' => [ "\n<p class=\"has-text-align-center has-text-color u-mb-ctrl u-mb-0\" style=\"color:#2563eb;font-size:16px;font-weight:700\">{$num_str}</p>\n" ],
	];

	$inner[] = $template['innerBlocks'][1]; // separator

	$figure_html = "\n<figure class=\"wp-block-image size-full is-resized\"><img src=\"" . esc_url( $url ) . "\" alt=\"\" class=\"wp-image-{$img_id}\" style=\"width:75px\"/></figure>\n";

	$inner[] = [
		'blockName'    => 'core/image',
		'attrs'        => [
			'id'              => $img_id,
			'width'           => '75px',
			'sizeSlug'        => 'full',
			'linkDestination' => 'none',
		],
		'innerBlocks'  => [],
		'innerHTML'    => $figure_html,
		'innerContent' => [ $figure_html ],
	];

	$inner[] = [
		'blockName'    => 'core/heading',
		'attrs'        => [
			'level'     => 4,
			'className' => 'has-text-align-center',
			'style'     => [
				'typography' => [
					'fontSize'   => '15px',
					'fontWeight' => '700',
				],
				'color'      => [ 'text' => '#0b3d8a' ],
			],
		],
		'innerBlocks'  => [],
		'innerHTML'    => "\n<h4 class=\"wp-block-heading has-text-align-center has-text-color\" style=\"color:#0b3d8a;font-size:15px;font-weight:700\">" . pega_esc( $title ) . "</h4>\n",
		'innerContent' => [ "\n<h4 class=\"wp-block-heading has-text-align-center has-text-color\" style=\"color:#0b3d8a;font-size:15px;font-weight:700\">" . pega_esc( $title ) . "</h4>\n" ],
	];

	if ( $desc !== '' ) {
		$inner[] = [
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
	} else {
		$inner[] = [
			'blockName'    => 'core/paragraph',
			'attrs'        => [
				'className' => 'has-contrast-2-color has-text-color wave-box__desc-spacer',
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
			'innerHTML'    => "\n<p class=\"has-text-align-center has-contrast-2-color has-text-color wave-box__desc-spacer\" style=\"color:#0b3d8a;font-size:12px;line-height:1.7\" aria-hidden=\"true\">&nbsp;</p>\n",
			'innerContent' => [ "\n<p class=\"has-text-align-center has-contrast-2-color has-text-color wave-box__desc-spacer\" style=\"color:#0b3d8a;font-size:12px;line-height:1.7\" aria-hidden=\"true\">&nbsp;</p>\n" ],
		];
	}

	$box['innerBlocks'] = $inner;
	return $box;
}

function pega_build_columns_row( array $template_columns, array $wave_boxes, string $className = 'u-mb-ctrl u-mb-20' ): array {
	$row           = json_decode( wp_json_encode( $template_columns ), true );
	$row['attrs']['className'] = $className;
	$col_template  = $template_columns['innerBlocks'][0];
	$columns       = [];

	foreach ( $wave_boxes as $card ) {
		$col               = json_decode( wp_json_encode( $col_template ), true );
		$col['innerBlocks'] = [ $card ];
		$columns[]         = $col;
	}

	$row['innerBlocks']  = $columns;
	$row['innerHTML']    = "\n<div class=\"wp-block-columns are-vertically-aligned-center {$className}\"></div>\n";
	$row['innerContent'] = array_merge(
		[ "\n<div class=\"wp-block-columns are-vertically-aligned-center {$className}\">" ],
		array_fill( 0, count( $columns ), null ),
		[ "</div>\n" ]
	);

	return $row;
}

function pega_replace_wave_section( array &$blocks, array $new_section_inner ): bool {
	foreach ( $blocks as &$block ) {
		if ( str_contains( $block['attrs']['className'] ?? '', 'wave-section' ) ) {
			$block['innerBlocks'] = $new_section_inner;
			return true;
		}
		if ( ! empty( $block['innerBlocks'] ) && pega_replace_wave_section( $block['innerBlocks'], $new_section_inner ) ) {
			return true;
		}
	}
	return false;
}

$post_id = 4941;
$post    = get_post( $post_id );
if ( ! $post ) {
	echo "Post $post_id not found\n";
	exit( 1 );
}

$blocks    = parse_blocks( $post->post_content );
$section   = pega_find_wave_section( $blocks );
$template  = pega_find_template_wave_box( $blocks );
$closing   = pega_find_closing_columns( $section['innerBlocks'] ?? [] );

if ( ! $section || ! $template || ! $closing ) {
	echo "Could not find wave-section template/closing\n";
	exit( 1 );
}

$template_row = null;
foreach ( $section['innerBlocks'] as $child ) {
	if ( ( $child['blockName'] ?? '' ) === 'core/columns' && count( $child['innerBlocks'] ) === 4 ) {
		$template_row = $child;
		break;
	}
}

if ( ! $template_row ) {
	echo "Could not find 4-column template row\n";
	exit( 1 );
}

$header_blocks = [];
foreach ( $section['innerBlocks'] as $child ) {
	if ( ( $child['blockName'] ?? '' ) === 'core/columns' ) {
		continue;
	}
	$header_blocks[] = $child;
}

$cards = [];
foreach ( $items as $i => $item ) {
	$n       = $i + 1;
	$cards[] = pega_build_wave_box(
		$template,
		$n,
		$item['title'],
		$item['desc'],
		$item['img'],
		sprintf( 'お悩み%02d', $n )
	);
}

$new_inner   = $header_blocks;
$new_inner[] = pega_build_columns_row( $template_row, array_slice( $cards, 0, 4 ) );
$new_inner[] = pega_build_columns_row( $template_row, array_slice( $cards, 4, 4 ) );
$new_inner[] = pega_build_columns_row( $template_row, array_slice( $cards, 8, 2 ), 'u-mb-ctrl u-mb-20 wave-cards-row-center' );
$new_inner[] = $closing;

if ( ! pega_replace_wave_section( $blocks, $new_inner ) ) {
	echo "Failed to replace wave-section\n";
	exit( 1 );
}

$new_content = serialize_blocks( $blocks );
$result      = wp_update_post(
	[
		'ID'           => $post_id,
		'post_content' => $new_content,
	],
	true
);

if ( is_wp_error( $result ) ) {
	echo 'Update failed: ' . $result->get_error_message() . "\n";
	exit( 1 );
}

$verify = parse_blocks( get_post( $post_id )->post_content );
$count  = 0;
function pega_count_cards( array $blocks, int &$count ): void {
	foreach ( $blocks as $b ) {
		if ( str_contains( $b['attrs']['className'] ?? '', 'wave-box' ) ) {
			$count++;
		}
		if ( ! empty( $b['innerBlocks'] ) ) {
			pega_count_cards( $b['innerBlocks'], $count );
		}
	}
}
pega_count_cards( $verify, $count );

echo "Updated post $post_id: $count wave-box cards\n";
foreach ( $items as $i => $item ) {
	$full = $item['title'] . $item['desc'];
	echo sprintf( "%02d: %s (img %d)\n", $i + 1, $full, $item['img'] );
}

exit( 0 );
