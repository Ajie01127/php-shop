-- 私域商城系统数据库结构
-- MySQL 8.0+

CREATE DATABASE IF NOT EXISTS `private_mall` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `private_mall`;

-- 用户表
CREATE TABLE `users` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL COMMENT '用户名',
  `email` varchar(100) NOT NULL COMMENT '邮箱',
  `phone` varchar(20) DEFAULT NULL COMMENT '手机号',
  `password` varchar(255) NOT NULL COMMENT '密码',
  `avatar` varchar(255) DEFAULT NULL COMMENT '头像',
  `vip_level` tinyint DEFAULT 0 COMMENT '会员等级',
  `points` int DEFAULT 0 COMMENT '积分',
  `balance` decimal(10,2) DEFAULT 0.00 COMMENT '余额',
  `status` tinyint DEFAULT 1 COMMENT '状态: 1正常 0禁用',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_email` (`email`),
  UNIQUE KEY `uk_phone` (`phone`),
  KEY `idx_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户表';

-- 管理员表
CREATE TABLE `admins` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL COMMENT '用户名',
  `password` varchar(255) NOT NULL COMMENT '密码',
  `role` varchar(20) DEFAULT 'admin' COMMENT '角色',
  `status` tinyint DEFAULT 1 COMMENT '状态: 1正常 0禁用',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='管理员表';

-- 商品分类表
CREATE TABLE `categories` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT '分类名称',
  `parent_id` int DEFAULT 0 COMMENT '父级ID',
  `sort_order` int DEFAULT 0 COMMENT '排序',
  `icon` varchar(255) DEFAULT NULL COMMENT '图标',
  `status` tinyint DEFAULT 1 COMMENT '状态',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='商品分类表';

