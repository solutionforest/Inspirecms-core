<?php

use SolutionForest\InspireCms\Filament\Clusters\Content\Resources\PageResource;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\DocumentTypeResource;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\FieldGroupResource;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\ImportJobResource;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\LanguageResource;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\NavigationResource;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\SitemapResource;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\TemplateResource;
use SolutionForest\InspireCms\Filament\Clusters\Users\Resources\RoleResource;
use SolutionForest\InspireCms\Filament\Clusters\Users\Resources\UserResource;
use SolutionForest\InspireCms\Models;
use SolutionForest\InspireCms\Support\Models as SupportModels;

// config for SolutionForest/InspireCms
return [

    'auth' => [
        'guard' => 'inspirecms',
        'failed_login_attempts' => 5,
        /**
         * The number of minutes to lock the user out for after the maximum number of failed login attempts is reached.
         */
        'lockout_duration' => 120,
    ],

    'avatar' => [
        'driver' => 'public',
    ],

    'cache' => [
        'languages' => [
            'key' => 'inspirecms.languages',
            'ttl' => 60 * 60 * 24,
        ],
        'navigation' => [
            'key' => 'inspirecms.navigation',
            'ttl' => 60 * 60 * 24,
        ],
    ],

    'filament' => [
        'enable_cluster_navigation' => true,
        'panel_id' => 'cms',
        'path' => 'cms',
        'database_notification' => [
            'enabled' => true,
            'polling_interval' => '30s',
        ],
        'login_background_image' => 'https://random.danielpetrica.com/api/random?format=regular',
        'resources' => [
            'page' => PageResource::class,
            'document_type' => DocumentTypeResource::class,
            'field_group' => FieldGroupResource::class,
            'language' => LanguageResource::class,
            'template' => TemplateResource::class,
            'user' => UserResource::class,
            'role' => RoleResource::class,
            'navigation' => NavigationResource::class,
            'sitemap' => SitemapResource::class,
            'import_job' => ImportJobResource::class,
        ],
        'pages' => [
            'dashboard' => \SolutionForest\InspireCms\Filament\Pages\Dashboard::class,
            'health' => \SolutionForest\InspireCms\Filament\Pages\Health::class,
        ],
        'clusters' => [
            'content' => \SolutionForest\InspireCms\Filament\Clusters\Content::class,
            'media' => \SolutionForest\InspireCms\Filament\Clusters\Media::class,
            'settings' => \SolutionForest\InspireCms\Filament\Clusters\Settings::class,
            'users' => \SolutionForest\InspireCms\Filament\Clusters\Users::class,
        ],
        'actions' => [
            \SolutionForest\InspireCms\Filament\Actions\ReorderContentAction::class,
            \SolutionForest\InspireCms\Filament\Actions\ContentHistoryAction::class,

            \SolutionForest\InspireCms\Filament\TreeNode\Actions\ReorderContentItemAction::class,
            \SolutionForest\InspireCms\Filament\TreeNode\Actions\SetDefaultContentPageAction::class,
        ],
    ],

    'media_library' => [
        'disk' => 'public',
        'directory' => '',
        'thumbnail' => [
            'width' => 300,
            'height' => 300,
        ],
        'should_map_video_properties_with_ffmpeg' => false,
    ],

    'imports' => [
        'disk' => 'local',
        'temp_disk' => 'local',
        'temp_directory' => 'temp/imports',
    ],

    'models' => [
        'table_name_prefix' => 'cms_',
        'morph_map_prefix' => 'cms_',
        'fqcn' => [
            'content' => Models\Content::class,
            'content_version' => Models\ContentVersion::class,
            'content_web_setting' => Models\ContentWebSetting::class,
            'content_path' => Models\ContentPath::class,
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
            'import_job' => Models\ImportJob::class,
        ],
    ],

    'override_plugins' => [
        'field_group_models' => true,
    ],

    'permissions' => [
        'skip_access_right_permission_on_resource' => false,
    ],

    'template' => [
        'path' => resource_path('views/inspire-cms/templates'),
    ],

    'resolvers' => [
        'user' => \SolutionForest\InspireCms\Support\Resolvers\UserResolver::class,
    ],

    'generators' => [
        'content_url_generator' => \SolutionForest\InspireCms\Generators\UrlGenerators\ContentUrlGenerator::class,
        'sitemap_generator' => \SolutionForest\InspireCms\Generators\SitemapGenerator::class,
    ],

    'routes' => [
        'middleware' => [
            'web',
        ],
        'sitemap' => [
            'file_path' => public_path('sitemap.xml'),
        ],
    ],

    'scheduled_tasks' => [
        'cleanup_content_verion' => [
            'enabled' => true,
            'schedule' => 'daily',
            'command' => \SolutionForest\InspireCms\Commands\CleanupContentVersion::class,
            'old_content_version_days' => 30,
        ],
        'execute_import_job' => [
            'enabled' => true,
            'schedule' => 'everyFiveMinutes',
            'command' => \SolutionForest\InspireCms\Commands\ExecuteImportJob::class,
            'arguments' => [
                '--limit 50', // limit
            ],
        ],
        'cleanup_import_job' => [
            'enabled' => true,
            'schedule' => 'daily',
            'command' => \SolutionForest\InspireCms\Commands\CleanupImportJob::class,
            'old_import_job_days' => 5,
        ],
    ],

    'available_locales' => [
        'en',
        'zh_CN',
        'zh_TW',
    ],
];
