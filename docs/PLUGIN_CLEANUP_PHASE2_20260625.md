# プラグイン整理 実行ログ（Phase 4・デザイン非直結の段階的削除）

**実施日:** 2026年6月26日
**対象環境:** ステージングのみ。本番は無変更。
**方針:** デザイン方針の到着待ちのため、**デザイン直結のブロック系（Gutentor / Useful Blocks / Easy Accordion / Templateberg）は対象外**。デザインに影響しないものを調査のうえ削除。
**結果:** 候補5件すべてを**調査の結果「安全」と確認して削除**。保留なし。プラグイン 25 → **20**。

---

## 0. 結論サマリー

| プラグイン | 調査結果 | 判断 |
|---|---|:---:|
| Regenerate Thumbnails | サムネイル再生成の一回限りツール。表示・他機能への依存なし | **削除** |
| Crop Thumbnails | 手動クロップ設定メタ **0件**（失われる設定なし） | **削除** |
| To Top | **SWELLのpageTopボタンが既定で稼働中**（id="pagetop"）。To Topは重複 | **削除**（SWELL機能で代替） |
| WordPress Popular Posts | CSS/JS・REST等の**資産のみ読込、可視の人気記事リストは全ページ0** | **削除** |
| Yoast Duplicate Post | republish保留1件（1296）を解消後、複製は編集補助のみで依存なし | **削除** |

- 作業前バックアップ：`backup/full_db_pre_cleanup2_20260626.sql.gz`（DB全体）／`backup/plugins_cleanup2_20260626.tgz`（5プラグインのファイル）。gitignore対象。
- **1件ずつ削除し、各削除後に全9ページ200を確認。**

---

## 1. 調査結果（削除前）

### Regenerate Thumbnails
- 既存画像のサムネイルを作り直すツール。常駐機能ではなく、削除しても既存画像・表示に影響しない。依存なし。

### Crop Thumbnails
- 手動クロップ設定（`crop_thumbnail` 系メタ）**0件**。手動クロップした画像の設定喪失リスクなし。通常の画像処理はWordPress標準で足りる。

### To Top（→ SWELLで代替）
- 現状、`/about/` 等に **SWELLのpageTopボタン `<button id="pagetop" class="c-fixBtn …" data-onclick="pageTop">` が既に出力**（SWELL既定 `pagetop_style='fix_circle'`、全ページで確認）。
- To Topプラグインも別途ボタン（`id="to-top"` / `plugins/to-top` 資産）を出力しており、**ボタンが二重**だった。
- → SWELLの機能で代替済み（追加設定不要）。To Top削除で重複が解消。

### WordPress Popular Posts（慎重調査）
- 公開投稿に `[wpp]` ショートコード **なし**。
- フロントの「WPP痕跡」は **CSS/JS・REST(wpp-json)・ウィジェット/ショートコードのスクリプトハンドルのみ**で、**可視の人気記事リスト（`wpp-list` 等）は / ・/news/ ・/reports/ ・/column/ ・記事・記事一覧の全テスト箇所で0**。
- → 公開ページに人気記事の**表示はない**。削除で表示崩れは発生しない。
- 備考：WPPの閲覧数集計データ（`…popularposts*` テーブル）は使われていないため温存→不要。SWELL代替は表示が無いため不要。

### Yoast Duplicate Post（republish保留の解消）
- 保留1件 = **投稿1296**（`dp-rewrite-republish`）。`_dp_original=1289`・`_dp_has_been_republished=1`。
- 元投稿 **1289は既に存在せず**、同内容の公開版は **1309（blog20221216-2, publish）に現存**。1296は `/?p=1296` が **404**（非公開）。
- → 1296は**再公開完了済みの残骸**。**ゴミ箱へ移動して解消**（表示・公開コンテンツに影響なし）。republish保留=0 に。
- Duplicate Postは複製（編集補助）機能のみで、フロント表示・他機能への依存なし。

### 依存関係（全候補共通）
- 今フェーズで重要な **CF7 / Flamingo / Code Snippets / Redirection / 資料請求mu-plugin / SEO SIMPLE PACK** はいずれも削除対象に依存せず、無影響。
- SWELLテーマは To Top/WPP に依存せず（pageTopは自前機能）。

