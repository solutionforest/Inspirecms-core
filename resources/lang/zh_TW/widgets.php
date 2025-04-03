<?php

return [

    'cms_info' => [
        'permission_display_name' => '查看 CMS 資訊',
    ],

    'content_page_overview' => [
        'default_page' => [
            'title' => '預設頁面',
            'description' => '這是網站的預設頁面。',
        ],
        'create_content' => [
            'title' => '建立 ' . str(__('inspirecms::inspirecms.content'))->title()->toString(),
            'description' => '建立一個新的內容頁面。',
            'message' => '使用此部分來為您的內容管理系統創建新內容。這使您能夠為您的網站添加新鮮且相關的信息，保持其最新且吸引觀眾。',
        ],
        'create_document_type' => [
            'title' => '建立 ' . str(__('inspirecms::inspirecms.document_type'))->title()->toString(),
            'description' => '建立一個新的 ' . str(__('inspirecms::inspirecms.document_type'))->lower()->toString(),
            'message' => '使用此部分來為您的內容管理系統創建新的文檔類型。這使您能夠對內容進行分類和組織，使其更易於管理和搜索特定信息。',
        ],
    ],

    'page_activity' => [
        'title' => '活動',
        'empty_state' => [
            'heading' => '沒有活動',
        ],
    ],

    'template_info' => [
        'permission_display_name' => '查看模板資訊',
    ],

];
