<?php
/**
 * CPT再構築: Custom Post Type UI のオプション(cptui_post_types / cptui_taxonomies)を
 * 本番に合わせて構築する。CPTUI が init で登録 → rewrite 再生成で /news/ 等が200になる。
 *
 * 本番準拠の要点:
 *  - works の rewrite slug は "column"(本番 /column/。/works/ ではない)
 *  - reports は taxonomy reports_cat を伴う(本番 types=[reports])
 *  - 投稿データには一切触れない(オプションと rewrite_rules のみ)
 *
 * 引数 apply で実行。無印はドライラン(出力のみ・無変更)。
 */
define('WP_USE_THEMES', false);
require '/var/www/html/wp-load.php';
$APPLY = (isset($argv[1]) && $argv[1]==='apply');

function pt($name,$label,$rewrite_slug,$taxes){
  return array(
    'name'=>$name,'label'=>$label,'singular_label'=>$label,'description'=>'',
    'public'=>'true','publicly_queryable'=>'true','show_ui'=>'true','show_in_nav_menus'=>'true',
    'delete_with_user'=>'false','show_in_rest'=>'true','rest_base'=>$name,
    'rest_controller_class'=>'WP_REST_Posts_Controller','rest_namespace'=>'wp/v2',
    'has_archive'=>'true','has_archive_string'=>'','exclude_from_search'=>'false','capability_type'=>'post',
    'hierarchical'=>'false','can_export'=>'true','rewrite'=>'true','rewrite_slug'=>$rewrite_slug,
    'rewrite_withfront'=>'true','query_var'=>'true','query_var_slug'=>'','menu_position'=>'',
    'show_in_menu'=>'true','show_in_menu_string'=>'','menu_icon'=>'','register_meta_box_cb'=>'',
    'supports'=>array('title','editor','thumbnail','excerpt','revisions','author'),
    'taxonomies'=>$taxes,'labels'=>array(),'custom_supports'=>'','enter_title_here'=>'',
  );
}
function tx($name,$label,$rewrite_slug,$objs,$hier){
  return array(
    'name'=>$name,'label'=>$label,'singular_label'=>$label,'description'=>'',
    'public'=>'true','publicly_queryable'=>'true','hierarchical'=>$hier,'show_ui'=>'true',
    'show_in_menu'=>'true','show_in_nav_menus'=>'true','query_var'=>'true','query_var_slug'=>'',
    'rewrite'=>'true','rewrite_slug'=>$rewrite_slug,'rewrite_withfront'=>'true','rewrite_hierarchical'=>'false',
    'show_admin_column'=>'true','show_in_rest'=>'true','show_tagcloud'=>'false','sort'=>'false','show_in_quick_edit'=>'',
    'rest_base'=>$name,'rest_controller_class'=>'WP_REST_Terms_Controller','rest_namespace'=>'wp/v2',
    'labels'=>array(),'meta_box_cb'=>'','default_term'=>'',
    'object_types'=>$objs,
  );
}

$post_types = array(
  'news'    => pt('news','お知らせ','news',array('news_category')),
  'reports' => pt('reports','職場実習レポート','reports',array('reports_cat')),
  'works'   => pt('works','代表コラム','column',array()),   // ★ rewrite slug = column(本番準拠)
);
$taxonomies = array(
  'news_category' => tx('news_category','お知らせカテゴリー','news_category',array('news'),'true'),
  'reports_cat'   => tx('reports_cat','職場実習レポートカテゴリー','reports_cat',array('reports'),'true'),
);

echo $APPLY ? "=== CPT構築 [APPLY] ===\n" : "=== CPT構築 [DRY-RUN] ===\n";
echo "post_types: " . implode(', ', array_keys($post_types)) . " (works rewrite=column)\n";
echo "taxonomies: " . implode(', ', array_keys($taxonomies)) . "\n";

if ($APPLY) {
  update_option('cptui_post_types', $post_types);
  update_option('cptui_taxonomies', $taxonomies);
  update_option('rewrite_rules', '');  // 次のフロント要求で CPT を含めて再生成
  echo "OK: cptui_post_types / cptui_taxonomies を保存、rewrite_rules をクリア。\n";
  echo "→ フロントへ1回アクセスして rewrite を再生成してください。\n";
} else {
  echo "[DRY-RUN] 変更なし。apply で書き込み。\n";
}

// JSON エクスポート(ホスト側で保存するため標準出力にマーカー付きで出す)
echo "<<<PTJSON\n" . json_encode($post_types, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) . "\nPTJSON\n";
echo "<<<TXJSON\n" . json_encode($taxonomies, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) . "\nTXJSON\n";