---

## 2. 削除の実施（1件ずつ・都度確認）

| 順 | プラグイン | 結果 | 削除後の全9ページ |
|---:|---|:---:|:---:|
| 0 | （前処理）投稿1296をゴミ箱へ（republish解消） | OK | 200 |
| 1 | Regenerate Thumbnails | 削除OK | 200 |
| 2 | Crop Thumbnails | 削除OK | 200 |
| 3 | To Top | 削除OK | 200 |
| 4 | WordPress Popular Posts | 削除OK | 200 |
| 5 | Yoast Duplicate Post | 削除OK | 200 |

---

## 3. SWELL機能で置き換えたもの

| 旧プラグイン | 置き換え先 | 状態 |
|---|---|---|
| To Top（ページトップへ戻る） | **SWELL標準のpageTopボタン**（`pagetop_style=fix_circle`・既定で有効） | 削除後も `id="pagetop"` ボタンが全ページで稼働。追加設定不要 |

> WordPress Popular Posts は**可視の表示が無かった**ため、SWELLでの置き換えは不要（削除のみ）。

---

## 4. 動作確認結果（全削除後）

| 項目 | 結果 |
|---|---|
| サイト全9ページ | ✅ すべて200（/・/contact/・/application/・/download/・/homeworker/・/news/・/reports/・/column/・/about/） |
| トップへ戻るボタン | ✅ **SWELL pageTop が代替**（id="pagetop"=1）、To Top痕跡=0 |
| 人気記事表示 | ✅ もともと表示なし。WPP資産も除去（/ のwpp資産=0）、表示崩れなし |
| SEO（SSP） | ✅ title重複なし・Yoast痕跡0 |
| サイトマップ | ✅ /wp-sitemap.xml=200、/sitemap.xml=301 |
| リダイレクト | ✅ /user-dicision1/=301（8件維持） |
| CPT | ✅ /reports/report1/=200 |
| CF7 | ✅ 資料請求送信 mail_sent |
| republish保留 | ✅ 0件（1296解消） |
| レイアウト崩れ | なし（全ページ200・主要導線正常） |

プラグイン総数：25 → **20**（今回5件削除）。

---

## 5. 残るPhase 4作業

| 区分 | プラグイン | 状況 |
|---|---|---|
| **デザイン直結ブロック系（デザイン方針待ち）** | Gutentor / Useful Blocks / Easy Accordion / Templateberg | 今回対象外。SWELLブロックへの置換を伴うため、ペガサス様のデザイン方針確定後に段階的に |
| 保留C（条件待ち） | WP-Optimize（キャッシュ方針確認後）／WP Mail Logging（本番切替後）／SAKURA RS WP SSL（本番サーバ決定後） | 各条件確定後に判断 |

> 既に削除済み（Phase 4）：停止中4＋未使用3（前回）／Yoast SEO・XML Sitemap Generator（SEO移行）／本回5件 = 計14件。

---

## 6. 本番移行時の留意
- To Top削除前に、本番でも **SWELL pageTopが出ていること**（id="pagetop"）を確認（SWELL既定で出るはずだが念のため）。
- WPP削除は、本番に**可視の人気記事表示が無いこと**を確認してから（本番にウィジェット配置があれば要SWELL置換）。
- Duplicate Post削除前に、本番の **republish保留・複製途中の投稿が無いこと**を確認。
- 各削除は本番でも**1件ずつ＋都度確認**。

---

## まとめ
- デザイン非直結の5件（Regenerate/Crop Thumbnails・To Top・WPP・Duplicate Post）を、調査で安全確認のうえ削除。**保留なし**。
- To Topは**SWELL標準pageTopで代替**（既定稼働）。WPPは可視表示が無く削除のみ。Duplicate Postはrepublish残骸(1296)を解消後に削除。
- 既存機能（CF7・リダイレクト・CPT・SEO・サイトマップ）に影響なし。デザイン直結ブロック系は方針待ちで未着手。本番は無変更。
