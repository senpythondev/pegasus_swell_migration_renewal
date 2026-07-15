# Code Snippets Pro → 無料版＋PHPスニペット 移行記録

**実施日:** 2026年7月15日
**背景:** 橋本様ご指摘「無料版でも PHP スニペット＋`wp_enqueue_scripts` 等のフックで CSS を出力・管理でき、それが標準的運用。Pro版は不要」への是正対応。Pro版は `class-evaluate-assets.php` の `was_licensed()` により**ライセンス認証が無いと CSS を一切出力しない**構造で、サイトの見た目が商用ライセンスに依存する脆弱性があった。無料版へ移行しこの依存を解消。**対象: ステージングのみ。見た目は一切変えない。**

---

## 0. 結論
- **無料版「Code Snippets」＋ PHP スニペット方式へ移行完了。** Pro依存（ライセンス）を解消。※当初3本で構築後、微調整しやすさのため**最終的にセクション別14本へ再分割**（§7-1）。以下 §1〜6 は初期の3本時点の記録、最終形は §7。
- **Proプラグイン本体も削除済**（§7-3）。管理画面からの実編集（編集→保存→反映→復元）も実証済（§7-2）。
- **見た目は移行前後でピクセル完全一致**（差分 0 / 1,620万px・最大画素差 0。PC/モバイル）。
- 既存PHPスニペット **id5-8（CF7確認欄・SMTP・メールリンク・サイトマップ）は完全保持・動作**（id5 は実送信で「メールアドレスが一致しません」を確認）。
- **Pro解除済**（`active_plugins` を無料版へ差替）→ **返金申請可能な状態**。

---

## 1. 移行方式（3層構造を PHP スニペットで再現）

Pro の CSSスニペット（site-css / 優先度で連結・外部配信）を、**無料版の PHPスニペット3本**に置換。各スニペットが `wp_enqueue_scripts` で `wp_register_style( handle, false, [依存] )` ＋ `wp_add_inline_style()` により CSS を出力。**`src=false` の空ハンドル＋依存チェーン**で、子テーマ→トークン→セクション→横断の読込順を厳密に再現。

| PHPスニペット(scope=global) | ハンドル | 依存(dependency) | 内容(3層) |
|---|---|---|---|
| `pega-css-1-tokens` | `pega-css-tokens` | **`child_style`** | ① 設計トークン（色/余白/フォント等・**運用の主入口**） |
| `pega-css-2-sections` | `pega-css-sections` | `pega-css-tokens` | ② 各セクション装飾（commute〜hero/header 10本を連結） |
| `pega-css-3-global` | `pega-css-global` | `pega-css-sections` | ③ 横断（layout→deco→motion / 最終上書き） |

- **読込順の再現（実測）:** レンダHTMLで `child_style`(link) → `pega-css-tokens-inline` → `pega-css-sections-inline` → `pega-css-global-inline` の順で出力。依存チェーンにより優先度に頼らず順序保証（＝Proの priority 1→10→30/31/32 と同義）。
- **編集可能性の維持:** CSS は各スニペット内の nowdoc（`<<<'PEGA_LAYER_EOF'`）に格納。ペガサス様は管理画面「スニペット」で3層それぞれを編集可能（トークンで色味、sections で各セクション、global で横断）。
- **inline肥大対策:** 出力時に `pega_css_min()`（コメント除去・行頭インデント除去・空行圧縮・末尾セミコロン除去。**プロパティ内スペース `16px 32px` 等は不変**）で軽量化。Pro同様のサイズ感。gzipで実転送は小。
- 原本(版管理): `scripts/code-snippets/php/pega-css-{1-tokens,2-sections,3-global}.php`（実体はDB `wp04447asnippets`）。

---

## 2. 手順（各段階で検証・バックアップ）

1. **バックアップ:** `backup/pro_to_free_20260715/`（`snippets_table.sql` / 14本のCSSファイル / `snippets_before_disable.sql` / `snippets_before_delete.sql`）。
2. **メカニズム検証:** 捨てスニペットで `src=false`＋`wp_add_inline_style`（dep child_style）が child_style 直後に出力されることを確認。
3. **PHPスニペット3本を作成**（Pro有効のまま・二重出力状態で検証）。構文チェック済。Pro出力（外部）とPHP出力（inline）を正規化比較 → 規則数409一致、相違はProの値ミニファイ（`rgba(255,255,255,0)`→`#fff0` 等の等価変換）のみ＝同一ソース由来。
4. **Pro CSSスニペット14本を無効化** → PHPのみで描画 → **baselineとピクセル完全一致（差分0）**を確認。
5. **Pro→無料版へ差替**（`active_plugins` を `code-snippets-pro/…` → `code-snippets/…`。直接差替で再宣言衝突を回避）。`CODE_SNIPPETS_PRO` 未定義化を確認。
6. **無料版で再検証:** ピクセル完全一致・全200・sitemap301・id5-8動作・SPメニュー開閉・motion/inview・モバイル。
7. **Pro CSSスニペット14本を削除**（バックアップ済）→ 再度ピクセル完全一致を確認。

---

