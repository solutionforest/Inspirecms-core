<?php

return [

    'avoid_to_clean' => [
        'label' => '避免清理',
        'instructions' => '如果启用，内容版本将不会被清理。',
    ],
    'publish_state' => [
        'label' => '发布状态',
    ],

    'empty_state' => [
        'heading' => '找不到版本',
        'description' => '此内容没有可用的版本。',
    ],

    'tables' => [
        'search_placeholder' => '按审计员姓名搜索...',
    ],

    'content_history_detail' => [
        'general_info' => 'General Info',
        'property_data' => 'Property Data',
        'empty_state' => '找不到版本',
    ],

    'buttons' => [
        'view_differences' => [
            'label' => '查看差异',
            'heading' => '内容版本差异',
            'description' => '由 :author 于 :date',
        ],
        'bulk_update_state' => [
            'label' => '批量更新状态',
            'heading' => '更新记录状态',
            'messages' => [
                'success' => [
                    'title' => '状态更新成功。',
                ],
                'failure' => [
                    'title' => '状态更新失败。',
                ],
            ],
        ],
        'toggle_avoid_to_clean' => [
            'true_label' => '允许清理',
            'false_label' => '避免清理',
            'messages' => [
                'wait_to_cleanup' => [
                    'title' => '现在等待清理。',
                    'body' => '此版本将在下次清理周期中被清理。',
                ],
                'avoid_cleanup' => [
                    'title' => '现在避免清理。',
                    'body' => '此版本将不会在下次清理周期中被清理。',
                ],
            ],
        ],
        'rollback' => [
            'label' => '恢复',
            'heading' => '从 :from 恢复到 :to',
            'description' => '您确定要恢复到此版本吗？此操作无法撤销。',
            'invalid_heading' => '发生错误',
            'invalid_description' => '发生错误。选定的版本无法恢复。',
            'messages' => [
                'success' => [
                    'title' => '内容已恢复到选定的版本。',
                ],
                'failure' => [
                    'title' => '恢复失败。',
                    'body' => '恢复内容时发生错误。(详情: :details)',
                ],
            ],
        ],
    ],
];
