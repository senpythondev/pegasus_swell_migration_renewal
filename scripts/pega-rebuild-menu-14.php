<?php
/**
 * グローバルメニュー ID14 を確定仕様に組み替える。
 *
 * Usage:
 *   docker cp scripts/pega-rebuild-menu-14.php pegasus_wp:/tmp/
 *   docker exec pegasus_wp wp eval-file /tmp/pega-rebuild-menu-14.php --allow-root
 */

if ( ! function_exists( 'wp_update_nav_menu_item' ) ) {
	echo "WordPress not loaded\n";
	exit( 1 );
}

$menu_id = 14;

/** @var int[] 削除対象(メニュー項目のみ。リンク先ページは削除しない) */
$remove_ids = [
	36,   // HOME
	161,  // スタッフ求人情報 /recruit/
	160,  // プライバシーポリシー /privacy/
	862,  // スタッフブログ /category/staff/
	1396, // 同窓会 /category/reunion/
	48,   // CTA 親(別実装のため nav から除外)
	584,  // 個別説明会申し込み
	163,  // お問い合わせ
	162,  // 資料請求
	165,  // 代表コラム(子) → 親を単一リンク化
];

/** 既存 nav_menu_item ID の再利用 */
$ids = [
	'homeworker' => 1206,
	'services'   => 37,
	'guide'      => 497,
	'program'    => 143,
	'practice'   => 707,
	'result'     => 140,
	'aftercare'  => 139,
	'access'     => 146,
	'voices'     => 42,   // 旧「ペガサスとは」親 → 実績・お声
	'user_voice' => 496,
	'user_dec'   => 1395,
	'column'     => 1207, // 旧親 → 単一「コラム・ブログ」
	'about'      => 144,
];

$page_ids = [
	'homeworker' => 1067,
	'guide'      => 68,
	'program'    => 129,
	'practice'   => 310,
	'result'     => 135,
	'aftercare'  => 137,
	'access'     => 110,
	'about'      => 56,
];

$term_ids = [
	'user_voice' => 5,
	'user_dec'   => 8,
];

function pega_update_page_item( int $menu_id, int $item_id, string $title, int $page_id, int $parent = 0, int $position = 0 ): void {
	wp_update_nav_menu_item(
		$menu_id,
		$item_id,
		[
			'menu-item-title'     => $title,
			'menu-item-type'      => 'post_type',
			'menu-item-object'    => 'page',
			'menu-item-object-id' => $page_id,
			'menu-item-url'       => get_permalink( $page_id ),
			'menu-item-parent-id' => $parent,
			'menu-item-position'  => $position,
			'menu-item-status'    => 'publish',
		]
	);
}

function pega_update_tax_item( int $menu_id, int $item_id, string $title, int $term_id, int $parent = 0, int $position = 0 ): void {
	$link = get_term_link( (int) $term_id, 'category' );
	if ( is_wp_error( $link ) ) {
		throw new RuntimeException( "term link failed for $term_id" );
	}
	wp_update_nav_menu_item(
		$menu_id,
		$item_id,
		[
			'menu-item-title'     => $title,
			'menu-item-type'      => 'taxonomy',
			'menu-item-object'    => 'category',
			'menu-item-object-id' => $term_id,
			'menu-item-url'       => $link,
			'menu-item-parent-id' => $parent,
			'menu-item-position'  => $position,
			'menu-item-status'    => 'publish',
		]
	);
}

function pega_update_custom_item( int $menu_id, int $item_id, string $title, string $url, int $parent = 0, int $position = 0 ): void {
	wp_update_nav_menu_item(
		$menu_id,
		$item_id,
		[
			'menu-item-title'     => $title,
			'menu-item-type'      => 'custom',
			'menu-item-url'       => $url,
			'menu-item-parent-id' => $parent,
			'menu-item-position'  => $position,
			'menu-item-status'    => 'publish',
		]
	);
}

