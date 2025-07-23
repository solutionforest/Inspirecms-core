<?php

return [
    'empty_state' => [
        'heading' => '没有文件类型',
        'description' => '创建一个文件类型以开始。',
    ],
    'title' => [
        'label' => '标题',
        'validation_attribute' => '标题',
    ],
    'category' => [
        'label' => '类别',
        'validation_attribute' => '类别',
    ],
    'show_at_root' => [
        'label' => '显示在根目录',
        'validation_attribute' => '显示在根目录',
    ],
    'show_as_table' => [
        'label' => '显示为表格',
        'validation_attribute' => '显示为表格',
    ],
    'icon' => [
        'label' => '图标',
        'validation_attribute' => '图标',
    ],
    'slug' => [
        'label' => 'Slug',
        'validation_attribute' => 'slug',
    ],
    'inherited_from' => [
        'label' => '继承自',
    ],

    'templates' => [
        'label' => '模板',
        'validation_attribute' => '模板',
        'description' => '渲染此文件类型时使用的模板。',
        'hint' => '创建一个模板来显示此文件类型。',
    ],

    'field_groups' => [
        'label' => '字段',
        'singular' => '字段',
        'plural' => '字段',
        'description' => '',
        'hint' => '创建一个字段组以用于此文件类型。',
    ],

    'inherited' => [
        'label' => '继承自 :name',
        'description' => '此文件类型继承的文件类型。',
    ],
    'inheriting' => [
        'label' => '继承至 :name',
        'description' => '继承此文件类型的文件类型。',
    ],

    'allowed_document_types' => [
        'label' => '允许的文件类型',
        'description' => '作为子项允许的文件类型。',
    ],
    'allowing_document_types' => [
        'label' => '允许的文件类型',
        'description' => '允许此文件类型的文件类型。',
    ],

    'categories' => [
        'web' => [
            'label' => '网页',
            'description' => '标准的网页布局。',
        ],
        'data' => [
            'label' => '数据',
            'description' => '不带路由的数据布局。',
        ],
        'inheritance' => [
            'label' => '继承',
            'description' => '可以继承的文件类型布局。',
        ],
    ],

    'tabs' => [
        'presentation' => '演示',
        'structure' => '结构',
        'field_groups' => '结构',
        'templates' => '模板',
    ],

    'sections' => [
        'general' => [
            'heading' => '一般',
        ],
        'display' => [
            'heading' => '显示',
            'description' => '设定在创建内容时如何显示此文件类型。',
        ],
    ],
];
