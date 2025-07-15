<?php

return [

    'avoid_to_clean' => [
        'label' => 'Avoid cleanup',
        'instructions' => 'If enabled, the content versions will not be cleaned up.',
    ],
    'publish_state' => [
        'label' => 'Publish State',
    ],

    'empty_state' => [
        'heading' => 'No versions found',
        'description' => 'There are no versions available for this content.',
    ],

    'tables' => [
        'search_placeholder' => 'Search by auditor\'s name ...',
    ],

    'content_history_detail' => [
        'general_info' => 'General Info',
        'property_data' => 'Property Data',
        'empty_state' => 'No differences found',
    ],

    'buttons' => [
        'view_differences' => [
            'label' => 'View Differences',
            'heading' => 'Content Version Differences',
            'description' => 'By :author on :date',
        ],
        'bulk_update_state' => [
            'label' => 'Bulk update state',
            'heading' => 'Update records\' state',
            'messages' => [
                'success' => [
                    'title' => 'State updated successfully.',
                ],
                'failure' => [
                    'title' => 'Failed to update state.',
                ],
            ],
        ],
        'toggle_avoid_to_clean' => [
            'true_label' => 'Allow cleanup',
            'false_label' => 'Avoid cleanup',
            'messages' => [
                'wait_to_cleanup' => [
                    'title' => 'Now waiting to cleanup.',
                    'body' => 'This version will be cleaned up in the next cleanup cycle.',
                ],
                'avoid_cleanup' => [
                    'title' => 'Now avoiding cleanup.',
                    'body' => 'This version will not be cleaned up in the next cleanup cycle.',
                ],
            ],
        ],
    ],
];
