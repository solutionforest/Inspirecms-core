# Configuration

The `config/inspirecms.php` file contains global settings

## Key Configuration Sections

### License Management
- `license_key`: Your InspireCMS license key, typically managed through environment variables

### Plugin System
- `override_plugins`: Controls which plugins can be overridden by the InspireCMS

### Authentication
- Guards configuration ([Learn more about custom guards](https://laravel.com/docs/12.x/authentication#adding-custom-guards))
    - `auth.guard.name`: Name of the authentication guard
    - `auth.guard.driver`: Authentication driver to use (e.g., session, token)
    - `auth.guard.provider`: User provider for the guard
- User provider configuration ([Learn more about custom user providers](https://laravel.com/docs/12.x/authentication#adding-custom-user-providers))
    - `auth.provider.name`: Name of the user provider
    - `auth.provider.driver`: User provider driver (e.g., eloquent, database)
    - `auth.provider.model`: User model class for the provider
- Password reset settings ([Learn more about password resets](https://laravel.com/docs/12.x/passwords))
    - `auth.resetting_password.enabled`: Enable/disable password reset functionality
    - `auth.resetting_password.name`: Name of the password broker
    - `auth.resetting_password.provider`: User provider for password resets
    - `auth.resetting_password.table`: Database table for storing reset tokens
    - `auth.resetting_password.expire`: Expiration time for reset tokens in minutes
    - `auth.resetting_password.throttle`: Minimum seconds between reset attempts
- `auth.failed_login_attempts`: Maximum number of failed login attempts before lockout
- `auth.lockout_duration`: Duration (in minutes) for account lockout after failed attempts
- `auth.skip_super_admin_check`: Whether to bypass authentication checks for super admins (before / after / none)
- `auth.skip_account_verification`: Enable/disable account verification requirement

### Media Management
- Avatar storage configuration
    - `media.user_avatar.disk`: Storage disk for user avatars
    - `media.user_avatar.directory`: Directory path for storing avatars
- Media library settings 
    - `media.media_library.disk`: Storage disk for media library files
    - `media.media_library.directory`: Directory path for storing media files
    - `media.media_library.thumbnail.width`: Default width for generated thumbnails
    - `media.media_library.thumbnail.height`: Default height for generated thumbnails
    - `media.media_library.should_map_video_properties_with_ffmpeg`: Enable video metadata extraction with FFmpeg
    - `media.media_library.middlewares`: Middleware applied to media library routes

### Caching
- Language caching configurations, and TTL (time-to-live) settings
    - `cache.languages.key`: Cache key for languages
    - `cache.languages.ttl`: Time-to-live duration for languages cache
- Navigation caching configurations, and TTL (time-to-live) settings
    - `cache.navigation.key`: Cache key for navigation
    - `cache.navigation.ttl`: Time-to-live duration for navigation cache
- Content route caching configurations, and TTL (time-to-live) settings
    - `cache.content_routes.key`: Cache key for content routes
    - `cache.content_routes.ttl`: Time-to-live duration for content routes cache
- Key-Value caching TTL (time-to-live) settings
    - `cache.key_value.ttl`: Time-to-live duration for key-value cache entries

### Filament Admin Panel
- `filament.panel_id`: Unique identifier for the admin panel
- `filament.path`: URL path for accessing the admin panel
- `filament.enable_cluster_navigation`: Enable/disable grouped navigation clusters
- `filament.background_image`: Path to custom background image for login/authentication screens
- `filament.resources`: List of resource classes to register with the panel
- `filament.pages`: List of custom page classes to register with the panel
- `filament.clusters`: Panel cluster configurations for organizing admin functionality
- Notification polling settings
    - `filament.database_notification.enabled`: Enable/disable database notification polling
    - `filament.database_notification.polling_interval`: Interval (in seconds) for polling notifications

### Data Import/Export
- Import
    - `imports.disk`: Storage disk for import files
    - `imports.temp_disk`: Temporary storage disk for in-progress imports
    - `imports.temp_directory`: Directory path for temporary import files
- Export
    - `exports.disk`: Storage disk for export files
    - `exports.exporters`: List of available exporter classes

### Models and Database
- `models.table_name_prefix`: Prefix added to all database table names
- `models.morph_map_prefix`: Prefix for polymorphic relationship mappings
- `models.fqcn`: Fully qualified class names for all models used by the CMS
- `models.policies`: Model-to-policy class mappings for authorization
- `models.prunable`: Configuration for models with automatic data pruning

### Custom Fields
- `custom_fields.extra_config`: Extensions and configurations for custom field types

### Permissions
- `permissions.skip_access_right_permission_on_resource`: Resources exempt from access permission checks
- `permissions.guard_actions`: Actions that require permission checks
- `permissions.guard_widgets`: Widgets that require permission checks

### Template
- `template.default_theme`: Default theme to use for frontend rendering
- `template.component_prefix`: Prefix used for template component registration
- `template.exported_template_dir`: Directory for exported template files

### Resolvers
- `resolvers.user`: Class used to resolve author/user information
- `resolvers.published_content`: Class used to resolve published content

### Frontend
- `frontend.routes.middleware`: Middleware applied to frontend routes
- `frontend.segment_provider`: Class used for URL segment resolution

### Sitemap Generation
- `sitemap.generator`: Class responsible for generating the sitemap
- `sitemap.file_path`: Output file path for the generated sitemap

### Scheduled Tasks
- Import job execution
    - `scheduled_tasks.execute_import_job.enabled`: Enable/disable scheduled import jobs
    - `scheduled_tasks.execute_import_job.schedule`: Cron expression for job scheduling
    - `scheduled_tasks.execute_import_job.command`: Command to execute
    - `scheduled_tasks.execute_import_job.arguments`: Command arguments
- Export job execution
    - `scheduled_tasks.execute_export_job.enabled`: Enable/disable scheduled export jobs
    - `scheduled_tasks.execute_export_job.schedule`: Cron expression for job scheduling
    - `scheduled_tasks.execute_export_job.command`: Command to execute
    - `scheduled_tasks.execute_export_job.arguments`: Command arguments
- Data cleanup scheduling
    - `scheduled_tasks.data_cleanup.enabled`: Enable/disable scheduled data cleanup
    - `scheduled_tasks.data_cleanup.schedule`: Cron expression for cleanup scheduling
    - `scheduled_tasks.data_cleanup.command`: Command to execute for cleanup

### Localization
- `available_locales`: Available locales for the CMS