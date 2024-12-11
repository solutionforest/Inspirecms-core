<?php

return [
    'empty_state' => [
        'heading' => '沒有匯入工作',
        'description' => '上傳 ZIP 檔案以開始匯入工作，並等待其排程和執行。',
    ],
    'file' => [
        'title' => '檔案',
        'hint' => '包含要匯入數據的 ZIP 檔案。',
    ],
    'available_at' => [
        'title' => '排程時間',
        'instructions' => '匯入工作將執行的日期和時間。',
        'hint' => '留空以立即開始匯入工作。',
    ],
    'finished_at' => [
        'title' => '完成時間',
    ],
    'failed_at' => [
        'title' => '失敗時間',
    ],
    'clear_at' => [
        'title' => '清除時間',
    ],
    'payload' => [
        'title' => 'Payload',
    ],
    'notification' => [
        'completed' => [
            'title' => '匯入工作完成',
            'body' => "工作 ':id' 已完成。",
        ],
    ],
    'actions' => [
        'download_sample' => [
            'label' => '下載範例',
        ],
    ],
    'file_structure_instructions' => [
        'title' => 'ZIP 檔案的文件夾結構',
        'hint' => '以下是 ZIP 檔案的文件夾結構。請確保 ZIP 檔案包含以下文件夾。',
    ],
];
