# SEO移行 設計書：Yoast SEO → SEO SIMPLE PACK

**作成日:** 2026年6月25日
**対象環境:** ステージング（調査）。本番は無変更。
**性質:** **調査・設計のみ。Yoast/SSPの設定もメタデータも一切変更していません（読み取りのみ）。**
**目的:** Yoast SEO が管理するメタデータ（タイトル/ディスクリプション/OGP/robots）を SEO SIMPLE PACK(SSP 3.7.0) へ移行し、Yoast SEO・XML Sitemap Generator を削除する。

---

## 0. 結論サマリー

| 項目 | 内容 |
|---|---|
| 実質的な移行対象 | **110投稿**（title/desc/robots/OGP画像のいずれかに値を持つ）|
| 主要メタ | メタタイトル 79（うち63は `%%title%%` のみ＝実質未カスタム）／ディスクリプション 101／robots 8投稿／OGP画像 5 |
| 移行が難しい項目（ギャップ） | ① OGP/Twitterの**個別タイトル・説明**（5投稿）②**プライマリカテゴリー**（100投稿）③**XMLサイトマップ**（SSPに機能なし）|
| ⚠️ 計画書との相違 | 計画書は「XMLサイトマップは**SSPの機能で代替**」としていたが、**SSP 3.7.0 にサイトマップ機能は無い**。代替方針の見直しが必要（§6）|

---

## 1. Yoast が管理しているメタデータの規模

`_yoast_wpseo_*` postmeta のキー別件数（非空＝実値あり）:

### 移行対象（SEO出力に関わる）
| Yoastメタキー | 非空件数 | 内容 |
|---|---:|---|
| `_yoast_wpseo_metadesc` | 101 | メタディスクリプション |
| `_yoast_wpseo_title` | 79 | メタタイトル（うち63は `%%title%%` のみ＝投稿タイトルそのまま、16がリテラルなカスタム）|
| `_yoast_wpseo_meta-robots-nofollow` | 8 | nofollow（値=1）|
| `_yoast_wpseo_meta-robots-noindex` | 7 | noindex（値=1）|
| `_yoast_wpseo_opengraph-image` (+image-id) | 5 | OGP画像 |
| `_yoast_wpseo_opengraph-title` | 5 | OGP個別タイトル（※ギャップ）|
| `_yoast_wpseo_opengraph-description` | 5 | OGP個別説明（※ギャップ）|
| `_yoast_wpseo_twitter-title/description` | 5/5 | Twitter個別（※ギャップ）|
| `_yoast_wpseo_twitter-image` (+id) | 4 | Twitter画像（OGP画像と共用で吸収可）|
| `_yoast_wpseo_canonical` | **0** | 個別canonicalは無し（移行不要）|

### Yoast内部データ（SEO出力に出ない＝移行不要）
| キー | 件数 | 理由 |
|---|---:|---|
| `_yoast_wpseo_estimated-reading-time-minutes` | 167 | 読了時間（内部）|
| `_yoast_wpseo_content_score` | 159 | 読みやすさスコア（内部）|
| `_yoast_wpseo_primary_category` ほか | 100/36/9 | プライマリカテゴリー（※パンくず用・ギャップ）|
| `_yoast_wpseo_focuskw` / `_linkdex` | 25/25 | キーワード分析（内部）|
| `_yoast_wpseo_wordproof_timestamp` | 167（全空）| 未使用 |

### 投稿タイプ別（実値ありの投稿数）
post(publish) 91 / news 36 / page 16 / works 12 / post(draft) 7 / page(draft) 3 / post(private) 1 / post(republish) 1。

---

## 2. サイト全体設定

| 設定 | Yoast | SSP（現状デフォルト） | 対応 |
|---|---|---|---|
| 区切り文字 | `sc-pipe`（`|`）| `line`（`|`）| **一致**（変更不要）|
| タイトル(投稿) | `%%title%% %%page%% %%sep%% %%sitename%%` | `%_page_title_% %_sep_% %_site_title_%` | ほぼ同等（`%%page%%`欠落のみ・§5-3）|
| タイトル(固定/CPT) | 同上（title-page/news/reports/works すべて同形）| post_type別に設定 | SSPでnews/reports/worksのテンプレを同形に設定 |
| トップ | `%%sitename%% %%page%% %%sep%% %%sitedesc%%` | `%_site_title_% %_sep_% %_tagline_%` | 同等 |
| ディスクリプションのテンプレ | 各 `metadesc-*` は空 | 空 | 一致 |
| OGP | 有効（`opengraph:1`）/ デフォルト画像なし / 前面OG題説なし | SSPで有効化 | 設定移行 |
| Twitter | 有効 / カード=`summary_large_image` / site未設定 | SSPで設定 | カード種別を large image に |
| XMLサイトマップ | **google-sitemap-generator** が `/sitemap.xml` を提供（Yoast自身のsitemap_index.xmlは404）| **SSPに機能なし** | §6 別途方針 |

---

## 3. Yoast → SEO SIMPLE PACK メタキー対応表

