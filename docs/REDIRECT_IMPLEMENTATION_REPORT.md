# URLリダイレクト実装レポート（Phase 2）

**実装日:** 2026年6月18日
**対象環境:** ステージング環境のみ（Docker / `pegasus_wp` コンテナ群）
**本番環境:** 一切変更なし（さくらサーバー上の本番には接続も変更もしていません）
**該当工程:** 提案書 Phase 2（URL資産維持・301リダイレクト設定）
**使用プラグイン:** Redirection 5.8.0（有効・稼働中）

---

## 1. 実装した3件のリダイレクト

「URL検証・リダイレクト計画表（完成版）」記載のタイポ修正3件を実装しました。

| # | 旧URL（タイポあり） | 新URL（正） | HTTPコード | 状態 | グループ |
|---|---|---|:---:|:---:|---|
| 1 | `/user-dicision1/` | `/user-decision1/` | 301 | enabled | pegasus-renewal |
| 2 | `/category/user-dicision/` | `/category/user-decision/` | 301 | enabled | pegasus-renewal |
| 3 | `/news_category/infomation/` | `/news_category/information/` | 301 | enabled | pegasus-renewal |

- 専用グループ **`pegasus-renewal`**（id=3）を新規作成し、既存グループ（「転送ルール」「編集済みの投稿」）と分離しました。
- データベース上の登録（`wp04447aredirection_items`）:

| id | url | match_url | action_code | status | action_data（転送先） |
|---|---|---|:---:|:---:|---|
| 1 | /user-dicision1/ | /user-dicision1 | 301 | enabled | /user-decision1/ |
| 2 | /category/user-dicision/ | /category/user-dicision | 301 | enabled | /category/user-decision/ |
| 3 | /news_category/infomation/ | /news_category/infomation | 301 | enabled | /news_category/information/ |

---

## 2. 実装方法

**Redirection プラグイン自身の PHP API（`Red_Item::create()`）を用いて登録しました。**

検討した3案のうち、この方法を選んだ理由は次の通りです。

| 方法 | 採否 | 理由 |
|---|:---:|---|
| (a) DBへ直接 INSERT | △ | 可能だが、Redirection が内部で持つ `match_url` の正規化ルールを手動で再現する必要があり、取りこぼしのリスクがある |
| (b) WP-CLI（`wp redirection`） | × | コンテナに WP-CLI が未導入。かつ Redirection は WP-CLI コマンドを標準提供していない |
| (c) 管理画面からのCSVインポート | × | 画面操作が必要で、今回の自動実行（ヘッドレス）では実施できない |
| **採用：Redirection の PHP API** | ◎ | プラグイン自身のモデルに登録を任せるため、`match_url` 等の正規化・整合が確実。`Red_Item::create()` で301・グループ・転送先をそのまま設定 |

実行は、一時スクリプトをコンテナへ送り、WordPress を読み込んだ上で `Red_Item::create()` を3回呼び出す形で行いました（スクリプトは実行後に削除済み）。

> 補足：本スタックは **nginx + php-fpm** 構成のため、Apache 用の `.htaccess` は読み込まれません。したがって `.htaccess` での実装は本環境では機能せず、PHPレイヤーで動く Redirection が適切です。

### 事前バックアップ
実装前に Redirection の全テーブルをバックアップしました（実装前は転送ルール0件の状態）。
- `backup/redirection_pre_20260618.sql`（`wp04447aredirection_items` / `_groups` / `_404` / `_logs`）

---

## 3. 動作確認

ステージングのnginx（ポート8080）に対し、本番ホスト名（home URL）を `Host` ヘッダーに指定して、WordPress の正規化リダイレクトと切り分けた上で確認しました。

### 確認コマンド
```bash
curl -sS -o /dev/null -D - -H 'Host: 220.158.21.114:8080' \
  "http://localhost:8080/<旧URL>"
```

### 結果（3件すべて 301 + 正しい Location）

| 旧URL | HTTPステータス | Location ヘッダー | 判定 |
|---|:---:|---|:---:|
| `/user-dicision1/` | **301 Moved Permanently** | `/user-decision1/` | ✅ |
| `/category/user-dicision/` | **301 Moved Permanently** | `/category/user-decision/` | ✅ |
| `/news_category/infomation/` | **301 Moved Permanently** | `/news_category/information/` | ✅ |

代表例（`/user-dicision1/`）の応答ヘッダー全文:
```
HTTP/1.1 301 Moved Permanently
Server: nginx/1.31.1
X-Redirect-By: redirection
Location: /user-decision1/
```
`X-Redirect-By: redirection` により、**Redirection プラグインが転送を処理している**ことが確認できます。

### 回帰確認（誤転送がないこと）
- `/user-decision2/`（正しい綴りの既存記事） → **200 OK**（転送されない）。タイポ修正対象だけが転送され、正常URLには影響しないことを確認しました。

### スクリーンショットについて
本作業は自動実行（ヘッドレス）のため、ブラウザでの画面キャプチャは取得していません。上記 curl のヘッダー出力を動作エビデンスとします。管理画面で目視確認される場合は、ステージングの **ツール → Redirection**（`http://localhost:8080/wp-admin/`）で、グループ `pegasus-renewal` に3件が並んでいることをご確認いただけます。

---

## 4. 補足（転送先の現況と、後工程について）

リダイレクト**ルール自体は3件とも仕様どおり301で動作**しています。一方、転送先 `/user-decision1/` は現時点では 404 を返します。これは、対象の投稿（ID 432）のスラッグがまだ旧綴り `user-dicision1` のままで、新スラッグ `user-decision1` の実体が存在しないためです。

計画表（②リダイレクト詳細の実装メモ）に記載のとおり、本番リニューアルでは「投稿スラッグを `user-decision1` に変更し、カテゴリーも `user-decision` へ再紐付け」する作業と対で行います。その結果、転送先も 200 で表示されるようになります。今回はご依頼の範囲である**リダイレクト3件の登録と301動作の確認**までを実施しました（スラッグ変更・カテゴリー再紐付けは別工程）。

---

## 5. ロールバック手順（必要な場合）

```bash
# グループごと削除（pegasus-renewal = id 3）
sg docker -c "docker compose exec -T db mysql -u pegasus -p<MYSQL_PASSWORD> pegasus_wp -e \
  \"DELETE FROM wp04447aredirection_items WHERE group_id=3; \
    DELETE FROM wp04447aredirection_groups WHERE id=3;\""

# もしくは実装前の状態へ全面復元
sg docker -c "docker compose exec -T db mysql -u pegasus -p<MYSQL_PASSWORD> pegasus_wp" \
  < backup/redirection_pre_20260618.sql
```

---

## 6. まとめ

| 項目 | 結果 |
|---|---|
| 実装件数 | 3件（すべて301・enabled） |
| 実装方法 | Redirection の PHP API（`Red_Item::create`） |
| 動作確認 | 3件すべて 301 + 正しい Location ヘッダーを確認 |
| 誤転送 | なし（正常URLは200のまま） |
| 本番環境 | 変更なし |
| バックアップ | 取得済み（`backup/redirection_pre_20260618.sql`） |
