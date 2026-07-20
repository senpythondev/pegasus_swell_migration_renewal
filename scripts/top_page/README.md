# トップページ（固定ページ ID=4865）ブロック原本

WordPress DB `post_content` に投入する各セクションの **Gutenberg ブロック HTML** です。

## 使い方

| ファイル | セクション |
|---------|-----------|
| `hero.html` | ヒーロー（FV） |
| `concerns.html` | 悩み共感 |
| `bridge.html` | 課題→解決ブリッジ |
| `homeworker.html` | 在宅型紹介 |
| `reasons.html` | 選ばれる理由 |
| `commute-banner.html` | 通所バナー |
| `voices.html` | 利用者の声 |
| `posts.html` | コラム/ブログ |
| `closing-cta.html` | 締めCTA |

- **本番の実体**は WordPress DB（固定ページ 4865）です。
- DB 喪失時の復元用バックアップとして Git で管理します。
- セクション単体更新: `scripts/update-concerns-post.py`（concerns 専用）を参照。
- ヒーロー（FV）単体更新: `scripts/update-hero-post.py`。
- 全セクション結合・投入は WP-CLI または管理画面で手動結合。

## 子テーマとの関係

- **CSS 構造**: `wp_data/wp-content/themes/swell_child/style.css`
- **CSS 装飾**: `scripts/code-snippets/php/pega-css-*.php`（Code Snippets 14本）
- **JS**: `wp_data/wp-content/themes/swell_child/pega-*.js`（`functions.php` で enqueue）

HTML 原本はここ（`scripts/top_page/`）のみ。テーマ JS/CSS の複製は置かない。
