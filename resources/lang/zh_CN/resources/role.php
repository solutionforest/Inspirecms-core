<?php

return [
    'name' => [
        'label' => '名称',
        'validation_attribute' => '名称',
    ],
    'permissions' => [
        'label' => '权限',
        'validation_attribute' => '权限',
    ],

    'cluster_section_access' => [
        'validation_attribute' => '允许集群',
        'label' => '允许集群',
    ],
    'action_permissions' => [
        'validation_attribute' => '操作权限',
    ],
    'page_permissions' => [
        'validation_attribute' => '页面权限',
    ],
    'resource_permissions' => [
        'validation_attribute' => '资源权限',
    ],
    'widget_permissions' => [
        'validation_attribute' => '资源权限',
    ],
    'tiered_permissions' => [
        'validation_attribute' => '分层权限',
        'steps' => [
            'target' => [
                'label' => '目标',
                'validation_attribute' => '目标',
            ],
            'access_control' => [
                'label' => '访问控制',
                'validation_attribute' => '访问控制',
            ],
        ],
    ],

    'sections' => [
        'cluster_section_access' => [
            'heading' => '集群',
            'description' => '选择此角色有权访问的集群。',
        ],
        'action_permissions' => [
            'heading' => '操作权限',
            'description' => '选择此角色对每个操作的权限。',
        ],
        'page_permissions' => [
            'heading' => '页面权限',
            'description' => '选择此角色对每个页面的权限。',
        ],
        'resource_permissions' => [
            'heading' => '资源权限',
            'description' => '选择此角色对每个资源的权限。',
        ],
        'widget_permissions' => [
            'heading' => '小工具权限',
            'description' => '选择此角色对每个小工具的权限。',
        ],
        'tiered_permissions' => [
            'heading' => '分层权限',
            'description' => '选择此角色对每个层级的权限。',
        ],
    ],
];
