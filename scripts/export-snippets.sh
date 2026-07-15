#!/usr/bin/env bash
#
# scripts/export-snippets.sh — DB(Code Snippets) → scripts/code-snippets/php/*.php
#
# 目的: 管理画面(ステージング)で編集した pega-css-* スニペットの内容を、
#       Gitの原本 scripts/code-snippets/php/*.php へ書き戻す。実行後 `git commit` する。
# 対象: name が 'pega-css-%' の14本のみ。
#       機能スニペット(CF7確認欄/テストSMTP/メールリンク/サイトマップ 等 id5-8)には一切触れない。
# 安全: 対象・変更有無を表示 / 確認プロンプト / --dry-run。
#
# 使い方:
#   bash scripts/export-snippets.sh            # 対象を表示→確認→書き出し
#   bash scripts/export-snippets.sh --yes      # 確認をスキップ
#   bash scripts/export-snippets.sh --dry-run  # 変更内容の表示のみ(書き込まない)
#
# 前提: リポジトリルートで実行 / Docker(pegasus_wp)稼働 / `sg docker` でdocker利用可。
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

mkdir -p "$PHP_DIR"
echo "▼ DB(pega-css-* スニペット) → $PHP_DIR/*.php  ※機能スニペット id5-8 は対象外"

# DBから name<TAB>base64(code) を取得（pega-css-* のみ）
DUMP="$(sg docker -c "docker compose exec -T wordpress php" <<'PHP'
<?php
require '/var/www/html/wp-load.php';
global $wpdb; $t = $wpdb->prefix . 'snippets';
foreach ( $wpdb->get_results( "SELECT name, code FROM $t WHERE name LIKE 'pega-css-%' ORDER BY name" ) as $r ) {
	echo $r->name . "\t" . base64_encode( $r->code ) . "\n";
}
PHP
)"

if [ -z "$DUMP" ]; then
	echo "DBに pega-css-* スニペットが見つかりません。中止。" >&2
	exit 1
fi

# 対象一覧＋変更有無を表示（DBの内容で php/*.php を生成し、現行ファイルと比較）
total=0; changed=0
PLAN=()
while IFS=$'\t' read -r name b64; do
	[ -z "$name" ] && continue
	total=$((total + 1))
	echo "$b64" | base64 -d > "$TMP/$name.body"
	{ printf '<?php\n'; cat "$TMP/$name.body"; } > "$TMP/$name.full"
	dst="$PHP_DIR/$name.php"
	if [ -f "$dst" ] && diff -q "$dst" "$TMP/$name.full" >/dev/null 2>&1; then
		echo "  = $name  (変更なし)"
	else
		echo "  * $name  (更新される)"
		changed=$((changed + 1))
	fi
	PLAN+=("$name")
done <<< "$DUMP"

echo "対象: $total 本 / 更新される: $changed 本"

if [ "$DRY" = "1" ]; then echo "(--dry-run のため書き込みません)"; exit 0; fi
if [ "$changed" = "0" ]; then echo "変更なし。書き込みスキップ（Git=DB 一致）。"; exit 0; fi

if [ "$YES" != "1" ]; then
	printf "上記を %s に書き出します。続行しますか? [y/N]: " "$PHP_DIR"
	read -r ans < /dev/tty || ans=""
	case "$ans" in y|Y|yes|YES) ;; *) echo "中止しました。"; exit 0 ;; esac
fi

for name in "${PLAN[@]}"; do
	cp "$TMP/$name.full" "$PHP_DIR/$name.php"
done
echo "✅ 書き出し完了（$changed 本）。次に: git add scripts/code-snippets/php && git commit"
