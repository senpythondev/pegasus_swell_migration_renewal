# 本番移行（カットオーバー）チェックリスト

**作成日:** 2026年6月26日
**対象:** pegasus-job.com（一般社団法人ペガサス）TCD NANO → SWELL リニューアルの本番反映
**前提:** ステージング（Docker / `pegasus_wp`）で全工程を実装・検証済み（`docs/INTEGRATION_TEST_20260625.md` 全56項目 PASS）。
**性質:** 本書は**本番作業時の手順書**。各項目は実行順に並べ、チェックボックスで1つずつ確認できるようにしている。各 `[ ]` を本番作業時に `[x]` にしていく運用。

> ⚠️ 本書は各タスクの実行ログ（`docs/*_20260623〜25.md`）から「本番移行に関する記述」を集約したもの。詳細は各レポートを参照。
> ⚠️ メールアドレスはマスク（`info@[managed-domain]` / `setsumei-kai@[managed-domain]` = pegasus-job.com 系）。実値は各設定・スニペットに格納。

---

## ★ 最重要：本番でも必ず再発／再設定が必要な落とし穴（先に頭に入れる）

| # | 落とし穴 | 対応 | 根拠レポート |
|---|---|---|---|
| 1 | **SWELLがコアXMLサイトマップを無効化**（`wp_sitemaps_enabled __return_false`）。SSPにはサイトマップ機能が無い | コア再有効化スニペット（id=8相当）を本番にも入れる＋flush | SEO §5,§本番手順 |
| 2 | **/application/ のメール難読化JSがSWELL移行で壊れる**（script剥がれ＋エンティティ符号化でテキスト表示） | ショートコード `[pega_setsumei_mail]`（antispambot）方式を本番にも適用 | MAIL_OBFUSCATION §3,§8 |
| 3 | **Code Snippets id=6「テスト用SMTP(mailpit)」は本番で無効化必須** | 本番は実SMTP/サーバMTA。id=6 を本番に持ち込まない/無効化 | FORM §F、DOWNLOAD §5 |
| 4 | **nginx設定（単一ファイルbind-mount）は `reload` では反映されない** | 設定変更後 `docker compose restart nginx`（本番がnginxの場合）。さくらApacheなら `.htaccess` | DOWNLOAD §段階1 |
| 5 | **works の rewrite slug は `column`**（`/works/` ではない。本番が `/column/`） | CPT再構築時 works=column を厳守（URL資産保全） | CPT §2 |
| 6 | **CF7フォームIDは環境ごとに異なる** | 本番フォーム作成後、`pega_dl_form_id` と各ページのショートコードIDを本番値に更新 | DOWNLOAD §本番手順4,7 |
| 7 | **プラグイン削除は本番でも1件ずつ＋都度確認**（DAEはuninstall不具合でファイル直接除去） | 段階削除・都度200確認 | PLUGIN各ログ |

---

## フェーズ0：事前準備・バックアップ

- [ ] 本番の**完全バックアップ**（DB＋wp-content＋uploads）を取得（UpdraftPlus等）。ロールバック起点を確保。
- [ ] 移行作業はメンテナンス時間帯に実施。必要なら一時的にメンテナンスモード。
- [ ] 本番の現行URL一覧・主要ページのHTTP状態を作業前にスナップショット（差分比較用）。
- [ ] ステージングの各バックアップ／成果物（`scripts/` 配下・本 `docs/`）が手元にあることを確認。

---

## フェーズ1：基盤（権限・テーマ・必須プラグイン）

### 1-1. ファイル権限 / FS_METHOD
- [ ] `wp-content`（plugins/themes/uploads/mu-plugins）の所有者・パーミッションがWeb実行ユーザーで**書き込み可能**であること。
- [ ] 自動更新・プラグイン操作でFTP資格情報を要求されないよう、必要に応じ `wp-config.php` に `define('FS_METHOD','direct');`（サーバ環境に合わせ判断）。
- [ ] uploads 配下の書き込み権限（メディア・トークンDB作成・PDF配置に影響）を確認。

