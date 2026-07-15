# Code Snippets（無料版）CSS原本

**現行の唯一の原本は `php/` 配下の14本（`pega-css-*.php`）です。**

- 実体は Code Snippets（無料版）のPHPスニペット（DB `wp04447asnippets`・scope=global）。
  各スニペットが `wp_enqueue_scripts` で `wp_register_style( '<name>', false, array('child_style') )`＋
  `wp_add_inline_style()` によりCSSを出力。ライセンス不要。
- **CSSの編集は「WordPress管理画面 → スニペット」で該当スニペットを直接編集**するのが基本
  （各スニペット冒頭に日本語の編集ガイドあり）。ファイルで管理する場合も `php/*.php` を正とする。
- 読込順は各スニペット末尾の `add_action(..., <優先度>)` の固有hook優先度で保証：
  `00-tokens=12` / `01〜10=13〜22`（各セクション）/ `20/21/22=30/31/32`（横断）。
  相互依存なし＝**個別ON/OFF可**・インポートでID変化しても順序不変。
- 本番移行：`php/*.php` の14本を無料版Code Snippetsに投入（scope=global・有効化）すれば
  ライセンス無しで同一の見た目を再現。詳細は `docs/PRODUCTION_CUTOVER_CHECKLIST.md` フェーズ11。

## 撤去したもの（旧・非使用）
- ルート直下にあった `pega-*.css`（14本）… **Pro時代の旧原本（site-cssスニペット id12-25用）**。
  Pro解除でそれらのスニペットは削除済のため**非使用**。内容は現行 `php/*.php` と同一のため撤去。
- `scripts/sync-snippets.sh` … 上記 *.css を旧Pro DBスニペットへ同期する旧スクリプト（対象ID削除済で無効）。撤去。

移行の全経緯は `docs/CODE_SNIPPETS_PRO_TO_FREE_20260715.md` を参照。
