# Drafts & Revisions

InspireCMS provides a powerful content versioning system that allows you to work with drafts and track revisions of your content. This guide explains how to use these features to manage your content workflow effectively.

## Content States Overview

In InspireCMS, content can exist in various states:

1. **Draft**: Content that is being worked on but not yet published
2. **Published**: Content that is live and visible to site visitors
3. **Unpublished**: Previously published content that has been taken offline
4. **Scheduled**: Content set to be published automatically at a future date

## Working with Drafts

### Creating a Draft

When you create new content in InspireCMS, it starts as a draft by default:

1. Navigate to **Content → [Content Type]** in the admin panel
2. Click **Create Content**
3. Add your content details, fields, and settings
4. Click **Save** (not "Publish") to store as a draft

Drafts are visible only in the admin panel and not on your live site.

### Identifying Drafts

Drafts are clearly marked in the content list:

- Status indicator shows "Draft"
- Often color-coded differently from published content
- May show an editing icon

### Editing Drafts

You can freely modify drafts without affecting your live content:

1. Find the draft in your content list
2. Click to open it in the editor
3. Make your changes
4. Click **Save** to update the draft

### Draft Preview

Preview your draft to see how it will look when published:

1. Open the draft in the editor
2. Click the **Preview** button in the editor toolbar
3. Your draft will open in a new tab showing how it will appear on the site

This preview is visible only to authenticated admin users.

## Publishing Content

When your draft is ready to go live:

1. Open the draft in the editor
2. Review all content and settings
3. Click the **Publish** button
4. Confirm the publish action

Once published, the content becomes visible on your live site.

### Scheduling Publication

For content that should go live at a specific time:

1. Edit your content as usual
2. In the publishing options, select **Schedule**
3. Set the desired publish date and time
4. Click **Schedule**

The system will automatically change the content status from "Scheduled" to "Published" at the specified time.

## Content Revisions

InspireCMS automatically tracks revisions each time content is saved, creating a history of changes.

### Viewing Revision History

To see the history of changes to a content item:

1. Open the content item in the editor
2. Look for the **Revisions** or **History** tab/button
3. View the list of all revisions with timestamps and authors

### Comparing Revisions

To see what changed between versions:

1. From the revisions list, select two revisions to compare
2. Click **Compare**
3. The system will display a side-by-side or inline diff showing:
   - Added content (typically highlighted in green)
   - Removed content (typically highlighted in red)
   - Changed formatting or metadata

### Restoring Previous Revisions

To revert to a previous version:

1. From the revisions list, find the version you want to restore
2. Click **Restore this version**
3. Confirm the restore action

The restored version becomes the current draft. You must publish it to make it live.

## Content Locks

To prevent conflicts when multiple users edit the same content:

1. When a user begins editing content, a lock is placed on that content
2. Other users see an indicator that the content is being edited
3. The lock expires after a period of inactivity (typically 15 minutes)
4. Administrators can override locks if necessary

### Force Edit

If you need to edit locked content:

1. Attempt to open the locked content
2. You'll see a notification showing who has it locked
3. Click **Force Edit** (admin users only)
4. Confirm your action

> **Note**: Forcing an edit may cause the other user's changes to be lost if they try to save after you.

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
                    $record->status = 2;
                    $record->save();
                    $action->success();
                })
        )
    );
}
```

## Content Approval Workflows

For organizations that require approval before publishing:

1. Content author creates and edits a draft
2. Author submits the content for review
3. Editors/approvers are notified of pending review
4. Approvers can:
   - Approve and publish
   - Request changes (returns to draft)
   - Reject the content

### Implementing a Review Workflow

A basic approval workflow can be set up using custom states and notifications:

```php
use SolutionForest\InspireCms\Facades\ContentStatusManifest;
use SolutionForest\InspireCms\DataTypes\Manifest\ContentStatusOption;
use Filament\Notifications\Notification;
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
                ->label('Submit for Review')
                ->action(function ($record, $action) {
                    $record->status = 2;
                    $record->save();
                    
                    // Notify reviewers
                    $editors = \SolutionForest\InspireCms\Models\User::role('editor')->get();
                    foreach ($editors as $editor) {
                        Notification::make()
                            ->title('Content Ready for Review')
                            ->body("'{$record->title}' needs your review.")
                            ->actions([
                                Action::make('review')
                                    ->button()
                                    ->url(route('filament.admin.resources.contents.edit', $record))
                            ])
                            ->sendToDatabase($editor);
                    }
                    
                    $action->success();
                })
        )
    );
}
```

## Version Control Integration

For advanced versioning needs, InspireCMS can be integrated with external version control:

1. Install the Git integration package
2. Configure the repository connection
3. Enable content versioning in settings

This allows for more advanced version management, including branching and merging content changes.

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