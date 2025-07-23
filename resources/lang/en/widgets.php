<?php

return [

    'cms_info' => [
        'permission_display_name' => 'View CMS Info',
    ],

    'cms_version_info' => [
        'permission_display_name' => 'View CMS Version Info',
    ],

    'content_page_overview' => [
        'default_page' => [
            'title' => 'Default Page',
            'description' => 'This is the default page for the website.',
        ],
        'create_content' => [
            'title' => 'Create ' . __('inspirecms::inspirecms.content.singular'),
            'description' => 'Create a new content page.',
            'message' => 'Use this section to create new content for your content management system. This allows you to add fresh and relevant information to your site, keeping it up-to-date and engaging for your audience.',
        ],
        'create_document_type' => [
            'title' => 'Create ' . __('inspirecms::inspirecms.document_type.singular'),
            'description' => 'Create a new ' . lcfirst(__('inspirecms::inspirecms.document_type.singular')),
            'message' => 'Use this section to create new ' . lcfirst(__('inspirecms::inspirecms.document_type.plural')) . ' for your content management system. This allows you to categorize and organize your content, making it easier to manage and search for specific information.',
        ],
    ],

    'page_activity' => [
        'title' => 'Activity',
        'empty_state' => [
            'heading' => 'No activity',
        ],
    ],

    'template_info' => [
        'title' => 'Template Info',
        'permission_display_name' => 'View Template Info',
    ],

    'theme_info' => [
        'title' => 'Theme Info',
        'permission_display_name' => 'View Theme Info',
    ],

    'user_activity' => [
        'title' => 'User Activity',
        'empty_state' => [
            'heading' => 'No recent activity found.',
        ],
        'permission_display_name' => 'View User Activity',
    ],

];
