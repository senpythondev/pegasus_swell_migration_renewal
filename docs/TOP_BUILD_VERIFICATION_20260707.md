# トップページ実装のSWELL適合性 検証レポート（客観・実物確認）

**実施日:** 2026年7月7日
**方法:** Cursorの報告を前提とせず、**固定ページ4865のpost_content（DB）・子テーマファイル・レンダリングHTML・SWELLコアとの差分**を直接確認。
**対象:** ステージングのみ（検証のみ・修正なし）。

---

## 0. 結論（正直な総括）
- **ページ本体の各セクション（ヒーロー/悩み共感/ブリッジ/在宅サービス紹介/選ばれている理由/通所バナー）＝ ✅ 良好。** SWELLネイティブなブロックで作られ、生HTMLなし、管理画面で編集可能、SWELL更新耐性あり。
- **ヘッダー周りだけ ⚠️ SWELL更新でバグるリスクあり。** ①SWELLコアテンプレの上書き ②SWELL内部クラスへのCSS上書き多数 ③SWELL内部クラス依存の独自JS——の3点が結合。**ここは要改善（または更新毎の要検証）。**
- 「独自HTML（wp:html/直書き）」は**ページ本体には無い**（＝先の通所バナーのような食い違いは、本体ブロックには見られない）。リスクは**ヘッダーに集中**。

---

## 1. ページ本体（固定ページ4865）＝ SWELLブロックで健全 ✅

