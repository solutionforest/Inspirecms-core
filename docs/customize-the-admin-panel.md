---
title: Customize the Admin Panel
slug: customize-the-admin-panel
path: docs/v1/customize-the-admin-panel
uri: /docs/v1/customize-the-admin-panel
heading: Customize the Admin Panel
brief: This guide explains how to customize and extend the admin panel to meet your specific needs, including creating custom resources, pages, widgets, and modifying the appearance to match your brand. 

Whether you need to add new functionality or tailor the existing features, InspireCMS offers multiple extension points for developers.
quick_links: []
---

## Overview

The InspireCMS admin panel is built on [Filament](https://filamentphp.com/), a powerful admin panel framework for Laravel applications. This integration allows you to:

-   Create custom admin pages
-   Add new resources for managing database models
-   Organize related functionality into clusters
-   Define custom dashboard widgets
-   Extend existing admin functionality
-   Customize the look and feel of the admin panel

---

## Prerequisites

Before you begin extending the admin panel, ensure you have:

1. A working InspireCMS installation
2. Basic knowledge of **Laravel** and **Filament** concepts
3. Appropriate permissions to access the admin area

---

## Creating a Custom Panel Provider

The recommended approach for customizing InspireCMS is to create your own CMS Panel Provider by extending the base `CmsPanelProvider`:

1. Generate a new provider:

```bash
php artisan make:filament-panel my-cms
```

2. Extend the base CMS Panel Provider:

```php
<?php

namespace App\Providers;

use Filament\Panel;
use SolutionForest\InspireCms\CmsPanelProvider;

class MyCmsPanelProvider extends CmsPanelProvider
{
    /**
     * Configure the CMS panel.
     */
    protected function configureCmsPanel(Panel $panel): Panel
    {
        $panel = parent::configureCmsPanel($panel);

        // Add your custom configuration here

        return $panel;
    }
}
```

3. Register the provider in `config/app.php`:

```php
'providers' => [
    // Other providers...
    // Comment out or remove the default CmsPanelProvider
    // SolutionForest\InspireCms\CmsPanelProvider::class,

    // Add your custom provider
    App\Providers\MyCmsPanelProvider::class,
],
```

### Customizing the Panel Appearance

Here's how to customize various aspects of the panel:

```php
<?php

namespace App\Providers;

use Filament\Panel;
use Filament\Navigation\NavigationGroup;
use SolutionForest\InspireCms\CmsPanelProvider;

class MyCmsPanelProvider extends CmsPanelProvider
{
    protected function configureCmsPanel(Panel $panel): Panel
    {
        $panel = parent::configureCmsPanel($panel);

        return $panel
            // Custom branding
            ->brandName('My Custom CMS')
            ->brandLogo(fn() => view('admin.logo'))
            ->favicon(asset('images/favicon.png'))

            // Custom colors
            ->colors([
                'primary' => [
                    50 => '238, 242, 255',
                    100 => '224, 231, 255',
                    200 => '199, 210, 254',
                    300 => '165, 180, 252',
                    400 => '129, 140, 248',
                    500 => '99, 102, 241',
                    600 => '79, 70, 229',
                    700 => '67, 56, 202',
                    800 => '55, 48, 163',
                    900 => '49, 46, 129',
                    950 => '30, 27, 75',
                ],
                'danger' => '#ff0000',
                'success' => '#10b981',
                'warning' => '#f59e0b',
            ])

            // Fonts
            ->font('Poppins')
            ->fontSize('md')

            // Dark mode
            ->darkMode(true) // Enable dark mode

            // Render hooks
            ->renderHook(
                'panels::body.start',
                fn () => view('custom-scripts')
            );
    }
}
```

### Customizing Navigation

You can customize the navigation structure:

```php
<?php

namespace App\Providers;

use Filament\Panel;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use SolutionForest\InspireCms\CmsPanelProvider;

class MyCmsPanelProvider extends CmsPanelProvider
{
    protected function configureNavigation(Panel $panel): Panel
    {
        return parent::configureNavigation($panel)
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Content')
                    ->icon('heroicon-o-document-text'),
                NavigationGroup::make()
                    ->label('Shop')
                    ->icon('heroicon-o-shopping-bag'),
                NavigationGroup::make()
                    ->label('Settings')
                    ->icon('heroicon-o-cog'),
            ])
            ->navigationItems([
                NavigationItem::make('Analytics')
                    ->url('https://analytics.google.com')
                    ->icon('heroicon-o-chart-bar')
                    ->group('Reports'),
            ]);
    }
}
```

### Adding Widgets and Resources

Configure default widgets and resources:

```php
<?php

namespace App\Providers;

use App\Filament\Widgets\StatsOverview;
use App\Filament\Resources\ProductResource;
use Filament\Panel;
use SolutionForest\InspireCms\CmsPanelProvider;

class MyCmsPanelProvider extends CmsPanelProvider
{
    protected function configureCmsPanel(Panel $panel): Panel
    {
        $panel = parent::configureCmsPanel($panel);

        return $panel
            ->widgets([
                StatsOverview::class,
            ])
            ->resources([
                ProductResource::class,
            ])
            ->pages([
                App\Filament\Pages\Settings::class,
            ]);
    }
}
```

### Advanced Configuration Options

For more advanced customization:

```php
<?php

namespace App\Providers;

use Filament\Panel;
use SolutionForest\InspireCms\CmsPanelProvider;

class MyCmsPanelProvider extends CmsPanelProvider
{
    protected function configureCmsPanel(Panel $panel): Panel
    {
        $panel = parent::configureCmsPanel($panel);

        return $panel
            // Custom middleware
            ->middleware([
                App\Http\Middleware\CustomMiddleware::class,
            ])
            ->authMiddleware([
                App\Http\Middleware\CustomAuthMiddleware::class,
            ])

            // Plugin registration
            ->plugin(new App\Filament\Plugins\CustomPlugin())

            // Custom assets
            ->assets([
                // CSS assets
                \Filament\Support\Assets\Css::make('custom-stylesheet', 'path/to/stylesheet.css'),
                // JavaScript assets
                \Filament\Support\Assets\Js::make('custom-script', 'path/to/script.js'),
            ])

            // Authentication
            ->login()
            ->registration()
            ->passwordReset()
            ->emailVerification();
    }
}
```

---

## Adding Custom Clusters

### Creating Custom Clusters

1. Generate a new resource class:

```bash
php artisan make:filament-cluster YourClusterName
```

2. Register your cluster:

You can register your custom cluster in two ways:

**Option 1: Using configuration**

```php
<?php

namespace App\Providers;

use App\Filament\Clusters\YourClusterName;
use Illuminate\Support\ServiceProvider;
use SolutionForest\InspireCms\InspireCmsConfig;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register your custom resource
        InspireCmsConfig::set('admin.clusters.your_cluster_name', YourClusterName::class);
    }
}
```

**Option 2: Using a custom panel provider**

```php
<?php

namespace App\Providers;

use App\Filament\Clusters\YourClusterName;
use App\Filament\Resources\YourModelResource;
use Filament\Panel;
use SolutionForest\InspireCms\CmsPanelProvider;

class MyCmsPanelProvider extends CmsPanelProvider
{
    protected function configureCmsPanel(Panel $panel): Panel
    {
        $panel = parent::configureCmsPanel($panel);

        return $panel
            ->clusters([
                YourClusterName::class,
                // Add more clusters here
            ]);
    }
}
```

### Example Cluster Class

```php
<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSection;

class ShopCluster extends Cluster implements ClusterSection
{
    use ClusterSectionTrait;

    protected static ?string $navigationLabel = 'Shop Management';

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?int $navigationSort = 3;
}
```

> [!IMPORTANT]
> Always ensure your Cluster implements the `SolutionForest\InspireCms\Filament\Contracts\ClusterSection` interface.

### Associating Resources and Pages with Clusters

To associate a resource or page with a cluster, specify the cluster class in the resource or page definition:

For resources:

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Clusters\ShopCluster;
use Filament\Resources\Resource;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionResourceTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionResource;

class ProductResource extends Resource implements ClusterSectionResource
{
    use ClusterSectionResourceTrait;

    protected static ?string $cluster = ShopCluster::class;

    // Other resource configuration...
}
```

For pages:

```php
<?php

namespace App\Filament\Pages;

use App\Filament\Clusters\ShopCluster;
use Filament\Pages\Page;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionPageTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionPage;

class ShopSettings extends Page implements ClusterSectionPage
{
    use ClusterSectionPageTrait;

    protected static ?string $cluster = ShopCluster::class;

    // Other page configuration...
}
```

---

## Custom Resources

Resources in Filament represent database models and provide a complete CRUD interface for managing them.

### Creating a Custom Resource

1. Generate a new resource class:

```bash
php artisan make:filament-resource YourModel
```

2. Register your resource:

You can register your custom resources in two ways:

**Option 1: Using configuration**

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use SolutionForest\InspireCms\InspireCmsConfig;
use App\Filament\Resources\YourModelResource;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register your custom resource
        InspireCmsConfig::set('admin.resources.your_model', YourModelResource::class);
    }
}
```

**Option 2: Using a custom panel provider**

```php
<?php

namespace App\Providers;

use App\Filament\Resources\YourModelResource;
use Filament\Panel;
use SolutionForest\InspireCms\CmsPanelProvider;

class MyCmsPanelProvider extends CmsPanelProvider
{
    protected function configureCmsPanel(Panel $panel): Panel
    {
        $panel = parent::configureCmsPanel($panel);

        return $panel
            ->resources([
                YourModelResource::class,
                // Add more resources here
            ]);
    }
}
```

### Example Resource Class

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Clusters\Shop;
use App\Models\Product;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionResourceTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionResource;

class ProductResource extends Resource implements ClusterSectionResource
{
    use ClusterSectionResourceTrait;

    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'Shop';

    protected static ?int $navigationSort = 1;

    protected static ?string $cluster = Shop::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                Forms\Components\Textarea::make('description')
                    ->maxLength(65535),
                Forms\Components\Toggle::make('is_active')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('price')
                    ->money('USD'),
                Tables\Columns\BooleanColumn::make('is_active'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
            ])
            ->filters([
                Tables\Filters\Filter::make('active')
                    ->query(fn (Builder $query): Builder => $query->where('is_active', true)),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
```

---

## Custom Pages

You can add standalone pages to the admin panel that aren't tied to a specific resource.

### Creating a Custom Page

1. Generate a new page class:

```bash
php artisan make:filament-page Settings
```

2. Register your page:

You can register your custom pages in two ways:

**Option 1: Using configuration**

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use SolutionForest\InspireCms\InspireCmsConfig;
use App\Filament\Pages\Settings;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register your custom page
        InspireCmsConfig::set('admin.pages.settings', Settings::class);
    }
}
```

**Option 2: Using a custom panel provider**

```php
<?php

namespace App\Providers;

use App\Filament\Pages\Settings;
use Filament\Panel;
use SolutionForest\InspireCms\CmsPanelProvider;

class MyCmsPanelProvider extends CmsPanelProvider
{
    protected function configureCmsPanel(Panel $panel): Panel
    {
        $panel = parent::configureCmsPanel($panel);

        return $panel
            ->pages([
                Settings::class,
                // Add more pages here
            ]);
    }
}
```

### Example Page Class

```php
<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Config;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionPageTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionPage;
use SolutionForest\InspireCms\Filament\Contracts\GuardPage;

class Settings extends Page implements ClusterSectionPage, GuardPage, HasActions, HasForms
{
    use ClusterSectionPageTrait;
    use InteractsWithActions;
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog';

    protected static ?string $navigationLabel = 'Site Settings';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 5;

    protected static string $view = 'filament.pages.settings';

    protected static ?string $cluster = \SolutionForest\InspireCms\Filament\Clusters\Settings::class;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'site_name' => config('app.name'),
            'site_description' => config('app.description'),
            'maintenance_mode' => config('app.maintenance'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('General Settings')
                    ->schema([
                        Forms\Components\TextInput::make('site_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('site_description')
                            ->maxLength(1000),
                        Forms\Components\Toggle::make('maintenance_mode'),
                    ]),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // Save settings logic here
        // For example, update .env file or settings table

        Notification::make()
            ->title('Settings saved successfully')
            ->success()
            ->send();
    }
}
```

---

## Custom Widgets

You can add widgets to the admin dashboard to display important information, statistics, or shortcuts.

### Creating a Custom Widget

1. Generate a new widget class:

```bash
php artisan make:filament-widget StatsOverview
```

2. Register your widget:

You can register your custom widgets in two ways:

**Option 1: Using configuration**

```php
<?php

namespace App\Providers;

use App\Filament\Widgets\StatsOverview;
use Illuminate\Support\ServiceProvider;
use SolutionForest\InspireCms\InspireCmsConfig;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register your custom widget
        InspireCmsConfig::set('admin.extra_widgets.stats_overview', StatsOverview::class);
    }
}
```

**Option 2: Using a custom panel provider**

```php
<?php

namespace App\Providers;

use App\Filament\Widgets\StatsOverview;
use Filament\Panel;
use SolutionForest\InspireCms\CmsPanelProvider;

class MyCmsPanelProvider extends CmsPanelProvider
{
    protected function configureCmsPanel(Panel $panel): Panel
    {
        $panel = parent::configureCmsPanel($panel);

        return $panel
            ->widgets([
                StatsOverview::class,
                // Add more widgets here
            ]);
    }
}
```

### Example Widget Class

```php
<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;

class StatsOverview extends BaseWidget
{
    protected function getCards(): array
    {
        return [
            Card::make('Total Users', User::count())
                ->description('Increased by 20%')
                ->descriptionIcon('heroicon-s-trending-up')
                ->color('success'),
            Card::make('Total Orders', Order::count())
                ->description('3% increase from last month')
                ->descriptionIcon('heroicon-s-trending-up')
                ->color('success'),
            Card::make('Average Order Value', '$' . number_format(Order::avg('total') ?? 0, 2))
                ->description('1.5% decrease from last month')
                ->descriptionIcon('heroicon-s-trending-down')
                ->color('danger'),
        ];
    }
}
```

---

## Further Resources

-   [Filament Documentation](https://filamentphp.com/docs)
-   [Laravel Documentation](https://laravel.com/docs)

With these tools and techniques, you can extend and customize the InspireCMS admin panel to suit your specific requirements while maintaining a consistent and user-friendly interface.
