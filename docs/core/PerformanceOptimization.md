# Performance Optimization

InspireCMS is designed to be fast and efficient, but as your site grows, optimizing performance becomes increasingly important. This guide covers various techniques and best practices to ensure your InspireCMS site remains responsive and speedy.

## Performance Overview

Performance optimization in InspireCMS involves several key areas:

- **Server Configuration**: Optimizing your hosting environment
- **Database Optimization**: Ensuring efficient queries and indexing
- **Application Caching**: Leveraging various caching mechanisms
- **Asset Optimization**: Minimizing and efficiently serving static assets
- **Content Delivery**: Optimizing how content reaches users
- **Frontend Performance**: Making the user interface responsive

## Server Optimization

### PHP Configuration { .font-bold  .text-2xl .my-2 }

Adjust PHP settings for optimal performance:

```ini
; php.ini optimizations
memory_limit = 256M
max_execution_time = 60
opcache.enable = 1
opcache.memory_consumption = 128
opcache.interned_strings_buffer = 16
opcache.max_accelerated_files = 10000
opcache.revalidate_freq = 0
opcache.save_comments = 1
```

### Web Server Configuration { .font-bold  .text-2xl .my-2 }

**Nginx Configuration**

```nginx
# nginx.conf optimizations
http {
    # Enable gzip compression
    gzip on;
    gzip_comp_level 5;
    gzip_min_length 256;
    gzip_proxied any;
    gzip_types
        application/javascript
        application/json
        application/xml
        text/css
        text/plain
        text/xml;
    
    # Cache control
    location ~* \.(jpg|jpeg|png|gif|ico|css|js)$ {
        expires 30d;
        add_header Cache-Control "public, no-transform";
    }
    
    # PHP-FPM configuration
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_buffer_size 16k;
        fastcgi_buffers 4 16k;
    }
}
```

**Apache Configuration**

```apache
# .htaccess optimizations
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/webp "access plus 1 year"
    ExpiresByType image/svg+xml "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType text/javascript "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>

<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css application/javascript application/json
</IfModule>
```

### Process Management { .font-bold  .text-2xl .my-2 }

Configure PHP-FPM for better process management:

```ini
; php-fpm.conf
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 500
```

## Database Optimization

### Query Optimization { .font-bold  .text-2xl .my-2 }

Identify and optimize slow queries:

```php
// Enable database query logging during development
\DB::connection()->enableQueryLog();

// After running operations, check the log
$queries = \DB::getQueryLog();
foreach ($queries as $query) {
    \Log::debug($query['query'], [
        'bindings' => $query['bindings'],
        'time' => $query['time']
    ]);
}
```

### Indexing Strategy { .font-bold  .text-2xl .my-2 }

Add proper indexes to frequently queried columns:

```php
// In a migration
Schema::table('cms_contents', function (Blueprint $table) {
    // Index commonly filtered/sorted columns
    $table->index(['document_type_id']);
    $table->index(['parent_id']);
    $table->index(['status']);
    $table->index(['created_at']);
});
```

### Query Caching { .font-bold  .text-2xl .my-2 }

Cache frequent database queries:

```php
// See the Caching section for query cache examples
```

### Database Connection Pooling { .font-bold  .text-2xl .my-2 }

Consider using database connection pooling for high-traffic sites:

```php
// config/database.php
'mysql' => [
    // ...
    'pool' => [
        'min_connections' => 1,
        'max_connections' => 10,
        'connection_timeout' => 10.0,
    ],
],
```

## Application Optimization

### Route Caching { .font-bold  .text-2xl .my-2 }

Cache your application routes:

```bash
php artisan route:cache
```

### Configuration Caching { .font-bold  .text-2xl .my-2 }

Cache your application configuration:

```bash
php artisan config:cache
```

### View Caching { .font-bold  .text-2xl .my-2 }

Cache compiled Blade templates:

```bash
php artisan view:cache
```

> **Note**: Clear these caches during development with the corresponding `clear` commands: `php artisan route:clear`, `php artisan config:clear`, and `php artisan view:clear`.

### Autoloader Optimization { .font-bold  .text-2xl .my-2 }

Optimize Composer's autoloader in production:

```bash
composer install --optimize-autoloader --no-dev
```

### Queue System { .font-bold  .text-2xl .my-2 }

Use queues for time-consuming tasks to make your application more responsive:

```php
// Process media optimization in the background
dispatch(new OptimizeMediaJob($mediaAsset));

// Generate sitemaps asynchronously
dispatch(new GenerateSitemapJob())->onQueue('low');
```

Configure a queue worker in production:

