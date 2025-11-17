# 邮箱通知功能使用指南

## 📧 功能概述

本系统已集成完整的邮箱通知功能，支持多种业务场景的邮件通知，管理员可以在后台灵活配置各种通知场景。

## 🚀 快速开始

### 1. 安装依赖

```bash
composer install
```

确保 `composer.json` 中已添加：
```json
{
    "require": {
        "phpmailer/phpmailer": "^6.8"
    }
}
```

### 2. 导入数据库结构

执行以下SQL文件创建邮箱相关数据表：
```sql
-- 导入文件
source database/migrations/add_email_notifications.sql
```

### 3. 配置路由

将 `email_routes_patch.php` 中的路由添加到 `config/routes.php` 文件中。

### 4. 配置邮箱服务

访问管理后台 `/admin/email/config` 配置SMTP邮箱服务。

## 📋 支持的通知场景

| 场景类型 | 描述 | 默认接收者 | 可用变量 |
|---------|------|-----------|---------|
| `user_register` | 用户注册 | 管理员 | username, email, created_at, ip_address |
| `user_login` | 用户登录 | 用户 | username, login_time, ip_address, location |
| `order_created` | 订单创建 | 用户 | username, order_no, total_amount, product_count, created_at |
| `order_paid` | 订单支付 | 用户 | username, order_no, paid_amount, payment_method, paid_at |
| `order_shipped` | 订单发货 | 用户 | username, order_no, express_company, tracking_number, shipped_at |
| `order_completed` | 订单完成 | 用户 | username, order_no, total_amount, completed_at |
| `payment_failed` | 支付失败 | 用户 | username, order_no, amount, error_message, failed_at |
| `low_stock` | 库存不足 | 管理员 | products |
| `system_error` | 系统错误 | 管理员 | error_type, error_message, error_time, request_uri, ip_address |

## 🛠️ 业务集成示例

### 在控制器中使用

```php
use App\Services\EmailNotificationService;

class OrderController 
{
    private $emailService;
    
    public function __construct()
    {
        $this->emailService = new EmailNotificationService();
    }
    
    public function createOrder()
    {
        // 创建订单逻辑...
        $order = [
            'order_no' => 'ORD202401001',
            'total_amount' => '299.00',
            'user_email' => 'user@example.com',
            'username' => '测试用户'
        ];
        
        // 发送订单创建通知
        $result = $this->emailService->orderCreated($order);
        
        if ($result['success']) {
            echo "通知发送成功";
        }
    }
}
```

### 在模型中使用

```php
use App\Services\EmailNotificationService;

class User 
{
    private $emailService;
    
    public function __construct()
    {
        $this->emailService = new EmailNotificationService();
    }
    
    public function register($userData)
    {
        // 注册逻辑...
        $userId = $this->saveUser($userData);
        
        // 发送注册通知给管理员
        $this->emailService->userRegister($userData);
        
        return $userId;
    }
}
```

### 发送自定义通知

```php
$emailService = new EmailNotificationService();

// 方法1：使用预定义场景
$result = $emailService->sendNotification('system_error', [
    'error_type' => 'Database Error',
    'error_message' => 'Connection failed',
    'request_uri' => $_SERVER['REQUEST_URI']
]);

// 方法2：发送到指定邮箱
$result = $emailService->sendNotification('custom_event', [
    'message' => '自定义通知内容'
], 'admin@example.com');
```

## 📊 后台管理功能

### 邮箱配置 (`/admin/email/config`)
- SMTP服务器配置
- 多种邮箱服务商支持（Gmail、QQ、163等）
- 连接测试和发送测试
- 加密方式选择

### 通知场景管理 (`/admin/email/notifications`)
- 场景开关控制
- 邮件模板编辑
- 变量说明和快速插入
- 模板预览功能
- 批量操作

### 邮件日志 (`/admin/email/logs`)
- 发送记录查看
- 状态筛选（已发送/失败/待发送）
- 失败邮件重试
- 日志导出和清理
- 详细信息查看

### 统计分析 (`/admin/email/statistics`)
- 发送成功率统计
- 按事件类型分析
- 按日期趋势分析
- 错误原因统计

## 🔧 高级配置

### 环境变量配置

可以在 `.env` 文件中配置邮箱参数：

```env
# 默认邮箱配置
MAIL_DRIVER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_FROM_NAME=系统通知
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_ENABLED=true
```

### 队列支持（可选）

如果使用队列异步处理邮件：

```php
// 将邮件加入队列
Queue::push('EmailJob', [
    'to' => 'user@example.com',
    'subject' => 'Test',
    'content' => 'Hello World'
]);
```

## 📝 模板语法

### 基本变量

```html
<h3>欢迎 {{username}}!</h3>
<p>您的订单 {{order_no}} 已创建成功。</p>
```

### 条件渲染

```html
{{#is_vip}}
<p>尊敬的VIP会员，享受专属优惠！</p>
{{/is_vip}}
```

### 循环渲染

```html
<ul>
{{#products}}
<li>{{name}} - ¥{{price}}</li>
{{/products}}
</ul>
```

## 🛡️ 安全注意事项

1. **密码安全**：使用应用专用密码而非账户密码
2. **HTTPS**：生产环境务必使用HTTPS
3. **权限控制**：限制邮箱配置页面的访问权限
4. **日志管理**：定期清理邮件日志避免数据过多
5. **频率限制**：设置发送频率避免被服务商限制

## 🔍 故障排除

### 常见问题

1. **连接失败**
   - 检查SMTP服务器和端口
   - 确认用户名和密码正确
   - 检查防火墙设置

2. **认证失败**
   - 使用应用专用密码
   - 确认开启了SMTP服务
   - 检查加密方式设置

3. **发送失败**
   - 检查发送频率限制
   - 确认邮箱格式正确
   - 查看错误日志

### 调试模式

在 `config/config.php` 中开启调试：

```php
'app' => [
    'debug' => true,
    'env' => 'development',
],
```

## 📞 技术支持

如遇到问题，请：
1. 查看邮件日志获取详细错误信息
2. 检查系统配置是否正确
3. 确认服务商设置和限制

## 🔄 更新说明

### v1.0.0
- 初始版本发布
- 支持9种通知场景
- 完整的后台管理界面
- SMTP协议支持
- 邮件日志和统计功能