-- 私域商城系统 - 数据库迁移文件
-- 执行顺序：按文件名顺序执行

-- 1. 基础表（已存在，保持不变）
-- users.sql
-- products.sql
-- categories.sql
-- orders.sql
-- order_items.sql
-- cart.sql
-- addresses.sql

-- 2. 支付相关表
SOURCE payment_channels.sql;

-- 3. 网站配置表
SOURCE site_settings.sql;

-- 4. 商品扩展表
SOURCE product_specs.sql;
SOURCE product_skus.sql;

-- 5. 运费模板表
SOURCE freight_templates.sql;
SOURCE freight_template_details.sql;

-- 6. 会员等级表
SOURCE member_levels.sql;

-- 7. 营销相关表
SOURCE marketing.sql;
SOURCE banners.sql;

-- 8. 快递相关表（新增）
SOURCE express_configs.sql;

-- 迁移完成
SELECT '数据库迁移完成！' as message;
