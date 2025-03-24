<?php

return [

    'empty_state' => [
        'heading' => 'No Export Jobs',
        'description' => 'Create an export job to start exporting data.',
    ],

    'exporter' => [
        'label' => 'Exporter',
    ],
    'message' => [
        'label' => 'Message',
    ],
    'result' => [
        'label' => 'Result',
    ],
    'status' => [
        'label' => 'Status',
    ],
    'finished_at' => [
        'label' => 'Finished at',
    ],
    'failed_at' => [
        'label' => 'Failed at',
    ],

    'notification' => [
        'completed' => [
            'title' => 'Export job completed',
            'body' => "Job ':id' has been completed.",
        ],
        'place_queue_success' => [
            'title' => 'Queued for export, please wait for the download link.',
        ],
        'place_queue_failue' => [
            'title' => 'Missing required data, failed to export.',
        ],

    ],

    'tabs' => [
        'details' => 'Details',
    ],
];
