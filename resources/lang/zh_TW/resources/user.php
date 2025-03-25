<?php

return [
    'name' => [
        'label' => '姓名',
        'validation_attribute' => '姓名',
    ],
    'email' => [
        'label' => '電子郵件地址',
        'validation_attribute' => '電子郵件地址',
    ],
    'roles' => [
        'label' => '角色',
        'validation_attribute' => '角色',
    ],
    'password' => [
        'label' => '密碼',
        'validation_attribute' => '密碼',
    ],
    'password_confirmation' => [
        'label' => '確認密碼',
        'validation_attribute' => '確認密碼',
    ],
    'avatar' => [
        'label' => '頭像',
        'validation_attribute' => '頭像',
    ],
    'preferred_language' => [
        'label' => '偏好語言',
        'validation_attribute' => '偏好語言',
    ],

    'email_confirmed_at' => [
        'label' => '電子郵件確認時間',
    ],
    'last_logged_in_at' => [
        'label' => '最後登入時間',
    ],
    'failed_login_attempt' => [
        'label' => '登入失敗次數',
    ],
    'last_lockouted_at' => [
        'label' => '最後鎖定時間',
        'hints' => '將於 :time 解鎖',
    ],

    'is_account_verified' => [
        'label' => '帳號驗證',
    ],
    'is_locked' => [
        'label' => '帳號鎖定',
    ],

    'buttons' => [
        'reset_lockout' => [
            'label' => '重置鎖定',
        ],
        'resend_verification_email' => [
            'label' => '重新發送驗證郵件',
        ],
    ],

    'notification' => [
        'account_not_verified' => [
            'title' => '帳戶未驗證',
            'body' => '您的帳戶尚未驗證。請檢查您的電子郵件以獲取驗證帳戶的說明。',
        ],
        'account_is_locked' => [
            'title' => '帳戶已鎖定',
            'body' => '您的帳戶已被鎖定。請聯繫支持部門尋求幫助。',
        ],
    ],

    'messages' => [
        'account_release_until' => '帳戶將於 :time 釋放',
    ],
];
