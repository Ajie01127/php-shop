-- 为订单表添加支付相关字段

ALTER TABLE `orders` 
ADD COLUMN `prepay_id` VARCHAR(64) DEFAULT NULL COMMENT '预支付ID' AFTER `payment_method`,
ADD COLUMN `transaction_id` VARCHAR(64) DEFAULT NULL COMMENT '微信支付交易号' AFTER `prepay_id`,
ADD COLUMN `refund_no` VARCHAR(64) DEFAULT NULL COMMENT '退款单号' AFTER `transaction_id`,
ADD INDEX `idx_transaction_id` (`transaction_id`),
ADD INDEX `idx_refund_no` (`refund_no`);

-- 新增退款状态
ALTER TABLE `orders` 
MODIFY COLUMN `status` VARCHAR(20) NOT NULL DEFAULT 'pending' 
COMMENT '订单状态: pending待支付 paid已支付 shipped已发货 completed已完成 cancelled已取消 refunding退款中 refunded已退款';
