---
title: Event Listeners
slug: event-listeners
path: docs/v1/event-listeners
uri: /docs/1.x/event-listeners
heading: Event Listeners
brief:
---

## Available Events in InspireCMS

InspireCMS fires events for various system activities. Here are the key events organized by category:

### Content Events

```php
// Content version events
SolutionForest\InspireCms\Events\Content\CreatingContentVersion::class  // Before a content version is created
SolutionForest\InspireCms\Events\Content\CreatedContentVersion::class   // After a content version is created
SolutionForest\InspireCms\Events\Content\CreatingPublishContentVersion::class // Before publishing a content version
SolutionForest\InspireCms\Events\Content\CreatedPublishContentVersion::class  // After publishing a content version
SolutionForest\InspireCms\Events\Content\DispatchContentVersion::class  // When a content version is dispatched

// Content status events
SolutionForest\InspireCms\Events\Content\ChangeStatus::class            // When content status is changed

// Sitemap events
SolutionForest\InspireCms\Events\Content\GenerateSitemap::class         // When sitemap generation is requested
SolutionForest\InspireCms\Events\Content\SitemapGenerated::class        // After sitemap has been generated
```

### Template Events

```php
// Template events
SolutionForest\InspireCms\Events\Template\UpdateContent::class          // When template content is updated
SolutionForest\InspireCms\Events\Template\CreateTheme::class            // When a new theme is created
SolutionForest\InspireCms\Events\Template\ChangeTheme::class            // When the active theme is changed
```

### Licensing Events

```php
// Licensing events
SolutionForest\InspireCms\Events\Licensing\LicensesRefreshed::class     // When licenses are refreshed
```

---

## Creating Event Listeners

### Step 1: Generate a Listener Class

You can create an event listener using Laravel's artisan command:

```bash
php artisan make:listener YourListener --event=SolutionForest\\InspireCms\\Events\\Content\\CreatedContentVersion
```

This creates a new listener in the `app/Listeners` directory.

### Step 2: Implement the Listener Logic

Edit the generated file to implement your listener logic:

```php
<?php

namespace App\Listeners;

use SolutionForest\InspireCms\Events\Content\CreatedContentVersion;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class YourListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     *
     * @param  \SolutionForest\InspireCms\Events\Content\CreatedContentVersion  $event
     * @return void
     */
    public function handle(CreatedContentVersion $event)
    {
        $content = $event->content;
        $version = $event->version;
        $status = $event->status;

        Log::info('New content version created:', [
            'content_id' => $content->id,
            'version_id' => $version->id,
            'status' => $status ? $status->name : 'none',
            'is_publishing' => $event->isPublishing,
        ]);

        // Your custom logic here
        // E.g., send notifications, update external systems, etc.
    }
}
```

Adding the `ShouldQueue` interface makes your listener run asynchronously for better performance. The `InteractsWithQueue` trait provides methods like `release()` and `delete()` for queue management.

### Step 3: Register the Listener

Register your listener in the `EventServiceProvider` class:

```php
<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use SolutionForest\InspireCms\Events\Content\CreatedContentVersion;
use App\Listeners\YourListener;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        CreatedContentVersion::class => [
            YourListener::class,
        ],
    ];
}
```

---

## Practical Examples of Event Listeners

### 1. Track Content Status Changes

```php
<?php

namespace App\Listeners;

use SolutionForest\InspireCms\Events\Content\ChangeStatus;
use App\Models\ContentStatusHistory;

class LogContentStatusChange
{
    public function handle(ChangeStatus $event)
    {
        $content = $event->content;
        $oldStatus = $event->oldStatus ? $event->oldStatus->name : null;
        $newStatus = $event->status ? $event->status->name : null;

        // Record status change in history table
        ContentStatusHistory::create([
            'content_id' => $content->id,
            'content_type' => get_class($content),
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'changed_by' => auth()->id(),
            'changed_at' => now(),
        ]);
    }
}
```

### 2. Generate Sitemap After Content Changes

```php
<?php

namespace App\Listeners;

use SolutionForest\InspireCms\Events\Content\CreatedPublishContentVersion;
use SolutionForest\InspireCms\Events\Content\GenerateSitemap;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Event;

class RegenerateSitemap implements ShouldQueue
{
    public function handle(CreatedPublishContentVersion $event)
    {
        $content = $event->content;

        // Only trigger sitemap generation for certain content types
        $relevantTypes = ['page', 'post', 'product'];

        if (in_array($content->document_type, $relevantTypes)) {
            // Dispatch the generate sitemap event
            Event::dispatch(new GenerateSitemap(
                get_class($content),
                $content->getKey(),
                'content_published'
            ));
        }
    }
}
```

### 3. Track User Activity

```php
<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Models\UserActivity;
use Jenssegers\Agent\Agent;

class TrackUserLogin
{
    public function handle(Login $event)
    {
        $user = $event->user;
        $request = request();

        $agent = new Agent();
        $agent->setUserAgent($request->userAgent());

        UserActivity::create([
            'user_id' => $user->id,
            'activity_type' => 'login',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'browser' => $agent->browser(),
            'browser_version' => $agent->version($agent->browser()),
            'device' => $agent->device(),
            'platform' => $agent->platform(),
            'platform_version' => $agent->version($agent->platform()),
        ]);

        // Update user's last login time
        $user->last_login_at = now();
        $user->last_login_ip = $request->ip();
        $user->save();
    }
}
```
