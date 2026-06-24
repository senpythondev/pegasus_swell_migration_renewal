# 資料請求システム 詳細設計（選択肢B・メール限定配布方式）

**作成日:** 2026年6月23日
**対象環境:** ステージング（実装は次ステップ。本書は**設計のみ**）。本番には触れていません。
**確定仕様（変更不可）:** 2026年5月にペガサス様と確定した「**選択肢B（メール限定配布方式）**」。提案書 8.4 節準拠。
**目的:** リード情報（メールアドレス）の収集。Download After Email → **CF7ベースの新システム**へ置き換え。

> メールドメインはマスク（`[managed-domain]`）。

---

## 0. 現状（調査で確定）

| 項目 | 現状 |
|---|---|
| 現行機構 | Download After Email（dae_download **ID 566**、ショートコード `[download_after_email id="566"]`） |
| プラグイン状態 | **停止中**（active_plugins に無し） |
| PDF本体 | `wp-content/uploads/dae-uploads/38902c1a528ac95c30855324e5e3a68f.pdf`（実在・md5 3a4ede7f…） |
| 添付登録 | attachment 4407「一般社団法人ペガサス-在宅支援サービス資料」（guid は本番パス /2025/06/…。**ステージングに2025/06コピーは無し**＝実体は dae-uploads のみ） |
| 利用実績 | 42 used of 57リンク（実需要あり＝確実に再現が必要） |
| **PDF直アクセス** | **現在 200 で誰でも取得可能（保護されていない）** ← 新システムで遮断 |
| /download-secure/ スラッグ | 衝突なし |

---

## 1. 全体構成（選択肢B）

```
[利用者] /download/ の資料請求フォーム（CF7・1画面完結）
   │  メールアドレスを入力して送信
   ▼
[CF7] 送信 → 自動返信メールに「ダウンロードURL（トークン付き）」を記載
   │  （同時に管理者にもリード通知＝メールアドレス収集）
   ▼
[利用者] メール内のURLをクリック  /download-secure/?token=xxxx&id=566
   ▼
[エンドポイント] トークン検証（有効期限7日・利用5回以内）
   → OK: PHP readfile() でPDFをストリーミング配信
   → NG: エラーページ（期限切れ/回数超過/不正）
```
- **確認画面なしの1画面完結**（選択肢B＝メールアドレス入力のみ）。
- PDF本体は**保護ディレクトリ**に置き、直リンク不可。配信は必ずトークン検証を経る。

---

## 2. 資料請求フォーム（CF7・1画面完結）

### 2-1. マークアップ
```
<label>メールアドレス（必須）
[email* your-email autocomplete:email] </label>
[acceptance accept] 個人情報の取り扱いに同意します（プライバシーポリシー） [/acceptance]
[hidden resource_id default:566]
[submit "資料をメールで受け取る"]
```
- 入力はメールアドレスのみ（＋同意チェック）。`resource_id`=566 を hidden で保持（将来複数資料に拡張可）。

### 2-2. メール設定
**Mail（管理者通知＝リード収集）**
- To: `info@[managed-domain]`（資料請求リードの通知先。運用に合わせて変更可）
- From: `一般社団法人ペガサス <info@[managed-domain]>` / Reply-To: `[your-email]`
- Subject: `【資料請求】[your-email]`
- Body: `資料請求がありました。メールアドレス：[your-email] ／ 資料ID：[resource_id] ／ 日時：[_date] [_time]`

**Mail(2)（自動返信＝ダウンロードURL送付）**
- To: `[your-email]`
- From: `一般社団法人ペガサス <info@[managed-domain]>`
- Subject: `【ペガサス】資料ダウンロードのご案内`
- Body（`{{DOWNLOAD_URL}}` は §4 のスニペットがトークンURLに置換）:
  ```
  この度は資料をご請求いただきありがとうございます。
  下記URLより7日以内にダウンロードしてください（ダウンロード回数の上限があります）。

  {{DOWNLOAD_URL}}

  ※本メールに心当たりがない場合は破棄してください。
  一般社団法人ペガサス
  ```

---

## 3. PDF保護（直接アクセス遮断）— nginx 読み替え

