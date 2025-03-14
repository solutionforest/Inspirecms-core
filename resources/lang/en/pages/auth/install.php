<?php

return [

    'title' => 'Install',

    'heading' => 'Install CMS',

    'subheading' => 'Register a new user',

    'form' => [

        'email' => [
            'label' => __('inspirecms::resources/user.email.label'),
            'validation_attribute' => __('inspirecms::resources/user.email.validation_attribute'),
        ],

        'name' => [
            'label' => __('inspirecms::resources/user.name.label'),
            'validation_attribute' => __('inspirecms::resources/user.name.validation_attribute'),
        ],

        'password' => [
            'label' => __('inspirecms::resources/user.password.label'),
            'validation_attribute' => __('inspirecms::resources/user.password.validation_attribute'),
        ],
        
        'password_confirmation' => [
            'label' => __('inspirecms::resources/user.password_confirmation.label'),
            'validation_attribute' => __('inspirecms::resources/user.password_confirmation.validation_attribute'),
        ],
    ],

    'buttons' => [
        
        'register' => [
            'label' => 'Sign up',
        ],

    ],

    'messages' => [

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