## 3. 検証結果（見た目・機能）
| 項目 | 結果 |
|---|---|
| 見た目（PC・移行前後ピクセル比較） | ✅ **差分 0 / 1,620万px・最大画素差 0**（Pro無効化後／無料版化後／削除後の3時点すべて一致） |
| 見た目（モバイル） | ✅ 目視一致（ヘッダー・ヒーロー・実績数字オレンジ下線・カード等） |
| 読込順・優先度 | ✅ child_style→tokens→sections→global（依存チェーンで再現） |
| 全ページ | ✅ 200（10ページ）・sitemap 301 |
| 既存PHP id5（CF7確認欄） | ✅ 不一致送信で `validation_failed`＋「メールアドレスが一致しません…」 |
| 既存PHP id6/7/8 | ✅ active保持（id8 sitemap 301 実動作） |
| SPメニュー開閉 | ✅ ハンバーガー→開く（visible/opacity1/active・puppeteer実クリック） |
| アニメーション | ✅ `--pega-motion-*`・`pega-inview.js`・`prefers-reduced-motion` 出力 |
| Pro依存 | ✅ 解消（`CODE_SNIPPETS_PRO`=未定義・`was_licensed` 無し無料版） |

---

## 4. Pro解除・返金
- **Pro プラグインは無効化済**（`active_plugins` から除外）。プラグインファイル `code-snippets-pro/` は**ロールバック用に一時保持**（安定確認後に削除可）。
- **返金申請可能な状態**。Freemius の返金（7/10購入・14日返金保証内）を進めれば、ライセンスは返金時に自動解放される。必要なら本番移行前にライセンスをFreemiusダッシュボードで解除。

## 5. ロールバック手段
- スニペットDB: `backup/pro_to_free_20260715/snippets_before_delete.sql`（削除直前・全スニペット）を復元。
- プラグイン: `active_plugins` を `code-snippets-pro/code-snippets.php` に戻す（Pro再有効化）。
- CSS原本: `backup/pro_to_free_20260715/css/*.css`（14本）／`scripts/code-snippets/php/*.php`（PHP版）。

## 6. 残タスク（本番移行時）
- 本番でも同**14本**のPHPスニペット（`scripts/code-snippets/php/pega-css-*.php`）を投入すれば、**ライセンス不要**でCSSが出力される（`docs/PRODUCTION_CUTOVER_CHECKLIST.md` の Code Snippets 項を「無料版＋PHPスニペット14本」に更新）。
- ペガサス様への周知: 装飾編集は「スニペット → pega-css-00-tokens（色味）/ 01〜10（各セクション）/ 20〜22（横断）」で行う。個別ON/OFFも可能。

---

## 7. 【追記 2026-07-15】報告前の3点対処

橋本様への報告前に、以下3点を確認・対処。

### 7-1. 粒度をセクション別（14本）へ再分割 ← 橋本様「微調整しやすさ」に合わせて
当初 sections を1本(pega-css-2-sections)に集約したが、**運用者が微調整しやすいよう、元Proと同じ粒度（1セクション=1スニペット）へ再分割**。
- **14本構成（scope=global・線形依存チェーンで読込順保証）:**
  `pega-css-00-tokens`(dep child_style) → `01-commute` → `02-concerns` → `03-bridge` → `04-homeworker` → `05-reasons` → `06-voices` → `07-posts` → `08-closing` → `09-hero` → `10-header` → `20-global-layout` → `21-global-deco` → `22-global-motion`（各々1つ前に依存）。
- **利点:** 個別ON/OFF可・目的箇所を探しやすい・編集範囲が小さく事故が少ない（＝橋本様の「微調整しやすさ」に合致）。名前を連番プレフィックスにし管理画面で順に並ぶ。
- 読込順は実測で child_style→00→…→22 の順を確認。**baselineとピクセル完全一致（差分0）**。原本: `scripts/code-snippets/php/pega-css-*.php`。

### 7-2. 管理画面からの実編集テスト（実施・成功）← 橋本様要求の核心
**実際に wp-admin にログインし、Code Snippets の編集画面で編集→保存→反映→復元を実施**（puppeteerでUI操作）。
- `pega-css-00-tokens` を開き、CodeMirror上で `--pega-base` を `#1067D6`→`#E4007F`（マゼンタ）に変更 → 「保存」ボタン押下。
- **結果:** スニペットは **active のまま（無効化されず・PHP保存エラー無し）**、DBに反映、**フロントのヘッダー等が即マゼンタ化**（--pega-base=#E4007F / header=rgb(228,0,127)）。
- **復元:** 同じ管理画面で `#E4007F`→`#1067D6` に戻して保存 → **baselineとピクセル完全一致（差分0）で青に復元**。
- → **運用者が管理画面から安全に微調整でき、保存時にPHPエラーや無効化が起きないことを実証。** CSSはnowdoc内のためPHP構造は保存で変わらず、値編集は安全。

