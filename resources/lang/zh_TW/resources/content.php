<?php

return [
    'title' => [
        'label' => '標題',
        'placeholder' => '輸入標題',
        'instructions' => '輸入內容的標題',
    ],
    'slug' => [
        'label' => '短網址',
        'placeholder' => '輸入短網址',
        'instructions' => '輸入內容的短網址',
    ],
    'content' => [
        'heading' => '內容',
    ],
    'general' => [
        'heading' => '基本資料',
    ],
    'seo' => [
        'heading' => 'SEO',
        'meta_title' => [
            'label' => 'Meta 標題',
            'placeholder' => '輸入 Meta 標題',
            'instructions' => '輸入內容的 Meta 標題',
        ],
        'meta_description' => [
            'label' => 'Meta 描述',
            'placeholder' => '輸入 Meta 描述',
            'instructions' => '輸入內容的 Meta 描述',
        ],
        'meta_keywords' => [
            'label' => 'Meta 關鍵字',
            'placeholder' => '輸入 Meta 關鍵字',
            'instructions' => '輸入內容的 Meta 關鍵字',
        ],
        'og' => [
            'heading' => 'Open Graph',
            'og_title' => [
                'label' => 'Open Graph 標題',
                'placeholder' => '輸入 Open Graph 標題',
                'instructions' => '輸入內容的 Open Graph 標題',
            ],
            'og_description' => [
                'label' => 'Open Graph 描述',
                'placeholder' => '輸入 Open Graph 描述',
                'instructions' => '輸入內容的 Open Graph 描述',
            ],
            'og_image' => [
                'label' => 'Open Graph 圖片',
                'instructions' => '輸入內容的 Open Graph 圖片',
            ],
        ],
        'robots' => [
            'heading' => 'Robots',
            'instructions' => '配置 robots meta 標籤',
            'noindex' => [
                'label' => '不索引',
                'instructions' => '防止搜索引擎索引此內容',
            ],
            'nofollow' => [
                'label' => '不跟隨',
                'instructions' => '防止搜索引擎跟隨此內容上的鏈接',
            ],
        ],
    ],
    'redirect' => [
        'heading' => '重定向',
        'redirect_path' => [
            'label' => '重定向路徑',
            'placeholder' => '輸入重定向路徑',
            'instructions' => '輸入內容的重定向路徑',
        ],
        'redirect_content' => [
            'label' => '重定向內容',
            'placeholder' => '選擇重定向內容',
            'instructions' => '選擇要重定向到的內容',
        ],
        'redirect_type' => [
            'label' => '重定向類型',
            'placeholder' => '選擇重定向類型',
            'instructions' => '選擇重定向類型',
            '301' => '301 永久',
            '302' => '302 臨時 (默認)',
        ],
    ],
    'sitemap' => [
        'heading' => '網站地圖',
        'enable' => [
            'label' => '啟用',
            'instructions' => '啟用內容以包含在網站地圖中',
        ],
        'priority' => [
            'label' => '優先級',
            'placeholder' => '輸入優先級',
            'instructions' => '
                <p><i>優先級應該是 0.0 到 1.0 之間的值。默認優先級是 0.5。</i></p>
                <p><b>1.0 是最高優先級，0.0 是最低優先級。</b></p>
                <p>頁面的優先級由搜索引擎用來確定頁面相對於網站上其他頁面的重要性。</p>
            ',
        ],
        'change_frequency' => [
            'label' => '更改頻率',
            'placeholder' => '選擇更改頻率',
            'instructions' => '選擇內容的更改頻率',
        ],
    ],
    'details' => [
        'heading' => '詳情',
    ],
    'parent' => [
        'label' => '父項',
    ],
    'id' => [
        'label' => 'ID',
    ],
    'created_at' => [
        'label' => '創建時間',
    ],
    'updated_at' => [
        'label' => '最後更新時間',
    ],
    'deleted_at' => [
        'label' => '刪除時間',
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
        'label' => '是否為根級別',
    ],
    'published_at' => [
        'label' => '發布時間',
        'hint' => '如果設置為未來日期，則此頁面將在該時間自動發布。',
    ],
    'latest_published_at' => [
        'label' => '最新發布時間',
    ],
    'url' => [
        'label' => 'URL',
    ],
    'template' => [
        'label' => '模板',
        'helperText' => '保持空白以使用文檔類型的默認模板',
    ],

    'notification' => [
        'remove_content_same_slug_in_same_parent' => [
            'title' => '刪除內容',
            'body' => '在相同父項中已存在具有相同短網址的內容。請先刪除現有內容。',
        ],
    ],
];
