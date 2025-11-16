-- 商品规格表
CREATE TABLE IF NOT EXISTS `product_specs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT UNSIGNED NOT NULL COMMENT '商品ID',
  `spec_name` VARCHAR(50) NOT NULL COMMENT '规格名称',
  `spec_values` TEXT NOT NULL COMMENT '规格值(JSON)',
  `sort` INT DEFAULT 0 COMMENT '排序',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商品规格表';

-- 商品SKU表
CREATE TABLE IF NOT EXISTS `product_skus` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT UNSIGNED NOT NULL COMMENT '商品ID',
  `sku_code` VARCHAR(100) UNIQUE NOT NULL COMMENT 'SKU编码',
  `spec_info` TEXT NOT NULL COMMENT '规格信息(JSON)',
  `price` DECIMAL(10, 2) NOT NULL COMMENT '售价',
  `original_price` DECIMAL(10, 2) DEFAULT NULL COMMENT '原价',
  `cost_price` DECIMAL(10, 2) DEFAULT NULL COMMENT '成本价',
  `stock` INT DEFAULT 0 COMMENT '库存',
  `weight` DECIMAL(10, 2) DEFAULT 0 COMMENT '重量(kg)',
  `volume` DECIMAL(10, 2) DEFAULT 0 COMMENT '体积(m³)',
  `image` VARCHAR(255) DEFAULT NULL COMMENT 'SKU图片',
  `is_default` TINYINT(1) DEFAULT 0 COMMENT '是否默认',
  `status` TINYINT(1) DEFAULT 1 COMMENT '状态:1启用0禁用',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_product_id` (`product_id`),
  INDEX `idx_sku_code` (`sku_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商品SKU表';

-- 运费模板表
CREATE TABLE IF NOT EXISTS `freight_templates` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL COMMENT '模板名称',
  `type` ENUM('weight', 'volume', 'piece') DEFAULT 'weight' COMMENT '计费方式:weight重量,volume体积,piece件数',
  `is_free` TINYINT(1) DEFAULT 0 COMMENT '是否包邮',
  `free_amount` DECIMAL(10, 2) DEFAULT 0 COMMENT '满额包邮金额',
  `free_num` INT DEFAULT 0 COMMENT '满件包邮数量',
  `sort` INT DEFAULT 0 COMMENT '排序',
  `is_default` TINYINT(1) DEFAULT 0 COMMENT '是否默认',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='运费模板表';

-- 运费模板详情表
CREATE TABLE IF NOT EXISTS `freight_template_details` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `template_id` INT UNSIGNED NOT NULL COMMENT '模板ID',
  `area_type` ENUM('all', 'include', 'exclude') DEFAULT 'all' COMMENT '地区类型:all全国,include指定地区,exclude排除地区',
  `area_codes` TEXT DEFAULT NULL COMMENT '地区代码(JSON)',
  `first_unit` DECIMAL(10, 2) NOT NULL COMMENT '首重/首件/首体积',
  `first_price` DECIMAL(10, 2) NOT NULL COMMENT '首费',
  `continue_unit` DECIMAL(10, 2) NOT NULL COMMENT '续重/续件/续体积',
  `continue_price` DECIMAL(10, 2) NOT NULL COMMENT '续费',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_template_id` (`template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='运费模板详情表';

-- 会员等级表
CREATE TABLE IF NOT EXISTS `member_levels` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `level_name` VARCHAR(50) NOT NULL COMMENT '等级名称',
  `level` INT NOT NULL UNIQUE COMMENT '等级数字',
  `min_points` INT DEFAULT 0 COMMENT '所需最低积分',
  `min_amount` DECIMAL(10, 2) DEFAULT 0 COMMENT '所需最低消费金额',
  `discount` DECIMAL(3, 2) DEFAULT 1.00 COMMENT '折扣:0.95表示95折',
  `icon` VARCHAR(255) DEFAULT NULL COMMENT '等级图标',
  `color` VARCHAR(20) DEFAULT NULL COMMENT '等级颜色',
  `benefits` TEXT DEFAULT NULL COMMENT '等级权益(JSON)',
  `description` VARCHAR(500) DEFAULT NULL COMMENT '等级描述',
  `sort` INT DEFAULT 0 COMMENT '排序',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_level` (`level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='会员等级表';

-- 插入默认会员等级
INSERT INTO `member_levels` (`level_name`, `level`, `min_points`, `min_amount`, `discount`, `color`, `benefits`, `description`, `sort`) VALUES
('普通会员', 1, 0, 0, 1.00, '#999999', '["基础购物权限"]', '注册即可成为普通会员', 1),
('银牌会员', 2, 1000, 1000, 0.98, '#C0C0C0', '["98折优惠","优先客服"]', '累计消费1000元或积分达1000', 2),
('金牌会员', 3, 3000, 3000, 0.95, '#FFD700', '["95折优惠","优先发货","专属客服"]', '累计消费3000元或积分达3000', 3),
('钻石会员', 4, 10000, 10000, 0.90, '#B9F2FF', '["9折优惠","免运费","专属活动","生日礼物"]', '累计消费10000元或积分达10000', 4),
('至尊会员', 5, 50000, 50000, 0.85, '#FF1493', '["85折优惠","全场免运费","专属客服","优先新品","生日礼包"]', '累计消费50000元或积分达50000', 5);

-- 修改商品表，添加运费模板字段
ALTER TABLE `products` 
ADD COLUMN `freight_template_id` INT UNSIGNED DEFAULT NULL COMMENT '运费模板ID' AFTER `stock`,
ADD COLUMN `has_sku` TINYINT(1) DEFAULT 0 COMMENT '是否有SKU规格' AFTER `freight_template_id`,
ADD INDEX `idx_freight_template` (`freight_template_id`);

-- 修改用户表，添加会员等级相关字段
ALTER TABLE `users` 
ADD COLUMN `member_level_id` INT UNSIGNED DEFAULT 1 COMMENT '会员等级ID' AFTER `points`,
ADD COLUMN `total_amount` DECIMAL(10, 2) DEFAULT 0 COMMENT '累计消费金额' AFTER `member_level_id`,
ADD COLUMN `level_updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT '等级更新时间' AFTER `total_amount`,
ADD INDEX `idx_member_level` (`member_level_id`);

-- 修改订单明细表，添加SKU信息
ALTER TABLE `order_items`
ADD COLUMN `sku_id` INT UNSIGNED DEFAULT NULL COMMENT 'SKU ID' AFTER `product_id`,
ADD COLUMN `sku_code` VARCHAR(100) DEFAULT NULL COMMENT 'SKU编码' AFTER `sku_id`,
ADD COLUMN `spec_info` TEXT DEFAULT NULL COMMENT '规格信息' AFTER `sku_code`,
ADD INDEX `idx_sku_id` (`sku_id`);

-- 修改订单表，添加运费字段
ALTER TABLE `orders`
ADD COLUMN `freight_amount` DECIMAL(10, 2) DEFAULT 0 COMMENT '运费' AFTER `pay_amount`,
ADD COLUMN `member_discount` DECIMAL(3, 2) DEFAULT 1.00 COMMENT '会员折扣' AFTER `freight_amount`;

-- 购物车表添加SKU字段
ALTER TABLE `cart`
ADD COLUMN `sku_id` INT UNSIGNED DEFAULT NULL COMMENT 'SKU ID' AFTER `product_id`,
ADD COLUMN `spec_info` TEXT DEFAULT NULL COMMENT '规格信息' AFTER `sku_id`,
ADD INDEX `idx_sku_id` (`sku_id`);
