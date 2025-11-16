-- 支付通道表
CREATE TABLE IF NOT EXISTS `payment_channels` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL COMMENT '通道名称',
  `type` ENUM('wechat', 'alipay') NOT NULL DEFAULT 'wechat' COMMENT '支付类型',
  `app_id` VARCHAR(100) NOT NULL COMMENT '应用ID',
  `mch_id` VARCHAR(100) NOT NULL COMMENT '商户号',
  `api_key` VARCHAR(255) NOT NULL COMMENT 'API密钥',
  `cert_path` VARCHAR(255) DEFAULT NULL COMMENT '证书路径',
  `key_path` VARCHAR(255) DEFAULT NULL COMMENT '私钥路径',
  `notify_url` VARCHAR(255) DEFAULT NULL COMMENT '回调地址',
  `is_active` TINYINT(1) DEFAULT 1 COMMENT '是否启用',
  `is_default` TINYINT(1) DEFAULT 0 COMMENT '是否默认',
  `config` TEXT DEFAULT NULL COMMENT '其他配置(JSON)',
  `remark` VARCHAR(500) DEFAULT NULL COMMENT '备注',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_type_active` (`type`, `is_active`),
  INDEX `idx_is_default` (`is_default`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='支付通道配置表';

-- 插入默认微信支付通道
INSERT INTO `payment_channels` 
(`name`, `type`, `app_id`, `mch_id`, `api_key`, `cert_path`, `key_path`, `is_default`, `remark`) 
VALUES 
('微信支付主通道', 'wechat', 'wx1234567890abcdef', '1234567890', 'your_api_v3_key_32_characters', '/certs/apiclient_cert.pem', '/certs/apiclient_key.pem', 1, '默认微信支付商户'),
('微信支付备用通道', 'wechat', 'wx0987654321fedcba', '0987654321', 'backup_api_v3_key_32_characters', '/certs/backup_cert.pem', '/certs/backup_key.pem', 0, '备用微信支付商户');

-- 订单表添加支付通道字段
ALTER TABLE `orders` 
ADD COLUMN `payment_channel_id` INT UNSIGNED DEFAULT NULL COMMENT '支付通道ID' AFTER `payment_method`,
ADD INDEX `idx_payment_channel` (`payment_channel_id`);
