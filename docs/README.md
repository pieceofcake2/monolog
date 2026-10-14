# Monolog Documentation

A CakePHP 2 log engine that sends the log messages to [Monolog](https://github.com/Seldaek/monolog)
handlers, with formatters and processors configured in `CakeLog::config()`.

Despite the advanced logging system offered by CakePHP, handling logs in other ways (rotating
files, Logstash, email notifications and so on) needs a lot more code. To write the least code
possible, this plugin uses the popular Monolog library.

- [Installation](#installation)
- [Configuration](#configuration)
- [Advanced Configuration](#advanced-configuration)
- [CakeEmail Handler](#cakeemail-handler)

## Installation

Install the plugin with [Composer](https://getcomposer.org/):

```
composer require pieceofcake2/monolog
```

This also installs Monolog (`monolog/monolog` 2.x or 3.x) as a dependency.

The plugin has the `cakephp-plugin` type, so it is installed into your plugins directory
(as `Monolog`) by `composer/installers`. Set `installer-paths` in your application's
`composer.json` so it is installed into the right place:

| Layout | `installer-paths` |
| --- | --- |
| Modern (`config/`, `src/`, `templates/`, `plugins/`) | `"plugins/{$name}/": ["type:cakephp-plugin"]` |
| Legacy (`app/Config/`, `app/Plugin/`) | `"app/Plugin/{$name}/": ["type:cakephp-plugin"]` |

## Configuration

Start by creating a logging configuration file, `config/log.php` (legacy layout:
`app/Config/log.php`), and include it early in `config/bootstrap.php` (legacy layout:
`app/Config/bootstrap.php`):

```php
require_once CONFIG . 'log.php';
```

A basic configuration, replicating what CakePHP does but with Monolog, looks like this:

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

Options:

* `channel`: name of the Monolog channel (default `monolog`)
* `handlers`: handlers to push to the logger (default: a `Stream` handler writing to
  `LOGS . 'monolog.log'`)
* `processors`: processors to push to the logger

Handlers, formatters and processors are given by their short Monolog names: `Stream` creates a
`Monolog\Handler\StreamHandler`, `Line` a `Monolog\Formatter\LineFormatter` and `MemoryUsage` a
`Monolog\Processor\MemoryUsageProcessor`. The values are the constructor arguments, in order.
A handler can have its own `formatters` and `processors`. The standard CakePHP options such as
`types` and `scopes` work as usual.

## Advanced Configuration

Now let's do some serious logging, otherwise why bother moving away from the default
[CakePHP logging](https://pieceofcake2.github.io/docs/core-libraries/logging.html) system?

The example below sets up:

* rotating logs that are kept for 30 days and readable by [Logstash](https://www.elastic.co/logstash),
  with memory peak usage info
* a normal log file with much more details about the request
* email notifications including only the error message (see [CakeEmail Handler](#cakeemail-handler))

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

## CakeEmail Handler

The plugin ships with `CakeEmailHandler`, a Monolog mail handler that sends the log messages
with `CakeEmail`. Its arguments are:

1. the recipient(s)
2. the subject
3. the email configuration to use (default `default`)
4. the minimum level (default `Logger::ERROR`)
5. whether the messages bubble up the stack (default `true`)

As it is not part of Monolog, use the full class name as the handler name and set `search` to
the directory of the class, as in the example above. To send the emails with another class
than `CakeEmail`, set `Email.classname`:

```php
Configure::write('Email.classname', 'MyEmail');
```
