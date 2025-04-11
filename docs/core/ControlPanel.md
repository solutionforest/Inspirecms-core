# Control Panel

The InspireCMS Control Panel provides a powerful and intuitive interface for managing your content and site settings. This guide walks you through the key features and customization options of the admin interface.

## Accessing the Control Panel

By default, the control panel is available at `/cms` on your domain. You can access it by navigating to:

```
https://your-domain.com/cms
```

The path can be customized in your configuration if needed:

```php
// config/inspirecms.php
'filament' => [
    'path' => 'admin', // Changes the path from /cms to /admin
    // other settings...
],
```

## Dashboard Overview

The InspireCMS dashboard provides insights and quick access to important areas of your site:

- **Content Statistics**: Shows number of pages, drafts, and published content
- **Recent Activity**: Displays recent content updates and user actions
- **Quick Links**: Shortcuts to commonly used sections
- **System Health**: Indicates the status of your CMS installation

## Navigation Structure

The control panel is organized into several main sections:

1. **Content**: Manage pages, blog posts, and other content types
2. **Media**: Upload and organize images, documents, and other media
3. **Settings**: Configure system settings, templates, and site options
4. **Users**: Manage user accounts and permissions

## Content Management

### Content List View

The Content section lists all your content items with filtering and search capabilities:

- Filter by status (draft, published, archived)
- Sort by various attributes (title, date, author)
- Search content by keyword
- Bulk operations for multiple items

### Content Editor

The content editor includes:

- **WYSIWYG Editor**: Rich text editing with formatting tools
- **Media Integration**: Insert and manage media within content
- **Custom Fields**: Input fields based on your defined content types
- **SEO Settings**: Meta title, description, and other optimization options
- **Versioning**: Manage content revisions and history
- **Publishing Controls**: Schedule content publication and set status

## Media Library

The Media Library provides tools for managing digital assets:

- **File Browser**: View and organize media in folders
- **Upload Interface**: Drag-and-drop uploading of multiple files
- **Image Editor**: Basic editing capabilities for images
- **Metadata Management**: View and edit file metadata
- **Usage Tracking**: See where media is being used across the site

## System Settings

The Settings section includes:

- **Site Configuration**: Basic site settings and metadata
- **Template Management**: Create and edit templates
- **Navigation**: Manage site menus and navigation structure
- **Languages**: Configure multilingual support
- **Field Groups**: Define custom fields
- **Document Types**: Configure content type structures
- **Import/Export**: Tools for data migration

## Customizing the Control Panel

### Branding

You can customize the appearance of the control panel in your service provider:

```php
use Filament\Panel;
use SolutionForest\InspireCms\CmsPanelProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        CmsPanelProvider::configureUsing(function (Panel $panel): void {
            $panel
                ->brandName('Your Site Name')
                ->brandLogo(fn () => view('custom.logo'))
                ->colors([
                    'primary' => [
                        50 => '238, 242, 255',
                        100 => '224, 231, 255',
                        // ...other color values
                    ],
                ])
                ->font('Poppins');
        });
    }
}
```

### Adding Custom Resources

You can extend the control panel with your own resources:

```php
// config/inspirecms.php
'filament' => [
    // Existing resources...
    'resources' => [
        'content' => FilamentResources\ContentResource::class,
        // Add your custom resource
        'products' => App\Filament\Resources\ProductResource::class,
    ],
],
```

### Adding Custom Pages

You can add custom pages to the control panel:

```php
// config/inspirecms.php
'filament' => [
    // Existing pages...
    'pages' => [
        'dashboard' => FilamentPages\Dashboard::class,
        // Add your custom page
        'analytics' => App\Filament\Pages\Analytics::class,
    ],
],
```

## Widgets

InspireCMS comes with several dashboard widgets:

- **Content Page Overview**: Shows content statistics and quick links
- **Page Activity**: Displays recent content changes
- **CMS Info**: Shows system information and version
- **Template Info**: Provides information about available templates
- **Tree Navigation**: Visual navigation builder

### Adding Custom Widgets

Add custom widgets to extend dashboard functionality:

```php
// config/inspirecms.php
'filament' => [
    // Other settings...
    'widgets' => [
        // Default widgets
        Widgets\CmsInfoWidget::class,
        Widgets\PageActivity::class,
        // Your custom widget
        App\Filament\Widgets\AnalyticsWidget::class,
    ],
],
```

## User Preferences

Users can customize their experience through the profile page:

- **Personal Information**: Update name, email, and profile picture
- **Password Management**: Change password and security settings
- **Interface Preferences**: Theme preferences and display options
- **Notification Settings**: Configure how and when to receive alerts

## Keyboard Shortcuts

The control panel supports various keyboard shortcuts for power users:

- `Ctrl+S` or `Cmd+S`: Save the current item
- `Ctrl+P` or `Cmd+P`: Open the command palette
- `Esc`: Close modals or cancel operations
- `/`: Focus the global search

## Mobile Experience

The control panel is fully responsive and optimized for mobile devices:

- **Responsive Layout**: Adapts to different screen sizes
- **Touch-Friendly**: Optimized for touch interactions
- **Simplified Navigation**: Collapsible menu for mobile screens

## Browser Compatibility

InspireCMS control panel works best with:

- Chrome (latest 2 versions)
- Firefox (latest 2 versions)
- Safari (latest 2 versions)
- Edge (latest 2 versions)

## Performance Optimization

For optimal control panel performance:

1. Enable caching for production environments
2. Use a CDN for static assets
3. Configure proper database indexing
4. Consider upgrading server resources for larger sites
5. Limit the number of custom fields per content type