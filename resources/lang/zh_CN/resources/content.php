<?php

return [
    
    'title' => [
        'label' => '标题',
        'validation_attribute' => '标题',
        'placeholder' => '输入标题',
        'instructions' => '输入内容的标题',
    ],
    'slug' => [
        'label' => '短网址',
        'validation_attribute' => '短网址',
        'placeholder' => '输入短网址',
        'instructions' => '输入内容的短网址',
    ],
    'parent' => [
        'label' => '父级',
    ],
    'created_at' => [
        'label' => '创建于',
    ],
    'updated_at' => [
        'label' => '最后更新于',
    ],
    'deleted_at' => [
        'label' => '删除于',
    ],
    'visibility' => [
        'label' => '可见性',
    ],
    'is_published' => [
        'label' => '已发布',
    ],
    'status' => [
        'label' => '状态',
    ],
    'is_root_level' => [
        'label' => '是根级别',
    ],
    'published_at' => [
        'label' => '发布于',
        'hint' => '如果设置为未来日期，当时间到达时，该页面将自动发布。',
    ],
    'latest_published_at' => [
        'label' => '最新发布于',
    ],
    'url' => [
        'label' => '网址',
    ],
    'template' => [
        'label' => '模板',
        'instructions' => '保持空白以使用文档类型的预设模板',
    ],
    'document_type' => [
        'label' => '文档类型',
        'validation_attribute' => '文档类型',
    ],

    'locked_at' => [
        'label' => '锁定于',
    ],
    'locked_by' => [
        'label' => '锁定者',
    ],    'seo' => [
        'meta_title' => [
            'label' => 'Meta 标题',
            'validation_attribute' => 'meta 标题',
            'placeholder' => '输入 meta 标题',
            'instructions' => '输入内容的 meta 标题',
        ],
        'meta_description' => [
            'label' => 'Meta 描述',
            'validation_attribute' => 'meta 描述',
            'placeholder' => '输入 meta 描述',
            'instructions' => '输入内容的 meta 描述',
        ],
        'meta_keywords' => [
            'label' => 'Meta 关键字',
            'validation_attribute' => 'meta 关键字',
            'placeholder' => '输入 meta 关键字',
            'instructions' => '输入内容的 meta 关键字',
        ],
        'og_title' => [
            'label' => 'Open Graph 标题',
            'validation_attribute' => 'open graph 标题',
            'placeholder' => '输入 open graph 标题',
            'instructions' => '输入内容的 open graph 标题',
        ],
        'og_description' => [
            'label' => 'Open Graph 描述',
            'validation_attribute' => 'open graph 描述',
            'placeholder' => '输入 open graph 描述',
            'instructions' => '输入内容的 open graph 描述',
        ],
        'og_image' => [
            'label' => 'Open Graph 图片',
            'validation_attribute' => 'open graph 图片',
            'instructions' => '输入内容的 open graph 图片',
        ],
    ],

    'robots' => [
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
            '302' => '302 臨時（預設）',
        ],
    ],

    'sitemap' => [
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
                <p><i>優先級應該是 0.0 到 1.0 之間的值。預設優先級是 0.5。</i></p>
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

    'routes' => [
        'is_default_pattern' => [
            'label' => '是否為預設模式',
            'validation_attribute' => '是否為預設模式',
            'hints' => '預設模式: :format',
        ],
        'language_id' => [
            'label' => '語言區域',
            'validation_attribute' => '語言區域',
            'placeholder' => '預設語言區域',
        ],
        'uri' => [
            'label' => '路徑',
            'validation_attribute' => '路徑',
            'hints' => '路徑應以 \'/\' 開頭。',
        ],
        'regex_constraints' => [
            'label' => '正則表達式約束',
            'key_label' => '參數值',
            'value_label' => '正則表達式',
            'hints' => '為路由模式添加正則表達式約束。例如: <i>:examples</i> ...',
        ],
    ],

    'history' => [
        'field' => [
            'label' => '欄位',
        ],
        'from' => [
            'label' => '從',
        ],
        'to' => [
            'label' => '到',
        ],
    ],

    'notification' => [
        'remove_content_same_slug_in_same_parent' => [
            'title' => '刪除內容',
            'body' => '在同一父級中已存在具有相同短網址的內容。請先刪除現有內容。',
        ],
        'property_data_not_changed' => [
            'title' => '無變更',
            'body' => '沒有要保存的變更。',
        ],
    ],

    'tabs' => [
        'content' => '內容',
        'details' => '詳情',
        'sitemap' => '網站地圖',
        'seo' => 'SEO',
    ],

    'sections' => [
        'general' => [
            'heading' => '常規',
        ],
        'seo_og' => [
            'heading' => 'Open Graph',
        ],
        'robots' => [
            'heading' => 'Robots',
        ],
        'redirect' => [
            'heading' => '重定向',
        ],
    ],

];