```bash
# Start queue worker (on server)
php artisan queue:work --queue=high,default,low --tries=3

# For use with Supervisor
[program:inspirecms-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/project/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/path/to/project/storage/logs/worker.log
```

## Content Delivery Optimization

### CDN Integration { .font-bold  .text-2xl .my-2 }

Use a Content Delivery Network for assets:

```php
// config/filesystems.php
'disks' => [
    'cdn' => [
        'driver' => 's3',
        'key' => env('CDN_ACCESS_KEY_ID'),
        'secret' => env('CDN_SECRET_ACCESS_KEY'),
        'region' => env('CDN_DEFAULT_REGION'),
        'bucket' => env('CDN_BUCKET'),
        'url' => env('CDN_URL'),
    ],
],

// config/inspirecms.php
'media' => [
    'media_library' => [
        'disk' => 'cdn',
        // other settings...
    ],
],
```

### Lazy Loading { .font-bold  .text-2xl .my-2 }

Implement lazy loading for images and heavy content:

```php
<img 
    src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" 
    data-src="{{ $image->getUrl() }}" 
    class="lazy-load" 
    alt="{{ $image->alt_text }}"
>
```

Include a JavaScript lazy loader:

```javascript
document.addEventListener("DOMContentLoaded", function() {
    let lazyImages = [].slice.call(document.querySelectorAll(".lazy-load"));
    
    if ("IntersectionObserver" in window) {
        let lazyImageObserver = new IntersectionObserver(function(entries, observer) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    let lazyImage = entry.target;
                    lazyImage.src = lazyImage.dataset.src;
                    if (lazyImage.dataset.srcset) {
                        lazyImage.srcset = lazyImage.dataset.srcset;
                    }
                    lazyImage.classList.remove("lazy-load");
                    lazyImageObserver.unobserve(lazyImage);
                }
            });
        });
        
        lazyImages.forEach(function(lazyImage) {
            lazyImageObserver.observe(lazyImage);
        });
    }
});
```

### Image Optimization { .font-bold  .text-2xl .my-2 }

Configure automatic image optimization:

```php
// config/inspirecms.php
'media' => [
    'image_optimization' => [
        'enabled' => true,
        'quality' => 80,
        'convert_to_webp' => true,
        'responsive_images' => [
            'enabled' => true,
            'widths' => [480, 768, 1280, 1920],
        ],
    ],
],
```

## Frontend Performance

### Asset Bundling { .font-bold  .text-2xl .my-2 }

Use Laravel Mix or Vite to bundle and optimize assets:

```js
// vite.config.js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    build: {
        minify: 'terser',
        terserOptions: {
            compress: {
                drop_console: true,
            },
        },
    },
});
```

Use in templates:

```php
@vite(['resources/css/app.css', 'resources/js/app.js'])
```

### Critical CSS { .font-bold  .text-2xl .my-2 }

Inline critical CSS for faster initial rendering:

```php
<head>
    <!-- Inline critical styles for above-the-fold content -->
    <style>
        /* Critical CSS goes here */
        .hero-section { /* ... */ }
        .main-navigation { /* ... */ }
        /* ... */
    </style>
    
    <!-- Defer non-critical CSS -->
    <link rel="preload" href="{{ asset('css/main.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="{{ asset('css/main.css') }}"></noscript>
</head>
```

### JavaScript Optimization { .font-bold  .text-2xl .my-2 }

Load JavaScript efficiently:

```html
<!-- Defer non-critical JavaScript -->
<script src="{{ asset('js/app.js') }}" defer></script>

<!-- For critical functionality, use inline or async scripts -->
<script>
    // Critical JS goes here
    document.querySelector('body').classList.add('js-loaded');
</script>
```

## Monitoring and Profiling

### Laravel Telescope { .font-bold  .text-2xl .my-2 }

Install Laravel Telescope for monitoring:

```bash
composer require laravel/telescope --dev
```

Configure in `config/telescope.php`:

```php
'enabled' => env('TELESCOPE_ENABLED', false),

'middleware' => [
    'web',
    \Laravel\Telescope\Http\Middleware\Authorize::class,
],

'watchers' => [
    \Laravel\Telescope\Watchers\QueryWatcher::class => [
        'enabled' => env('TELESCOPE_QUERY_WATCHER', true),
        'slow' => 100,
    ],
    // Other watchers...
],
```

### Application Profiling { .font-bold  .text-2xl .my-2 }

Use Laravel Debugbar for profiling:

```bash
composer require barryvdh/laravel-debugbar --dev
```

### Performance Testing { .font-bold  .text-2xl .my-2 }

Implement performance testing in your development workflow:

