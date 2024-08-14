<?php

use SolutionForest\InspireCms\Filament\Resources;
use SolutionForest\InspireCms\Models;

// config for SolutionForest/InspireCms
return [
    'resources' => [
        'field_group' => Resources\Settings\FieldGroupResource::class,
        'page' => Resources\Contents\PageResource::class,
        'page_type' => Resources\Settings\PageTypeResource::class,
    ],
    'models' => [
        'page' => [
            'fqcn' => Models\CmsPage::class,
            'table_name' => 'cms_pages',
        ],
        'page_type' => [
            'fqcn' => Models\CmsPageType::class,
            'table_name' => 'cms_page_types',
        ],
        'component_version' => [
            'fqcn' => Models\Polymorphic\CmsComponentVersion::class,
            'table_name' => 'cms_component_versions',
        ],
        'model_has_field_groups' => [
            'fqcn' => Models\Polymorphic\ModelHasFieldGroup::class,
            'table_name' => 'cms_model_has_field_groups',
        ],
    ],
];
