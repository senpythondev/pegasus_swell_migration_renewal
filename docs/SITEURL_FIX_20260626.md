# wp-admin リダイレクト404 修正ログ（ポート8080欠落問題）

**実施日:** 2026年6月26日
**対象環境:** ステージングのみ（Docker / `pegasus_wp`）。本番（pegasus-job.com）は無変更。
**事象:** `http://220.158.21.114:8080/wp-admin/`（正しくは `/wp-admin` 末尾スラッシュ無し）にアクセスすると、**ポート欠落の `http://220.158.21.114/wp-admin/` にリダイレクトされ 404**。ポート80では別システムが稼働しているため。

---

## 0. 結論サマリー

| 項目 | 結果 |
|---|---|
| 原因 | **nginx の自動リダイレクト**（ディレクトリ末尾スラッシュ追加）が、内部リッスンポート80で絶対URLを生成し **:8080 が欠落**していた |
| siteurl / home | **元から正しい**（`http://220.158.21.114:8080`）。推測（ポート無し）は**該当せず** |
| 修正 | nginx に **`absolute_redirect off;`**（相対リダイレクト化）を追加。siteurl/home は変更せず |
| 結果 | `/wp-admin` → 相対 `Location: /wp-admin/` ＝ ブラウザの :8080 を保持 → 404解消 |
| 既存機能 | 影響なし（リダイレクト8件・CF7・CPT・サイトマップすべて正常） |
| DB内URL不整合 | **なし**（ポート無しURL 0件） |

---

## 1. 現状確認（修正前）

| 確認項目 | 値 |
|---|---|
| DB `siteurl` | `http://220.158.21.114:8080`（**:8080あり＝正しい**） |
| DB `home` | `http://220.158.21.114:8080`（**:8080あり＝正しい**） |
| wp-config.php の `WP_SITEURL`/`WP_HOME` | **定義なし**（DB値を使用） |

→ **siteurl/home は正しくポート8080を含んでいた。** 当初の推測「siteurl/home がポート無し」は**該当しなかった**。

### 切り分け：どこがポートを落としていたか
| リクエスト | 応答 | 判定 |
|---|---|---|
| `/wp-admin/`（スラッシュ有り） | 302 → `http://220.158.21.114:8080/wp-login.php…`（**:8080あり**） | WordPressのリダイレクトは正常 |
| `/wp-admin`（スラッシュ**無し**） | **301（Server: nginx）→ `http://220.158.21.114/wp-admin/`（:8080欠落）** | **nginxが原因** |
| `/news`（スラッシュ無し・CPT） | 200 | 実ディレクトリでないため影響なし |

- `Server: nginx` ヘッダーから、このリダイレクトは **WordPressではなく nginx が生成**していると確定。
- `/wp-admin` は実ディレクトリのため、nginx が末尾スラッシュを付けるための自動リダイレクトを発行する。
- **原因:** Docker のポートマッピング（ホスト8080→コンテナ80）下で、nginx は自分のリッスンポート（80）で絶対URLを組み立てる。80は既定ポートとして省略され、結果 `http://220.158.21.114/wp-admin/`（:8080欠落）になる。ブラウザはこれを辿り、ポート80の別システムへ → 404。
- ブラウザが `/wp-admin`（スラッシュ無し）でアクセスする、または管理画面内リンクが `/wp-admin` を指すと、この nginx 301 が発火して 404 になっていた。

---

## 2. 実施前バックアップ

- `backup/nginx.conf.pre_absredirect_20260626`（nginx.conf）。
- siteurl/home の現在値（`http://220.158.21.114:8080`）を本書に記録（DB変更は行わないため値の退避のみ）。

---

## 3. 修正内容

**siteurl/home は変更せず**（既に正しい）。**nginx.conf に1行追加**：

```nginx
server {
    listen 80;
    server_name _;
    root /var/www/html;
    index index.php index.html;

    # Docker のポートマッピング(ホスト8080→コンテナ80)環境では、nginx の自動リダイレクト
    # (ディレクトリ末尾スラッシュ追加 例: /wp-admin → /wp-admin/ など)が
    # 内部リッスンポート80で絶対URLを生成し、外部ポート :8080 が欠落して
    # http://220.158.21.114/wp-admin/(別システム)へ飛び 404 になる。
    # 相対リダイレクトに切り替え、ブラウザ側の host:port(:8080)を保持する。
    absolute_redirect off;
    …
```

