# mailpit 外部公開ログ（検収用・Basic認証付きサブパス）

**実施日:** 2026年6月26日
**対象環境:** ステージングのみ（Docker / `pegasus_wp`）。本番（pegasus-job.com）は無変更。
**性質:** **検収用の一時公開**（橋本様がフォーム送信メールを確認するため）。**ステージング専用・本番には載せない。**
**要件:** Basic認証なしでは絶対にアクセスさせない（テストメール内容＝個人情報の保護）。WordPress（8080）の既存Basic認証・フォーム動作に影響させない。phpMyAdmin・SMTPは引き続き外部遮断。

> パスワードは本書に平文記載しない（チャットで大野様に伝達・別管理）。`.htpasswd` は `.gitignore` 済み。

---

## 0. 結論サマリー

| 項目 | 結果 |
|---|---|
| 公開URL | **http://220.158.21.114:8080/mailpit/**（8080のサブパス） |
| Basic認証 | ✅ 必須。認証なしは **401**（外部実測で確認）。サイトと同じ `.htpasswd`（ユーザー `pegasus`） |
| 方式 | nginx リバースプロキシ（8080サーバ配下の `location /mailpit/`）→ `mailpit:8025`（Dockerネットワーク経由） |
| mailpit機能 | ✅ 一覧・詳細・**WebSocketライブ更新（101）**・資料DL URL→PDF取得 すべて正常 |
| phpMyAdmin / SMTP | ✅ 外部遮断のまま（変更なし） |
| WordPress(8080) | ✅ 影響なし（認証・フロント・wp-admin・フォーム正常） |

---

## 1. 重要な発見：当VPSは上位ファイアウォールで **8080のみ外部許可**

当初、mailpit専用に新ポート **8026** を公開する方式を実装したが、**外部からの実測で 8026 に到達できないこと**が判明：

| ポート | 外部到達（VPS外からの実測） |
|---:|---|
| 8080 | ✅ 到達（401を返す＝reachable） |
| 8026（新規公開を試行） | ❌ 到達不可（接続できず） |

- ホスト側では `0.0.0.0:8026` でLISTENしていたが、**上位（プロバイダ/クラウド）ファイアウォールが 8080 以外を遮断**しているため、新ポートは外部公開できなかった。
- → **方式を変更**：外部到達が確実な **8080 のサブパス `/mailpit/`** で公開（8026は撤去）。これなら確実に橋本様が到達でき、かつ8080の既存Basic認証で保護される。

> 本番移行とは無関係のステージング固有事情（VPSのポート制約）。記録として残す。

---

## 2. 実施前バックアップ

- `backup/docker-compose.yml.pre_mailpitpub_20260626`
- `backup/nginx.conf.pre_mailpitpub_20260626`

ロールバック：両ファイルを戻し `docker compose up -d mailpit nginx` で、mailpit外部非公開（ループバック限定）の状態に復帰。

---

## 3. 適用した変更

### 3-1. mailpit（`docker-compose.yml`）：サブパス配信のため webroot 設定
```yaml
  mailpit:
    environment:
      MP_WEBROOT: /mailpit   # UI/API/WebSocket を /mailpit 配下で配信(アセット絶対パス・WSがサブパスで正しく解決)
    ports:
      - "127.0.0.1:8025:8025"   # ホスト公開は従来どおりループバック限定(SSHトンネル用)
```
- mailpit のホスト公開ポート（8025）は**ループバック限定のまま**（外部公開はしない）。外部公開は nginx 経由のみ。

### 3-2. nginx（`nginx.conf`）：8080サーバ配下に `/mailpit/` プロキシ
```nginx
# http コンテキスト先頭(WebSocketアップグレード用)
map $http_upgrade $connection_upgrade { default upgrade; '' close; }

server {
    listen 80;                       # ホスト8080
    …
    auth_basic "Pegasus Staging (Kensho)";        # ← サーバレベル認証が /mailpit/ にも適用
    auth_basic_user_file /etc/nginx/.htpasswd;
    …
    location ^~ /mailpit/ {
        proxy_pass http://mailpit:8025;           # Dockerネットワーク経由(ホスト127.0.0.1:8025とは別経路)
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_http_version 1.1;                   # WebSocket
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection $connection_upgrade;
        proxy_read_timeout 3600s;
    }
}
```
- **Basic認証は8080サーバのサーバレベル設定をそのまま継承** → `/mailpit/` も認証必須（個別設定不要・確実）。
- `^~` で正規表現（php/静的）ロケーションより優先。WordPress の処理に回らない。
- 認証情報は**サイトと同一の `.htpasswd`**（ユーザー `pegasus`）。橋本様は1組のID/パスワードでサイトもmailpitも閲覧可。

