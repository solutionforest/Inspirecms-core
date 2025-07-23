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

    'bulk_detach' => [
        'label' => '批量分離',
    ],

    'cancel' => [
        'label' => '取消',
    ],

    'change_theme' => [
        'label' => '更改主題',
        'messages' => [
            'success' => [
                'title' => '主題更改成功。',
            ],
            'failure' => [
                'title' => '更改主題失敗。',
            ],
        ],
    ],

    'choose' => [
        'label' => '選擇',
    ],

    'clear' => [
        'label' => '清除',
    ],

    'clone_theme' => [
        'label' => '克隆主題',
        'messages' => [
            'success' => [
                'title' => '主題克隆成功。',
            ],
            'failure' => [
                'title' => '克隆主題失敗。',
            ],
        ],
    ],

    'content_history' => [
        'label' => '版本',
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

    'create_theme' => [
        'label' => '創建主題',
        'messages' => [
            'success' => [
                'title' => '主題創建成功。',
            ],
            'failure' => [
                'title' => '創建主題失敗。',
            ],
        ],
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

    'edit_config' => [
        'label' => '編輯配置',
        'heading' => '編輯 :name 配置',
    ],

    'edit' => [
        'label' => '編輯',
    ],

    'export' => [
        'label' => '匯出',
    ],

    'export_content_templates' => [
        'label' => '匯出內容模板',
        'messages' => [
            'success' => [
                'title' => '內容模板匯出成功。',
            ],
            'failure' => [
                'title' => '匯出內容模板失敗。',
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

    'lock_content' => [
        'label' => '鎖定內容',
        'messages' => [
            'success' => [
                'title' => __('inspirecms::messages.locked'),
            ],
        ],
    ],

    'move_to_under' => [
        'label' => '移動到 :name 下',
        'heading' => '移動到 :name 下',
    ],

    'move_to' => [
        'label' => '移動到 ...',
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

    'rename' => [
        'label' => '重命名',
        'messages' => [
            'success' => [
                'title' => '重命名成功',
            ],
            'failure' => [
                'title' => '重命名失敗',
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
        'label' => '設為預設頁面',
        'permission_display_name' => '設置預設頁面',
        'messages' => [
            'success' => [
                'title' => '預設頁面已更新。',
            ],
        ],
    ],

    'trash_bin' => [
        'label' => '垃圾桶',
    ],

    'update_content_route' => [
        'label' => 'URL 與路由',
        'heading' => 'URL 與路由',
        'messages' => [
            'success' => [
                'title' => '路由已更新',
            ],
        ],
    ],

    'unlock_content' => [
        'label' => '解鎖內容',
        'messages' => [
            'success' => [
                'title' => '已解鎖',
            ],
            'not_owner_error' => [
                'title' => '解鎖失敗',
                'body' => '您不是鎖定者。',
            ],
        ],
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

    'view_usage' => [
        'label' => '查看使用情況',
        'heading' => '查看 :name 的使用情況',
    ],

];
