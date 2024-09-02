<?php

use SolutionForest\InspireCms\Filament\Resources;

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
        'page' => Resources\Contents\PageResource::class,
        'document_type' => Resources\Settings\DocumentTypeResource::class,
        'field_group' => Resources\Settings\FieldGroupResource::class,
        'user' => Resources\Users\UserResource::class,
    ],

    'models' => [
        'table_name_prefix' => 'cms_',
        'morph_map_prefix' => 'cms_',
    ],
];
