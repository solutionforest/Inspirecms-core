<?php

return [
    'name' => [
        'label' => 'Name',
        'validation_attribute' => 'name',
    ],
    'email' => [
        'label' => 'Email address',
        'validation_attribute' => 'email address',
    ],
    'roles' => [
        'label' => 'Roles',
        'validation_attribute' => 'roles',
    ],
    'password' => [
        'label' => 'Password',
        'validation_attribute' => 'password',
    ],
    'password_confirmation' => [
        'label' => 'Password Confirmation',
        'validation_attribute' => 'password Confirmation',
    ],
    'avatar' => [
        'label' => 'Avatar',
        'validation_attribute' => 'avatar',
    ],
    'preferred_language' => [
        'label' => 'Preferred Language',
        'validation_attribute' => 'preferred Language',
    ],

    'email_confirmed_at' => [
        'label' => 'Email confirmed at',
    ],
    'last_logged_in_at' => [
        'label' => 'Last logged in at',
    ],
    'failed_login_attempt' => [
        'label' => 'Failed login attempt',
    ],
    'last_lockouted_at' => [
        'label' => 'Last lockouted at',
        'hints' => 'Will release at :time',
    ],

    'is_account_verified' => [
        'label' => 'Account Verified',
    ],
    'is_locked' => [
        'label' => 'Account Locked',
    ],

    'buttons' => [
        'reset_lockout' => [
            'label' => 'Reset Lockout',
        ],
        'resend_verification_email' => [
            'label' => 'Resent Verification Email',
        ],
        'set_account_verified' => [
            'label' => 'Set Account Verified',
        ],
    ],

    'notification' => [

        'account_not_verified' => [
            'title' => 'Account Not Verified',
            'body' => 'Your account has not been verified. Please check your email for instructions on how to verify your account.',
        ],
        'account_is_locked' => [
            'title' => 'Account Locked',
            'body' => 'Your account has been locked. Please contact support for assistance.',
        ],
    ],

    'messages' => [
        'account_release_until' => 'Your account has been locked. It will be released at :time.',
    ],
];
