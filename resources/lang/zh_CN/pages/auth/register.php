<?php

return [

    'title' => [
        'installed' => '註冊',
        'not_installed' => '安裝 CMS',
    ],

    'heading' => [
        'installed' => '註冊新用戶',
        'not_installed' => '安裝 CMS',
    ],

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
            'label' => '註冊',
        ],
        'login' => [
            'before' => '已經有帳戶？',
            'label' => '登錄您的帳戶',
        ],
    ],

    'messages' => [
        'throttled' => [
            'title' => '注册尝试次数过多',
            'body' => '请在 :seconds 秒后再试。',
        ],
        'assign_role_failed' => [
            'title' => '分配用户角色失败',
            'body' => '请确保您已经运行迁移并导入默认数据。',
        ],
        'license_limit_exceeded' => [
            'title' => '用户限制已达到',
            'body' => '您已达到当前许可证允许的最大用户数。请升级您的计划以添加更多用户。',
        ],
    ],

];
