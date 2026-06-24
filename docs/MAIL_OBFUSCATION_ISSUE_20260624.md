# 調査レポート：メール難読化スクリプトの表示問題

**調査日:** 2026年6月24日
**対象環境:** ステージング（調査）。本番（pegasus-job.com）は**閲覧（curl）のみ**・無変更。
**性質:** **調査のみ。修正は未実施**（原因・影響範囲を把握し方針を決める段階）。
**事象:** /application/（個別説明会申込）ページに、メール難読化のJavaScriptコードが画面上にテキストとして表示される。

> メールアドレスはマスク（`setsumei-kai@[managed-domain]` = pegasus-job.com 系）。

---

## 0. 結論サマリー

| 問い | 結論 |
|---|---|
| 原因 | /application/（post 151）の `wp:html` ブロック内のメール難読化JSが、**`<script>`タグなし＋HTMLエンティティ符号化**で保存されており、ブラウザが実行せずテキスト表示している |
| コメント記号 | **半角 `<!--`**（保存上は `&lt;!--`）。全角崩れではない（画面表示の見た目の誤認） |
| ショートコード差替が原因か | **いいえ。** 差替前のバックアップにも同ブロックが存在。差替は無関係 |
| テーマ移行（TCD→SWELL）との関係 | **あり。** 本番(TCD NANO)では `<script>` で正しく動作。ステージング(SWELL)移行の過程で `<script>` が剥がれ＋エンティティ符号化され、表示が壊れた |
| 影響ページ数 | **1ページのみ（/application/, post 151）** |
| 推奨修正 | クリーンな mailto リンク（軽い難読化はサーバ側ショートコード/スニペットで）へ置換。CF7フォームが主導線のため副次導線として簡素化 |

---

## 1. 現象の原因

### 1-1. 保存されている中身（post 151 の `wp:html` ブロック・抜粋）
```
<!-- wp:html -->
&lt;!--
function converter(M){
var str=&quot;&quot;, str_as=&quot;&quot;;
for(var i=0;i&lt;M.length;i++){
str_as = M.charCodeAt(i);
str += String.fromCharCode(str_as + 1);
}
return str;
}
function mail_to(k_1,k_2)
{eval(String.fromCharCode(108,111,99,...)) ... }
document.write('<a href="mail_to(&quot;&quot;,&quot;&quot;)">…</a>');
//--&gt;

setsumei-kai@[managed-domain]
<!-- /wp:html -->
```

### 1-2. なぜテキスト表示されるか
1. **`<script>` タグが無い。** 本来は `<script type="text/javascript"> … </script>` で囲まれているべきJSが、囲みタグごと失われている。
2. **HTMLエンティティ符号化されている。** `<` → `&lt;`、`"` → `&quot;`、`'` → `&#039;`（例: `&lt;!--`、`i&lt;M.length`、`&quot;&quot;`）。
3. `wp:html` ブロックは中身を**そのまま出力**する。出力された `&lt;!--…` はブラウザ上で「文字としての `<!--…`」に見える（エンティティはタグ/コメントとして再解釈されない）。`<script>` も無いので **JSは実行されず、コード全体が地の文として表示**される。
4. 末尾の `setsumei-kai@[managed-domain]` も生のまま見えている。

→ つまり**保存データ自体が壊れている**（SWELLが実行時に失敗しているのではなく、ブロックの中身が既に script 無し＋符号化済み）。

### 1-3. コメント記号について
- 保存上は **半角 `<!--`**（エンティティ表記 `&lt;!--`）。画面では `<!--` と表示される。
- ご指摘の「全角ダッシュ `<!–`」は、**画面表示された半角 `<!--` の見た目の誤認**と判断（DB上に全角ダッシュ `–`(U+2013) は存在せず）。

---

## 2. ショートコード差替との切り分け

- 差替前バックアップ `backup/contact_application_pages_pre_cf7_20260624.sql`（差替直前の post_content）にも、**同じ難読化ブロック（converter ×2 / mail_to ×2 / charCodeAt / 「個別説明会希望」）が存在**。
- 6/24の差替は `[mwform_formkey key="1369"]` → `[contact-form-7 id="4804"]` の**フォーム部分のみ**で、難読化ブロックは別の `wp:html` ブロック。
- **結論: ショートコード差替は本事象の原因ではない。元々ページ本文に存在していた問題。**

---

## 3. テーマ移行（TCD NANO → SWELL）との関係