### 7-3. Proプラグイン本体を削除（実施）
- 無料版で全機能・見た目・管理画面編集が安定していることを確認の上、**`wp-content/plugins/code-snippets-pro/` を削除**。
- バックアップ2重（`tmp/code-snippets-pro.3.9.6.zip` 原本ZIP ＋ `backup/pro_to_free_20260715/code-snippets-pro_backup.tar.gz`）。
- **削除後の再確認:** 全ページ200・sitemap301・**baselineとピクセル完全一致（差分0）**・id5-8動作・**Code Snippets管理画面が正常に開き14本を一覧表示（fatal無し）**・Pro定数=未定義。
- → 「Pro版は解除しました」と報告可能な実態（プラグイン本体も除去）。**返金申請を進められます。**

---

## 8. 【追記 2026-07-15 その2】大野様レビュー指摘6点の対処

管理画面レビューで判明した「実物と報告の食い違い・運用者への分かりにくさ」6点を是正。**全時点で baseline とピクセル完全一致（差分0）。**

### 8-1. Pro時代のコメント修正（14本すべて）
各CSSの先頭に残っていた「Code Snippets Pro (site-css / priority N) に集約」等のPro時代コメントを**全除去**。代わりに各スニペット冒頭へ**運用者向けの日本語ガイド**（下記8-4）を配置。`grep` でPro/site-css/priority/フォールバックの残存ゼロを確認（※font名 "Hiragino Kaku Gothic ProN" の "Pro" は正当なので不変）。

### 8-2. 「個別ON/OFF」の設計見直し＋実検証
- **旧: 線形依存チェーン**（各スニペットが1つ前に依存）は、途中1本を停止すると**それ以降が依存先を失い全て消える**危険があった（ご指摘の通り）。
- **新: 各スニペットは `child_style` のみに依存**し、読込順は `add_action` の**固有hook優先度**（tokens=12／各section=13〜22／global=30/31/32）で制御。相互依存なし。
- **実検証**: `pega-css-05-reasons` を停止 → **06〜22 は全て出力継続**（reasonsのセクション装飾だけ外れ、他・横断は健全・全200）。復帰でピクセル完全一致。
- **`pega_css_min` 関数依存の解消**: 関数自体を廃止（8-4）したため、「tokens停止で他が『関数がない』エラー」は**原理的に発生しない**。実検証: tokens停止でも他13本は出力継続・全200・PHP fatalなし。
- → **橋本様に「各セクション/横断を個別にON/OFFできる」と説明できる状態**（tokensは色の土台なので通常オンのまま）。

### 8-3. 子テーマの重複 :root を削除（色定義を一本化）
子テーマ `swell_child/style.css` の `:root{ --pega-* }`（フォールバック）を削除。**色・余白・フォントの定義元は `pega-css-00-tokens` の1箇所のみ**に統一（「トークンが主入口」と実態が一致）。`.pega-br-pc`（構造）は残置。削除後ピクセル完全一致。

### 8-4. pega_css_min（圧縮関数）を削除・簡潔化
- スニペット冒頭に並んでいた正規表現のPHP関数 `pega_css_min` を**全廃**。CSSを**そのまま出力**（`wp_add_inline_style($handle, $css)`）。効果の小さい圧縮より、**運用者の分かりやすさ・安全性**を優先。
- 各スニペット冒頭に**日本語の編集ガイド**を配置：「▼ペガサス様 編集ガイド ─ ○○の装飾／下の `$css = <<<'CSS' 〜 CSS;` の間がCSS。色や余白を変えたい時はそこを書き換えて保存／このスニペット単体をオフにしても他に影響しません／色の共通設定は pega-css-00-tokens」。
- → スニペットを開くと**最初に日本語ガイドが見え、すぐ下にCSS**。「ここの色を変えればいい」が一目で分かる。

### 8-5. 日付のずれ（07/16 表示）を修正
原因: スニペット保存時の `modified` を**ローカル時刻(JST)で保存**していたが、Code Snippets は `modified` を**GMTで保存する前提**で管理画面表示時に +9h する。→ JST保存だと二重加算で翌日(07/16)表示に。**`current_time('mysql', true)`（GMT）で保存**に修正。管理画面から07/16表示が消え、正しい相対表示（「◯分前」）に。WPタイムゾーン設定(Asia/Tokyo)自体は正常。

### 8-6. サンプルスニペット削除
Code Snippets 初期サンプル4本（ファイル名英小文字化／管理バー無効化／絵文字／現在の年・いずれも停止中・未使用）を**削除**し一覧を整理。バックアップ `backup/pro_to_free_20260715/snippets_before_cleanup.sql`。

### 最終状態
- スニペット計18本＝**pega-css 14本（tokens＋10セクション＋global3・全active・個別ON/OFF可・Pro記述なし・日本語ガイド付き）** ＋ 既存PHP 4本（id5-8）。
- 見た目: **全時点 baseline とピクセル完全一致（差分0/1,620万px）**。全200。管理画面から編集→保存→反映→復元 実証済。
- 原本: `scripts/code-snippets/php/pega-css-*.php`（新版）。
- ※子テーマ :root 削除は staging 反映済・working tree にあり。style.css は他作業の未コミット差分が大きいため本コミットには含めず（別途コミット時に併せる）。
