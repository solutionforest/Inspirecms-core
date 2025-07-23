<?php

return [
    'empty_state' => [
        'heading' => '沒有' . lcfirst(__('inspirecms::inspirecms.document_type.plural')),
        'description' => '建立一個' . lcfirst(__('inspirecms::inspirecms.document_type.singular')) . '以開始。',
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
        'label' => '圖示',
        'validation_attribute' => '圖示',
    ],
    'slug' => [
        'label' => 'Slug',
        'validation_attribute' => 'slug',
    ],
    'inherited_from' => [
        'label' => '繼承自',
    ],

    'templates' => [
        'label' => __('inspirecms::inspirecms.template.plural'),
        'validation_attribute' => lcfirst(__('inspirecms::inspirecms.template.plural')),
        'description' => str_replace([':dt', ':t'], [lcfirst(__('inspirecms::inspirecms.document_type.singular')), lcfirst(__('inspirecms::inspirecms.template.singular'))], '渲染此:dt時使用的:t'),
        'hint' => str_replace([':dt', ':t'], [lcfirst(__('inspirecms::inspirecms.document_type.singular')), lcfirst(__('inspirecms::inspirecms.template.singular'))], '建立一個:t來顯示此:dt'),
    ],

    'field_groups' => [
        'label' => __('inspirecms::inspirecms.field.plural'),
        'singular' => __('inspirecms::inspirecms.field.singular'),
        'plural' => __('inspirecms::inspirecms.field.plural'),
        'description' => '',
        'hint' => str_replace([':dt', ':fg'], [lcfirst(__('inspirecms::inspirecms.document_type.singular')), lcfirst(__('inspirecms::inspirecms.field_group.singular'))], '建立一個:fg以用於此:dt'),
    ],

    'inherited' => [
        'label' => '繼承自 :name',
        'description' => str_replace([':dts', ':dt'], [lcfirst(__('inspirecms::inspirecms.document_type.plural')), lcfirst(__('inspirecms::inspirecms.document_type.singular'))], '此:dts繼承的:dt'),
    ],
    'inheriting' => [
        'label' => '繼承至 :name',
        'description' => str_replace([':dts', ':dt'], [lcfirst(__('inspirecms::inspirecms.document_type.plural')), lcfirst(__('inspirecms::inspirecms.document_type.singular'))], '繼承此:dt的:dts'),
    ],

    'allowed_document_types' => [
        'label' => '允許的' . lcfirst(__('inspirecms::inspirecms.document_type.singular')),
        'description' => str_replace([':dts'], [lcfirst(__('inspirecms::inspirecms.document_type.plural'))], '作為子項允許的:dts'),
    ],
    'allowing_document_types' => [
        'label' => '允許的' . lcfirst(__('inspirecms::inspirecms.document_type.singular')),
        'description' => str_replace([':dts', ':dt'], [lcfirst(__('inspirecms::inspirecms.document_type.plural')), lcfirst(__('inspirecms::inspirecms.document_type.singular'))], '允許此:dt的:dts'),
    ],

    'categories' => [
        'web' => [
            'label' => '網頁',
            'description' => '標準的網頁佈局。',
        ],
        'data' => [
            'label' => '資料',
            'description' => '不帶路由的資料佈局。',
        ],
        'inheritance' => [
            'label' => '繼承',
            'description' => '可以繼承的' . lcfirst(__('inspirecms::inspirecms.document_type.singular')) . '佈局。',
        ],
    ],

    'tabs' => [
        'presentation' => '展示',
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
            'description' => '設定在建立內容時如何顯示此' . lcfirst(__('inspirecms::inspirecms.document_type.singular')),
        ],
    ],
];
