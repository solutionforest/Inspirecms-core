<?php

return [

    'title' => 'Install',

    'heading' => 'Install CMS',

    'subheading' => 'Register a new user',

    'form' => [

        'email' => [
            'label' => 'Email address',
            'validation_attribute' => 'email address',
        ],

        'login_name' => [
            'label' => 'Login name',
            'validation_attribute' => 'login name',
        ],

        'name' => [
            'label' => 'Name',
            'validation_attribute' => 'name',
        ],

        'password' => [
            'label' => 'Password',
            'validation_attribute' => 'password',
        ],

        'password_confirmation' => [
            'label' => 'Confirm password',
            'validation_attribute' => 'password confirmation',
        ],

        'actions' => [

            'register' => [
                'label' => 'Sign up',
            ],

        ],

    ],

    'notifications' => [

        'throttled' => [
            'title' => 'Too many registration attempts',
            'body' => 'Please try again in :seconds seconds.',
        ],

        'assign_role_failed' => [
            'title' => 'Assign user role failed',
            'body' => 'Please ensure you have already run the migration and imported the default data.',
        ],

    ],

];
