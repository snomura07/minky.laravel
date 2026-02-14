# 目的
レガシー実装していたminkyをlaravelフレームワークで実装しなおす。

# 技術スタック
- フレームワーク
    - Laravel:12
- バックエンド
    - php 8.3
- フロントエンド
    - Blade + javascript
- Webサーバー, APサーバー
    - Nginx + php-fpm


# 動作環境
- docker + docker-compose
- ファイルはDcokerfile内でCOPYせずにdocker-composeからマウントすること
- AP, DBはそれぞれコンテナを分けること
- DBコンテナはコンテナを消してもデータが残るようにマウントしておくこと

# ディレクトリ構成
- minky.laravel
    ├ src：この配下にLaravelの資源を置く
    ├ docker：Docker関連のファイル
    ├ docs：ドキュメント類
    └ db：DBコンテナのマウント先

# 実装レイヤー概要
- model -> repository -> action -> controller -> view
- modelはテーブル構造の定義
- repositoryはテーブル操作
- actionはドメインに関する操作
- contorollerはviewに渡すデータの成型
