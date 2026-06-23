# CPT再構築レポート（news / news_category / reports / works）

**実施日:** 2026年6月23日
**対象環境:** ステージングのみ（Docker / `pegasus_wp`）。本番（pegasus-job.com）は**参照（GET）のみ**で変更なし。
**該当工程:** 提案書 Phase 2「CPT再構築・全URL動作検証」
**手段:** Custom Post Type UI 1.19.2（オプション `cptui_post_types` / `cptui_taxonomies` を本番準拠で構築 → rewrite再生成）

---

## 0. 結論（サマリー）

SWELL切替で失われていた CPT を再構築し、404だったCPT系URLがすべて200に復活しました。投稿データは一切変更していません。

| 項目 | 結果 |
|---|---|
| 再構築したCPT | news / reports / works（＋必要なタクソノミー news_category / reports_cat） |
| アーカイブ | /news/・/reports/・**/column/** すべて 200 |
| 個別記事 | news/reports/works 各サンプル 200 |
| タクソノミー | /news_category/event/ 200・/reports_cat/it/ 200 |
| 投稿データ | 変更なし（news 38+4 / reports 16 / works 29 のまま） |
| 既存公開ページ | 無破損（/・/homeworker/・/about/ ほか 200） |

> **要確認ポイント（重要）:** works の rewrite slug は、ご指示の「works」ではなく **「column」** にしました。理由は §2 を参照。

---

## 1. 事前調査の結果

### 本番サイト（REST API / 実URLで確認）
| CPT | 本番ラベル | rewrite（実URL） | has_archive | taxonomies |
|---|---|---|:---:|---|
| news | お知らせ | `/news/`（200） | true | news_category |
| reports | 職場実習レポート | `/reports/`（200） | true | reports_cat |
| works | **代表コラム** | **`/column/`（200）**／`/works/`は**404** | true | （なし） |

- news / news_category は REST 非公開（`show_in_rest=false`）のため REST types では取得できず、実URLの挙動で確認。
- reports_cat: 本番 hierarchical=true、types=[reports]。

### ステージングDB（投稿は現存・CPT定義のみ欠落）
| post_type | 件数 | 紐づくタクソノミー |
|---|---|---|
| news | 38 publish ＋ 4 draft | news_category（infomation 32 / event 4） |
| reports | 16 publish | reports_cat（11ターム / 16紐付き） |
| works | 29 publish | （独自タクソノミーなし。標準category に1件のみ残存） |

→ 投稿・タームはすべて残存。CPT/タクソノミー定義が無いため404になっていただけ、と確認。

---

## 2. 再構築した各CPTの設定内容

すべて public=true / has_archive=true / query_var=true / show_in_rest=true（編集利便のため）。投稿データは不変。

### CPT
| post_type | ラベル | rewrite slug | hierarchical | taxonomies | supports |
|---|---|---|:---:|---|---|
| news | お知らせ | `news` | false | news_category | title, editor, thumbnail, excerpt, revisions, author |
| reports | 職場実習レポート | `reports` | false | reports_cat | 同上 |
| works | 代表コラム | **`column`** | false | （なし） | 同上 |

### タクソノミー
| taxonomy | ラベル | rewrite slug | hierarchical | object_types |
|---|---|---|:---:|---|
| news_category | お知らせカテゴリー | `news_category` | true | news |
| reports_cat | 職場実習レポートカテゴリー | `reports_cat` | true | reports |

### ★ ご指示との相違点（works の rewrite slug）
- ご指示では works の rewrite slug を「works」とされていましたが、**本番は `/column/` 配下**（/works/ は本番でも404）です。
- 本案件の核心は**現行URLの保全**であり、ご指示にも「本番と同じ設定」とあるため、**works は rewrite slug を `column` に設定**しました。
- これにより works（代表コラム）29件のURL `/column/{slug}/` がそのまま維持されます。「works」にしていた場合、29件すべてのURLが変わり、SEO資産を失う恐れがありました。
- 結果として **`/works/` は本番同様 404**、**`/column/` が 200** です。この方針でよいか、念のためご確認ください。

### ★ スコープ追加（reports_cat）
- ご指示の対象は news / news_category / reports / works でしたが、**reports は reports_cat に依存**（本番 types=[reports]、16件紐付き）しているため、reports を本番同等にするには reports_cat の再構築が必須でした。あわせて再構築しています。
- works は独自タクソノミーが無いため、タクソノミー追加はありません。

---

## 3. 動作確認結果（HTTPステータス）

ステージング nginx（8080）へ、本番ホスト名を Host ヘッダーで指定して確認。

### アーカイブ
| URL | 結果 |
|---|:---:|
| /news/ | **200**（お知らせ一覧を表示） |
| /reports/ | **200** |
| /column/ | **200**（works＝代表コラム一覧。13リンク確認） |
| /works/ | 404（本番と一致。rewrite=column のため） |

### 個別記事（各CPTサンプル）
| URL | 結果 | URL | 結果 |
|---|:---:|---|:---:|
| /news/program202404/ | 200 | /reports/report1/ | 200 |
| /news/news20240119/ | 200 | /reports/report8/ | 200 |
| /news/news-20220405/ | 200 | /reports/report16/ | 200 |
| /column/20231114_daihyoublog/ | 200 | /column/20221216/ | 200 |
| /column/20180105/ | 200 | | |

### タクソノミー
| URL | 結果 | 備考 |
|---|:---:|---|
| /news_category/event/ | **200** | リダイレクト無しの実ターム。news_category が機能していることの確証 |
| /reports_cat/it/ | **200** | reports 3件を列挙 |
| /news_category/infomation/ | **301 → /news_category/information/** | 後述（タイポ修正の前段） |

### 回帰（既存公開ページ・無破損）
/・/homeworker/・/about/・/program/・/result/・/user-decision1/ … すべて **200**。

---

## 4. news_category の infomation/information について（次ステップへの申し送り）

- 現在 `/news_category/infomation/`（誤綴り）にアクセスすると、**タスク#6で実装済みの301リダイレクトが働き** `/news_category/information/` へ転送されます。
- 転送先 `/news_category/information/` は、**タームのスラッグがまだ `infomation`（term_id=6）のため現状404**です。
- つまり「infomation のままだが200」ではなく、**リダイレクトが先に効くため infomation は301**になります（設計どおり）。news_category タクソノミー自体が機能していることは、リダイレクトの無い **`/news_category/event/` が200** であることで確認しています。
- **次ステップ（term_id=6 のスラッグを infomation → information に修正）** を行うと、`/news_category/information/` が 200 になり、`/news_category/infomation/` → 301 → 200 の流れが完成します。

### 次ステップ（news_category タイポ修正）に向けた準備状況
- 対象は term_id=6（taxonomy=news_category、現slug=infomation、name=お知らせ、32件紐付き）。
- news_category が登録済みになったため、`wp_update_term(6,'news_category',['slug'=>'information'])` が**正常に通る状態**になりました（以前はタクソノミー未登録で「不正なタクソノミー」エラーだった）。
- 転送先（information）の受け皿が整ったので、スラッグ修正だけで完結します。32件の紐付けは term_taxonomy_id 不変のため維持されます。

---

## 5. 成果物・再現性

- `scripts/cpt_definitions/cptui_post_types.json` … CPT定義3種（CPTUIインポート用）
- `scripts/cpt_definitions/cptui_taxonomies.json` … タクソノミー定義2種
- `scripts/cpt_definitions/rebuild_cpt.php` … 再適用スクリプト（ドライラン→apply）
- `scripts/cpt_definitions/README.md` … インポート/再適用手順

定義はCPTUI管理下（管理画面 CPT UI から編集可能）。投稿データには一切手を加えていません。

---

## 6. まとめ

- news / reports / works（＋ news_category / reports_cat）を再構築し、**CPT系URLがすべて200に復活**。
- works は本番準拠で **rewrite=column**（/column/）。/works/ は本番同様404。← 要ご確認。
- 投稿データ無変更、既存公開ページ無破損。
- 次は news_category の infomation→information タイポ修正（受け皿は整備済み）。
