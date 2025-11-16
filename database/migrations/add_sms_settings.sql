-- 添加短信设置到系统配置表
INSERT IGNORE INTO site_settings (`key`, `value`, `description`, `type`, `group`, `sort`) VALUES
-- 基本设置
('sms_enable', '0', '是否启用短信服务', 'switch', 'sms', 1),
('sms_secret_id', '', '腾讯云SecretId', 'text', 'sms', 2),
('sms_secret_key', '', '腾讯云SecretKey', 'password', 'sms', 3),
('sms_sdk_app_id', '', '短信应用SDK AppID', 'text', 'sms', 4),
('sms_sign_name', '', '短信签名', 'text', 'sms', 5),

-- 场景启用设置
('sms_scene_login', '1', '启用登录验证码', 'switch', 'sms', 6),
('sms_scene_register', '1', '启用注册验证码', 'switch', 'sms', 7),
('sms_scene_reset', '1', '启用密码重置', 'switch', 'sms', 8),
('sms_scene_order', '1', '启用订单通知', 'switch', 'sms', 9),
('sms_scene_payment', '1', '启用支付通知', 'switch', 'sms', 10),
('sms_scene_shipping', '1', '启用发货通知', 'switch', 'sms', 11),
('sms_scene_refund', '1', '启用退款通知', 'switch', 'sms', 12),

-- 注册配置
('sms_register_enable', '1', '全站开放短信注册', 'switch', 'sms', 20),
('sms_auto_bind', '1', '小程序用户手机号自动绑定', 'switch', 'sms', 21),

-- 模板设置
('sms_template_id_login', '', '登录验证码模板ID', 'text', 'sms', 30),
('sms_template_id_register', '', '注册验证码模板ID', 'text', 'sms', 31),
('sms_template_id_reset', '', '密码重置模板ID', 'text', 'sms', 32),
('sms_template_id_order', '', '订单确认模板ID', 'text', 'sms', 33),
('sms_template_id_payment', '', '支付成功模板ID', 'text', 'sms', 34),
('sms_template_id_shipping', '', '发货通知模板ID', 'text', 'sms', 35),
('sms_template_id_refund', '', '退款通知模板ID', 'text', 'sms', 36);

-- 创建短信发送记录表
CREATE TABLE IF NOT EXISTS sms_records (
    id INT(11) NOT NULL AUTO_INCREMENT,
    phone VARCHAR(20) NOT NULL COMMENT '手机号',
    template_id VARCHAR(50) NOT NULL COMMENT '模板ID',
    scene VARCHAR(50) NOT NULL COMMENT '发送场景',
    params TEXT COMMENT '模板参数',
    request_id VARCHAR(100) DEFAULT NULL COMMENT '请求ID',
    serial_no VARCHAR(100) DEFAULT NULL COMMENT '发送流水号',
    status ENUM('pending', 'success', 'failed') DEFAULT 'pending' COMMENT '发送状态',
    error_message TEXT COMMENT '错误信息',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_phone (phone),
    KEY idx_scene (scene),
    KEY idx_created_at (created_at),
    KEY idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='短信发送记录表';

-- 创建验证码表
CREATE TABLE IF NOT EXISTS sms_codes (
    id INT(11) NOT NULL AUTO_INCREMENT,
    phone VARCHAR(20) NOT NULL COMMENT '手机号',
    code VARCHAR(10) NOT NULL COMMENT '验证码',
    scene VARCHAR(50) NOT NULL COMMENT '验证场景',
    used TINYINT(1) DEFAULT 0 COMMENT '是否已使用',
    expire_time TIMESTAMP NOT NULL COMMENT '过期时间',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_phone (phone),
    KEY idx_scene (scene),
    KEY idx_expire_time (expire_time),
    KEY idx_used (used)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='短信验证码表';