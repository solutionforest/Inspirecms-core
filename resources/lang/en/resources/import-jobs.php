<?php 

return [
    'file' => [
        'title' => 'File',
        'instructions' => 'The ZIP file containing the data to import.',
    ],
    'available_at' => [
        'title' => 'Scheduled At',
    ],
    'finished_at' => [
        'title' => 'Finished At',
    ],
    'failed_at' => [
        'title' => 'Failed At',
    ],
    'clear_at' => [
        'title' => 'Clear At',
    ],
    'payload' => [
        'title' => 'Payload',
    ],
    'empty' => [
        'title' => 'No Import Jobs',
        'description' => 'Upload a ZIP file to start an import job and wait for it to be scheduled and executed.',
    ],
    'notification' => [
        'completed' => [
            'title' => 'Import Job Completed',
            'body' => "The job ':id' has completed.",
        ],
    ],
];