確定仕様は `.htaccess` 遮断だが、**本環境は nginx + php-fpm で .htaccess は無効**。以下に読み替えて設計。

### 3-1. 保護ディレクトリ
- 配置先: `wp-content/uploads/protected/`（新規）。ここに資料PDFを置く。
- 例: `wp-content/uploads/protected/pegasus-shiryou.pdf`（dae-uploads の 38902c1a….pdf をコピー/リネーム）。

### 3-2. nginx で直アクセスを遮断（`nginx.conf` に追記する設計）
```nginx
# 保護ディレクトリ：直アクセス禁止（配信は /download-secure/ 経由のみ）
location ^~ /wp-content/uploads/protected/ {
    deny all;
    return 404;
}
```
- PHP の `readfile()` は nginx の location 制限の影響を受けず、サーバ上の絶対パスから読めるため、`/download-secure/` 経由の配信は機能する。
- **注意:** `nginx.conf` 変更後は `docker compose restart nginx` が必要（実装時）。既存の uploads キャッシュ設定（js/css/画像）とは別 location のため影響なし。
- あわせて**現行の無防備な配信元を塞ぐ**：旧 `dae-uploads/` のPDFは保護ディレクトリへ移動（または同様に deny）。本番移行時は media library 直URL（attachment 4407）も保護対象に含めるか確認。

---

## 4. トークン認証付きダウンロードURL

### 4-1. URL形式
```
/download-secure/?token=<64桁ランダム>&id=566
```
- token: `bin2hex(random_bytes(32))`（64桁hex）。**有効期限7日**・**利用回数上限5回**。

### 4-2. トークン保存テーブル（新規）
```sql
CREATE TABLE wp04447a_pegasus_dl_tokens (
  id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  token       CHAR(64)        NOT NULL,
  resource_id BIGINT UNSIGNED NOT NULL,
  email       VARCHAR(190)    NOT NULL,
  created_at  DATETIME        NOT NULL,
  expires_at  DATETIME        NOT NULL,
  max_uses    INT             NOT NULL DEFAULT 5,
  used_count  INT             NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY token (token),
  KEY resource_id (resource_id)
) DEFAULT CHARSET=utf8mb4;
```
※ Code Snippets または小さな専用 mu-plugin の有効化時に `dbDelta()` で作成。

### 4-3. CF7送信時：トークン生成＋自動返信URLへ差し込み（Code Snippets）
```php
// 資料請求フォーム送信時にトークンを発行し、自動返信メール本文の {{DOWNLOAD_URL}} を置換
add_action( 'wpcf7_before_send_mail', 'pegasus_dl_issue_token', 10, 1 );
function pegasus_dl_issue_token( $cf7 ) {
    $sub = WPCF7_Submission::get_instance();
    if ( ! $sub ) return;
    $data  = $sub->get_posted_data();
    // 対象フォームの判定（フォームIDで限定。実装時に実IDへ）
    if ( (int) $cf7->id() !== (int) PEGASUS_SHIRYOU_FORM_ID ) return;

    $email = isset( $data['your-email'] ) ? sanitize_email( $data['your-email'] ) : '';
    $rid   = isset( $data['resource_id'] ) ? (int) $data['resource_id'] : 566;
    if ( ! $email ) return;

    global $wpdb;
    $token = bin2hex( random_bytes( 32 ) );
    $now   = current_time( 'mysql' );
    $exp   = date( 'Y-m-d H:i:s', current_time( 'timestamp' ) + 7 * DAY_IN_SECONDS );
    $wpdb->insert( $wpdb->prefix . 'pegasus_dl_tokens', array(
        'token' => $token, 'resource_id' => $rid, 'email' => $email,
        'created_at' => $now, 'expires_at' => $exp, 'max_uses' => 5, 'used_count' => 0,
    ) );

    $url = home_url( '/download-secure/?token=' . $token . '&id=' . $rid );
    // 自動返信(Mail2)本文のプレースホルダを置換
    add_filter( 'wpcf7_mail_components', function ( $components, $contact_form, $mail ) use ( $url ) {
        if ( ! empty( $components['body'] ) ) {
            $components['body'] = str_replace( '{{DOWNLOAD_URL}}', $url, $components['body'] );
        }
        return $components;
    }, 10, 3 );
}
```

