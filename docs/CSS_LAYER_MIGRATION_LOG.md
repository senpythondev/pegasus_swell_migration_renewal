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

---

## Step 2（2026-07-10）: commute（通所バナー）パイロット — 装飾/構造の分離

**目的:** 独立性の高い commute で「装飾③をスニペットへ／構造②は子テーマに残す」手順を確立し、横展開の型を作る。**見た目は一切変えない。**

### 確立した手順（次セクションへの横展開テンプレート）
1. **洗い出し:** `grep -nE '\.pega-<section>' style.css` で、対象セレクタを**本体ブロック＋横断セクション（UNIFIED/LAYOUT）両方**から検出。
2. **分類（セレクタ単位）:** 「装飾プロパティ（色・背景・グラデ・影・角丸・hover・font-size/weight）が1つでも混ざるセレクタは、セレクタごと③へ丸ごと移す」。純構造（grid/flex・幅・position・@media・inner-container余白）は②に残す。**プロパティ単位分割はしない**（カスケード事故回避）。
3. **トークン化は"値が完全一致する時だけ":** 見た目を変えないため、トークン値と異なるハードコードは維持。commute は意図的に中間色（グレー系＝控えめ動線）で青系カードトークンと値が異なるため、`border-radius:12px = --pega-card-radius` の1件のみ var() 化。
4. **スニペット作成（③）:** site-css・優先度10（tokens=1 より後、将来の pega-global-\* より前）。管理画面保存 or DB直挿し＋`code_snippets_assets_rev` 加算。
5. **効いていることを確認 → その後に子テーマから削除:** エンドポイント `?code-snippets-css` に対象装飾が出力されるのを確認してから、子テーマの装飾ブロックを削除（重複解消）。削除は exact-block 置換＋ブレース均衡チェック。
6. **検証:** 全ページ200／既存機能／視覚（PC・SP）。

### commute の分類結果
| 分類 | セレクタ | 移動先 |
|---|---|---|
| **③装飾（8）** | `.pega-commute`(背景/font/border-top)、`__banner`(背景/枠/角丸/影)、`__thumb img`(角丸/枠/影)、`__title`(色/太さ/サイズ)、`__text`(色/サイズ)、`__note`(色/サイズ)、`__cta .swell-block-button__link`(背景/色/枠/角丸)、同 `:hover` | **pega-sec-commute** スニペット |
| **②構造** | inner-container群・`__wrap`(幅/余白)・`__inner`(grid骨格)・`__thumb`(幅/flex)・`__body`(余白)・`__action`(flex)・`__cta.swell-block-button`(margin)・`@media 640/639`(grid) | 子テーマ style.css に残す |

- スニペット原本（版管理）: `scripts/code-snippets/pega-sec-commute.css`（DB id=13・active・優先度10）。
- 子テーマの commute 装飾8ブロックは削除、構造は保持（削除箇所に移行メモのコメントを残す）。

### ⚠️ 横断CSS（UNIFIED REFINEMENT / LAYOUT FIXES）との干渉 — 重要
commute の装飾は**本体ブロックだけで完結していない**。以下が**横断の共有グループ**に存在する:
- **カード枠線・影:** `.home :is(… .pega-commute__banner)` が `border/box-shadow` を指定（LAYOUT FIXES、8セクション共有）。**実際の枠/影はこの横断側が有効**（詳細度 `.home`＝高いため本体ブロックを上書き）。
- **タイトル装飾リセット:** `.home .post_content :is(… .pega-commute__title)` と `::before` が `border:none/background:transparent`（UNIFIED、10セクション共有）。
- **幅・余白:** `.home .pega-commute__wrap` / `.home .pega-commute`（幅・padding）も横断側。

→ これらは `:is()` で**多数セクションを一括指定**しており、commute 分だけ切り出すと他セクションのルールを壊すため、**本ステップでは横断側は子テーマに残置**。commute の card枠/影・title リセットは、まだ②（子テーマの横断層）にある。

**カスケード安全性:** 横断側は全て `.home` 接頭辞で**詳細度が高く、読込順に関係なく勝つ**。本体ブロック装飾（詳細度低）をスニペット（後読み）へ移しても**反転は起きない**＝見た目不変。実際、削除した本体 `__banner` の border/shadow は元々横断側に上書きされて無効だった値なので、削除しても表示は不変。

### 検証（見た目不変）
| 確認 | 結果 |
|---|---|
| スニペットが装飾を出力 | ✅ `?code-snippets-css` に commute 8セレクタ出力（`border-radius:var(--pega-card-radius)` 含む）。削除**前**に確認。 |
| 子テーマから装飾が消えたか | ✅ 配信 style.css に `#f8f7f4`/`#f3f2ef`/title色=**0件**。構造 `__inner`(grid)=5件は残存。 |
| ブレース均衡 | ✅ `{`=`}`=646。CSS破損なし。 |
| キャッシュ | ✅ child_style は `filemtime` 版付き（`?ver=20260710121241`）＝編集で自動バスティング。 |
| 全ページ | ✅ 200（10ページ）。 |
| 既存機能 | ✅ id8 sitemap=301。PHPスニペット無影響。 |
| 視覚（PC/SP） | ✅ commute バナー（グレー背景・白カード・平塚サムネ・CTAボタン・余白）に変化なし。 |

### ロールバック
スニペット `pega-sec-commute`（id=13）停止では**装飾が消える**（子テーマから削除済のため）。完全復元は git revert（このコミット）で子テーマ装飾を復活。DBバックアップ `backup/snippets_pre_pro_20260710.sql`。

### 次ステップの推奨 → **pega-global-\* を先に**
commute パイロットで、**セクション装飾が横断層（UNIFIED/LAYOUT）と絡む**ことが判明。card枠/影・title リセット・幅/余白の"最終調整"は横断の `:is()` グループにあり、各セクション snippet だけでは③に集約しきれない。
→ **次は pega-global-\*（UNIFIED REFINEMENT + LAYOUT FIXES を snippet 化、優先度は section より後＝最終上書き）を先に処理**し、その後に残りのセクションを進めるのが、二度手間なく綺麗な3層になる。