```bash
# Using k6 for load testing
k6 run -u 50 -d 30s tests/performance/load-homepage.js
```

Example test script:

```javascript
// tests/performance/load-homepage.js
import http from 'k6/http';
import { check, sleep } from 'k6';

export default function() {
    let res = http.get('https://your-site.com/');
    check(res, { 'status is 200': (r) => r.status === 200 });
    sleep(1);
}
```

## Advanced Optimization Techniques

### TTFB Optimization { .font-bold  .text-2xl .my-2 }

Improve Time To First Byte with these techniques:

1. Enable OPcache
2. Use database connection pooling
3. Optimize routing to reduce bootstrap overhead
4. Cache rendered content for anonymous users
5. Implement edge caching with CDN

### Memory Optimization { .font-bold  .text-2xl .my-2 }

Reduce memory usage in your application:

```php
// Optimize collection handling for large datasets
$chunks = InspireCmsConfig::getContentModelClass()::cursor()->map(function ($content) {
    // Process each item with minimal memory footprint
    return $content->id;
});
```

### Database Optimization Commands { .font-bold  .text-2xl .my-2 }

Create commands to optimize database performance:

```php
namespace App\Console\Commands;

use Illuminate\Console\Command;

class OptimizeDatabaseCommand extends Command
{
    protected $signature = 'inspirecms:optimize-database';
    protected $description = 'Optimize database tables and indexes';

    public function handle()
    {
        $this->info('Optimizing database tables...');
        \DB::statement('OPTIMIZE TABLE cms_contents, cms_field_groupables, cms_content_paths');
        
        $this->info('Analysis of content table');
        $analysis = \DB::select('ANALYZE TABLE cms_contents');
        $this->table(['Table', 'Operation', 'Msg Type', 'Msg Text'], $analysis);
        
        return Command::SUCCESS;
    }
}
```

### Database Sharding { .font-bold  .text-2xl .my-2 }

For very large sites, consider database sharding:

```php
// config/database.php
'connections' => [
    'mysql_content' => [
        'driver' => 'mysql',
        'host' => env('DB_CONTENT_HOST', '127.0.0.1'),
        // other configuration...
    ],
    'mysql_users' => [
        'driver' => 'mysql',
        'host' => env('DB_USERS_HOST', '127.0.0.1'),
        // other configuration...
    ],
],
```

Update models to use specific connections:

```php
// Example configuration for content model
namespace App\Models;

use SolutionForest\InspireCms\Models\Content as BaseContent;

class Content extends BaseContent
{
    protected $connection = 'mysql_content';
}
```

## Environment-Specific Optimizations

### Development Environment { .font-bold  .text-2xl .my-2 }

Optimize for developer experience:

```php
// config/app.php for development
'debug' => true,
'cache' => false,

// .env.development
APP_DEBUG=true
DEBUGBAR_ENABLED=true
TELESCOPE_ENABLED=true
CACHE_DRIVER=array
```

### Production Environment { .font-bold  .text-2xl .my-2 }

Optimize for performance:

```php
// config/app.php for production
'debug' => false,
'cache' => true,

// .env.production
APP_DEBUG=false
DEBUGBAR_ENABLED=false
TELESCOPE_ENABLED=false
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
```

### CI/CD Optimization { .font-bold  .text-2xl .my-2 }

Include performance checks in your CI/CD pipeline:

```yaml
# .github/workflows/performance.yml
name: Performance Tests

on:
  push:
    branches: [ main ]

jobs:
  performance:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - name: Install k6
        run: |
          curl -L https://github.com/loadimpact/k6/releases/download/v0.33.0/k6-v0.33.0-linux-amd64.tar.gz | tar xzf -
          sudo cp k6-v0.33.0-linux-amd64/k6 /usr/local/bin/k6
      
      - name: Run Performance Tests
        run: k6 run tests/performance/load-tests.js
```

## Best Practices Checklist

1. **Enable Production Mode**: Always use production mode in live environments
2. **Cache Everything**: Implement multiple cache layers
3. **Optimize Database**: Index properly and monitor query performance
4. **Minimize HTTP Requests**: Combine assets and use HTTP/2
5. **Optimize Images**: Compress images and use responsive sizes
6. **Use CDN**: Serve assets from a CDN
7. **Implement Queues**: Move time-consuming tasks to the background
8. **Monitor Performance**: Use tools to identify and address bottlenecks
9. **Optimize Critical Path**: Focus on above-the-fold content delivery
10. **Regular Maintenance**: Run periodic cleanup and optimization tasks

By applying these optimization techniques, your InspireCMS site will deliver content efficiently, providing a better experience for visitors and content editors alike.