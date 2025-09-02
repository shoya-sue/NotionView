# VPS環境セットアップ手順

## 環境情報
- OS: AlmaLinux
- PHP: 8.2.29
- PostgreSQL: 15.12
- Apache: 2.4.62

## セットアップ手順

### 1. Composerインストール（未インストールの場合）
```bash
cd ~
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer
```

### 2. Laravelプロジェクト作成
```bash
cd /var/www/html  # または適切なディレクトリ
composer create-project laravel/laravel notion-viewer
cd notion-viewer
```

### 3. 必要なPHP拡張モジュール確認
```bash
php -m | grep -E "(pdo|pgsql|mbstring|openssl|tokenizer|xml|ctype|json)"
```

不足している場合：
```bash
sudo dnf install php-pdo php-pgsql php-mbstring php-xml php-json
```

### 4. Laravel Notion APIパッケージインストール
```bash
composer require 5am-code/laravel-notion-api
```

### 5. PostgreSQLデータベース作成
```bash
sudo -u postgres psql
```

PostgreSQL内で：
```sql
CREATE DATABASE notion_viewer;
CREATE USER notion_user WITH PASSWORD 'your_password';
GRANT ALL PRIVILEGES ON DATABASE notion_viewer TO notion_user;
\q
```

### 6. 環境変数設定
```bash
cp .env.example .env
nano .env
```

以下を設定：
```env
APP_NAME="Notion Viewer"
APP_URL=http://your-vps-domain.com

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=notion_viewer
DB_USERNAME=notion_user
DB_PASSWORD=your_password

NOTION_API_TOKEN=your_notion_integration_token
NOTION_DATABASE_ID=your_notion_database_id
```

### 7. Laravelセットアップ
```bash
php artisan key:generate
php artisan migrate
php artisan storage:link
```

### 8. ディレクトリ権限設定
```bash
sudo chown -R apache:apache storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### 9. Apache設定
```bash
sudo nano /etc/httpd/conf.d/notion-viewer.conf
```

以下を追加：
```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /var/www/html/notion-viewer/public
    
    <Directory /var/www/html/notion-viewer/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog /var/log/httpd/notion-viewer-error.log
    CustomLog /var/log/httpd/notion-viewer-access.log combined
</VirtualHost>
```

### 10. Apache再起動
```bash
sudo systemctl restart httpd
```

### 11. SELinux設定（有効な場合）
```bash
sudo setsebool -P httpd_can_network_connect_db 1
sudo semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/html/notion-viewer/storage(/.*)?"
sudo semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/html/notion-viewer/bootstrap/cache(/.*)?"
sudo restorecon -Rv /var/www/html/notion-viewer
```

## Notion API設定

### 1. Integration作成
1. https://www.notion.com/my-integrations にアクセス
2. 「New integration」をクリック
3. 名前を設定（例：Laravel Notion Viewer）
4. Capabilitiesで「Read content」を有効化
5. 「Submit」で作成

### 2. Token取得
作成したIntegrationの「Internal Integration Token」をコピー

### 3. Notionページ/データベースに統合を追加
1. 表示したいNotionページ/データベースを開く
2. 右上の「...」メニュー → 「Connections」
3. 作成したIntegrationを検索して追加

### 4. Database ID取得
NotionデータベースのURLから取得：
```
https://www.notion.so/workspace/xxxxxx?v=yyyyyy
                                ^^^^^^ この部分がDatabase ID
```

## 動作確認
```bash
# ブラウザでアクセス
http://your-vps-domain.com
```

## トラブルシューティング

### 500エラーの場合
```bash
# ログ確認
tail -f /var/www/html/notion-viewer/storage/logs/laravel.log
tail -f /var/log/httpd/notion-viewer-error.log

# 権限再設定
sudo chown -R apache:apache /var/www/html/notion-viewer
sudo chmod -R 775 storage bootstrap/cache
```

### データベース接続エラー
```bash
# PostgreSQL状態確認
sudo systemctl status postgresql
```

### Composer権限エラー
```bash
sudo chown -R $(whoami):$(whoami) ~/.composer
```