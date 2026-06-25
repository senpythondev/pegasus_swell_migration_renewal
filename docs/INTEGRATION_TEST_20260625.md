# 統合動作検証レポート（Phase 4・総合テスト）

**実施日:** 2026年6月26日
**対象環境:** ステージングのみ（Docker / `pegasus_wp`）。本番（pegasus-job.com）は**一切無変更**（GET/curlも本テストでは行わず、検証はすべてローカルのステージングに対して実施）。
**目的:** 本番カットオーバー前に、これまでに実装した全機能（URL/CPT・リダイレクト・フォーム・資料請求・SEO・メール難読化）がステージング上で正しく動作することを総合的に確認する。
**検証方法:** ステージング nginx（localhost:8080）へ、本番 home URL（`220.158.21.114:8080`）を `Host` ヘッダーで指定して実施。フォームは CF7 REST feedback エンドポイント、メールは mailpit API（:8025）、PDFは実バイト取得で確認。

> メールアドレスはレポート上もマスク方針だが、本テストでは送信ルートの正否確認のため、宛先ドメイン（pegasus-job.com）の表示を含む（実アドレスのローカル部はテスト用ダミー）。

---

## 0. 結論サマリー

| 区分 | 検証項目数 | PASS | FAIL |
|---|---:|---:|---:|
| A. 主要URL（200） | 11 | 11 | 0 |
| B. CPT個別記事（200） | 6 | 6 | 0 |
| C. タクソノミーアーカイブ（200） | 2 | 2 | 0 |
| D. リダイレクト（301） | 8 | 8 | 0 |
| E. サイトマップ | 3 | 3 | 0 |
| F. SEOメタ単一出力／OGP | 7 | 7 | 0 |
| G. メール難読化（/application/） | 5 | 5 | 0 |
| H. CF7フォーム送信／検証 | 4 | 4 | 0 |
| I. メール配信ルート（mailpit） | 6 | 6 | 0 |
| J. Flamingo保存 | 1 | 1 | 0 |
| K. 資料請求トークン／PDF保護 | 3 | 3 | 0 |
| **合計** | **56** | **56** | **0** |

**総合判定：全項目 PASS（FAIL 0）。** ステージング上で全機能が正常動作。

---

## A. 主要URL（HTTP 200）

| URL | 結果 | URL | 結果 |
|---|:---:|---|:---:|
| / | 200 | /homeworker/ | 200 |
| /about/ | 200 | /program/ | 200 |
| /contact/ | 200 | /result/ | 200 |
| /application/ | 200 | /news/ | 200 |
| /download/ | 200 | /reports/ | 200 |
| | | /column/ | 200 |

→ **11/11 PASS**

## B. CPT個別記事サンプル（HTTP 200）

| URL | 結果 | URL | 結果 |
|---|:---:|---|:---:|
| /news/program202404/ | 200 | /reports/report16/ | 200 |
| /news/news20240119/ | 200 | /column/20180105/ | 200 |
| /reports/report1/ | 200 | /column/20221216/ | 200 |

→ **6/6 PASS**（news / reports / works(column) 各CPTが稼働）

## C. タクソノミーアーカイブ（HTTP 200）

| URL | 結果 |
|---|:---:|
| /news_category/event/ | 200 |
| /reports_cat/it/ | 200 |

→ **2/2 PASS**

## D. リダイレクト（HTTP 301）

| 旧URL | → 転送先 | 結果 |
|---|---|:---:|
| /user-dicision1/ | /user-decision1/ | 301 |
| /category/user-dicision/ | /category/user-decision/ | 301 |
| /news_category/infomation/ | /news_category/information/ | 301 |
| /contact/contact-confirm/ | /contact/ | 301 |
| /contact/contact-thanks/ | /contact/ | 301 |
| /application/confirm/ | /application/ | 301 |
| /application/thanks/ | /application/ | 301 |
| /sitemap.xml | /wp-sitemap.xml | 301 |

→ **8/8 PASS**（タイポ3＋確認/完了4＋サイトマップ1）

## E. サイトマップ

| 項目 | 結果 |
|---|:---:|
| /wp-sitemap.xml | 200 |
| /sitemap.xml → /wp-sitemap.xml | 301 |
| 全CPT/タクソノミー網羅 | ✅ |

サブサイトマップ：`posts-post` / `posts-page` / `posts-news` / `posts-reports` / `posts-works` ＋ `taxonomies-category` / `taxonomies-news_category` / `taxonomies-reports_cat` ＋ `users`。
→ **3/3 PASS**（コアサイトマップが SWELL の無効化を上書きして稼働）

