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
        'label' => '标题',
    ],
    'content' => [
        'label' => '内容',
    ],
    'property_type_instructions' => [
        'label' => 'Property 说明',
        'group' => '群组',
        'field' => __('inspirecms::inspirecms.field.singular'),
    ],
    'page_component_instructions' => [
        'label' => 'Page component 说明',
    ],
    'theme' => [
        'label' => __('inspirecms::inspirecms.theme'),
    ],
    'source_theme' => [
        'label' => '源主题',
    ],
    'editor' => [
        'title' => '模板编辑器',
        'tabs' => [
            'content' => '内容',
            'instructions' => '说明',
        ],
    ],
];
