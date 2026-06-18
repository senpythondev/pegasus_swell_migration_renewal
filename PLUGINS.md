# 導入プラグイン記録

このプロジェクトでは、サードパーティのプラグイン本体は `.gitignore` で除外しています(`wp_data/wp-content/plugins/`)。代わりに本ドキュメントを「どのプラグインを、いつ、何のために入れたか」の正本(source of truth)とします。

最終更新: 2026-06-18

---

## Phase 1 — リニューアル基盤として導入済み(5 プラグイン)

ステージング環境 (`pegasus_wp` Docker コンテナ) の WordPress 管理画面からインストール済み。バージョンは `wp_data/wp-content/plugins/<slug>/*.php` のヘッダから取得。

| # | プラグイン名 | スラッグ | バージョン | 役割 | 利用フェーズ |
|---|------|---|---|---|---|
| 1 | SEO SIMPLE PACK | `seo-simple-pack` | 3.7.0 | 既存サイトの Yoast SEO からの移行先。タイトル/メタディスクリプション/OGP を軽量に管理 | Phase 2(SEO データ移行 + 新サイトの SEO 設定) |
| 2 | Contact Form 7 | `contact-form-7` | 6.1.6 | 既存の MW WP Form からの移行先。お問い合わせ / 個別説明会申込 / 資料請求の各フォーム | Phase 2(フォーム再構築) |
| 3 | Custom Post Type UI | `custom-post-type-ui` | 1.19.2 | 旧サイトの CPT(`news` / `reports` / `works` / `service` / `company`)と関連タクソノミーを新サイト構成に再定義 | Phase 2(コンテンツ構造の再設計) |
| 4 | Redirection | `redirection` | 5.8.0 | 旧 URL → 新 URL の 301 リダイレクトを管理。`user-dicision1` → `user-decision1` 等のタイポ修正、CPT 構造変更に伴うパス変更を一括設定 | Phase 3(リニューアル切替時の URL 維持) |
| 5 | Code Snippets | `code-snippets` | 3.9.6 | `functions.php` 直書きを避け、機能追加スニペットを安全に集約。SWELL 子テーマ更新と疎結合に保つ | Phase 2 〜 Phase 3(横断的に利用) |

### 移行関係まとめ

| 旧 → 新 | 移行元(旧サイト) | 移行先(新サイト) | 備考 |
|---|---|---|---|
| SEO メタ | Yoast SEO | **SEO SIMPLE PACK** | Yoast 独自メタフィールドからの値抽出が必要 |
| お問い合わせフォーム | MW WP Form(5 件の `mw-wp-form` CPT) | **Contact Form 7** | 送信先メール、送信内容ログ、確認/完了ページの構成を再現 |
| カスタム投稿タイプ | テーマ/プラグインからのコード定義(現サイトは `pegasus` テーマ等で定義) | **Custom Post Type UI** | 管理画面で定義 → エクスポート JSON を別途保存予定 |

---

## 今後追加予定(候補)

確定していないものは TBD で記録。決まった時点で本ファイルを更新。

| プラグイン名 | 役割 | 状態 | 想定フェーズ |
|---|---|---|---|
| (TBD) Cache / Performance 系 | ページキャッシュ・画像最適化 | 検討中(現状の WP-Optimize 継続 or 別プラグインに乗り換え) | Phase 3 |
| (TBD) セキュリティ系 | 管理画面ログイン制限・ファイル変更検知 | 未検討 | Phase 3 |
| (TBD) バックアップ系 | 現在 UpdraftPlus 利用中、新サイトでも継続するか判断 | 検討中 | Phase 3 |

---

## ポリシー

1. **プラグイン本体は git に入れない**(`.gitignore: wp_data/wp-content/plugins/`)。WordPress 管理画面または公式リポジトリからインストール。
2. **本ドキュメントが導入履歴の正本**。新規導入・アップデート・削除の都度、表を更新してコミットする。
3. **有料プラグインを導入する場合**は、ライセンス情報の保管場所(共有のパスワードマネージャ等)も本ファイルに追記する。
4. **設定値**(CPT定義 / リダイレクトルール / フォーム定義 / コードスニペット)は、可能なものは JSON 等でエクスポートし、本リポジトリ配下 `docs/` か `config/` に保存する(別途整備予定)。

---

## バージョン確認の再現コマンド

```bash
cd wp_data/wp-content/plugins
for p in seo-simple-pack contact-form-7 custom-post-type-ui redirection code-snippets; do
  ver=$(grep -m1 -iE "^[[:space:]]*\*?[[:space:]]*Version:" "$p"/*.php | head -1 | sed -E 's/.*Version:[[:space:]]*//')
  echo "$p: $ver"
done
```
