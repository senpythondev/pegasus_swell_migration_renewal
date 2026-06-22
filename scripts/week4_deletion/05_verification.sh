#!/usr/bin/env bash
# STEP 5 / Week4 deletion — 削除後の動作確認(読み取りのみ)
# 実行: bash scripts/week4_deletion/05_verification.sh
cd "$(dirname "$0")/../.."   # -> ~/Wordpress
PW=pegasus_pass_change_me
HOST='Host: 220.158.21.114:8080'
BASE='http://localhost:8080'
pass=0; fail=0
ok(){ echo "  [OK]  $1"; pass=$((pass+1)); }
ng(){ echo "  [NG]  $1"; fail=$((fail+1)); }

q(){ sg docker -c "docker compose exec -T db mysql -u pegasus -p$PW --default-character-set=utf8mb4 pegasus_wp -N -B -e \"$1\"" 2>/dev/null | grep -v Warning; }
code(){ curl -sS -o /dev/null -w '%{http_code}' -H "$HOST" "$BASE$1"; }
loc(){ curl -sS -o /dev/null -D - -H "$HOST" "$BASE$1" | awk 'tolower($1)=="location:"{print $2}' | tr -d '\r'; }

echo "===== A. データベース確認 ====="
n=$(q "SELECT COUNT(*) FROM wp04447aposts WHERE ID IN (19,24,29,34,18,23,28,33,124,3427,3902,4408,4409,3632,1399,1403,3200,3014,3016,3018,3020);")
[ "$n" = "0" ] && ok "削除対象21件: 残存0 (=$n)" || ng "削除対象21件が残存 (=$n)"

n=$(q "SELECT COUNT(*) FROM wp04447aposts WHERE ID IN (3509,3513,3514,3516,3519,3520,3531,3552,3568,3570,3612,4136,4138) AND post_type='attachment' AND post_parent=1067;")
[ "$n" = "13" ] && ok "カテゴリ1の13件: 親=1067 で保全 (=$n)" || ng "カテゴリ1の親=1067 が13件でない (=$n)"

n=$(q "SELECT COUNT(*) FROM wp04447aposts WHERE ID IN (103,104,1578,1579,1580,1581,1582,1584,1570,3518,1569,2726);")
[ "$n" = "0" ] && ok "カテゴリ2+3の12件: 削除済み 残存0 (=$n)" || ng "カテゴリ2+3が残存 (=$n)"

echo "===== B. 物理ファイル確認 (コンテナ内) ====="
chk_file(){ sg docker -c "docker compose exec -T -u www-data wordpress sh -c 'test -f /var/www/html/wp-content/uploads/$1 && echo Y || echo N'" 2>/dev/null | tr -d '\r'; }
# カテゴリ1=残っているべき(代表3件)
for f in "2023/12/1692480.jpg" "2024/07/d10fc8d1d111cf5e6cc02e618632f30e.mp4" "2024/07/69a5bae54ccaff2773282509ac6017db.mp4"; do
  [ "$(chk_file "$f")" = "Y" ] && ok "保全(存在): $f" || ng "消えている(あるべき): $f"
done
# カテゴリ2/3=消えているべき(代表3件)
for f in "2021/03/img_guide.jpg" "2023/02/kobu-agency-7okkFhxrxNw-unsplash-scaled.jpg" "2023/12/2165767.jpg"; do
  [ "$(chk_file "$f")" = "N" ] && ok "削除済み(不在): $f" || ng "まだ存在: $f"
done

echo "===== C. ライブ表示(HTTP) ====="
c=$(code "/homeworker/");        [ "$c" = "200" ] && ok "/homeworker/ = 200" || ng "/homeworker/ = $c"
c=$(code "/");                   [ "$c" = "200" ] && ok "トップ / = 200" || ng "トップ / = $c"
for p in /about/ /program/ /result/ /guide/ /access/; do
  c=$(code "$p"); [ "$c" = "200" ] && ok "$p = 200" || ng "$p = $c"
done
# 既存ブログ/事例(壊れていないか)
c=$(code "/user-decision1/");    [ "$c" = "200" ] && ok "/user-decision1/ = 200" || ng "/user-decision1/ = $c"
# 先のリダイレクトが生きているか
c=$(code "/user-dicision1/");    l=$(loc "/user-dicision1/")
{ [ "$c" = "301" ] && [ "$l" = "/user-decision1/" ]; } && ok "/user-dicision1/ = 301 -> $l" || ng "/user-dicision1/ = $c (loc=$l)"

echo "===== D. 補足(service/company は下書きのため公開URLなし) ====="
echo "  service4/company5 は元々 draft で公開URLを持たないため、404確認は対象外。"
echo "  → 上記Aの『削除対象21件 残存0』で削除完了を確認する。"

echo "=================================================="
echo "  PASS=$pass  FAIL=$fail"
[ "$fail" = "0" ] && echo "  ★ すべて合格" || echo "  × 失敗あり。ROLLBACK.md を参照"
echo "=================================================="
