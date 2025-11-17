-- 邮箱配置表
CREATE TABLE IF NOT EXISTS `mall_email_configs` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `driver` VARCHAR(50) NOT NULL DEFAULT 'smtp' COMMENT '邮件驱动: smtp, mail, sendmail',
    `host` VARCHAR(255) NOT NULL DEFAULT 'smtp.gmail.com' COMMENT 'SMTP服务器',
    `port` INT(11) NOT NULL DEFAULT 587 COMMENT 'SMTP端口',
    `encryption` VARCHAR(20) DEFAULT 'tls' COMMENT '加密方式: ssl, tls',
    `username` VARCHAR(255) NOT NULL COMMENT '邮箱用户名',
    `password` VARCHAR(255) NOT NULL COMMENT '邮箱密码/应用专用密码',
    `from_name` VARCHAR(100) NOT NULL DEFAULT '系统通知' COMMENT '发件人名称',
    `from_email` VARCHAR(255) NOT NULL COMMENT '发件人邮箱',
    `is_enabled` TINYINT(1) DEFAULT 0 COMMENT '是否启用',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 邮箱通知场景配置表
CREATE TABLE IF NOT EXISTS `mall_email_notifications` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `event_type` VARCHAR(100) NOT NULL COMMENT '事件类型',
    `name` VARCHAR(100) NOT NULL COMMENT '场景名称',
    `description` TEXT COMMENT '场景描述',
    `template_subject` VARCHAR(255) NOT NULL COMMENT '邮件主题模板',
    `template_content` TEXT NOT NULL COMMENT '邮件内容模板',
    `is_enabled` TINYINT(1) DEFAULT 1 COMMENT '是否启用',
    `recipient_type` ENUM('admin', 'user', 'custom') DEFAULT 'user' COMMENT '接收者类型',
    `recipients` TEXT COMMENT '自定义接收者邮箱列表，JSON格式',
    `variables` TEXT COMMENT '可用变量说明，JSON格式',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_event_type` (`event_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 邮件发送记录表
