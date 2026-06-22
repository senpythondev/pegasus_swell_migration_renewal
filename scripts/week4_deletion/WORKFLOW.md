# Week 4 削除作業 手順書（2026年6月22日 月曜）

**対象:** ステージング環境（Docker / `pegasus_wp`）**のみ**。本番環境（pegasus-job.com）には一切触れません。
**ゴール:** 不要な下書き・テストページ21件と、未使用の添付12件を削除し、ライブ `/homeworker/` を含む公開ページを無傷で保つ。
**所要時間の目安:** **約30〜45分**（ドライラン確認・本実行・動作確認を含む）

---

## 0. 大原則・安全装置

- **PHPスクリプト（01〜04）は、引数なしだと「ドライラン（確認のみ・無変更）」**。実行は末尾に `apply` を付けたときだけ。
- 各スクリプトは**実行前チェック**を持ち、想定と違えば**その場で中断（abort）**します。
- **順序厳守:** 必ず `01 → 02 → 03 → 04` の順。特に **01（添付の付け替え）を 02（親削除）より先**に。
  - 02 には「01 未完了なら中断する」ガードが入っていますが、順序は必ず守ってください。
- 迷ったら止めて、`ROLLBACK.md` を参照。

### スクリプトの置き場所と実行場所
| スクリプト | 実行場所 | 種別 |
|---|---|---|
| `00_backup.sh` | **ホスト**（`~/Wordpress`） | bash |
| `01`〜`04` `.php` | **wordpressコンテナ内** | php（ドライラン既定） |
| `05_verification.sh` | **ホスト** | bash |

### コンテナでPHPを動かす共通パターン
```bash
# 例: 01 をコンテナに送り、まずドライラン → 問題なければ apply
sg docker -c "docker compose cp scripts/week4_deletion/01_reattach_to_homeworker.php wordpress:/tmp/01.php"
sg docker -c "docker compose exec -T -u www-data wordpress php /tmp/01.php"        # ドライラン
sg docker -c "docker compose exec -T -u www-data wordpress php /tmp/01.php apply"  # 本実行
```

---

## 1. 作業ステップ

### STEP 0 — 事前バックアップ（必須）
```bash
cd ~/Wordpress
bash scripts/week4_deletion/00_backup.sh
```
- DB全体ダンプ（`backup/full_db_pre_delete_RUN_<日時>.sql.gz`）と、削除予定の元画像12件（`backup/week4_deleted_files/`）を退避。
- 「欠落0」で完了することを確認。

### STEP 1 — カテゴリ1の添付13件を 1067 へ付け替え
```bash
sg docker -c "docker compose cp scripts/week4_deletion/01_reattach_to_homeworker.php wordpress:/tmp/01.php"
sg docker -c "docker compose exec -T -u www-data wordpress php /tmp/01.php"        # ドライラン
sg docker -c "docker compose exec -T -u www-data wordpress php /tmp/01.php apply"  # 本実行
```
- 期待: 13件すべて `parent -> 1067`。事後確認で「親=1067 は 13/13」。

### STEP 2 — 親投稿21件を完全削除
```bash
sg docker -c "docker compose cp scripts/week4_deletion/02_delete_21_posts.php wordpress:/tmp/02.php"
sg docker -c "docker compose exec -T -u www-data wordpress php /tmp/02.php"        # ドライラン
sg docker -c "docker compose exec -T -u www-data wordpress php /tmp/02.php apply"  # 本実行
```
- 期待: 21件すべて削除（残存0）。リビジョンも自動削除。
- ガード: 01 未完了なら中断します。

### STEP 3 — カテゴリ2の添付10件を削除（ファイルごと）
```bash
sg docker -c "docker compose cp scripts/week4_deletion/03_delete_category2_attachments.php wordpress:/tmp/03.php"
sg docker -c "docker compose exec -T -u www-data wordpress php /tmp/03.php"        # ドライラン
sg docker -c "docker compose exec -T -u www-data wordpress php /tmp/03.php apply"  # 本実行
```
- 直前再チェック: 公開投稿で強シグナル参照があれば中断。

### STEP 4 — カテゴリ3の添付2件を削除（ファイルごと）
```bash
sg docker -c "docker compose cp scripts/week4_deletion/04_delete_category3_attachments.php wordpress:/tmp/04.php"
sg docker -c "docker compose exec -T -u www-data wordpress php /tmp/04.php"        # ドライラン
sg docker -c "docker compose exec -T -u www-data wordpress php /tmp/04.php apply"  # 本実行
```
- 1569 / 2726 を削除。`dp_options`（旧テーマ設定・停止中）は変更しません。

### STEP 5 — 動作確認
```bash
cd ~/Wordpress
bash scripts/week4_deletion/05_verification.sh
```
- 末尾の `PASS / FAIL` を確認。`FAIL=0` なら全合格。

### 後片付け（任意）
```bash
sg docker -c "docker compose exec -T -u root wordpress rm -f /tmp/01.php /tmp/02.php /tmp/03.php /tmp/04.php"
```

---

## 2. 動作確認チェックリスト（05が自動判定）

- [ ] 削除対象21件の残存が **0**
- [ ] カテゴリ1の13件が **親=1067 で保全**
- [ ] カテゴリ2+3の12件が **削除済み（残存0）**
- [ ] カテゴリ1の物理ファイル（代表3件）が **残っている**
- [ ] カテゴリ2/3の物理ファイル（代表3件）が **消えている**
- [ ] `/homeworker/` = **200**（画像・動画が表示される）
- [ ] トップ `/`・`/about/`・`/program/`・`/result/`・`/guide/`・`/access/` = **200**
- [ ] `/user-decision1/` = 200、`/user-dicision1/` = 301 → `/user-decision1/`（既存リダイレクト維持）

> service4 / company5 は元々 **下書き（公開URLなし）** のため、404確認の対象外。DBの「残存0」で削除完了を確認します。

---

## 3. 完了報告に書く内容（テンプレ）

```
■ Week 4 削除作業 完了報告（2026/6/22 ステージング）
- STEP0 バックアップ: full_db_pre_delete_RUN_<日時>.sql.gz / week4_deleted_files 12件
- STEP1 付け替え: カテゴリ1 13件 → 親1067（保全）
- STEP2 削除: 親投稿21件（service4/company5/page12）完全削除、リビジョン同時削除
- STEP3 削除: カテゴリ2 添付10件（ファイルごと）
- STEP4 削除: カテゴリ3 添付2件（1569/2726）
- STEP5 動作確認: 05_verification.sh PASS=○ / FAIL=0
  - /homeworker/ 正常表示、他の公開ページ崩れなし、既存リダイレクト維持
- 本番環境: 変更なし
```

---

## 4. 困ったときは
- いずれかのステップが `ABORT` で止まった → メッセージを確認し、原因を解消（多くは順序違い・状態違い）。不明なら**そこで停止**して相談。
- 表示崩れ等が出た → `ROLLBACK.md` の該当ステップ手順で戻す。
