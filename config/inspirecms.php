<?php

use SolutionForest\InspireCms\Filament\Clusters\Content\Resources\PageResource;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\DocumentTypeResource;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\FieldGroupResource;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\FieldResource;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\LanguageResource;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\TemplateResource;
use SolutionForest\InspireCms\Filament\Clusters\Users\Resources\RoleResource;
use SolutionForest\InspireCms\Filament\Clusters\Users\Resources\UserResource;

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
        ],
    ],

    'models' => [
        'table_name_prefix' => 'cms_',
        'morph_map_prefix' => 'cms_',
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
        'user' => \SolutionForest\InspireCms\Resolver\UserResolver::class,
    ],

    'available_locales' => [
        'en',
        'zh_CN',
        'zh_TW',
    ],
];
