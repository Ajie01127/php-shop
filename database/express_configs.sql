-- 快递配置表
CREATE TABLE IF NOT EXISTS `express_configs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT '配置ID',
  `express_code` VARCHAR(20) NOT NULL COMMENT '快递公司代码(SF-顺丰,YTO-圆通等)',
  `express_name` VARCHAR(50) NOT NULL COMMENT '快递公司名称',
  `partner_id` VARCHAR(100) NOT NULL COMMENT '合作伙伴ID/客户编码',
  `checkword` VARCHAR(200) NOT NULL COMMENT '校验码/密钥',
  `sender_name` VARCHAR(50) NOT NULL COMMENT '发件人姓名',
  `sender_mobile` VARCHAR(20) NOT NULL COMMENT '发件人手机',
  `sender_province` VARCHAR(50) NOT NULL COMMENT '发件省份',
  `sender_city` VARCHAR(50) NOT NULL COMMENT '发件城市',
  `sender_county` VARCHAR(50) NOT NULL COMMENT '发件区县',
  `sender_address` VARCHAR(200) NOT NULL COMMENT '发件详细地址',
  `monthly_account` VARCHAR(50) DEFAULT NULL COMMENT '月结账号(月结客户必填)',
  `express_types` VARCHAR(200) DEFAULT '25,26' COMMENT '推荐快递类型(逗号分隔)',
  `sandbox_mode` TINYINT(1) DEFAULT 1 COMMENT '是否沙箱模式(0-生产 1-测试)',
  `status` TINYINT(1) DEFAULT 1 COMMENT '状态(0-禁用 1-启用)',
  `sort_order` INT DEFAULT 0 COMMENT '排序',
  `remark` VARCHAR(500) DEFAULT NULL COMMENT '备注',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  UNIQUE KEY `uk_express_code` (`express_code`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='快递公司配置表';

-- 插入顺丰测试配置
INSERT INTO `express_configs` (`express_code`, `express_name`, `partner_id`, `checkword`, `sender_name`, `sender_mobile`, `sender_province`, `sender_city`, `sender_county`, `sender_address`, `monthly_account`, `sandbox_mode`, `status`, `sort_order`, `remark`) VALUES
('SF', '顺丰速运', 'test_partner_id', 'test_checkword', '张三', '13800138000', '广东省', '深圳市', '南山区', '科技园南区', '123456789', 1, 1, 1, '测试配置，请替换为真实参数');

-- 快递订单表
CREATE TABLE IF NOT EXISTS `express_orders` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT '快递订单ID',
  `order_id` INT UNSIGNED NOT NULL COMMENT '关联订单ID',
  `order_no` VARCHAR(50) NOT NULL COMMENT '订单编号',
  `express_code` VARCHAR(20) NOT NULL COMMENT '快递公司代码',
  `express_name` VARCHAR(50) NOT NULL COMMENT '快递公司名称',
  `waybill_no` VARCHAR(50) DEFAULT NULL COMMENT '运单号',
  `express_type` TINYINT NOT NULL DEFAULT 1 COMMENT '快递类型(1-标准快递 2-顺丰特惠等)',
  `pay_method` TINYINT NOT NULL DEFAULT 1 COMMENT '付款方式(1-寄付 2-到付 3-月结)',
  `cargo_name` VARCHAR(100) DEFAULT '商品' COMMENT '货物名称',
  `cargo_count` INT DEFAULT 1 COMMENT '货物数量',
  `cargo_unit` VARCHAR(10) DEFAULT '件' COMMENT '货物单位',
  `weight` DECIMAL(10,3) DEFAULT NULL COMMENT '重量(kg)',
  `volume` DECIMAL(10,3) DEFAULT NULL COMMENT '体积(m³)',
  `consignee_name` VARCHAR(50) NOT NULL COMMENT '收件人姓名',
  `consignee_mobile` VARCHAR(20) NOT NULL COMMENT '收件人手机',
  `consignee_province` VARCHAR(50) NOT NULL COMMENT '收件省份',
  `consignee_city` VARCHAR(50) NOT NULL COMMENT '收件城市',
  `consignee_county` VARCHAR(50) NOT NULL COMMENT '收件区县',
  `consignee_address` VARCHAR(200) NOT NULL COMMENT '收件详细地址',
  `sender_name` VARCHAR(50) NOT NULL COMMENT '发件人姓名',
  `sender_mobile` VARCHAR(20) NOT NULL COMMENT '发件人手机',
  `sender_province` VARCHAR(50) NOT NULL COMMENT '发件省份',
  `sender_city` VARCHAR(50) NOT NULL COMMENT '发件城市',
  `sender_county` VARCHAR(50) NOT NULL COMMENT '发件区县',
  `sender_address` VARCHAR(200) NOT NULL COMMENT '发件详细地址',
  `status` TINYINT NOT NULL DEFAULT 1 COMMENT '状态(1-已下单 2-已揽收 3-运输中 4-派送中 5-已签收 6-已取消 7-异常)',
  `status_desc` VARCHAR(100) DEFAULT NULL COMMENT '状态描述',
  `error_msg` VARCHAR(500) DEFAULT NULL COMMENT '错误信息',
  `api_request` TEXT DEFAULT NULL COMMENT 'API请求数据(JSON)',
  `api_response` TEXT DEFAULT NULL COMMENT 'API响应数据(JSON)',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  KEY `idx_order_id` (`order_id`),
  KEY `idx_order_no` (`order_no`),
  KEY `idx_waybill_no` (`waybill_no`),
  KEY `idx_express_code` (`express_code`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='快递订单表';

-- 快递路由表（物流轨迹）
CREATE TABLE IF NOT EXISTS `express_routes` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT '路由ID',
  `express_order_id` INT UNSIGNED NOT NULL COMMENT '快递订单ID',
  `waybill_no` VARCHAR(50) NOT NULL COMMENT '运单号',
  `route_time` DATETIME NOT NULL COMMENT '路由时间',
  `route_desc` VARCHAR(500) NOT NULL COMMENT '路由描述',
  `route_code` VARCHAR(50) DEFAULT NULL COMMENT '路由代码',
  `location` VARCHAR(100) DEFAULT NULL COMMENT '当前位置',
  `operator` VARCHAR(50) DEFAULT NULL COMMENT '操作员',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  KEY `idx_express_order_id` (`express_order_id`),
  KEY `idx_waybill_no` (`waybill_no`),
  KEY `idx_route_time` (`route_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='快递路由表';
