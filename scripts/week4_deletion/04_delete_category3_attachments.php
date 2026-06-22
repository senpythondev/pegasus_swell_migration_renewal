<?php
/**
 * STEP 4 / Week4 deletion
 * カテゴリ3(旧テーマ設定 dp_options にのみ参照が残る)の添付2件を削除する。
 *   1569 (kobu-agency)  /  2726 (27114857_m)
 * いずれも現行サイト(SWELL)では未使用。技術判断で削除する方針(橋本様了承済み)。
 *
 * 使い方(コンテナ内):
 *   php 04_delete_category3_attachments.php          ← ドライラン
 *   php 04_delete_category3_attachments.php apply     ← 実行
 *
 * 安全装置:
 *  - STEP3 と同じ強シグナル(wp-image/"id"/URL)で公開投稿の参照を直前再チェック
 *  - dp_options(旧テーマ設定)は変更しない。参照が残っていても旧テーマは停止中で表示に影響しないため、
 *    本スクリプトは添付の削除のみ行う(dp_options の整理は別途のクリーンアップで扱う)
 */
define('WP_USE_THEMES', false);
require '/var/www/html/wp-load.php';
global $wpdb;

$APPLY = (isset($argv[1]) && $argv[1] === 'apply');
$CAT3 = array(1569, 2726);

function fail($m){ fwrite(STDERR, "ABORT: $m\n"); exit(1); }
echo $APPLY ? "=== STEP4 カテゴリ3削除 [APPLY] ===\n" : "=== STEP4 カテゴリ3削除 [DRY-RUN] ===\n";

// 公開投稿を一度だけ読み込む
$PUB = $wpdb->get_results("SELECT ID, post_content FROM {$wpdb->posts}
    WHERE post_status='publish' AND post_type NOT IN ('attachment','revision')");

// 強いシグナル(語境界つき)で使用先IDを返す。LIKE の部分一致を避ける。
function strong_hits($id, $guid, $PUB) {
    $hits = array();
    $re_img = '/wp-image-' . $id . '\b/';
    $re_blk = '/"id"\s*:\s*' . $id . '\b/';
    foreach ($PUB as $p) {
        $c = $p->post_content;
        if ( preg_match($re_img,$c) || preg_match($re_blk,$c) || ($guid && strpos($c,$guid)!==false) )
            $hits[] = (int)$p->ID;
    }
    return $hits;
}

foreach ($CAT3 as $id) {
    $p = get_post($id);
    if ( ! $p ) { echo "  - $id: 既に存在しません(スキップ)\n"; continue; }
    if ( $p->post_type !== 'attachment' ) fail("ID $id は attachment ではありません");
    $hits = strong_hits((int)$id, $p->guid, $PUB);
    if ( $hits )
        fail("ID $id は公開投稿で参照されています(投稿ID: " . implode(',', $hits) . ")。削除を中止します");
    // 参考: dp_options(旧テーマ設定)に残る参照(削除はしない・表示影響なし)
    $stem = pathinfo(get_post_meta($id,'_wp_attached_file',true), PATHINFO_FILENAME);
    $indp = $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name='dp_options' AND option_value LIKE %s",
        '%' . $wpdb->esc_like($stem) . '%' ));
    echo "  確認 OK(公開未使用): $id  file=" . (get_attached_file($id) ?: '-') . "  dp_options参照=" . ((int)$indp? 'あり(旧テーマ・停止中)':'なし') . "\n";
}

if ( ! $APPLY ) {
    echo "[DRY-RUN] カテゴリ3の2件を物理ファイルごと削除します。apply で実行。\n";
    exit(0);
}

$deleted = 0;
foreach ($CAT3 as $id) {
    if ( ! get_post($id) ) { echo "  skip(なし): $id\n"; continue; }
    $r = wp_delete_attachment($id, true);
    if ( ! $r ) fail("ID $id の削除に失敗");
    if ( get_post($id) !== null ) fail("ID $id 削除後も存在します");
    echo "  OK 削除: $id\n";
    $deleted++;
}
echo "STEP4 完了: {$deleted}件のカテゴリ3添付を削除。\n";
echo "備考: dp_options(旧テーマ設定)の参照は残置。旧テーマは停止中のため表示に影響しません。\n";
