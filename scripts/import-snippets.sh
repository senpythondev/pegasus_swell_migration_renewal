#!/usr/bin/env bash
#
# scripts/import-snippets.sh — scripts/code-snippets/php/*.php → DB(Code Snippets)
#
# 目的: Gitの原本(pega-css-* 14本)を DB へ投入する(name で upsert)。
#       本番移行時の投入、またはステージング復元に使用(チェックリスト フェーズ11の手順)。
# 対象: scripts/code-snippets/php/pega-css-*.php のみ。
#       機能スニペット(id5-8 等)には一切触れない（PHP側でも name が 'pega-css-' 始まり以外は無視）。
# 安全: 対象表示 / 確認プロンプト / --dry-run。
#
# 使い方:
#   bash scripts/import-snippets.sh            # 対象表示→確認→投入
#   bash scripts/import-snippets.sh --yes      # 確認をスキップ
#   bash scripts/import-snippets.sh --dry-run  # 対象表示のみ(DBに投入しない)
#
# 前提: リポジトリルートで実行 / Docker(pegasus_wp)稼働 / `sg docker` でdocker利用可。
#       投入先は scope=global・active=1・priority=10。読込順は各スニペット内の hook優先度で決まる。
#
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
PHP_DIR="$ROOT/scripts/code-snippets/php"
TMP="$(mktemp -d)"; trap 'rm -rf "$TMP"' EXIT

DRY=0; YES=0
for a in "$@"; do
	case "$a" in
		--dry-run) DRY=1 ;;
		--yes|-y)  YES=1 ;;
		*) echo "不明なオプション: $a" >&2; exit 2 ;;
	esac
done

shopt -s nullglob
files=("$PHP_DIR"/pega-css-*.php)
shopt -u nullglob
if [ "${#files[@]}" -eq 0 ]; then
	echo "$PHP_DIR に pega-css-*.php がありません。中止。" >&2
	exit 1
fi

echo "▼ $PHP_DIR/pega-css-*.php → DB(Code Snippets)  ※機能スニペット id5-8 は対象外"
echo "対象:"
# name<TAB>base64(<?php 除いた本体) を payload に組み立て
: > "$TMP/payload.tsv"
for f in "${files[@]}"; do
	name="$(basename "$f" .php)"
	echo "  - $name"
	# 先頭の <?php 行を除いた本体を base64(1行) 化
	b64="$(tail -n +2 "$f" | base64 -w0)"
	printf '%s\t%s\n' "$name" "$b64" >> "$TMP/payload.tsv"
done
echo "対象: ${#files[@]} 本"

if [ "$DRY" = "1" ]; then echo "(--dry-run のため DB には投入しません)"; exit 0; fi
if [ "$YES" != "1" ]; then
	printf "上記を DB に投入(name で upsert・scope=global・有効化)します。続行しますか? [y/N]: "
	read -r ans < /dev/tty || ans=""
	case "$ans" in y|Y|yes|YES) ;; *) echo "中止しました。"; exit 0 ;; esac
fi

# payload をコンテナへ配置（PHPスクリプトは heredoc の stdin で渡すため、payload はファイル経由）
cat "$TMP/payload.tsv" | sg docker -c "docker compose exec -T wordpress bash -c 'cat > /tmp/pega_import_payload.tsv'"

sg docker -c "docker compose exec -T wordpress php" <<'PHP'
<?php
require '/var/www/html/wp-load.php';
global $wpdb; $t = $wpdb->prefix . 'snippets';
$gmt = current_time( 'mysql', true ); // Code Snippets は modified を GMT で保持
$in  = file_get_contents( '/tmp/pega_import_payload.tsv' );
$n = 0; $skip = 0;
foreach ( explode( "\n", $in ) as $line ) {
	if ( trim( $line ) === '' ) { continue; }
	$parts = explode( "\t", $line, 2 );
	if ( count( $parts ) < 2 ) { continue; }
	list( $name, $b64 ) = $parts;
	// 安全: pega-css-* 以外は絶対に触れない
	if ( strpos( $name, 'pega-css-' ) !== 0 ) { $skip++; continue; }
	$code = base64_decode( $b64 );
	$ex = $wpdb->get_row( $wpdb->prepare( "SELECT id FROM $t WHERE name = %s", $name ) );
	if ( $ex ) {
		$wpdb->update( $t, array( 'code' => $code, 'scope' => 'global', 'active' => 1, 'modified' => $gmt ), array( 'id' => $ex->id ) );
	} else {
		$wpdb->insert( $t, array( 'name' => $name, 'description' => '', 'code' => $code, 'tags' => '', 'scope' => 'global', 'condition_id' => 0, 'priority' => 10, 'active' => 1, 'modified' => $gmt, 'revision' => 1 ) );
	}
	$n++;
}
echo "upsert: {$n} 本" . ( $skip ? " / スキップ(pega-css-*以外): {$skip}" : '' ) . "\n";
PHP

sg docker -c "docker compose exec -T wordpress rm -f /tmp/pega_import_payload.tsv" 2>/dev/null || true
echo "✅ 投入完了。管理画面の表示反映にはページ再読込を。"
