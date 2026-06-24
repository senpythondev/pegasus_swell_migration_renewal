# フォーム移行 実装ログ（第一段階：CF7フォーム作成）

**実施日:** 2026年6月23日
**対象環境:** ステージングのみ（Docker / `pegasus_wp`）。本番は変更なし。
**範囲:** 第一段階＝**CF7フォーム2件の新規作成＋確認欄検証スニペット**。既存 MW WP Form・Download After Email は**無変更で並存**。本番切り替えはまだ行わない。
**準拠:** `docs/FORM_MIGRATION_DESIGN_20260623.md`

> メールアドレスのドメインはマスク（`[managed-domain]` = pegasus-job.com 系）。実アドレスは機能上 CF7 設定に格納（DB内のみ）。

---

## 1. メール送信環境の状況（重要）

| 確認項目 | 結果 |
|---|---|
| `wp_mail()` 実送信 | **不可**（戻り値 false／「メール機能をインスタンス化できませんでした」） |
| MTA（sendmail） | `/usr/sbin/sendmail` **実体なし**（wordpress:php8.1-fpm に未同梱） |
| SMTP設定 | なし（MailHog/mailpit 等の compose サービスなし、WP Mail SMTP 未導入） |
| wp-mail-logging | 有効（送信"試行"はログされるが、実配信はされない） |

→ **現状ステージングからメールは送信できません。** よってフォームの**実送信テストは次段階に延期**（設計どおり）。CF7フォームの作成・設定・レンダリングは完了済み。

### 推奨：テスト用SMTP（mailpit）の導入（次段階で実施）
実配信せず全メールを捕捉できる **mailpit** を推奨（MailHog後継・軽量）。
```yaml
# docker-compose.yml に追加するサービス（案）
  mailpit:
    image: axllent/mailpit:latest
    container_name: pegasus_mailpit
    restart: unless-stopped
    ports:
      - "8025:8025"   # Web UI（受信メール閲覧）
    networks: [ pegasus_net ]
```
WordPress 側は次のいずれかで mailpit:1025 へ送信:
- **WP Mail SMTP プラグイン**：Host=`mailpit` / Port=`1025` / 暗号化なし / 認証なし、または
- **Code Snippets** に `phpmailer_init` で SMTP を `mailpit:1025` に向ける小スニペット。

導入後、`/contact/` `/application/` からテスト送信 → mailpit Web UI（:8025）で **宛先（info / setsumei-kai）・自動返信・確認欄不一致エラー**を確認する。

---

## 2. 作成したCF7フォーム2件

| 用途 | CF7 ID | ショートコード | 後継元（MW） | 状態 |
|---|:---:|---|:---:|---|
| お問い合わせ | **4803** | `[contact-form-7 id="4803" title="お問い合わせ（CF7移行版）"]` | 1352 | 作成済（未配置） |
| 個別説明会申込 | **4804** | `[contact-form-7 id="4804" title="個別説明会申込（CF7移行版）"]` | 1369 | 作成済（未配置） |

- 管理画面「お問い合わせ（CF7）」一覧に2件表示を確認（既存の既定「コンタクトフォーム 1」(4800)は CF7 初期フォームで無関係）。
- **まだどのページにも未配置**（既存MWと並存。配置・切替は次段階）。

### 2-1. フィールド構成（設計どおり・レンダリング確認済み）
| フォーム | フィールド |
|---|---|
| 4803 お問い合わせ | your-name（必須）/ furigana / your-email（必須）/ your-email-confirm（必須）/ your-message（必須） |
| 4804 個別説明会申込 | **venue（会場 radio・必須：平塚事業所／在宅訓練）** / your-name（必須）/ furigana / your-email（必須）/ your-email-confirm（必須）/ your-message |

- `do_shortcode()` で両フォームとも `<form class="wpcf7...">` が正しく出力されることを確認。
- 旧1369の不整合（phoneバリデーション残骸・会場未必須）は設計どおり整理（phone廃止、venue必須化）。

