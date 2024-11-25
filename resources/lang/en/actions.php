<?php

return [

    'clear' => [

        'label' => 'Clear',

    ],

    'clone' => [

        'label' => 'Clone',

        'modal' => [

            'heading' => 'Cloning <i>:label</i>',

            'description' => 'Are you sure you want to clone this item?',

            'actions' => [

                'clone' => [

                    'label' => 'Clone',

                ],
            ],
        ],

        'notifications' => [

            'cloned' => [

                'title' => 'Clone Successful',

            ],
        ],
    ],

    'create_content' => [

        'label' => 'Create content',

        'modal' => [

            'heading' => 'Create content under :title',

        ],

    ],

    'content_history' => [

        'label' => 'Content History',

        'permission_display_name' => 'View content history',

    ],

    'open' => [

        'label' => 'Open',

    ],

    'publish' => [

        'label' => 'Publish',

        'actions' => [
            'publish' => [
                'label' => 'Publish',
            ],
        ],

        'notifications' => [
            'published' => [
                'title' => 'Published Successful',
            ],
        ],
    ],

    'preview' => [

        'label' => 'Preview',

    ],

    'private' => [

        'label' => 'Set privately used',

        'actions' => [
            'private' => [
                'label' => 'Set privately used',
            ],
        ],

        'notifications' => [
            'updated' => [
                'title' => 'Private Setting Updated Successfully',
            ],
        ],
    ],

    'more_actions' => [

        'label' => 'More actions',

    ],

    'save' => [

        'label' => 'Save',

    ],

    'save_draft' => [

        'label' => 'Save draft',

    ],

    'select' => [

        'label' => 'Select',

    ],

    'unpublish' => [

        'label' => 'Unpublish',

        'actions' => [
            'unpublish' => [
                'label' => 'Unpublish',
            ],
        ],

        'notifications' => [

            'unpublished' => [

                'title' => 'Unpublished Successful',

            ],
        ],
    ],

    'trash' => [

        'label' => 'Trash',

    ],

    'reorder_content' => [

        'label' => 'Reorder content',

        'notifications' => [
            'invalid_model' => [
                'title' => 'Invalid Model',
            ],
            'success' => [
                'title' => 'Reorder content successful',
            ],
            'error' => [
                'title' => 'Reorder content error',
            ],
        ],

        'permission_display_name' => 'Reorder content',

    ],

    'set_default_content_page' => [

        'label' => 'Set default content page',

        'permission_display_name' => 'Set default content page',

        'notifications' => [

            'success' => [

                'title' => 'Set default content page successful',

            ],
        ],
        
    ],
];
