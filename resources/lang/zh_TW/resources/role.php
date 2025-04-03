<?php

return [
    'name' => [
        'label' => '名稱',
        'validation_attribute' => '名稱',
    ],
    'permissions' => [
        'label' => '權限',
        'validation_attribute' => '權限',
    ],

    'cluster_section_access' => [
        'validation_attribute' => '允許叢集',
        'label' => '允許叢集',
    ],
    'action_permissions' => [
        'validation_attribute' => '操作權限',
    ],
    'page_permissions' => [
        'validation_attribute' => '頁面權限',
    ],
    'resource_permissions' => [
        'validation_attribute' => '資源權限',
    ],
    'widget_permissions' => [
        'validation_attribute' => '資源權限',
    ],
    'tiered_permissions' => [
        'validation_attribute' => '分層權限',
        'steps' => [
            'target' => [
                'label' => '目標',
                'validation_attribute' => '目標',
            ],
            'access_control' => [
                'label' => '訪問控制',
                'validation_attribute' => '訪問控制',
            ],
        ],
    ],

    'sections' => [
        'cluster_section_access' => [
            'heading' => '叢集',
            'description' => '選擇此角色有權訪問的叢集。',
        ],
        'action_permissions' => [
            'heading' => '操作權限',
            'description' => '選擇此角色對每個操作的權限。',
        ],
        'page_permissions' => [
            'heading' => '頁面權限',
            'description' => '選擇此角色對每個頁面的權限。',
        ],
        'resource_permissions' => [
            'heading' => '資源權限',
            'description' => '選擇此角色對每個資源的權限。',
        ],
        'widget_permissions' => [
            'heading' => '小工具權限',
            'description' => '選擇此角色對每個小工具的權限。',
        ],
        'tiered_permissions' => [
            'heading' => '分層權限',
            'description' => '選擇此角色對每個層級的權限。',
        ],
    ],
];