### 2-2. メール設定（宛先はマスク。取り違えなしを確認）
**4803 お問い合わせ**
- Mail（管理者通知）: To=`info@[managed-domain]` ／ From=`一般社団法人ペガサス <info@[managed-domain]>` ／ Reply-To=`[your-email]` ／ 件名=`【お問い合わせ】[your-name] 様`
- Mail(2)（自動返信・active）: To=`[your-email]` ／ From=`一般社団法人ペガサス事務局 <info@[managed-domain]>` ／ 件名=`お問い合わせありがとうございました。`

**4804 個別説明会申込**
- Mail（管理者通知）: To=`setsumei-kai@[managed-domain]` ／ From=`…個別説明会担当 <setsumei-kai@[managed-domain]>` ／ Reply-To=`[your-email]` ／ 件名=`【個別説明会お申込み】[your-name] 様`
- Mail(2)（自動返信・active）: To=`[your-email]` ／ From=`…個別説明会担当 <setsumei-kai@[managed-domain]>` ／ 件名=`個別説明会のお申し込みありがとうございました。`

> ✅ **宛先取り違えチェック：お問い合わせ＝info、説明会＝setsumei-kai。正しく設定（逆転なし）を実機で確認済み。**
> From はサイト管理ドメイン、Reply-To は入力アドレス（設計どおり・迷惑メール化回避）。

---

## 3. メールアドレス確認欄の一致検証（Code Snippets）

- Code Snippets に新規スニペット **id=5「CF7 メールアドレス確認欄の一致検証」**（scope=global / active=1）を追加。
- `your-email` と `your-email-confirm` が不一致なら CF7 バリデーションで弾く（`wpcf7_validate_email*` フック）。
- 稼働確認: `function_exists('pegasus_cf7_confirm_email')` = あり、`has_filter('wpcf7_validate_email*', …)` = あり。
- ※ サイト全ページ200を維持（スニペットによる致命的エラーなし）。

---

## 4. 動作確認結果

| 項目 | 結果 |
|---|---|
| CF7フォーム2件 作成 | ✅ ID 4803 / 4804 |
| フィールド構成 | ✅ 設計どおり（確認済み） |
| メール宛先 | ✅ info / setsumei-kai（取り違えなし） |
| From / Reply-To | ✅ サイトドメイン / 入力アドレス |
| 自動返信(Mail2) | ✅ 有効・正しい名義 |
| 確認欄一致検証 | ✅ スニペット稼働 |
| フォームHTML出力 | ✅ 両フォームとも正常 |
| サイト無破損 | ✅ /・/contact/・/application/・/homeworker/・/news/ すべて200 |
| 実メール送信テスト | ⏸ 環境未整備のため次段階（mailpit導入後） |

### 現状不変の確認（既存を変更していないこと）
- MW WP Form：**5件のまま**（536/555/1135/1352/1369、無変更）
- Download After Email：**停止中のまま**
- Redirection ルール：**3件のまま**

---

## 5. 次段階への準備状況

- **第二段階（確認画面301転送・Flamingo導入）**: 設計済み（FORM_MIGRATION_DESIGN §4・§5）。Redirection 4件追記・Flamingo導入で着手可能。
- **メール送信テスト**: §1 の mailpit 導入で実施可能になる。第二段階の冒頭で mailpit を入れるのが効率的。
- **本番切替（/contact/・/application/ のショートコード差し替え）**: 第二段階以降。現在はMWと並存のまま。
- **第三段階（資料請求 PDF保護+トークン）**: 別設計書（DOWNLOAD_SYSTEM_DESIGN）に従い後続。

---

## 6. まとめ（第一段階）
- CF7フォーム2件（4803お問い合わせ / 4804説明会申込）を設計どおり作成。宛先・From/Reply-To・自動返信・確認欄検証まで設定・確認済み。
- 既存MW WP Form・DAE・Redirectionは無変更で並存。本番切替は未実施。
- メール実送信は環境未整備（MTAなし）→ mailpit 導入を推奨し、送信テストは次段階へ。

---

# 第二段階（mailpit導入・送信テスト・Flamingo・確認画面301転送）

**実施日:** 2026年6月24日

