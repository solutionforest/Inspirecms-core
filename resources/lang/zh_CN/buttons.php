<?php

return [

    'add_with_name' => [
        'label' => '新增 :name',
        'heading' => '新增 :name',
    ],

    'add' => [
        'label' => '新增',
    ],

    'attach' => [
        'label' => '附加',
    ],

    'back' => [
        'label' => '返回',
    ],

    'bulk_detach' => [
        'label' => '批量分离',
    ],

    'cancel' => [
        'label' => '取消',
    ],

    'change_theme' => [
        'label' => '更改主题',
        'messages' => [
            'success' => [
                'title' => '主题更改成功。',
            ],
            'failure' => [
                'title' => '更改主题失败。',
            ],
        ],
    ],

    'choose' => [
        'label' => '选择',
    ],

    'clear' => [
        'label' => '清除',
    ],

    'clone_theme' => [
        'label' => '克隆主题',
        'messages' => [
            'success' => [
                'title' => '主题克隆成功。',
            ],
            'failure' => [
                'title' => '克隆主题失败。',
            ],
        ],
    ],

    'content_history' => [
        'label' => '版本',
    ],

    'copy_to_clipboard' => [
        'label' => '复制到剪贴板',
    ],

    'copy' => [
        'label' => '复制',
    ],

    'create_content' => [
        'label' => '创建内容',
        'heading' => '在 :title 下创建内容',
        'empty_state' => '没有可用的文档类型。请先创建文档类型。',
    ],

    'create_theme' => [
        'label' => '创建主题',
        'messages' => [
            'success' => [
                'title' => '主题创建成功。',
            ],
            'failure' => [
                'title' => '创建主题失败。',
            ],
        ],
    ],

    'download_sample' => [
        'label' => '下载模板',
    ],

    'download' => [
        'label' => '下载',
    ],

    'edit_and_preview' => [
        'label' => '编辑并预览',
        'messages' => [
            'success' => [
                'title' => __('inspirecms::messages.saved'),
                'body' => null,
            ],
            'failure' => [
                'title' => null,
                'body' => null,
            ],
        ],
    ],

    'edit_config' => [
        'label' => '编辑配置',
        'heading' => '编辑 :name 配置',
    ],

    'edit' => [
        'label' => '编辑',
    ],

    'export' => [
        'label' => '导出',
    ],

    'export_content_templates' => [
        'label' => '导出内容模板',
        'messages' => [
            'success' => [
                'title' => '内容模板导出成功。',
            ],
            'failure' => [
                'title' => '导出内容模板失败。',
            ],
        ],
    ],

    'fix' => [
        'label' => '修复',
    ],

    'import' => [
        'label' => '导入',
        'heading' => '导入',
    ],

    'lock_content' => [
        'label' => '锁定内容',
        'messages' => [
            'success' => [
                'title' => __('inspirecms::messages.locked'),
            ],
        ],
    ],

    'move_to_under' => [
        'label' => '移动到 :name 下',
        'heading' => '移动到 :name 下',
    ],

    'move_to' => [
        'label' => '移动到 ...',
    ],

    'more_actions' => [
        'label' => '更多操作',
    ],

    'open' => [
        'label' => '打开',
    ],

    'preview' => [
        'label' => '预览',
    ],

    'publish_descendants_and_self' => [
        'label' => '发布后代和自身',
        'heading' => '发布后代和自身',
        'messages' => [
            'success' => [
                'title' => '发布成功',
            ],
        ],
    ],

    'publish' => [
        'label' => '发布',
        'heading' => '发布内容',
        'messages' => [
            'success' => [
                'title' => '发布成功',
            ],
        ],
    ],

    'rename' => [
        'label' => '重命名',
        'messages' => [
            'success' => [
                'title' => '重命名成功',
            ],
            'failure' => [
                'title' => '重命名失败',
            ],
        ],
    ],

    'reorder_children' => [
        'label' => '重新排序子项',
        'messages' => [
            'invalid_model' => [
                'title' => '无效模型',
            ],
            'success' => [
                'title' => '重新排序子项成功',
            ],
        ],
        'permission_display_name' => '重新排序子内容',
    ],

    'save_changes' => [
        'label' => '保存更改',
    ],

    'save_draft' => [
        'label' => '保存草稿',
    ],

    'save' => [
        'label' => '保存',
    ],

    'select' => [
        'label' => '选择',
    ],

    'set_as_default' => [
        'label' => '设为默认',
        'messages' => [
            'success' => [
                'title' => '设为默认',
                'body' => '该项目已设为默认。',
            ],
            'failure' => [
                'title' => '设为默认失败',
                'body' => '该项目无法设为默认。请检查日志以获取更多信息。',
            ],
        ],
    ],

    'set_default_content_page' => [
        'label' => '设为默认页面',
        'permission_display_name' => '设置默认页面',
        'messages' => [
            'success' => [
                'title' => '默认页面已更新。',
            ],
        ],
    ],

    'trash_bin' => [
        'label' => '垃圾桶',
    ],

    'update_content_route' => [
        'label' => 'URL 与路由',
        'heading' => 'URL 与路由',
        'messages' => [
            'success' => [
                'title' => '路由已更新',
            ],
        ],
    ],

    'unlock_content' => [
        'label' => '解锁内容',
        'messages' => [
            'success' => [
                'title' => '已解锁',
            ],
            'not_owner_error' => [
                'title' => '解锁失败',
                'body' => '您不是锁定者。',
            ],
        ],
    ],

    'unpublish' => [
        'label' => '取消发布',
        'heading' => '取消发布内容',
        'messages' => [
            'success' => [
                'title' => '取消发布',
            ],
        ],
    ],

    'view' => [
        'label' => '查看',
    ],

    'view_usage' => [
        'label' => '查看使用情况',
        'heading' => '查看 :name 的使用情况',
    ],

];