### 1-2. SWELLテーマ
- [ ] **ペガサス様購入後**の SWELL 本体＋子テーマ（`swell_child`）を本番に導入。
- [ ] SWELL を正規ライセンスでアクティベート（ユーザー認証）。子テーマを有効化。
- [ ] 外観の主要導線（ヘッダー/フッター/メニュー/トップ）が崩れないことを確認。

### 1-3. 必須プラグイン導入
- [ ] 移行で導入した必須プラグインを本番にも導入・有効化：
  - [ ] **Contact Form 7**（フォーム）
  - [ ] **Flamingo**（送信データ保存・サードパーティ、Git非管理）
  - [ ] **Code Snippets**（スニペット基盤）
  - [ ] **Redirection**（301転送）
  - [ ] **SEO SIMPLE PACK**（SEOメタ）
  - [ ] （CPT定義に **Custom Post Type UI** を使う場合）CPTUI
- [ ] バージョンはステージングと揃える（互換性確保）。

### 1-4. siteurl / home（サイトURL）
- [ ] DB `wp_options` の **`siteurl` / `home` を本番URL `https://pegasus-job.com` に設定**（ステージングのIP:8080から変更）。`wp-config.php` で `WP_SITEURL`/`WP_HOME` を定義する方法でも可。
- [ ] 本番が標準ポート（443/80）単独なら、ステージングで発生した「nginx のポート欠落による wp-admin 404」（`SITEURL_FIX_20260626.md`）は起きない。ただし**リバースプロキシ/コンテナ/非標準ポート構成の場合は `absolute_redirect off`（または `port_in_redirect`/プロキシヘッダ）で対処**。
- [ ] HTTPS強制（`FORCE_SSL_ADMIN` 等）と整合を確認。混在コンテンツ（http→https）がないか確認。

---

## フェーズ2：CPT再構築（news / reports / works ＋ タクソノミー）

- [ ] CPT定義を本番に適用（`scripts/cpt_definitions/` のJSON/スクリプト、またはCPTUIインポート）：
  - [ ] `news`（rewrite=`news`, タクソノミー `news_category`）
  - [ ] `reports`（rewrite=`reports`, タクソノミー `reports_cat`）
  - [ ] `works`（rewrite=**`column`** ← `/works/` ではない。**最重要#5**）
  - [ ] タクソノミー `news_category`（hierarchical）/ `reports_cat`（hierarchical）
