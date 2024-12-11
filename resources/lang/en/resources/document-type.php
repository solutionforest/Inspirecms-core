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
        'tab' => [
            'label' => 'Templates',
        ],
    ],
    'field_groups' => [
        'label' => 'Fields',
        'singular' => 'Field',
        'plural' => 'Fields',
        'description' => '',
        'hint' => 'Create a field group to use with this document type.',
        'tab' => [
            'label' => 'Structure',
        ],
    ],
    'inherited' => [
        'label' => 'Inherited from :name',
        'description' => 'The document types that this document type inherits from.',
    ],
    'inheriting' => [
        'label' => 'Inheriting to :name',
        'description' => 'The document types that inherit from this document type.',
    ],
    'rejected_document_types' => [
        'label' => 'Rejected document types',
        'validation_attribute' => 'rejected document types',
    ],

    'categories' => [
        'web' => [
            'label' => 'Web page',
            'description' => 'A standard web page layout.',
        ],
        'inheritance' => [
            'label' => 'Inheritance',
            'description' => 'A document type layout that can be inherited.',
        ],
    ],

    'presentation' => [
        'tab' => [
            'label' => 'Presentation',
        ],
    ],
    'structure' => [
        'tab' => [
            'label' => 'Structure',
        ],
    ],

    'general' => [
        'section' => [
            'heading' => 'General',
        ],
    ],
    'rejected' => [
        'section' => [
            'heading' => 'Rejected document types',
        ],
    ],
    'rejecting' => [
        'label' => 'Rejecting document types',
        'description' => 'The document types that reject this document type.',
    ],
];