CREATE TABLE IF NOT EXISTS `mall_email_logs` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `to_email` VARCHAR(255) NOT NULL COMMENT '接收邮箱',
    `subject` VARCHAR(255) NOT NULL COMMENT '邮件主题',
    `content` TEXT NOT NULL COMMENT '邮件内容',
    `event_type` VARCHAR(100) DEFAULT NULL COMMENT '关联事件类型',
    `status` ENUM('pending', 'sent', 'failed') DEFAULT 'pending' COMMENT '发送状态',
    `error_message` TEXT COMMENT '错误信息',
    `sent_at` TIMESTAMP NULL COMMENT '发送时间',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_to_email` (`to_email`),
    KEY `idx_event_type` (`event_type`),
    KEY `idx_status` (`status`),
    KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 插入默认的邮件通知场景
INSERT INTO `mall_email_notifications` (`event_type`, `name`, `description`, `template_subject`, `template_content`, `recipient_type`, `variables`) VALUES
('user_register', '用户注册', '新用户注册时发送通知给管理员', '新用户注册通知', '<h3>新用户注册通知</h3><p>有新用户注册了您的商城：</p><ul><li>用户名：{{username}}</li><li>邮箱：{{email}}</li><li>注册时间：{{created_at}}</li><li>IP地址：{{ip_address}}</li></ul>', 'admin', '{"username": "用户名", "email": "邮箱", "created_at": "注册时间", "ip_address": "IP地址"}'),

('user_login', '用户登录', '用户登录时发送邮件给用户', '登录提醒', '<h3>登录提醒</h3><p>您好 {{username}}，您已成功登录商城：</p><ul><li>登录时间：{{login_time}}</li><li>登录IP：{{ip_address}}</li><li>登录地点：{{location}}</li></ul><p>如果这不是您本人操作，请及时修改密码。</p>', 'user', '{"username": "用户名", "login_time": "登录时间", "ip_address": "登录IP", "location": "登录地点"}'),

('order_created', '订单创建', '用户下单时发送邮件通知', '订单创建成功', '<h3>订单创建成功</h3><p>尊敬的 {{username}}，您的订单已创建成功：</p><ul><li>订单号：{{order_no}}</li><li>订单金额：¥{{total_amount}}</li><li>商品数量：{{product_count}} 件</li><li>创建时间：{{created_at}}</li></ul><p>我们会尽快处理您的订单，请耐心等待。</p>', 'user', '{"username": "用户名", "order_no": "订单号", "total_amount": "订单金额", "product_count": "商品数量", "created_at": "创建时间"}'),

('order_paid', '订单支付', '订单支付成功时发送邮件', '支付成功通知', '<h3>支付成功通知</h3><p>尊敬的 {{username}}，您的订单已支付成功：</p><ul><li>订单号：{{order_no}}</li><li>支付金额：¥{{paid_amount}}</li><li>支付方式：{{payment_method}}</li><li>支付时间：{{paid_at}}</li></ul><p>我们将尽快为您发货，感谢您的支持！</p>', 'user', '{"username": "用户名", "order_no": "订单号", "paid_amount": "支付金额", "payment_method": "支付方式", "paid_at": "支付时间"}'),

('order_shipped', '订单发货', '订单发货时发送邮件给用户', '订单发货通知', '<h3>订单发货通知</h3><p>尊敬的 {{username}}，您的订单已发货：</p><ul><li>订单号：{{order_no}}</li><li>快递公司：{{express_company}}</li><li>快递单号：{{tracking_number}}</li><li>发货时间：{{shipped_at}}</li></ul><p>您可以通过快递单号查询物流信息。</p>', 'user', '{"username": "用户名", "order_no": "订单号", "express_company": "快递公司", "tracking_number": "快递单号", "shipped_at": "发货时间"}'),

('order_completed', '订单完成', '订单完成时发送邮件给用户', '订单完成通知', '<h3>订单完成通知</h3><p>尊敬的 {{username}}，您的订单已完成：</p><ul><li>订单号：{{order_no}}</li><li>订单金额：¥{{total_amount}}</li><li>完成时间：{{completed_at}}</li></ul><p>感谢您的购买，期待您的再次光临！</p>', 'user', '{"username": "用户名", "order_no": "订单号", "total_amount": "订单金额", "completed_at": "完成时间"}'),

('payment_failed', '支付失败', '支付失败时发送邮件给用户', '支付失败通知', '<h3>支付失败通知</h3><p>尊敬的 {{username}}，您的支付未成功：</p><ul><li>订单号：{{order_no}}</li><li>支付金额：¥{{amount}}</li><li>失败原因：{{error_message}}</li><li>失败时间：{{failed_at}}</li></ul><p>您可以重新尝试支付或联系客服。</p>', 'user', '{"username": "用户名", "order_no": "订单号", "amount": "金额", "error_message": "失败原因", "failed_at": "失败时间"}'),

('low_stock', '库存不足', '商品库存不足时通知管理员', '库存不足提醒', '<h3>库存不足提醒</h3><p>以下商品库存不足：</p><ul>{{#products}}<li>{{name}} - 当前库存：{{stock}} 件</li>{{/products}}</ul><p>请及时补充库存。</p>', 'admin', '{"products": "商品列表"}'),

('system_error', '系统错误', '系统发生错误时通知管理员', '系统错误报告', '<h3>系统错误报告</h3><p>系统发生错误：</p><ul><li>错误类型：{{error_type}}</li><li>错误信息：{{error_message}}</li><li>发生时间：{{error_time}}</li><li>影响页面：{{request_uri}}</li><li>用户IP：{{ip_address}}</li></ul><p>请及时处理。</p>', 'admin', '{"error_type": "错误类型", "error_message": "错误信息", "error_time": "错误时间", "request_uri": "请求URI", "ip_address": "用户IP"}');

-- 插入默认邮箱配置
INSERT INTO `mall_email_configs` (`driver`, `host`, `port`, `encryption`, `username`, `password`, `from_name`, `from_email`) VALUES
('smtp', 'smtp.gmail.com', 587, 'tls', 'your-email@gmail.com', 'your-app-password', '系统通知', 'your-email@gmail.com');