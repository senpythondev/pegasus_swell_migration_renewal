<?php
/**
 * STEP 1 / Week4 deletion
 * カテゴリ1の添付13件の post_parent を 1067(/homeworker/) へ付け替え保全する。
 * 親3427/3902 を削除しても、ライブ /homeworker/ の画像・動画が孤立しないようにする。
 *
 * 使い方(コンテナ内):
 *   php 01_reattach_to_homeworker.php          ← ドライラン(変更しない・確認のみ)
 *   php 01_reattach_to_homeworker.php apply     ← 実行
 *
 * 安全装置:
 *  - 対象が attachment で、現在の親が 3427/3902 であることを確認してから付け替え
 *  - 付け替え先 1067 が publish の page であることを確認
 *  - いずれか不一致なら中断(exit 1)
 */
define('WP_USE_THEMES', false);
require '/var/www/html/wp-load.php';

$APPLY = (isset($argv[1]) && $argv[1] === 'apply');
$TARGET_PARENT = 1067;
$CAT1 = array(3509,3513,3514,3516,3519,3520,3531,3552,3568,3570,3612,4136,4138); // 11(3427) + 2(3902)
$OLD_PARENTS = array(3427, 3902);

function fail($m){ fwrite(STDERR, "ABORT: $m\n"); exit(1); }
echo $APPLY ? "=== STEP1 付け替え [APPLY] ===\n" : "=== STEP1 付け替え [DRY-RUN] ===\n";

// 付け替え先の健全性
$home = get_post($TARGET_PARENT);
if ( ! $home || $home->post_status !== 'publish' || $home->post_type !== 'page' )
    fail("付け替え先 1067 が publish の page ではありません");
echo "付け替え先: ID {$TARGET_PARENT} ({$home->post_title}) OK\n";

// 事前確認
$ok = array();
foreach ($CAT1 as $id) {
    $p = get_post($id);
    if ( ! $p ) fail("添付 $id が存在しません");
    if ( $p->post_type !== 'attachment' ) fail("ID $id は attachment ではありません ({$p->post_type})");
    if ( (int)$p->post_parent === $TARGET_PARENT ) { echo "  - $id: 既に親=1067(スキップ対象)\n"; continue; }
    if ( ! in_array((int)$p->post_parent, $OLD_PARENTS, true) )
        fail("ID $id の現在の親 {$p->post_parent} が想定(3427/3902)と違います");
    $ok[] = $id;
}
echo "付け替え対象: " . count($ok) . " 件 / カテゴリ1総数 " . count($CAT1) . "\n";

if ( ! $APPLY ) {
    foreach ($ok as $id) { $p = get_post($id); echo "  would: ID $id  parent {$p->post_parent} -> {$TARGET_PARENT}\n"; }
    echo "[DRY-RUN] 変更は行っていません。apply を付けて実行してください。\n";
    exit(0);
}

// 実行
$done = 0;
foreach ($ok as $id) {
    $r = wp_update_post(array('ID'=>$id, 'post_parent'=>$TARGET_PARENT), true);
    if ( is_wp_error($r) ) fail("ID $id の付け替え失敗: " . $r->get_error_message());
    $now = (int) get_post_field('post_parent', $id);
    if ( $now !== $TARGET_PARENT ) fail("ID $id 付け替え後の親が {$now} (期待 {$TARGET_PARENT})");
    echo "  OK: ID $id parent -> {$TARGET_PARENT}\n";
    $done++;
}

// 事後確認: 13件すべてが親=1067 か
$cnt1067 = 0;
foreach ($CAT1 as $id) { if ((int)get_post_field('post_parent',$id) === $TARGET_PARENT) $cnt1067++; }
echo "事後確認: カテゴリ1のうち親=1067 は {$cnt1067}/" . count($CAT1) . " 件\n";
if ( $cnt1067 !== count($CAT1) ) fail("親=1067 が全件になっていません");
echo "STEP1 完了: {$done}件を付け替え。\n";