| 環境 | テーマ | 該当ブロックの形態 | 表示 |
|---|---|---|---|
| **本番** pegasus-job.com | TCD NANO 系 | `<h4>メール</h4>` の直後に **`<script type="text/javascript">` `<!--` `function converter…` `//-->` `</script>`**（生のJS・script内） | ✅ **正常に実行**（クリックでメール送信できるリンクとして動作） |
| **ステージング** | SWELL | `wp:html` 内に **`<script>`無し＋エンティティ符号化** | ❌ **コードがテキスト表示** |

- 本番HTMLでは `&lt;!--` の出現が **0**（生のJS）。`function converter` の直前は `<script type="text/javascript">`（**SCRIPT内＝実行される**）であることを確認。
- ステージングでは `&lt;!--` を含み、`<script>` が無い。
- **原因の本質:** TCD NANO 環境では生HTML（script）として保存・出力されていたものが、**SWELL移行に伴う本ページの編集/再保存の過程で WordPress のサニタイズ（KSES）により `<script>` が除去され、内部の `<` `"` `'` がエンティティ符号化された**と考えられる（`unfiltered_html` 権限のない保存経路、または移行ツールの整形による）。
- 難読化JS自体はテーマ非依存（`<script>` で正しく囲めば SWELL でも動く）。よって「SWELLが実行できない」のではなく、「**保存データが script 無し＋符号化で壊れている**」のが直接原因。テーマ移行が引き金。

> ⚠️ **本番への波及予告:** 本番を SWELL へ切り替える際、同じ編集/保存経路を通ると **本番でも同様に壊れる**。本番カットオーバーの内容修正に、本ページの対応を必ず含めること。

---

## 4. 影響範囲（大野様ご指摘の「他ページ」確認）

DB全体（revision/attachment 除く・全post_status）を複数シグネチャで走査：

| シグネチャ | 該当件数 |
|---|---:|
| `function converter` | **1** |
| `String.fromCharCode` | 1 |
| `charCodeAt` | 1 |
| `mail_to(` | 1 |
| `wp:html` 内の `&lt;!--`（script剥がれ痕跡） | 1 |
| 公開pageの `mailto:` / 生`@pegasus-job.com` | 1 |

- 該当はいずれも **post 151（/application/）のみ**。
- **影響ページ数：1ページ。** 他ページへの波及は確認されず。

| ID | URL | タイトル | 状態 |
|---|---|---|---|
| 151 | /application/ | 個別説明会申し込み | publish |

---

## 5. 推奨する修正方針（実装は未着手）

/application/ は **6/24に CF7 申込フォームへ切替済み**で、メール申込はあくまで**副次導線**。よって難読化ブロックは簡素化してよい。

### 案A（推奨）：クリーンな mailto リンクへ置換
- 壊れた `wp:html` ブロックを撤去し、SWELLのボタン/リンクで:
  `メールでお申し込み → mailto:setsumei-kai@[managed-domain]?subject=個別説明会希望`
- 長所: 確実に動く・保守容易・SWELLと相性良。短所: アドレスがHTML上に出る（スパム収集リスク微）。
- 軽い難読化が必要なら、**Code Snippets/ショートコードでサーバ側組み立て**（`[pega_mail]`等）にし、`<script>`を post_content に保存しない形にする（再保存でKSESに剥がされない）。

### 案B：難読化を維持してSWELLで動かす
- 同等のJSを **プラグイン/Code Snippets/ショートコードで出力**（post_content に生scriptを持たせない）。
- 長所: 既存挙動を踏襲。短所: 仕組みが増え保守対象増。`wp:html`への生script直書きはKSESで再び壊れるため不可。

### 案C：ブロック削除（最小）
- CF7フォームがあるため、難読化ブロックを削除し「件名に『個別説明会希望』…」の案内も整理。メール導線を残すなら案Aを併用。

### 影響範囲全体の対応
- 対象は1ページのみのため、**/application/ を個別修正**で完結。
- **本番移行時**：本番カットオーバーで同ページが同様に壊れないよう、移行後の /application/ を案A/Bで整える手順を、移行チェックリストに追加。

---

