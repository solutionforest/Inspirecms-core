<?php

use SolutionForest\InspireCms\Filament\Clusters\Contents\Resources\PageResource;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\DocumentTypeResource;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\FieldGroupResource;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\LanguageResource;
use SolutionForest\InspireCms\Filament\Clusters\Users\Resources\RoleResource;
use SolutionForest\InspireCms\Filament\Clusters\Users\Resources\UserResource;

// config for SolutionForest/InspireCms
return [

    'auth' => [
        'guard' => 'inspirecms',
    ],

    'override_plugins' => [
        'field_group_models' => true,
    ],

    'template' => [
        'path' => env('INSPIRECMS_TEMPLATE_PATH', resource_path('views/inspire-cms/templates')),
    ],

    'resources' => [
        'page' => PageResource::class,
        'document_type' => DocumentTypeResource::class,
        'field_group' => FieldGroupResource::class,
        'language' => LanguageResource::class,
        'user' => UserResource::class,
        'role' => RoleResource::class,
    ],

    'models' => [
        'table_name_prefix' => 'cms_',
        'morph_map_prefix' => 'cms_',
    ],
];
