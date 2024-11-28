<?php 

return [
    'file' => [
        'title' => '檔案',
        'instructions' => '包含要匯入數據的 ZIP 檔案。',
    ],
    'available_at' => [
        'title' => '排程時間',
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
    'empty' => [
        'title' => '沒有匯入工作',
        'description' => '上傳 ZIP 檔案以開始匯入工作，並等待其排程和執行。',
    ],
    'notification' => [
        'completed' => [
            'title' => '匯入工作完成',
            'body' => "工作 ':id' 已完成。",
        ],
    ],
];