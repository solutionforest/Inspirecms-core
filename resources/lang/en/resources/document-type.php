<?php

return [
    'empty_state' => [
        'heading' => 'No ' . strtolower(__('inspirecms::inspirecms.document_type.plural')),
        'description' => 'Create a ' . strtolower(__('inspirecms::inspirecms.document_type.singular')) . ' to get started.',
    ],
    'title' => [
        'label' => 'Title',
        'validation_attribute' => 'title',
    ],
    'category' => [
        'label' => 'Category',
        'validation_attribute' => 'category',
    ],
    'show_at_root' => [
        'label' => 'Show at root',
        'validation_attribute' => 'show at root',
    ],
    'show_as_table' => [
        'label' => 'Show as table',
        'validation_attribute' => 'show as table',
    ],
    'icon' => [
        'label' => 'Icon',
        'validation_attribute' => 'icon',
    ],
    'slug' => [
        'label' => 'Slug',
        'validation_attribute' => 'slug',
    ],
    'inherited_from' => [
        'label' => 'Inherited from',
    ],

    'templates' => [
        'label' => __('inspirecms::inspirecms.template.plural'),
        'validation_attribute' => strtolower(__('inspirecms::inspirecms.template.plural')),
        'description' => str_replace([':dt', ':t'], [strtolower(__('inspirecms::inspirecms.document_type.singular')), strtolower(__('inspirecms::inspirecms.template.singular'))], 'The :t to use when rendering this :dt'),
        'hint' => str_replace([':dt', ':t'], [strtolower(__('inspirecms::inspirecms.document_type.singular')), strtolower(__('inspirecms::inspirecms.template.singular'))], 'Create a :t to display this :dt'),
    ],

    'field_groups' => [
        'label' => __('inspirecms::inspirecms.field.plural'),
        'singular' => __('inspirecms::inspirecms.field.singular'),
        'plural' => __('inspirecms::inspirecms.field.plural'),
        'description' => '',
        'hint' => str_replace([':dt', ':fg'], [strtolower(__('inspirecms::inspirecms.document_type.singular')), strtolower(__('inspirecms::inspirecms.field_group.singular'))], 'Create a :fg to use with this :dt'),
    ],

    'inherited' => [
        'label' => 'Inherited from :name',
        'description' => str_replace([':dts', ':dt'], [strtolower(__('inspirecms::inspirecms.document_type.plural')), strtolower(__('inspirecms::inspirecms.document_type.singular'))], 'The :dts that this :dt inherits from.'),
    ],
    'inheriting' => [
        'label' => 'Inheriting to :name',
        'description' => str_replace([':dts', ':dt'], [strtolower(__('inspirecms::inspirecms.document_type.plural')), strtolower(__('inspirecms::inspirecms.document_type.singular'))], 'The :dts that inherit from this :dt'),
    ],

    'allowed_document_types' => [
        'label' => 'Allowed ' . strtolower(__('inspirecms::inspirecms.document_type.plural')),
        'description' => str_replace([':dts'], [strtolower(__('inspirecms::inspirecms.document_type.plural'))], 'The :dts that are allowed as child items.'),
    ],
    'allowing_document_types' => [
        'label' => 'Allowing ' . strtolower(__('inspirecms::inspirecms.document_type.plural')),
        'description' => str_replace([':dts', ':dt'], [strtolower(__('inspirecms::inspirecms.document_type.plural')), strtolower(__('inspirecms::inspirecms.document_type.singular'))], 'The :dts that allow this :dt'),
    ],

    'categories' => [
        'web' => [
            'label' => 'Web page',
            'description' => 'A standard web page layout.',
        ],
        'data' => [
            'label' => 'Data',
            'description' => 'A data layout that without routing.',
        ],
        'inheritance' => [
            'label' => 'Inheritance',
            'description' => 'A ' . strtolower(__('inspirecms::inspirecms.document_type.singular')) . ' layout that can be inherited.',
        ],
    ],

    'tabs' => [
        'presentation' => 'Presentation',
        'structure' => 'Structure',
        'field_groups' => 'Structure',
        'templates' => 'Templates',
    ],

    'sections' => [
        'general' => [
            'heading' => 'General',
        ],
        'display' => [
            'heading' => 'Display',
            'description' => 'Settings that determine how this ' . strtolower(__('inspirecms::inspirecms.document_type.singular')) . ' is displayed when creating content.',
        ],
    ],
];