- [ ] パーマリンク保存（`flush_rewrite_rules`）でルート再生成。
- [ ] **確認:** /news/・/reports/・**/column/** アーカイブ 200／各CPT個別記事 200／`/news_category/event/`・`/reports_cat/it/` 200／`/works/` は本番同様404。
- [ ] news_category タイポ（term slug `infomation`→`information`）が本番でも必要なら適用（受け皿整備後）。

> 注：本番DBをSWELL用に移行した時点でCPT定義が失われていれば同様に404になる。投稿データは不変。

---

## フェーズ3：フォーム移行（CF7・メール・Flamingo）

### 3-1. CF7フォーム作成
- [ ] 本番にCF7フォームを作成（エクスポート/インポート or 新規）：
  - [ ] お問い合わせ（宛先 **info@[managed-domain]**、確認欄あり、自動返信あり）
  - [ ] 個別説明会申込（venue必須、宛先 **setsumei-kai@[managed-domain]**、自動返信あり）
  - [ ] 資料請求（email＋同意、hidden resource_id、Mail2に `{{DOWNLOAD_URL}}`）
- [ ] **宛先取り違えチェック:** お問い合わせ=info／説明会=setsumei-kai（**逆転なし**）。From=サイトドメイン／Reply-To=入力アドレス。

### 3-2. 確認欄一致検証スニペット
- [ ] Code Snippets「CF7 メールアドレス確認欄の一致検証」（id=5相当）を本番にも作成・有効化。

### 3-3. メール送信（mailpit → 本番SMTP）
- [ ] **Code Snippets id=6「テスト用SMTP(mailpit)」は本番に入れない／無効化（最重要#3）。**
- [ ] 本番は実SMTP/サーバMTAで送信。SPF/DKIM/送信ドメイン認証を確認。
- [ ] 本番で実際にテスト送信し、**管理通知・自動返信が届く**ことを確認（迷惑メール判定も）。

### 3-4. ページへの配置（ショートコード差替）
- [ ] /contact/ のフォームを本番CF7 IDに差替（旧 MW WP Form ショートコードを置換、説明文等は不変）。
- [ ] /application/ のフォームを本番CF7 IDに差替。
- [ ] **Flamingo** に送信データが保存されることを確認。

### 3-5. 確認/完了ページの301（フェーズ5のRedirectionに含む）
- [ ] /contact/contact-confirm/・/contact/contact-thanks/・/application/confirm/・/application/thanks/ の4件を301（→ フェーズ5）。

---

## フェーズ4：メール難読化修正（/application/）★最重要#2

- [ ] 本番DBをSWELL移行後、`/application/` に難読化JSが**テキスト表示されていないか**確認（`function converter` / `&lt;!--` の有無）。
- [ ] Code Snippets ショートコード「メールリンク(個別説明会・antispambot)」（id=7相当・`[pega_setsumei_mail]`）を本番にも作成。宛先 `setsumei-kai@[managed-domain]`、件名「個別説明会希望」。
- [ ] 本番 /application/ 本文の壊れた `wp:html` ブロックを `<!-- wp:shortcode -->[pega_setsumei_mail]<!-- /wp:shortcode -->` に置換。
- [ ] **確認:** 難読化テキスト非表示／mailtoリンク動作／CF7（venue）表示／ページ200。
- [ ] 移行時点のDBで `function converter` 等を**全ページ再走査**（本調査時点では /application/ の1ページのみ）。

---

## フェーズ5：URLリダイレクト（Redirection 8件）

- [ ] Redirection にグループ `pegasus-renewal` を作成し、8件を301登録（`Red_Item::create` or 管理画面/CSV）：

| # | 旧URL | → 転送先 | 種別 |
|---|---|---|---|
| 1 | /user-dicision1/ | /user-decision1/ | タイポ修正 |
| 2 | /category/user-dicision/ | /category/user-decision/ | タイポ修正 |
| 3 | /news_category/infomation/ | /news_category/information/ | タイポ修正 |
| 4 | /contact/contact-confirm/ | /contact/ | フォーム確認画面 |
| 5 | /contact/contact-thanks/ | /contact/ | フォーム完了画面 |
| 6 | /application/confirm/ | /application/ | フォーム確認画面 |
| 7 | /application/thanks/ | /application/ | フォーム完了画面 |
| 8 | /sitemap.xml | /wp-sitemap.xml | サイトマップ（フェーズ6） |

- [ ] タイポ修正は対の**スラッグ変更・カテゴリ再紐付け**（投稿432等）も実施し、転送先が200になることを確認。
- [ ] **確認:** 8件すべて301＋正しいLocation。正常URL（例 /user-decision2/）は200のまま（誤転送なし）。
- [ ] 注：本番がApache（さくら）の場合、`.htaccess` ではなく **Redirection（PHPレイヤー）** で動く点は同じ。

---

## フェーズ6：SEO移行（Yoast → SEO SIMPLE PACK ＋ サイトマップ）

### 6-1. SSP全体設定
- [ ] 本番SSPに設定を適用：CPTタイトルテンプレ（`news_title`/`reports_title`/`works_title` = `%_page_title_% %_sep_% %_site_title_%`）、Twitterカード=`summary_large_image`、`home_desc`（本番Yoastの `metadesc-home-wpseo`）、既定OG画像（`…/uploads/2022/08/mv_sp.png`）。

### 6-2. メタ移行
- [ ] メタ移行スクリプトを**本番DBでドライラン→本適用**（Yoastメタは残置＝ロールバック可）。
  - 変数変換：`%%title%%`→`%_page_title_%` / `%%sitename%%`→`%_site_title_%` / `%%sep%%`→`%_sep_%` / `%%sitedesc%%`→`%_tagline_%`。
  - **`%%title%%` のみの投稿は `%_page_title_%` を明示設定**（空にすると「Title | Site」化して不一致）。
  - robots：別メタ2つ→`ssp_meta_robots` 結合（noindex,nofollow 等）。OGP画像：`_yoast_wpseo_opengraph-image`→`ssp_meta_image`。

### 6-3. 突合（順序厳守）
- [ ] Yoast稼働のまま本番サンプルの `<title>`/description/og を記録。
- [ ] **Yoast を無効化**（削除はまだ）。
- [ ] SSP単独出力と突合：タイトル・ディスクリプション一致、og二重出力解消、og:image補完を確認。

### 6-4. サイトマップ（案A：コア `/wp-sitemap.xml`）★最重要#1
- [ ] 本番の **XML Sitemap Generator（GSG）があれば削除**。
- [ ] **コアサイトマップ再有効化スニペット**（id=8相当 `add_filter('wp_sitemaps_enabled','__return_true',99)`）を本番に追加（**本番SWELLも無効化しているため必須**）。
- [ ] パーマリンク保存（flush）。
- [ ] Redirection に `/sitemap.xml` → `/wp-sitemap.xml`（301）を追加（フェーズ5の#8）。
- [ ] **確認:** `/wp-sitemap.xml` 200（全CPT/タクソノミー網羅）／`/sitemap.xml` 301。
- [ ] **Search Console に `/wp-sitemap.xml` を再登録**（旧 `/sitemap.xml` は301で保全）。

### 6-5. Yoast削除
- [ ] **Yoast SEO を削除**（メタ温存ならファイル直接除去、不要なら通常削除）。`active_plugins` 不在・Yoastメタ温存を確認。
- [ ] **確認:** 主要ページのmeta重複なし・Yoast痕跡0・SSP単独出力・noindex該当ページの robots。

---

## フェーズ7：資料請求システム（mu-plugin・PDF保護・トークン）

- [ ] **mu-plugin 配置:** `scripts/download_system/pega-download-system.php` を本番 `wp-content/mu-plugins/` に配置（自動有効化、init でトークンテーブル自動作成）。
- [ ] **PDF配置:** 本番 `wp-content/uploads/protected/pegasus-shiryou.pdf` を配置。`pega_dl_resources()` のパス/表示名を確認。
- [ ] **PDF直アクセス遮断（本番Webサーバに合わせる）★最重要#4:**
  - [ ] 本番が **Apache（さくら）** なら `wp-content/uploads/protected/.htaccess` に `Require all denied`（または `Deny from all`）。
  - [ ] 本番が **nginx** なら `location ^~ /wp-content/uploads/protected/ { deny all; return 404; }` を追加し、**`docker compose restart nginx`**（reloadでは反映されない場合あり）。
  - [ ] 反映後、protected/PDF 直アクセスが 403/404 になることを確認。
- [ ] **フォームID設定★最重要#6:** `update_option('pega_dl_form_id', 本番の資料請求フォームID)`。
- [ ] **rewrite flush:** 「設定→パーマリンク」を保存し `/download-secure/` を有効化。
- [ ] **/download/ ページ:** 旧 `[download_after_email id="566"]` を本番CF7資料請求フォームのショートコードに差替、郵送導線（→/contact/）は維持。
- [ ] **メール送信（最重要#3）:** mailpitスニペット（id=6）は本番無効化。実SMTPで自動返信（DL URL）・管理通知（リード）が届くことを確認。
- [ ] **確認（一連）:** 資料請求送信→自動返信→トークンURL→PDF取得200／PDF直アクセス遮断／トークン期限7日・回数5・不正/超過410。

