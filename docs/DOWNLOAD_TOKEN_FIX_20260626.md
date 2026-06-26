# 資料請求ダウンロードリンク「不正なURLです」修正ログ

**実施日:** 2026年6月26日
**対象環境:** ステージングのみ（Docker / `pegasus_wp`）。本番（pegasus-job.com）は無変更。
**事象:** 資料請求の自動返信メールのダウンロードリンクをクリックすると「**不正なURLです。**」(HTTP 410) が表示され、PDFが取得できない。

---

## 0. 結論サマリー

| 問い | 回答 |
|---|---|
| 「不正なURル」の原因 | ダウンロードURLが **`&id=0`**（resource_id=0）になっていた。mu-plugin は `id` が偽値だと「不正なURLです」を返す（正しい防御）。**トークン自体は有効**だった |
| デモメールのトークンは本物かダミーか | **本物**（DBに存在・未失効）。ダミーではない。ただし **resource_id が 0** で発行されていた |
| 根本原因 | **CF7資料請求フォームの hidden フィールド `[hidden resource_id default:566]` が、描画時に `value=""`（空）** になっていた（CF7が default を適用せず）。→ ブラウザ送信で `resource_id=""` → mu-plugin が `(int)"" = 0` → URLが `id=0` |
| Basic認証の干渉 | **なし**。Basic認証はnginx層、トークン検証はWordPress層で独立。正規トークンは認証下で200 |
| 実フォーム送信→自動返信→DL の一連 | ✅ 修正後、ブラウザ相当の送信で **id=566・PDF 200** を確認 |
| 検収デモ | ✅ クリーンな有効デモに差し替え済み（DL可能・回数に余裕） |

> 重要：これは**デモだけの問題ではなく、実際のブラウザ送信でも発生する本番影響のあるバグ**だった（フォームが空の resource_id を送るため）。過去の検証が通っていたのは、テスト時に `resource_id=566` を明示送信していたため。

---

## 1. 原因の特定

### 1-1. mu-plugin の該当ロジック（正しい防御）
`wp-content/mu-plugins/pega-download-system.php`：
```php
$rid = isset( $_GET['id'] ) ? (int) $_GET['id'] : 0;
if ( strlen( $token ) !== 64 || ! $rid ) { pega_dl_fail( '不正なURLです。' ); }  // ← id=0 だと !$rid が真
```
→ `id=0` のとき「不正なURLです」。これは不正URL防御として正しい挙動。問題は **URLが id=0 になっていたこと**。

### 1-2. トークンテーブルの実態
クリックされたデモのトークンは DB に存在し、有効だったが **resource_id=0**：

| id | token | resource_id | email | used | 状態 |
|---|---|:---:|---|:---:|---|
| 9 | 06f4b8bef575… | **0** | test@gmail.com | 0 | 本物・未失効だが **rid=0** |

→ ダミーではなく**本物のトークン**。ただし resource_id=0 のため URL が `id=0` になり弾かれていた。

### 1-3. 根本原因＝CF7フォームの hidden 値が空
- フォーム定義は `[hidden resource_id default:566]`（一見正しい）。
- だが **実際の描画は値が空**：
  ```html
  <input class="wpcf7-form-control wpcf7-hidden" value="" type="hidden" name="resource_id" />
  ```
- CF7 が `default:566` を hidden の value に反映していなかった。
- mu-plugin の発行ロジック（修正前）:
  ```php
  $rid = isset( $data['resource_id'] ) ? (int) $data['resource_id'] : 566;
  ```
  → `resource_id` は**送られてくるが空文字**のため `isset` は真、`(int)"" = 0`。**未送信なら 566 になるが、空送信だと 0**になる落とし穴。
- 結果、ブラウザからの実送信は **rid=0** のトークンを発行し、メールのURLが `id=0` → 「不正なURル」。

> 過去の「トークンURL→PDF 200」検証は、`resource_id=566` を明示送信していたため成功していた。ブラウザの素の送信経路が未検証だった。

---

## 2. 修正内容（2層で対処）

### 2-1.【主因】CF7フォームの hidden を実値で出力（DB・ステージング）
- フォーム4820の hidden タグを **`[hidden resource_id default:566]` → `[hidden resource_id "566"]`** に変更（WPCF7 API で `set_properties`→`save`）。
- 結果、描画が **`value="566"`** になり、ブラウザ送信で `resource_id=566` が確実に送られる。
- 事前バックアップ：`backup/cf7_4820_form_pre_ridfix_20260626.sql`。

