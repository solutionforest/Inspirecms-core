<?php

return [
    'is_default' => [
        'label' => __('inspirecms::inspirecms.default'),
        'validation_attribute' => strtolower(__('inspirecms::inspirecms.default')),
    ],
    'slug' => [
        'label' => 'Slug',
        'validation_attribute' => 'slug',
    ],
    'path' => [
        'label' => '標題',
    ],
    'content' => [
        'label' => '內容',
    ],
    'property_type_instructions' => [
        'label' => 'Property 說明',
        'group' => '群組',
        'field' => __('inspirecms::inspirecms.field.singular'),
    ],
    'page_component_instructions' => [
        'label' => 'Page component 說明',
    ],
    'theme' => [
        'label' => __('inspirecms::inspirecms.theme'),
    ],
    'source_theme' => [
        'label' => '源主題',
    ],
    'editor' => [
        'title' => '模板編輯器',
        'tabs' => [
            'content' => '內容',
            'instructions' => '說明',
        ],
    ],
];
