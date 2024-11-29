<?php

return [
    'file' => [
        'title' => 'File',
        'hint' => 'The ZIP file containing the data to import.',
    ],
    'available_at' => [
        'title' => 'Scheduled At',
        'instructions' => 'The date and time when the import job will be executed.',
        'hint' => 'Leave empty to start the import job immediately.',
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
    'actions' => [
        'download_sample' => [
            'label' => 'Download Sample',
        ],
    ],
    'file_structure_instructions' => [
        'title' => 'Folder Structure of zip file',
        'hint' => 'Below is the folder structure of the zip file. Please ensure that the zip file contains the following folders.',
    ],
];