-- 商品表
CREATE TABLE `products` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL COMMENT '商品名称',
  `description` text COMMENT '商品描述',
  `category_id` int unsigned NOT NULL COMMENT '分类ID',
  `price` decimal(10,2) NOT NULL COMMENT '价格',
  `original_price` decimal(10,2) DEFAULT NULL COMMENT '原价',
  `cost_price` decimal(10,2) DEFAULT NULL COMMENT '成本价',
  `stock` int DEFAULT 0 COMMENT '库存',
  `sales` int DEFAULT 0 COMMENT '销量',
  `rating` decimal(2,1) DEFAULT 5.0 COMMENT '评分',
  `images` text COMMENT '图片JSON数组',
  `specs` text COMMENT '规格JSON',
  `status` tinyint DEFAULT 1 COMMENT '状态: 1上架 0下架',
  `sort_order` int DEFAULT 0 COMMENT '排序',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_category_id` (`category_id`),
  KEY `idx_status` (`status`),
  KEY `idx_price` (`price`),
  KEY `idx_sales` (`sales`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='商品表';

-- 购物车表
CREATE TABLE `cart` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL COMMENT '用户ID',
  `product_id` int unsigned NOT NULL COMMENT '商品ID',
  `quantity` int DEFAULT 1 COMMENT '数量',
  `selected_spec` varchar(100) DEFAULT NULL COMMENT '选中规格',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user_product` (`user_id`, `product_id`, `selected_spec`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='购物车表';

-- 订单表
CREATE TABLE `orders` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `order_no` varchar(50) NOT NULL COMMENT '订单号',
  `user_id` int unsigned NOT NULL COMMENT '用户ID',
  `total_amount` decimal(10,2) NOT NULL COMMENT '订单总额',
  `pay_amount` decimal(10,2) NOT NULL COMMENT '实付金额',
  `discount_amount` decimal(10,2) DEFAULT 0.00 COMMENT '优惠金额',
  `status` varchar(20) NOT NULL DEFAULT 'pending' COMMENT '订单状态: pending待支付 paid已支付 shipped已发货 completed已完成 cancelled已取消',
  `payment_method` varchar(20) DEFAULT NULL COMMENT '支付方式',
  `remark` varchar(500) DEFAULT NULL COMMENT '备注',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `paid_at` timestamp NULL DEFAULT NULL COMMENT '支付时间',
  `shipped_at` timestamp NULL DEFAULT NULL COMMENT '发货时间',
  `completed_at` timestamp NULL DEFAULT NULL COMMENT '完成时间',
  `cancelled_at` timestamp NULL DEFAULT NULL COMMENT '取消时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_order_no` (`order_no`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='订单表';

-- 订单明细表
CREATE TABLE `order_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int unsigned NOT NULL COMMENT '订单ID',
  `product_id` int unsigned NOT NULL COMMENT '商品ID',
  `product_name` varchar(200) NOT NULL COMMENT '商品名称',
  `product_image` varchar(255) DEFAULT NULL COMMENT '商品图片',
  `price` decimal(10,2) NOT NULL COMMENT '单价',
  `quantity` int NOT NULL COMMENT '数量',
  `total_price` decimal(10,2) NOT NULL COMMENT '小计',
  `selected_spec` varchar(100) DEFAULT NULL COMMENT '选中规格',
  PRIMARY KEY (`id`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='订单明细表';

-- 收货地址表
CREATE TABLE `addresses` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL COMMENT '用户ID',
  `name` varchar(50) NOT NULL COMMENT '收货人',
  `phone` varchar(20) NOT NULL COMMENT '手机号',
  `province` varchar(50) NOT NULL COMMENT '省份',
  `city` varchar(50) NOT NULL COMMENT '城市',
  `district` varchar(50) NOT NULL COMMENT '区县',
  `detail` varchar(200) NOT NULL COMMENT '详细地址',
  `is_default` tinyint DEFAULT 0 COMMENT '是否默认',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='收货地址表';

-- 营销活动表
CREATE TABLE `marketing` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(20) NOT NULL COMMENT '活动类型: discount折扣 coupon优惠券 seckill秒杀 group拼团',
  `title` varchar(100) NOT NULL COMMENT '活动标题',
  `description` text COMMENT '活动描述',
  `start_time` timestamp NOT NULL COMMENT '开始时间',
  `end_time` timestamp NOT NULL COMMENT '结束时间',
  `status` varchar(20) DEFAULT 'draft' COMMENT '状态: draft草稿 active进行中 ended已结束',
  `rules` text COMMENT '规则JSON',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_type` (`type`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='营销活动表';

-- 轮播图表
CREATE TABLE `banners` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL COMMENT '标题',
  `image` varchar(255) NOT NULL COMMENT '图片',
  `link` varchar(255) DEFAULT NULL COMMENT '链接',
  `description` varchar(200) DEFAULT NULL COMMENT '描述',
  `sort_order` int DEFAULT 0 COMMENT '排序',
  `status` tinyint DEFAULT 1 COMMENT '状态',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='轮播图表';

-- 插入初始数据
INSERT INTO `admins` (`username`, `password`, `role`) VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'); -- 密码: password

-- 插入商品分类
INSERT INTO `categories` (`name`, `parent_id`, `icon`, `sort_order`) VALUES
('雾化配件', 0, '🔥', 1),
('烟嘴配件', 0, '⭐', 2),
('过滤配件', 0, '💎', 3),
('雾化器', 0, '🎁', 4);

-- 插入示例商品
INSERT INTO `products` (`name`, `description`, `category_id`, `price`, `original_price`, `stock`, `sales`, `rating`, `images`, `specs`) VALUES
('高端陶瓷雾化芯', '采用先进陶瓷材料，雾化效果出色，口感纯正，使用寿命长', 1, 299.00, 399.00, 100, 523, 4.8, '["https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=800"]', '[{"name":"材质","value":"陶瓷"},{"name":"尺寸","value":"10mm*15mm"}]'),
('精密注塑烟嘴', '自主研发高速注塑工艺，表面光滑，手感舒适，耐用性强', 2, 159.00, 199.00, 200, 867, 4.9, '["https://images.unsplash.com/photo-1572635196237-14b3f281503f?w=800"]', '[{"name":"材质","value":"PEEK塑料"},{"name":"颜色","value":"黑色/银色"}]'),
('棉芯过滤器套装', '优质棉芯材料，高效过滤，提升口感，6支装超值组合', 3, 89.00, NULL, 500, 1234, 4.7, '["https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=800"]', '[{"name":"规格","value":"6支装"},{"name":"材质","value":"有机棉"}]'),
('智能温控雾化器', '智能温控系统，精准控温，防止干烧，安全可靠', 4, 599.00, 799.00, 80, 234, 4.9, '["https://images.unsplash.com/photo-1611078489935-0cb964de46d6?w=800"]', '[]');

-- 插入轮播图
INSERT INTO `banners` (`title`, `image`, `description`, `sort_order`) VALUES
('新品上市', 'https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?w=1200&h=400&fit=crop', '高端陶瓷雾化芯，限时优惠', 1),
('品质保证', 'https://images.unsplash.com/photo-1607082349566-187342175e2f?w=1200&h=400&fit=crop', '自主研发高速注塑工艺', 2),
('全场促销', 'https://images.unsplash.com/photo-1607083206968-13611e3d76db?w=1200&h=400&fit=crop', '满299减50，满599减120', 3);
