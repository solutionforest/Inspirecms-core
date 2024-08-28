<?php

return [
    'add_xxx' => 'Add :name',
    'can_use_at_root' => 'Can use at root?',
    'content' => 'Content',
    'create_xxx' => 'Create :name',
    'created_at' => 'Created at',
    'dashboard' => 'Dashboard',
    'details' => 'Details',
    'document_type' => 'Document Type',
    'field_group' => 'Field Group',
    'general' => 'General',
    'id' => 'ID',
    'is_active' => 'Active?',
    'is_published' => 'Is Published',
    'is_root_level' => 'Is Root?',
    'last_published_at' => 'Last published at',
    'last_updated_at' => 'Last updated at',
    'n/a' => 'N/A',
    'name' => 'Name',
    'no_parent' => 'No parent',
    'page' => 'Page',
    'parent_xxx' => 'Parent :name',
    'parent' => 'Parent',
    'preview_fields' => 'Preview fields',
    'publish_at' => 'Publish at',
    'setting' => 'Setting',
    'slug' => 'Slug',
    'status' => 'Status',
    'template' => 'Template',
    'title' => 'Title',
    'total_xxx_have_used' => 'Total :name has been used',
    'total' => 'Total',
    'visibility' => 'Visibility',

    'page_status' => [
        'draft' => [
            'label' => 'Draft',
        ],
        'publish' => [
            'label' => 'Publish',
        ],
        'private' => [
            'label' => 'Private',
        ],
        'unpublish' => [
            'label' => 'Unpublish',
        ],
    ],

    'hints' => [
        'future_published_at_description' => 'Content will be published automatically when the specified date and time arrives.',
        'future_publish' => 'If set to a future date, this page will be published automatically when that time is reached.',
    ],

    'actions' => [

        'save_draft' => [

            'label' => 'Save draft',

        ],

        'save' => [

            'label' => 'Save',

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

        'quick_create' => [

            'label' => 'Quick create',

            'modal' => [

                'heading' => 'Quick create :label',

                'actions' => [
                    'create' => [
                        'label' => 'Create',
                    ],
                ],
            ],
        ],

        'quick_edit' => [

            'label' => 'Quick edit',

            'modal' => [

                'heading' => 'Quick edit :label',

                'actions' => [
                    'save' => [
                        'label' => 'Save',
                    ],
                ],
            ],
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
    ],

    'notification' => [
        'template_directory_not_found' => [
            'title' => 'Template Directory Not Found',
            'body' => 'The specified template directory does not exist. Please check your configuration.',
        ],
        'form_check_error' => [
            'title' => 'There seems to be an issue with your form. Please review the fields and try again.',
        ],
    ],
];
