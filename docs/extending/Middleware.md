# Middleware in InspireCMS

Middleware provides a mechanism for filtering HTTP requests entering your application. For example, middleware can be used for authentication, localization, content caching, or response modification. This guide explains how to create and use custom middleware in InspireCMS.

## Understanding Middleware in InspireCMS

InspireCMS uses Laravel's middleware architecture, which allows code to be executed before and after an HTTP request is processed. There are several types of middleware in InspireCMS:

1. **Global Middleware**: Applied to every HTTP request
2. **Route Middleware**: Applied to specific routes or route groups
3. **Frontend Middleware**: Applied to frontend content routes

## Creating Custom Middleware

### Step 1: Generate a Middleware Class

You can create a middleware class using Laravel's artisan command:

```bash
php artisan make:middleware YourMiddlewareName
```

This creates a new middleware class in the `app/Http/Middleware` directory.

### Step 2: Implement the Middleware Logic

Edit the generated file to implement your middleware logic:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class YourMiddlewareName
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Code executed before the request is processed
        
        // Check for certain conditions
        if ($someCondition) {
            // You can redirect, abort, or modify the request
            return redirect('/somewhere-else');
        }
        
        // Process the request
        $response = $next($request);
        
        // Code executed after the request is processed
        
        // You can modify the response
        $response->header('X-Custom-Header', 'Value');
        
        return $response;
    }
}
```

## Registering Custom Middleware

There are several ways to register your middleware with InspireCMS:

### 1. Global Middleware

To apply middleware to every HTTP request, register it in the `$middleware` property of the `app/Http/Kernel.php` file:

```php
protected $middleware = [
    // Other middleware...
    \App\Http\Middleware\YourMiddlewareName::class,
];
```

### 2. Route Middleware

To make your middleware available to specific routes, register it in the `$routeMiddleware` property of the `app/Http/Kernel.php` file:

```php
protected $routeMiddleware = [
    // Other middleware...
    'your-middleware' => \App\Http\Middleware\YourMiddlewareName::class,
];
```

You can then apply it to routes in a route file:

```php
Route::get('/your-route', function () {
    // Your route logic
})->middleware('your-middleware');
```

### 3. Frontend Middleware

To apply middleware specifically to frontend content routes in InspireCMS, modify the configuration:

```php
// config/inspirecms.php
'frontend' => [
    'routes' => [
        'middleware' => [
            // Add your middleware here
            'your-middleware',
        ],
    ],
],
```

## Common Use Cases for Middleware

### 1. Site Maintenance Mode

Create middleware that checks if the site is in maintenance mode:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;

class CheckMaintenanceMode
{
    public function handle($request, Closure $next)
    {
        if (Cache::get('site_maintenance_mode') && !$this->isAdmin($request)) {
            return response()->view('maintenance', [], 503);
        }

        return $next($request);
    }

    protected function isAdmin($request)
    {
        return $request->is('cms/*') && auth()->guard('inspirecms')->check();
    }
}
```

### 2. Custom Analytics Tracking

Middleware to add analytics tracking to all pages:

```php
<?php

namespace App\Http\Middleware;

use Closure;

class TrackPageViews
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        
        // Only process HTML responses
        if (!$request->ajax() && 
            $response->headers->get('Content-Type') === 'text/html; charset=UTF-8') {
            
            $content = $response->getContent();
            
            // Add analytics code before </body>
            $analyticsCode = "<script>// Your analytics code here</script>";
            $content = str_replace('</body>', $analyticsCode . '</body>', $content);
            
            $response->setContent($content);
        }
        
        return $response;
    }
}
```

### 3. Language Selection

Middleware to set the site language based on user preference:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LocaleMiddleware
{
    public function handle($request, Closure $next)
    {
        // Check for language in URL (e.g., /en/page)
        $segments = $request->segments();
        $locale = '';
        
        if (count($segments) > 0 && in_array($segments[0], ['en', 'fr', 'de', 'es'])) {
            $locale = $segments[0];
            Session::put('locale', $locale);
        }
        
        // If no locale in URL, check session or cookie
        if (empty($locale)) {
            $locale = Session::get('locale', config('app.locale'));
        }
        
        // Set the application locale
        App::setLocale($locale);
        
        return $next($request);
    }
}
```

### 4. Content Security Policy

Add security headers to protect against XSS and other attacks:

```php
<?php

namespace App\Http\Middleware;

use Closure;

class ContentSecurityPolicy
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        
        $response->headers->set('Content-Security-Policy', "default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data:;");
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        
        return $response;
    }
}
```

### 5. Cache Control

Middleware to manage cache headers:

```php
<?php

namespace App\Http\Middleware;

use Closure;

class CacheControl
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        
        // Check if the response is cacheable
        if (!$request->is('cms/*') && $request->isMethod('GET')) {
            $response->headers->set('Cache-Control', 'public, max-age=3600'); // 1 hour
        } else {
            // Don't cache admin pages or non-GET requests
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
        }
        
        return $response;
    }
}
```

## Middleware Groups

You can organize related middleware into groups for easier application to routes. Define middleware groups in `app/Http/Kernel.php`:

```php
protected $middlewareGroups = [
    'web' => [
        // Laravel's default web middleware...
    ],
    
    'api' => [
        // Laravel's default API middleware...
    ],
    
    'inspirecms.frontend' => [
        \App\Http\Middleware\TrackPageViews::class,
        \App\Http\Middleware\CacheControl::class,
        \App\Http\Middleware\LocaleMiddleware::class,
    ],
];
```

Then apply the group to routes:

```php
Route::middleware('inspirecms.frontend')->group(function () {
    // Routes here will have all the middleware in the group applied
});
```

## Middleware Priority

The order in which middleware is executed matters. You can set the priority in `app/Http/Kernel.php`:

```php
protected $middlewarePriority = [
    // High priority (executed first)
    \App\Http\Middleware\CheckMaintenanceMode::class,
    \Illuminate\Session\Middleware\StartSession::class,
    
    // Medium priority
    \App\Http\Middleware\LocaleMiddleware::class,
    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    \App\Http\Middleware\VerifyCsrfToken::class,
    
    // Low priority (executed last)
    \App\Http\Middleware\TrackPageViews::class,
    \App\Http\Middleware\CacheControl::class,
];
```

## Best Practices

1. **Keep middleware focused**: Each middleware should have a single responsibility
2. **Use middleware parameters** when configurability is needed
3. **Consider performance impact**, especially for middleware applied globally
4. **Document your middleware** to explain its purpose and behavior
5. **Test thoroughly**, especially edge cases
6. **Check for existing middleware** before creating a new one—Laravel and InspireCMS already include many useful middleware classes

By following these guidelines, you can effectively extend InspireCMS's request handling capability through custom middleware.