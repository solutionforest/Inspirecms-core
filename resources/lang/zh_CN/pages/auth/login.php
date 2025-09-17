<?php

return [

    'title' => '登录',

    'heading' => '欢迎回来！',

    'subheading' => '登录您的账户',

    'buttons' => [
        'register' => [
            'before' => '或',
            'label' => '注册账号',
        ],
        'request_password_reset' => [
            'label' => '忘记密码？',
        ],
        'authenticate' => [
            'label' => '登录',
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
            'label' => '记住我',
        ],
    ],

    'messages' => [
        'failed' => '所提供的账号密码与数据库中的记录不相符。',
    ],

    'notification' => [
        'throttled' => [
            'title' => '尝试登录次数过多。请在 :seconds 秒后重试。',
        ],
    ],

];
