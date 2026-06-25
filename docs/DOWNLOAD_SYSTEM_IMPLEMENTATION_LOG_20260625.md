# 資料請求システム 実装ログ（選択肢B・PDF保護＋トークン認証）

**実施日:** 2026年6月25日
**対象環境:** ステージングのみ（Docker / `pegasus_wp`）。**本番（pegasus-job.com）は一切無変更。**
**確定仕様:** 選択肢B（メール限定配布・リード収集）。`docs/DOWNLOAD_SYSTEM_DESIGN_20260623.md` 準拠。
**現行システム:** Download After Email（dae_download 566・停止中）は**削除せず温存**。

> メールアドレスはマスク（`info@[managed-domain]` = pegasus-job.com 系）。実値はCF7/コードに格納。

---

## 0. 完成した動作フロー（ステージング）
```
/download/ の資料請求フォーム(CF7・メールのみ＋同意)
  → 送信  → 管理者通知メール(リード=info@宛にメアド収集)
          → 自動返信メール(入力アドレス宛に /download-secure/?token=64hex&id=566 を送付)
  → メール内URLをクリック → トークン検証(有効期限7日・利用5回) → readfile でPDF配信
  → PDF直アクセス(protected/)は nginx で 404 遮断 ＝ メールを取らないとPDFを取れない
```

---

## 段階1：PDF保護（直アクセス遮断）

- 事前バックアップ: `backup/nginx.conf.pre_protected_20260625` / `backup/docker-compose.yml.pre_protected_20260625`。
- 保護ディレクトリ作成＆PDFコピー（元 dae-uploads は温存）:
  `wp-content/uploads/protected/pegasus-shiryou.pdf`（38902c1a….pdf と md5一致 3a4ede7f…、1,756,127バイト/12頁）。
- `nginx.conf` に追加（`^~` で正規表現ロケーションより優先）:
  ```nginx
  location ^~ /wp-content/uploads/protected/ {
      deny all;
      return 404;
  }
  ```
- **⚠️ 予期した事態と対処（記録）:** `nginx.conf` は単一ファイルのbind-mount。編集ツールがinodeを置換するため、`nginx -s reload` では**旧設定のまま**だった（コンテナが旧inodeを参照）。**`docker compose restart nginx`** でbind-mountを再解決し新設定を反映。既存サービス（db/wp/pma/mailpit）は無停止。
- **動作確認:**
  - protected/PDF 直アクセス → **404（遮断成功）**。`.php`偽装も 404。
  - PHP `is_readable()` = true（readfile 配信は可能）。
  - 通常uploads画像 → 200（遮断が広すぎない）。
  - サイト全ページ 200。

## 段階2：トークン認証システム

- 専用 **mu-plugin** で実装（ソースはgit `scripts/download_system/pega-download-system.php`、配置 `wp-content/mu-plugins/`）。
- テーブル `wp04447apega_download_tokens`（`$wpdb->prefix . 'pega_download_tokens'`）を `dbDelta` で作成（init で一度だけ、`pega_dl_db_ver` フラグ）。
  - 列: id / token(CHAR64) / resource_id / email / created_at / expires_at / max_uses(5) / used_count。
- トークン発行 `pega_dl_issue_token($email,$rid)`: `bin2hex(random_bytes(32))` の64桁hex、有効期限=+7日、上限=5。

> 設計の表名は `…_pegasus_dl_tokens` だったが、実プレフィックス `wp04447a`（末尾アンダースコアなし）に合わせ `$wpdb->prefix . 'pega_download_tokens'` を採用（命名のみの差異・仕様は同一）。

## 段階3：/download-secure/ エンドポイント

- rewrite `^download-secure/?$ → index.php?pega_dl=1`（query_var `pega_dl`）。flush 済み。
- `template_redirect` で検証→配信: トークン存在／期限内／回数未満 を確認 → `used_count+1` → `readfile()` でPDFをストリーミング（Content-Type: application/pdf, Content-Disposition: attachment, Content-Length）。
- 失敗時は `status_header(410)` ＋ 日本語メッセージ。
- **動作確認（トークン認証）:**

| ケース | 結果 |
|---|---|
| 有効トークン | **200 / application/pdf / Content-Length 1,756,127**（正しいファイル名） |
| 連続DL | used_count が 1→2 と加算 |
| 不正トークン | **410** |
| 回数超過（used_count=5） | **410** |
| 期限切れ（expires_at 過去） | **410**（「有効期限（7日）が切れています…」） |

## 段階4：CF7 資料請求フォーム

- CF7フォーム **「資料請求（CF7）」ID=4820** 作成。1画面完結（確認画面なし）。
  - 項目: `[email* your-email]` ＋ `[acceptance accept]`（同意必須）＋ `[hidden resource_id default:566]`。
