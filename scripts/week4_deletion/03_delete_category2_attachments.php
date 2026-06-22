<?php
/**
 * STEP 3 / Week4 deletion
 * カテゴリ2(どこにも未使用)の添付10件を、物理ファイルごと削除する。
 *
 * 使い方(コンテナ内):
 *   php 03_delete_category2_attachments.php          ← ドライラン
 *   php 03_delete_category2_attachments.php apply     ← 実行
 *
 * 安全装置(直前再チェック):
 *  - 各添付IDが、公開投稿の本文で「強いシグナル」で参照されていないか直前に再確認
 *    (wp-image-<ID> / "id":<ID> / 完全URL)。1件でも見つかれば、その添付は削除せず中断。
 *    ※ ファイル名の部分一致は使わない(3518 の 2165767 誤検出を避けるため)
 */
define('WP_USE_THEMES', false);
require '/var/www/html/wp-load.php';
global $wpdb;

$APPLY = (isset($argv[1]) && $argv[1] === 'apply');
$CAT2 = array(103,104,1578,1579,1580,1581,1582,1584,1570,3518);

function fail($m){ fwrite(STDERR, "ABORT: $m\n"); exit(1); }
echo $APPLY ? "=== STEP3 カテゴリ2削除 [APPLY] ===\n" : "=== STEP3 カテゴリ2削除 [DRY-RUN] ===\n";

// 公開投稿を一度だけ読み込む(削除対象の下書き・リビジョン・添付は除外)
$PUB = $wpdb->get_results("SELECT ID, post_content FROM {$wpdb->posts}
    WHERE post_status='publish' AND post_type NOT IN ('attachment','revision')");

/**
 * 添付IDの「強いシグナル」での参照を、語境界つきで厳密に判定して使用先ID配列を返す。
 * LIKE の部分一致(例: "id":103 が "id":1039 に誤一致)を避けるため preg_match(\b) を使う。
 */
function strong_hits($id, $guid, $PUB) {
    $hits = array();
    $re_img = '/wp-image-' . $id . '\b/';
    $re_blk = '/"id"\s*:\s*' . $id . '\b/';
    foreach ($PUB as $p) {
        $c = $p->post_content;
        if ( preg_match($re_img, $c) || preg_match($re_blk, $c)
             || ($guid && strpos($c, $guid) !== false) ) {
            $hits[] = (int) $p->ID;
        }
    }
    return $hits;
}

// 直前再チェック(強いシグナルのみ・語境界つき)
foreach ($CAT2 as $id) {
    $p = get_post($id);
    if ( ! $p ) { echo "  - $id: 既に存在しません(スキップ)\n"; continue; }
    if ( $p->post_type !== 'attachment' ) fail("ID $id は attachment ではありません");
    $hits = strong_hits((int)$id, $p->guid, $PUB);
    if ( $hits )
        fail("ID $id は公開投稿で参照されています(投稿ID: " . implode(',', $hits) . ")。削除を中止します");
    $file = get_attached_file($id);
    echo "  確認 OK(未使用): $id  file=" . ($file ?: '-') . "\n";
}

if ( ! $APPLY ) {
    echo "[DRY-RUN] カテゴリ2の10件を物理ファイルごと削除します。apply で実行。\n";
    exit(0);
}

// 実行
$deleted = 0;
foreach ($CAT2 as $id) {
    if ( ! get_post($id) ) { echo "  skip(なし): $id\n"; continue; }
    $r = wp_delete_attachment($id, true); // true = 物理ファイルも削除
    if ( ! $r ) fail("ID $id の削除に失敗");
    if ( get_post($id) !== null ) fail("ID $id 削除後も存在します");
    echo "  OK 削除: $id\n";
    $deleted++;
}
echo "STEP3 完了: {$deleted}件のカテゴリ2添付を削除。\n";
