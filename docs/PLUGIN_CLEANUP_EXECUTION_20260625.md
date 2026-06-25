# プラグイン整理 実行ログ（Phase 4 着手・安全分の削除）

**実施日:** 2026年6月25日
**対象環境:** ステージングのみ（Docker / `pegasus_wp`）。本番は無変更。
**承認:** ペガサス様（橋本様）より整理計画書に全面同意。今回は**最も安全なカテゴリC**の一部を実施。
**準拠:** `docs/PLUGIN_CLEANUP_PLAN.md`（カテゴリC）。カテゴリB（SWELL代替8件）・SEO移行は別途。

---

## 0. 結論サマリー

| 区分 | 件数 | 内容 |
|---|---:|---|
| **削除** | **7** | 停止中4件 ＋ 未使用(現役だが依存なし)3件 |
| **保留** | **7** | SEO移行待ち/フォーム移行待ち/依存あり/要方針確認/サーバ依存 |
| カテゴリC 合計 | 14 | 計画書MDのカテゴリC（C-1〜C-4）の総数 |

> **件数の補足（ご報告）:** ご依頼文では「未使用14件＋停止中4件＝18件」とありましたが、整理計画書（`PLUGIN_CLEANUP_PLAN.md`）のカテゴリCは**合計14件**（うち停止中4件を含む）です。本ログは計画書の14件を基準に、安全な7件を削除・残り7件を保留としました。

- 作業前バックアップ：`backup/full_db_pre_plugin_cleanup_20260625.sql.gz`（DB全体）／`backup/plugins_deleted_20260625.tgz`（削除対象プラグインのファイル）。いずれもgitignore対象。
- 削除は**1件ずつ実施し、各削除後にサイト主要ページ200を確認**。

---

## 1. 削除したプラグイン（7件）

### 停止中4件（C-4・最も安全）
| プラグイン | スラッグ | 結果 |
|---|---|---|
| Block Visibility | block-visibility | ✅ 削除 |
| Download After Email | download-after-email | ✅ 削除（※下記の特記） |
| Font Awesome | font-awesome | ✅ 削除（SWELL内蔵で代替済み） |
| TS Webfonts for SAKURA RS | ts-webfonts-for-sakura | ✅ 削除 |

### 未使用・現役だが依存なし 3件（C-2）
| プラグイン | スラッグ | 削除理由・確認 |
|---|---|---|
| WebSub (旧 PubSubHubbub) | pubsubhubbub | 歴史的役割。フロント依存なし。削除 |
| Loco Translate | loco-translate | 翻訳は .mo として残存（プラグイン非依存）。削除 |
| Public Post Preview | public-post-preview | `_ppp_public_preview` メタ **0件**＝公開プレビューURLの利用実態なしを確認。削除 |

### ⚠️ Download After Email の特記（予期しない事態と対処）
- WordPress標準の `delete_plugins()` 実行時、DAEの**アンインストールフックで Fatal error**（`dae_uninstall` 関数が未ロード＝無効プラグインのため）。CLI削除プロセスは落ちたが、**ライブサイトは終始200で無事**。
- 対処：壊れたuninstallフックを回避し、**プラグインのファイルを直接除去**（root, `rm -rf`）。`active_plugins` にも不在を確認。
- 残置（無害なクラフト）：`dae_download` 投稿566・dae系オプション・`dae-uploads/` のPDFは残存。新しい資料請求システムは `protected/` のコピーPDFを使うため**完全独立**（削除後も資料請求フロー＝送信→自動返信URL→PDF取得200 を確認済み）。残置分はDB整理時に別途撤去可。

---

## 2. 保留したプラグイン（7件）と理由

