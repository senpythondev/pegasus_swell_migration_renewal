# 転送先スラッグ変更・カテゴリ再紐付け 実装レポート（Phase 2）

**実施日:** 2026年6月18日
**対象環境:** ステージング環境のみ（Docker / `pegasus_wp`）。本番環境は一切変更なし。
**前提作業:** 別レポート（[REDIRECT_IMPLEMENTATION_REPORT.md](REDIRECT_IMPLEMENTATION_REPORT.md)）で実装済みの301リダイレクト3件の「転送先」を、実体のある正しいURLにするための対の後工程。

---

## 0. 結論（サマリー）

| タスク | 内容 | 結果 |
|---|---|:---:|
| タスク1 | 投稿432 スラッグ `user-dicision1` → `user-decision1` | ✅ 完了・200確認 |
| タスク2 | カテゴリ term_id 8 スラッグ `user-dicision` → `user-decision` | ✅ 完了・200確認 |
| タスク3 | カテゴリ再紐付け（配下10件を user-decision へ） | ✅ 完了（同一termのリネームで全10件を保持） |
| タスク4 | news_category `infomation` → `information` | ⏸ **保留**（下記の理由により、現環境では変更を見送り） |

タスク1〜3は「旧URL → 301 → 新URL → 200」の完全な流れを確認しました。
タスク4は、**現在 news_category タクソノミー自体がフロントに登録されていない**ため（SWELL切替後のCPT再構築が未実施）、スラッグを変えても「200表示」を達成できない状態でした。データ破損を避けるため変更を保留し、本レポートで状況を報告します。

---

## 1. 実施した変更（タスク1〜3）

WordPress標準の関数で実施し、term count・キャッシュの整合をWordPressに委ねました。

| タスク | 使用関数 | 対象 | 変更前 | 変更後 |
|---|---|---|---|---|
| 1 | `wp_update_post()` | 投稿 ID 432 | post_name = `user-dicision1` | `user-decision1` |
| 2 | `wp_update_term()` | term_id 8（category） | slug = `user-dicision` | `user-decision` |
| 4(保留) | ― | term_id 6（news_category） | slug = `infomation` | （変更せず） |

### タスク3（再紐付け）について
カテゴリは **既存の term_id 8 をリネーム** したため、その配下にあった**10件全て**（投稿432 + user-decision2〜10）が、そのまま新スラッグ `user-decision` の配下になります。新しいカテゴリを作って移動する方式ではないため、紐付け（term_relationships）は一切壊れません。
- 変更後の確認: `user-decision` 配下の公開投稿 = **10件**（ID 432,435,437,438,439,440,449,450,451,452）。投稿432のカテゴリ = `user-decision`。

### WordPress自動リダイレクト（_wp_old_slug）との競合確認
`wp_update_post()` でスラッグを変えると、WordPressは旧スラッグを `_wp_old_slug`（= `user-dicision1`）として保存し、コア機能（`wp_old_slug_redirect`）でも旧URL→新URLへ301する場合があります。今回の確認では:
- 旧URL `/user-dicision1/` の応答に **`X-Redirect-By: redirection`** ヘッダーが付与されており、**Redirection プラグインが優先的に処理**していることを確認しました（コアの `wp_old_slug_redirect` は `template_redirect` で動くのに対し、Redirection は `init` で先に動くため）。
- なお、両者が動いたとしても転送先は同じ `/user-decision1/` のため、結果に矛盾は生じません（無限ループ等もなし）。

---

## 2. 動作確認結果（タスク1〜3）

ステージングのnginx（8080）に、本番ホスト名を `Host` ヘッダーで指定して確認。

### ステータス推移（旧 → 301 → 新 → 200）

| 入口URL | ① 旧URLの応答 | ② Location | ③ 新URLの応答 | チェーン追跡（curl -L） |
|---|:---:|---|:---:|---|
| `/user-dicision1/` | 301（X-Redirect-By: redirection） | `/user-decision1/` | **200 OK** | 最終 200・リダイレクト1回 |
| `/category/user-dicision/` | 301（X-Redirect-By: redirection） | `/category/user-decision/` | **200 OK** | 最終 200・リダイレクト1回 |

```text
# 投稿
/user-dicision1/        → 301 → /user-decision1/        → 200   （final_code=200, num_redirects=1）
# カテゴリ
/category/user-dicision/ → 301 → /category/user-decision/ → 200   （final_code=200, num_redirects=1）
```

### 既存URLへの影響なし（回帰確認）
- `/user-decision2/` → **200**
- `/user-decision5/` → **200**
- `/user-decision10/` → **200**

カテゴリのリネームによる既存記事への悪影響はありません（10件すべて表示可能・カテゴリ件数も10件のまま）。