### 2-2.【堅牢化・防御多重化】mu-plugin の発行ロジック（git管理・本番にも反映される）
- `scripts/download_system/pega-download-system.php`（＝稼働 mu-plugin と同一）の発行箇所を変更：
  ```php
  // 変更前: isset(...) ? (int)... : 566   ← 空文字だと 0 になる
  // 変更後:
  $rid = ! empty( $data['resource_id'] ) ? (int) $data['resource_id'] : 566;
  ```
- **空文字・0 を無効値として既定資料(566)にフォールバック**。資料は現状1点(566)のみのため、空→566 は意図どおりで安全。
- これにより、フォーム側が将来再び空を送っても `id=0` のURLは生成されない（**本番でも同種事故を防止**）。
- 事前バックアップ：`backup/pega-download-system.php.pre_ridfix_20260626`。git版＝稼働版の一致を確認。
- ※ トークン**検証・配信ロジック（readfile・回数・期限）は無変更**。発行時の既定値ガードのみ追加。

---

## 3. 動作確認結果

### 3-1. フォーム描画
| 項目 | 修正前 | 修正後 |
|---|---|---|
| `/download/` の hidden value | `value=""` | **`value="566"`** |

### 3-2. エンドツーエンド（3経路すべて id=566・PDF 200）
| 送信パターン | 発行 resource_id | DL URL | PDF |
|---|:---:|---|:---:|
| A. resource_id=566（修正後ブラウザ相当） | 566 | `…&id=566` | **200 / application/pdf** |
| B. resource_id=空（旧バグ再現） | **566**（堅牢化で救済） | `…&id=566` | **200** |
| C. resource_id 無し | 566（既定） | `…&id=566` | **200** |

### 3-3. Basic認証との干渉（なし）
| アクセス | 結果 |
|---|---|
| 認証あり＋正規トークン | **200 / application/pdf** |
| 認証なし | **401**（nginxで遮断。トークン検証以前） |
| 連続ダウンロード（2回目） | **200**（max_uses に余裕） |

→ Basic認証（nginx層）とトークン検証（WordPress層）は独立。**干渉なし**。

### 3-4. 回帰
- `/`・`/download/`・`/contact/`・`/application/`・`/wp-sitemap.xml` すべて **200**。既存機能に影響なし。

---

## 4. 検収用デモの整備（橋本様が確実にDL体験できる状態）

- mailpit を一度クリアし、**壊れた id=0 デモ・E2Eテスト分を一掃**。
- クリーンな検収デモを再投入（6通）：お問い合わせ（管理通知＋自動返信）／個別説明会（同）／**資料請求（管理通知＋資料DL案内）**。
- 資料DL案内メールのトークン（**#13・resource_id=566**）に検収用の余裕を付与：**`max_uses=99` / `used_count=0`**（橋本様が何度試してもダウンロード可能）。有効期限 2026-07-03。
- 最終確認：資料DL案内メールのリンク → **PDF 200 / 1,756,127バイト**。

> 橋本様の体験：`/mailpit/` で「【ペガサス】資料ダウンロードのご案内」を開く → リンククリック → （同じBasic認証で）PDFがダウンロードされる。

---

## 5. 本番移行への注意（申し送り）
- **mu-plugin の堅牢化は git 管理済み**（`scripts/download_system/`）。本番に配置すれば自動的に反映され、空 resource_id 事故を防止。
- **本番のCF7資料請求フォームでも、hidden を `[hidden resource_id "566"]`（実値）で作成**すること（`default:` は描画されない場合がある）。本番フォーム作成手順（`DOWNLOAD_SYSTEM_IMPLEMENTATION_LOG` §本番移行）に本注意を反映。
- 検収用に付与した `max_uses=99` はステージングのデモ用。本番の実トークンは既定（5回・7日）のままでよい。

---

## まとめ
- 「不正なURル」は **トークンが偽物だったのではなく、URLの `id` が 0** だったため。原因は **CF7 hidden の `default:566` が空描画**されていたこと。
- **フォームを実値出力（`"566"`）に修正**＋**mu-plugin で空値を566にフォールバック**の2層で根治。実送信→自動返信→DLが id=566・PDF200 で機能。
- Basic認証は干渉なし。検収用デモを有効トークン（回数99）で整備し、橋本様が確実にDL体験できる状態にした。本番は無変更。
</content>
