---
title: Content Routing
slug: content-routing
path: docs/v1/content-routing
uri: /docs/1.x/content-routing
heading: Content Routing
brief:
---

## Overview

By default, content URLs follow a hierarchical structure:

```plaintext
/parent-section/child-section/content-slug
```

For example:

-   `/about`: A top-level "About" page
-   `/products/widgets/blue-widget`: A "Blue Widget" page under the "Widgets" section of "Products"

---

## Setting Content Routes

To set a custom route for content:

1. Edit the content item in the admin panel
2. Navigate to the "**URL & Routing**" button in the top-right corner of the admin panel
3. Enter your custom route in the "Path" field
4. Save the content

### Route Constraints

You can define URL patterns with parameters:

```plaintext
/blog/{year}/{month}/{slug}
```

Define constraints to validate parameters:

```php
[
    'year' => '[0-9]{4}',
    'month' => '[0-9]{1,2}',
    'slug' => '[a-z0-9\-]+'
]
```

---

## Content Slugs

Content slugs are URL-friendly versions of content titles used in routes.

### Automatic Slug Generation

When creating content, InspireCMS automatically generates a slug from the title:

-   "Hello World" becomes "hello-world"
-   "Top 10 Tips & Tricks" becomes "top-10-tips-tricks"

### Custom Slugs

To use a custom slug:

1. Edit the content item
2. Find the "Slug" field (often near the title)
3. Enter your custom slug
4. Save the content

### Slug Validation

Slugs must:

-   Contain only lowercase letters, numbers, and hyphens
-   Not conflict with existing routes or reserved words
-   Be unique within their parent section

---

## Route Registration

InspireCMS registers routes during application bootstrap:

```php {title="routes/web.php"}
use Illuminate\Support\Facades\Route;

\SolutionForest\InspireCms\Facades\InspireCms::routes();
```

## Customizing Routes

### Adding Custom Middleware

Apply middleware to frontend routes:

```php {title="config/inspirecms.php"}
'frontend' => [
    'routes' => [
        'middleware' => [
            \App\Http\Middleware\TrackVisitors::class,
            \App\Http\Middleware\CacheControl::class,
        ],
    ],
],
```

### Custom Route Handlers

To handle specific routes with custom logic, register them in your application's `routes/web.php` file:

```php {title="routes/web.php"}
use Illuminate\Support\Facades\Route;

Route::get('/special-section/{id}', [\App\Http\Controllers\SpecialController::class, 'show'])
    ->name('special.show')
    ->where('id', '[0-9]+');
```

These custom routes will be processed before InspireCMS content routes, allowing you to override or extend functionality for specific URL patterns. For more information on Laravel routing, refer to the [Laravel documentation](https://laravel.com/docs/11.x/routing#basic-routing).

---

## Route Caching

InspireCMS caches content routes for performance:

### Cache Configuration

```php {title="config/inspirecms.php"}
'cache' => [
    'content_routes' => [
        'key' => 'inspirecms.content_routes',
        'ttl' => 120 * 60 * 24, // 120 days in seconds
    ],
],
```

### Clearing Route Cache

To clear the route cache:

```bash
php artisan route:clear
```

This is useful after:

-   Bulk content changes
-   Changing route configuration
-   Upgrading InspireCMS

---

## Advanced Routing

### Content Route Resolution

The route resolution process:

1. Incoming request URL is processed by the FrontendController
2. The PublishedContentResolver analyzes the request and route
3. For default routes, the system extracts URL segments and locale information
4. For custom routes, the system checks against registered content route patterns
5. Content is retrieved based on the resolved route with appropriate language settings
6. The system verifies that the content is published and configured as a web page
7. Content, template, and locale information are combined into a PublishedContentDto
8. Response is generated using the appropriate template and content data

### Custom Segment Provider

For custom URL structure handling:

```php
namespace App\Services;

use SolutionForest\InspireCms\Content\SegmentProviderInterface;

class CustomSegmentProvider implements SegmentProviderInterface
{
    public function getUrlSegmentFromDefaultRoute($route)
    {
        // Custom logic
    }
}
```

Register in configuration:

```php {title="config/inspirecms.php"}
'frontend' => [
    'segment_provider' => \App\Services\CustomSegmentProvider::class,
],
```

### Customizing PublishedContentResolver

The PublishedContentResolver is responsible for determining which content to display based on the requested URL. You can extend or replace this component to implement custom routing logic.

#### Extending the Default Resolver

Create your own resolver by extending the default implementation:

```php
namespace App\Resolvers;

use SolutionForest\InspireCms\Resolvers\PublishedContentResolver;

class CustomContentResolver extends PublishedContentResolver
{
    protected function resolve(...$args)
    {
        // Custom implementation for finding content based on route
        // You can add additional logic here before or after the parent method

        return parent::resolve($args);
    }
}
```

#### Register Your Custom Resolver

Register your custom resolver in a service provider:

```php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use SolutionForest\InspireCms\Resolvers\PublishedContentResolverInterface;
use App\Resolvers\CustomContentResolver;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(PublishedContentResolverInterface::class, CustomContentResolver::class);
    }
}
```

---

## Redirects and URL Management

### Managing Content Redirects

-   Edit the content item in the admin panel
-   Navigate to the "SEO" tab
-   Scroll down to the "Redirect" section
-   Set the destination URL and redirect type (301 permanent, 302 temporary)
-   Save the content

---

## Route Debugging

For troubleshooting routing issues:

```bash
php artisan inspirecms:routes
```

This command shows all registered content routes with:

-   URL pattern
-   Name
-   Bindings
-   Middleware
