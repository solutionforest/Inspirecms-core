# This is my package inspirecms-core

[![Latest Version on Packagist](https://img.shields.io/packagist/v/solution-forest/inspirecms-core.svg?style=flat-square)](https://packagist.org/packages/solution-forest/inspirecms-core)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/solution-forest/inspirecms-core/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/solutionforest/inspirecms-core/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/solution-forest/inspirecms-core/fix-php-code-styling.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/solutionforest/inspirecms-core/actions?query=workflow%3A"Fix+PHP+code+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/solution-forest/inspirecms-core.svg?style=flat-square)](https://packagist.org/packages/solution-forest/inspirecms-core)



This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.


## Development
### Install inspirecms-support library
`COMPOSER_ROOT_VERSION=dev-main composer update`

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

### Existing scheduled jobs in the configuration file:
```php
'scheduled_tasks' => [
    'execute_import_job' => [
        'enabled' => true,
        'schedule' => 'everyFiveMinutes',
        'command' => \SolutionForest\InspireCms\Commands\ExecuteImport::class,
        'arguments' => [
            '--limit 50', // limit
        ],
    ],
    'data_cleanup' => [
        'enabled' => true,
        'schedule' => 'daily',
        'command' => \SolutionForest\InspireCms\Commands\DataCleanup::class,
    ],
],
```

### Content Approving Flow
1. Add custom status
```php
\SolutionForest\InspireCms\Facades\ContentStatusManifest::replaceOption(
    new \SolutionForest\InspireCms\DataTypes\Manifest\ContentStatusOption(
        value: 5,
        name: 'reviewing',
        formAction: fn () => \Filament\Actions\Action::make('reviewing')
            ->authorize('reviewing')
            ->action(function (null | \SolutionForest\InspireCms\Models\Content $record, \Filament\Actions\Action $action, $livewire) {
                
                // Handle your action here

            }),
    )
)
```
2. Add policy for Content Model (Super admin already skip all guard)
```php
use \SolutionForest\InspireCms\Models\Content;

/**
 * Bootstrap any application services.
 */
public function boot(): void
{
    Gate::policy(Content::class, YourContentPolicy::class);
}
```

Or override ContentPublishPolicy on config
```php
    // ...
    'models' => [
        // ...
        'policies' => [
            'content' => YourContentPolicy::class,
        ]
    ],
    //...
```

```php
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Models\Contracts\Content;

class YourContentPolicy
{
    /**
     * @param  Authenticatable|User|Model  $user
     * @param  null|Content|Model  $content
     * @return bool
     */
    public function reviewing($user, $content)
    {
        return true;
    }
}
```

### Adding extract filament cluster/resource/page

> [!IMPORTANT]  
> need add back miss permission after cluster/resource/page added.

Call
```bash
php artisan inspirecms:repair-permissions
```

#### Adding extract filament cluster

- Option 1: create by `make:filament-cluster xxx --panel=cms`
- Option 2: create you cluster, and add this to `filament.cluster` on config file.

1. After cluster created, please apply `ClusterSectionTrait` and `ClusterSection` to your resource.
```php
use Filament\Clusters\Cluster;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSection;

class Test extends Cluster implements ClusterSection
{
    use ClusterSectionTrait;
}
```

#### Adding extract filament resource
- Option 1: create by `make:filament-resource xxx --panel=cms`
- Option 2: create you resource, and add this to `filament.resources` on config file.

After resource created, please apply `ClusterSectionResource`, `ClusterSectionResourceTrait`, and Cluster to your resource.

> [!IMPORTANT] 
> Ensure your Cluster apply SolutionForest\InspireCms\Filament\Contracts\ClusterSection interface.

```php
use Filament\Resources\Resource;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionResourceTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionResource;

class TestResource extends Resource implements ClusterSectionResource
{
    use ClusterSectionResourceTrait;

    protected static ?string $cluster = \App\Clusters\Test::class;
}
```

#### Adding extract filament page
- Option 1: create by `make:filament-page xxx --panel=cms`
- Option 2: create you page, and add this to `filament.pages` on config file.

After page created, please apply `ClusterSectionPage`, `ClusterSectionResourceTrait`, `GuardPage`, and Cluster to your resource.

> [!IMPORTANT] 
> Ensure your Cluster apply SolutionForest\InspireCms\Filament\Contracts\ClusterSection interface.

use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionPageTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionPage;
use SolutionForest\InspireCms\Filament\Contracts\GuardPage;

```php
class Test extends Page implements ClusterSectionPage, GuardPage
{
    use ClusterSectionPageTrait;

    protected static ?string $cluster = \App\Clusters\Test::class;

    public static function getPermissionName(): string
    {
        return 'view_test_page';
    }

    public static function getPermissionDisplayName(): string
    {
        return 'View test page';
    }
}
```

## Extending

### Override model
```php
public function register(): void
{
    \SolutionForest\InspireCms\Facades\ModelManifest::replace(
        \SolutionForest\InspireCms\Models\Contracts\Content::class,
        Your\Model\Class::class,
    );
}
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
