-- 添加小程序配置项
INSERT INTO `site_settings` (`key`, `value`, `type`, `group`, `label`, `description`, `sort`) VALUES
-- 小程序设置
('enable_miniprogram', '1', 'switch', 'miniprogram', '开启小程序', '是否启用微信小程序功能', 1),
('miniprogram_name', '私域商城小程序', 'text', 'miniprogram', '小程序名称', '小程序在微信中显示的名称', 2),
('miniprogram_app_id', 'wx1234567890abcdef', 'text', 'miniprogram', '小程序AppID', '小程序开发平台AppID', 3),
('miniprogram_app_secret', 'yourappsecret', 'text', 'miniprogram', '小程序AppSecret', '小程序开发平台AppSecret', 4),
('miniprogram_token_expire', '7200', 'number', 'miniprogram', 'Token过期时间', '小程序登录Token过期时间(秒)', 5),
('miniprogram_api_domain', 'https://yourdomain.com', 'text', 'miniprogram', 'API域名', '小程序API接口域名', 6),
('miniprogram_debug', '1', 'switch', 'miniprogram', '调试模式', '是否开启小程序调试模式', 7),
('miniprogram_welcome_text', '欢迎使用私域商城小程序', 'textarea', 'miniprogram', '欢迎语', '小程序首页欢迎语', 8),
('miniprogram_copyright', 'Copyright © 2024 私域商城. All rights reserved.', 'text', 'miniprogram', '小程序版权', '小程序底部版权信息', 9),
('miniprogram_share_title', '私域商城 - 专业电商平台', 'text', 'miniprogram', '分享标题', '小程序分享时的默认标题', 10),
('miniprogram_share_desc', '发现优质商品，尽在私域商城', 'textarea', 'miniprogram', '分享描述', '小程序分享时的默认描述', 11),
('miniprogram_share_image', '/images/share-logo.png', 'image', 'miniprogram', '分享图片', '小程序分享时的默认图片', 12);

-- 更新网站设置分组列表
UPDATE `site_settings` SET `group` = 'miniprogram' WHERE `key` LIKE 'miniprogram_%';