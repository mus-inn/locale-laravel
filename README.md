# [Locale](https://uselocale.com): The Laravel localization tool

[![Latest Version on Packagist](https://img.shields.io/packagist/v/uselocale/locale-laravel.svg?style=flat-square)](https://packagist.org/packages/uselocale/locale-laravel)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/uselocale/locale-laravel/run-tests?label=tests)](https://github.com/uselocale/locale-laravel/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/uselocale/locale-laravel/Check%20&%20fix%20styling?label=code%20style)](https://github.com/uselocale/locale-laravel/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/uselocale/locale-laravel.svg?style=flat-square)](https://packagist.org/packages/uselocale/locale-laravel)

[Locale](https://uselocale.com) is the first localization platform specifically built for Laravel. Forget the old-fashioned email exchanges between teams and translators that always slow down project development, manage translations with our admin panel and smoothly synchronize the files with your project using our simple package commands.

<p align="center">
  <img alt="Locale screenshot" src="https://uselocale.com/img/landing/screenshot.png">
</p>

## Installation

Follow the details provided on [Locale](https://uselocale.com) after creating a new project.

## Available commands

### Setup
```bash
php artisan locale:setup
```
You only need to run this command once. It will upload your existing translations to Locale and prepare your local files to be synced in the future.

Your local files will be reformatted but won't change their content.

### Sync
```bash
php artisan locale:sync
```

Run this command to upload any new translation keys to Locale and download updates on all your target languages.

If there's any conflict during the process, you'll receive a confirmation message.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Creagia](https://creagia.com)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
