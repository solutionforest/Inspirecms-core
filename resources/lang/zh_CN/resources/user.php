<?php

return [
    'name' => [
        'label' => '姓名',
        'validation_attribute' => '姓名',
    ],
    'email' => [
        'label' => '电子邮件地址',
        'validation_attribute' => '电子邮件地址',
    ],
    'roles' => [
        'label' => '角色',
        'validation_attribute' => '角色',
    ],
    'password' => [
        'label' => '密码',
        'validation_attribute' => '密码',
    ],
    'password_confirmation' => [
        'label' => '确认密码',
        'validation_attribute' => '确认密码',
    ],
    'avatar' => [
        'label' => '头像',
        'validation_attribute' => '头像',
    ],
    'preferred_language' => [
        'label' => '偏好语言',
        'validation_attribute' => '偏好语言',
    ],

    'email_confirmed_at' => [
        'label' => '电子邮件确认时间',
    ],
    'last_logged_in_at' => [
        'label' => '最后登录时间',
    ],
    'failed_login_attempt' => [
        'label' => '登录失败次数',
    ],
    'last_lockouted_at' => [
        'label' => '最后锁定时间',
        'hints' => '将于 :time 解锁',
    ],

    'is_account_verified' => [
        'label' => '账号验证',
    ],
    'is_locked' => [
        'label' => '账号锁定',
    ],

    'buttons' => [
        'reset_lockout' => [
            'label' => '重置锁定',
        ],
        'resend_verification_email' => [
            'label' => '重新发送验证邮件',
        ],
        'set_account_verified' => [
            'label' => '设置账户已验证',
        ],
    ],

    'notification' => [
        'account_not_verified' => [
            'title' => '账户未验证',
            'body' => '您的账户尚未验证。请检查您的电子邮件以获取验证账户的说明。',
        ],
        'account_is_locked' => [
            'title' => '账户已锁定',
            'body' => '您的账户已被锁定。请联系支持部门寻求帮助。',
        ],
    ],

    'messages' => [
        'account_release_until' => '账户将于 :time 释放',
    ],
];