SSPのper-postメタ（`class/metabox.php` POST_META_KEYS）:

| 項目 | Yoast postmeta | → SSP postmeta | 変換 |
|---|---|---|---|
| メタタイトル | `_yoast_wpseo_title` | `ssp_meta_title` | 変数変換（§5-1）。`%%title%%`のみは空にしてSSP既定に委ねる |
| ディスクリプション | `_yoast_wpseo_metadesc` | `ssp_meta_description` | 変数変換（該当1件） |
| robots | `_yoast_wpseo_meta-robots-noindex` ＋ `-nofollow` | `ssp_meta_robots` | **2メタを1値に結合**（§5-2）|
| canonical | `_yoast_wpseo_canonical` | `ssp_meta_canonical` | 個別0件＝移行なし |
| OGP/SNS画像 | `_yoast_wpseo_opengraph-image` | `ssp_meta_image` | URLをそのまま |
| キーワード(任意) | `_yoast_wpseo_focuskw` | `ssp_meta_keyword` | 内部用・移行任意（SEO出力なし）|
| OGP個別タイトル/説明 | `_yoast_wpseo_opengraph-title/description` | **対応なし** | §4 ギャップ |
| Twitter個別タイトル/説明 | `_yoast_wpseo_twitter-title/description` | **対応なし** | §4 ギャップ |

---

## 4. 移行が難しい項目（ギャップ）と対処

1. **OGP/Twitterの個別タイトル・説明（5投稿: 896, 1156, 1221, 1244, 1296）**
   - SSPのper-postには **OGP画像（ssp_meta_image）しかない**。OGP/Twitterの個別「タイトル・説明」を投稿ごとに上書きする項目が無い。
   - SSPの挙動: OGP/Twitterのタイトル・説明は **メタタイトル・メタディスクリプションから自動生成**。
   - 対処: これら5投稿は、メタタイトル/説明がOGPにも使われる前提で許容（多くは同等）。**OGP独自文言を厳密に保ちたい場合のみ、5件を手動確認**。一覧は §7 に記録。

2. **プライマリカテゴリー（100投稿）**
   - 複数カテゴリ投稿で「主カテゴリ」を決めるYoast機能。SSPに同等なし。
   - 影響: パンくず表示のカテゴリ選択のみ（URLは `/%postname%/` で不変、title/descにも非依存）。**SEO実害は軽微**。SWELLのパンくず設定側で対応を検討。

3. **XMLサイトマップ（重要・§6）**

4. **`%%page%%`（ページ番号）**: SSP既定テンプレに無い。ページネーション時のタイトルに「2/3」等が出なくなるのみ。軽微。

---

## 5. 変換ロジック設計

### 5-1. タイトル/ディスクリプションの変数変換
| Yoast変数 | → SSPプレースホルダ |
|---|---|
| `%%title%%` | `%_page_title_%` |
| `%%sitename%%` | `%_site_title_%` |
| `%%sep%%` | `%_sep_%` |
| `%%sitedesc%%` | `%_tagline_%` |
| `%%page%%` | （削除）|

- **`ssp_meta_title` に入れる値の判定**:
  - Yoast値が **`%%title%%` だけ**（63件）→ カスタムではないので **`ssp_meta_title` は設定しない（空）**。SSPの既定テンプレ（`%_page_title_% %_sep_% %_site_title_%`）が同じ結果を出す。
  - それ以外（リテラル16件＋変数混在）→ 変数変換後の文字列を `ssp_meta_title` に保存。
- ディスクリプションは変数を含むのが1件のみ。基本そのままコピー＋変数変換。

### 5-2. robots の結合（2メタ → 1値）
| Yoast noindex | Yoast nofollow | → `ssp_meta_robots` |
|:---:|:---:|---|
| 1 | 1 | `noindex,nofollow` |
| 1 | （なし）| `noindex` |
| （なし）| 1 | `nofollow` |
| 2（index上書き）| – | `index,follow` |
| なし | なし | （設定しない＝既定）|

実データ: **7投稿が `noindex,nofollow`**（544,546,559,560,2271,2856,3204＝確認/完了ページ・作業用下書き）、**1投稿が `nofollow`**（3749＝private）。noindexのみ/index上書きは0。

### 5-3. 移行スクリプト（PHP・設計／実行は承認後）
```
対象投稿(_yoast_wpseo_title or metadesc or robots or opengraph-image を持つ)をループ:
  1) title:  $t = yoast_title;
     if ($t === '%%title%%') { /* 何もしない＝SSP既定 */ }
     else { $t = strtr($t, [変数表]); update_post_meta($id,'ssp_meta_title',$t); }
  2) desc:   update_post_meta($id,'ssp_meta_description', strtr($yoast_desc,[変数表]));
  3) robots: $ni=get noindex; $nf=get nofollow;
     $r = ($ni==1 && $nf==1)?'noindex,nofollow':(($ni==1)?'noindex':(($nf==1)?'nofollow':''));
     if ($r) update_post_meta($id,'ssp_meta_robots',$r);
  4) image:  if (yoast og-image) update_post_meta($id,'ssp_meta_image', $yoast_og_image);
  ※ Yoastメタは消さず残す（ロールバック容易）。SSP優先出力はYoast無効化で担保。
```
- ドライラン（件数と変換後値のプレビュー）→ 本適用、の2段で実施。

