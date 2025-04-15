 # Caching

InspireCMS implements multiple caching layers to optimize performance. This guide explains the caching system, how to configure it, and best practices for managing cache in your application.

## Caching Overview

Caching in InspireCMS accelerates content delivery by storing frequently accessed data in fast-access storage. The system employs several caching strategies:

- **Content Cache**: Speeds up content retrieval
- **Navigation Cache**: Makes menu loading faster
- **Route Cache**: Improves URL resolution
- **Template Cache**: Accelerates template rendering
- **Query Cache**: Reduces database load
- **Asset Cache**: Optimizes media delivery
- **View Cache**: Improves rendering performance

## Cache Configuration

Configure caching in your application's configuration files.

### Main Cache Settings

Configure basic cache settings in `config/inspirecms.php`:

```php
'cache' => [
    'languages' => [
        'key' => 'inspirecms.languages',
        'ttl' => 60 * 60 * 24, // 24 hours in seconds
    ],
    'navigation' => [
        'key' => 'inspirecms.navigation',
        'ttl' => 60 * 60 * 24,
    ],
    'content_routes' => [
        'key' => 'inspirecms.content_routes',
        'ttl' => 120 * 60 * 24, // 120 days in seconds
    ],
    'key_value' => [
        'ttl' => 60 * 60 * 24,
    ],
],
```

### Laravel Cache Driver Configuration

InspireCMS uses Laravel's caching system. Configure the cache driver in `config/cache.php`:

```php
'default' => env('CACHE_DRIVER', 'file'),

'stores' => [
    'file' => [
        'driver' => 'file',
        'path' => storage_path('framework/cache/data'),
    ],
    'redis' => [
        'driver' => 'redis',
        'connection' => 'cache',
        'lock_connection' => 'default',
    ],
    'memcached' => [
        'driver' => 'memcached',
        'persistent_id' => env('MEMCACHED_PERSISTENT_ID'),
        'sasl' => [
            env('MEMCACHED_USERNAME'),
            env('MEMCACHED_PASSWORD'),
        ],
        'options' => [
            // Memcached::OPT_CONNECT_TIMEOUT => 2000,
        ],
        'servers' => [
            [
                'host' => env('MEMCACHED_HOST', '127.0.0.1'),
                'port' => env('MEMCACHED_PORT', 11211),
                'weight' => 100,
            ],
        ],
    ],
],
```

## Content Caching

InspireCMS automatically caches content to reduce database queries.

### Content Cache Configuration

Adjust content cache settings:

```php
// config/inspirecms.php
'cache' => [
    'content' => [
        'enabled' => true,
        'ttl' => 3600, // 1 hour in seconds
        'per_user' => false, // Whether to cache content per user
    ],
],
```

### Content Cache Tags

Content is cached with tags for easy invalidation:

- `content:{id}` - Individual content item
- `content:type:{type}` - All content of a specific type
- `content:lang:{lang}` - Content in a specific language

Example of accessing cached content:

```php
use Illuminate\Support\Facades\Cache;

// Get content with fallback to database
$content = Cache::tags(['content:123'])->remember('content:123', 3600, function () {
    return InspireCmsConfig::getContentModelClass()::find(123);
});
```

### Invalidating Content Cache

Automatically invalidated when content is updated, but can also be cleared manually:

```php
// Clear cache for a specific content item
Cache::tags(['content:123'])->flush();

// Clear cache for all content of a specific type
Cache::tags(['content:type:blog'])->flush();
```

## Navigation Caching

Navigation structure is cached for improved performance.

### Navigation Cache Configuration

```php
// config/inspirecms.php
'cache' => [
    'navigation' => [
        'key' => 'inspirecms.navigation',
        'ttl' => 60 * 60 * 24, // 24 hours in seconds
    ],
],
```

### Working with Navigation Cache

Example of cached navigation retrieval:

```php
// This method internally uses caching
$navigation = inspirecms()->getNavigation('main', 'en');
```

