#!/usr/bin/env bash
# STEP 0 / Week4 deletion — 月曜の作業直前に実行する事前バックアップ
#  (1) DB全体の新鮮なダンプ
#  (2) 削除予定の添付(カテゴリ2/3)の元ファイルを backup/ へ退避
# 実行: bash scripts/week4_deletion/00_backup.sh
set -euo pipefail
cd "$(dirname "$0")/../.."   # -> ~/Wordpress
PW=pegasus_pass_change_me     # ステージング用(docker-compose.yml の MYSQL_PASSWORD)
STAMP=$(date +%Y%m%d_%H%M%S)
OUT=backup
mkdir -p "$OUT/week4_deleted_files"

echo "== (1) DB全体ダンプ =="
sg docker -c "docker compose exec -T db mysqldump -u pegasus -p$PW --default-character-set=utf8mb4 --single-transaction pegasus_wp" 2>/dev/null \
  | gzip > "$OUT/full_db_pre_delete_RUN_${STAMP}.sql.gz"
ls -la "$OUT/full_db_pre_delete_RUN_${STAMP}.sql.gz"

echo "== (2) 削除予定ファイル(カテゴリ2/3=12件の元画像)を退避 =="
UP=wp_data/wp-content/uploads
FILES=(
  "2021/03/img_guide.jpg"                                  # 103  cat2
  "2021/03/img_voice.jpg"                                  # 104  cat2
  "2023/02/brooke-cagle-g1Kr4Ozfoac-unsplash.jpg"          # 1578 cat2
  "2023/02/tim-gouw-bwki71ap-y8-unsplash.jpg"              # 1579 cat2
  "2023/02/adnan-elezovic-QFpXllrxdw4-unsplash-1-scaled.jpg" # 1580 cat2
  "2023/02/redd-f-PTRzqc_h1r4-unsplash.jpg"                # 1581 cat2
  "2023/02/campaign-creators-gMsnXqILjp4-unsplash.jpg"     # 1582 cat2
  "2023/02/kenny-eliason-1-aA2Fadydc-unsplash.jpg"         # 1584 cat2
  "2023/02/adnan-elezovic-QFpXllrxdw4-unsplash-scaled.jpg" # 1570 cat2
  "2023/12/2165767.jpg"                                    # 3518 cat2
  "2023/02/kobu-agency-7okkFhxrxNw-unsplash-scaled.jpg"    # 1569 cat3
  "2023/07/27114857_m.jpg"                                 # 2726 cat3
)
copied=0; missing=0
for f in "${FILES[@]}"; do
  if [ -f "$UP/$f" ]; then
    mkdir -p "$OUT/week4_deleted_files/$(dirname "$f")"
    cp -p "$UP/$f" "$OUT/week4_deleted_files/$f"
    echo "  copied: $f"; copied=$((copied+1))
  else
    echo "  !! MISSING: $f"; missing=$((missing+1))
  fi
done
echo "退避完了: ${copied}件コピー / ${missing}件欠落"
echo
echo "※ サムネイル(リサイズ版)は退避していません。復元時は Regenerate Thumbnails で再生成できます。"
echo "※ 元画像は本番(pegasus-job.com)にも存在します。最終的な復元元として利用可能です。"
[ "$missing" -eq 0 ] || { echo "欠落があります。中身を確認してください。"; exit 1; }
echo "STEP0 完了。"
