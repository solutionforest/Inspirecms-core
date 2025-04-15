# Drafts & Revisions

InspireCMS provides a powerful content versioning system that allows you to work with drafts and track revisions of your content. This guide explains how to use these features to manage your content workflow effectively.

## Content States Overview

In InspireCMS, content can exist in various states:

1. **Draft**: Content that is being worked on but not yet published
2. **Published**: Content that is live and visible to site visitors
3. **Unpublished**: Previously published content that has been taken offline
4. **Scheduled**: Content set to be published automatically at a future date

## Working with Drafts

### Creating a Draft { .font-bold  .text-2xl .my-2 }

When you create new content in InspireCMS, it starts as a draft by default:

1. Navigate to **Content** in the admin panel
2. Click **Create Content**
3. Add your content details, fields, and settings
4. Click **Save** (not "Publish") to store as a draft

Drafts are visible only in the admin panel and not on your live site.

### Identifying Drafts { .font-bold  .text-2xl .my-2 }

Drafts are clearly marked in the content list:

- Status indicator shows "Draft"
- Often color-coded differently from published content
- Show an editing icon

### Editing Drafts { .font-bold  .text-2xl .my-2 }

You can freely modify drafts without affecting your live content:

1. Find the draft in your content list
2. Click to open it in the editor
3. Make your changes
4. Click **Save** to update the draft

### Draft Preview { .font-bold  .text-2xl .my-2 }

Preview your draft to see how it will look when published:

1. Open the draft in the editor
2. Click the **Preview** button in the editor toolbar
3. Your draft will appear in a modal window showing how it will appear on the site

This preview is visible only to authenticated admin users.

## Publishing Content

When your draft is ready to go live:

1. Open the draft in the editor
2. Review all content and settings
3. Click the **Publish** button
4. Confirm the publish action

Once published, the content becomes visible on your live site.

### Scheduling Publication { .font-bold  .text-2xl .my-2 }

For content that should go live at a specific time:

1. Edit your content as usual
2. In the publishing options, select **Schedule**
3. Set the desired publish date and time
4. Click **Schedule**

The system will automatically change the content status from "Scheduled" to "Published" at the specified time.

## Content Revisions

InspireCMS automatically tracks revisions each time content is saved, creating a history of changes.

### Viewing Revision History { .font-bold  .text-2xl .my-2 }

To see the history of changes to a content item:

1. Open the content item in the editor
2. Look for the **Content History** tab/button
3. View the list of all revisions with timestamps and authors

## Content Locks

To prevent conflicts when multiple users edit the same content:

1. When a user begins editing content, a lock is placed on that content
2. Other users see an indicator that the content is being edited
3. Locks remain active until explicitly released
4. Only administrators and the user who placed the lock can unlock the content

## Custom Publishing States

InspireCMS allows for custom publishing states to match your workflow:

```php
use SolutionForest\InspireCms\Facades\ContentStatusManifest;
use SolutionForest\InspireCms\DataTypes\Manifest\ContentStatusOption;
use Filament\Actions\Action;

// In your service provider
public function boot()
{
    ContentStatusManifest::addOption(
        new ContentStatusOption(
            value: 2,
            name: 'review',
            formAction: fn () => Action::make('review')
                ->label('Send for Review')
                ->action(function ($record, $action) {
                    if (is_null($record)) {
                        $action->cancel();
                        return;
                    }
                    if (! \SolutionForest\InspireCms\Helpers\ContentHelper::handlePublishableRecord($record, $publishableState, $livewire, [])) {
                        return;
                    }
                    $action->success();
                })
        )
    );
}
```

## Example Usage: Content Review and Approval System

For organizations that require approval before publishing:

1. Content author creates and edits a draft
2. Author submits the content for review
3. Editors/approvers are notified of pending review
4. Approvers can:
    - Approve and publish
    - Request changes (returns to draft)
    - Reject the content

### Adding a Custom Content Status { .font-bold  .text-2xl .my-2 }

