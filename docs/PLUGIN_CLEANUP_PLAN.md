# プラグイン整理計画書

**対象サイト:** pegasus-job.com(ステージング環境で調査)
**作成日:** 2026-06-18
**目的:** Phase 4「不要プラグインの段階的削除」に向けた判断資料

---

## 1. 概要

現サイト(ステージング環境にコピー済み)には **合計 33 個** のプラグインがインストールされ、うち **29 個が有効**、**4 個が無効** の状態です。

リニューアル後の SWELL テーマ移行および新規導入予定プラグイン(SEO SIMPLE PACK / Contact Form 7 / Custom Post Type UI / Redirection / Code Snippets。[PLUGINS.md](../PLUGINS.md) 参照)を踏まえ、現行プラグインを以下 3 カテゴリに仕分けしました。

| カテゴリ | 件数 | 方針 |
|---|---:|---|
| **A. 継続使用** | 11 | リニューアル後も役割があり、SWELL では代替できない |
| **B. SWELL 代替で削除可能** | 8 | SWELL の標準機能で同等以上のことが実現できる |
| **C. 未使用 / 不要** | 14 | 利用実績がない、または新規プラグインに役割が引き継がれる |

**段階的削除の進め方(推奨):**

| フェーズ | 削除タイミング | 対象 |
|---|---|---|
| Phase 2(コンテンツ移行中) | データ移行先のプラグインに切替次第 | Yoast SEO → SEO SIMPLE PACK、MW WP Form → Contact Form 7 等の旧→新セット |
| Phase 3(本番リハーサル) | テーマ切替前に SWELL で問題なく動くもの | カテゴリ B(ブロック/UI 系) |
| Phase 4(本番切替後) | 切替後 2 週間動作確認 → 安定確認後 | カテゴリ C 全体 + その他不要分 |

---

## 2. 全プラグイン仕分け一覧

### 凡例

- 状態: ● 有効 / ○ 無効
- 削除可否: 🟢 削除可 / 🟡 条件付き(時期指定) / 🔴 継続

---

### カテゴリ A — 継続使用(11 件)

リニューアル後も役割があり、SWELL の標準機能では代替できないプラグインです。

| プラグイン名 | スラッグ | バージョン | 状態 | 役割 | 継続判定の理由 |
|---|---|---|:---:|---|---|
| SEO SIMPLE PACK | seo-simple-pack | 3.7.0 | ● | タイトル/メタディスクリプション/OGP 管理 | Phase 1 で新規導入(Yoast SEO の移行先)。継続。 |
| Contact Form 7 | contact-form-7 | 6.1.6 | ● | お問い合わせフォーム | Phase 1 で新規導入(MW WP Form の移行先)。継続。 |
| Custom Post Type UI | custom-post-type-ui | 1.19.2 | ● | カスタム投稿タイプ(CPT)定義 | Phase 1 で新規導入。新サイトの CPT(news/works/reports 等)を管理画面から定義。継続。 |
| Redirection | redirection | 5.8.0 | ● | 301 リダイレクト管理 | Phase 1 で新規導入。旧 URL → 新 URL の維持に必須。継続。 |
| Code Snippets | code-snippets | 3.9.6 | ● | カスタム PHP/JS/CSS の安全な管理 | Phase 1 で新規導入。SWELL 子テーマと疎結合に保つ。継続。 |
| TablePress | tablepress | 2.3 | ● | 表組み(現在 9 テーブルが現役で稼働中) | サイト内の「実習先企業」「就職実績」「就労実績」「法人概要」等の表で利用中。SWELL に同等機能なし。継続。 |
| TablePress: Responsive Tables | tablepress-responsive-tables | 1.8 | ● | TablePress のレスポンシブ拡張 | TablePress 継続に伴い継続。 |
| TablePress: DataTables Sorting plugins | tablepress-datatables-sorting-plugins | 1.0 | ● | TablePress の並び替え拡張 | 「就労実績(年月日ソート版)」で日付ソートに使用中。継続。 |
| WP Multibyte Patch | wp-multibyte-patch | 2.9 | ● | 日本語環境での文字化け・検索・抜粋などの不具合修正 | WordPress 日本語版で事実上必須。継続。 |
| UpdraftPlus | updraftplus | 1.24.3 | ● | バックアップ・復元 | 本番サイトもこれで運用。SWELL では代替不可。継続。 |
| GTM4WP(Google Tag Manager) | duracelltomi-google-tag-manager | 1.20.3 | ● | Google Tag Manager 連携 | アクセス解析 / 広告タグ管理。SWELL には GTM 連携機能なし。継続。 |

---

### カテゴリ B — SWELL 代替で削除可能(8 件)

SWELL の標準機能(ブロック・カスタマイザー設定など)で同等の機能が提供されるため、SWELL への切替後に削除できます。

