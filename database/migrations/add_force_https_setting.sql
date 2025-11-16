-- 添加强制HTTPS配置项
-- 日期：2024-01-15

-- 检查是否已存在
DELETE FROM `site_settings` WHERE `key` = 'force_https';
DELETE FROM `site_settings` WHERE `key` = 'site_status';
DELETE FROM `site_settings` WHERE `key` = 'maintenance_message';

-- 插入新配置
INSERT INTO `site_settings` (`key`, `value`, `group`, `type`, `title`, `description`, `sort_order`) VALUES
('force_https', '0', 'basic', 'switch', '强制HTTPS', '开启后所有HTTP请求将自动重定向到HTTPS，提升安全性', 6),
('site_status', '1', 'basic', 'switch', '网站状态', '关闭后网站将显示维护页面', 7),
('maintenance_message', '网站维护中，请稍后访问', 'basic', 'textarea', '维护提示', '网站关闭时显示的提示信息', 8);

-- 更新其他配置的排序（如果需要）
UPDATE `site_settings` SET `sort_order` = 1 WHERE `key` = 'site_name';
UPDATE `site_settings` SET `sort_order` = 2 WHERE `key` = 'site_logo';
UPDATE `site_settings` SET `sort_order` = 3 WHERE `key` = 'site_keywords';
UPDATE `site_settings` SET `sort_order` = 4 WHERE `key` = 'site_description';
UPDATE `site_settings` SET `sort_order` = 5 WHERE `key` = 'site_icp';

SELECT '强制HTTPS配置项已添加！' as message;
