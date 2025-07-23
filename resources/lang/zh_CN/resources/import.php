<?php

return [
    'empty_state' => [
        'heading' => '没有导入工作',
        'description' => '上传 ZIP 文件以开始导入工作，并等待其被调度和执行。',
    ],

    'file_name' => [
        'label' => '文件',
        'validation_attribute' => '文件',
        'hint' => '包含要导入数据的 ZIP 文件。',
    ],
    'available_at' => [
        'label' => '调度时间',
        'validation_attribute' => '调度时间',
        'instructions' => '导入工作将被执行的日期和时间。',
        'hint' => '留空以立即开始导入工作。',
    ],
    'finished_at' => [
        'label' => '完成于',
    ],
    'failed_at' => [
        'label' => '失败于',
    ],
    'clear_at' => [
        'label' => '清除于',
    ],
    'payload' => [
        'label' => '有效负载',
    ],
    'status' => [
        'label' => '状态',
    ],
    'file_structure_instructions' => [
        'label' => 'ZIP 文件的文件夹结构',
        'hint' => '以下是 ZIP 文件的文件夹结构。请确保 ZIP 文件包含以下文件夹。',
    ],

    'notification' => [

        'completed' => [
            'title' => '导入工作完成',
            'body' => "工作 ':id' 已完成。",
        ],

    ],
];
