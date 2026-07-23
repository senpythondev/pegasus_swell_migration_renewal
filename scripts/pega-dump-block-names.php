<?php
function pega_tree( array $blocks, int $depth = 0 ): void {
	foreach ( $blocks as $block ) {
		$name = $block['attrs']['metadata']['name'] ?? null;
		$type = $block['blockName'] ?? '(null)';
		$cls  = $block['attrs']['className'] ?? '';
		if ( $name || 'core/group' === $type || 'core/block' === $type ) {
			$label = $name ?: '【グループ・無名】';
			echo str_repeat( '  ', $depth ) . $label . ( $cls ? " ({$cls})" : '' ) . "\n";
		}
		if ( ! empty( $block['innerBlocks'] ) ) {
			pega_tree( $block['innerBlocks'], $depth + ( $name ? 1 : 0 ) );
		}
	}
}

foreach (
	[
		5210 => '5210 ヘッダー',
		5008 => '5008 サービス紹介',
		4941 => '4941 完全版',
	] as $id => $title
) {
	echo "\n=== {$title} ===\n";
	pega_tree( parse_blocks( get_post( $id )->post_content ) );
}