### Invalidating Navigation Cache

Clear the navigation cache manually:

```php
// Clear all navigation cache
\SolutionForest\InspireCms\Facades\InspireCms::forgetCachedNavigation();

// Clear category-specific cache
Cache::forget("inspirecms.navigation.main.en");
```

## Route Caching

InspireCMS caches content routes for faster URL resolution.

### Route Cache Configuration

```php
// config/inspirecms.php
'cache' => [
    'content_routes' => [
        'key' => 'inspirecms.content_routes',
        'ttl' => 120 * 60 * 24, // 120 days in seconds
    ],
],
```

### Route Cache Commands

In addition to Laravel's route caching, clear InspireCMS route cache:

```bash
php artisan inspirecms:clear-route-cache
```

## Template Caching

Templates are cached at different levels for optimal performance.

### View Cache

Laravel's view cache for compiled templates:

```bash
# Cache all Blade templates
php artisan view:cache

# Clear view cache
php artisan view:clear
```

### Fragment Caching

Cache specific parts of templates:

```php
@php
$cacheKey = "content_block_{$content->id}_" . md5(json_encode($content_block_data));
$cacheTtl = 60 * 24; // 24 hours in minutes
@endphp

@cache($cacheKey, $cacheTtl)
    <div class="complex-content-block">
        <!-- Complex or slow-rendering content -->
        @foreach($content_block_data as $item)
            <div class="item">{{ $item->title }}</div>
        @endforeach
    </div>
@endcache
```

## Query Caching

Cache database queries to reduce load on your database.

### Model Cache

Cache model queries directly:

```php
use Illuminate\Support\Facades\Cache;

// Get all published blog posts, cached for 1 hour
$posts = Cache::remember('published_blog_posts', 3600, function () {
    return InspireCmsConfig::getContentModelClass()::whereDocumentType('blog')
        ->whereIsPublished()
        ->latest()
        ->get();
});
```

### Query Cache Considerations

- Cache appropriate queries (frequently accessed, rarely changing)
- Use specific cache keys based on query parameters
- Set appropriate TTL based on data volatility
- Clear cache when data changes

## Asset Caching

Media and asset caching improves frontend performance.

### Media Cache Configuration

Configure cache headers for media assets:

```php
// config/inspirecms.php
'media' => [
    'media_library' => [
        // other settings...
        'middlewares' => [
            'cache.headers:public;max_age=2628000;etag',
        ],
    ],
],
```

### Asset Preprocessing

For frontend assets, use Laravel Mix or Vite with proper versioning:

```php
// In your template
<link rel="stylesheet" href="{{ mix('css/app.css') }}">
<script src="{{ mix('js/app.js') }}" defer></script>
```

## HTTP Cache

Implement HTTP caching for better performance.

### Cache Headers

InspireCMS sets proper cache headers for different content types:

```php
// Example middleware implementation
public function handle($request, Closure $next)
{
    $response = $next($request);
    
    // For public, unchanging content
    if ($this->shouldCache($request)) {
        $response->header('Cache-Control', 'public, max-age=3600');
    } else {
        // For dynamic or user-specific content
        $response->header('Cache-Control', 'no-store, private');
    }
    
    return $response;
}
```

### Custom Cache Middleware

Create custom cache middleware for specific sections:

```php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CacheControl
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        
        if ($request->segment(1) === 'blog') {
            // Cache blog pages for 1 hour
            $response->header('Cache-Control', 'public, max-age=3600');
        } elseif ($request->segment(1) === 'products') {
            // Cache product pages for 15 minutes
            $response->header('Cache-Control', 'public, max-age=900');
        }
        
        return $response;
    }
}
```

Register in `app/Http/Kernel.php`:

```php
protected $middlewareGroups = [
    'web' => [
        // ...
        \App\Http\Middleware\CacheControl::class,
    ],
];
```

