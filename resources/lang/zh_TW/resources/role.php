<?php

return [
    'name' => [
        'label' => '名稱',
        'validation_attribute' => '名稱',
    ],
    'cluster_section_access' => [
        'label' => '允許叢集',
        'section' => [
            'heading' => '叢集',
            'description' => '選擇此角色有權訪問的叢集。',
        ],
    ],
    'action_permissions' => [
        'section' => [
            'heading' => '操作權限',
            'description' => '選擇此角色對每個操作的權限。',
        ],
    ],
    'page_permissions' => [
        'section' => [
            'heading' => '頁面權限',
            'description' => '選擇此角色對每個頁面的權限。',
        ],
    ],
    'resource_permissions' => [
        'section' => [
            'heading' => '資源權限',
            'description' => '選擇此角色對每個資源的權限。',
        ],
    ],
    'widget_permissions' => [
        'validation_attribute' => '小工具權限',
        'section' => [
            'heading' => '小工具權限',
            'description' => '選擇此角色對每個小工具的權限。',
        ],
    ],
    'tiered_permissions' => [
        'section' => [
            'heading' => '分層權限',
            'description' => '選擇此角色對每個層級的權限。',
        ],
    ],
];
