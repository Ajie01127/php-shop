// 邮箱管理路由配置
// 请将这些路由添加到 config/routes.php 文件的 return 数组中

// 邮箱管理
'GET /admin/email/config' => 'Admin\\EmailController@config',
'POST /admin/email/config' => 'Admin\\EmailController@config',
'POST /admin/email/test-config' => 'Admin\\EmailController@testConfig',
'POST /admin/email/send-test-email' => 'Admin\\EmailController@sendTestEmail',

// 邮箱通知配置
'GET /admin/email/notifications' => 'Admin\\EmailController@notifications',
'GET /admin/email/notification/edit/{eventType?}' => 'Admin\\EmailController@editNotification',
'POST /admin/email/notification/edit/{eventType?}' => 'Admin\\EmailController@editNotification',
'POST /admin/email/batch-update-status' => 'Admin\\EmailController@batchUpdateStatus',
'GET /admin/email/preview-template' => 'Admin\\EmailController@previewTemplate',

// 邮件日志
'GET /admin/email/logs' => 'Admin\\EmailController@logs',
'GET /admin/email/log/view/{id}' => 'Admin\\EmailController@viewLog',
'POST /admin/email/batch-delete-logs' => 'Admin\\EmailController@batchDeleteLogs',
'POST /admin/email/delete-log/{id}' => 'Admin\\EmailController@deleteLog',
'POST /admin/email/clear-logs' => 'Admin\\EmailController@clearLogs',
'POST /admin/email/retry-failed' => 'Admin\\EmailController@retryFailed',

// 邮件统计
'GET /admin/email/statistics' => 'Admin\\EmailController@statistics',