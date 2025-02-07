<?php

return [
    'name' => [
        'label' => 'Name',
        'validation_attribute' => 'name',
    ],
    'cluster_section_access' => [
        'validation_attribute' => 'allow clusters',
        'label' => 'Allow Clusters',
        'section' => [
            'heading' => 'Cluster',
            'description' => 'Select the cluster that this role has access to.',
        ],
    ],
    'action_permissions' => [
        'validation_attribute' => 'action permissions',
        'section' => [
            'heading' => 'Actions Permissions',
            'description' => 'Select the permissions that this role has for each action.',
        ],
    ],
    'page_permissions' => [
        'validation_attribute' => 'page permissions',
        'section' => [
            'heading' => 'Page Permissions',
            'description' => 'Select the permissions that this role has for each page.',
        ],
    ],
    'resource_permissions' => [
        'validation_attribute' => 'resource permissions',
        'section' => [
            'heading' => 'Resource Permissions',
            'description' => 'Select the permissions that this role has for each resource.',
        ],
    ],
    'widget_permissions' => [
        'validation_attribute' => 'widget permissions',
        'section' => [
            'heading' => 'Widget Permissions',
            'description' => 'Select the permissions that this role has for each widget.',
        ],
    ],
    'tiered_permissions' => [
        'validation_attribute' => 'tiered permissions',
        'section' => [
            'heading' => 'Tiered Permissions',
            'description' => 'Select the permissions that this role has for each tier.',
        ],
    ],
];
