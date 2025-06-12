---
title: Installing
slug: installing
path: docs/v1/installing
uri: /docs/1.x/installing
heading: Middleware
brief:
---

## Overview

InspireCMS uses Laravel's middleware architecture, which allows code to be executed before and after an HTTP request is processed. There are several types of middleware in InspireCMS:

1. **Global Middleware**: Applied to every HTTP request
2. **Route Middleware**: Applied to specific routes or route groups
3. **Frontend Middleware**: Applied to frontend content routes
4. **Admin Panel Middleware**: Applied specifically to routes within the InspireCMS admin panel

---

## Creating Custom Middleware

The process of creating custom middleware in InspireCMS follows Laravel's standard approach. For detailed information on creating and implementing middleware, please refer to the [Laravel official documentation on middleware](https://laravel.com/docs/middleware#defining-middleware).

---

## Registering Custom Middleware

There are several ways to register your middleware with InspireCMS:

### 1. Global Middleware & 2. Route Middleware

InspireCMS follows Laravel's standard middleware registration process for global and route middleware. For detailed information on registering these types of middleware, please refer to the [Laravel official documentation on middleware](https://laravel.com/docs/middleware).

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

### 4. Admin Panel Middleware

To apply middleware specifically to the admin panel in InspireCMS, you need to create a custom panel provider. This allows you to define middleware that will only run for admin panel routes.

First, make sure you've set up a custom panel provider as described in [Extending the Admin Panel](./admin-panel#creating-a-custom-panel-provider){.doc-link}.

Once you have your custom panel provider set up, you can add middleware to the admin panel:

```php
<?php

namespace App\Providers;

use Filament\Panel;
use SolutionForest\InspireCms\CmsPanelProvider;

class MyCmsPanelProvider extends CmsPanelProvider
{
    protected function configureCmsPanel(Panel $panel): Panel
    {
        $panel = parent::configureCmsPanel($panel);

        return $panel
            // Add middleware that runs on all admin panel routes
            ->middleware([
                \App\Http\Middleware\AdminLoggingMiddleware::class,
                \App\Http\Middleware\AdminAnalyticsMiddleware::class,
            ])
            // Add middleware that runs only on authenticated admin panel routes
            ->authMiddleware([
                \App\Http\Middleware\AdminPermissionsMiddleware::class,
            ]);
    }
}
```

For example, you might create a middleware to log all admin actions:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminLoggingMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Log the admin action
        Log::channel('admin')->info('Admin action', [
            'user' => auth()->user()?->name ?? 'Guest',
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip' => $request->ip(),
        ]);

        return $next($request);
    }
}
```

Or middleware to implement additional admin permissions:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Filament\Notifications\Notification;

class AdminPermissionsMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Get the authenticated user
        $user = auth()->user();

        // Example: Check if the user has a specific permission for certain routes
        if ($request->is('admin/settings*') && !$user->can('manage_settings')) {
            // Show notification and redirect
            Notification::make()
                ->title('Access denied')
                ->body('You do not have permission to manage settings.')
                ->danger()
                ->send();

            return redirect()->route('filament.admin.pages.dashboard');
        }

        return $next($request);
    }
}
```

---

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
