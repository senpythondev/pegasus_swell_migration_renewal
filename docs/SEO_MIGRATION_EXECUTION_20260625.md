# SEO移行 実行ログ：Yoast SEO → SEO SIMPLE PACK

**実施日:** 2026年6月26日（設計 `SEO_MIGRATION_DESIGN_20260625.md` 承認後）
**対象環境:** ステージングのみ。本番は無変更。
**承認方針:** メタ移行（キー対応表どおり）＋ サイトマップは**案A（WordPressコア `/wp-sitemap.xml` ＋ `/sitemap.xml`→301）**。
**結果:** 移行完了。Yoast SEO・XML Sitemap Generator を削除。Yoastメタは残置（ロールバック可）。

---

## 0. 結論サマリー

| 項目 | 結果 |
|---|---|
| メタ移行 | title 79 / description 101 / robots 8 / OGP画像 5 を SSP へ移行 |
| Yoast→SSP 突合 | タイトル・ディスクリプション**一致**、metaタグ**二重出力解消**（各1） |
| サイトマップ | 案A：コア `/wp-sitemap.xml`（全CPT含む）＋ `/sitemap.xml`→301→/wp-sitemap.xml |
| 削除 | **Yoast SEO ＋ XML Sitemap Generator**（2件）。プラグイン 27→25 |
| ロールバック | Yoastメタ（79件）残置、設定/プラグインのバックアップ取得済み |

---

## 段階1：SSP サイト全体設定

- 事前バックアップ: `backup/postmeta_options_pre_seo_20260626.sql.gz`（postmeta＋options）。
- SSPの既定はYoastとほぼ一致（区切り `|`、post/page/home テンプレ同等）。差分のみ調整:
  - **CPTタイトルテンプレ追加**（SSPは `{post_type}_title` 未設定だと単一投稿でタイトルが空になる）: `news_title` / `reports_title` / `works_title` = `%_page_title_% %_sep_% %_site_title_%`（＋ `*_desc` 空）。
  - **Twitterカード**: `summary` → `summary_large_image`（Yoastに合わせる）。
  - **home_desc**: Yoast `metadesc-home-wpseo`（「全国どこからでも利用可能…」）を SSP `home_desc` に設定（後の突合で発覚した差異を修正）。
  - **既定OG画像**: `og_image` を `…/uploads/2022/08/mv_sp.png` に設定（アイキャッチ無しページのOG画像欠落を補完）。

## 段階2：メタデータ移行（ドライラン→本適用）

- 変換スクリプト（Yoastメタは残置）。**ドライラン→本適用**の2段で実施。
- 変数変換: `%%title%%`→`%_page_title_%` / `%%sitename%%`→`%_site_title_%` / `%%sep%%`→`%_sep_%` / `%%sitedesc%%`→`%_tagline_%` / `%%page%%`→（削除）。
- **タイトルの重要補正:** Yoastは per-post `%%title%%` を「タイトルのみ（サイト名なし）」で出力していた。SSPで同じにするため **`%%title%%`のみ(61件)は `%_page_title_%` を明示設定**（空にすると「Title | Site」になり不一致になるため）。リテラル等18件はそのまま/変数変換。
- robots: 別メタ2つ→`ssp_meta_robots` 結合（**7件 `noindex,nofollow` / 1件 `nofollow`**）。
- OGP画像: `_yoast_wpseo_opengraph-image` → `ssp_meta_image`（5件）。
- 移行件数（postmeta）: `ssp_meta_title` 79 / `ssp_meta_description` 101 / `ssp_meta_robots` 8 / `ssp_meta_image` 5。Yoastメタ（79 title 等）は**残置**。

## 段階3：OGP個別5件（896/1156/1221/1244/1296）

- SSPは per-post の OGP「タイトル・説明」を持たず、メタタイトル/説明から自動生成。
- 調査の結果、**1221/1244/1296 の Yoast OGP題/説は post 1156 からの複製残骸（誤値）**だった。SSPはメタ（各投稿の正しい値）からOGPを作るため、**むしろ正しいOGPに修正される**。896は軽微差・1156は同一。
- → **個別対応（Code Snippet）は不要**。SSP挙動を採用（改善）。

## 段階4：Yoast無効化と突合（順序厳守）

1. Yoast稼働のまま、サンプル（blog20220820/blog20221111/about/home/news/reports/column）の `<title>`・description・og:image を記録。**現状は Yoast＋SSP 両有効で og:title/description が二重出力**を確認。
2. **Yoast SEO を無効化**（削除はまだ）。
3. SSP単独出力と記録を突合:
   - **タイトル: 全サンプル一致**（reports1 のみ生タイトルの二重空白差＝表示上のみ・SEO無影響）。
   - **ディスクリプション: 一致**（home は §段階1の home_desc 設定で一致）。
   - **og:title / description の二重出力: 解消（各1）**。
   - **og:image**: アイキャッチ/メタ画像ありは一致（reports1・896は同一画像の別サイズ）。アイキャッチ無しページ（about/news等）は **既定OG画像(mv_sp.png)で補完**（Yoastは本文画像にフォールバックしていた＝挙動差。全ページにOG画像がある状態に）。
   - **noindex**: 移行先 `ssp_meta_robots` をDBで検証（7=noindex,nofollow / 1=nofollow）。該当8件は確認/完了ページ（301転送）・下書き・privateで**非公開**のため、ライブHTMLでの目視は不可（DBレベルで結合変換の正確性を確認）。

