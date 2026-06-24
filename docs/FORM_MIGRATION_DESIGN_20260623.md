# フォーム移行 詳細設計：お問い合わせ・個別説明会申込（CF7化）

**作成日:** 2026年6月23日
**対象環境:** ステージング（実装は次ステップ。本書は**設計のみ**）。本番には触れていません。
**確定方針（変更不可）:** MW WP Form → Contact Form 7。確認画面は「**簡素化＋301転送でURL保全**」（技術判断・確定済み）。
**対象フォーム:** 1352 お問い合わせ（現役）/ 1369 個別説明会申込（現役）

> メールアドレスのドメインは取扱ガイドに従いマスク（`[managed-domain]` = pegasus-job.com 系の管理ドメイン）。ローカル部は宛先の取り違え防止のため保持。

---

## 0. 前提（調査で確定済み）
- 現役フォームは2件のみ（1352・1369）。旧版 536/555/1135 は未使用（移行後に整理）。
- 既存 Redirection ルールは3件（タイポ修正・group=pegasus-renewal/id=3）。本設計の追加4件と**衝突なし**。
- CF7 は導入済み・未配置（クリーンに移行可能）。MW WP Form / Download After Email は**本実装まで保持**（無変更）。

---

## 1. CF7 フォーム定義（マークアップ）

### 1-1. お問い合わせ（旧 1352 → 新CF7）
```
<label>お名前（必須）
[text* your-name autocomplete:name] </label>

<label>ふりがな
[text furigana] </label>

<label>メールアドレス（必須）
[email* your-email autocomplete:email] </label>

<label>メールアドレス（確認のため再入力・必須）
[email* your-email-confirm] </label>

<label>お問い合わせ内容（必須）
[textarea* your-message] </label>

[submit "送信する"]
```
- 必須: your-name / your-email / your-email-confirm / your-message（旧 validation: noempty・mail に一致）
- ふりがな（furigana）は任意（旧 kana も任意）。

### 1-2. 個別説明会申込（旧 1369 → 新CF7）
```
<label>ご希望の会場（必須）</label>
[radio venue use_label_element default:1 "平塚事業所" "在宅訓練"]

<label>お名前（必須）
[text* your-name autocomplete:name] </label>

<label>ふりがな
[text furigana] </label>

<label>メールアドレス（必須）
[email* your-email autocomplete:email] </label>

<label>メールアドレス（確認のため再入力・必須）
[email* your-email-confirm] </label>

<label>備考・ご質問
[textarea your-message] </label>

[submit "申し込む"]
```
- 旧 1369 の残骸（phone の validation／会場の未必須）は**本設計で整理**：phone 廃止、会場（venue）は必須化。
- 会場の選択肢は現行どおり「平塚事業所／在宅訓練」（逗子は閉所済みで対象外）。

---

## 2. メール設定（CF7 Mail / Mail(2)）

CF7 は2通（Mail=管理者通知、Mail(2)=自動返信）を設定可能。MW の管理者通知→Mail、自動返信→Mail(2) に対応させる。

### 2-1. 差出人（From）/ Reply-To の方針（迷惑メール化回避）
- **From は必ずサイトドメインのアドレス**（例: `info@[managed-domain]`／`setsumei-kai@[managed-domain]`）。MW が From に `{E-mail}`（送信者本人）を使っていた挙動は**踏襲しない**（SPF/DKIM 失敗・なりすまし判定を避ける）。
- 送信者本人へ返信できるよう **Reply-To に送信者の `[your-email]`** を設定。

### 2-2. お問い合わせ（1352相当）
**Mail（管理者通知）**
- To: `info@[managed-domain]`　← 取り違え厳禁（お問い合わせは info）
- From: `一般社団法人ペガサス <info@[managed-domain]>`
- Reply-To: `[your-email]`
- Subject: `【お問い合わせ】[your-name] 様`
- Body:
  ```
  ホームページよりお問い合わせがありました。ご対応をお願いいたします。
  ---------------------------------------
  お名前　：[your-name]
  ふりがな：[furigana]
  メール　：[your-email]
  内容　　：
  [your-message]
  ---------------------------------------
  送信日時：[_date] [_time] ／ 送信元IP：[_remote_ip]
  ```

**Mail(2)（自動返信）**
- To: `[your-email]`
- From: `一般社団法人ペガサス事務局 <info@[managed-domain]>`
- Subject: `お問い合わせありがとうございました。`
- Body: 旧 mail_content（「自動返信です／確認のうえ改めてご連絡します」＋送信内容控え）を移植。

### 2-3. 個別説明会申込（1369相当）
**Mail（管理者通知）**
- To: `setsumei-kai@[managed-domain]`　← 取り違え厳禁（説明会は setsumei-kai）
- From: `一般社団法人ペガサス個別説明会担当 <setsumei-kai@[managed-domain]>`
- Reply-To: `[your-email]`
- Subject: `【個別説明会お申込み】[your-name] 様`
- Body:
  ```
  ホームページより個別説明会のお申込みがありました。ご対応をお願いいたします。
  ---------------------------------------
  ご希望会場：[venue]
  お名前　　：[your-name]
  ふりがな　：[furigana]
  メール　　：[your-email]
  備考　　　：
  [your-message]
  ---------------------------------------
  送信日時：[_date] [_time]
  ```

**Mail(2)（自動返信）**
- To: `[your-email]`
- From: `一般社団法人ペガサス個別説明会担当 <setsumei-kai@[managed-domain]>`
- Subject: `個別説明会のお申し込みありがとうございました。`
- Body: 旧 mail_content（控え＋追ってご連絡）を移植。