---

## フェーズ8：プラグイン整理（本番でも段階削除）★最重要#7

- [ ] ステージングで削除した**計14件**を本番でも、**1件ずつ＋都度サイト200確認**で削除：
  - 停止中4：Block Visibility / Download After Email / Font Awesome / TS Webfonts for SAKURA RS
  - 未使用3：WebSub(PubSubHubbub) / Loco Translate / Public Post Preview
  - SEO移行2：Yoast SEO / XML Sitemap Generator（フェーズ6で実施）
  - デザイン非直結5：Regenerate Thumbnails / Crop Thumbnails / To Top / WordPress Popular Posts / Yoast Duplicate Post
- [ ] **Download After Email** は `delete_plugins()` でuninstall不具合（`dae_uninstall` 未定義Fatal）の可能性 → **ファイル直接除去**で対応。
- [ ] **To Top** 削除前に、本番でも **SWELL pageTop（`id="pagetop"`）が出ている**ことを確認（重複解消）。
- [ ] **WordPress Popular Posts** 削除前に、本番に**可視の人気記事表示が無い**ことを確認（ウィジェット配置があればSWELL置換要）。
- [ ] **Yoast Duplicate Post** 削除前に、本番の **republish保留・複製途中の投稿が無い**ことを確認。
- [ ] **保留（条件が揃ってから判断）:** WP-Optimize（キャッシュ方針）/ WP Mail Logging（本番安定後）/ SAKURA RS WP SSL（本番サーバ決定後）/ MW WP Form（フォーム本番安定後）。
- [ ] **デザイン直結ブロック系（方針待ち）:** Gutentor / Useful Blocks / Easy Accordion / Templateberg は**ペガサス様のデザイン方針確定後**に、SWELLブロック置換を伴い段階的に。本カットオーバーでは触れない。