- メール設定:
  - **Mail（管理者通知＝リード収集）**: To=`info@[managed-domain]` / From=サイトドメイン / Reply-To=`[your-email]` / 件名=`【資料請求】[your-email]` / 本文にメアド・資料ID・日時。
  - **Mail(2)（自動返信＝DL URL）**: To=`[your-email]` / 本文に `{{DOWNLOAD_URL}}` プレースホルダ。
- mu-plugin が `wpcf7_before_send_mail` でフォームID（`pega_dl_form_id`=4820）を判定 → トークン発行 → `wpcf7_mail_components` で `{{DOWNLOAD_URL}}` を `/download-secure/?token=…&id=566` に置換。
- **動作確認（mailpit・一連の流れ）:**
  - 送信 → status=mail_sent。
  - 自動返信「【ペガサス】資料ダウンロードのご案内」→ 入力アドレス宛、本文にDL URL。
  - 管理者通知「【資料請求】(メアド)」→ `info@[managed-domain]` 宛、**リード(メアド)記録**・Reply-To=入力アドレス。
  - 自動返信のURL → **PDF取得 200（1,756,127バイト）**。

## 段階5：/download/ ページ整備

- 事前バックアップ: `backup/download_page_pre_cf7_20260625.sql`。
- 2導線を整備（該当箇所のみ・他要素不変）:
  - **ダウンロード導線**: `[download_after_email id="566"]` → `[contact-form-7 id="4820"]`（メール入力→URL送付）。案内文も「メールアドレスをご入力ください。ダウンロード用のURLをメールでお送りします。」に整合。
  - **郵送導線**: 「郵送をご希望の方」ボタン（→ /contact/）は**維持**。
- **最終動作確認:**
  - /download/ = 200、CF7資料請求フォーム表示（your-email＋acceptance）、DAEショートコードのリテラル残りなし、郵送導線維持。
  - **PDF直アクセス protected/ = 404（遮断）** ＝ メールを取らないとPDFが取れない＝リード収集の目的達成。
  - サイト全ページ 200。
  - 既存温存: MW WP Form=5 / dae_download 566=存在 / Redirection=7 / CF7=4。

---

## 本番移行時に必要な作業（チェックリスト）

1. **mu-plugin 配置**: `scripts/download_system/pega-download-system.php` を本番 `wp-content/mu-plugins/` に配置（自動有効化）。init でトークンテーブルが本番DBに自動作成される。
2. **PDF配置**: 本番 `wp-content/uploads/protected/pegasus-shiryou.pdf` を用意（本番の現行PDF実体から配置）。`pega_dl_resources()` のパス/表示名を確認。
3. **PDF直アクセス遮断（本番Webサーバに合わせる）**:
   - 本番がApache（さくら）なら、設計どおり **`wp-content/uploads/protected/.htaccess` に `Deny from all`（または `Require all denied`）**。
   - 本番がnginxなら、本ステージングと同じ `location ^~ …/protected/ { deny all; return 404; }`。
   - 反映後、protected/PDF 直アクセスが 403/404 になることを確認。
4. **CF7資料請求フォーム**: 本番にフォームを作成（またはエクスポート/インポート）。**本番フォームIDを `update_option('pega_dl_form_id', 本番ID)` で設定**（IDは環境ごとに異なるため必須）。
5. **メール送信（mailpit→本番SMTP）**: ステージングの Code Snippets「テスト用SMTP送信(mailpit)」(id=6) は**本番では無効化**。本番は実SMTP/サーバMTAで送信。自動返信（DL URL）と管理者通知（リード）が**実際に届く**ことを確認（SPF/DKIM）。
6. **rewrite flush**: 本番で「設定→パーマリンク」を保存し、`/download-secure/` を有効化。
7. **/download/ ページ**: 本番 /download/ の `[download_after_email id="566"]` を `[contact-form-7 id="本番ID"]` に差替、郵送ボタンは維持。
8. **最終確認（本番）**: 資料請求→自動返信→URL→PDF取得 の一連、PDF直アクセス遮断、トークンの期限/回数、サイト200。
9. **旧DAEの整理**: 本番安定後、Download After Email プラグイン／dae_download 566／dae-uploads を Phase 4 で整理。

---

## まとめ
- 選択肢B（メール限定配布・リード収集）を、PDF保護(nginx deny)＋トークン認証(/download-secure/)＋CF7フォーム＋自動返信URL で**ステージング上に完全実装・動作確認済み**。
- トークンは有効期限7日・利用5回が正しく機能（有効/無効/期限切れ/回数超過すべて検証）。
- PDF直アクセスは404で遮断＝メール取得が必須＝目的達成。
- 既存（MW WP Form / Download After Email）は無変更で並存。本番は無変更。
