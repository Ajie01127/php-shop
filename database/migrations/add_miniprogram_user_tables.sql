-- 小程序用户表结构升级
-- 用于支持小程序用户注册和登录功能

-- 1. 更新用户表以支持小程序用户
ALTER TABLE `users` 
ADD COLUMN `openid` varchar(128) DEFAULT NULL COMMENT '微信openid' AFTER `phone`,
ADD COLUMN `unionid` varchar(128) DEFAULT NULL COMMENT '微信unionid' AFTER `openid`,
ADD COLUMN `nickname` varchar(100) DEFAULT NULL COMMENT '昵称' AFTER `avatar`,
ADD COLUMN `gender` tinyint DEFAULT 0 COMMENT '性别: 0未知 1男 2女' AFTER `nickname`,
ADD COLUMN `country` varchar(50) DEFAULT NULL COMMENT '国家' AFTER `gender`,
ADD COLUMN `province` varchar(50) DEFAULT NULL COMMENT '省份' AFTER `country`,
ADD COLUMN `city` varchar(50) DEFAULT NULL COMMENT '城市' AFTER `province`,
ADD COLUMN `language` varchar(20) DEFAULT 'zh_CN' COMMENT '语言' AFTER `city`,
ADD COLUMN `user_type` varchar(20) DEFAULT 'web' COMMENT '用户类型: web, mini_program' AFTER `language`,
ADD COLUMN `last_login_at` timestamp NULL DEFAULT NULL COMMENT '最后登录时间' AFTER `updated_at`;

-- 2. 创建用户token表（用于小程序用户认证）
CREATE TABLE IF NOT EXISTS `user_tokens` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL COMMENT '用户ID',
  `token` varchar(255) NOT NULL COMMENT '访问令牌',
  `device_type` varchar(20) DEFAULT 'mini_program' COMMENT '设备类型',
  `device_info` varchar(255) DEFAULT NULL COMMENT '设备信息',
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'IP地址',
  `expires_at` timestamp NOT NULL COMMENT '过期时间',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_token` (`token`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_expires` (`expires_at`),
  CONSTRAINT `fk_user_tokens_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户访问令牌表';

-- 3. 创建小程序用户地址表（如果不存在）
CREATE TABLE IF NOT EXISTS `addresses` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL COMMENT '用户ID',
  `name` varchar(50) NOT NULL COMMENT '收货人姓名',
  `phone` varchar(20) NOT NULL COMMENT '收货人电话',
  `province` varchar(50) DEFAULT NULL COMMENT '省份',
  `city` varchar(50) DEFAULT NULL COMMENT '城市',
  `district` varchar(50) DEFAULT NULL COMMENT '区县',
  `address` varchar(255) NOT NULL COMMENT '详细地址',
  `is_default` tinyint DEFAULT 0 COMMENT '是否默认地址',
  `status` tinyint DEFAULT 1 COMMENT '状态: 1正常 0删除',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_user_default` (`user_id`, `is_default`),
  CONSTRAINT `fk_addresses_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户收货地址表';

-- 4. 为小程序用户创建索引
ALTER TABLE `users` ADD INDEX `idx_openid` (`openid`);
ALTER TABLE `users` ADD INDEX `idx_user_type` (`user_type`);
ALTER TABLE `users` ADD INDEX `idx_last_login` (`last_login_at`);

-- 5. 插入示例小程序用户（用于测试）
INSERT INTO `users` (`username`, `password`, `email`, `mobile`, `openid`, `nickname`, `avatar`, `gender`, `user_type`, `status`, `created_at`) VALUES
('mini_program_user_1', 'hashed_password', 'test1@example.com', '13800138001', 'oP4e54wQxXg4HkL1aJyLhBcJQ2qM', '小程序测试用户1', 'https://example.com/avatar1.jpg', 1, 'mini_program', 1, NOW()),
('mini_program_user_2', 'hashed_password', 'test2@example.com', '13800138002', 'oP4e54wQxXg4HkL1aJyLhBcJQ2qN', '小程序测试用户2', 'https://example.com/avatar2.jpg', 2, 'mini_program', 1, NOW());

-- 6. 更新现有用户类型（如果用户已存在但没有设置user_type）
UPDATE `users` SET `user_type` = 'web' WHERE `user_type` IS NULL;

-- 7. 创建小程序配置表（如果不存在，与之前的迁移合并）
INSERT IGNORE INTO `site_settings` (`key`, `value`, `type`, `group`, `label`, `description`, `sort`) VALUES
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

-- 迁移完成提示
SELECT '小程序用户表结构升级完成！' as result;