## Cache Warming

Populate cache in advance to avoid cache misses and initial slowdowns.

### Cache Warming Command

Create a command to pre-populate cache:

```php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use SolutionForest\InspireCms\Models\Contracts\Content;

class WarmCacheCommand extends Command
{
    protected $signature = 'inspirecms:warm-cache';
    protected $description = 'Warm the InspireCMS content cache';

    public function handle()
    {
        $this->info('Warming content cache...');
        
        // Warm main navigation
        $this->info('Warming navigation cache...');
        foreach (inspirecms()->getAllAvailableLanguages() as $locale => $lang) {
            inspirecms()->getNavigation('main', $locale);
            $this->info("Cached main navigation for {$locale}");
        }
        
        // Warm homepage and main sections
        $this->info('Warming content cache...');
        $contentClass = InspireCmsConfig::getContentModelClass();
        
        // Get homepage
        $homepage = $contentClass::whereIsDefault()->first();
        if ($homepage) {
            $this->cacheContent($homepage);
        }
        
        // Cache main sections
        $mainSections = $contentClass::whereParentId($homepage->id ?? null)
            ->whereIsPublished()
            ->get();
            
        foreach ($mainSections as $section) {
            $this->cacheContent($section);
        }
        
        $this->info('Cache warming complete');
        
        return Command::SUCCESS;
    }
    
    protected function cacheContent(Content $content)
    {
        // Access properties to cache them
        $content->getTitle();
        $content->getUrl();
        $content->getSeo();
        
        // Process content properties
        foreach ($content->getPropertyGroups() as $group) {
            foreach ($group->getProperties() as $property) {
                $property->getValue();
            }
        }
        
        $this->line("Cached content: {$content->title}");
    }
}
```

Schedule cache warming:

```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    // Warm cache daily during off-peak hours
    $schedule->command('inspirecms:warm-cache')->dailyAt('3:00');
}
```

## Cache Clearing

Properly clear cache when needed to ensure fresh content.

### Cache Clear Commands

InspireCMS provides specific commands for clearing various caches:

```bash
# Clear all InspireCMS caches
php artisan inspirecms:clear-cache

# Clear specific caches
php artisan inspirecms:clear-language-cache
php artisan inspirecms:clear-route-cache
php artisan inspirecms:clear-navigation-cache
```

### Automatic Cache Clearing

InspireCMS automatically clears relevant caches when:

- Content is created, updated or deleted
- Settings are changed
- Templates are modified
- Navigation is restructured

## Monitoring Cache Performance

Track and optimize your cache performance.

### Cache Hit/Miss Tracking

Implement cache monitoring:

```php
// Custom cache wrapper
public function remember($key, $ttl, $callback)
{
    $exists = Cache::has($key);
    
    $startTime = microtime(true);
    $value = Cache::remember($key, $ttl, $callback);
    $endTime = microtime(true);
    
    $duration = ($endTime - $startTime) * 1000; // in ms
    
    $status = $exists ? 'HIT' : 'MISS';
    Log::debug("Cache {$status}: {$key} ({$duration}ms)");
    
    return $value;
}
```

### Cache Performance Dashboard

Consider implementing a cache monitoring dashboard:

- Cache hit rate
- Average cache retrieval time
- Cache storage usage
- Cache invalidation frequency

## Best Practices

1. **Use Appropriate TTL**: Set cache lifetime based on content update frequency
2. **Segment Cache**: Use specific cache keys and tags for targeted invalidation
3. **Layer Caching**: Combine different caching strategies (HTTP, application, database)
4. **Cache Busting**: Use versioning for assets to force refresh when they change
5. **Warm Cache**: Pre-populate cache for frequently accessed content
6. **Monitor Performance**: Track cache hit rates and effectiveness
7. **Avoid Over-Caching**: Don't cache highly dynamic or user-specific content
8. **Consistent Invalidation**: Clear related caches together to prevent inconsistencies