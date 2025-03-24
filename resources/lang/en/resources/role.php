<?php

return [
    'name' => [
        'label' => 'Name',
        'validation_attribute' => 'name',
    ],
    'permissions' => [
        'label' => 'Permissions',
        'validation_attribute' => 'permissions',
    ],

    'cluster_section_access' => [
        'validation_attribute' => 'allow clusters',
        'label' => 'Allow Clusters',
    ],
    'action_permissions' => [
        'validation_attribute' => 'action permissions',
    ],
    'page_permissions' => [
        'validation_attribute' => 'page permissions',
    ],
    'resource_permissions' => [
        'validation_attribute' => 'resource permissions',
    ],
    'widget_permissions' => [
        'validation_attribute' => 'widget permissions',
    ],
    'tiered_permissions' => [
        'validation_attribute' => 'tiered permissions',
        'steps' => [
            'target' => [
                'label' => 'Target',
                'validation_attribute' => 'target',
            ],
            'access_control' => [
                'label' => 'Access Control',
                'validation_attribute' => 'access control',
            ],
        ],
    ],

    'sections' => [
        'cluster_section_access' => [
            'heading' => 'Cluster',
            'description' => 'Select the cluster that this role has access to.',
        ],
        'action_permissions' => [
            'heading' => 'Actions Permissions',
            'description' => 'Select the permissions that this role has for each action.',
        ],
        'page_permissions' => [
            'heading' => 'Page Permissions',
            'description' => 'Select the permissions that this role has for each page.',
        ],
        'resource_permissions' => [
            'heading' => 'Resource Permissions',
            'description' => 'Select the permissions that this role has for each resource.',
        ],
        'widget_permissions' => [
            'heading' => 'Widget Permissions',
            'description' => 'Select the permissions that this role has for each widget.',
        ],
        'tiered_permissions' => [
            'heading' => 'Tiered Permissions',
            'description' => 'Select the permissions that this role has for each tier.',
        ],
    ],
];
