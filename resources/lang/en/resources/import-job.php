<?php

return [
    'empty_state' => [
        'heading' => 'No Import Jobs',
        'description' => 'Upload a ZIP file to start an import job and wait for it to be scheduled and executed.',
    ],
    'file' => [
        'label' => 'File',
        'validation_attribute' => 'file',
        'hint' => 'The ZIP file containing the data to import.',
    ],
    'available_at' => [
        'label' => 'Scheduled At',
        'validation_attribute' => 'scheduled at',
        'instructions' => 'The date and time when the import job will be executed.',
        'hint' => 'Leave empty to start the import job immediately.',
    ],
    'finished_at' => [
        'label' => 'Finished At',
    ],
    'failed_at' => [
        'label' => 'Failed At',
    ],
    'clear_at' => [
        'label' => 'Clear At',
    ],
    'payload' => [
        'label' => 'Payload',
    ],
    'status' => [
        'label' => 'Status',
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
        'label' => 'Folder Structure of zip file',
        'hint' => 'Below is the folder structure of the zip file. Please ensure that the zip file contains the following folders.',
    ],
];
