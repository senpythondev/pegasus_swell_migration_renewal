<?php
/**
 * STEP 2 / Week4 deletion
 * 削除対象の親投稿21件(service4/company5/page12)を完全削除する。
 * リビジョンは wp_delete_post により自動で一緒に削除される。
 *
 * 使い方(コンテナ内):
 *   php 02_delete_21_posts.php          ← ドライラン
 *   php 02_delete_21_posts.php apply     ← 実行
 *
 * 安全装置:
 *  - 21件すべてが存在し post_status='draft' であることを確認(publish が混じれば中断)
 *  - STEP1 未実施の検知: カテゴリ1の13件が、まだ 3427/3902 を親にしていたら中断
 *  - 1件ずつ削除し、毎回 get_post()===null を確認
 */
define('WP_USE_THEMES', false);
require '/var/www/html/wp-load.php';

$APPLY = (isset($argv[1]) && $argv[1] === 'apply');
$TARGETS = array(19,24,29,34, 18,23,28,33,124, 3427,3902,4408,4409,3632, 1399,1403,3200, 3014,3016,3018,3020);
$EXPECT_TYPE = array(
  19=>'service',24=>'service',29=>'service',34=>'service',
  18=>'company',23=>'company',28=>'company',33=>'company',124=>'company',
  3427=>'page',3902=>'page',4408=>'page',4409=>'page',3632=>'page',
  1399=>'page',1403=>'page',3200=>'page',3014=>'page',3016=>'page',3018=>'page',3020=>'page',
);
$CAT1 = array(3509,3513,3514,3516,3519,3520,3531,3552,3568,3570,3612,4136,4138);

function fail($m){ fwrite(STDERR, "ABORT: $m\n"); exit(1); }
echo $APPLY ? "=== STEP2 21件削除 [APPLY] ===\n" : "=== STEP2 21件削除 [DRY-RUN] ===\n";

// STEP1 完了ガード: カテゴリ1が 3427/3902 を親にしたまま残っていないか
foreach ($CAT1 as $id) {
    $par = (int) get_post_field('post_parent', $id);
    if ( in_array($par, array(3427,3902), true) )
        fail("STEP1 未完了: 添付 {$id} がまだ親 {$par} のままです。先に 01_reattach を apply 実行してください");
}
echo "ガードOK: カテゴリ1の13件は 3427/3902 から外れています\n";

// 事前確認
foreach ($TARGETS as $id) {
    $p = get_post($id);
    if ( ! $p ) { echo "  - $id: 既に存在しません(削除済み?)\n"; continue; }
    if ( $p->post_status !== 'draft' )
        fail("ID $id の状態が draft ではありません(現在 {$p->post_status})。安全のため中断");
    if ( isset($EXPECT_TYPE[$id]) && $p->post_type !== $EXPECT_TYPE[$id] )
        fail("ID $id の型 {$p->post_type} が想定 {$EXPECT_TYPE[$id]} と違います");
    echo "  確認 OK: $id  {$p->post_type}/{$p->post_status}  \"" . mb_substr($p->post_title,0,30) . "\"\n";
}

if ( ! $APPLY ) {
    echo "[DRY-RUN] 21件を完全削除(force_delete=true)します。リビジョンも自動削除。\n";
    echo "  実行するには apply を付けてください。\n";
    exit(0);
}

// 実行(1件ずつ)
$deleted = 0;
foreach ($TARGETS as $id) {
    if ( ! get_post($id) ) { echo "  skip(なし): $id\n"; continue; }
    $r = wp_delete_post($id, true); // true = ゴミ箱を経由せず完全削除
    if ( $r === false || $r === null ) fail("ID $id の削除に失敗しました");
    if ( get_post($id) !== null ) fail("ID $id 削除後もまだ存在します");
    echo "  OK 削除: $id\n";
    $deleted++;
}
$remain = 0; foreach ($TARGETS as $id) { if (get_post($id)) $remain++; }
echo "事後確認: 残存 {$remain} 件(期待 0)\n";
if ( $remain !== 0 ) fail("削除しきれていません");
echo "STEP2 完了: {$deleted}件を完全削除。\n";
