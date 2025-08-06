<?php

return [
    'empty_state' => [
        'heading' => '没有' . strtolower(__('inspirecms::inspirecms.document_type.plural')),
        'description' => '创建一个' . strtolower(__('inspirecms::inspirecms.document_type.singular')) . '以开始。',
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
        'label' => __('inspirecms::inspirecms.template.plural'),
        'validation_attribute' => strtolower(__('inspirecms::inspirecms.template.plural')),
        'description' => str_replace([':dt', ':t'], [strtolower(__('inspirecms::inspirecms.document_type.singular')), strtolower(__('inspirecms::inspirecms.template.singular'))], '渲染此:dt时使用的:t'),
        'hint' => str_replace([':dt', ':t'], [strtolower(__('inspirecms::inspirecms.document_type.singular')), strtolower(__('inspirecms::inspirecms.template.singular'))], '创建一个:t来显示此:dt'),
    ],

    'field_groups' => [
        'label' => __('inspirecms::inspirecms.field.plural'),
        'singular' => __('inspirecms::inspirecms.field.singular'),
        'plural' => __('inspirecms::inspirecms.field.plural'),
        'description' => '',
        'hint' => str_replace([':dt', ':fg'], [strtolower(__('inspirecms::inspirecms.document_type.singular')), strtolower(__('inspirecms::inspirecms.field_group.singular'))], '创建一个:fg以用于此:dt'),
    ],

    'inherited' => [
        'label' => '继承自 :name',
        'description' => str_replace([':dts', ':dt'], [strtolower(__('inspirecms::inspirecms.document_type.plural')), strtolower(__('inspirecms::inspirecms.document_type.singular'))], '此:dts继承的:dt'),
    ],
    'inheriting' => [
        'label' => '继承至 :name',
        'description' => str_replace([':dts', ':dt'], [strtolower(__('inspirecms::inspirecms.document_type.plural')), strtolower(__('inspirecms::inspirecms.document_type.singular'))], '继承此:dt的:dts'),
    ],

    'allowed_document_types' => [
        'label' => '允许的' . strtolower(__('inspirecms::inspirecms.document_type.singular')),
        'description' => str_replace([':dts'], [strtolower(__('inspirecms::inspirecms.document_type.plural'))], '作为子项允许的:dts'),
    ],
    'allowing_document_types' => [
        'label' => '允许的' . strtolower(__('inspirecms::inspirecms.document_type.singular')),
        'description' => str_replace([':dts', ':dt'], [strtolower(__('inspirecms::inspirecms.document_type.plural')), strtolower(__('inspirecms::inspirecms.document_type.singular'))], '允许此:dt的:dts'),
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
            'description' => '可以继承的' . strtolower(__('inspirecms::inspirecms.document_type.singular')) . '布局。',
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
            'description' => '设定在创建内容时如何显示此' . strtolower(__('inspirecms::inspirecms.document_type.singular')),
        ],
    ],
];