---

## 3. タスク4の保留について（重要・要ご判断）

### 何が起きたか
`wp_update_term(6, 'news_category', …)` が「不正なタクソノミーです」エラーを返しました。調査の結果、**現在のステージングでは `news_category`（および `news` / `reports` / `works` 等の独自投稿タイプ・タクソノミー）がフロント側に登録されていない**ことが分かりました。

### 根本原因
これらの独自投稿タイプ・タクソノミーは、**旧テーマ（TCD NANO 系）が登録していた**ものです。現在ステージングは **SWELL（swell_child）に切り替え済み**のため、旧テーマによる登録が失われ、まだ Custom Post Type UI での再構築（提案書 Phase 2 の作業項目）が済んでいません。

裏付け（いずれも既存・リダイレクト無しのURL）:
- `/news_category/event/`（タイポと無関係の既存カテゴリ）→ **404**
- `/news/program202404/`（既存のお知らせ記事）→ **404**
- CPTUI 設定は `cptui_new_install`（初期状態）のみ＝CPT未構築

### つまり
news_category のスラッグを `information` に変えても、**タクソノミー自体が未登録のため `/news_category/information/` は 404 のまま**で、タスク4の合格条件「200で正常表示・配下32件表示」を満たせません。これはタイポの問題ではなく、**CPT再構築がまだ実施されていない**という、移行途中の環境状態によるものです。

### 対応（データ破損を避けるため変更を保留）
- term_id 6 のスラッグは **`infomation` のまま据え置き**ました（DBは変更前の状態を維持。配下32件の紐付けも維持）。
- Redirection ルール #3（`/news_category/infomation/` → `/news_category/information/`）は登録済みで、301自体は動作します（転送先が404になるのは、上記のとおり news_category 全体が現在404のため）。

### ご提案
news_category のスラッグ修正（`infomation` → `information`）は、**Custom Post Type UI で news 系のCPT・タクソノミーを再構築する作業と同時に実施する**のが安全で確実です。その際に、
1. CPTUI で `news` / `news_category` を登録（rewrite slug を含む）、
2. term_id 6 のスラッグを `information` に修正、
3. `/news_category/information/` の200表示と配下32件をまとめて確認、
という順で行えば、転送先まで一気に整います。ご希望があれば、この一連の作業を続けて実施します。

---

## 4. バックアップ / ロールバック手順

### 取得済みバックアップ（`backup/`・gitignore対象）
- `backup/slug_change_pre_20260618.sql.gz` … 変更前の `wp04447aposts` / `wp04447aterms` / `wp04447aterm_taxonomy` / `wp04447aterm_relationships`
- `backup/redirection_pre_20260618.sql` … リダイレクト実装前の Redirection テーブル

### ロールバック（タスク1〜3を戻す場合）
```bash
PW=<MYSQL_PASSWORD>
# 投稿スラッグを戻す
sg docker -c "docker compose exec -T db mysql -u pegasus -p$PW pegasus_wp -e \
  \"UPDATE wp04447aposts SET post_name='user-dicision1' WHERE ID=432;\""
# カテゴリスラッグを戻す
sg docker -c "docker compose exec -T db mysql -u pegasus -p$PW pegasus_wp -e \
  \"UPDATE wp04447aterms SET slug='user-dicision' WHERE term_id=8;\""
# _wp_old_slug メタを掃除（任意）
sg docker -c "docker compose exec -T db mysql -u pegasus -p$PW pegasus_wp -e \
  \"DELETE FROM wp04447apostmeta WHERE post_id=432 AND meta_key='_wp_old_slug' AND meta_value='user-dicision1';\""

# もしくはバックアップから4テーブルを全面復元
zcat backup/slug_change_pre_20260618.sql.gz | \
  sg docker -c "docker compose exec -T db mysql -u pegasus -p$PW pegasus_wp"
```
（復元後はキャッシュ整合のため `wp-admin` で対象を開く、または object cache をクリア）

---

## 5. まとめ

| 項目 | 結果 |
|---|---|
| 投稿432 スラッグ修正 | ✅ user-decision1（旧URL→301→新URL→200） |
| カテゴリ term8 スラッグ修正＋再紐付け | ✅ user-decision（配下10件保持・200） |
| Redirection 優先処理 | ✅ X-Redirect-By: redirection を確認（コアと競合なし） |
| 既存URLへの影響 | なし（user-decision2/5/10 ほか 200） |
| news_category スラッグ修正（タスク4） | ⏸ 保留（CPT再構築待ち。データは未変更・維持） |
| 本番環境 | 変更なし |
| バックアップ | 取得済み（4テーブル＋Redirection） |
