<?php

return [
    'title' => [
        'label' => 'Title',
        'validation_attribute' => 'title',
        'placeholder' => 'Enter title',
        'instructions' => 'Enter the title of the content',
    ],
    'slug' => [
        'label' => 'Slug',
        'validation_attribute' => 'slug',
        'placeholder' => 'Enter slug',
        'instructions' => 'Enter the slug of the content',
    ],
    'seo' => [
        'tab' => [
            'label' => 'SEO',
        ],
        'meta_title' => [
            'label' => 'Meta Title',
            'validation_attribute' => 'meta title',
            'placeholder' => 'Enter meta title',
            'instructions' => 'Enter the meta title of the content',
        ],
        'meta_description' => [
            'label' => 'Meta Description',
            'validation_attribute' => 'meta description',
            'placeholder' => 'Enter meta description',
            'instructions' => 'Enter the meta description of the content',
        ],
        'meta_keywords' => [
            'label' => 'Meta Keywords',
            'validation_attribute' => 'meta keywords',
            'placeholder' => 'Enter meta keywords',
            'instructions' => 'Enter the meta keywords of the content',
        ],
        'og_title' => [
            'label' => 'Open Graph Title',
            'validation_attribute' => 'open graph title',
            'placeholder' => 'Enter open graph title',
            'instructions' => 'Enter the open graph title of the content',
        ],
        'og_description' => [
            'label' => 'Open Graph Description',
            'placeholder' => 'Enter open graph description',
            'instructions' => 'Enter the open graph description of the content',
        ],
        'og_image' => [
            'label' => 'Open Graph Image',
            'instructions' => 'Enter the open graph image of the content',
        ],
    ],
    'robots' => [
        'section' => [
            'heading' => 'Robots',
        ],
        'noindex' => [
            'label' => 'No Index',
            'validation_attribute' => 'no index',
            'instructions' => 'Prevent search engines from indexing this content',
        ],
        'nofollow' => [
            'label' => 'No Follow',
            'validation_attribute' => 'no follow',
            'instructions' => 'Prevent search engines from following links on this content',
        ],
    ],
    'redirect' => [
        'section' => [
            'heading' => 'Redirect',
        ],
        'redirect_path' => [
            'label' => 'Redirect Path',
            'validation_attribute' => 'redirect path',
            'placeholder' => 'Enter redirect path',
            'instructions' => 'Enter the redirect path of the content',
        ],
        'redirect_content' => [
            'label' => 'Redirect Content',
            'validation_attribute' => 'redirect content',
            'placeholder' => 'Select redirect content',
            'instructions' => 'Select the content to redirect to',
        ],
        'redirect_type' => [
            'label' => 'Redirect Type',
            'validation_attribute' => 'redirect type',
            'placeholder' => 'Select redirect type',
            'instructions' => 'Select the type of redirect',
            '301' => '301 Permanent',
            '302' => '302 Temporary (default)',
        ],
    ],
    'sitemap' => [
        'tab' => [
            'label' => 'Site Map',
        ],
        'enable' => [
            'label' => 'Enable',
            'validation_attribute' => 'enable',
            'instructions' => 'Enable the content to be included in the site map',
        ],
        'priority' => [
            'label' => 'Priority',
            'validation_attribute' => 'priority',
            'placeholder' => 'Enter priority',
            'instructions' => '
                <p><i>The priority should be a value between 0.0 and 1.0. The default priority is 0.5. </i></p>
                <p><b>1.0 is the highest priority and 0.0 is the lowest priority.</b></p>
                <p>The priority of a page is used by search engines to determine the importance of the page relative to other pages on the site.</p>
            ',
        ],
        'change_frequency' => [
            'label' => 'Change Frequency',
            'validation_attribute' => 'change frequency',
            'placeholder' => 'Select change frequency',
            'instructions' => 'Select the change frequency of the content',
        ],
    ],
    'details' => [
        'tab' => [
            'label' => 'Details',
        ],
    ],
    'parent' => [
        'label' => 'Parent',
    ],
    'created_at' => [
        'label' => 'Created At',
    ],
    'updated_at' => [
        'label' => 'Last Updated At',
    ],
    'deleted_at' => [
        'label' => 'Deleted At',
    ],
    'visibility' => [
        'label' => 'Visibility',
    ],
    'is_published' => [
        'label' => 'Is Published',
    ],
    'status' => [
        'label' => 'Status',
    ],
    'is_root_level' => [
        'label' => 'Is Root Level',
    ],
    'published_at' => [
        'label' => 'Publish At',
        'hint' => 'If set to a future date, this page will be published automatically when that time is reached.',
    ],
    'latest_published_at' => [
        'label' => 'Latest Published At',
    ],
    'url' => [
        'label' => 'URL',
    ],
    'template' => [
        'label' => 'Template',
        'instructions' => 'Keep empty to use the default template of the document type',
    ],
    'document_type' => [
        'label' => 'Document Type',
        'validation_attribute' => 'document type',
    ],

    'notification' => [
        'remove_content_same_slug_in_same_parent' => [
            'title' => 'Remove Content',
            'body' => 'A content with the same slug already exists in the same parent. Please remove the existing content first.',
        ],
    ],

    'general' => [
        'section' => [
            'heading' => 'General',
        ],
    ],
    'content' => [
        'tab' => [
            'label' => 'Content',
        ],
    ],
    'seo_og' => [
        'section' => [
            'heading' => 'Open Graph',
        ],
    ],

    'actions' => [

        'preview' => [

            'label' => 'Preview',
    
        ],

        'more_actions' => [

            'label' => 'More actions',
    
        ],


        'publish' => [

            'label' => 'Publish',
    
            'modal' => [
    
                'heading' => 'Publish content',
                
                'actions' => [

                    'publish' => [

                        'label' => 'Publish',

                    ],

                ],
    
            ],
    
            'notification' => [

                'published' => [

                    'title' => 'Published Successful',

                ],
            ],
        ],

        'save_draft' => [
    
            'label' => 'Save draft',
    
        ],

        'trash_bin' => [
    
            'label' => 'Trash bin',
    
        ],

        'reorder_content' => [
    
            'label' => 'Reorder content',
    
            'notification' => [

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
    
            'label' => 'Set as default page',
    
            'permission_display_name' => 'Set default page',
    
            'notification' => [
    
                'success' => [
    
                    'title' => 'Default page updated.',
    
                ],
            ],
    
        ],

        'unpublish' => [
    
            'label' => 'Unpublish',
    
            'modal' => [
    
                'heading' => 'Unpublish content',

                'description' => '',

                'actions' => [
    
                    'unpublish' => [
    
                        'label' => 'Unpublish',
    
                    ],
                ],
    
            ],
    
            'notification' => [
    
                'unpublished' => [
    
                    'title' => 'Unpublished Successful',
    
                ],
            ],
        ],

        'create_content' => [
    
            'label' => 'Create content',
    
            'modal' => [
    
                'heading' => 'Create content under :title',
    
            ],
    
            'empty_state' => 'No document types available. Please create a document type first.',
    
        ],
    
        'content_history' => [
    
            'label' => 'Content History',
    
            'permission_display_name' => 'View content history',
    
        ],

        'back'=> [

            'label' => 'Back',

        ],
    ],
];