---

## フェーズ9：最終検証（本番）

ステージングの `docs/INTEGRATION_TEST_20260625.md` と同じ56項目を本番で実施：

- [ ] 全主要URL・CPTアーカイブ・CPT個別記事・タクソノミー = 200。
- [ ] リダイレクト8件 = 301（正しいLocation、誤転送なし）。
- [ ] サイトマップ：`/wp-sitemap.xml` 200（全CPT網羅）、`/sitemap.xml` 301。
- [ ] SEO：metaタグ単一出力・Yoast痕跡0・OGP（既定画像補完）・noindex該当。
- [ ] メール難読化：/application/ でコード露出なし・mailtoリンク動作。
- [ ] CF7 3種：送信→実メール（管理通知＝info/setsumei-kai 取り違えなし・自動返信）→Flamingo保存。確認欄不一致でvalidation_failed。
- [ ] 資料請求：送信→自動返信→トークンURL→PDF取得、PDF直アクセス遮断、トークン期限/回数/不正の挙動。
- [ ] スニペット：id=6（テストSMTP）が**無効**であること。
- [ ] レイアウト崩れがないこと（主要導線・表示）。

---

## フェーズ10：移行後の監視・後片付け

- [ ] Search Console：`/wp-sitemap.xml` のカバレッジ・インデックス・主要KWの順位を一定期間監視。
- [ ] メール到達性（SPF/DKIM）の監視。迷惑メール判定の有無。
- [ ] 旧DAE残置（`dae_download` 566・dae-uploads）をDB整理で撤去（本番安定後）。
- [ ] 保留プラグイン（WP-Optimize / WP Mail Logging / SAKURA SSL / MW WP Form）を条件成立後に判断。
- [ ] デザイン直結ブロック系（Gutentor等）をデザイン方針確定後に段階移行。

---

## ロールバック方針

- **SEO:** Yoastメタは全残置 → Yoast再設置＋有効化で即復旧。SSP設定/メタはバックアップから復元。サイトマップはGSG再設置で旧状態。
- **フォーム:** MW WP Form 定義は本番安定まで残置 → ショートコードを戻せば旧フォームに復帰。
- **リダイレクト:** グループ `pegasus-renewal` 削除、またはバックアップから復元。
- **全体:** フェーズ0の完全バックアップから復元。

