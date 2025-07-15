<?php

return [

    'avoid_to_clean' => [
        'label' => '避免清理',
        'instructions' => '如果啟用，內容版本將不會被清理。',
    ],
    'publish_state' => [
        'label' => '發布狀態',
    ],

    'empty_state' => [
        'heading' => '找不到版本',
        'description' => '此內容沒有可用的版本。',
    ],

    'tables' => [
        'search_placeholder' => '按審計員姓名搜尋...',
    ],

    'content_history_detail' => [
        'general_info' => 'General Info',
        'property_data' => 'Property Data',
        'empty_state' => '找不到版本',
    ],

    'buttons' => [
        'view_differences' => [
            'label' => '檢視差異',
            'heading' => '內容版本差異',
            'description' => '由 :author 於 :date',
        ],
        'bulk_update_state' => [
            'label' => '批量更新狀態',
            'heading' => '更新記錄狀態',
            'messages' => [
                'success' => [
                    'title' => '狀態更新成功。',
                ],
                'failure' => [
                    'title' => '狀態更新失敗。',
                ],
            ],
        ],
        'toggle_avoid_to_clean' => [
            'true_label' => '允許清理',
            'false_label' => '避免清理',
            'messages' => [
                'wait_to_cleanup' => [
                    'title' => '現在等待清理。',
                    'body' => '此版本將在下次清理週期中被清理。',
                ],
                'avoid_cleanup' => [
                    'title' => '現在避免清理。',
                    'body' => '此版本將不會在下次清理週期中被清理。',
                ],
            ],
        ],
    ],
];
