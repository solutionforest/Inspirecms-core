---
title: Routing
slug: routing
path: docs/v1/routing
uri: /docs/1.x/routing
---
# Routing

InspireCMS provides a flexible and powerful routing system to control how content is accessed on your website. This guide covers how routing works, customization options, and best practices.

---

## How Routing Works

InspireCMS uses a hierarchical routing system that combines standard Laravel routes with content-based dynamic routing. The system determines which content to display based on the requested URL.

### Route Types

InspireCMS handles several types of routes:

1. **Content Routes**: Dynamic routes that map to content entries
2. **Admin Routes**: Routes for the administrative control panel
3. **Asset Routes**: Routes for media and static assets
4. **Custom Routes**: Developer-defined routes for custom functionality

---

## Content Routing

Content routing is the heart of the system, determining how URLs map to content entries.

### URL Structure

By default, content URLs follow a hierarchical structure:

```plaintext
/parent-section/child-section/content-slug
```

For example:

-   `/about`: A top-level "About" page
-   `/products/widgets/blue-widget`: A "Blue Widget" page under the "Widgets" section of "Products"

### Route Patterns

Each content item can have:

1. **Default Route**: The system-generated path based on content hierarchy
2. **Custom Route**: A user-defined URL that overrides the default
3. **Aliases**: Additional URLs that redirect to the content

### Setting Content Routes

To set a custom route for content:

1. Edit the content item in the admin panel
2. Navigate to the "**URL & Routing**" button in the top-right corner of the interface
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

```php {title="InspireCms.php"}

use Illuminate\Support\Facades\Route;

public function routes(): void
{
    Route::name('inspirecms.')
        ->group(function () {
            Route::name('sitemap')
                ->get('sitemap.xml', CmsControllers\SitemapController::class);

            $frontendMiddlewares = InspireCmsConfig::get('frontend.routes.middleware', [
                CmsMiddlewares\SetUpPoweredBy::class,
            ]);
            Route::name('frontend.')
                ->middleware($frontendMiddlewares)
                ->group(function () {

                        $factory = ContentSegmentFactory::create();
                        $customFrontendRoutes = Schema::hasTable(InspireCmsConfig::getContentRouteTableName()) && Schema::hasTable('cache')
                                ? $this->getContentRoutes()
                                : [];

                        foreach ($customFrontendRoutes as $index => $item) {
                                Route::any($item['uri'], CmsControllers\FrontendController::class)
                                        ->where($item['regex_constraints'] ?? [])
                                        ->name($item['alias'] ?? 'content_' . $index);
                        }

                        // default route
                        Route::any($factory->getDefaultRoutePattern(), CmsControllers\FrontendController::class)
                                ->where($factory->getDefaultRouteConstraints())
                                ->name('default');
                });
        });
}
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
    protected function getContentAndLocaleByRoute($route)
    {
        // Custom implementation for finding content based on route
        // You can add additional logic here before or after the parent method

        return parent::getContentAndLocaleByRoute($route);
    }

    // Override other methods as needed
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

#### Use Cases for Custom Resolvers

-   Implementing A/B testing for content pages
-   Adding personalization based on user attributes
-   Supporting custom URL formats or legacy URL structures
-   Integrating with external content sources
-   Implementing content access restrictions based on user role or subscription

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
php artisan inspirecms:list-routes
```

This command shows all registered content routes with:

-   URL pattern
-   Name
-   Bindings
-   Middleware

## Best Practices

-   **Intuitive Structure**: Design URLs that are logical and easy to remember
-   **SEO-Friendly**: Use descriptive words in URLs instead of IDs or codes
-   **Consistent Patterns**: Maintain consistent URL structures for similar content
-   **Avoid Deep Nesting**: Keep URL hierarchies reasonably flat (3-4 levels max)
-   **Use Redirects**: Maintain redirects when changing established URLs
-   **Cache Management**: Clear route caches after significant content structure changes
-   **Performance**: Monitor route resolution times and optimize when necessary
-   **Avoid Conflicts**: Prevent content routes from conflicting with system routes