## 6. まとめ
- **原因:** /application/(151) の wp:html ブロック内の難読化JSが `<script>`無し＋エンティティ符号化で保存され、テキスト表示。コメント記号は半角（全角崩れではない）。
- **ショートコード差替は無関係**（元々の問題・差替前バックアップにも存在）。
- **テーマ移行が引き金**：本番(TCD)は `<script>` で動作、SWELL移行時の保存でscript剥がれ＋符号化。本番もSWELL切替時に同事象が起きる見込み（要・移行チェックリスト反映）。
- **影響:1ページのみ（/application/）。**
- **推奨:** 案A（クリーンmailto、必要なら軽難読化はサーバ側ショートコード）。実装は方針確定後に別途。

---

## 7. 修正の実施記録（2026年6月25日・案A適用）

**ステータス:** ステージングで修正完了。本番は無変更。

### 採用方式：サーバー側ショートコード（KSES耐性）
- Code Snippets にショートコード **`[pega_setsumei_mail]`**（id=7「メールリンク(個別説明会・antispambot)」/ scope=global / active）を追加。
- 出力：`<a href="mailto:（antispambot符号化アドレス）?subject=個別説明会希望">ここをクリックすると直接メールが送れます</a>`
  - WordPress標準 `antispambot()` で**メールアドレスをHTMLエンティティ符号化**（JS不要・確実に動作・軽いスパム対策）。
  - 件名は `個別説明会希望` を `rawurlencode`。
- post 151 の本文：壊れた `<!-- wp:html -->…<!-- /wp:html -->` ブロック**のみ**を、`<!-- wp:shortcode -->[pega_setsumei_mail]<!-- /wp:shortcode -->` に置換。CF7（id=4804）・案内文「件名に『個別説明会希望』…」・見出し等の他要素は無変更。
- **生のメールアドレス／`<script>` を post_content に保存しない**ため、Gutenbergで再保存しても KSES に壊されない。

### なぜ直書きでなくショートコードか
- 直書き `mailto:` は post_content にメールアドレスが露出し、再保存時に整形され得る。サーバー側組み立てなら、本文には `[pega_setsumei_mail]` のみ＝KSESの影響を受けず、アドレスは PHP（スニペット）側に一元管理。

### 動作確認結果（ステージング）
| 項目 | 結果 |
|---|---|
| /application/ HTTP | 200 |
| 難読化コードのテキスト表示 | **消滅**（function converter / charCodeAt / `&lt;!--` = 各0） |
| mailto リンク出力 | ✅ `href="mailto:…(antispambot)…?subject=個別説明会希望"`、文言「ここをクリックすると直接メールが送れます」 |
| ショートコードのリテラル残り | 0（実行済み） |
| 案内文「件名に『個別説明会希望』」 | 維持 |
| CF7 申込フォーム（f4804・venue） | ✅ 引き続き正常表示 |
| post_content の安全性 | 生メール=0 / converter=0 / `[pega_setsumei_mail]`=1（KSES耐性） |
| サイト無破損 | /application/・/contact/・/・/homeworker/・/news/ すべて200 |
| 全DBの難読化残存 | **0件** |

- 実施前バックアップ：`backup/application_page_pre_mailfix_20260625.sql`（post 151 本文・gitignore対象）。

---

## 8. 本番カットオーバー用 手順（移行チェックリスト項目）

> 本番(TCD NANO)では現在 `<script>` 直書きで正常動作しているが、**本番を SWELL へ切り替えると /application/ の同ブロックが script 剥がれ＋エンティティ符号化で壊れる**。本番移行時に必ず以下を適用すること。

**チェックリスト項目（/application/ メールリンク）**
1. 本番DBを SWELL 用に移行後、`/application/`（該当 post）に難読化スクリプトが**テキスト表示されていないか**を確認（`function converter` / `&lt;!--` の有無）。
2. ショートコード方式を本番にも適用：
   - 本ステージングの Code Snippets スニペット「メールリンク(個別説明会・antispambot)」を**本番にもインポート/作成**（コードは本書 §7 のとおり。アドレスは `setsumei-kai@[managed-domain]`）。
   - 本番 `/application/` 本文の壊れた `wp:html` ブロックを `<!-- wp:shortcode -->[pega_setsumei_mail]<!-- /wp:shortcode -->` に置換。
3. 動作確認：難読化テキスト非表示・mailtoリンク動作・CF7表示・ページ200。
4. 併せて、他ページに同種ブロックが無いか再走査（移行時点のDBで `function converter` 等を検索。本調査時点ではステージング上 /application/ のみ）。

> 補足：このスニペットは汎用化容易（宛先・件名・文言を引数化すれば他フォーム/拠点にも転用可）。本番では必要に応じてパラメータ化を検討。
