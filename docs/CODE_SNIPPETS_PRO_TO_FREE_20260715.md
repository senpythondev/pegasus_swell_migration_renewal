# Code Snippets Pro → 無料版＋PHPスニペット 移行記録

**実施日:** 2026年7月15日
**背景:** 橋本様ご指摘「無料版でも PHP スニペット＋`wp_enqueue_scripts` 等のフックで CSS を出力・管理でき、それが標準的運用。Pro版は不要」への是正対応。Pro版は `class-evaluate-assets.php` の `was_licensed()` により**ライセンス認証が無いと CSS を一切出力しない**構造で、サイトの見た目が商用ライセンスに依存する脆弱性があった。無料版へ移行しこの依存を解消。**対象: ステージングのみ。見た目は一切変えない。**

---

## 0. 結論
- **無料版「Code Snippets」＋ PHP スニペット3本方式へ移行完了。** Pro依存（ライセンス）を解消。
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
- 本番でも同3本のPHPスニペットを投入すれば、**ライセンス不要**でCSSが出力される（`docs/PRODUCTION_CUTOVER_CHECKLIST.md` の Code Snippets 項を「無料版＋PHPスニペット3本」に更新）。
- ペガサス様への周知: 装飾編集は「スニペット → pega-css-1-tokens/2-sections/3-global」で行う。
