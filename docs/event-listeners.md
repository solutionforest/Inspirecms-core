---
title: Event Listeners
slug: event-listeners
path: docs/v1/event-listeners
uri: /docs/1.x/event-listeners
---
# Event Listeners

Event listeners provide a powerful way to extend InspireCMS functionality without modifying core code. They allow you to execute custom code when specific events occur in the system, such as content creation, user registration, or media uploads.

---

## Understanding the Event System

InspireCMS uses Laravel's event broadcasting system, which follows the observer pattern:

1. **Events**: Classes that represent something that happened in the system
2. **Listeners**: Classes that respond to events with custom logic
3. **Subscribers**: Classes that group related event listeners together

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

### 1. Send Notification When Theme Changes

```php
<?php

namespace App\Listeners;

use SolutionForest\InspireCms\Events\Template\ChangeTheme;
use App\Notifications\ThemeChangedNotification;
use Illuminate\Support\Facades\Notification;
use App\Models\User;

class NotifyThemeChange
{
    public function handle(ChangeTheme $event)
    {
        // Find admin users to notify
        $admins = User::role('administrator')->get();
        
        // Send notification to all admin users
        Notification::send($admins, new ThemeChangedNotification(
            $event->oldTheme,
            $event->newTheme
        ));
        
        // Log the theme change
        \Log::info('Theme changed', [
            'from' => $event->oldTheme,
            'to' => $event->newTheme,
            'user' => auth()->user() ? auth()->user()->name : 'System',
        ]);
    }
}
```

### 2. Regenerate Sitemap After Content Version Published

```php
<?php

namespace App\Listeners;

use SolutionForest\InspireCms\Events\Content\CreatedPublishContentVersion;
use Illuminate\Contracts\Queue\ShouldQueue;
use SolutionForest\InspireCms\Events\Content\GenerateSitemap;
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

### 3. Track Content Status Changes

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

### 4. Generate Sitemap After Content Changes

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

### 5. Track User Activity

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

---

## Testing Event Listeners

Testing event listeners is crucial to ensure they respond correctly to events:

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use App\Listeners\YourListener;
use SolutionForest\InspireCms\Events\Content\CreatedContentVersion;
use SolutionForest\InspireCms\Models\Content;

class YourListenerTest extends TestCase
{
    public function testListenerDispatchedWhenContentVersionCreated()
    {
        Event::fake();
        
        // Create a content version (implementation depends on your specific models)
        // This is a simplified example
        $content = Content::factory()->create();
        $version = $content->createVersion(['data' => ['title' => 'Test']]);
        
        // Assert that the event was dispatched
        Event::assertDispatched(CreatedContentVersion::class);
        
        // Assert that the listener was dispatched for the event
        Event::assertListening(
            CreatedContentVersion::class,
            YourListener::class
        );
    }
    
    public function testListenerProcessesCorrectly()
    {
        Queue::fake();
        
        $listener = new YourListener();
        $content = Content::factory()->create();
        $version = $content->createVersion(['data' => ['title' => 'Test']]);
        $event = new CreatedContentVersion($content, $version, null, false);
        
        // Call the listener directly
        $listener->handle($event);
        
        // Assert the expected outcome using your application's logic
        // This will depend on what your listener should do
    }
}
```

## Performance Considerations

When creating event listeners, consider these performance best practices:

1. **Use Queued Listeners**: Implement `ShouldQueue` for non-critical listeners to process events asynchronously
2. **Set Queue Priority**: For time-sensitive listeners, set a higher priority:

```php
class YourListener implements ShouldQueue
{
    public $queue = 'high';
}
```

3. **Avoid Circular Events**: Be careful not to create circular event chains that trigger each other
4. **Use Database Transactions**: For listeners that update multiple records, use database transactions
5. **Batch Related Operations**: If an event triggers multiple similar operations, consider batching them

---

## Event-Driven Architecture Tips

To build a robust event-driven architecture in your InspireCMS project:

1. **Keep Events Focused**: Each event should represent a single specific occurrence
2. **Use Past Tense for Event Names**: Events represent something that has already happened (`UserRegistered` not `RegisterUser`)
3. **Listeners Should Be Single-Purpose**: Each listener should do just one thing in response to an event
4. **Consider Event Sourcing**: For complex applications, track events as the source of truth for application state
5. **Document Events and Listeners**: Keep documentation of all events and their listeners for easier maintenance

By following these guidelines, you can effectively extend InspireCMS functionality using the event system while maintaining code organization and performance.