function pega_update_archive_item( int $menu_id, int $item_id, string $title, string $post_type, int $parent = 0, int $position = 0 ): void {
	$url = get_post_type_archive_link( $post_type );
	if ( ! $url ) {
		throw new RuntimeException( "archive link missing for $post_type" );
	}
	wp_update_nav_menu_item(
		$menu_id,
		$item_id,
		[
			'menu-item-title'     => $title,
			'menu-item-type'      => 'post_type_archive',
			'menu-item-object'    => $post_type,
			'menu-item-url'       => $url,
			'menu-item-parent-id' => $parent,
			'menu-item-position'  => $position,
			'menu-item-status'    => 'publish',
		]
	);
}

// 1. 在宅型就労移行支援
pega_update_page_item( $menu_id, $ids['homeworker'], '在宅型就労移行支援', $page_ids['homeworker'], 0, 1 );

// 2. サービス・サポート ▾
pega_update_custom_item( $menu_id, $ids['services'], 'サービス・サポート', '#', 0, 2 );
pega_update_page_item( $menu_id, $ids['guide'], 'ご利用の流れ', $page_ids['guide'], $ids['services'], 1 );
pega_update_page_item( $menu_id, $ids['program'], 'プログラム内容', $page_ids['program'], $ids['services'], 2 );
pega_update_page_item( $menu_id, $ids['practice'], '企業実習', $page_ids['practice'], $ids['services'], 3 );
pega_update_page_item( $menu_id, $ids['result'], '就職活動サポート', $page_ids['result'], $ids['services'], 4 );
pega_update_page_item( $menu_id, $ids['aftercare'], '就職後のアフターケア', $page_ids['aftercare'], $ids['services'], 5 );
pega_update_page_item( $menu_id, $ids['access'], '通所をご希望の方へ|平塚教室', $page_ids['access'], $ids['services'], 6 );

// 3. 実績・お声 ▾ — reports は新規、既存 taxonomy は再利用
pega_update_custom_item( $menu_id, $ids['voices'], '実績・お声', '#', 0, 3 );

$reports_id = wp_update_nav_menu_item(
	$menu_id,
	0,
	[
		'menu-item-title'     => '職場実習レポート',
		'menu-item-type'      => 'post_type_archive',
		'menu-item-object'    => 'reports',
		'menu-item-url'       => get_post_type_archive_link( 'reports' ),
		'menu-item-parent-id' => $ids['voices'],
		'menu-item-position'  => 1,
		'menu-item-status'    => 'publish',
	]
);
if ( is_wp_error( $reports_id ) ) {
	echo 'ERROR reports: ' . $reports_id->get_error_message() . "\n";
	exit( 1 );
}

pega_update_tax_item( $menu_id, $ids['user_voice'], '利用者の声', $term_ids['user_voice'], $ids['voices'], 2 );
pega_update_tax_item( $menu_id, $ids['user_dec'], '就労決定者の声', $term_ids['user_dec'], $ids['voices'], 3 );

// 4. コラム・ブログ（CPT works /column/）
pega_update_archive_item( $menu_id, $ids['column'], 'コラム・ブログ', 'works', 0, 4 );

// 5. ペガサスについて
pega_update_page_item( $menu_id, $ids['about'], 'ペガサスについて', $page_ids['about'], 0, 5 );

// 除外項目をメニューから削除
foreach ( $remove_ids as $rid ) {
	$result = wp_delete_post( $rid, true );
	if ( ! $result ) {
		echo "WARN: could not delete menu item $rid\n";
	}
}

echo "Menu $menu_id rebuilt.\n";
echo 'reports item id: ' . $reports_id . "\n";

$items = wp_get_nav_menu_items( $menu_id );
$out   = [];
foreach ( $items as $i ) {
	$out[] = [
		'db_id'     => (int) $i->ID,
		'title'     => $i->title,
		'url'       => $i->url,
		'type'      => $i->type,
		'object'    => $i->object,
		'object_id' => (int) $i->object_id,
		'parent'    => (int) $i->menu_item_parent,
		'position'  => (int) $i->menu_order,
	];
}
echo wp_json_encode( $out, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) . "\n";