---

## 参照レポート

| 領域 | レポート |
|---|---|
| CPT再構築 | `docs/CPT_RECONSTRUCTION_REPORT_20260623.md` |
| フォーム移行 | `docs/FORM_IMPLEMENTATION_LOG_20260623.md`（§F・各本番手順） |
| メール難読化 | `docs/MAIL_OBFUSCATION_ISSUE_20260624.md`（§8） |
| 資料請求システム | `docs/DOWNLOAD_SYSTEM_IMPLEMENTATION_LOG_20260625.md`（§本番移行） |
| SEO移行 | `docs/SEO_MIGRATION_EXECUTION_20260625.md`（§本番移行手順） |
| リダイレクト | `docs/REDIRECT_IMPLEMENTATION_REPORT.md` |
| プラグイン整理 | `docs/PLUGIN_CLEANUP_EXECUTION_20260625.md` / `docs/PLUGIN_CLEANUP_PHASE2_20260625.md` |
| 総合検証 | `docs/INTEGRATION_TEST_20260625.md` |
| CSS3層移行 | `docs/CSS_LAYER_MIGRATION_LOG.md`（Step1〜5） |

---

## フェーズ11：CSS3層構造 と SWELL内部クラス上書き（SWELL更新時の再点検）★橋本様ご指摘対応

CSS を3層化（`docs/CSS_LAYER_MIGRATION_LOG.md`）。装飾は **無料版 Code Snippets の PHPスニペット14本**（`wp_enqueue_scripts`＋`wp_add_inline_style`で出力・**ライセンス不要**）に集約、子テーマ style.css は**構造のみ**（`:root` 変数も持たない＝色定義は `pega-css-00-tokens` が唯一）。Pro版は解除・削除済（`docs/CODE_SNIPPETS_PRO_TO_FREE_20260715.md`）。本番移行時・SWELL更新時に以下を点検する。

### 11-1. スコープ方針（競合回避）
- トップ限定の上書きは **`.pega-top`** でスコープ（`functions.php` の `pega_body_class_top()`／`body_class`フィルタ・`is_front_page()`限定で **#body_wrap** に付与）。WPコア `.home` と同一要素のため付け替えは挙動不変。
- 全ページ共有の上書き（SPメニュー `.p-spMenu*`・連絡先 `.pega-header-contact` base・ハンバーガーアイコン色）は **bare で据え置き**（テンプレートoverrideで全ページ出力される共有要素のため。`.pega-top`に閉じるとサブページの見た目が変わる）。
- 物理層（読込順）：`pega-css-00-tokens` → `pega-css-01〜10`（各セクション） → `pega-css-20/21/22`（横断）。各スニペットは `child_style` のみ依存＋`add_action` の固有hook優先度（tokens=12／section=13〜22／global=30〜32）で順序保証。相互依存なしのため**個別ON/OFF可**・ID非依存（詳細は移行記録 §10）。

### 11-2. 上書きしている SWELL 内部クラス（**SWELL更新でクラス名/DOM/既定が変わると競合・失効**）
- **ヘッダー（`.pega-top #header …` トップ限定）**：`#header`/`#fix_header`/`.l-header__inner`/`.l-header__logo`/`.l-header__bar`/`.l-header__gnav`/`.l-header__menuBtn`/`.l-header__customBtn`/`.c-headLogo__link`/`.c-headLogo__img`/`.c-gnav`/`.c-gnavWrap`/`.sub-menu`/`.ttl`/`.c-iconBtn`(`__icon`)/`.current-menu-item`/`.l-container`
- **SPメニュー（`.p-spMenu …` bare・全ページ）**：`.p-spMenu`(`#sp_menu.p-spMenu`)/`.p-spMenu__inner`(`::before`)/`__body`/`__nav`/`__overlay`/`__closeBtn`/`.c-listMenu`/`.c-spnav`/`.c-widget__title.-spmenu`/`.c-submenuToggleBtn`(`.is-opened`)
  - ⚠️ **機能上書き**：`.p-spMenu .c-listMenu.c-spnav { min-height:0; overflow:visible }`（SWELL既定 `min-height:8em;overflow-y:auto` で項目が隠れる問題への対処）と、`@media 960–1349 .pega-top #sp_menu.p-spMenu { display:block }`（タブレット幅で開閉可能化）。SWELL更新で既定が変わったら**必ず開閉と全項目表示を確認**。
