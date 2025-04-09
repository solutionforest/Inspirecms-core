# Services

## Content Service

You can access the Content Service using one of the following methods:
```php
$service = app(\SolutionForest\InspireCms\Services\ContentServiceInterface::class);
// or
$service = inspirecms_content();
```

### Example Usage

#### Find Content by IDs
Retrieve content items by their IDs:
```php
$ids = ['550e8400-e29b-41d4-a716-446655440000', '550e8400-e29b-41d4-a716-446655440001'];

$contentItems = inspirecms_content()->findByIds(
    $ids,
    isWebPage: true,
    isPublished: true,
    withRelations: ['author'],
    sorting: ['created_at' => 'desc'],
    limit: 5
);
```

#### Find Content by Route Pattern and Language ID
Retrieve content items by their route pattern and language ID:
```php
$uri = '/blog';
$contentItems = inspirecms_content()->findByRoutePatternWithLangId(
    $uri,
    isDefaultRoutePattern: true,
    isWebPage: true,
    withRelations: ['tags'],
    sorting: ['title' => 'asc'],
    limit: 10
);
```

#### Find Content by Real Path
Retrieve content items by their real path:
```php
$path = '/home/blogs/2023/10/01';
$contentItems = inspirecms_content()->findByRealPath(
    $path,
    isWebPage: true,
    isPublished: true,
    withRelations: ['category'],
    sorting: ['updated_at' => 'desc']
);
```

#### Get Content Under a Real Path
Retrieve content items under a specific real path:
```php
$path = '/home/blogs';
$contentItems = inspirecms_content()->getUnderRealPath(
    $path,
    isWebPage: true,
    isPublished: true,
    withRelations: ['comments'],
    sorting: ['created_at' => 'asc']
);
```

#### Paginate Content by IDs
Retrieve paginated content items by their IDs:
```php
$ids = ['550e8400-e29b-41d4-a716-446655440000', '550e8400-e29b-41d4-a716-446655440001'];
$paginatedContent = inspirecms_content()->getPaginatedByIds(
    $ids,
    page: 1,
    perPage: 10,
    pageName: 'page',
    isWebPage: true,
    isPublished: true,
    withRelations: ['author'],
    sorting: ['created_at' => 'desc']
);
```

#### Get Default Template for Content
Retrieve the default template for a specific content item:
```php
$content = \SolutionForest\InspireCms\InspireCmsConfig::getContentModelClass()::find(1);
$defaultTemplate = inspirecms_content()->getDefaultTemplateFor($content);
```

#### Retrieve Templates for Content
Retrieve all templates associated with a specific content item:
```php
$content = \SolutionForest\InspireCms\InspireCmsConfig::getContentModelClass()::find(1);
$templates = inspirecms_content()->getTemplatesFor($content);
```

#### Retrieve a Specific Template for Content
Retrieve a specific template by its slug for a given content item:
```php
$content = \SolutionForest\InspireCms\InspireCmsConfig::getContentModelClass()::find(1);
$templateSlug = 'blog-template';
$template = inspirecms_content()->getTemplateFor($content, $templateSlug);
```

### Customizing the Content Service
You can customize the Content Service by binding your own implementation in your application's ServiceProvider. This allows you to extend or completely replace the default content management functionality with your own business logic.

In your ServiceProvider's `boot` method, use Laravel's service container to register your custom implementation:
You can customize the Content Service by binding your own implementation:
```php
use SolutionForest\InspireCms\Services\ContentServiceInterface;

public function boot()
{
    $this->app->singleton(ContentServiceInterface::class, fn () => new YourService());
}
```