### 4-4. /download-secure/ エンドポイント（rewrite ＋ 配信）
```php
// rewrite ルール（有効化時に flush。設定→パーマリンク保存でも可）
add_action( 'init', function () {
    add_rewrite_rule( '^download-secure/?$', 'index.php?pegasus_dl=1', 'top' );
} );
add_filter( 'query_vars', function ( $v ) { $v[] = 'pegasus_dl'; return $v; } );

add_action( 'template_redirect', 'pegasus_dl_serve' );
function pegasus_dl_serve() {
    if ( ! get_query_var( 'pegasus_dl' ) ) return;

    $token = isset( $_GET['token'] ) ? preg_replace( '/[^a-f0-9]/', '', $_GET['token'] ) : '';
    $rid   = isset( $_GET['id'] ) ? (int) $_GET['id'] : 0;
    if ( strlen( $token ) !== 64 || ! $rid ) { pegasus_dl_fail( '不正なURLです。' ); }

    global $wpdb; $t = $wpdb->prefix . 'pegasus_dl_tokens';
    $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $t WHERE token=%s AND resource_id=%d", $token, $rid ) );
    if ( ! $row )                                  pegasus_dl_fail( 'トークンが無効です。' );
    if ( strtotime( $row->expires_at ) < time() )  pegasus_dl_fail( '有効期限（7日）が切れています。お手数ですが再度ご請求ください。' );
    if ( (int) $row->used_count >= (int) $row->max_uses ) pegasus_dl_fail( 'ダウンロード回数の上限に達しました。' );

    // 資料ID → 実ファイルのマッピング（実装時に確定）
    $map = array(
        566 => array(
            'path' => WP_CONTENT_DIR . '/uploads/protected/pegasus-shiryou.pdf',
            'name' => '一般社団法人ペガサス_在宅支援サービス資料.pdf',
        ),
    );
    if ( empty( $map[ $rid ] ) || ! is_readable( $map[ $rid ]['path'] ) ) pegasus_dl_fail( 'ファイルが見つかりません。' );

    // 利用回数を加算
    $wpdb->update( $t, array( 'used_count' => $row->used_count + 1 ), array( 'id' => $row->id ) );

    $file = $map[ $rid ];
    nocache_headers();
    header( 'Content-Type: application/pdf' );
    header( 'Content-Disposition: attachment; filename="' . rawurlencode( $file['name'] ) . '"' );
    header( 'Content-Length: ' . filesize( $file['path'] ) );
    readfile( $file['path'] );
    exit;
}
function pegasus_dl_fail( $msg ) {
    status_header( 410 ); // Gone（期限/回数）。不正時は 403 でも可
    wp_die( esc_html( $msg ), '資料ダウンロード', array( 'response' => 410 ) );
}
```
- `home_url()` ベースのため、ステージング/本番のドメイン差異に追随。
- rewrite 追加後は **flush（設定→パーマリンク保存、または有効化フック）** が必要。

---

## 5. 現行PDFの移行方法
1. `wp-content/uploads/dae-uploads/38902c1a528ac95c30855324e5e3a68f.pdf` を `wp-content/uploads/protected/pegasus-shiryou.pdf` へ**コピー**（リネーム）。
   - 実装コマンド例（次ステップ・コンテナ内）:
     ```
     mkdir -p wp-content/uploads/protected
     cp wp-content/uploads/dae-uploads/38902c1a528ac95c30855324e5e3a68f.pdf \
        wp-content/uploads/protected/pegasus-shiryou.pdf
     chown www-data:www-data wp-content/uploads/protected/pegasus-shiryou.pdf
     ```
2. nginx に §3-2 の deny を追記 → `docker compose restart nginx`。
3. 旧 dae-uploads の直URLが塞がれた（404）ことを確認。
4. PDFを差し替える場合は protected/ のファイルを置換するだけ（URLは不変）。

---

## 6. /download/ ページの2導線の扱い（作業3）

現状の /download/（固定ページ157）には2つのボタン：
- 「ダウンロード」：`[download_after_email id="566"]`（DAE）
- 「郵送をご希望の方」：`/contact/` へのリンク（お問い合わせ誘導）