## F. SEOメタ単一出力／OGP（/about/ ほか）

| 項目 | 結果 |
|---|:---:|
| `<title>` | 1（重複なし） |
| meta description | 1 |
| og:title | 1 |
| og:description | 1 |
| og:image | 1 |
| Yoast痕跡 | **0** |
| SSP出力 | 1（SEO SIMPLE PACK 単独） |

- トップページ og:image = 既定画像 `…/uploads/2022/08/mv_sp.png`（アイキャッチ無しページの補完）。
→ **7/7 PASS**（Yoast痕跡ゼロ・二重出力なし・OGP補完）

## G. メール難読化（/application/）

| 項目 | 結果 |
|---|:---:|
| `function converter` 露出 | 0 |
| `charCodeAt` 露出 | 0 |
| `&lt;!--` 痕跡 | 0 |
| `[pega_setsumei_mail]` リテラル残り | 0（実行済み） |
| mailtoリンク | ✅ `mailto:…(antispambot符号化)…?subject=個別説明会希望` |

→ **5/5 PASS**（壊れた難読化JSは消滅、サーバ側ショートコードで mailto を出力）

## H. CF7フォーム送信／検証

| テスト | 期待 | 結果 |
|---|---|:---:|
| 4803 お問い合わせ 正常送信 | mail_sent | **mail_sent** |
| 4803 確認欄不一致 | validation_failed | **validation_failed**（「メールアドレスが一致しません」） |
| 4804 個別説明会申込 正常送信 | mail_sent | **mail_sent** |
| 4820 資料請求 正常送信 | mail_sent | **mail_sent** |

→ **4/4 PASS**（確認欄一致検証スニペット id=5 も正常動作）

## I. メール配信ルート（mailpit で目視）

| メール | 宛先（To） | 結果 |
|---|---|:---:|
| お問い合わせ 管理通知 | info@pegasus-job.com | ✅ |
| お問い合わせ 自動返信 | （入力アドレス） | ✅ |
| 説明会 管理通知 | **setsumei-kai@pegasus-job.com** | ✅ |
| 説明会 自動返信 | （入力アドレス） | ✅ |
| 資料請求 管理通知（リード） | info@pegasus-job.com | ✅ |
| 資料請求 自動返信（DL URL） | （入力アドレス）＋本文にトークンURL | ✅ |

→ **6/6 PASS**。**宛先取り違えなし**（お問い合わせ＝info／説明会＝setsumei-kai）。

## J. Flamingo 保存

- 送信前 inbound = 9 → 送信後 = 12（**+3＝mail_sent 3件分が保存**）。確認欄不一致（validation_failed）は保存されず（正しい挙動）。
→ **1/1 PASS**

## K. 資料請求トークン／PDF保護

| テスト | 期待 | 結果 |
|---|---|:---:|
| 自動返信のトークンURLでPDF取得 | 200 / application/pdf | **200 / application/pdf / 1,756,127バイト / 12頁** |
| PDF直アクセス（protected/） | 遮断 | **404** |
| 不正トークン | 410 | **410** |

→ **3/3 PASS**（メールを取得しなければPDFを取れない＝リード収集の目的を達成）

---

## 補足：環境状態（検証時点）

- コンテナ：db（4週間稼働）/ nginx / wordpress（7日）/ phpmyadmin / mailpit（healthy）。
- 有効プラグイン数：**20**。
- Code Snippets：id5（確認欄検証・active）/ id6（**テスト用SMTP mailpit・active＝本番では無効化必須**）/ id7（説明会mailto・active）/ id8（コアサイトマップ有効化・active）。
- mu-plugin：`pega-download-system.php` 配置済み。protected PDF 配置済み。
- republish保留：0。

---

## まとめ

- 全56項目 **PASS（FAIL 0）**。URL/CPT・リダイレクト8件・CF7フォーム3種・資料請求トークン/PDF保護・SEO単一出力・メール難読化のすべてがステージング上で正常動作。
- 検出された問題：**なし**。
- 本テストで確認した「本番でも再現する/再設定が必要な点」（SWELLのサイトマップ無効化、メール難読化の壊れ、Code Snippets id6 の本番無効化、nginx設定 restart など）は、`docs/PRODUCTION_CUTOVER_CHECKLIST.md` に集約済み。
</content>
</invoke>
