<?php

return [
    'empty_state' => [
        'heading' => '沒有匯入工作',
        'description' => '上傳 ZIP 文件以開始匯入工作，並等待其被排程和執行。',
    ],

    'file_name' => [
        'label' => '文件',
        'validation_attribute' => '文件',
        'hint' => '包含要匯入數據的 ZIP 文件。',
    ],
    'available_at' => [
        'label' => '排程時間',
        'validation_attribute' => '排程時間',
        'instructions' => '匯入工作將被執行的日期和時間。',
        'hint' => '留空以立即開始匯入工作。',
    ],
    'finished_at' => [
        'label' => '完成於',
    ],
    'failed_at' => [
        'label' => '失敗於',
    ],
    'clear_at' => [
        'label' => '清除於',
    ],
    'payload' => [
        'label' => '有效負載',
    ],
    'status' => [
        'label' => '狀態',
    ],
    'file_structure_instructions' => [
        'label' => 'ZIP 文件的文件夾結構',
        'hint' => '以下是 ZIP 文件的文件夾結構。請確保 ZIP 文件包含以下文件夾。',
    ],

    'notification' => [

        'completed' => [
            'title' => '匯入工作完成',
            'body' => "工作 ':id' 已完成。",
        ],

    ],
];
