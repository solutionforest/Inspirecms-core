<?php

use SolutionForest\InspireCms\Filament\Clusters\Content\Resources\PageResource;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\DocumentTypeResource;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\FieldGroupResource;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\ImportResource;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\LanguageResource;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\NavigationResource;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\SitemapResource;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\TemplateResource;
use SolutionForest\InspireCms\Filament\Clusters\Users\Resources\RoleResource;
use SolutionForest\InspireCms\Filament\Clusters\Users\Resources\UserResource;
use SolutionForest\InspireCms\Models;
use SolutionForest\InspireCms\Policies;
use SolutionForest\InspireCms\Support\Models as SupportModels;

// config for SolutionForest/InspireCms
return [

    'override_plugins' => [
        'field_group_models' => true,
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
        'failed_login_attempts' => 5,
        /**
         * The number of minutes to lock the user out for after the maximum number of failed login attempts is reached.
         */
        'lockout_duration' => 120,
    ],

    'avatar' => [
        'driver' => 'public',
        'directory' => 'avatars',
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
        'content_routes' => [
            'key' => 'inspirecms.content_routes',
            'ttl' => 120 * 60 * 24,
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
        'background_image' => 'https://random.danielpetrica.com/api/random?format=regular',
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
            'import_job' => ImportResource::class,
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
            'content_path' => Models\ContentPath::class,
            'content_version' => Models\ContentVersion::class,
            'content_web_setting' => Models\ContentWebSetting::class,
            'content_route' => Models\ContentRoute::class,
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
        ],
    ],

    'permissions' => [
        'skip_access_right_permission_on_resource' => false,
        'guard_actions' => [
            \SolutionForest\InspireCms\Filament\Actions\ReorderContentAction::class,
            \SolutionForest\InspireCms\Filament\Actions\ContentHistoryAction::class,
            \SolutionForest\InspireCms\Filament\Actions\ExportTemplateAction::class,

            \SolutionForest\InspireCms\Filament\TreeNode\Actions\ReorderContentItemAction::class,
            \SolutionForest\InspireCms\Filament\TreeNode\Actions\SetDefaultContentPageAction::class,
        ],
    ],

    'template' => [
        'theme' => 'manifest',
        'themes' => [
            'manifest' => 'Manifest',
            'blogrock' => 'Blogrock',
            'know-press' => 'Know Press',
        ],
        'component_prefix' => 'inspirecms',
        'exported_template_dir' => resource_path('views/inspirecms/templates'),
    ],

    'resolvers' => [
        'user' => \SolutionForest\InspireCms\Support\Resolvers\UserResolver::class,
        'published_content' => \SolutionForest\InspireCms\Resolvers\PublishedContentResolver::class,
    ],

    'content' => [
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
        'data_cleanup' => [
            'enabled' => true,
            'schedule' => 'daily',
            'command' => \SolutionForest\InspireCms\Commands\DataCleanup::class,
        ],
    ],

    'available_locales' => [
        'en',
        'zh_CN',
        'zh_TW',
    ],
];