| プラグイン | スラッグ | 保留理由・適切な削除Phase |
|---|---|---|
| **Yoast SEO** | wordpress-seo | **SEO移行（→SEO SIMPLE PACK）完了まで保持**。メタデータのエクスポート元。※ご指示の必須保持 |
| **MW WP Form** | mw-wp-form | **フォーム移行が本番で安定するまで保持**（現在ステージングでCF7と並存）。※ご指示の必須保持 |
| XML Sitemap Generator | google-sitemap-generator | SEO移行セット。SEO SIMPLE PACKのサイトマップがSearch Console登録後に削除（Yoastと同時期） |
| Yoast Duplicate Post | duplicate-post | **Rewrite & Republish の保留中ドラフトが1件あり**（`dp-rewrite-republish` 状態）。先にこの保留を解消（公開 or 破棄）してから削除すべきため保留 |
| WP-Optimize | wp-optimize | 計画書で「要方針確認」。キャッシュ/最適化戦略（サーバ側設定で代替するか）を確定後に判断 |
| WP Mail Logging | wp-mail-logging | 計画書で「本番切替後に削除」。現在ステージングのメール検証で有用なため、本番安定後に削除 |
| SAKURA RS WP SSL | sakura-rs-wp-ssl | C-3サーバ依存。本番サーバ（さくら継続 or 移転）の決定後に継続/削除を判断 |

---

## 3. 動作確認結果（削除後）

| 確認項目 | 結果 |
|---|---|
| サイト全ページ200 | ✅ /・/contact/・/application/・/download/・/homeworker/・/news/・/reports/・/column/・/about/ |
| リダイレクト7件 | ✅ すべて301（タイポ修正3＋確認/完了4） |
| CPT個別記事 | ✅ /news/program202404/・/reports/report1/・/column/20180105/ = 200 |
| CF7 お問い合わせ(4803) | ✅ mail_sent（宛先 info@ 維持） |
| CF7 資料請求(4820) ＋ DL | ✅ 送信→自動返信URL→**PDF取得 200/application/pdf**（DAE削除後も独立動作） |
| 各削除直後のサイト | ✅ 1件ずつ削除し、毎回主要ページ200を確認 |
| レイアウト崩れ | なし（全ページ200・主要導線正常） |
| 保留7件の稼働 | ✅ すべて有効を維持 |

- 依存関係：今回フォーム移行で導入した **CF7 / Flamingo / Code Snippets / Redirection / mu-plugin（資料請求）** は削除対象に含まれず、影響なし。
- プラグイン総数：34 → **27**（削除7）。残り27件はすべて有効。

---

## 4. 残るPhase 4 作業

| 区分 | 内容 | 状況 |
|---|---|---|
| カテゴリB（SWELL代替8件） | Gutentor / Useful Blocks / Easy Accordion / To Top / WordPress Popular Posts / Templateberg / Crop Thumbnails / Regenerate Thumbnails | 未着手。SWELLブロックへの置換を伴うため段階的に（コンテンツの表示崩れに注意） |
| SEO移行 | Yoast SEO → SEO SIMPLE PACK（メタデータ移行）→ Yoast・XML Sitemap 削除 | 未着手。メタデータ移行を慎重に |
| 保留Cの解消 | Duplicate Post（republish保留の解消後）／WP-Optimize（方針確認後）／WP Mail Logging（本番後）／SAKURA SSL（サーバ決定後） | 各条件確定後に削除 |

---

## 5. 本番移行時の留意
- 本番でも同じ7件を削除する際、**Download After Email は同じuninstall不具合が出る可能性**があるため、ファイル直接除去（またはバックアップのうえ手動削除）で対応。
- 削除は本番でも**1件ずつ＋都度確認**を推奨。

---

## まとめ
- 停止中4件＋未使用3件＝**7件を安全に削除**、サイトは全項目正常。
- **Yoast SEO・MW WP Form を含む7件は理由を付して保留**（SEO移行・フォーム本番安定・依存解消・方針確認・サーバ決定の各条件待ち）。
- DAEのuninstall不具合はファイル直接除去で回避、資料請求システムは独立動作を確認。本番は無変更。