- `absolute_redirect off` により nginx は **相対 `Location`（例 `/wp-admin/`）**を返す。ブラウザは元のスキーム・ホスト・ポート（`:8080`）を維持するため、ポート欠落が起きない。
- **適用方法:** nginx.conf は単一ファイルのbind-mount（編集でinode置換）のため、`docker compose restart nginx` で反映（`nginx -s reload` では旧設定のままになる既知事象）。`nginx -t` 構文OK・既存サービス（db/wordpress/pma/mailpit）は無停止。

### なぜ siteurl/home を変えない方法を選んだか
- siteurl/home は既に `:8080` で正しく、変更不要（むしろ変えると別の不整合を招く）。
- 問題は **nginx のインフラ層**。WordPress側ではなく nginx 側で直すのが正しい対処。Basic認証・リダイレクト・フォーム等の既存設定とも矛盾しない。

---

## 4. 動作確認結果（修正後）

### 4-1. wp-admin リダイレクト
| ホップ | Location | 判定 |
|---|---|---|
| `/wp-admin` → | **`/wp-admin/`（相対）** | ✅ :8080保持 |
| `/wp-admin/` → | `http://220.158.21.114:8080/wp-login.php?redirect_to=…%3A8080%2Fwp-admin%2F&reauth=1` | ✅ :8080あり |
| `/wp-login.php` | **200**（ログイン画面） | ✅ |

- ブラウザ実機フロー：`:8080/wp-admin` → 301 `/wp-admin/`（:8080維持）→ 302 `:8080/wp-login.php`（Basic認証は通過済み）→ **ログイン画面表示（404にならない）**。
- ※ コマンドライン検証で最終ホップが401になるのは、curl が別ホストへのリダイレクト時に認証情報を引き継がない仕様によるもので、ブラウザでは認証済みのため発生しない（`/wp-login.php` 単体は200を確認）。

### 4-2. フロント・既存機能（影響なし）
| 項目 | 結果 |
|---|---|
| フロント `/`・`/about/`・`/news/`・`/reports/report1/`・`/column/` | ✅ 200 |
| `/wp-login.php` | ✅ 200 |
| `/wp-sitemap.xml` | ✅ 200 |
| リダイレクト：`/sitemap.xml`→`/wp-sitemap.xml`、`/user-dicision1/`→`/user-decision1/`、`/contact/contact-confirm/`→`/contact/` | ✅ 正常（相対Locationで :8080保持） |
| CPT・サイトマップ・フォーム | ✅ 影響なし |

> リダイレクト8件（Redirectionプラグイン）は元々相対パスのLocationで動作しており、本修正と整合（ブラウザが :8080 を保持）。

### 4-3. DB内URL不整合スキャン
| 対象 | ポート無し `http://220.158.21.114/…` の出現 |
|---|---|
| wp_posts(post_content) | **0** |
| wp_postmeta | **0** |
| wp_options | **0** |
| （参考）正しい `:8080` を含む post_content | 906 |

→ **ポート不一致の不整合は無し**。投稿・メタ・オプションのURLはすべて :8080 で一貫（または相対）。検索置換は不要。

---

## 5. 予期しない事態
- なし。原因は nginx のポート欠落リダイレクトで、`absolute_redirect off` の1行で解消。siteurl/home・投稿データ・既存設定はいずれも無変更。

---

## 6. 本番移行への注意（チェックリスト追記済み）
- 本番では siteurl/home は `https://pegasus-job.com`。**本問題はステージング特有**（1つのVPSに複数環境が同居し、ポート8080で公開しているため nginx のポート欠落が顕在化）。
- 本番が標準ポート（443/80）単独なら本事象は起きない。ただしリバースプロキシ/コンテナ/非標準ポート構成の場合は `absolute_redirect off`（または `port_in_redirect`/プロキシ設定）で同様の対処が必要。
- `docs/PRODUCTION_CUTOVER_CHECKLIST.md` のフェーズ1に「siteurl/home を本番URL（https://pegasus-job.com）に設定」を確認項目として追記。

---

## まとめ
- wp-admin の 404 は **siteurl/home ではなく nginx の自動リダイレクトがポート :8080 を落としていた**ことが原因。
- **`absolute_redirect off`** を nginx に追加し、相対リダイレクト化で解消（siteurl/home は正しいため無変更）。
- wp-admin → ログイン画面が正常表示。フロント・既存機能（リダイレクト8件・CF7・CPT・サイトマップ）に影響なし。DB内URL不整合なし。本番は無変更。
</content>
