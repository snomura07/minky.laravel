# minky.laravel
## 初期化手順

1. 依存パッケージインストール
```bash
composer install
```

3. `.env` 準備（初回のみ）

```bash
cp src/.env.example src/.env
```

4. アプリキー生成（初回のみ）

```bash
php artisan key:generate
```

5. マイグレーション実行

```bash
php artisan migrate --force
```

6. 都市マスタ投入（東京・福井）

```bash
php artisan db:seed --class=Database\\Seeders\\CitySeeder --force
```

7. 動作確認用データ投入（任意）

```bash
php artisan weather:seed-daily --days=30
```

8. 画面確認

- `http://localhost:8088/dashboard`
- 任意日を1日指定する場合: `http://localhost:8088/dashboard?date=2026-02-14`

## バッチコマンド
- 現在値取得（`cities` 全件）
```bash
php artisan weather:fetch-current
```

- 日次集計（`cities` 全件）
```bash
php artisan weather:aggregate-daily
```

- 単一都市で日次集計（`city_id` 指定）
```bash
php artisan weather:aggregate-daily --city-id=2
```

## 権限エラー対処（Laravel `storage` / `bootstrap/cache`）
`file_put_contents(.../storage/framework/views/...): Failed to open stream: Permission denied` が出る場合は、コンテナ内の書き込み権限を修正してください。

```bash
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R ug+rwX /var/www/html/storage /var/www/html/bootstrap/cache

php artisan view:clear
php artisan cache:clear
```
