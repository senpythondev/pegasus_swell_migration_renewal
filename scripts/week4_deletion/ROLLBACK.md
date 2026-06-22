# Week 4 削除作業 ロールバック手順書

問題が起きたら、まず**そのステップで停止**し、本書で戻します。対象は**ステージングのみ**。

---

## 0. バックアップの場所

| ファイル | 内容 | 取得元 |
|---|---|---|
| `backup/full_db_pre_delete_RUN_<日時>.sql.gz` | **当日の作業直前**のDB全体（最優先の戻し先） | `00_backup.sh` |
| `backup/week4_deleted_files/…` | 削除予定の元画像12件（カテゴリ2/3） | `00_backup.sh` |
| `backup/full_db_pre_delete_20260619.sql.gz` | 6/19時点のDB全体（予備） | 事前確認タスク |
| `backup/delete_targets_21_20260619.sql` | 21件＋子（添付/リビジョン）の行データ（部分復元用） | 事前確認タスク |

> 元画像は**本番（pegasus-job.com）にも存在**します。最終的な復元元として利用可能です。サムネイル（リサイズ版）は **Regenerate Thumbnails プラグイン**で再生成できます。

接続パスワード（ステージング）: `pegasus_pass_change_me`（`docker-compose.yml` の `MYSQL_PASSWORD`）。

---

## 1. もっとも確実な戻し方：DB全体リストア

どのステップで問題が起きても、**作業直前の状態**へ一括で戻せます（ステージングなので全体復元が安全）。

```bash
cd ~/Wordpress
PW=pegasus_pass_change_me
# 当日 00_backup.sh が作った最新ダンプを使う（ファイル名は実際のものに合わせる）
DUMP=backup/full_db_pre_delete_RUN_<日時>.sql.gz
zcat "$DUMP" | sg docker -c "docker compose exec -T db mysql -u pegasus -p$PW pegasus_wp"
```
- 削除した投稿・リビジョン・添付の**DB行**はこれで全て戻ります。
- **物理ファイル**（カテゴリ2/3で消した画像）は別途、下記2-3で戻します。

復元後はキャッシュ整合のため、念のため:
```bash
sg docker -c "docker compose restart wordpress"
```

---

## 2. ステップ別の戻し方

### STEP 1（付け替え）を戻す ― ※02実行前のみ有効
カテゴリ1の親を元（3427/3902）へ戻す:
```bash
PW=pegasus_pass_change_me
sg docker -c "docker compose exec -T db mysql -u pegasus -p$PW pegasus_wp -e \"
UPDATE wp04447aposts SET post_parent=3427 WHERE ID IN (3509,3513,3514,3516,3519,3520,3531,3552,3568,3570,3612);
UPDATE wp04447aposts SET post_parent=3902 WHERE ID IN (4136,4138);\""
```
> 02 を実行して 3427/3902 を削除済みの場合、親は存在しません。その場合は付け替えを**戻す必要はありません**（1067 のままで正常表示されます）。

### STEP 2（21件削除）を戻す
- 全体リストア（§1）が最も確実。
- 一部だけ戻したい場合は、`delete_targets_21_20260619.sql` から該当 `INSERT` を流し込む:
```bash
PW=pegasus_pass_change_me
# 例: ファイル全体を流す（21件＋子の行が入っています）
sg docker -c "docker compose exec -T db mysql -u pegasus -p$PW pegasus_wp" < backup/delete_targets_21_20260619.sql
```
> 注: 6/19以降に内容変更があった場合は、当日ダンプ（§1）を優先してください。

### STEP 3 / STEP 4（添付削除）を戻す
**(a) DB行を戻す**（§1の全体リストア、または `delete_targets_21_…sql` 内の添付行）。
**(b) 物理ファイルを戻す**:
```bash
cd ~/Wordpress
# 退避した元画像を uploads へ書き戻す（コンテナ内へコピー）
for f in $(cd backup/week4_deleted_files && find . -type f | sed 's#^\./##'); do
  sg docker -c "docker compose cp backup/week4_deleted_files/$f wordpress:/var/www/html/wp-content/uploads/$f"
done
# 所有者を www-data に
sg docker -c "docker compose exec -T -u root wordpress chown -R www-data:www-data /var/www/html/wp-content/uploads"
```
**(c) サムネイル再生成**: 管理画面 → ツール → Regenerate Thumbnails、または対象メディアを再生成。

---

## 3. 復元後の確認
```bash
bash scripts/week4_deletion/05_verification.sh
```
- ただし「削除済みであること」を確認する項目は、復元後は逆の結果（存在/残存）になります。**主目的の `/homeworker/` ほか公開ページが 200 で正常表示されること**を確認してください。
- 個別には:
  - `/homeworker/` = 200、画像・動画が表示される
  - 戻したい投稿が管理画面に存在する

---

## 4. 判断の目安
| 症状 | 対応 |
|---|---|
| あるステップが `ABORT` で止まった（変更前に停止） | 原因（順序/状態違い）を直す。データは無傷なので戻し不要 |
| `/homeworker/` の画像/動画が出ない | STEP1の付け替え漏れ → §2 STEP1、またはファイル復元 §2(b) |
| 公開ページが広範囲に崩れた | 迷わず §1 全体リストア |
| 添付を消しすぎた | §2 STEP3/4 (a)(b)(c) |
