<?php

return [
    'title' => [
        'label' => '標題',
        'validation_attribute' => '標題',
        'placeholder' => '輸入標題',
        'instructions' => '輸入內容的標題',
    ],
    'slug' => [
        'label' => '短網址',
        'validation_attribute' => '短網址',
        'placeholder' => '輸入短網址',
        'instructions' => '輸入內容的短網址',
    ],
    'seo' => [
        'tab' => [
            'label' => 'SEO',
        ],
        'meta_title' => [
            'label' => 'Meta 標題',
            'validation_attribute' => 'meta 標題',
            'placeholder' => '輸入 meta 標題',
            'instructions' => '輸入內容的 meta 標題',
        ],
        'meta_description' => [
            'label' => 'Meta 描述',
            'validation_attribute' => 'meta 描述',
            'placeholder' => '輸入 meta 描述',
            'instructions' => '輸入內容的 meta 描述',
        ],
        'meta_keywords' => [
            'label' => 'Meta 關鍵字',
            'validation_attribute' => 'meta 關鍵字',
            'placeholder' => '輸入 meta 關鍵字',
            'instructions' => '輸入內容的 meta 關鍵字',
        ],
        'og_title' => [
            'label' => 'Open Graph 標題',
            'validation_attribute' => 'open graph 標題',
            'placeholder' => '輸入 open graph 標題',
            'instructions' => '輸入內容的 open graph 標題',
        ],
        'og_description' => [
            'label' => 'Open Graph 描述',
            'placeholder' => '輸入 open graph 描述',
            'instructions' => '輸入內容的 open graph 描述',
        ],
        'og_image' => [
            'label' => 'Open Graph 圖片',
            'instructions' => '輸入內容的 open graph 圖片',
        ],
    ],
    'robots' => [
        'section' => [
            'heading' => 'Robots',
        ],
        'noindex' => [
            'label' => '不索引',
            'validation_attribute' => '不索引',
            'instructions' => '防止搜索引擎索引此內容',
        ],
        'nofollow' => [
            'label' => '不跟隨',
            'validation_attribute' => '不跟隨',
            'instructions' => '防止搜索引擎跟隨此內容上的鏈接',
        ],
    ],
    'redirect' => [
        'section' => [
            'heading' => '重定向',
        ],
        'redirect_path' => [
            'label' => '重定向路徑',
            'validation_attribute' => '重定向路徑',
            'placeholder' => '輸入重定向路徑',
            'instructions' => '輸入內容的重定向路徑',
        ],
        'redirect_content' => [
            'label' => '重定向內容',
            'validation_attribute' => '重定向內容',
            'placeholder' => '選擇重定向內容',
            'instructions' => '選擇要重定向的內容',
        ],
        'redirect_type' => [
            'label' => '重定向類型',
            'validation_attribute' => '重定向類型',
            'placeholder' => '選擇重定向類型',
            'instructions' => '選擇重定向類型',
            '301' => '301 永久',
            '302' => '302 臨時（默認）',
        ],
    ],
    'sitemap' => [
        'tab' => [
            'label' => '網站地圖',
        ],
        'enable' => [
            'label' => '啟用',
            'validation_attribute' => '啟用',
            'instructions' => '啟用內容以包含在網站地圖中',
        ],
        'priority' => [
            'label' => '優先級',
            'validation_attribute' => '優先級',
            'placeholder' => '輸入優先級',
            'instructions' => '
                <p><i>優先級應該是 0.0 到 1.0 之間的值。默認優先級是 0.5。</i></p>
                <p><b>1.0 是最高優先級，0.0 是最低優先級。</b></p>
                <p>頁面的優先級由搜索引擎用來確定頁面相對於網站上其他頁面的重要性。</p>
            ',
        ],
        'change_frequency' => [
            'label' => '更改頻率',
            'validation_attribute' => '更改頻率',
            'placeholder' => '選擇更改頻率',
            'instructions' => '選擇內容的更改頻率',
        ],
    ],
    'details' => [
        'tab' => [
            'label' => '詳情',
        ],
    ],
    'parent' => [
        'label' => '父級',
    ],
    'created_at' => [
        'label' => '創建於',
    ],
    'updated_at' => [
        'label' => '最後更新於',
    ],
    'deleted_at' => [
        'label' => '刪除於',
    ],
    'visibility' => [
        'label' => '可見性',
    ],
    'is_published' => [
        'label' => '已發布',
    ],
    'status' => [
        'label' => '狀態',
    ],
    'is_root_level' => [
        'label' => '是根級別',
    ],
    'published_at' => [
        'label' => '發布於',
        'hint' => '如果設置為未來日期，當時間到達時，該頁面將自動發布。',
    ],
    'latest_published_at' => [
        'label' => '最新發布於',
    ],
    'url' => [
        'label' => '網址',
    ],
    'template' => [
        'label' => '模板',
        'instructions' => '保持空白以使用文檔類型的默認模板',
    ],
    'document_type' => [
        'label' => '文檔類型',
        'validation_attribute' => '文檔類型',
    ],

    'notification' => [
        'remove_content_same_slug_in_same_parent' => [
            'title' => '刪除內容',
            'body' => '在同一父級中已存在具有相同短網址的內容。請先刪除現有內容。',
        ],
    ],

    'general' => [
        'section' => [
            'heading' => '常規',
        ],
    ],
    'content' => [
        'tab' => [
            'label' => '內容',
        ],
    ],
    'seo_og' => [
        'section' => [
            'heading' => 'Open Graph',
        ],
    ],

    'actions' => [

        'preview' => [

            'label' => '預覽',
    
        ],

        'more_actions' => [

            'label' => '更多操作',
    
        ],


        'publish' => [

            'label' => '發布',
    
            'modal' => [
    
                'heading' => '發布內容',
                
                'actions' => [

                    'publish' => [

                        'label' => '發布',

                    ],

                ],
    
            ],
    
            'notification' => [

                'published' => [

                    'title' => '發布成功',

                ],
            ],
        ],

        'save_draft' => [
    
            'label' => '保存草稿',
    
        ],

        'trash_bin' => [
    
            'label' => '垃圾桶',
    
        ],

        'reorder_content' => [
    
            'label' => '重新排序內容',
    
            'notification' => [

                'invalid_model' => [

                    'title' => '無效模型',

                ],
                'success' => [

                    'title' => '重新排序內容成功',

                ],
                'error' => [

                    'title' => '重新排序內容錯誤',

                ],
            ],
    
            'permission_display_name' => '重新排序內容',
    
        ],
    
        'set_default_content_page' => [
    
            'label' => '設為默認頁面',
    
            'permission_display_name' => '設置默認頁面',
    
            'notification' => [
    
                'success' => [
    
                    'title' => '默認頁面已更新。',
    
                ],
            ],
    
        ],

        'unpublish' => [
    
            'label' => '取消發布',
    
            'modal' => [
    
                'heading' => '取消發布內容',

                'description' => '',

                'actions' => [
    
                    'unpublish' => [
    
                        'label' => '取消發布',
    
                    ],
                ],
    
            ],
    
            'notification' => [
    
                'unpublished' => [
    
                    'title' => '取消發布成功',
    
                ],
            ],
        ],

        'create_content' => [
    
            'label' => '創建內容',
    
            'modal' => [
    
                'heading' => '在 :title 下創建內容',
    
            ],
    
            'empty_state' => '沒有可用的文檔類型。請先創建文檔類型。',
    
        ],
    
        'content_history' => [
    
            'label' => '內容歷史',
    
            'permission_display_name' => '查看內容歷史',
    
        ],

        'back'=> [

            'label' => '返回',

        ],
    ],
];
