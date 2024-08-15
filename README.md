# This is my package inspirecms-core

[![Latest Version on Packagist](https://img.shields.io/packagist/v/solution-forest/inspirecms-core.svg?style=flat-square)](https://packagist.org/packages/solution-forest/inspirecms-core)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/solution-forest/inspirecms-core/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/solutionforest/inspirecms-core/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/solution-forest/inspirecms-core/fix-php-code-styling.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/solutionforest/inspirecms-core/actions?query=workflow%3A"Fix+PHP+code+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/solution-forest/inspirecms-core.svg?style=flat-square)](https://packagist.org/packages/solution-forest/inspirecms-core)



This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Installation

You can install the package via composer:

```bash
composer require solution-forest/inspirecms-core
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="inspirecms-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="inspirecms-config"
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="inspirecms-views"
```

This is the contents of the published config file:

```php
return [
];
```

## Usage

```php
$inspireCms = new SolutionForest\InspireCms();
echo $inspireCms->echoPhrase('Hello, SolutionForest!');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [carly](https://github.com/solutionforest)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
