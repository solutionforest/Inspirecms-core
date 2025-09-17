<?php

return [

    'title' => 'Login',

    'heading' => 'Welcome back!',

    'subheading' => 'Sign in to your account',

    'buttons' => [
        'register' => [
            'before' => 'or',
            'label' => 'sign up for an account',
        ],
        'request_password_reset' => [
            'label' => 'Forgot password?',
        ],
        'authenticate' => [
            'label' => 'Sign in',
        ],
    ],

    'form' => [
        'email' => [
            'label' => __('inspirecms::resources/user.email.label'),
        ],
        'password' => [
            'label' => __('inspirecms::resources/user.password.label'),
        ],
        'remember' => [
            'label' => 'Remember me',
        ],
    ],

    'messages' => [
        'failed' => 'These credentials do not match our records.',
    ],

    'notification' => [
        'throttled' => [
            'title' => 'Too many login attempts',
            'body' => 'Please try again in :seconds seconds.',
        ],
    ],

];
