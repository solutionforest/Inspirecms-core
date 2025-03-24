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
    'show_at_root' => [
        'label' => '顯示在根目錄',
        'validation_attribute' => '顯示在根目錄',
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
        'label' => 'Slug',
        'validation_attribute' => 'slug',
    ],
    'inherited_from' => [
        'label' => '繼承自',
    ],
    
    'templates' => [
        'label' => '模板',
        'validation_attribute' => '模板',
        'description' => '渲染此文件類型時使用的模板。',
        'hint' => '創建一個模板來顯示此文件類型。',
    ],

    'field_groups' => [
        'label' => '字段',
        'singular' => '字段',
        'plural' => '字段',
        'description' => '',
        'hint' => '創建一個字段組以用於此文件類型。',
    ],

    'inherited' => [
        'label' => '繼承自 :name',
        'description' => '此文件類型繼承的文件類型。',
    ],
    'inheriting' => [
        'label' => '繼承至 :name',
        'description' => '繼承此文件類型的文件類型。',
    ],

    'allowed_document_types' => [
        'label' => '允許的文件類型',
        'description' => '作為子項允許的文件類型。',
    ],
    'allowing_document_types' => [
        'label' => '允許的文件類型',
        'description' => '允許此文件類型的文件類型。',
    ],

    'categories' => [
        'web' => [
            'label' => '網頁',
            'description' => '標準的網頁佈局。',
        ],
        'data' => [
            'label' => '數據',
            'description' => '不帶路由的數據佈局。',
        ],
        'inheritance' => [
            'label' => '繼承',
            'description' => '可以繼承的文件類型佈局。',
        ],
    ],

    'tabs' => [
        'presentation' => '演示',
        'structure' => '結構',
        'field_groups' => '結構',
        'templates' => '模板',
    ],

    'sections' => [
        'general' => [
            'heading' => '一般',
        ],
        'display' => [
            'heading' => '顯示',
            'description' => '設定在創建內容時如何顯示此文件類型。',
        ],
    ],
];