| プラグイン名 | スラッグ | バージョン | 状態 | 役割 | SWELL での代替 | 推奨削除時期 |
|---|---|---|:---:|---|---|---|
| Gutentor | gutentor | 3.3.0 | ● | Gutenberg ブロックパック / ページビルダー | SWELL 純正ブロック群(`SWELL ブロック`)で代替 | Phase 3 |
| Useful Blocks | useful-blocks | 1.7.4 | ● | Gutenberg 追加ブロック | SWELL ブロックで代替 | Phase 3 |
| Easy Accordion | easy-accordion-free | 2.3.7 | ● | アコーディオン UI | SWELL の「FAQ ブロック」「アコーディオンブロック」 | Phase 3 |
| To Top | to-top | 2.5.2 | ● | ページトップへ戻るボタン | SWELL カスタマイザー → ボタン → ページトップへ戻るボタン | Phase 3 |
| WordPress Popular Posts | wordpress-popular-posts | 6.4.2 | ● | 人気投稿の集計・表示 | SWELL の「投稿リストブロック」+ アクセス数表示の運用判断(完全互換ではないため要検討) | Phase 3 ※要確認 |
| Templateberg | templateberg | 1.1.6 | ● | テンプレート / スターターパック | SWELL の「ブロックパターン」「デモコンテンツ」 | Phase 3 |
| Crop Thumbnails | crop-thumbnails | 1.7.2 | ● | アップロード時の画像トリミング | SWELL では画像の基本処理は WordPress 標準で十分。必要に応じて再考。 | Phase 3 |
| Regenerate Thumbnails | regenerate-thumbnails | 3.1.6 | ● | サムネイル再生成(テーマ切替時の必須作業に1回利用) | SWELL に切り替え後、本プラグインで一度全画像を再生成 → 用済みなので削除 | Phase 3 切替直後 |

**判定にあたっての注意:**
- カテゴリ B のプラグインで生成されたコンテンツ(Gutentor 製ページ、Useful Blocks ブロック等)は、削除すると **ブロック表示が崩れる可能性** があります。Phase 3 で SWELL ブロックに置き換える作業が必要です。
- WordPress Popular Posts は「人気記事」の集計データ(独自テーブル `wp04447apopularposts*` に保存)を持っており、削除すると過去の集計データが失われます。引き続き「人気記事」を表示したい場合は移行方針の事前合意が必要です。

---

### カテゴリ C — 未使用 / 不要(14 件)

そもそも使われていない、または新規導入プラグインに役割が引き継がれるため不要になります。

#### C-1. 移行先プラグインに役割を引き継ぐ(削除確定)

| プラグイン名 | スラッグ | バージョン | 状態 | 役割 | 引き継ぎ先 | 削除時期 |
|---|---|---|:---:|---|---|---|
| Yoast SEO | wordpress-seo | 22.9 | ● | SEO(タイトル・メタ・サイトマップ・パンくず等) | **SEO SIMPLE PACK** | Phase 2 後半(メタデータ移行完了後) |
| MW WP Form | mw-wp-form | 5.0.3 | ● | 旧お問い合わせフォーム(`mw-wp-form` CPT で 5 件運用中) | **Contact Form 7** | Phase 2 後半(フォーム再構築・送信先メール確認後) |
| XML Sitemap Generator for Google | google-sitemap-generator | 4.1.19 | ● | XML サイトマップ生成 | **SEO SIMPLE PACK** の XML サイトマップ機能 | Phase 2 後半(SEO SIMPLE PACK のサイトマップが Search Console に登録された後) |

#### C-2. 役割を終えている / 利用実績なし

| プラグイン名 | スラッグ | バージョン | 状態 | 役割 | 削除可否の判断 |
|---|---|---|:---:|---|---|
| WebSub(FKA. PubSubHubbub) | pubsubhubbub | 3.2.1 | ● | ブログ更新通知(WebSub プロトコル) | 役割はほぼ歴史的なもの。Google 等は別経路でクロール。削除可。 |
| Public Post Preview | public-post-preview | 2.10.0 | ● | 公開前プレビューを匿名 URL で共有 | 利用実態がなければ削除。クライアント様にヒアリング推奨。 |
| Yoast Duplicate Post | duplicate-post | 4.5 | ● | 投稿の複製 | 編集作業の便利機能。運用上の必要性をヒアリングして判断。 |
| Loco Translate | loco-translate | 2.6.7 | ● | テーマ/プラグインの翻訳カスタマイズ | 翻訳カスタマイズが終わっていれば削除可。SWELL は日本語完備のため不要。 |
| WP Mail Logging | wp-mail-logging | 1.12.0 | ● | メール送信ログ(デバッグ用) | ステージングでは便利だが、本番ではログが肥大化する。本番切替後は削除推奨。 |
| WP-Optimize | wp-optimize | 3.2.18 | ● | DB クリーンアップ / 画像圧縮 / キャッシュ | 機能の重複が懸念。SWELL はキャッシュ機能を持たないが、サーバ側設定で対応可なら不要。要方針確認。 |

#### C-3. サーバ依存プラグイン(リニューアル先サーバ次第)