### 3-3. 反映
- `docker compose up -d mailpit nginx`（mailpitはwebroot反映、nginxは設定反映のため再作成）。`nginx -t` OK。**db/wordpress は無停止**。
- ※ mailpit はメモリ保持のため再作成で過去のテストメールはクリア。検収用にデモ送信を再投入済み（§4）。

---

## 4. 動作確認結果

### 4-1. Basic認証（保護）
| テスト | 結果 |
|---|---|
| 認証なし `/mailpit/`（ローカル） | **401** |
| 認証なし `/mailpit/`（**外部VPS外から実測**） | **401 Unauthorized**（reachable かつ保護） |
| 認証なし WebSocket `/mailpit/api/events` | **401** |
| 認証あり `/mailpit/` | **200**（`<title>Mailpit</title>`） |

→ **認証なしでは一切表示されない**（テストメール内容を保護）。

### 4-2. mailpit機能（認証あり）
| 機能 | 結果 |
|---|---|
| UI表示・アセット | ✅ 200。アセットが `/mailpit/dist/app.js`・`/mailpit/dist/app.css`・`/mailpit/favicon.svg` と**サブパス配下で解決**（MP_WEBROOT有効） |
| メール一覧 API（`/mailpit/api/v1/messages`） | ✅ 6件表示 |
| **WebSocket ライブ更新（`/mailpit/api/events`）** | ✅ **HTTP 101**（リアルタイム更新が通る） |
| 資料請求 自動返信の **ダウンロードURL → PDF取得** | ✅ **200 / application/pdf / 1,756,127バイト** |

検収用デモメール（6件）：
| 件名 | 宛先 |
|---|---|
| 【お問い合わせ】検収デモ 様 | info@pegasus-job.com |
| お問い合わせありがとうございました。 | （入力アドレス） |
| 【個別説明会お申込み】検収デモ 様 | setsumei-kai@pegasus-job.com |
| 個別説明会のお申し込みありがとうございました。 | （入力アドレス） |
| 【資料請求】… | info@pegasus-job.com |
| 【ペガサス】資料ダウンロードのご案内 | （入力アドレス・本文にDL URL） |

### 4-3. 既存サービスへの影響（なし）
| 項目 | 結果 |
|---|---|
| WordキエンドBasic認証 | ✅ 認証なし401 / 認証あり200（不変） |
| フロント `/`・`/about/`・`/wp-sitemap.xml` | ✅ 200 |
| wp-admin リダイレクト | ✅ 相対 `/wp-admin/`（前回修正維持） |
| CF7フォーム送信 | ✅ mail_sent（mailpit受信） |
| phpMyAdmin(8081) / mailpit-host(8025) | ✅ **127.0.0.1限定のまま**（外部不可） |
| SMTP(1025) | ✅ ホスト非公開のまま |
| 公開ポート | **8080 のみ**（8026は撤去） |

---

## 5. 橋本様に渡すアクセス情報

| 用途 | URL | 認証 |
|---|---|---|
| **テストメール確認（mailpit）** | **http://220.158.21.114:8080/mailpit/** | Basic認証（サイトと同じ：ユーザー `pegasus` ＋ パスワード ※別途） |

- **Basic認証のID/パスワードはサイト閲覧と共通**（1組で両方）。パスワードは本書に記載せず、大野様が安全な経路で橋本様へ通知。
- 補足：mailpit内の資料DLリンクは**サイト側（同じ8080）**を開くため、同じBasic認証で動作（同一ID/パスワード）。
- mailpit はリアルタイム更新（WebSocket）対応。フォーム送信後、画面に自動でメールが現れる。

---

## 6. 本番移行への注意（チェックリスト：ステージング専用・本番不要）
- **本mailpit公開（/mailpit/ プロキシ・MP_WEBROOT・WebSocketマップ）はステージング専用の検収用一時設定。本番には一切載せない。**
- 本番は実SMTP配信のため mailpit 自体が不要。本番の nginx/compose にこれらの設定を持ち込まないこと。
- 本番移行チェックリストには含めない（＝「ステージング専用・本番不要」と明記）。

---

## まとめ
- mailpit を **8080のサブパス `/mailpit/`** で、サイトと同じ **Basic認証付きで外部公開**。認証なしは外部実測で401＝テストメール内容を保護。
- 当VPSは上位FWで8080のみ外部許可のため、新ポート方式（8026）から**サブパス方式に変更**して確実に到達可能に。
- 一覧・詳細・WebSocketライブ更新・資料DL URL→PDF取得まで正常。phpMyAdmin/SMTPは外部遮断のまま、WordPress・フォームに影響なし。**ステージング専用・本番不要。**
</content>
