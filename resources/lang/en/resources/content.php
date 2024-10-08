<?php

return [
    'title' => [
        'label' => 'Title',
        'placeholder' => 'Enter title',
        'instructions' => 'Enter the title of the content',
    ],
    'slug' => [
        'label' => 'Slug',
        'placeholder' => 'Enter slug',
        'instructions' => 'Enter the slug of the content',
    ],
    'seo' => [
        'heading' => 'SEO',
        'meta_title' => [
            'label' => 'Meta Title',
            'placeholder' => 'Enter meta title',
            'instructions' => 'Enter the meta title of the content',
        ],
        'meta_description' => [
            'label' => 'Meta Description',
            'placeholder' => 'Enter meta description',
            'instructions' => 'Enter the meta description of the content',
        ],
        'meta_keywords' => [
            'label' => 'Meta Keywords',
            'placeholder' => 'Enter meta keywords',
            'instructions' => 'Enter the meta keywords of the content',
        ],
        'og' => [
            'heading' => 'Open Graph',
            'og_title' => [
                'label' => 'Open Graph Title',
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
                'placeholder' => 'Enter open graph image',
                'instructions' => 'Enter the open graph image of the content',
            ],
        ],
        'robots' => [
            'heading' => 'Robots',
            'instructions' => 'Configure the robots meta tag',
            'noindex' => [
                'label' => 'No Index',
                'instructions' => 'Prevent search engines from indexing this content',
            ],
            'nofollow' => [
                'label' => 'No Follow',
                'instructions' => 'Prevent search engines from following links on this content',
            ],
        ],
    ],
    'redirect' => [
        'heading' => 'Redirect',
        'redirect_path' => [
            'label' => 'Redirect Path',
            'placeholder' => 'Enter redirect path',
            'instructions' => 'Enter the redirect path of the content',
        ],
        'redirect_content' => [
            'label' => 'Redirect Content',
            'placeholder' => 'Select redirect content',
            'instructions' => 'Select the content to redirect to',
        ],
        'redirect_type' => [
            'label' => 'Redirect Type',
            'placeholder' => 'Select redirect type',
            'instructions' => 'Select the type of redirect',
            '301' => '301 Permanent',
            '302' => '302 Temporary',
        ],
    ],
    'sitemap' => [
        'heading' => 'Site Map',
        'enable' => [
            'label' => 'Enable',
            'instructions' => 'Enable the content to be included in the site map',
        ],
        'priority' => [
            'label' => 'Priority',
            'placeholder' => 'Enter priority',
            'instructions' => '
                <p><i>The priority should be a value between 0.0 and 1.0. The default priority is 0.5. </i></p>
                <p><b>1.0 is the highest priority and 0.0 is the lowest priority.</b></p>
                <p>The priority of a page is used by search engines to determine the importance of the page relative to other pages on the site.</p>
            ',
        ],
        'change_frequency' => [
            'label' => 'Change Frequency',
            'placeholder' => 'Select change frequency',
            'instructions' => 'Select the change frequency of the content',
        ],
    ],
];
