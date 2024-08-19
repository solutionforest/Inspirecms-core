<?php

use SolutionForest\InspireCms\Models;

// config for SolutionForest/InspireCms
return [
    'template' => [
        'path' => env('INSPIRECMS_TEMPLATE_PATH', resource_path('views/inspire-cms/templates')),
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