A basic approval workflow can be set up using custom states and notifications:

```php
use SolutionForest\InspireCms\Facades\ContentStatusManifest;
use SolutionForest\InspireCms\DataTypes\Manifest\ContentStatusOption;
use Filament\Actions\Action;

// In your service provider
public function boot()
{
     // Add "In Review" status
     ContentStatusManifest::addOption(
          new ContentStatusOption(
                value: 2,
                name: 'in_review',
                formAction: fn () => Action::make('submit_for_review')
                    ->authorize('inReview')
                    ->successNotificationTitle('Send to Review')
                    ->action(function ($record, $action) {
                        $if (is_null($record)) {
                        $action->cancel();

                        return;
                    }
            
                    $publishableState = 'in_review';

                    if (! \SolutionForest\InspireCms\Helpers\ContentHelper::handlePublishableRecord($record, $publishableState, $livewire, [])) {
                        return;
                    }

                    $action->success();
                    })
          )
     );
}
```

### Customizing Models and Authorization Policies { .font-bold  .text-2xl .my-2 }

To fully implement a review workflow, you may need to extend the default content model and define authorization policies:

#### Custom Content Model { .font-bold .my-2 }

```php
namespace App\Models;

use SolutionForest\InspireCms\Models\Content as BaseContent;

class Content extends BaseContent
{
}
```

#### Custom Content Policy { .font-bold .my-2 }

```php
namespace App\Policies;

use App\Models\Content;
use App\Models\User;
use SolutionForest\InspireCms\Policies\ContentStatusPolicy as BasePolicy;

class ContentPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }
    
    public function view(User $user, Content $content): bool
    {
        return true;
    }
    
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['author', 'editor', 'admin']);
    }
    
    public function update(User $user, Content $content): bool
    {
        // Authors can only edit drafts they created
        if ($user->hasRole('author') && $content->user_id === $user->id) {
            return $content?->display_status?->getName() === 'draft';
        }
        
        // Editors can review content in review status and edit any draft
        if ($user->hasRole('editor')) {
            return in_array($content?->display_status?->getName(), [
                'draft',
                'in_review',
            ]);
        }
        
        // Admins can edit anything
        return $user->hasRole('admin');
    }
    
    public function publish(User $user, Content $content): bool
    {
        return $user->hasAnyRole(['editor', 'admin']);
    }

    public function inReview(User $user, Content $content): bool
    {
        return $content?->display_status?->getName() !== 'in_review';
    }
}
```

Register your custom model and policy in your `AppServiceProvider`:

```php
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use SolutionForest\InspireCms\Facades\InspireCms;
use SolutionForest\InspireCms\Facades\ModelManifest;
use SolutionForest\InspireCms\Models\Contracts\Content;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        ModelManifest::replace(Content::class, \App\Models\Content::class);
    }

    public function boot()
    {
        Gate::policy(\App\Models\Content::class, \App\Policies\ContentPolicy::class);
    }
}
```

Or update in config:

```php
// config/inspirecms.php
return [
    'models' => [
        'fqcn' => [
            'content' => \App\Models\Content::class,
        ],
        'policies' => [
            'content' => \App\Policies\YourContentPolicy::class,
        ]
    ],
];
```

## Conflict Resolution

When conflicting edits occur:

1. The system detects when two users have edited the same content
2. On save, the second user is shown a conflict resolution screen
3. They can choose to:
   - Merge changes manually
   - Keep their version (overwrite)
   - Discard their changes
   - Save as a new draft

## Best Practices

- **Regular Saves**: Save your work frequently to create revision points
- **Meaningful Comments**: Add descriptive comments when making significant changes
- **Test Before Publishing**: Always preview your content before publishing
- **Schedule Major Updates**: Use scheduling for significant changes to go live during off-peak hours
- **Limit Draft Duration**: Try not to keep drafts unpublished for extended periods to avoid outdated content
- **Regular Cleanup**: Periodically review and remove unnecessary drafts and revisions