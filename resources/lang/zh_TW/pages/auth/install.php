<?php

return [

    'title' => '安裝',

    'heading' => '安裝 CMS',

    'subheading' => '註冊新用戶',

    'form' => [

        'email' => [
            'label' => '電子郵件地址',
        ],

        'login_name' => [
            'label' => '登入名稱',
        ],

        'name' => [
            'label' => '名稱',
        ],

        'password' => [
            'label' => '密碼',
            'validation_attribute' => '密碼',
        ],

        'password_confirmation' => [
            'label' => '確認密碼',
        ],

        'actions' => [

            'register' => [
                'label' => '註冊',
            ],

        ],

    ],

    'notifications' => [

        'throttled' => [
            'title' => '註冊嘗試次數過多',
            'body' => '請在 :seconds 秒後再試。',
        ],

        'assign_role_failed' => [
            'title' => '分配用戶角色失敗',
            'body' => '請確保您已經運行了遷移並導入了默認數據。',
        ],

    ],

];