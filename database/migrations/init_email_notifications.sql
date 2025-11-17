-- 初始化邮件通知配置
-- 这个文件在系统安装时执行，用于创建默认的邮件通知配置

-- 插入默认邮件通知场景配置
INSERT INTO `email_notifications` (`event_type`, `name`, `description`, `enabled`, `template_subject`, `template_body`, `created_at`, `updated_at`) VALUES
('user_register', '用户注册', '用户注册成功后发送欢迎邮件', 1, '欢迎注册{{site_name}}', 
'亲爱的{{username}}：

欢迎您注册成为{{site_name}}的用户！

您的账户信息：
- 用户名：{{username}}
- 邮箱：{{email}}
- 注册时间：{{register_time}}

现在您可以开始享受我们的购物体验了！

如有任何问题，请联系我们的客服团队。

祝您购物愉快！

{{site_name}} 团队', NOW(), NOW()),

('order_created', '订单创建', '用户下单后发送订单确认邮件', 1, '您的订单{{order_no}}已创建', 
'亲爱的{{username}}：

您的订单已成功创建！

订单信息：
- 订单号：{{order_no}}
- 订单金额：¥{{total_amount}}
- 创建时间：{{created_at}}

{{#items}}
商品：{{product_name}}
单价：¥{{price}}
数量：{{quantity}}
小计：¥{{total_price}}
{{/items}}

请及时完成支付，订单将在24小时后自动取消。

如有疑问，请联系客服。

{{site_name}} 团队', NOW(), NOW()),

('payment_success', '支付成功', '用户支付成功后发送通知', 1, '订单{{order_no}}支付成功', 
'亲爱的{{username}}：

恭喜！您的订单已支付成功！

支付信息：
- 订单号：{{order_no}}
- 支付金额：¥{{amount}}
- 支付方式：{{pay_type}}
- 交易号：{{trade_no}}
- 支付时间：{{pay_time}}

我们将尽快为您发货，请耐心等待。

您可以在"我的订单"中查看订单状态。

{{site_name}} 团队', NOW(), NOW()),

('order_shipped', '订单发货', '管理员发货后通知用户', 1, '您的订单{{order_no}}已发货', 
'亲爱的{{username}}：

您的订单已发货！

发货信息：
- 订单号：{{order_no}}
- 发货时间：{{updated_at}}
- 物流公司：{{express_company}}
- 运单号：{{tracking_number}}

您可以通过运单号查询物流信息。

商品正在向您飞奔而来，请注意查收！

{{site_name}} 团队', NOW(), NOW()),

('order_completed', '订单完成', '订单确认收货后发送', 1, '订单{{order_no}}已完成', 
'亲爱的{{username}}：

您的订单已确认完成！

订单信息：
- 订单号：{{order_no}}
- 完成时间：{{updated_at}}
- 订单金额：¥{{total_amount}}

感谢您的信任与支持！
欢迎您对我们的商品和服务进行评价。

期待您的再次光临！

{{site_name}} 团队', NOW(), NOW()),

('order_cancelled', '订单取消', '订单取消后通知用户', 1, '订单{{order_no}}已取消', 
'亲爱的{{username}}：

您的订单已取消。

取消信息：
- 订单号：{{order_no}}
- 取消时间：{{updated_at}}
- 取消原因：{{cancel_reason}}

如有疑问，请联系客服。

{{site_name}} 团队', NOW(), NOW()),

('password_reset', '密码重置', '用户重置密码后发送', 1, '您的密码已重置', 
'亲爱的{{username}}：

您的密码已成功重置。

重置信息：
- 重置时间：{{reset_time}}
- 操作IP：{{ip_address}}

为了账户安全，建议您：
1. 定期更换密码
2. 不要使用过于简单的密码
3. 不要在公共场所保存密码

如有异常操作，请及时联系客服。

{{site_name}} 团队', NOW(), NOW()),

('low_stock', '库存不足', '商品库存不足时通知管理员', 0, '商品库存不足提醒', 
'管理员您好！

以下商品库存不足，请及时补货：

{{#products}}
商品：{{product_name}}
当前库存：{{stock}}
预警库存：{{warning_stock}}
商品链接：{{product_url}}
{{/products}}

请及时处理库存补充事宜。

系统自动发送', NOW(), NOW());