## 段階5：サイトマップ（案A）

- **判明した障害（予期しない事態→対処）:**
  - SSP 3.7.0 に**サイトマップ機能なし**。`/sitemap.xml` は **google-sitemap-generator(GSG)** が提供していた。
  - **GSGがコアサイトマップを無効化**（`wp_sitemaps_enabled=false`）し、`/wp-sitemap.xml` も乗っ取って空出力していた。
  - さらに **SWELLテーマ自身も `wp_sitemaps_enabled` を `__return_false`** で無効化（`themes/swell/lib/hooks/remove.php`）。
- **対処:**
  1. **GSG（XML Sitemap Generator）を削除**（ファイル退避 `backup/plugin_google-sitemap-generator_20260626.tgz`）。
  2. **Code Snippets（id=8）でコアサイトマップを再有効化**: `add_filter('wp_sitemaps_enabled','__return_true',99)`（SWELL/Yoastの無効化を上書き）。
  3. `flush_rewrite_rules` でコアサイトマップのルートを登録。
  4. **Redirection に `/sitemap.xml` → `/wp-sitemap.xml`（301）追加**（Search Console登録URL保全。group=pegasus-renewal、計8件目）。
- **確認:** `/wp-sitemap.xml` がコア出力に復活し、**post120 / page18 / news38 / reports16 / works29** ＋ タクソノミー（category/news_category/reports_cat）＋ users を網羅。`/sitemap.xml` → 301 → /wp-sitemap.xml → 200。

## 段階6：最終確認とYoast削除

- **Yoast SEO を削除**（uninstallがYoastメタを消す恐れがあるため、**ファイル直接除去でメタ温存**。退避 `backup/plugin_wordpress-seo_20260626.tgz`）。`active_plugins` 不在・**Yoastメタ79件温存**を確認。
- **最終動作確認:**
  - サイト全9ページ 200。
  - `/about/`: **Yoast痕跡0・metaタグ重複なし**（title/description/og:title 各1）。SSP単独出力。
  - サイトマップ: `/wp-sitemap.xml` 200、`/sitemap.xml` 301→200。
  - リダイレクト8件・CPT（/news/…）200・CF7お問い合わせ送信 mail_sent（既存機能に影響なし）。
- プラグイン: 27 → **25**（Yoast SEO ＋ XML Sitemap Generator 削除）。

---

## 計画書からの変更点（要・ペガサス様報告）
- 計画書では「XMLサイトマップは **SSPの機能で代替**」としていたが、**SSP 3.7.0 にサイトマップ機能が無い**ことが判明。技術判断として **案A：WordPress標準 `/wp-sitemap.xml` を採用**し、`/sitemap.xml` → `/wp-sitemap.xml` を301転送（登録URL保全）。より確実な標準機能で、プラグイン依存も増やさない。

---

## 本番移行時の手順

1. **SSP全体設定**を本番にも適用: CPTタイトルテンプレ（news/reports/works）、tw_card=summary_large_image、home_desc（本番Yoastの `metadesc-home-wpseo`）、既定OG画像。
2. **メタ移行スクリプト**を本番DBで**ドライラン→本適用**（Yoastメタ残置）。robots結合・`%%title%%`→`%_page_title_%` 補正を含む。
3. **突合**: Yoast稼働で本番サンプルの title/desc/og を記録 → **Yoast無効化** → SSP出力と一致を確認。
4. **サイトマップ**:
   - 本番の **GSG（あれば）を削除**。
   - **コアサイトマップ再有効化スニペット**（`wp_sitemaps_enabled __return_true`）を本番にも追加（本番SWELLも無効化しているため必須）。
   - `flush`（パーマリンク保存）。`/sitemap.xml` → `/wp-sitemap.xml` の301をRedirectionに追加。
   - **Search Console に `/wp-sitemap.xml` を再登録**（旧 `/sitemap.xml` は301で保全）。
5. **Yoast削除**（メタ温存ならファイル直接除去、不要なら通常削除）。
6. **検証＋監視**: 主要ページのmeta（重複なし・noindex）、サイトマップ、カバレッジ／順位を一定期間 Search Console で監視。
7. メール送信系（mailpit）スニペットは本番では無効化（別件）。

---

## ロールバック
- Yoastメタ（`_yoast_wpseo_*`）は**全て残置**。Yoast 22.9 を再設置（`backup/plugin_wordpress-seo_20260626.tgz`）＋有効化で即復旧。
- SSP設定・メタは `backup/postmeta_options_pre_seo_20260626.sql.gz` で復元可。
- サイトマップ: GSG（`backup/plugin_google-sitemap-generator_20260626.tgz`）再設置で旧状態へ。

---

## まとめ
- Yoast管理のSEOメタ（title79/desc101/robots8/OGP画像5）を SEO SIMPLE PACK へ移行。突合でタイトル・説明一致、二重出力解消を確認。
- サイトマップは技術判断で **WordPress標準（案A）** を採用（SSPに機能が無いため）。`/sitemap.xml`→301で保全。
- **Yoast SEO ＋ XML Sitemap Generator を削除**。既存機能（CF7・リダイレクト・CPT）に影響なし。Yoastメタ残置でロールバック可。本番は無変更。
