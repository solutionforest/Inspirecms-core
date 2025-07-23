<?php

return [
    'is_default' => [
        'label' => __('inspirecms::inspirecms.is_default'),
        'validation_attribute' => strtolower(__('inspirecms::inspirecms.default')),
    ],
    'slug' => [
        'label' => 'Slug',
        'validation_attribute' => 'slug',
    ],
    'path' => [
        'label' => 'Title',
    ],
    'content' => [
        'label' => 'Content',
    ],
    'property_type_instructions' => [
        'label' => 'Property instructions',
        'group' => 'Group',
        'field' => __('inspirecms::inspirecms.field.singular'),
    ],
    'page_component_instructions' => [
        'label' => 'Page component instructions',
    ],
    'theme' => [
        'label' => __('inspirecms::inspirecms.theme'),
    ],
    'source_theme' => [
        'label' => 'Source Theme',
    ],
    'editor' => [
        'title' => 'Template Editor',
        'tabs' => [
            'content' => 'Content',
            'instructions' => 'Instructions',
        ],
    ],
];
