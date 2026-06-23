# CPT 定義(Custom Post Type UI 再構築用)

SWELL 切替で失われた CPT を Custom Post Type UI で再構築した際の定義一式。

## ファイル
| ファイル | 内容 |
|---|---|
| `cptui_post_types.json` | CPT定義3種（news / reports / works）。CPTUI「ツール → インポート/エクスポート」の **投稿タイプ** 欄にそのまま貼り付け可 |
| `cptui_taxonomies.json` | タクソノミー定義2種（news_category / reports_cat）。同 **タクソノミー** 欄に貼り付け可 |
| `rebuild_cpt.php` | 上記をDBへ書き込む再現スクリプト（CPTUIオプション + rewrite_rules クリア） |

## 重要：rewrite slug は本番準拠
| CPT | ラベル | rewrite slug | アーカイブURL |
|---|---|---|---|
| news | お知らせ | `news` | /news/ |
| reports | 職場実習レポート | `reports` | /reports/ |
| **works** | **代表コラム** | **`column`** | **/column/**（/works/ ではない） |

> works は本番が `/column/` 配下のため、URL保全のうえで rewrite slug を **column** にしています。
> タクソノミー: news→news_category、reports→reports_cat、works→なし（標準category非使用）。

## 再適用の手順（別環境や再構築時）
### A. スクリプトで
```bash
sg docker -c "docker compose cp scripts/cpt_definitions/rebuild_cpt.php wordpress:/tmp/rebuild_cpt.php"
sg docker -c "docker compose exec -T -u www-data wordpress php /tmp/rebuild_cpt.php"        # ドライラン
sg docker -c "docker compose exec -T -u www-data wordpress php /tmp/rebuild_cpt.php apply"  # 実行
# rewrite 再生成（フロントへ1回アクセス、または 設定→パーマリンク を保存）
curl -s -o /dev/null -H 'Host: 220.158.21.114:8080' http://localhost:8080/
```
### B. 管理画面で
CPTUI → ツール → インポート/エクスポート に、各JSONを貼り付けてインポート → 設定 → パーマリンク を保存。

## 注意
- 投稿データ（news/reports/works の投稿）には触れません。定義（オプション）と rewrite_rules のみ。
- ステージング専用。本番環境では実行しないこと。
