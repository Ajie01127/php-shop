-- 更新users表支持小程序用户
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
ADD COLUMN `last_login_at` timestamp NULL DEFAULT NULL COMMENT '最后登录时间' AFTER `updated_at`,
DROP INDEX `uk_phone`,
ADD UNIQUE KEY `uk_openid` (`openid`),
ADD UNIQUE KEY `uk_unionid` (`unionid`),
ADD UNIQUE KEY `uk_phone` (`phone`),
ADD KEY `idx_user_type` (`user_type`),
ADD KEY `idx_openid` (`openid`);

-- 更新现有用户数据
UPDATE `users` SET 
  `nickname` = `username`,
  `user_type` = 'web'
WHERE `nickname` IS NULL;