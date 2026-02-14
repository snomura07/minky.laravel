# minky Laravel 再実装メモ

## 概要
- 旧 `minky` を Laravel 12 + PHP 8.3 で再実装しました。
- レイヤーは `Model -> Repository -> Action -> Controller -> View` に統一しています。
- ダッシュボードは福井の気温可視化（過去1か月の推移、当日の最高/最低）を表示します。

## ディレクトリ
- `src/`: Laravel アプリ本体
- `docker/`: Dockerfile / docker-compose / Nginx設定
- `docker/php-fpm/www.conf`: php-fpm プール設定（外出し）
- `docker/php/conf.d/custom.ini`: php.ini 追加設定（外出し）
- `db/`: MySQL データ永続化マウント先
- `docs/`: ドキュメント

## 主な実装
- モデル
  - `src/app/Models/WeatherReport.php`
  - `src/app/Models/DailyWeatherStat.php`
- リポジトリ
  - `src/app/Repositories/WeatherReportRepository.php`
  - `src/app/Repositories/DailyWeatherStatRepository.php`
- アクション
  - `src/app/Actions/WeatherReportAction.php`
  - `src/app/Actions/DailyWeatherStatAction.php`
- コントローラー
  - `src/app/Http/Controllers/DashboardController.php`
- ビュー
  - `src/resources/views/dashboard.blade.php`

## バッチ相当（artisan command）
- `weather:fetch-current`
  - Open-Meteo API から現在値を取得して `weather_reports` に保存
  - 保存後に Discord Webhook へ通知
- `weather:aggregate-daily`
  - `weather_reports` を日次集計して `daily_weather_stats` に保存
  - 保存後に Discord Webhook へ通知
- `weather:seed-daily`
  - 画面確認用に `daily_weather_stats` のテストデータを投入

## 起動手順
1. `docker compose -p minky_laravel -f docker/docker-compose.yml up -d --build`
2. `docker compose -p minky_laravel -f docker/docker-compose.yml exec app composer install`
3. `docker compose -p minky_laravel -f docker/docker-compose.yml exec app php artisan migrate`
4. `docker compose -p minky_laravel -f docker/docker-compose.yml exec -u root app sh -lc "chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache && chmod -R ug+rwx /var/www/html/storage /var/www/html/bootstrap/cache"`
5. 必要なら `docker compose -p minky_laravel -f docker/docker-compose.yml exec app php artisan weather:seed-daily`
6. `http://localhost:8088/dashboard` を開く

## php-fpm 設定外出し
- `docker/php-fpm/www.conf` を `app` コンテナにマウントしています。
- 設定変更時は `docker compose -p minky_laravel -f docker/docker-compose.yml up -d --build` で再作成してください。

## php.ini 設定外出し
- `docker/php/conf.d/custom.ini` を `app` コンテナにマウントしています。
- `memory_limit`, `max_execution_time`, `upload_max_filesize` などをここで管理します。

## Discord 通知設定
- 旧 `DiscodeAction` 相当を `src/app/Actions/DiscodeAction.php` として移植済みです。
- 通知先は次の優先順で解決します。
  1. `DISCORD_WEBHOOK_URL`
  2. `DISCODE_ID` + `DISCODE_TOKEN`
- 未設定時はコマンド本体は継続し、通知のみスキップします。
