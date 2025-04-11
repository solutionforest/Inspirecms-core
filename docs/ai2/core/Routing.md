# Routing

InspireCMS provides a flexible and powerful routing system to control how content is accessed on your website. This guide covers how routing works, customization options, and best practices.

## How Routing Works in InspireCMS

InspireCMS uses a hierarchical routing system that combines standard Laravel routes with content-based dynamic routing. The system determines which content to display based on the requested URL.

### Route Types

InspireCMS handles several types of routes:

1. **Content Routes**: Dynamic routes that map to content entries
2. **Admin Routes**: Routes for the administrative control panel
3. **Asset Routes**: Routes for media and static assets
4. **API Routes**: Routes for programmatic access to content
5. **Custom Routes**: Developer-defined routes for custom functionality

## Content Routing

Content routing is the heart of the system, determining how URLs map to content entries.

### URL Structure

By default, content URLs follow a hierarchical structure:

```
/parent-section/child-section/content-name
```

For example:
- `/about`: A top-level "About" page
- `/products/widgets/blue-widget`: A "Blue Widget" page under the "Widgets" section of "Products"

### Route Patterns

Each content item can have:

1. **Default Route**: The system-generated path based on content hierarchy
2. **Custom Route**: A user-defined URL that overrides the default
3. **Aliases**: Additional URLs that redirect to the content

### Setting Content Routes

To set a custom route for content:

1. Edit the content item in the admin panel
2. Navigate to the "URL & Routing" section
3. Enter your custom route in the "Custom URL" field
4. Save the content

### Route Constraints

You can define URL patterns with parameters:

```
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

## Content Slugs

Content slugs are URL-friendly versions of content titles used in routes.

### Automatic Slug Generation

When creating content, InspireCMS automatically generates a slug from the title:

- "Hello World" becomes "hello-world"
- "Top 10 Tips & Tricks" becomes "top-10-tips-tricks"

### Custom Slugs

To use a custom slug:

1. Edit the content item
2. Find the "Slug" field (often near the title)
3. Enter your custom slug
4. Save the content

### Slug Validation

Slugs must:
- Contain only lowercase letters, numbers, and hyphens
- Not conflict with existing routes or reserved words
- Be unique within their parent section

## Route Registration

InspireCMS registers routes during application bootstrap:

```php
// From InspireCms.php
public function routes(): void
{
    Route::name('inspirecms.asset')
        ->get('assets/{key}', AssetController::class)
        ->middleware([
            'cache.headers:public;max_age=2628000;etag',
        ]);

    Route::name('inspirecms.sitemap')
        ->get('sitemap.xml', SitemapController::class);

    Route::name('inspirecms.frontend.')
        ->middleware(InspireCmsConfig::get('frontend.routes.middlewares', []))
        ->group(function () {
            // Content routes registered here
            // ...
            
            // Default route fallback
            Route::any('{path?}', FrontendController::class)
                ->where('path', '.*')
                ->name('default');
        });
}
```

## Customizing Routes

### Adding Custom Middleware

Apply middleware to frontend routes:

```php
// config/inspirecms.php
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

To handle specific routes with custom logic:

```php
use SolutionForest\InspireCms\Facades\RouteManifest;

// In your service provider
public function boot()
{
    RouteManifest::register([
        'pattern' => '/special-section/{id}',
        'handler' => [\App\Http\Controllers\SpecialController::class, 'show'],
        'name' => 'special.show',
        'constraints' => [
            'id' => '[0-9]+'
        ]
    ]);
}
```

### Route Interceptors

For more advanced route handling:

```php
use SolutionForest\InspireCms\Facades\RouteInterceptor;

// In your service provider
public function boot()
{
    RouteInterceptor::register(function ($request, $next) {
        // Check if route starts with /campaign/
        if (strpos($request->path(), 'campaign/') === 0) {
            // Log campaign visit
            logger()->info('Campaign visit: ' . $request->path());
        }
        
        return $next($request);
    });
}
```

## Route Caching

InspireCMS caches content routes for performance:

### Cache Configuration

```php
// config/inspirecms.php
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
php artisan inspirecms:clear-route-cache
```

This is useful after:
- Bulk content changes
- Changing route configuration
- Upgrading InspireCMS

## Advanced Routing

### Route Localization

For multilingual sites:

```php
use SolutionForest\InspireCms\Facades\RouteManifest;

// In your service provider
public function boot()
{
    // Register language-prefixed routes
    RouteManifest::registerGroup([
        'prefix' => '{locale}',
        'where' => ['locale' => 'en|fr|es'],
        'middleware' => [\App\Http\Middleware\SetLocale::class],
    ], function () {
        // Routes registered here will have language prefix
        // e.g., /en/about, /fr/about, /es/about
    });
}
```

### Content Route Resolution

The route resolution process:

1. Incoming request URL is parsed
2. System checks for exact matches in the content route table
3. If no exact match, pattern routes are checked
4. If still no match, default route handler is invoked
5. Content is retrieved based on resolved route
6. Response is generated with the appropriate template

### Custom Segment Provider

For custom URL structure handling:

```php
namespace App\Services;

use SolutionForest\InspireCms\Content\SegmentProviderInterface;

class CustomSegmentProvider implements SegmentProviderInterface
{
    public function getSegments(string $uri): array
    {
        // Custom logic to extract segments from URI
        return [/* your segments */];
    }
}
```

Register in configuration:

```php
// config/inspirecms.php
'frontend' => [
    'segment_provider' => \App\Services\CustomSegmentProvider::class,
],
```

## Redirects and URL Management

### Managing Redirects

InspireCMS provides a redirect management system:

1. Navigate to **Settings → Redirects** in the admin panel
2. Click **Add Redirect**
3. Specify:
   - Source path
   - Destination path
   - Redirect type (301 permanent, 302 temporary)
4. Save the redirect

### Automatic Redirects

The system can automatically create redirects when:

- Content is moved or renamed
- Custom routes are changed
- Content is unpublished (optional)

Configure automatic redirects:

```php
// config/inspirecms.php
'content' => [
    'auto_redirects' => [
        'enabled' => true,
        'on_slug_change' => true,
        'on_unpublish' => false,
    ],
],
```

## Route Debugging

For troubleshooting routing issues:

```bash
php artisan inspirecms:list-routes
```

This command shows all registered content routes with:
- URL pattern
- Content ID
- Handler class
- Middleware

## Best Practices

- **Intuitive Structure**: Design URLs that are logical and easy to remember
- **SEO-Friendly**: Use descriptive words in URLs instead of IDs or codes
- **Consistent Patterns**: Maintain consistent URL structures for similar content
- **Avoid Deep Nesting**: Keep URL hierarchies reasonably flat (3-4 levels max)
- **Use Redirects**: Maintain redirects when changing established URLs
- **Cache Management**: Clear route caches after significant content structure changes
- **Performance**: Monitor route resolution times and optimize when necessary
- **Avoid Conflicts**: Prevent content routes from conflicting with system routes