## A. mailpit 導入（Docker構成変更）
- 変更前 `docker-compose.yml` を `backup/docker-compose.yml.pre_mailpit_20260624` に保全。
- `docker-compose.yml` に **mailpit サービス**追加（`axllent/mailpit`、SMTP 1025 / Web UI 8025、`pegasus_net` に配置）。既存サービスのブロックは一切変更せず**追記のみ**。
- `docker compose up -d --no-recreate mailpit` で **mailpit のみ起動**。
- **既存サービスへの影響なしを確認**:
  - db / nginx / phpmyadmin = **Up 3 weeks（無停止）**、wordpress = **Up 6 days（無停止）**＝再作成されていない。
  - サイト全ページ200（/・/contact/・/application/・/homeworker/・/news/・/reports/）。
  - mailpit Web UI（http://localhost:8025/）= 200、wordpress→`mailpit:1025` SMTP 疎通OK。
- WordPress→mailpit 送信は **Code Snippets** で実装（id=6「テスト用SMTP送信(mailpit)」/ scope=global / active）。`phpmailer_init` で `mailpit:1025` へ。**※本番では無効化必須**（説明をスニペットに明記）。
- `wp_mail()` テスト → mailpit 受信を確認（基盤OK）。

## B. メール送信テスト（CF7実送信・mailpitで目視）
| テスト | 結果 |
|---|---|
| お問い合わせ(4803) 正常送信 | status=mail_sent。**管理通知 To=`info@[managed-domain]`**、自動返信 To=入力アドレス |
| 説明会申込(4804) 正常送信 | status=mail_sent。**管理通知 To=`setsumei-kai@[managed-domain]`**、自動返信 To=入力アドレス |
| 宛先取り違え | ✅ **なし**（お問い合わせ=info / 説明会=setsumei-kai、mailpitで目視確認） |
| From / Reply-To | ✅ From=サイトドメイン、**Reply-To=入力アドレス**（管理通知ヘッダで確認） |
| メール確認欄 不一致 | ✅ status=**validation_failed**、`your-email-confirm`にエラー、**mailpit受信0件（送信されない）** |

## C. Flamingo 導入（送信データ保存）
- Flamingo（CF7作者製・wordpress.org版）を `wp-content/plugins/flamingo` に配置 → 有効化（`is_plugin_active`=yes）。サイト200維持。
- CF7テスト送信 → **flamingo_inbound 投稿が作成**され、`_field_your-name / _field_your-email / _field_your-message / _from` が保存されることを確認。
- 備考（軽微）: 受信タイトルが `[your-subject]` 表示。フォームに `your-subject` 項目が無いためで、**データ本体は各フィールドに正しく保存**。必要なら Flamingo のタイトルマッピングを後日調整。
- ※ Flamingo はサードパーティのため Git 非対象（`wp-content/plugins/` 除外）。

## D. 確認画面の301転送（Redirection 4件追加）
- 実施前に `backup/redirection_pre_4urls_20260624.sql`（items/groups）を保全。
- group **pegasus-renewal（id=3）** に4件追加（既存3件とは別・衝突なし）:

| 旧URL | → | 推移 |
|---|---|---|
| /contact/contact-confirm/ | /contact/ | 301 → 最終200 |
| /contact/contact-thanks/ | /contact/ | 301 → 最終200 |
| /application/confirm/ | /application/ | 301 → 最終200 |
| /application/thanks/ | /application/ | 301 → 最終200 |

- **既存3件（タイポ修正）維持**: /user-dicision1/・/category/user-dicision/・/news_category/infomation/ すべて301継続。
- 追加後も **CF7メール送信は正常**（mail_sent・2通）。
- ※ 入力ページ /contact/・/application/ は**まだMWショートコードのまま**（本番切替=ショートコード差し替えは次段階）。確認/完了URLが先に転送される「移行途中の状態」だが、ステージングでの想定どおり。

## E. 第二段階 後の状態
- コンテナ: db/nginx/pma/wordpress 無停止 ＋ mailpit（healthy）。
- データ: **MW=5（無変更）/ CF7=3（既定1＋移行2）/ Redirection=7（3＋4）/ Flamingo保存=2件**。
- 既存 MW WP Form・Download After Email（停止中）は**無変更で並存**。本番切替は未実施。

## F. 第二段階で追加した「本番移行時に戻す/無効化すべきもの」
- Code Snippets id=6「テスト用SMTP送信(mailpit)」… **本番では無効化**（本番は実SMTP/サーバMTAを使用）。
- mailpit サービス … ステージング専用。本番compose には載せない。
