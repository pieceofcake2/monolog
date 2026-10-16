# CakePHP Monolog Plugin

[![GitHub License](https://img.shields.io/github/license/pieceofcake2/monolog?label=License)](LICENSE)
[![Packagist Version](https://img.shields.io/packagist/v/pieceofcake2/monolog?label=Packagist)](https://packagist.org/packages/pieceofcake2/monolog)
![PHP](https://img.shields.io/packagist/dependency-v/pieceofcake2/monolog/php?logo=php&logoColor=%23FFFFFF&label=PHP&labelColor=%23777BB4&color=%23FFFFFF)
![CakePHP](https://img.shields.io/packagist/dependency-v/pieceofcake2/monolog/pieceofcake2/cakephp?logo=cakephp&logoColor=%23FFFFFF&label=CakePHP&labelColor=%23D33C43&color=%23FFFFFF)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%205-brightgreen?label=PHPStan)](https://github.com/pieceofcake2/monolog/actions/workflows/CI.yml)
[![CI](https://img.shields.io/github/actions/workflow/status/pieceofcake2/monolog/CI.yml?label=CI)](https://github.com/pieceofcake2/monolog/actions/workflows/CI.yml)
[![Codecov](https://img.shields.io/codecov/c/gh/pieceofcake2/monolog?label=Coverage)](https://codecov.io/gh/pieceofcake2/monolog)

A CakePHP 2 log engine that sends the log messages to [Monolog](https://github.com/Seldaek/monolog)
handlers, with formatters and processors configured in `CakeLog::config()`. It also ships with a
Monolog handler that sends log messages by email with `CakeEmail`. This is a fork of
[jadb/cakephp-monolog](https://github.com/jadb/cakephp-monolog) for
[pieceofcake2/cakephp](https://github.com/pieceofcake2/cakephp).

## Documentation

- Website: https://pieceofcake2.github.io/monolog/ (日本語: https://pieceofcake2.github.io/monolog/ja/)
- Markdown: [docs/README.md](docs/README.md) (日本語: [docs/ja/README.md](docs/ja/README.md)) (for AI tools and offline reading)

The Markdown documentation is also included in the Composer package, in the plugin's directory: `plugins/Monolog/docs/README.md` (the location depends on your `installer-paths`). Install the package with `--dev` if you only need it for development.

## Requirements

* PHP 8.0+
* pieceofcake2/cakephp 2.12+
* monolog/monolog 2.5+ or 3.9+

## Installation

```bash
composer require pieceofcake2/monolog
```

## Contributing

See the [contribution guidelines](https://github.com/pieceofcake2/.github/blob/master/CONTRIBUTING.md).

## Origin

This package is a maintained fork of [jadb/cakephp-monolog](https://github.com/jadb/cakephp-monolog)
(`jadb/cakephp-monolog` on Packagist, last upstream version 2.0.0), originally by Jad Bitar. It is
maintained for CakePHP 2 on modern PHP by [pieceofcake2](https://github.com/pieceofcake2). Changes
from the original are listed in the
[commit history since the fork](https://github.com/pieceofcake2/monolog/compare/73bfc43...2.x).

## License

The MIT License. See [LICENSE](LICENSE).
