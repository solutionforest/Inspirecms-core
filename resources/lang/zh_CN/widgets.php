<?php

return [

    'cms_info' => [
        'permission_display_name' => '查看 CMS 信息',
    ],

    'cms_version_info' => [
        'permission_display_name' => '查看 CMS 版本信息',
    ],

    'content_page_overview' => [
        'default_page' => [
            'title' => '预设页面',
            'description' => '这是网站的预设页面。',
        ],
        'create_content' => [
            'title' => '创建 ' . __('inspirecms::inspirecms.content.singular'),
            'description' => '创建一个新的内容页面。',
            'message' => '使用此部分来为您的内容管理系统创建新内容。这使您能够为您的网站添加新鲜且相关的信息，保持其最新且吸引观众。',
        ],
        'create_document_type' => [
            'title' => '创建 ' . __('inspirecms::inspirecms.document_type.singular'),
            'description' => '创建一个新的 ' . lcfirst(__('inspirecms::inspirecms.document_type.singular')),
            'message' => '使用此部分来为您的内容管理系统创建新的' . __('inspirecms::inspirecms.document_type.plural') . '。这使您能够对内容进行分类和组织，使其更易于管理和搜索特定信息。',
        ],
    ],

    'page_activity' => [
        'title' => '活动',
        'empty_state' => [
            'heading' => '没有活动',
        ],
    ],

    'template_info' => [
        'title' => '模板信息',
        'permission_display_name' => '查看模板信息',
    ],

    'theme_info' => [
        'title' => '主题信息',
        'permission_display_name' => '查看主题信息',
    ],

    'user_activity' => [
        'title' => '用户活动',
        'empty_state' => [
            'heading' => '没有最近的活动。',
        ],
        'permission_display_name' => '查看用户活动',
    ],

];
