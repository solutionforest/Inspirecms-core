# Logs & Error Handling

This guide explains how to effectively use logs and handle errors in InspireCMS to diagnose and resolve issues.

## Log Files

InspireCMS uses Laravel's logging system with some additional specialized logs.

### Main Log Files

- **Laravel Log**: `storage/logs/laravel-*.log` - General application logs
- **Security Log**: `storage/logs/security-*.log` - Authentication and security-related events
- **Admin Log**: `storage/logs/admin-*.log` - Admin panel activities

### Accessing Log Files

#### Command Line

```bash
# View the latest logs
tail -f storage/logs/laravel-*.log

# Search logs for specific errors
grep -i "error" storage/logs/laravel-*.log

# Count occurrences of errors
grep -i "error" storage/logs/laravel-*.log | wc -l
```

#### Log Viewer

For a more user-friendly experience, consider installing a log viewer:

```bash
composer require opcodesio/log-viewer
```

Then access your logs at `/log-viewer` (you should secure this route in production).

## Log Configuration

### Customizing Logging

You can customize InspireCMS logging in `config/logging.php`:

```php
'channels' => [
    // Default Laravel logs
    'stack' => [
        'driver' => 'stack',
        'channels' => ['single', 'slack'],
        'ignore_exceptions' => false,
    ],
    
    // Custom InspireCMS security logs
    'security' => [
        'driver' => 'daily',
        'path' => storage_path('logs/security.log'),
        'level' => 'notice',
        'days' => 14,
    ],
    
    // Custom admin activity logs
    'admin' => [
        'driver' => 'daily',
        'path' => storage_path('logs/admin.log'),
        'level' => 'info',
        'days' => 30,
    ],
],
```

### Log Levels

InspireCMS uses standard PSR-3 log levels:

- **emergency**: System is unusable
- **alert**: Action must be taken immediately
- **critical**: Critical conditions
- **error**: Error conditions
- **warning**: Warning conditions
- **notice**: Normal but significant events
- **info**: Informational messages
- **debug**: Debug-level information

## Writing to Logs

### Standard Logging

```php
use Illuminate\Support\Facades\Log;

// Basic logging
Log::info('Content was created', ['content_id' => $content->id]);

// Error logging
try {
    // Some operation
} catch (\Exception $e) {
    Log::error('Failed to create content', [
        'exception' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString(),
    ]);
}
```

### Channel-Specific Logging

```php
// Log to security channel
Log::channel('security')->warning('Failed login attempt', [
    'ip' => request()->ip(),
    'email' => $request->email,
]);

// Log to admin channel
Log::channel('admin')->info('Content published', [
    'user' => auth()->user()->email,
    'content_id' => $content->id,
]);
```

### Context-Enriched Logging

You can create a custom logger that automatically adds context:

```php
// app/Support/Logger.php
namespace App\Support;

use Illuminate\Support\Facades\Log;

class Logger
{
    public static function adminAction($message, $data = [])
    {
        $user = auth()->user();
        
        Log::channel('admin')->info($message, array_merge([
            'user_id' => $user ? $user->id : null,
            'user_email' => $user ? $user->email : null,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ], $data));
    }
}

// Usage
\App\Support\Logger::adminAction('Updated template settings', [
    'template' => $template->name,
]);
```

## Error Handling

### Default Error Handling

InspireCMS uses Laravel's exception handler located at `app/Exceptions/Handler.php`. You can extend this to customize error handling.

### Custom Exception Handling

To customize how specific exceptions are handled:

```php
// app/Exceptions/Handler.php
namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use SolutionForest\InspireCms\Exceptions\ContentNotFoundException;
use SolutionForest\InspireCms\Exceptions\TemplateNotFoundException;
use Throwable;

class Handler extends ExceptionHandler
{
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            // Add custom reporting logic
        });
        
        $this->renderable(function (ContentNotFoundException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Content not found'], 404);
            }
            
            return response()->view('errors.content-not-found', [], 404);
        });
        
        $this->renderable(function (TemplateNotFoundException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Template not found'], 404);
            }
            
            return response()->view('errors.template-not-found', [
                'template' => $e->getTemplateName(),
            ], 404);
        });
    }
}
```

### Creating Custom Error Pages

Create custom error pages for common HTTP errors:

