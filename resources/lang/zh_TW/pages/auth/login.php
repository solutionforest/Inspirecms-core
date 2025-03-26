<?php

return [

    'title' => '登入',
    'heading' => '登入帳號',

    'buttons' => [
        'register' => [
            'before' => '或',
            'label' => '註冊帳號',
        ],
        'request_password_reset' => [
            'label' => '忘記密碼？',
        ],
        'authenticate' => [
            'label' => '登入',
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
            'label' => '記住我',
        ],
    ],

    'messages' => [
        'failed' => '所提供的帳號密碼與資料庫中的記錄不相符。',
    ],

    'notification' => [
        'throttled' => [
            'title' => '嘗試登入次數過多。請在 :seconds 秒後重試。',
        ],
    ],

];