- **追従CTA**：`.p-fixBtnWrap`（位置調整・`pega-global-layout`）

### 11-3. テンプレート override（SWELLコア差分・要diff）
SWELLにフックが無いため子テーマでコアテンプレを**最小上書き**（git追跡下）。**SWELL更新時はコア原本とdiffし、注入行を保ったまま取り込む。**
- `parts/header/header_contents.php`：SWELLコア複製＋**1行注入** `pega_render_header_contact()`（連絡先ブロック）。
- `parts/footer/footer_contents.php`：SWELLコア複製＋フッター独自コンテンツ注入（`pega_render_footer_*`）。

### 11-4. SWELL更新後の表示・動作チェック
- [ ] トップ：ヘッダー青背景・白ロゴ（drop-shadow）・ナビ白文字＋accent下線
- [ ] トップ：960–1349px でハンバーガー（`order:-1`最左）・gnav非表示
- [ ] **SPメニュー開閉**（白パネル＋青「メニュー」バー＋HOME緑＋Xボタン＋overlayぼかし）※`pega-sp-menu.js`
- [ ] SPメニュー内で**全項目表示**（`overflow:visible` 機能上書きが有効か）
- [ ] 連絡先：601–959px 全ページ／960–1349px トップで表示
- [ ] 全ページ200・サブページのヘッダー（SWELL既定）が壊れていない
- [ ] `#body_wrap` に `.pega-top`（トップのみ）

### 11-5. 本番のCode Snippets（無料版＋PHPスニペット14本・ライセンス不要）
**Pro版は不要になった**（無料版へ移行済・`docs/CODE_SNIPPETS_PRO_TO_FREE_20260715.md`）。本番でも以下でライセンス無しに同一の見た目を再現できる。

- [ ] 本番に **無料版「Code Snippets」** を導入・有効化（Pro版は入れない）。
- [ ] **PHPスニペット14本を投入**：`scripts/code-snippets/php/pega-css-*.php`（00-tokens／01-commute〜10-header／20-global-layout・21-global-deco・22-global-motion）。Code Snippets の「インポート」またはスニペット新規作成で、各ファイルの内容（`<?php` 直後の本体）を貼り付け、**scope=「サイト全体で実行(global)」・有効化**。
  - 各スニペットは `wp_register_style( '<name>', false, array('child_style'), null )`＋`wp_add_inline_style()` で CSS を inline 出力。読込順は**コード内にハードコードした hook優先度**（`}, 12);` 等）で決まり、**インポートでIDが変わっても順序は保たれる**（ライセンス／`code_snippets_assets_rev` 不要）。
- [ ] 前提：子テーマ `functions.php` が `child_style` を優先度11で enqueue していること（14本はこれに依存して直後に読み込まれる）。
- [ ] 投入後の確認：レンダHTMLで `child_style` → `pega-css-00-tokens` → … → `pega-css-22-global-motion` の順に `<style>` が出ること／全ページ200／トップの見た目がステージングと一致すること。
- [ ] 運用周知：装飾の微調整は「スニペット → pega-css-00-tokens（色・余白の共通設定）／01〜10（各セクション）／20〜22（横断）」で編集・保存。**pega-css-00-tokens は土台のためオフにしない**（各スニペット冒頭の日本語ガイド参照）。
- [ ] 既存PHPスニペット（CF7確認欄 id5相当／メールリンク id7相当／サイトマップ id8相当）も本番で作成・有効化（id6 テストSMTP は入れない＝§フェーズ3・最重要#3）。
</content>
