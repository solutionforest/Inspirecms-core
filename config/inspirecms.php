<?php

use SolutionForest\InspireCms\Filament\Resources;
use SolutionForest\InspireCms\Models;

// config for SolutionForest/InspireCms
return [
    'resources' => [
        'field_group' => Resources\Settings\FieldGroupResource::class,
        'page' => Resources\Contents\PageResource::class,
        'document_type' => Resources\Settings\DocumentTypeResource::class,
    ],
    'models' => [
        'page' => [
            'fqcn' => Models\CmsPage::class,
            'table_name' => 'cms_pages',
            'polymorphic_type' => 'cms_page',
        ],
        'document_type' => [
            'fqcn' => Models\CmsDocumentType::class,
            'table_name' => 'cms_document_types',
            'polymorphic_type' => 'cms_document_type',
        ],
        'component_version' => [
            'fqcn' => Models\Polymorphic\CmsComponentVersion::class,
            'table_name' => 'cms_component_versions',
            'polymorphic_type' => 'cms_component_version',
        ],
        'component_field_group' => [
            'fqcn' => Models\Polymorphic\CmsComponentFieldGroup::class,
            'table_name' => 'cms_component_field_groups',
            'polymorphic_type' => 'cms_component_field_group',
        ],
        'component_tree' => [
            'fqcn' => Models\Polymorphic\CmsComponentTree::class,
            'table_name' => 'cms_component_trees',
            'polymorphic_type' => 'cms_component_tree',
        ],
    ],
];
