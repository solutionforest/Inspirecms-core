<?php

return [

    'title' => 'Import',

    'fields' => [
        'file' => [
            'label' => 'File',
            'instructions' => 'Upload a JSON file to import data.',
        ],
    ],

    'steps' => [
        'file' => [
            'label' => 'Upload File',
        ],
    ],

    'actions' => [
        'import' => [
            'label' => 'Import',
        ],
    ],

    'notification' => [
        'success' => [
            'title' => 'Data Imported',
            'message' => 'Data has been successfully imported.',
        ],
        'error' => [
            'title' => 'Error',
            'message' => 'An error occurred while importing data.',
        ],
        'validation' => [
            'title' => 'Validation Error',
            'message' => 'The data provided is invalid. Please correct the errors and try again.',
        ],
        'error-after-process' => [
            'title' => 'Error',
            'message' => 'An error occurred while processing the data.',
        ],
    ],
];