### 6-1. 設計
| 導線 | 現行 | リニューアル後 |
|---|---|---|
| ダウンロード | DAEショートコード | **新CF7資料請求フォーム**（§2）に置換。送信→自動返信でトークンURL |
| 郵送をご希望の方 | /contact/ リンク | **維持**。リンク先 /contact/ は CF7化後も同一URL。任意で「資料の郵送希望」と分かるよう、リンクに `?subject=資料郵送希望` を付け、CF7側に hidden で受け取る拡張も可（任意） |

### 6-2. /download/ 本文の再現（イメージ）
```
<!-- リード文：資料のご請求方法 -->
<p>資料をご希望の方は、メールアドレスをご入力ください。ダウンロードURLをメールでお送りします。</p>
[contact-form-7 id="XXXX" title="資料請求"]

<!-- 郵送導線（維持） -->
<p>郵送をご希望の方は、お問い合わせフォームよりご連絡ください。</p>
<a class="wp-block-button__link" href="/contact/?subject=資料郵送希望">郵送をご希望の方はこちら</a>
```
- DAEショートコードは撤去（プラグインは停止中で現状も非表示）。本実装で完全にCF7へ置換。

---

## 7. 実装時の注意点・リスク

| # | 項目 | リスク/注意 | 対策 |
|---|---|---|---|
| 1 | PDF直アクセス | 現状200で誰でも取得可能 | §3 で protected/ ＋ nginx deny。旧 dae-uploads も塞ぐ |
| 2 | nginx変更 | 反映に restart 要・記述ミスで全体停止 | 設計の location をそのまま追記、restart後に全URL疎通確認 |
| 3 | rewrite flush | /download-secure/ が404のまま | 有効化フックで flush、または設定→パーマリンク保存 |
| 4 | トークン総当り | 64桁hexで実質不可だが要配慮 | 長さ64・UNIQUE・期限7日・回数5。失敗時は 410/403 |
| 5 | メール到達性 | 自動返信が届かないとDLできない | From=サイトドメイン、SPF/DKIM整備、テスト送信 |
| 6 | 個人情報 | メール収集＝リード情報の保持 | 保持テーブルの保管期間・アクセス権の運用ルール明記（同意acceptance必須化済み） |
| 7 | 多重DL | 5回制限の意図（共有抑止） | used_count加算をDL確定時に実施（本設計は配信直前に加算） |
| 8 | 既存DAEとの併存 | 本実装まで現行を保持 | DAEは停止中のまま。新システム検証後に /download/ を切替 |
| 9 | 添付4407の本番パス | 本番は /2025/06/ にPDF実体の可能性 | 本番移行時に実体パスを確認し protected/ へ集約 |

---

## 8. 確定仕様（選択肢B・提案書8.4節）との対応

| 確定仕様 | 本設計での実現 |
|---|---|
| 選択肢B：メール限定配布（リード収集） | §1〜§2：CF7 1画面（メール入力）＋自動返信でURL送付。管理者通知でリード収集 |
| 1画面完結（確認画面なし） | §2-1：入力はメールのみ、確認画面なし |
| PDF直アクセス遮断（.htaccess→環境読み替え） | §3：protected/ ＋ nginx `deny all`（.htaccess無効環境に対応） |
| トークン認証URL（/download-secure/?token=…&id=566） | §4-1：URL形式どおり。id=566 を踏襲 |
| 有効期限7日・利用5回 | §4-2/4-4：expires_at＝+7日、max_uses=5、used_count検証 |
| readfile()でストリーミング配信 | §4-4：template_redirect で readfile 配信 |
| 現行PDF（38902c1a….pdf）の移行 | §5：dae-uploads → protected/ にコピー |
| 42/57リンク使用（実需要） | 新システムで継続提供（ダウンタイムなく切替） |

**確定仕様との矛盾点：なし。** 唯一 `.htaccess` 遮断のみ、環境（nginx+php-fpm）に合わせて **nginx location ＋ PHP制御**へ読み替え（方針は同一＝直アクセス遮断）。これは事前に想定された読み替えであり、方針変更ではありません。
