---
title: Content
slug: fe-content
path: docs/v1/fe-content
uri: /docs/v1/fe-content
heading: Content
brief: Learn how to work with content in your frontend templates.

quick_links: []
---


## Content Helper

The `inspirecms_content()` helper provides access to content items throughout your frontend templates:

```php
// Get content by ID
$content = inspirecms_content()->findByIds(ids: '550e8400-e29b-41d4-a716-446655440000', limit: 1)->first();

// Get content by slug
$content = inspirecms_content()->findByRealPath(path: 'about-us', limit: 1)->first();

// Get multiple content items
$contents = inspirecms_content()->findByIds(['550e8400-e29b-41d4-a716-446655440000', '7f1b96c0-d4f0-11ed-afa1-0242ac120002']);

// Get published content under 'home'
$contents = inspirecms_content()->getUnderRealPath(path: 'home', isPublished: true);

// Get paginated published content under 'home'
$contents = inspirecms_content()->getPaginatedUnderRealPath(path: 'home', isPublished: true, page: 1, perPage: 10);

// Get content by document type
$blogPosts = inspirecms_content()->getByDocumentType(documentType: 'blog_post', isPublished: true);

// Get paginated content by document type
$blogPosts = inspirecms_content()->getPaginatedByDocumentType(documentType: 'blog_post', isPublished: true, page: 1, perPage: 10);
```

> [!note]
>
> These functions return Content model instances. To access content properties and use them in templates, convert the model to a DTO using the `toDto()` method:
>
> ```php
> $contentDto = $content->toDto($locale ?? app()->getLocale());
> $title = $contentDto->getTitle();
> ```

---

## Accessing Content Properties

### Basic Properties

Access core content attributes:

```php
$title = $content->getTitle();          // Get content title
$slug = $content->slug;                 // Get content slug
$url = $content->getUrl();              // Get content URL
$locale = $content->getLocale();        // Get content locale
$publishedAt = $content->publishAt;     // Publication date
```

### Custom Fields

Use property directives in Blade templates to access custom fields:

```php
<!-- Single property -->
<h1>@property('hero', 'title')</h1>

<!-- With custom variable name -->
@property('hero', 'images', 'custom_images')
@foreach($custom_images ?? [] as $image)
    <img src="{{ $image->getUrl() }}">
@endforeach

<!-- Value is from $blogDTO, variable available as $blog_category -->
@property('blog', 'category', null, $blogDTO)

<!-- Array properties -->
@propertyArray('gallery', 'images')
@foreach($gallery_images ?? [] as $image)
    <img src="{{ $image->getUrl() }}" alt="{{ $image->alt_text }}">
@endforeach

<!-- Conditional display -->
@propertyNotEmpty('hero', 'button_text')
    <a href="@property('hero', 'button_link')" class="btn">
        {{ $hero_button_text }}
    </a>
@endif
```

You can also access properties programmatically:

```php
// Check if property exists
if ($content->hasProperty('hero', 'title')) {
    // Get property value
    $title = $content->getPropertyValue('hero', 'title');

    // Get property value with fallback
    $subtitle = $content->getPropertyValue('hero', 'subtitle') ?? 'Default subtitle';

    // Get multilingual property with specific locale
    $frenchTitle = $content->getPropertyValue('hero', 'title', 'fr');
}

// Check if property group exists
if ($content->hasPropertyGroup('hero')) {
    // Get entire property group
    $heroGroup = $content->getPropertyGroup('hero');
}
```

---

## Content Relationships

Access related content and structure:

```php
// Get parent content
$parent = $content->getParent();

// Get all child content
$children = $content->getChildren();

// Get ancestors in hierarchical order
$ancestors = $content->getAncestors();
```

---

## Content Filtering and Sorting

Filter and sort content collections:

```php
// Get recent blog posts
$recentPosts = inspirecms_content()->getUnderRealPath(
    path: 'blogs',
    isPublished: true,
    sorting: ['__latest_version_publish_dt' => 'desc'],
    limit: 5,
);

// Get paginated recent blog posts
$recentPosts = inspirecms_content()->getPaginatedUnderRealPath(
    path: 'blogs',
    page: 1,
    perPage: 10,
    isPublished: true,
    sorting: ['__latest_version_publish_dt' => 'desc'],
);

// Filter by custom fields
$featuredPosts = inspirecms_content()->getByDocumentType(
        documentType: 'blog_post',
        limit: 50,
    )
    ->toDto($locale ?? app()->getLocale())
    ->filter(function($post) {
        return $post->getPropertyValue('blog', 'is_featured') === true;
    });
```

---

## Working with Multiple Languages

Access content in different languages:

```php
// Get content in specific language
$frenchContent = $contentModel->toDto('fr');

// Check if content is available in a language
$hasSpanish = $content->hasTranslation('es');

// Loop through all available translations
foreach (inspirecms()->getAllAvailableLanguages() as $locale => $langDto) {
    $translatedTitle = $content->getTitle($locale);
    // Do something with the translation
}
```

---

## Pagination

Paginate content collections:

```php
// In your controller
$paginatedContent = inspirecms_content()->getPaginatedByDocumentType(documentType: 'blog_post', perPage: 10);

// In your Blade template
@foreach ($paginatedContent as $post)
    <h2>{{ $post->getTitle() }}</h2>
    <p>{{ $post->getPropertyValue('blog', 'excerpt') }}</p>
@endforeach

{{ $paginatedContent->links() }}
```

---

## Best Practices

-   Use `inspirecms_content()` helper for retrieving content instead of direct database queries
-   Always check if properties exist before using them
-   Cache frequent content queries for better performance
-   For large content sets, use pagination to improve page load times
-   Use property directives in Blade templates for cleaner syntax

> [!note]
>
> For examples of displaying content in layouts, see the [Layouts](./fe-layouts){.doc-link} documentation.