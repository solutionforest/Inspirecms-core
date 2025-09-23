<?php

use SolutionForest\InspireCms\Commands\DataCleanupCommand;
use SolutionForest\InspireCms\Commands\ExecuteExportCommand;
use SolutionForest\InspireCms\Commands\ExecuteImportCommand;
use SolutionForest\InspireCms\Content\DefaultPreviewProvider;
use SolutionForest\InspireCms\Content\DefaultSegmentProvider;
use SolutionForest\InspireCms\Content\DefaultSlugGenerator;
use SolutionForest\InspireCms\Exports\Exporters\DocumentTypeExporter;
use SolutionForest\InspireCms\Exports\Exporters\FieldGroupExporter;
use SolutionForest\InspireCms\Exports\Exporters\ImportUsedExporter;
use SolutionForest\InspireCms\Exports\Exporters\TemplateExporter;
use SolutionForest\InspireCms\Fields\Configs\ContentPicker;
use SolutionForest\InspireCms\Fields\Configs\IconPicker;
use SolutionForest\InspireCms\Fields\Configs\MarkdownEditor;
use SolutionForest\InspireCms\Fields\Configs\MediaPicker;
use SolutionForest\InspireCms\Fields\Configs\Repeater;
use SolutionForest\InspireCms\Fields\Configs\RichEditor;
use SolutionForest\InspireCms\Fields\Configs\Tags;
use SolutionForest\InspireCms\Filament\Clusters as FilamentClusters;
use SolutionForest\InspireCms\Filament\Pages as FilamentPages;
use SolutionForest\InspireCms\Filament\Resources as FilamentResources;
use SolutionForest\InspireCms\Filament\Widgets\CmsInfoWidget;
use SolutionForest\InspireCms\Filament\Widgets\CmsVersionInfo;
use SolutionForest\InspireCms\Filament\Widgets\TemplateInfo;
use SolutionForest\InspireCms\Filament\Widgets\ThemeInfo;
use SolutionForest\InspireCms\Filament\Widgets\UserActivity;
use SolutionForest\InspireCms\Http\Middleware\SetUpPoweredBy;
use SolutionForest\InspireCms\Models;
use SolutionForest\InspireCms\Policies\ContentStatusPolicy;
use SolutionForest\InspireCms\Resolvers\PublishedContentResolver;
use SolutionForest\InspireCms\Sitemap\SitemapGenerator;
use SolutionForest\InspireCms\Support\Models as SupportModels;
use SolutionForest\InspireCms\Support\Resolvers\UserResolver;

