---
title: Customization
slug: customization
path: docs/v1/customization
uri: /docs/1.x/customization
---
# Customization

This guide explains how to customize InspireCMS with custom publishing states, Filament components, and model overrides.

---

## Creating Custom Publish States

InspireCMS provides a flexible publishing system that you can extend with your own custom states for workflow management.

### Implementation Steps

1. Create a class implementing `PublishStateInterface`
2. Register your new state in a service provider
3. (Optional) Add custom styling and behavior

## Example: Content Approval Workflow

### Adding a Custom Content Status

```php {title="app/Providers/AppServiceProvider.php"}
use SolutionForest\InspireCms\Facades\ContentStatusManifest;
use SolutionForest\InspireCms\DataTypes\Manifest\ContentStatusOption;
use Filament\Actions\Action;
use SolutionForest\InspireCms\Models\Content;
use SolutionForest\InspireCms\Helpers\ContentHelper;

// Register "approved" state with value 5
ContentStatusManifest::replaceOption(
    new ContentStatusOption(
        value: 5,
        name: 'approved',
        formAction: fn () => Action::make('approved')
            ->authorize('approved')
            ->action(function (null | Content $record, Action $action, $livewire) {
                if (is_null($record)) {
                    $action->cancel();
                    return;
                }

                $publishableState = 'approved';

                if (! ContentHelper::handlePublishableRecord($record, $publishableState, $livewire, [])) {
                    return;
                }

                $action->success();
            }),
    )
);
```

### Setting Up Permissions

After adding new Filament components:

```bash
php artisan inspirecms:repair-permissions
```

### Creating Content Approval Policies

**Option 1: Add a Policy Using Gate**

```php {title="app/Providers/AppServiceProvider.php"}
// In your service provider
public function boot(): void
{
    Gate::policy(Content::class, YourContentPolicy::class);
}
```

**Option 2: Override Default Policy in Config**

```php {title="config/inspirecms.php}
return [
    'models' => [
        'policies' => [
            'content' => \App\Policies\YourContentPolicy::class,
        ]
    ],
];
```

### Policy Class Example

```php
namespace App\Policies;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Models\Contracts\Content;

class YourContentPolicy
{
    /**
     * Determine if user can approve content
     */
    public function approved($user, $content)
    {
        return $user->hasAnyRole(['editor', 'senior_editor']);
    }
    
    /**
     * Determine if user can publish content
     */
    public function publish($user, $content)
    {
        return $user->hasRole('senior_editor');
    }
}
```

### Overriding the Content Model

1. **Create Extended Model**

```php
namespace App\Models;

use SolutionForest\InspireCms\Models\Content as BaseModel;
use SolutionForest\InspireCms\Models\Contracts\Content as ContentContract;

class Content extends BaseModel implements ContentContract
{
    public function isPublished(): bool
    {
        // Published if status is 1 (published) or 5 (approved)
        return $this->status === 1 || $this->status === 5;
    }
    
    public function scopeWhereIsPublished($query, bool $condition = true)
    {
        if ($condition) {
            return $query->where(function($query) {
                $query->where('status', 1)->orWhere('status', 5);
            });
        }
        
        return $query->where(function($query) {
            $query->where('status', '!=', 1)->where('status', '!=', 5);
        });
    }
}
```

2. **Update Configuration**

```php {title="config/inspirecms.php"}
return [
    'models' => [
        'content' => \App\Models\Content::class,
    ],
];
```

### Complete Implementation Example

**Service Provider Setup**

```php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use SolutionForest\InspireCms\Facades\ContentStatusManifest;
use SolutionForest\InspireCms\DataTypes\Manifest\ContentStatusOption;
use Filament\Actions\Action;
use SolutionForest\InspireCms\Models\Content;
use Illuminate\Support\Facades\Gate;

class ContentPublishingServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Register custom status
        ContentStatusManifest::replaceOption(
            new ContentStatusOption(
                value: 5,
                name: 'approved',
                formAction: fn () => Action::make('approved')
                    ->label('Approve')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->authorize('approved')
                    ->action(function (null | Content $record, Action $action, $livewire) {
                        if (is_null($record)) {
                            $action->cancel();
                            return;
                        }

                        $record->status = 5;
                        $record->save();

                        $action->success();
                        $livewire->notify('success', 'Content has been approved!');
                    }),
            )
        );
        
        // Register policy
        Gate::policy(Content::class, \App\Policies\ContentApprovalPolicy::class);
    }
}
```

**Workflow Configuration**

This creates a workflow where:
- Authors create and submit content (draft)
- Editors review and approve content (approved)
- Senior editors make final publishing decisions (published)

Content is visible on the frontend if either approved or published.

---

## Adding Filament Components

### Adding Clusters

**Option 1: Using Artisan**
```bash
php artisan make:filament-cluster YourClusterName --panel=cms
```

**Option 2: Manual Creation**
Create your cluster and add it to `filament.cluster` in the config file.

**Required Implementation:**
```php
use Filament\Clusters\Cluster;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSection;

class Test extends Cluster implements ClusterSection
{
    use ClusterSectionTrait;
}
```

### Adding Resources

**Option 1: Using Artisan**
```bash
php artisan make:filament-resource YourResourceName --panel=cms
```

**Option 2: Manual Creation**
Create your resource and add it to `filament.resources` in the config file.

**Required Implementation:**
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

### Adding Pages

**Option 1: Using Artisan**
```bash
php artisan make:filament-page YourPageName --panel=cms
```

**Option 2: Manual Creation**
Create your page and add it to `filament.pages` in the config file.

**Required Implementation:**
```php
use Filament\Pages\Page;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionPageTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionPage;
use SolutionForest\InspireCms\Filament\Contracts\GuardPage;

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

> [!IMPORTANT] 
> Always ensure your Cluster implements the `SolutionForest\InspireCms\Filament\Contracts\ClusterSection` interface.

---

## Customize Model

```php {title="app/Providers/AppServiceProvider.php"}
public function register(): void
{
    \SolutionForest\InspireCms\Facades\ModelManifest::replace(
        \SolutionForest\InspireCms\Models\Contracts\Content::class,
        Your\Model\Class::class,
    );
}
```