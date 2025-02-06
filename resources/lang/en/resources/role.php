<?php

return [
    'name' => [
        'label' => 'Name',
        'validation_attribute' => 'name',
    ],
    'cluster_section_access' => [
        'label' => 'Allow Clusters',
        'section' => [
            'heading' => 'Cluster',
            'description' => 'Select the cluster that this role has access to.',
        ],
    ],
    'action_permissions' => [
        'section' => [
            'heading' => 'Actions Permissions',
            'description' => 'Select the permissions that this role has for each action.',
        ],
    ],
    'page_permissions' => [
        'section' => [
            'heading' => 'Page Permissions',
            'description' => 'Select the permissions that this role has for each page.',
        ],
    ],
    'resource_permissions' => [
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
        'section' => [
            'heading' => 'Tiered Permissions',
            'description' => 'Select the permissions that this role has for each tier.',
        ],
    ],
];
