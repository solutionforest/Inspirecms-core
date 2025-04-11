<?php

use SolutionForest\InspireCms\Filament\Clusters as FilamentClusters;
use SolutionForest\InspireCms\Filament\Pages as FilamentPages;
use SolutionForest\InspireCms\Filament\Resources as FilamentResources;
use SolutionForest\InspireCms\Models;
use SolutionForest\InspireCms\Policies;
use SolutionForest\InspireCms\Support\Models as SupportModels;

// config for SolutionForest/InspireCms
return [

    'license_key' => env('INSPIRECMS_LICENSE_KEY'),

    'override_plugins' => [
        'field_group_models' => true,
        'spatie_permission' => true,
    ],

    'auth' => [
        'guard' => [
            'name' => 'inspirecms',
            'driver' => 'session',
            'provider' => 'cms_users',
        ],
        'provider' => [
            'name' => 'cms_users',
            'driver' => 'eloquent',
            'model' => \SolutionForest\InspireCms\Models\User::class,
        ],
        'resetting_password' => [
            'enabled' => true,
            'name' => 'inspirecms',
            'provider' => 'cms_users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],

        'failed_login_attempts' => 5,

        /**
         * The number of minutes to lock the user out for after the maximum number of failed login attempts is reached.
         */
        'lockout_duration' => 120,

        /**
         * Skip authentication for super admin users.
         *
         * Allowed values: before, after, none
         */
        'skip_super_admin_check' => 'before',

        /**
         * Skip account verification for users.
         */
        'skip_account_verification' => false,
    ],

    'media' => [
        'user_avatar' => [
            'disk' => 'public',
            'directory' => 'avatars',
        ],

        'media_library' => [
            'disk' => 'public',
            'directory' => '',
            'thumbnail' => [
                'width' => 300,
                'height' => 300,
            ],
            'should_map_video_properties_with_ffmpeg' => false,
            'middlewares' => [
                'cache.headers:public;max_age=2628000;etag',
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
        'enable_cluster_navigation' => true,
        'panel_id' => 'cms',
        'path' => 'cms',
        'brand' => [ // More info https://filamentphp.com/docs/3.x/panels/themes#adding-a-logo
            'name' => 'InspireCMS',
            'logo' => fn () => view('inspirecms::logo'), 
            'favicon' => fn () => asset('images/favicon.png'),
        ],
        'database_notification' => [
            'enabled' => true,
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
            'import' => FilamentResources\ImportResource::class,
            'export' => FilamentResources\ExportResource::class,
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
    ],

    'import_export' => [

        'imports' => [

            'disk' => 'local',
            'directory' => 'imports',

            'temporary' => [
                'disk' => 'local',
                'directory' => 'temp/imports',
            ],
            
            'allowed_mime_types' => [
                'application/zip',
                'application/octet-stream',
                'application/x-zip-compressed',
                'multipart/x-zip',
            ],

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
                \SolutionForest\InspireCms\Exports\Exporters\ImportUsedExporter::class,
                \SolutionForest\InspireCms\Exports\Exporters\DocumentTypeExporter::class,
                \SolutionForest\InspireCms\Exports\Exporters\FieldGroupExporter::class,
                \SolutionForest\InspireCms\Exports\Exporters\TemplateExporter::class,
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
        'policies' => [
            'content' => Policies\ContentStatusPolicy::class,
        ],
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

            \SolutionForest\InspireCms\Fields\Configs\Repeater::class,
            \SolutionForest\InspireCms\Fields\Configs\Tags::class,

            \SolutionForest\InspireCms\Fields\Configs\RichEditor::class,
            \SolutionForest\InspireCms\Fields\Configs\MarkdownEditor::class,

            \SolutionForest\InspireCms\Fields\Configs\ContentPicker::class,
            \SolutionForest\InspireCms\Fields\Configs\MediaPicker::class,
        ],
    ],

    'permissions' => [
        'skip_access_right_permission_on_resource' => false,
        'guard_actions' => [

        ],
        'guard_widgets' => [
            \SolutionForest\InspireCms\Filament\Widgets\CmsInfoWidget::class,
            \SolutionForest\InspireCms\Filament\Widgets\TemplateInfo::class,
        ],
    ],

    'template' => [
        'default_theme' => 'manifest',
        'component_prefix' => 'inspirecms',
        'exported_template_dir' => resource_path('views/inspirecms/templates'),
    ],

    'resolvers' => [
        'user' => \SolutionForest\InspireCms\Support\Resolvers\UserResolver::class,
        'published_content' => \SolutionForest\InspireCms\Resolvers\PublishedContentResolver::class,
    ],

    'frontend' => [
        'routes' => [
            'middleware' => [],
        ],
        'segment_provider' => \SolutionForest\InspireCms\Content\DefaultSegmentProvider::class,
    ],

    'sitemap' => [
        'generator' => \SolutionForest\InspireCms\Sitemap\SitemapGenerator::class,
        'file_path' => public_path('sitemap.xml'),
    ],

    'scheduled_tasks' => [
        'execute_import_job' => [
            'enabled' => true,
            'schedule' => 'everyFiveMinutes',
            'command' => \SolutionForest\InspireCms\Commands\ExecuteImport::class,
            'arguments' => [
                '--limit 50', // limit
            ],
        ],
        'execute_export_job' => [
            'enabled' => true,
            'schedule' => 'everyFiveMinutes',
            'command' => \SolutionForest\InspireCms\Commands\ExecuteExport::class,
            'arguments' => [
                '--limit 50', // limit
            ],
        ],
        'data_cleanup' => [
            'enabled' => true,
            'schedule' => 'daily',
            'command' => \SolutionForest\InspireCms\Commands\DataCleanup::class,
        ],
    ],

    'localization' => [
        'user_preferred_locales' => ['en','zh_CN','zh_TW'],
    ],
];
