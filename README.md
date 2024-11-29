# This is my package inspirecms-core

[![Latest Version on Packagist](https://img.shields.io/packagist/v/solution-forest/inspirecms-core.svg?style=flat-square)](https://packagist.org/packages/solution-forest/inspirecms-core)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/solution-forest/inspirecms-core/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/solutionforest/inspirecms-core/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/solution-forest/inspirecms-core/fix-php-code-styling.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/solutionforest/inspirecms-core/actions?query=workflow%3A"Fix+PHP+code+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/solution-forest/inspirecms-core.svg?style=flat-square)](https://packagist.org/packages/solution-forest/inspirecms-core)



This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Installation

1. You can install the package via composer:

```bash
composer require solution-forest/inspirecms-core
```

2. Run install command:
```bash
php artisan inspirecms:install
```

Optional: Install required default data:
```bash
php artisan inspirecms:import-default-data
```

3. Execute the schedule command to run scheduled jobs:
```bash
php artisan schedule:work
```

Existing scheduled jobs in the configuration file:
```php
'scheduled_tasks' => [
    'cleanup_content_verion' => [
        'enabled' => true,
        'schedule' => 'daily',
        'command' => \SolutionForest\InspireCms\Commands\CleanupContentVersion::class,
        'old_content_version_days' => 30,
    ],
    'execute_import_job' => [
        'enabled' => true,
        'schedule' => 'everyMinute',
        'command' => \SolutionForest\InspireCms\Commands\ExecuteImportJob::class,
        'arguments' => [
            '--limit 50',
        ],
    ],
    'cleanup_import_job' => [
        'enabled' => true,
        'schedule' => 'daily',
        'command' => \SolutionForest\InspireCms\Commands\CleanupImportJob::class,
        'old_import_job_days' => 5,
    ],
],
```

## Configuration

## Extending

### Override model
```php
\SolutionForest\InspireCms\Facades\ModelManifest::replace(
    \SolutionForest\InspireCms\Models\Content::class,
    Your\Model\Class::class,
);
```

## Schedule jobs
config on `inspirecms.php` config file:

1. cleanup_content_verion
2. execute_import_job
3. cleanup_import_job

## Testing

```bash
composer test
```

## Development
### Build js and css

1. Install composer dependencies:
```bash
composer install
```

2. Install npm dependencies:
```bash
npm i 
```

3. Build assets:
```bash
npm run build
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