> **宛先取り違え防止チェック**：実装後の送信テストで、お問い合わせは info、説明会は setsumei-kai に**実際に着信**することを必ず確認。

---

## 3. メールアドレス確認欄（your-email-confirm）の一致検証

CF7 標準では2つのメール欄の一致を検証できないため、**Code Snippets（導入済）** に以下を追加する設計。

```php
// CF7: メールアドレス確認欄の一致検証（your-email == your-email-confirm）
add_filter( 'wpcf7_validate_email*', 'pegasus_cf7_confirm_email', 20, 2 );
add_filter( 'wpcf7_validate_email',  'pegasus_cf7_confirm_email', 20, 2 );
function pegasus_cf7_confirm_email( $result, $tag ) {
    $tag = new WPCF7_FormTag( $tag );
    if ( 'your-email-confirm' === $tag->name ) {
        $email   = isset( $_POST['your-email'] ) ? trim( wp_unslash( $_POST['your-email'] ) ) : '';
        $confirm = isset( $_POST['your-email-confirm'] ) ? trim( wp_unslash( $_POST['your-email-confirm'] ) ) : '';
        if ( $email !== $confirm ) {
            $result->invalidate( $tag, 'メールアドレスが一致しません。確認のためもう一度ご入力ください。' );
        }
    }
    return $result;
}
```
※ 2フォーム共通（確認欄の name を `your-email-confirm` で統一）。

---

## 4. 確認画面の簡素化＋301転送（URL資産保全）

### 4-1. 方針
- MW の「入力→確認→完了」3ページ遷移を、**CF7のインライン送信（同一ページ＋完了メッセージ）に簡素化**。
- 既存の確認/完了ページ（公開URL）は **Redirection で301転送**して資産保全。ページ実体は当面残置（Redirection が init で先に転送するため表示はされない）。本実装が安定後に整理（trash）してよい。

### 4-2. 追加する301リダイレクト（4件・Redirection group=pegasus-renewal へ）
| 旧URL（確認/完了） | 転送先 | コード |
|---|---|:---:|
| /contact/contact-confirm/ | /contact/ | 301 |
| /contact/contact-thanks/ | /contact/ | 301 |
| /application/confirm/ | /application/ | 301 |
| /application/thanks/ | /application/ | 301 |

Redirection インポート用CSV（実装時）:
```csv
source,target,regex,code
/contact/contact-confirm/,/contact/,0,301
/contact/contact-thanks/,/contact/,0,301
/application/confirm/,/application/,0,301
/application/thanks/,/application/,0,301
```
- 既存3件（タイポ修正）は**変更しない**。本4件は追記のみ。実装後の合計は7件。
- 完了表示は CF7 の `[submit]` 後インラインメッセージ（「お問い合わせ／お申し込みありがとうございました。確認のうえ改めてご連絡します。」）で代替。

---

## 5. 送信データの保存（usedb=1 の代替）

- MW WP Form は送信内容をDB保存（管理画面で閲覧）。CF7 標準は保存しない。
- **Flamingo プラグイン**（CF7作者製・無料）を導入し、送信内容を「Flamingo → 受信メッセージ」で保存・一覧する設計。
- 個人情報保持の観点：保存期間・閲覧権限の運用ルールを別途明記（次ステップ）。

---

## 6. 実装手順（次ステップ用・概要）
1. CF7 で2フォーム作成（§1・§2）。
2. Code Snippets に確認欄一致検証を追加（§3）。
3. Flamingo 導入（§5）。
4. /contact/（155）/ /application/（151）の本文を、MWショートコードから CF7 ショートコードへ差し替え。
5. Redirection に4件追加（§4-2）。設定→パーマリンク不要（Redirectionはrewrite非依存）。
6. 送信テスト：管理通知の**宛先正誤**（info/setsumei-kai）、自動返信受信、確認欄不一致時のエラー、Flamingo保存、旧確認URLの301。
7. 安定確認後、旧 MW WP Form（5件）と確認/完了ページ4枚を整理。

---

## 7. 実装時の注意点・リスク

| # | 項目 | リスク/注意 | 対策 |
|---|---|---|---|
| 1 | 宛先取り違え | info と setsumei-kai を逆に設定すると問い合わせが届かない | 実送信テストで両方の着信を確認 |
| 2 | From のなりすまし判定 | From に送信者アドレスを使うと迷惑メール化 | From=サイトドメイン／Reply-To=[your-email] |
| 3 | 確認画面の喪失 | 確認画面前提の運用だった場合のUX変化 | 簡素化は確定方針。完了メッセージで明示、入力内容の自動返信控えで担保 |
| 4 | 送信データ未保存 | Flamingo未導入だと問い合わせ履歴が残らない | Flamingo を必須導入 |
| 5 | 確認欄検証 | スニペット未適用だと不一致を素通り | §3を先に適用してからテスト |
| 6 | 旧URLの転送 | 確認/完了4URLの転送漏れでSEO/ブックマーク切れ | §4-2を登録、curlで301確認 |
| 7 | メール到達性 | 自動返信が迷惑判定 | SPF/DKIM・送信元の整備（サーバ移行時に再確認） |

---

## 8. 確定方針との対応
- 「MW WP Form → CF7」：§1〜§6で全面設計。
- 「簡素化＋301転送でURL保全」：§4 で4URLの転送を設計（既存3件は不変）。
- 「宛先取り違え注意・From是正」：§2 で明示。
- 矛盾点：**なし**（確定方針どおり）。1369の phone validation 残骸・会場未必須は不整合だったため、整理方針（phone廃止・会場必須化）を§1-2に明記（仕様の不整合修正であり方針変更ではない）。
