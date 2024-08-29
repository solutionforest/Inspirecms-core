<?php

return [

    'title' => 'Install',

    'heading' => 'Install CMS',

    'subheading' => 'Register a new user',

    'form' => [

        'email' => [
            'label' => 'Email address',
        ],

        'login_name' => [
            'label' => 'Login name',
        ],

        'name' => [
            'label' => 'Name',
        ],

        'password' => [
            'label' => 'Password',
            'validation_attribute' => 'password',
        ],

        'password_confirmation' => [
            'label' => 'Confirm password',
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