```bash
# Create error views directory if it doesn't exist
mkdir -p resources/views/errors
```

Example 404 error page:

```php
// resources/views/errors/404.blade.php
@extends('layouts.error')

@section('title', 'Page Not Found')

@section('content')
    <div class="error-page">
        <h1>404</h1>
        <h2>Page Not Found</h2>
        <p>We couldn't find the page you're looking for.</p>
        <a href="{{ url('/') }}" class="btn btn-primary">Return Home</a>
    </div>
@endsection
```

## Common InspireCMS Errors

### Content Not Found Errors

When content can't be found, InspireCMS throws `ContentNotFoundException`.

```php
try {
    $content = inspirecms_content()->findByIds($id)->firstOrFail();
} catch (\SolutionForest\InspireCms\Exceptions\ContentNotFoundException $e) {
    Log::warning('Content not found', ['id' => $id, 'url' => request()->url()]);
    abort(404);
}
```

## Error Monitoring

### Setting Up External Error Monitoring

For production environments, consider using an external error monitoring service.

#### Sentry Integration

```bash
composer require sentry/sentry-laravel
```

Configure in `.env`:

```
SENTRY_LARAVEL_DSN=your-sentry-dsn
```

Initialize in `app/Exceptions/Handler.php`:

```php
public function register(): void
{
    $this->reportable(function (Throwable $e) {
        if (app()->bound('sentry')) {
            app('sentry')->captureException($e);
        }
    });
}
```

### Custom Error Reports

Create a command to generate error reports:

```php
// app/Console/Commands/GenerateErrorReport.php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class GenerateErrorReport extends Command
{
    protected $signature = 'inspirecms:error-report {--days=7}';
    protected $description = 'Generate a report of errors from logs';

    public function handle()
    {
        $days = $this->option('days');
        $this->info("Generating error report for the last {$days} days");
        
        $logFiles = File::glob(storage_path('logs/laravel-*.log'));
        // Implementation to parse logs and generate report
        // ...
        
        $this->info("Report generated: " . storage_path('logs/reports/error-report.html'));
    }
}
```

## Log Rotation and Management

### Configuring Log Rotation

InspireCMS uses Laravel's daily log channel by default which handles log rotation.

Custom configuration:

```php
// config/logging.php
'daily' => [
    'driver' => 'daily',
    'path' => storage_path('logs/laravel.log'),
    'level' => env('LOG_LEVEL', 'debug'),
    'days' => 14, // Keep logs for 14 days
],
```

### Log Cleanup

You can create a scheduled command to clean up old logs:

```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    // Clean logs older than 30 days
    $schedule->command('inspirecms:clean-logs --days=30')->weekly();
}
```

```php
// app/Console/Commands/CleanLogs.php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class CleanLogs extends Command
{
    protected $signature = 'inspirecms:clean-logs {--days=30}';
    protected $description = 'Clean up old log files';

    public function handle()
    {
        $days = $this->option('days');
        $this->info("Cleaning logs older than {$days} days");
        
        $cutoffDate = Carbon::now()->subDays($days);
        
        collect(File::glob(storage_path('logs/*.log')))
            ->filter(function ($file) use ($cutoffDate) {
                return Carbon::createFromTimestamp(File::lastModified($file))->lt($cutoffDate);
            })
            ->each(function ($file) {
                $this->info("Removing: " . basename($file));
                File::delete($file);
            });
            
        $this->info('Log cleanup complete');
    }
}
```

## Best Practices

1. **Log Responsibly**: Don't log sensitive information like passwords or tokens
2. **Use Appropriate Log Levels**: Choose the right log level based on the severity
3. **Include Context**: Add relevant context to log messages for easier debugging
4. **Monitor Disk Space**: Ensure logs don't fill up your disk space
5. **Set Up Alerts**: Configure alerts for critical errors
6. **Custom Exception Classes**: Create custom exception classes for specific errors
7. **Graceful Degradation**: When possible, recover from errors rather than showing error pages

## Next Steps

If you encounter errors that you can't resolve:

1. Check the [Common Issues](./CommonIssues.md) guide
2. Try the diagnostic steps in the [Debugging](./Debugging.md) guide
3. Contact our support through the [Support Resources](./SupportResources.md)