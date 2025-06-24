---
title: Configuration
slug: configuration
path: docs/v1/configuration
uri: /docs/1.x/configuration
heading: Configuration
brief:
---

## Configuration Files

InspireCMS's configuration is primarily managed through the `config/inspirecms.php` file. If this file doesn't exist after installation, you can publish it using:

```bash
php artisan vendor:publish --tag="inspirecms-config"
```

## Key Configuration Sections

### System Settings

```php
'system' => [
    /**
     * Whether to include an X-Powered-By header in HTTP responses
     *
     * When true, InspireCMS adds an X-Powered-By HTTP header to responses.
     */
    'send_powered_by_header' => true,

    /**
     * License configuration for InspireCMS
     *
     * These settings are required for the CMS to validate your license.
     */
    'license' => [
        // Your InspireCMS license key from your subscription
        'key' => env('INSPIRECMS_LICENSE_KEY'),
    ],

    /**
     * Control how InspireCMS interacts with key plugins
     */
    'override_plugins' => [
        'field_group_models' => true, // Whether to override field group models
        'spatie_permission' => true,  // Whether to override Spatie Permission package functionality
    ],
],
```

### Authentication

Configure how users authenticate with your CMS \([learn more about laravel authentication](<(https://laravel.com/docs/12.x/authentication#adding-custom-guards)>)\):

```php
'auth' => [

    /**
     * Define the guard that InspireCMS will use for authentication
     */
    'guard' => [
        'name' => 'inspirecms', // The name of the guard - used in auth middleware
        'driver' => 'session', // Authentication method (session or token)
        'provider' => 'cms_users', // Which provider this guard uses
    ],

    /**
     * Define how users are retrieved from your database
     */
    'provider' => [
        'name' => 'cms_users', // Name of the provider
        'driver' => 'eloquent', // Driver to use (eloquent or database)
        'model' => \SolutionForest\InspireCms\Models\User::class, // User model - change to use a custom model
    ],

    /**
     * Password reset functionality
     */
    'resetting_password' => [
        'enabled' => true, // Set to false to disable password reset functionality
        // other password reset settings...
    ],

    /**
     * Security settings to protect against brute-force attacks
     *
     * Number of failed attempts before lockout
     */
    'failed_login_attempts' => 5, // Number of attempts before account lockout

    /**
     * The number of minutes to lock the user out for after the maximum number of failed login attempts is reached.
     */
    'lockout_duration' => 120, // Duration of lockout in minutes

    /**
     * Controls when super admin checks are performed in the authentication flow
     *
     * Allowed values: before, after, none
     */
    'skip_super_admin_check' => 'before',

    /**
     * Skip account verification for users.
     *
     * Set to true to skip account email verification requirements.
     */
    'skip_account_verification' => false,
],
```

### Media Management

Configure media uploads, storage, and processing:

```php
'media' => [

    /**
     * User avatar storage configuration
     */
    'user_avatar' => [
        'disk' => 'public',        // Storage disk to use (public, s3, etc.)
        'directory' => 'avatars',  // Subdirectory where avatars will be stored
    ],

    /**
     * Media library configuration
     */
    'media_library' => [
        'disk' => 'public',        // Storage disk (public makes files accessible via URL)
                                  // Use 's3' or other drivers for cloud storage

        /**
         * Allowed file types
         *
         * e.g. ['image/jpeg', 'image/png', 'video/mp4']
         */
        'allowed_mime_types' => [],

        /**
         * Maximum file size in KB
         */
        'max_file_size' => null,

        /**
         * Automatic thumbnail generation settings
         */
        'thumbnail' => [
            'width' => 300,        // Width of generated thumbnails in pixels
            'height' => 300,       // Height of generated thumbnails in pixels
                                  // Set both the same for square thumbnails
        ],

        /**
         * Whether to use FFmpeg to extract metadata from video files
         */
        'should_map_video_properties_with_ffmpeg' => false, // Set to true to analyze video files
                      // Requires FFmpeg to be installed on the server
                      // Enables extraction of duration, dimensions, codec info
                      // Increases processing time for video uploads

        /**
         * Responsive image generation settings
         */
        'responsive_images' => [
            'small' => [
                'enabled' => true,
                'width' => 400,
            ],
            'medium' => [
                'enabled' => true,
                'width' => 600,
            ],
        ],
    ],
],
```

### Caching

Optimize performance with caching configurations:

```php
'cache' => [
     'languages' => [
        'store' => null, // null: Fallback to default store
        'key' => 'inspirecms.languages',  // Cache key for storing language data
        'ttl' => 60 * 60 * 24,            // Time-to-live in seconds (24 hours)
                                        // Decrease for more frequent updates
                                        // Increase for better performance
    ],
    'navigation' => [
        'store' => null, // null: Fallback to default store
        'key' => 'inspirecms.navigation', // Cache key for menu structures
        'ttl' => 60 * 60 * 24,            // 24 hour cache duration
                                        // Clear with: php artisan cache:clear
                                        // Critical for site performance under high traffic
    ],
    'content_routes' => [
        'store' => null, // null: Fallback to default store
        'key' => 'inspirecms.content_routes', // Cache key for content URL routing data
        'ttl' => 120 * 60 * 24,               // 5-day cache duration (longer than other caches)
                                                // Extended duration improves routing performance
                                                // Clear after adding new content types
    ],
    'key_value' => [
        'store' => null, // null: Fallback to default store
        'ttl' => 60 * 60 * 24,              // Cache duration for system settings
                                                // Affects all configuration values retrieved at runtime
                                                // Consider shorter values during development
                                                // Longer values (3-7 days) for production
        'prefix' => 'inspire_key_value.',
    ],
    // For production environments, consider enabling a persistent cache driver
    // such as Redis or Memcached in your .env file:
    // CACHE_DRIVER=redis

    // Monitor cache usage with: php artisan inspirecms:cache-stats
],
```

### Admin Panel

Configure the admin panel:

```php
use SolutionForest\InspireCms\Filament\Clusters as FilamentClusters;
use SolutionForest\InspireCms\Filament\Pages as FilamentPages;
use SolutionForest\InspireCms\Filament\Resources as FilamentResources;

'admin' => [
    'enable_cluster_navigation' => true, // Group navigation items by function
                                        // Set to false for a flat navigation structure
    'navigation_position' => 'top',     // left, top
    'panel_id' => 'cms',               // Internal identifier for the panel
                                        // Must be unique if using multiple panels
    'path' => 'cms',                   // URL path segment for admin area
                                        // Example: https://yoursite.com/cms

    'allow_registration' => false, // Whether to allow user registration via the admin panel

    'brand' => [ // More info https://filamentphp.com/docs/3.x/panels/themes#adding-a-logo
        'name' => 'InspireCMS',        // Display name shown in admin header
        'logo' => fn () => view('inspirecms::logo'), // Logo component (can be replaced with custom view)
        'logo_title' => 'InspireCMS',
        'logo_show_text' => true,
        'favicon' => fb () => asset('images/favicon.png'), // Browser tab icon
    ],
    'database_notification' => [
        'enabled' => true,             // Real-time admin notifications
                                        // Disable for improved performance
        'polling_interval' => '30s',   // How often to check for new notifications
                                        // Lower for more responsiveness, higher for reduced server load
    ],
    'background_image' => 'https://random.danielpetrica.com/api/random?format=regular',
                                        // Login page background
                                        // Replace with your own image path for branding

    // Resource classes define admin CRUD interfaces
    // Replace with custom classes to modify behavior
    'resources' => [
        'content' => FilamentResources\ContentResource::class,
        'document_type' => FilamentResources\DocumentTypeResource::class,
        // ... other resources
    ],

    // Admin panel pages (replace to customize specific pages)
    'pages' => [
        'dashboard' => FilamentPages\Dashboard::class,
        'export' => FilamentPages\Export::class,
        'health' => FilamentPages\Health::class,
    ],

    // Navigation clusters (groupings of admin features)
    'clusters' => [
        'content' => FilamentClusters\Content::class,
        'media' => FilamentClusters\Media::class,
        'settings' => FilamentClusters\Settings::class,
        'users' => FilamentClusters\Users::class,
    ],

    // Extra widgets to display on the dashboard
    // Add custom widgets to enhance the admin experience
    'extra_widgets' => [
        // Example: App\Filament\Widgets\LatestOrders::class,
        // Example: App\Filament\Widgets\VisitorStatistics::class,
        // Each widget will appear on the dashboard page
        // Implement custom widgets by extending Filament\Widgets\Widget
    ],
],
```

### Data Import/Export

Manage data migration and content portability:

```php
'import_export' => [

    'imports' => [

        // Storage configuration for imports
        'disk' => 'local',          // Storage disk for finished imports
                        // Options: 'local', 'public', 's3', etc.
                        // Use 'local' for security as imports may contain sensitive data
        'directory' => 'imports',   // Directory within the disk where imports are stored
                        // Keep distinct from other file types for organization

        'temporary' => [
            'disk' => 'local',      // Storage for in-progress imports before processing
                        // Should be fast, local storage for performance
            'directory' => 'temp/imports', // Temporary location during processing
                        // Automatically cleaned after successful import
        ],

        'allowed_mime_types' => [   // Limit file formats for security
            'application/zip',
            'application/octet-stream',
            'application/x-zip-compressed',
            'multipart/x-zip',
        ],
        'max_file_size' => 10 * 1024, // 10MB limit
                                      // Increase for larger datasets
    ],

    'exports' => [
        'directory' => storage_path('app/exports'), // Where export files are saved
        'include_media' => false,    // Set to true to include media files in exports
                                    // Warning: Can create very large exports
        'include_users' => false,    // Set to true to export user accounts
                                    // Consider security implications
    ],
],
```

### Models and Database

Configure entity models and database settings:

```php
use SolutionForest\InspireCms\Models;
use SolutionForest\InspireCms\Policies;
use SolutionForest\InspireCms\Support\Models as SupportModels;

'models' => [
    'table_name_prefix' => 'cms_',  // Prefix for database tables
                                   // Change requires database migration update
    'morph_map_prefix' => 'cms_',   // Prefix for polymorphic relationships

    // Model class mappings - replace with your own to extend functionality
    // Example: 'user' => App\Models\User::class
    'fqcn' => [
        'content' => Models\Content::class,
        'content_path' => Models\ContentPath::class,
        // ... other models
    ],

    /**
     * Policy mappings control authorization
     */
    'policies' => [
        'content' => Policies\ContentStatusPolicy::class,
        // Add custom policies here
    ],

    /**
     * Auto-cleanup settings for database tables that can grow large
     */
    'prunable' => [
        'content_version' => [
            'interval' => 30,      // Delete content versions older than 30 days
        ],
        'import' => [
            'interval' => 5,       // Delete import records older than 5 days
        ],
        'export' => [
            'interval' => 5,       // Delete export records older than 5 days
        ],
    ],
],
```

### Custom Fields

Define and manage custom fields for content types:

```php
'custom_fields' => [
    // Register field configuration classes
    // Add your own custom field types by creating a class that extends
    // \SolutionForest\InspireCms\Fields\Configs\FieldConfig
    'extra_config' => [
        // Complex field types
        \SolutionForest\InspireCms\Fields\Configs\Repeater::class,      // Repeatable field groups
        \SolutionForest\InspireCms\Fields\Configs\Tags::class,          // Tag selection field

        // Rich content editors
        \SolutionForest\InspireCms\Fields\Configs\RichEditor::class,    // WYSIWYG editor
        \SolutionForest\InspireCms\Fields\Configs\MarkdownEditor::class, // Markdown support

        // Relationship fields
        \SolutionForest\InspireCms\Fields\Configs\ContentPicker::class, // Select related content
        \SolutionForest\InspireCms\Fields\Configs\MediaPicker::class,   // Select media items
    ],
],
```

### Permissions

Set up role-based access control:

```php

use SolutionForest\InspireCms\Filament\Widgets as FilamentWidgets;

'permissions' => [
    /**
     * Whether to skip access right permission checks on resources
     */
    'skip_access_right_permission_on_resource' => false,

    /**
     * Define actions that require specific permissions
     */
    'guard_actions' => [

    ],

    /**
     * Dashboard widgets requiring permissions to view
     */
    'guard_widgets' => [
        FilamentWidgets\CmsInfoWidget::class,
        FilamentWidgets\TemplateInfo::class,
        FilamentWidgets\UserActivity::class,
    ],
],
```

### Template Management

Configure themes and templates:

```php
'template' => [
    'component_prefix' => 'inspirecms', // Prefix for Blade layout components
                                       // Example usage: <x-inspirecms.{theme-name}.component-name>
    'exported_template_dir' => resource_path('views/inspirecms/templates'),
                                       // Where template exports are stored
                                       // Make sure this directory exists and is writable
],
```

### Resolvers

Configure how InspireCMS resolves various components:

```php
'resolvers' => [
    // Service classes for resolving common entities
    // Replace with custom classes to modify behavior

    // How the current user is determined
    'user' => \SolutionForest\InspireCms\Support\Resolvers\UserResolver::class,

    // How published content is retrieved and filtered
    'published_content' => \SolutionForest\InspireCms\Resolvers\PublishedContentResolver::class,

    // Add custom resolvers here as needed for extending functionality
],
```

### Frontend and Routing

Control how InspireCMS handles frontend requests:

```php
'frontend' => [
    'routes' => [
        'middleware' => [],         // Apply middleware to all frontend routes
                                   // Example: ['web', 'localize', 'cache']
                                   // Core middleware like 'web' is already applied
    ],
    /**
     * Handles URL segment parsing for content routing
     *
     * Replace with custom class to implement custom URL schemes
     */
    'segment_provider' => \SolutionForest\InspireCms\Content\DefaultSegmentProvider::class,
    /**
     * Class that handles content previews
     *
     * Override with a custom class to implement specialized preview behavior
     */
    'preview_provider' => \SolutionForest\InspireCms\Content\DefaultPreviewProvider::class,

    /**
     * Controls how URL slugs are generated for content
     *
     * Override to implement custom slug generation rules
     */
    'slug_generator' => \SolutionForest\InspireCms\Content\DefaultSlugGenerator::class,
],
```

### Sitemap Generation

Configure automatic sitemap generation:

```php
'sitemap' => [
    // Class responsible for generating sitemaps
    // Replace with custom class for specialized sitemap behavior
    'generator' => \SolutionForest\InspireCms\Sitemap\SitemapGenerator::class,

    // Where the sitemap is stored - should be in public web directory
    'file_path' => public_path('sitemap.xml'),

    // To regenerate sitemap: php artisan inspirecms:generate-sitemap
],
```

### Scheduled Tasks

Set up automated background tasks:

```php
'scheduled_tasks' => [
    'execute_import_job' => [
        'enabled' => true,           // Enable/disable automated imports
        'schedule' => 'everyFiveMinutes', // Laravel schedule frequency
                                         // See Laravel docs for options
        // Command configurations...
    ],
    'execute_export_job' => [
        'enabled' => true,           // Enable/disable automated exports
        // Export job settings...
    ],
    'data_cleanup' => [
        'enabled' => true,           // Enable/disable data pruning
        'schedule' => 'daily',       // Run once per day
        // Cleanup settings...
    ],

    // To use scheduled tasks, ensure Laravel's scheduler is running:
    // * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
],
```

### Localization

Configure language and translation settings:

```php
'localization' => [
    // Languages available in the admin panel
    // Format: language code or locale identifier
    'user_preferred_locales' => ['en','zh_CN','zh_TW'],
],
```
