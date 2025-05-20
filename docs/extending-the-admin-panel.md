---
title: Extending the Admin Panel
slug: extending-the-admin-panel
path: docs/v1/extending-the-admin-panel
uri: /docs/1.x/extending-the-admin-panel
---
# Extending the Admin Panel

InspireCMS provides a powerful, Filament-based admin panel that can be extensively customized and extended. This guide covers how to add your own functionality to the admin interface.

---

## Overview

The InspireCMS admin panel is built on [Filament](https://filamentphp.com/), a powerful admin panel framework for Laravel applications. This integration allows you to:

- Create custom admin pages
- Add new resources for managing database models
- Define custom dashboard widgets
- Extend existing admin functionality
- Customize the look and feel of the admin interface

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

### Adding Global Search

Enable and customize global search:

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
            ->globalSearch(true)
            ->globalSearchKeyBindings(['command+k', 'ctrl+k']);
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

## Custom Resources

Resources in Filament represent database models and provide a complete CRUD interface for managing them.

### Creating a Custom Resource

1. Generate a new resource class:

```bash
php artisan make:filament-resource YourModel
```

2. Register your resource with InspireCMS:

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

## Custom Admin Pages

You can add standalone pages to the admin panel that aren't tied to a specific resource.

### Creating a Custom Page

1. Generate a new page class:

```bash
php artisan make:filament-page Settings
```

2. Register your page with InspireCMS:

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

### Creating a Custom View

Create a view file at `resources/views/filament/pages/settings.blade.php`:

```blade
<x-filament::page>
    <form wire:submit.prevent="save">
        {{ $this->form }}
        
        <div class="flex justify-end mt-4">
            <x-filament::button type="submit">
                Save Settings
            </x-filament::button>
        </div>
    </form>
</x-filament::page>
```

### Advanced Page Features

You can further customize pages with actions, tabs, and more:

```php
<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Pages\Page;

class Dashboard extends Page
{
    // ... other properties
    
    protected function getHeaderActions(): array
    {
        return [
            Action::make('settings')
                ->label('Settings')
                ->url(route('filament.admin.pages.settings'))
                ->icon('heroicon-s-cog'),
            Action::make('visit')
                ->label('Visit Site')
                ->url('/')
                ->icon('heroicon-s-external-link')
                ->openUrlInNewTab(),
        ];
    }
    
    protected function getFooterWidgets(): array
    {
        return [
            // Add widgets that appear at the bottom of the page
        ];
    }
}
```

--

## Custom Dashboard Widgets

You can add widgets to the admin dashboard to display important information, statistics, or shortcuts.

### Creating a Custom Widget

1. Generate a new widget class:

```bash
php artisan make:filament-widget StatsOverview
```

2. Register your widget with InspireCMS:

```php
<?php

namespace App\Providers;

use App\Filament\Widgets\StatsOverview;
use Filament\Panel;
use Illuminate\Support\ServiceProvider;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\CmsPanelProvider;

class MyCmsPanelProvider extends CmsPanelProvider
{
    protected function configureCmsPanel(Panel $panel)
    {
        return parent::configureCmsPanel($panel)
            ->widgets([
                // Register your custom widget
                StatsOverview::class,
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

## Best Practices

1. **Follow a Consistent Structure**: Organize your extensions logically in their own namespaces
2. **Register Extensions in Service Providers**: Keep all extension registrations in dedicated service providers
3. **Avoid Duplicating Functionality**: Extend existing components rather than recreating them
4. **Respect User Permissions**: Always check permissions before allowing access to custom functionality
5. **Stay Updated**: Keep track of InspireCMS updates and adjust your extensions accordingly
6. **Test Thoroughly**: Ensure your extensions work correctly across different scenarios
7. **Document Your Extensions**: Provide clear documentation for team members or clients

---

## Troubleshooting

### Common Issues

- **Resources Not Appearing**: Ensure you've registered them correctly with `InspireCmsConfig::addAdminResource()`
- **Permission Problems**: Check that your user has the necessary roles and permissions
- **Navigation Issues**: Verify your navigation items have the correct structure and labels
- **Style Conflicts**: Use Filament's styling conventions to avoid CSS conflicts

### Debugging Tips

1. Check Laravel logs at `storage/logs/laravel.log`
2. Enable debugging in config/app.php (`'debug' => true`)
3. Use `dd()` or `dump()` helpers in your code to inspect variables
4. Verify your service providers are registered in the correct order

---

## Further Resources

- [Filament Documentation](https://filamentphp.com/docs)
- [Laravel Documentation](https://laravel.com/docs)
- [InspireCMS GitHub Repository](https://github.com/solutionforest/inspire-cms)

With these tools and techniques, you can extend and customize the InspireCMS admin panel to suit your specific requirements while maintaining a consistent and user-friendly interface.