// config for SolutionForest/InspireCms
return [

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
            'filament_peek' => true, // Whether to override Filament Peek package functionality
        ],
    ],

    'auth' => [

        /**
         * Define the guard that InspireCMS will use for authentication
         */
        'guard' => [
            'name' => 'inspirecms',
            'driver' => 'session',
            'provider' => 'cms_users',
        ],

        /**
         * Define how users are retrieved from your database
         */
        'provider' => [
            'name' => 'cms_users',
            'driver' => 'eloquent',
            'model' => Models\User::class,
        ],

        /**
         * Password reset functionality
         */
        'resetting_password' => [
            'enabled' => true,
            'name' => 'inspirecms',
            'provider' => 'cms_users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],

        /**
         * Security settings to protect against brute-force attacks
         *
         * Number of failed attempts before lockout
         */
        'failed_login_attempts' => 5,

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

    'media' => [

        /**
         * User avatar storage configuration
         */
        'user_avatar' => [
            'disk' => 'public', // Storage disk to use (public, s3, etc.)
            'directory' => 'avatars', // Subdirectory where avatars will be stored (empty for root)
        ],

        /**
         * Media library configuration
         */
        'media_library' => [
            'disk' => 'public', // Storage disk (public makes files accessible via URL)

            /**
             * Allowed file types
             *
             * e.g. ['image/jpeg', 'image/png', 'video/mp4']
             */
            'allowed_mime_types' => [],

            /**
             * Maximum file size in Bytes
             */
            'max_file_size' => null,

            /**
             * Automatic thumbnail generation settings
             */
            'thumbnail' => [
                'width' => 300,
                'height' => 300,
            ],

            /**
             * Whether to use FFmpeg to extract metadata from video files
             *
             * Set to true to analyze video files
             *
             * Requires FFmpeg to be installed on the server
             * Enables extraction of duration, dimensions, codec info
             * Increases processing time for video uploads
             */
            'should_map_video_properties_with_ffmpeg' => false,

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

    'cache' => [
        'languages' => [
            'store' => null, // null: Fallback to default store
            'key' => 'inspirecms.languages',
            'ttl' => 60 * 60 * 24,
        ],
        'navigation' => [
            'store' => null, // null: Fallback to default store
            'key' => 'inspirecms.navigation',
            'ttl' => 60 * 60 * 24,
        ],
        'content_routes' => [
            'store' => null, // null: Fallback to default store
            'key' => 'inspirecms.content_routes',
            'ttl' => 120 * 60 * 24,
        ],
        'key_value' => [
            'store' => null, // null: Fallback to default store
            'ttl' => 60 * 60 * 24,
            'prefix' => 'inspire_key_value.',
        ],
    ],

    'admin' => [

        /**
         * Whether to enable the admin panel for managing navigation base on the Clusters
         */
        'enable_cluster_navigation' => true,

        'navigation_position' => 'top', // left, top

        'panel_id' => 'cms',

        'path' => 'cms',

        'allow_registration' => false, // Whether to allow user registration via the admin panel

        'brand' => [ // More info https://filamentphp.com/docs/3.x/panels/themes#adding-a-logo
            'name' => 'InspireCMS',
            'logo' => fn () => view('inspirecms::logo'),
            'logo_title' => 'InspireCMS',
            'logo_show_text' => true,
            'favicon' => fn () => asset('images/favicon.png'),
        ],

        'database_notification' => [
            'enabled' => false,
            'polling_interval' => '30s',
        ],

        'background_image' => 'https://random.danielpetrica.com/api/random?format=regular',

        'resources' => [
            'content' => FilamentResources\ContentResource::class,
            'document_type' => FilamentResources\DocumentTypeResource::class,
            'field_group' => FilamentResources\FieldGroupResource::class,
            'language' => FilamentResources\LanguageResource::class,
            'template' => FilamentResources\TemplateResource::class,
            'user' => FilamentResources\UserResource::class,
            'role' => FilamentResources\RoleResource::class,
            'navigation' => FilamentResources\NavigationResource::class,
            'sitemap' => FilamentResources\SitemapResource::class,
        ],

        'pages' => [
            'dashboard' => FilamentPages\Dashboard::class,
            'export' => FilamentPages\Export::class,
            'health' => FilamentPages\Health::class,
        ],

        'clusters' => [
            'content' => FilamentClusters\Content::class,
            'media' => FilamentClusters\Media::class,
            'settings' => FilamentClusters\Settings::class,
            'users' => FilamentClusters\Users::class,
        ],

        'extra_widgets' => [
            // Extra widgets to be added to the CMS Dashboard
        ],
    ],

    'import_export' => [

        'imports' => [

            'disk' => 'local',
            'directory' => 'imports',

            'temporary' => [
                'disk' => 'local',
                'directory' => 'temp/imports',
            ],

            /**
             * Allowed file types for import
             * e.g. ['application/vnd.ms-excel', 'text/csv', 'text/plain']
             */
            'allowed_mime_types' => [
                'application/zip',
                'application/octet-stream',
                'application/x-zip-compressed',
                'multipart/x-zip',
            ],

            /**
             * Maximum file size in KB
             */
            'max_file_size' => 10 * 1024,
        ],

        'exports' => [

            'disk' => 'local',
            'directory' => 'exports',

            'temporary' => [
                'disk' => 'local',
                'directory' => 'temp/exports',
            ],

            'exporters' => [
                ImportUsedExporter::class,
                DocumentTypeExporter::class,
                FieldGroupExporter::class,
                TemplateExporter::class,
            ],
        ],
    ],

    'models' => [

        'table_name_prefix' => 'cms_',

        'morph_map_prefix' => 'cms_',

        'fqcn' => [
            'content' => Models\Content::class,
            'content_path' => Models\ContentPath::class,
            'content_route' => Models\ContentRoute::class,
            'content_lock' => Models\ContentLock::class,
            'content_version' => Models\ContentVersion::class,
            'content_web_setting' => Models\ContentWebSetting::class,
            'document_type' => Models\DocumentType::class,
            'document_type_inheritance' => Models\Pivot\DocumentTypeInheritance::class,
            'language' => Models\Language::class,
            'user' => Models\User::class,
            'field_groupable' => Models\Polymorphic\FieldGroupable::class,
            'user_login_activity' => Models\Users\UserLoginActivity::class,
            'template' => Models\Template::class,
            'templateable' => Models\Polymorphic\Templateable::class,
            'sitemap' => Models\Sitemap::class,
            'navigation' => Models\Navigation::class,
            'media_asset' => SupportModels\MediaAsset::class,
            'nestable_tree' => SupportModels\Polymorphic\NestableTree::class,
            'import' => Models\Import::class,
            'export' => Models\Export::class,
            'key_value' => Models\KeyValue::class,
        ],

        /**
         * Policy mappings control authorization
         */
        'policies' => [
            'content' => ContentStatusPolicy::class,
        ],

        /**
         * Auto-cleanup settings for database tables that can grow large
         */
        'prunable' => [
            'content_version' => [
                'interval' => 30,
            ],
            'import' => [
                'interval' => 5,
            ],
            'export' => [
                'interval' => 5,
            ],
        ],
    ],

    'custom_fields' => [
        'extra_config' => [
            Repeater::class,
            Tags::class,
            RichEditor::class,
            MarkdownEditor::class,
            ContentPicker::class,
            MediaPicker::class,
            IconPicker::class,
        ],
    ],

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
            CmsVersionInfo::class,
            CmsInfoWidget::class,
            ThemeInfo::class,
            TemplateInfo::class,
            UserActivity::class,
        ],
    ],

    'template' => [
        /**
         * The prefix for the component names used in the CMS
         */
        'component_prefix' => 'inspirecms',

        /**
         * The directory where exported templates are stored
         */
        'exported_template_dir' => resource_path('views/inspirecms/templates'),
    ],

    'resolvers' => [
        'user' => UserResolver::class,
        'published_content' => PublishedContentResolver::class,
    ],

    'frontend' => [
        'routes' => [
            'middleware' => [
                SetUpPoweredBy::class,
            ],
        ],
        'segment_provider' => DefaultSegmentProvider::class,
        'preview_provider' => DefaultPreviewProvider::class,
        'slug_generator' => DefaultSlugGenerator::class,
        'fallback_seo' => [
            'title' => env('APP_NAME', 'Home'),
            'description' => null,
            'keywords' => null,
            'image' => null,
        ],
    ],

    'sitemap' => [
        'generator' => SitemapGenerator::class,
        'file_path' => public_path('sitemap.xml'),
    ],

    'scheduled_tasks' => [
        'execute_import_job' => [
            'enabled' => true,
            'schedule' => 'everyFiveMinutes',
            'command' => ExecuteImportCommand::class,
            'arguments' => [
                '--limit 50', // limit
            ],
        ],
        'execute_export_job' => [
            'enabled' => true,
            'schedule' => 'everyFiveMinutes',
            'command' => ExecuteExportCommand::class,
            'arguments' => [
                '--limit 50', // limit
            ],
        ],
        'data_cleanup' => [
            'enabled' => true,
            'schedule' => 'daily',
            'command' => DataCleanupCommand::class,
        ],
    ],

    'localization' => [
        'available_locales' => ['en', 'fr', 'zh_CN', 'zh_TW', 'es', 'ja', 'de'],
        'user_preferred_locales' => ['en', 'zh_CN', 'zh_TW'],
    ],
];
