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
];
