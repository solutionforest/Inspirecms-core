<?php

return [
    'title' => [
        'label' => 'Title',
    ],
    'type' => [
        'label' => 'Type',
    ],
    'show_children_as_table' => [
        'label' => 'Show children as table',
    ],
    'is_root' => [
        'label' => 'Is Root',
    ],
    'slug' => [
        'label' => 'Slug',
    ],
    'inherited_from' => [
        'label' => 'Inherited from',
    ],
    'templates' => [
        'label' => 'Templates',
        'singular' => 'Template',
        'plural' => 'Templates',
        'description' => 'The template to use when rendering this document type.',
        'hint' => 'Create a template to display this document type.',
    ],
    'field_groups' => [
        'label' => 'Field Groups',
        'singular' => 'Field Group',
        'plural' => 'Field Groups',
        'description' => 'The field groups to use when creating a document of this type.',
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
];