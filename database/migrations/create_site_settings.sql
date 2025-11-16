-- 网站配置表
CREATE TABLE IF NOT EXISTS `site_settings` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `key` VARCHAR(100) NOT NULL UNIQUE COMMENT '配置键',
  `value` TEXT DEFAULT NULL COMMENT '配置值',
  `type` ENUM('text', 'textarea', 'number', 'image', 'switch', 'json') DEFAULT 'text' COMMENT '值类型',
  `group` VARCHAR(50) DEFAULT 'basic' COMMENT '配置分组',
  `label` VARCHAR(200) DEFAULT NULL COMMENT '配置标签',
  `description` VARCHAR(500) DEFAULT NULL COMMENT '配置说明',
  `sort` INT DEFAULT 0 COMMENT '排序',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_group` (`group`),
  INDEX `idx_key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='网站配置表';

-- 插入默认配置
INSERT INTO `site_settings` (`key`, `value`, `type`, `group`, `label`, `description`, `sort`) VALUES
-- 基本信息
('site_name', '私域商城', 'text', 'basic', '网站名称', '显示在网站标题和页眉', 1),
('site_logo', '/images/logo.png', 'image', 'basic', '网站Logo', 'Logo图片地址', 2),
('site_favicon', '/images/favicon.ico', 'image', 'basic', '网站图标', 'Favicon图标地址', 3),
('site_keywords', '私域商城,电商平台,在线购物', 'text', 'basic', 'SEO关键词', '用于搜索引擎优化', 4),
('site_description', '专业的私域电商平台，提供优质商品和服务', 'textarea', 'basic', 'SEO描述', '网站描述信息', 5),
('site_copyright', 'Copyright © 2024 私域商城. All rights reserved.', 'text', 'basic', '版权信息', '网站底部版权文字', 6),
('site_icp', '京ICP备xxxxxxxx号','京ICP备xxxxxxxx号', 'text', 'basic', 'ICP备案号', '网站备案信息', 7),

-- 联系方式
('contact_phone', '400-888-8888', 'text', 'contact', '客服电话', '客服联系电话', 1),
('contact_email', 'service@example.com', 'text', 'contact', '客服邮箱', '客服邮箱地址', 2),
('contact_address', '北京市朝阳区xxx大厦', 'text', 'contact', '公司地址', '公司联系地址', 3),
('contact_wechat', 'wxid_xxxxxxx', 'text', 'contact', '微信号', '官方微信号', 4),
('contact_qq', '123456789', 'text', 'contact', 'QQ号', '官方QQ号', 5),
('work_time', '周一至周日 9:00-21:00', 'text', 'contact', '工作时间', '客服工作时间', 6),

-- 商城设置
('mall_status', '1', 'switch', 'mall', '商城状态', '开启/关闭商城', 1),
('mall_notice', '欢迎来到私域商城！', 'textarea', 'mall', '商城公告', '首页公告内容', 2),
('default_points', '100', 'number', 'mall', '注册赠送积分', '新用户注册赠送的积分', 3),
('points_ratio', '100', 'number', 'mall', '积分兑换比例', '多少积分等于1元', 4),
('free_shipping_amount', '99', 'number', 'mall', '包邮金额', '满多少元包邮', 5),
('min_order_amount', '0', 'number', 'mall', '最低下单金额', '最低起订金额', 6),

-- 订单设置
('order_auto_cancel', '30', 'number', 'order', '订单自动取消', '未支付订单多少分钟后自动取消', 1),
('order_auto_confirm', '7', 'number', 'order', '订单自动确认', '发货多少天后自动确认收货', 2),
('order_auto_comment', '7', 'number', 'order', '订单自动好评', '确认收货多少天后自动好评', 3),
('refund_expire_days', '7', 'number', 'order', '退款有效期', '确认收货后多少天内可申请退款', 4),

-- 上传设置
('upload_max_size', '5', 'number', 'upload', '最大上传大小', '图片上传最大尺寸(MB)', 1),
('upload_allow_ext', 'jpg,jpeg,png,gif', 'text', 'upload', '允许的扩展名', '允许上传的文件类型', 2),
('upload_path', '/uploads/', 'text', 'upload', '上传路径', '文件上传保存路径', 3),

-- 社交媒体
('social_wechat_qr', '', 'image', 'social', '微信二维码', '微信公众号二维码', 1),
('social_weibo', '', 'text', 'social', '微博地址', '官方微博链接', 2),
('social_douyin', '', 'text', 'social', '抖音号', '官方抖音号', 3),

-- 其他设置
('enable_register', '1', 'switch', 'other', '开启注册', '是否允许用户注册', 1),
('enable_comment', '1', 'switch', 'other', '开启评论', '是否允许商品评论', 2),
('enable_coupon', '1', 'switch', 'other', '开启优惠券', '是否启用优惠券功能', 3),
('enable_points', '1', 'switch', 'other', '开启积分', '是否启用积分功能', 4);