---

## 6. XMLサイトマップの方針（計画書の見直し）

- **判明事項:** SSP 3.7.0 に**XMLサイトマップ機能は無い**。現在 `/sitemap.xml` は **google-sitemap-generator** が提供（Yoastのsitemap_index.xmlは404）。**WPコアの `/wp-sitemap.xml` は200で利用可能**。
- 計画書の「XMLサイトマップは SSP の機能で代替」は**成立しない**。代替案:
  - **案A（推奨）: WordPress コア標準サイトマップ `/wp-sitemap.xml` を採用**（プラグイン不要・既に200）。google-sitemap-generator を削除し、**`/sitemap.xml` → `/wp-sitemap.xml` を301転送**（Search Console登録済みURLを保全）＋ Search Console に `/wp-sitemap.xml` を再登録。
  - 案B: google-sitemap-generator を**削除せず継続**（サイトマップ目的のみ保持）。※プラグイン削減方針に反する。
  - 案C: 別のサイトマップ専用プラグイン（XML Sitemaps 等）。新規依存増。
- **要ご判断**。推奨は案A（コア標準＋301転送）。

---

## 7. 推奨する移行手順

1. **（調査）** 完了（本書）。
2. **（SSPサイト全体設定）** 区切り（一致・変更不要）、投稿/固定/news/reports/works のタイトルテンプレを `%_page_title_% %_sep_% %_site_title_%` に、トップを `%_site_title_% %_sep_% %_tagline_%` に。OGP有効・Twitterカード=large image・（必要ならデフォOG画像）。
3. **（メタ変換）** §5-3 のスクリプトをドライラン→本適用（Yoastメタは残置）。
4. **（検証・Yoast稼働のまま）** 主要ページ（トップ/固定/news/reports/column/各サンプル）の現在の `<title>`・`meta description`・`og:image`・`robots` を**Yoast出力として記録**。
   - ※ **YoastとSSPを同時に有効化するとメタタグが二重出力**になるため、検証は「記録→Yoast無効化→SSP出力と突合」の順で行う。
5. **（Yoast無効化）** Yoast SEO ＋ XML Sitemap Generator を**無効化**（削除はまだ）。
6. **（SSP出力の確認）** 同じページでSSP出力が記録と一致するか（タイトル/説明/robots/og:image）。noindexページ（確認/完了4・下書き等）に noindex が出ているか。サイトマップ（§6案A）の疎通・Search Console再登録。
7. **（削除）** 問題なければ Yoast SEO ＋ XML Sitemap Generator を**削除**。`/sitemap.xml`→`/wp-sitemap.xml` の301をRedirectionに追加。
8. **（事後監視）** Search Console でカバレッジ／インデックス、主要KWの順位を一定期間モニタ。

OGP個別5件（896,1156,1221,1244,1296）は手順4〜6で個別に見え方を確認。

---

## 8. 移行時の注意点・リスク

| # | 項目 | リスク | 対策 |
|---|---|---|---|
| 1 | 二重出力 | Yoast＋SSP同時有効でmeta重複 | 移行→**Yoast無効化**してからSSP検証（手順厳守）|
| 2 | サイトマップ | SSPに機能なし | §6案A（コア`/wp-sitemap.xml`＋301）|
| 3 | OGP/Twitter個別 | 5投稿の独自文言が消える | §4-1。許容 or 5件手動確認 |
| 4 | プライマリカテゴリ | パンくずカテゴリが変わる可能性 | 軽微。SWELLパンくず側で調整 |
| 5 | robots誤適用 | 結合ミスでindex/noindex取り違え | §5-2の表に厳密準拠、検証で noindexページを確認 |
| 6 | テンプレ差異 | `%%page%%` 欠落 | 軽微（ページ番号のみ）。必要ならSSP側で付与 |
| 7 | ロールバック | 移行後の出戻り | **Yoastメタは消さず残置**。Yoast再有効化で即復旧可。事前にDB全体バックアップ |
| 8 | 本番反映 | 順位影響 | 本番は段階反映＋Search Console監視。サイトマップURL再登録を忘れない |

---

## 9. 確定仕様・計画書との対応
- 「Yoast → SEO SIMPLE PACK 移行 → Yoast/XML Sitemap 削除」: §3・§7 で全面設計。
- **計画書との相違（要報告）:** 「XMLサイトマップは SSP 機能で代替」は **SSPに機能が無く不成立**。→ §6 案A（WPコア `/wp-sitemap.xml` ＋ `/sitemap.xml` 301転送）を推奨。勝手に変更せず、方針のご確認をお願いします。
- 実装は本設計のご承認後に着手（本書は調査・設計のみ・無変更）。
