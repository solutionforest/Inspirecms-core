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

---

## Asset Service

The Asset Service allows you to manage media assets such as images and files.

### Accessing the Service
```php
$service = app(\SolutionForest\InspireCms\Services\AssetServiceInterface::class);
// or
$service = inspirecms_asset();
```

### Example Usage

#### Find Media Assets
Retrieve multiple media assets by their keys:
```php
$mediaAssets = inspirecms_asset()->findByKeys([
    '550e8400-e29b-41d4-a716-446655440000',
    '550e8400-e29b-41d4-a716-446655440001'
]);
```

---

## Import Data Service

The Import Data Service allows you to programmatically define and import content, templates, and navigation.

### Accessing the Service
```php
$service = app(\SolutionForest\InspireCms\Services\ImportDataServiceInterface::class);
```

### Example Usage
```php
use SolutionForest\InspireCms\ImportData\Entities;

$service = app(\SolutionForest\InspireCms\Services\ImportDataServiceInterface::class);

$service->addDocumentType('home', new Entities\DocumentType(
    slug: 'home',
    showAsTable: false,
    showAtRoot: true,
    category: 'web',
    icon: 'heroicon-o-home',
    fieldGroups: ['hero'],
    templates: ['home'],
    defaultTemplate: 'home',
));

$service->addFieldGroup('hero', new Entities\FieldGroup(
    slug: 'hero',
    fields: [
        new Entities\Field(slug: 'title', type: 'text', config: ['translatable' => true]),
        new Entities\Field(slug: 'image_slider', type: 'mediaPicker', config: ['types' => ['image']]),
    ],
));

$service->addTemplate('home', '<p>Sample content</p>');
$service->addTemplate('home2');
$service->addTemplate('home3', \SolutionForest\InspireCms\Helpers\TemplateHelper::retrieveDefaultThemeContent());
$service$service->addTemplate('home2');
$service->addTemplate('home3', \SolutionForest\InspireCms\Helpers\TemplateHelper::retrieveDefaultThemeContent());
->addCon$service->addTemplate('home2');
$service->addTemplate('home3', \SolutionForest\InspireCms\Helpers\TemplateHelper::retrieveDefaultThemeContent());
tent('home', null, new Entities\Content(
    slug: 'home',
    title: ['en' => 'Home'],
    documentType: 'home',
    isDefault: true,
    properties: [
        'hero' => [
            'title' => ['en' => 'Hello World'],
            'image_slider' => [],
        ],
    ],
    publishState: 'publish'
));
$service->addNavigation(new Entities\Navigation(
    category: 'main',
    type: 'content',
    title: ['en' => 'Home'],
    contentSlugPath: 'home',
));
$service->addNavigation(new Entities\Navigation(
    category: 'main',
    type: 'content',
    title: ['en' => 'About'],
    contentSlugPath: 'home/about',
));
$servi$service->addNavigation(new Entities\Navigation(
    category: 'main',
    type: 'content',
    title: ['en' => 'Home'],
    contentSlugPath: 'home',
));
$service->addNavigation(new Entities\Navigation(
    category: 'main',
    type: 'content',
    title: ['en' => 'About'],
    contentSlugPath: 'home/about',
));

$service->run();
```

---

## Import Service

The Import Service allows you to execute import operations for predefined records.

### Accessing the Service
```php
$service = app(\SolutionForest\InspireCms\Services\ImportServiceInterface::class);
```

### Example Usage
```php
use SolutionForest\InspireCms\Models\Contracts\Import;

$record = app(Import::class)::find(1);
app(\SolutionForest\InspireCms\Services\ImportServiceInterface::class)->execute($record);
```

---

## Export Service

The Export Service allows you to execute export operations for predefined records.

### Accessing the Service
```php
$service = app(\SolutionForest\InspireCms\Services\ExportServiceInterface::class);
```

### Example Usage
```php
use SolutionForest\InspireCms\Models\Contracts\Export;

$record = app(Export::class)::find(1);
app(\SolutionForest\InspireCms\Services\ExportServiceInterface::class)->execute($record);
```

---

## Customizing Services

You can customize any service by binding your own implementation in your ServiceProvider's `boot` method:
```php
use SolutionForest\InspireCms\Services\AssetServiceInterface;
use SolutionForest\InspireCms\Services\ContentServiceInterface;

public function boot()
{
    // Customizing Content Service
    $this->app->singleton(ContentServiceInterface::class, fn () => new YourContentService());
    // Customizing Asset Service
    $this->app->singleton(AssetServiceInterface::class, fn () => new YourAssetService());
}
```