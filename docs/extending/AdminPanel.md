# Extending the Admin Panel

InspireCMS provides a powerful, Filament-based admin panel that can be extensively customized and extended. This guide covers how to add your own functionality to the admin interface.

## Overview

The InspireCMS admin panel is built on [Filament](https://filamentphp.com/), a powerful admin panel framework for Laravel applications. This integration allows you to:

- Create custom admin pages
- Add new resources for managing database models
- Define custom dashboard widgets
- Extend existing admin functionality
- Customize the look and feel of the admin interface

## Prerequisites

Before you begin extending the admin panel, ensure you have:

1. A working InspireCMS installation
2. Basic knowledge of Laravel and Filament concepts
3. Appropriate permissions to access the admin area

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

## Custom Admin Pages

You can add standalone pages to the admin panel that aren't tied to a specific resource.

### Creating a Custom Page

1. Generate a new page class:

```bash
php artisan make:filament-page Settings
```

2. Register your custom page with InspireCMS:

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

### Example Page Class

```php
<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;

class Settings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog';
    
    protected static string $view = 'filament.pages.settings';
    
    protected static ?string $navigationGroup = 'System';
    
    public $siteName;
    public $enableFeature;
    
    public function mount(): void
    {
        $this->form->fill([
            'siteName' => config('app.name'),
            'enableFeature' => config('features.example_feature', false),
        ]);
    }
    
    protected function getFormSchema(): array
    {
        return [
            TextInput::make('siteName')
                ->required()
                ->label('Site Name'),
            Toggle::make('enableFeature')
                ->label('Enable Example Feature'),
        ];
    }
    
    public function submit(): void
    {
        // Process the form submission
        $data = $this->form->getState();
        
        // Save the settings
        // ...
        
        Notification::make()
            ->title('Settings saved successfully')
            ->success()
            ->send();
    }
}
```

3. Create the view file:

```php
<!-- resources/views/filament/pages/settings.blade.php -->
<x-filament::page>
    <form wire:submit.prevent="submit">
        {{ $this->form }}
        
        <x-filament::button type="submit" class="mt-4">
            Save Settings
        </x-filament::button>
    </form>
</x-filament::page>
```

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

## Custom Navigation

You can customize the admin navigation to organize your resources and pages.

### Adding Custom Navigation Items

```php
<?php

namespace App\Providers;

use App\Filament\Widgets\StatsOverview;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Illuminate\Support\ServiceProvider;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\CmsPanelProvider;

class MyCmsPanelProvider extends CmsPanelProvider
{
    protected function configureNavigation(Panel $panel): Panel
    {
        return parent::configureNavigation($panel)
            ->navigationGroups([
                NavigationGroup::make('External Resources'),
            ]);
    }
}
```

## Extending Existing Resources

You can extend or override InspireCMS's built-in resources to customize their behavior.

### Example: Extending the User Resource

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Filament\Resources\UserResource;
use App\Filament\Resources\CustomUserResource;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Replace the default User resource with a custom one
        InspireCmsConfig::set('admin.resources.user', CustomUserResource::class);
    }
}
```

Define your custom resource:

```php
<?php

namespace App\Filament\Resources;

use SolutionForest\InspireCms\Filament\Resources\UserResource as BaseUserResource;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Tables;

class CustomUserResource extends BaseUserResource
{
    public static function form(Form $form): Form
    {
        // Get the parent form schema
        $parentSchema = parent::form($form)->getSchema();
        
        // Add your custom fields
        $customFields = [
            Forms\Components\TextInput::make('custom_field')
                ->label('Custom Field'),
            // Add more custom fields as needed
        ];
        
        // Merge and return
        return $form->schema(array_merge($parentSchema, $customFields));
    }
    
    public static function table(Table $table): Table
    {
        // Get the parent table columns
        $parentColumns = parent::table($table)->getColumns();
        
        // Add your custom columns
        $customColumns = [
            Tables\Columns\TextColumn::make('custom_field')
                ->label('Custom Field'),
            // Add more custom columns as needed
        ];
        
        // Merge and return
        return $table->columns(array_merge($parentColumns, $customColumns));
    }
}
```

## Theme Customization

You can customize the appearance of the admin panel by modifying its theme.

1. Configure the theme in the provider:

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Facades\Filament;
use Filament\Panel;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\CmsPanelProvider;

class MyCmsPanelProvider extends CmsPanelProvider
{
    protected function configureCmsPanel(Panel $panel)
    {
        $panel = parent::configureCmsPanel($panel);

        return $panel
            // Custom logo
            ->brandLogo(fn() => view('admin.logo'))
            // Custom favicon
            ->favicon(asset('images/favicon.png'))
            ->colors([
                // Light mode colors
                'primary' => 'rgb(16, 185, 129)',
                'primary-hover' => 'rgb(4, 165, 109)',
                'secondary' => 'rgb(14, 165, 233)',
                'secondary-hover' => 'rgb(2, 132, 199)',
                'success' => 'rgb(16, 185, 129)',
                'warning' => 'rgb(250, 204, 21)',
                'danger' => 'rgb(239, 68, 68)',
                
                // Dark mode colors (optional)
                'dark-primary' => 'rgb(20, 210, 150)',
                'dark-primary-hover' => 'rgb(10, 190, 130)',
                'dark-secondary' => 'rgb(20, 184, 255)',
                'dark-secondary-hover' => 'rgb(10, 150, 220)',
            ]);
    }
}
```

2. Register the provider in `config/app.php`:

```php
'providers' => [
    // Other providers...
    App\Providers\MyCmsPanelProvider::class,
],
```

## Best Practices

1. **Follow a Consistent Structure**: Organize your extensions logically in their own namespaces
2. **Register Extensions in Service Providers**: Keep all extension registrations in dedicated service providers
3. **Avoid Duplicating Functionality**: Extend existing components rather than recreating them
4. **Respect User Permissions**: Always check permissions before allowing access to custom functionality
5. **Stay Updated**: Keep track of InspireCMS updates and adjust your extensions accordingly
6. **Test Thoroughly**: Ensure your extensions work correctly across different scenarios
7. **Document Your Extensions**: Provide clear documentation for team members or clients

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

## Further Resources

- [Filament Documentation](https://filamentphp.com/docs)
- [Laravel Documentation](https://laravel.com/docs)
- [InspireCMS GitHub Repository](https://github.com/solutionforest/inspire-cms)

With these tools and techniques, you can extend and customize the InspireCMS admin panel to suit your specific requirements while maintaining a consistent and user-friendly interface.
