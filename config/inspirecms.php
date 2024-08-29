<?php

use SolutionForest\InspireCms\Filament\Resources;
use SolutionForest\InspireCms\Models;

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
        'content' => [
            'fqcn' => Models\CmsContent::class,
            'table_name' => 'cms_contents',
            'polymorphic_type' => 'cms_content',
        ],
        'component_field_group' => [
            'fqcn' => Models\Polymorphic\CmsComponentFieldGroup::class,
            'table_name' => 'cms_component_field_groups',
            'polymorphic_type' => 'cms_component_field_group',
        ],
        'property_data' => [
            'fqcn' => Models\CmsPropertyData::class,
            'table_name' => 'cms_property_datas',
            'polymorphic_type' => 'cms_property_data',
        ],
        'component_tree' => [
            'fqcn' => Models\Polymorphic\CmsComponentTree::class,
            'table_name' => 'cms_component_trees',
            'polymorphic_type' => 'cms_component_tree',
        ],
        'content_version' => [
            'fqcn' => Models\CmsContentVersion::class,
            'table_name' => 'cms_content_versions',
            'polymorphic_type' => 'cms_content_version',
        ],
        'document_type' => [
            'fqcn' => Models\CmsDocumentType::class,
            'table_name' => 'cms_document_types',
            'polymorphic_type' => 'cms_document_type',
        ],
        'user' => [
            'fqcn' => Models\CmsUser::class,
            'table_name' => 'cms_users',
            'polymorphic_type' => 'cms_user',
        ],
        'user_login_activity' => [
            'fqcn' => Models\Users\CmsUserLoginActivity::class,
            'table_name' => 'cms_user_login_activities',
            'polymorphic_type' => 'cms_user_login_activity',
        ],
        'language' => [
            'fqcn' => Models\CmsLanauage::class,
            'table_name' => 'cms_languages',
            'polymorphic_type' => 'cms_language',
        ],
    ],
];
