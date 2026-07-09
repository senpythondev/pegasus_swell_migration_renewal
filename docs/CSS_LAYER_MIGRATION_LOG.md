# CSS 3層移行 実施ログ

**目的:** 橋本様のご指摘（CSS3層化）に沿い、装飾/トークンを子テーマ（②土台）から Code Snippets Pro（③装飾・site-css）へ段階移行する。各ステップは**見た目不変**を原則に、1ステップ=1コミットで進める。
**前提:** Code Snippets Pro 導入・認証・読込順ゲート実測合格済（`docs/CODE_SNIPPETS_PRO_INSTALL_20260710.md`）。計画は `docs/CODE_SNIPPETS_PRO_PLAN.md`。
**対象:** ステージングのみ。本番無変更。

---

## Step 1（2026-07-10）: pega-tokens（設計トークン）を Code Snippets へ

### 実施内容
- **Code Snippets（site-css / 優先度1）に `pega-tokens` スニペットを作成**（DB id=12・active）。運用調整の主入口。
  - 原本（版管理）: `scripts/code-snippets/pega-tokens.css`。
  - 内容: 子テーマ `:root` の**設計トークン28変数**＋ `@media(min-width:960px)` の `--pega-header-h:84px` 上書き。
- **移した変数（28）:**
  - 色: `--pega-base #1067D6` / `--pega-second #FD757C` / `--pega-accent #8DC73C` / `--pega-base-ink` / `--pega-base-dark` / `--pega-text` / `--pega-text-sub`
  - フォント: `--pega-font`（Noto Sans JP…）
  - ヘッダー: `--pega-header-h`（72px、≥960pxで84px）
  - 派生色/枠/影/角丸/余白: `--pega-soft-bg/-blue/-green` / `--pega-border` / `--pega-border-strong` / `--pega-card-shadow` / `--pega-card-radius` / `--pega-card-padding`
  - レイアウト幅/余白: `--pega-section-px/-py/-py-sm` / `--pega-wrap-content/-narrow/-wide/-banner`
  - 見出し/本文: `--pega-h2/-h3/-lead/-body`
- **スニペットは `:root` と `@media{:root}` のみ**（要素セレクタを含まない）＝**視覚的な副作用は原理的にゼロ**。

### 子テーマ側の扱い（安全策：フォールバック維持）
- **子テーマ `swell_child/style.css` の `:root` 定義は削除せず、フォールバックとして一時保持**（Cursor提案の方針）。
- 読込順は **Code Snippets（優先度15）＞ 子テーマ（優先度11）** のため、**スニペットの `:root` が有効値**（＝運用の主入口）。値は子テーマと**完全一致**のため見た目は不変。
- 万一スニペットが未読込/エラーでも、**子テーマの `:root` が同値を供給**＝崩れない。
- 次ステップ以降で、pega-tokens の安定を確認後に**子テーマ側の重複 `:root` を削除**予定（本ステップでは残す＝最も安全）。

### 検証（見た目不変）
| 確認 | 結果 |
|---|---|
| トークン値の一致（子テーマ vs スニペット） | ✅ **28変数すべて値が完全一致**（`diff` で確認）。見た目変化は不可能。 |
| スニペット出力・読込順 | ✅ snippet CSS（`<link ?code-snippets-css>` HTML123行）が子テーマ `style.css`（117行）**より後**にenqueue＝有効。 |
| 配信形式 | ✅ 外部スタイルシート（Content-Type: text/css / Expires +1年＝キャッシュ可、inline肥大なし）。 |
| 全ページ | ✅ 200（/・/about/・/news/・/reports/・/column/・/contact/・/application/・/download/・/homeworker/・/wp-sitemap.xml）。 |
| 既存機能 | ✅ id5 CF7確認欄検証=validation_failed、id8 サイトマップ=/sitemap.xml 301。PHPスニペット無影響。 |
| スニペット数 | 9件（有効5）＝既存8＋pega-tokens。 |

> **視覚回帰について:** 本ステップは「トークン値が子テーマと**バイト一致**＋子テーマをフォールバックで維持＋スニペットは:rootのみ（要素装飾なし）」のため、**レンダリング結果は原理的に不変**（ピクセル差分より強い保証）。当VPSはヘッドレスChromeが低速・不安定なため、確認用スクショはベストエフォート（差分ツール無し・真のbaseline確保困難）で、判定はトークン値一致＋全200＋機能無影響を主根拠とした。

### ペガサス様の運用
- **色・余白・フォントの調整は、管理画面 スニペット →「pega-tokens」を編集**すれば全ページに反映（＝運用調整の主入口が確立）。
- 保存時にアセットリビジョンが自動加算され、外部CSSがキャッシュバスティングされる。

### ロールバック
- スニペット `pega-tokens`（id=12）を停止/削除すれば、子テーマ `:root`（フォールバック）が同値を供給＝即座に元状態。DB `wp04447asnippets` のバックアップは `backup/snippets_pre_pro_20260710.sql`（Pro化前）。

### 次ステップ
- **pega-global-\*（横断装飾）** または **commute パイロット（セクション別の第1号）** へ。トークンが安定して効くことを確認できたため、着手可能。
- 子テーマ側の重複 `:root` 削除は、数ステップ安定後にまとめて（現時点は安全のため維持）。
