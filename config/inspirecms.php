<?php

use SolutionForest\InspireCms\Filament\Clusters\Content\Resources\PageResource;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\DocumentTypeResource;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\FieldGroupResource;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\FieldResource;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\LanguageResource;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\NavigationResource;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\TemplateResource;
use SolutionForest\InspireCms\Filament\Clusters\Users\Resources\RoleResource;
use SolutionForest\InspireCms\Filament\Clusters\Users\Resources\UserResource;
use SolutionForest\InspireCms\Models;
use SolutionForest\InspireCms\Support\Models as SupportModels;

// config for SolutionForest/InspireCms
return [

    'auth' => [
        'guard' => 'inspirecms',
    ],

    'avatar' => [
        'driver' => 'public',
    ],

    'cache' => [
        'languages' => [
            'key' => 'inspirecms.languages',
            'ttl' => 60 * 60 * 24,
        ],
    ],

    'filament' => [
        'enable_cluster_navigation' => true,
        'panel_id' => 'cms',
        'resources' => [
            'page' => PageResource::class,
            'document_type' => DocumentTypeResource::class,
            'field_group' => FieldGroupResource::class,
            'field' => FieldResource::class,
            'language' => LanguageResource::class,
            'template' => TemplateResource::class,
            'user' => UserResource::class,
            'role' => RoleResource::class,
            'navigation' => NavigationResource::class,
        ],
        'pages' => [
            'dashboard' => \SolutionForest\InspireCms\Filament\Pages\Dashboard::class,
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
    ],

    'models' => [
        'table_name_prefix' => 'cms_',
        'morph_map_prefix' => 'cms_',
        'fqcn' => [
            'content' => Models\Content::class,
            'content_version' => Models\ContentVersion::class,
            'content_web_setting' => Models\ContentWebSetting::class,
            'document_type' => Models\DocumentType::class,
            'language' => Models\Language::class,
            'user' => Models\User::class,
            'field_groupable' => Models\Polymorphic\FieldGroupable::class,
            'nestable_tree' => Models\Polymorphic\NestableTree::class,
            'user_login_activity' => Models\Users\UserLoginActivity::class,
            'template' => Models\Template::class,
            'templateable' => Models\Polymorphic\Templateable::class,
            'site_map' => Models\SiteMap::class,
            'media_asset' => SupportModels\MediaAsset::class,
            'navigation' => Models\Navigation::class,
        ],
    ],

    'override_plugins' => [
        'field_group_models' => true,
        'scout' => true,
    ],

    'permissions' => [
        'skip_access_right_permission_on_resource' => false,
    ],

    'template' => [
        'path' => resource_path('views/inspire-cms/templates'),
    ],

    'resolvers' => [
        'user' => \SolutionForest\InspireCms\Support\Resolver\UserResolver::class,
    ],

    'generators' => [
        'content_path_generator' => \SolutionForest\InspireCms\Support\PathGenerators\ContentPathGenerator::class,
        'content_url_generator' => \SolutionForest\InspireCms\Support\UrlGenerators\ContentUrlGenerator::class,
    ],

    'indexes' => [
        'content' => [
            'enabled' => true,
            'index_name' => 'content_index',
            'index_settings' => [
                'filterableAttributes' => ['slug', 'full_path', 'level', 'published_at'],
                'sortableAttributes' => ['level', 'published_at'],
            ],
        ],
    ],

    'routes' => [
        'middleware' => [
            'web',
            \SolutionForest\InspireCms\Http\Middlewares\ContentLocaleMiddleware::class,
        ],
    ],

    'available_locales' => [
        'en',
        'zh_CN',
        'zh_TW',
    ],
];
