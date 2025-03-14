<?php

return [

    'add_with_name' => [
        'label' => '新增 :name',
        'heading' => '新增 :name',
    ],

    'add' => [
        'label' => '新增',
    ],

    'attach' => [
        'label' => '附加',
    ],

    'back' => [
        'label' => '返回',
    ],

    'cancel' => [
        'label' => '取消',
    ],

    'choose' => [
        'label' => '選擇',
    ],

    'clear' => [
        'label' => '清除',
    ],

    'content_history' => [
        'label' => '內容歷史',
        'permission_display_name' => '查看內容歷史',
    ],

    'copy_to_clipboard' => [
        'label' => '複製到剪貼簿',
    ],

    'copy' => [
        'label' => '複製',
    ],

    'create_content' => [
        'label' => '創建內容',
        'heading' => '在 :title 下創建內容',
        'empty_state' => '沒有可用的文檔類型。請先創建文檔類型。',
    ],

    'download_sample' => [
        'label' => '下載範本',
    ],

    'download' => [
        'label' => '下載',
    ],

    'edit_and_preview' => [
        'label' => '編輯並預覽',
        'messages' => [
            'success' => [
                'title' => __('inspirecms::messages.saved'),
                'body' => null,
            ],
            'failure' => [
                'title' => null,
                'body' => null,
            ],
        ],
    ],

    'fix' => [
        'label' => '修復',
    ],

    'import' => [
        'label' => '匯入',
        'heading' => '匯入',
    ],

    'more_actions' => [
        'label' => '更多操作',
    ],

    'open' => [
        'label' => '打開',
    ],

    'preview' => [
        'label' => '預覽',
    ],

    'publish_descendants_and_self' => [
        'label' => '發布後代和自身',
        'heading' => '發布後代和自身',
        'messages' => [
            'success' => [
                'title' => '發布成功',
            ],
        ],
    ],

    'publish' => [
        'label' => '發布',
        'heading' => '發布內容',
        'messages' => [
            'success' => [
                'title' => '發布成功',
            ],
        ],
    ],

    'reorder_children' => [
        'label' => '重新排序子項',
        'messages' => [
            'invalid_model' => [
                'title' => '無效模型',
            ],
            'success' => [
                'title' => '重新排序子項成功',
            ],
        ],
        'permission_display_name' => '重新排序子內容',
    ],

    'save_changes' => [
        'label' => '儲存變更',
    ],

    'save_draft' => [
        'label' => '保存草稿',
    ],

    'save' => [
        'label' => '儲存',
    ],

    'select' => [
        'label' => '選擇',
    ],
    
    'set_as_default' => [
        'label' => '設為預設',
        'messages' => [
            'success' => [
                'title' => '設為預設',
                'body' => '該項目已設為預設。',
            ],
            'failure' => [
                'title' => '設為預設失敗',
                'body' => '該項目無法設為預設。請檢查日誌以獲取更多信息。',
            ],
        ],
    ],

    'set_default_content_page' => [
        'label' => '設為默認頁面',
        'permission_display_name' => '設置默認頁面',
        'messages' => [
            'success' => [
                'title' => '默認頁面已更新。',
            ],
        ],
    ],

    'trash_bin' => [
        'label' => '垃圾桶',
    ],

    'unpublish' => [
        'label' => '取消發布',
        'heading' => '取消發布內容',
        'messages' => [
            'success' => [
                'title' => '取消發布',
            ],
        ],
    ],

    'view' => [
        'label' => '查看',
    ],

];
