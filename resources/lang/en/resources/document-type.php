<?php

return [
    'empty_state' => [
        'heading' => 'No document types',
        'description' => 'Create a document type to get started.',
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
        'label' => 'Templates',
        'validation_attribute' => 'templates',
        'description' => 'The template to use when rendering this document type.',
        'hint' => 'Create a template to display this document type.',
    ],

    'field_groups' => [
        'label' => 'Fields',
        'singular' => 'Field',
        'plural' => 'Fields',
        'description' => '',
        'hint' => 'Create a field group to use with this document type.',
    ],

    'inherited' => [
        'label' => 'Inherited from :name',
        'description' => 'The document types that this document type inherits from.',
    ],
    'inheriting' => [
        'label' => 'Inheriting to :name',
        'description' => 'The document types that inherit from this document type.',
    ],

    'allowed_document_types' => [
        'label' => 'Allowed document types',
        'description' => 'The document types that are allowed as child items.',
    ],
    'allowing_document_types' => [
        'label' => 'Allowing document types',
        'description' => 'The document types that allow this document type.',
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
            'description' => 'A document type layout that can be inherited.',
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
            'description' => 'Settings that determine how this document type is displayed when creating content.',
        ],
    ],
];
