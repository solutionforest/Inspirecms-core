# Debugging InspireCMS

This guide provides effective techniques for troubleshooting and debugging issues in InspireCMS.

## Enabling Debug Mode

For development environments, enable debug mode to get detailed error messages:

```php
// .env
APP_DEBUG=true
APP_ENV=local
```

> **Warning**: Never enable debug mode in production environments as it exposes sensitive information.

## Debugging Techniques

### Laravel Debugging Tools

#### Dump and Die

Use Laravel's built-in helper functions for quick debugging:

```php
// Simple value dump and die
dd($variable);

// Dump multiple values without stopping execution
dump($variable1, $variable2);
// Continue execution...

// Dump when rendering a view
<div>{{ dump($variable) }}</div>
```

#### Debug Bar

Install Laravel Debugbar for enhanced debugging:

```bash
composer require barryvdh/laravel-debugbar --dev
```

This adds a debug panel to your site showing:
- Queries executed
- Request information
- View rendering time
- Session data
- Cache operations

#### Tinker

Use Laravel's Tinker to interact with your application from the command line:

```bash
php artisan tinker
```

Examples:
```php
// Check if content exists
\SolutionForest\InspireCms\Models\Content::count();

// Test content retrieval
inspirecms_content()->findByIds('550e8400-e29b-41d4-a716-446655440000');

// Check template configuration
inspirecms_templates()->getCurrentTheme();
```

### InspireCMS Specific Debugging

#### System Information

Get system details to help with debugging:

```bash
php artisan inspirecms:about
```

#### Test Templates

Test template rendering in isolation:

```php
// In a controller or route
$content = inspirecms_content()->findByIds('content-id')->first();
return view('components.inspirecms.your-theme.page', [
    'content' => $content,
])->render();
```

#### Component Testing

Check if components are registered correctly:

```php
// In Tinker or controller
$hasComponent = inspirecms_templates()->hasComponent('header', 'your-theme');
dd($hasComponent);
```

### Advanced Debugging Techniques

#### Logging to File

Add custom logging statements to trace execution:

```php
\Log::debug('Debugging content retrieval', [
    'content_id' => $content->id,
    'properties' => $content->getProperties(),
]);
```

#### Using Clock Helper

Add timing information to debug performance issues:

```php
use Illuminate\Support\Facades\Log;

$startTime = microtime(true);
// Operation to measure
$endTime = microtime(true);
Log::info('Operation took: ' . ($endTime - $startTime) . ' seconds');
```

#### Creating a Test Route

Create a temporary debug route in `routes/web.php`:

```php
Route::get('/_debug/test-template/{id}', function ($id) {
    $content = inspirecms_content()->findByIds($id)->first();
    if (!$content) {
        return 'Content not found';
    }
    
    return [
        'content' => [
            'id' => $content->id,
            'title' => $content->getTitle(),
            'document_type' => $content->document_type,
            'has_property' => $content->hasProperty('content', 'body'),
            'property_value' => $content->getPropertyValue('content', 'body'),
        ],
        'template' => inspirecms_templates()->getCurrentTheme(),
        'components' => [
            'has_page' => inspirecms_templates()->hasComponent('page'),
            'has_layout' => inspirecms_templates()->hasComponent('layout'),
        ],
    ];
})->middleware('auth');
```

#### Debug Front-End Components

Add temporary debug output to Blade templates:

```php
<!-- resources/views/components/inspirecms/your-theme/page.blade.php -->
<div style="background: #f8d7da; padding: 10px; margin: 10px; border: 1px solid #f5c6cb; display: none;">
    <strong>Debug:</strong>
    <pre>{{ print_r(['content' => $content ? $content->toArray() : null, 'locale' => $locale ?? null], true) }}</pre>
    <button onclick="this.parentNode.style.display = 'block'">Show Debug</button>
</div>
```

## Backend Debugging Tools

### Using Xdebug

For in-depth debugging, configure Xdebug with your IDE:

1. Install Xdebug with PHP
2. Configure your `php.ini`:

```ini
[xdebug]
zend_extension=xdebug.so
xdebug.mode=debug
xdebug.client_host=127.0.0.1
xdebug.client_port=9003
xdebug.start_with_request=yes
```

3. Configure your IDE (PHPStorm, VSCode) to listen for Xdebug connections
4. Set breakpoints in your code and run your application

### Query Debugging

Debug database queries:

```php
// Log all queries for a code block
\DB::enableQueryLog();

// Your code that executes queries
$content = inspirecms_content()->findByIds('id')->first();

// Get the query log
$queries = \DB::getQueryLog();
dd($queries);
```

## Frontend Debugging

### Browser Developer Tools

Use browser developer tools for frontend debugging:

1. **Chrome DevTools / Firefox Developer Tools**:
   - Network tab to check asset loading
   - Console for JavaScript errors
   - Elements tab to inspect HTML structure

2. **JavaScript Debugging**:
   - Add `console.log()` statements
   - Set breakpoints in browser DevTools

### Troubleshooting Templates

If templates aren't rendering correctly:

```php
// Test direct rendering of components
@php
    try {
        echo view('components.inspirecms.your-theme.header', ['locale' => app()->getLocale()])->render();
    } catch (\Throwable $e) {
        echo "Error: " . $e->getMessage();
    }
@endphp
```

## Debugging Custom Extensions

When debugging custom resources or pages:

```php
// Create a debug route for your custom resource
Route::get('/_debug/my-resource', function () {
    $class = \App\Filament\Resources\CustomResource::class;
    return [
        'exists' => class_exists($class),
        'registered' => in_array(
            $class, 
            collect(config('inspirecms.admin.resources'))->values()->toArray()
        ),
    ];
});
```

## Performance Debugging

### Memory Usage

Track memory usage:

```php
$initialMemory = memory_get_usage();
// Code to profile
$peakMemory = memory_get_peak_usage();
$usedMemory = memory_get_usage() - $initialMemory;

Log::info('Memory usage', [
    'used' => round($usedMemory / 1024 / 1024, 2) . ' MB',
    'peak' => round($peakMemory / 1024 / 1024, 2) . ' MB',
]);
```

### Request Profiling

For advanced profiling, use Laravel Telescope:

```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

Access Telescope at `/telescope` to view detailed metrics on:
- Requests and responses
- Database queries
- Cache operations
- Model operations

## Troubleshooting Environment Issues

### Testing PHP Configuration

Create a diagnostic file to check your environment:

```php
// public/phpinfo.php
<?php
phpinfo();
```
> Remember to remove this file after debugging!

### Testing File Permissions

```bash
# See if PHP can write to storage
php -r "file_put_contents(storage_path('test.txt'), 'test');"
php -r "echo file_exists(storage_path('test.txt')) ? 'Success' : 'Failed';"
php -r "@unlink(storage_path('test.txt'));"
```

## Next Steps

If you're still experiencing issues after using these debugging techniques:

1. Check specific error messages against the [Common Issues](./CommonIssues.md) guide
2. Review the [Logs & Error Handling](./LogsErrorHandling.md) documentation
3. Reach out to the community using our [Support Resources](./SupportResources.md)