| プラグイン名 | スラッグ | バージョン | 状態 | 役割 | 判断ポイント |
|---|---|---|:---:|---|---|
| SAKURA RS WP SSL | sakura-rs-wp-ssl | 1.4.0 | ● | さくらインターネット環境での常時 SSL 対応 | リニューアル後もさくらサーバ継続なら継続(カテゴリ A)、別サーバへ移転なら削除 |

#### C-4. 無効状態のまま放置されているもの(即削除候補)

| プラグイン名 | スラッグ | バージョン | 状態 | 役割 | 判断 |
|---|---|---|:---:|---|---|
| Block Visibility | block-visibility | 3.0.4 | ○ | ブロックの表示条件制御 | 無効状態のまま運用されており、現サイトでは未使用。SWELL のブロック表示制御で代替可能。削除可。 |
| Download After Email | download-after-email | 2.1.10 | ○ | メアド登録でファイル提供 | 無効状態。資料請求は別フォーム経由で処理しているため未使用。削除可。 |
| Font Awesome | font-awesome | 4.4.0 | ○ | アイコンフォント | 無効状態。SWELL は Font Awesome を内蔵。削除可。 |
| TS Webfonts for SAKURA RS | ts-webfonts-for-sakura | 3.1.3 | ○ | さくら + モリサワ Web フォント | 無効状態。リニューアルでフォント方針を再設計するため削除可(継続するならカテゴリ A に再分類)。 |

---

## 3. 段階的削除スケジュール(推奨)

| Phase | 時期 | 削除対象 | 内訳 | 件数 |
|---|---|---|---|---:|
| Phase 2 後半 | 移行データ動作確認後 | Yoast SEO、MW WP Form、XML Sitemap Generator | C-1 | 3 |
| Phase 3 中盤 | SWELL テーマ切替直前 | Gutentor、Useful Blocks、Easy Accordion、To Top、Templateberg、Crop Thumbnails、WordPress Popular Posts(※運用合意後) | B(7 件) | 7 |
| Phase 3 切替直後 | サムネイル再生成完了直後 | Regenerate Thumbnails | B(1 件) | 1 |
| Phase 4 序盤 | 本番切替後 2 週間の動作確認後 | 無効プラグイン 4 件 + 役割終了の 6 件 | C-2 + C-4 | 10 |
| Phase 4 中盤 | サーバ方針確定後 | SAKURA RS WP SSL(サーバ次第) | C-3 | 1 |

**合計削除予定:** 22 件
**残存:** 11 件(カテゴリ A)

---

## 4. ペガサス様への確認事項

判定にあたって、以下の点について方針のご確認をお願いいたします。

1. **WordPress Popular Posts**:現サイトで「人気記事」を表示している場合、過去の閲覧データを引き継ぐ必要があるか。引き継ぐ場合は SWELL ブロックでは完全代替できないため、本プラグインを継続するかご検討ください。
2. **Public Post Preview**:非公開記事のプレビュー URL を社内外の関係者に共有する運用があるか。ない場合は削除可。
3. **Yoast Duplicate Post**:管理画面で「投稿の複製」機能を日常的に使われているか。
4. **WP-Optimize**:現在はキャッシュ・DB 最適化・画像圧縮の 3 機能を使用していますが、リニューアル後はサーバ側のキャッシュ設定や別の手段(画像最適化サービス等)に切り替えるか、継続使用するか。
5. **リニューアル後のサーバ**:さくらインターネット継続か、別サーバ(VPS / クラウド等)への移転か。これにより「SAKURA RS WP SSL」「TS Webfonts for SAKURA RS」の継続可否が変わります。

---

## 5. データ参照元

- インストール済プラグイン:`wp_data/wp-content/plugins/` 配下のディレクトリ走査
- 有効プラグイン:`wp04447aoptions` テーブル `option_name='active_plugins'` のシリアライズ済配列
- バージョン情報:各プラグインメインファイルの `Plugin Name:` / `Version:` ヘッダ
- 調査スクリプト:`bash` ループで全プラグインのヘッダから抽出(再現コマンドは下記)

### 再現コマンド

```bash
# 全プラグインのバージョン一覧
cd wp_data/wp-content/plugins
for p in */; do
  slug="${p%/}"
  main=$(grep -l "Plugin Name:" "$slug"/*.php 2>/dev/null | head -1)
  ver=$(grep -m1 -iE "^[[:space:]]*\*?[[:space:]]*Version:" "$main" 2>/dev/null | sed -E 's/.*Version:[[:space:]]*//')
  echo "$slug: $ver"
done

# 有効プラグイン一覧(DB から)
sg docker -c "docker compose exec -T db mysql -u pegasus -ppegasus_pass_change_me \
  --default-character-set=utf8mb4 pegasus_wp -N -B -e \
  \"SELECT option_value FROM wp04447aoptions WHERE option_name='active_plugins';\""
```

---

**改訂履歴**

| 日付 | 改訂内容 |
|---|---|
| 2026-06-18 | 初版作成(Phase 4 準備) |