### 実測（DBのpost_content・38KB）
- 使用ブロック：`core/group`×63、`core/paragraph`×43、`core/heading`×23、`core/column`×22、`core/columns`×9、`core/image`×9、`core/embed`×4、**`loos/button`×4、`loos/full-wide`×1**（loos/* はSWELL純正ブロック）。
- **`wp:html` ＝ 0**（生HTMLブロックなし）。**直書きHTMLの露出＝0**（フロントで `&lt;div` 等の露出なし）。
- **画像は全て `wp:image`（添付ID付き。例 id:4878）** ＝メディアライブラリ連携・管理画面で差し替え可能。生`<img>`9個＝wp:image9個で一致（生画像の直挿し無し）。
- インライン`style=`は**1つだけ**（loos/full-wideの背景画像属性＝ブロックの正規出力）。
- 各セクションは**入れ子のコアブロック＋`className:"pega-*"`** で構成（例：bridge＝group>group>heading/paragraph、group>columns>column>group>image…）。
- レンダリング：フロント200、全セクション出力（hero/concerns/bridge/homeworker/reasons/commute/trust すべて描画）、**ブロック崩れ/recovery兆候＝0**。

### 評価
- **ペガサス様は各セクションをブロックエディタで編集可能**（見出し・段落・画像・ボタンが個別ブロック）。
- **SWELL更新耐性あり**：構造はコア/純正ブロック、装飾は独自`.pega-*`クラス（CSS 317行）。SWELL内部構造に依存しない。
- `scripts/top_page/*.html`（hero/concerns/bridge/homeworker/reasons/commute-banner）は**テーマから読み込まれていない＝単なるソース控え**（grep で参照ゼロを確認）。実体はDB。→ 独自HTMLの実行時依存は無い。

---

## 2. ヘッダー周り＝ SWELL更新でバグるリスク ⚠️（要改善）

デザイン優先でヘッダーをカスタムした結果、**SWELL内部への依存が3か所**に発生。いずれも「SWELLが該当箇所を更新すると壊れる/古くなる」種類。

### リスク①：SWELLコアテンプレの上書き（最重要）
- `swell_child/parts/header/header_contents.php` が存在。**SWELLコアの `parts/header/header_contents.php`（49行）のほぼ完全コピー（45行）＋1行注入**：
  `<?php if ( function_exists('pega_render_header_contact') ) pega_render_header_contact(); ?>`
- SWELLの `get_parts()` は子テーマを優先するため、**このコピーがコア版の代わりに使われる**。
- **問題:** SWELLが将来 `header_contents.php` を更新（機能追加・改修・不具合修正）しても、**この凍結コピーには反映されない** → ヘッダーの機能欠落や、関連パーツ変更時の破綻リスク。
- **さらに:** このファイルは**git未追跡**（`?? parts/`）＝バージョン管理外（控えは scripts/top_page/header_contents.php にあるが、テーマ配下は追跡されていない）。

### リスク②：SWELL内部クラスへのCSS上書き多数
- `style.css`（51KB・393セレクタ）のうち317行は独自`.pega-*`で健全だが、**約90行超がSWELL内部クラスを対象**：
  `.l-header__inner/__logo/__menuBtn/__customBtn`、`.c-gnav`(13)、`.p-spMenu`(18)、`.c-headLogo__link/__img`、`.c-iconBtn`(5)、`.c-spnav`、`.c-listMenu`、`.swell-block-button__link`(14)、`.swell-block-fullWide`/`.wp-block-loos-full-wide`(各6) 等。
- **問題:** ヘッダー/グロナビ/SPメニュー/ロゴ/ボタンを**SWELL内部クラスの上書きで見た目調整**している。SWELLがこれらのクラス名・構造を変更すると**ヘッダー/ボタンの表示が崩れる**。

### リスク③：SWELL内部依存の独自JS
- `pega-sp-menu.js`（enqueue済）は**SWELL内部のSPメニュー実装に依存**：
  `#sp_menu .c-spnav`、`.l-header__menuBtn [data-onclick="toggleMenu"]`、`.c-submenuToggleBtn.is-opened`。
- **問題:** SWELLがSPメニューのクラス/データ属性を変えると**SPメニュー開閉が動かなくなる**恐れ。
- 補足：functions.php は `acc_submenu=true`（SWELL純正のサブメニュー開閉）も設定済み。**独自JSがSWELL純正機能と重複していないか要確認**（重複なら独自JSを外して純正に寄せると堅牢）。

### 補足：ボタン
- CTAは `loos/button`（SWELL純正ブロック）＝良好。ただし見た目は `.swell-block-button__link` の上書きで調整＝軽度の結合（SWELLボタン改修時に影響し得る）。

---

## 3. 管理画面での編集可能性
- ページ本体：✅ 各セクションが正規ブロック（core/loos）＝**文言・画像を管理画面で編集可能**。画像はwp:image（添付ID）でメディアから差し替え可。ブロックrecovery兆候なし＝パース健全。
- ヘッダー：ロゴ/メニューはSWELLカスタマイザー/メニュー管理で編集可能だが、**連絡先の注入はテンプレ上書き由来**のため、管理画面からは編集不可（コード管理）。

---

## 4. 改善提案（判断は大野様・司令塔に委ねる／今回は未修正）
1. **ヘッダー連絡先の注入をテンプレ上書き→SWELLフック方式へ。** SWELLのアクションフック（例：`swell_...` 系）や「ヘッダー内カスタムHTML/ウィジェット」機能で `pega_render_header_contact()` を出力できないか検討。実現できれば `parts/header/header_contents.php` の上書きを撤去でき、更新耐性が大幅改善。
2. **上書きが不可避なら:** ①gitに追跡、②「SWELL更新時に header_contents.php のコア差分を要確認」を移行チェックリストに明記、③差分を最小化。
3. **ヘッダーCSSのSWELL内部クラス上書きは、上書き対象クラス一覧を文書化**し、SWELL更新時に再点検。可能な範囲はSWELLカスタマイザー設定（ヘッダー配色・レイアウト・ボタン）へ寄せる。
4. **`pega-sp-menu.js` がSWELL純正のSPメニュー開閉と重複していないか検証**。重複なら撤去し純正に統一。
5. **バージョン管理:** `parts/`・`pega-*.js` をgit追跡（現状 未追跡）。
6. **SWELL更新時の重点確認箇所＝ヘッダー**（PCグロナビ・SPハンバーガー開閉・ロゴ・ボタン・連絡先）。ページ本体セクションは低リスク。

---

## 5. まとめ（正直な評価）
| 領域 | 実態 | SWELL更新耐性 | 管理画面編集 |
|---|---|:---:|:---:|
| ページ本体6セクション | core/loosブロック＋pega-*クラス＋wp:image。生HTML/wp:html=0 | ✅ 高い | ✅ 可能 |
| ヘッダー（テンプレ上書き） | SWELLコアtemplateの凍結コピー+1行注入 | ⚠️ 低い | ✗ コード |
| ヘッダー/ナビ/ボタンCSS | SWELL内部クラス上書き約90行 | ⚠️ 低い | － |
| SPメニューJS | SWELL内部クラス/属性依存 | ⚠️ 低い | － |

**総評:** 「各セクションはSWELLブロックで、独自HTMLなし、管理画面で編集可能」というCursor報告は**ページ本体については実物と一致・妥当**。一方で**ヘッダーはSWELL内部への上書き/依存が強く、SWELL更新でバグるリスクが実在**。次工程前に、ヘッダーの作り（特にテンプレ上書き）を改善するか、更新時要検証として明示するかの判断を推奨。**本体セクションは土台として問題なし。**
