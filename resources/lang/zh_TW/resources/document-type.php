<?php

return [
    'empty_state' => [
        'heading' => '沒有文件類型',
        'description' => '創建一個文件類型以開始。',
    ],
    'title' => [
        'label' => '標題',
        'validation_attribute' => '標題',
    ],
    'category' => [
        'label' => '類別',
        'validation_attribute' => '類別',
    ],
    'show_as_table' => [
        'label' => '顯示為表格',
        'validation_attribute' => '顯示為表格',
    ],
    'icon' => [
        'label' => '圖標',
        'validation_attribute' => '圖標',
    ],
    'slug' => [
        'label' => '別名',
        'validation_attribute' => '別名',
    ],
    'inherited_from' => [
        'label' => '繼承自',
    ],
    'templates' => [
        'label' => '模板',
        'validation_attribute' => '模板',
        'description' => '渲染此文件類型時使用的模板。',
        'hint' => '創建一個模板來顯示此文件類型。',
        'tab' => [
            'label' => '模板',
        ],
    ],
    'field_groups' => [
        'label' => '字段',
        'singular' => '字段',
        'plural' => '字段',
        'description' => '',
        'hint' => '創建一個字段組以用於此文件類型。',
        'tab' => [
            'label' => '結構',
        ],
    ],
    'inherited' => [
        'label' => '繼承自 :name',
        'description' => '此文件類型繼承的文件類型。',
    ],
    'inheriting' => [
        'label' => '繼承至 :name',
        'description' => '繼承此文件類型的文件類型。',
    ],
    'rejected_document_types' => [
        'label' => '被拒絕的文件類型',
        'validation_attribute' => '被拒絕的文件類型',
    ],

    'categories' => [
        'web' => [
            'label' => '網頁',
            'description' => '標準的網頁佈局。',
        ],
        'inheritance' => [
            'label' => '繼承',
            'description' => '可以繼承的文件類型佈局。',
        ],
    ],

    'presentation' => [
        'tab' => [
            'label' => '演示',
        ],
    ],
    'structure' => [
        'tab' => [
            'label' => '結構',
        ],
    ],

    'general' => [
        'section' => [
            'heading' => '一般',
        ],
    ],
    'rejected' => [
        'section' => [
            'heading' => '被拒絕的文件類型',
        ],
    ],
    'rejecting' => [
        'label' => '拒絕的文件類型',
        'description' => '拒絕此文件類型的文件類型。',
    ],
];
