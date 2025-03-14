<?php

return [

    'title' => '安裝',

    'heading' => '安裝 CMS',

    'subheading' => '註冊新用戶',

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

    ],

    'messages' => [

        'throttled' => [
            'title' => '註冊嘗試次數過多',
            'body' => '請在 :seconds 秒後再試。',
        ],

        'assign_role_failed' => [
            'title' => '分配用戶角色失敗',
            'body' => '請確保您已經運行遷移並導入默認數據。',
        ],

    ],

];
