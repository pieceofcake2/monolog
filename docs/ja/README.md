# Monolog ドキュメント

ログメッセージを [Monolog](https://github.com/Seldaek/monolog) のハンドラーに送る CakePHP 2 のログエンジンです。
フォーマッターとプロセッサーは `CakeLog::config()` で設定します。

CakePHP には高度なロギングの仕組みがありますが、ほかの方法でログを扱う (ファイルのローテーション、Logstash、
メールでの通知など) には、さらに多くのコードが必要です。書くコードをできるだけ少なくするために、このプラグインは
よく使われている Monolog ライブラリを使います。

- [インストール](#installation)
- [設定](#configuration)
- [高度な設定](#advanced-configuration)
- [CakeEmail ハンドラー](#cakeemail-handler)

<a id="installation"></a>

## インストール

[Composer](https://getcomposer.org/) でプラグインをインストールします:

```bash
composer require pieceofcake2/monolog
```

依存パッケージとして Monolog (`monolog/monolog` 2.x または 3.x) もインストールされます。

このプラグインは `cakephp-plugin` タイプなので、`composer/installers` によってプラグインのディレクトリに
(`Monolog` として) インストールされます。正しい場所にインストールされるように、アプリケーションの
`composer.json` で `installer-paths` を設定してください:

| 構成 | `installer-paths` |
| --- | --- |
| モダン (`config/`、`src/`、`templates/`、`plugins/`) | `"plugins/{$name}/": ["type:cakephp-plugin"]` |
| レガシー (`app/Config/`、`app/Plugin/`) | `"app/Plugin/{$name}/": ["type:cakephp-plugin"]` |

<a id="configuration"></a>

## 設定

まず、ログの設定ファイル `config/log.php` (レガシー構成では `app/Config/log.php`) を作成し、
`config/bootstrap.php` (レガシー構成では `app/Config/bootstrap.php`) の早い段階で読み込みます:

```php
require_once CONFIG . 'log.php';
```

CakePHP と同じことを Monolog で行う基本的な設定は次のようになります:

```php
CakePlugin::load('Monolog');

CakeLog::config('debug', [
    'engine' => 'Monolog.Monolog',
    'channel' => 'app',
    'handlers' => [
        'Stream' => [
            LOGS . 'debug.log',
            'formatters' => [
                'Line' => ["%datetime% %channel% %level_name%: %message%\n"],
            ],
        ],
    ],
]);
```

オプション:

* `channel`: Monolog のチャンネルの名前 (デフォルトは `monolog`)
* `handlers`: ロガーに追加するハンドラー (デフォルト: `LOGS . 'monolog.log'` に書き込む `Stream` ハンドラー)
* `processors`: ロガーに追加するプロセッサー

ハンドラー、フォーマッター、プロセッサーは、Monolog での短い名前で指定します。`Stream` は
`Monolog\Handler\StreamHandler` を、`Line` は `Monolog\Formatter\LineFormatter` を、`MemoryUsage` は
`Monolog\Processor\MemoryUsageProcessor` を作成します。値はコンストラクターの引数で、順番どおりに渡されます。
ハンドラーには、それぞれ独自の `formatters` と `processors` を指定できます。`types` や `scopes` などの CakePHP の
標準のオプションは、通常どおり使えます。

<a id="advanced-configuration"></a>

## 高度な設定

それでは本格的なロギングをしてみましょう。そうでなければ、デフォルトの
[CakePHP のロギング](https://pieceofcake2.github.io/docs/ja/core-libraries/logging.html) の仕組みから
わざわざ移行する意味がありません。

次の例では、以下を設定します:

* 30 日間保持され、[Logstash](https://www.elastic.co/logstash) で読める、メモリ使用量のピークの情報付きの
  ローテーションするログ
* リクエストについてずっと詳しい情報を含む通常のログファイル
* エラーメッセージだけを含むメールでの通知 ([CakeEmail ハンドラー](#cakeemail-handler) を参照)

```php
CakeLog::config('logstash', [
    'engine' => 'Monolog.Monolog',
    'channel' => 'app',
    'handlers' => [
        'RotatingFile' => [
            LOGS . 'logstash.log',
            30,
            'formatters' => [
                'Logstash' => ['web', env('SERVER_ADDR')],
            ],
            'processors' => ['MemoryPeakUsage'],
        ],
        'Stream' => [
            LOGS . 'app.log',
            'formatters' => [
                'Line' => ["%datetime% %channel% %level_name%: %message% %context% %extra%\n"],
            ],
            'processors' => ['MemoryUsage', 'Web'],
        ],
        'CakeEmailHandler' => [
            'admin@example.com',
            'ALERT: APPLICATION REQUIRES IMMEDIATE ATTENTION.',
            'default',
            'search' => CakePlugin::path('Monolog') . 'Lib' . DS . 'Log' . DS . 'Handler',
        ],
    ],
]);
```

<a id="cakeemail-handler"></a>

## CakeEmail ハンドラー

このプラグインには、`CakeEmail` でログメッセージを送信する Monolog のメールハンドラー `CakeEmailHandler` が
同梱されています。引数は次のとおりです:

1. 宛先
2. 件名
3. 使用するメールの設定 (デフォルトは `default`)
4. 最小のレベル (デフォルトは `Logger::ERROR`)
5. メッセージをスタックの上位に伝播させるかどうか (デフォルトは `true`)

これは Monolog の一部ではないので、上の例のように、ハンドラー名に完全なクラス名を使い、`search` にクラスの
ディレクトリを設定してください。`CakeEmail` 以外のクラスでメールを送信するには、`Email.classname` を設定します:

```php
Configure::write('Email.classname', 'MyEmail');
```

<a id="origin"></a>

## 由来

このパッケージは、[jadb/cakephp-monolog](https://github.com/jadb/cakephp-monolog) (Packagist では
`jadb/cakephp-monolog`、最後のアップストリームのバージョンは 2.0.0) を保守しているフォークで、元々は Jad Bitar によるものです。
モダンな PHP で動く CakePHP 2 のために [pieceofcake2](https://github.com/pieceofcake2) が保守しています。
元のコードからの変更点は
[フォーク以降のコミット履歴](https://github.com/pieceofcake2/monolog/compare/73bfc43...2.x)
を参照してください。
