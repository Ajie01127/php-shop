<?php

namespace App\Services;

use App\Models\EmailNotification;
use App\Models\EmailLog;

class EmailNotificationService
{
    private $emailService;
    private $emailNotification;
    
    public function __construct()
    {
        $this->emailService = new EmailService();
        $this->emailNotification = new EmailNotification();
    }
    
    /**
     * 发送事件通知
     */
    public function sendNotification($eventType, $variables = [], $recipientEmail = null)
    {
        try {
            // 获取通知配置
            $notification = $this->emailNotification->getByEventType($eventType);
            
            if (!$notification || !$notification['is_enabled']) {
                return ['success' => false, 'message' => '通知场景不存在或未启用'];
            }
            
            // 确定接收者
            $recipients = $this->getRecipients($notification, $variables, $recipientEmail);
            
            if (empty($recipients)) {
                return ['success' => false, 'message' => '没有找到接收者'];
            }
            
            $results = [];
            
            foreach ($recipients as $recipient) {
                $result = $this->emailService->sendTemplate($recipient, $eventType, $variables);
                $results[] = [
                    'recipient' => $recipient,
                    'result' => $result
                ];
            }
            
            // 统计发送结果
            $successCount = count(array_filter($results, function($r) {
                return $r['result']['success'];
            }));
            
            return [
                'success' => $successCount > 0,
                'message' => "共发送 " . count($results) . " 封邮件，成功 {$successCount} 封",
                'results' => $results
            ];
            
        } catch (\Exception $e) {
            return ['success' => false, 'message' => '发送通知失败：' . $e->getMessage()];
        }
    }
    
    /**
     * 获取接收者列表
     */
    private function getRecipients($notification, $variables, $customRecipient = null)
    {
        $recipients = [];
        
        switch ($notification['recipient_type']) {
            case 'admin':
                // 发送给所有管理员
                $recipients = $this->getAdminEmails();
                break;
                
            case 'user':
                // 发送给指定用户
                if ($customRecipient) {
                    $recipients[] = $customRecipient;
                } elseif (isset($variables['email'])) {
                    $recipients[] = $variables['email'];
                }
                break;
                
            case 'custom':
                // 发送给自定义邮箱列表
                if (!empty($notification['recipients'])) {
                    $recipientArray = json_decode($notification['recipients'], true);
                    if (is_array($recipientArray)) {
                        $recipients = array_filter($recipientArray, function($email) {
                            return filter_var($email, FILTER_VALIDATE_EMAIL);
                        });
                    }
                }
                break;
        }
        
        return array_unique($recipients);
    }
    
    /**
     * 获取管理员邮箱列表
     */
    private function getAdminEmails()
    {
        try {
            $pdo = new \PDO(
                "mysql:host=" . config('database.host') . ";dbname=" . config('database.database'),
                config('database.username'),
                config('database.password')
            );
            
            $stmt = $pdo->prepare("SELECT DISTINCT email FROM mall_users WHERE role = 'admin' AND email IS NOT NULL AND email != ''");
            $stmt->execute();
            
            $emails = [];
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                if (filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
                    $emails[] = $row['email'];
                }
            }
            
            return $emails;
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * 用户注册通知
     */
    public function userRegister($user)
    {
        $variables = [
            'username' => $user['username'] ?? $user['name'] ?? '',
            'email' => $user['email'] ?? '',
            'created_at' => date('Y-m-d H:i:s'),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '未知'
        ];
        
        return $this->sendNotification('user_register', $variables);
    }
    
    /**
     * 用户登录通知
     */
    public function userLogin($user)
    {
        $variables = [
            'username' => $user['username'] ?? $user['name'] ?? '',
            'login_time' => date('Y-m-d H:i:s'),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '未知',
            'location' => $this->getLocationByIP($_SERVER['REMOTE_ADDR'] ?? '')
        ];
        
        return $this->sendNotification('user_login', $variables, $user['email'] ?? '');
    }
    
    /**
     * 订单创建通知
     */
    public function orderCreated($order)
    {
        $variables = [
            'username' => $order['username'] ?? $order['user_name'] ?? '用户',
            'order_no' => $order['order_no'] ?? '',
            'total_amount' => $order['total_amount'] ?? '0.00',
            'product_count' => $order['product_count'] ?? count($order['items'] ?? []),
            'created_at' => $order['created_at'] ?? date('Y-m-d H:i:s')
        ];
        
        return $this->sendNotification('order_created', $variables, $order['user_email'] ?? '');
    }
    
    /**
     * 订单支付成功通知
     */
    public function orderPaid($order, $payment)
    {
        $variables = [
            'username' => $order['username'] ?? $order['user_name'] ?? '用户',
            'order_no' => $order['order_no'] ?? '',
            'paid_amount' => $payment['amount'] ?? $order['total_amount'] ?? '0.00',
            'payment_method' => $payment['method'] ?? $order['payment_method'] ?? '未知',
            'paid_at' => $payment['paid_at'] ?? date('Y-m-d H:i:s')
        ];
        
        return $this->sendNotification('order_paid', $variables, $order['user_email'] ?? '');
    }
    
    /**
     * 订单发货通知
     */
    public function orderShipped($order, $shipping)
    {
        $variables = [
            'username' => $order['username'] ?? $order['user_name'] ?? '用户',
            'order_no' => $order['order_no'] ?? '',
            'express_company' => $shipping['express_company'] ?? '',
            'tracking_number' => $shipping['tracking_number'] ?? '',
            'shipped_at' => $shipping['shipped_at'] ?? date('Y-m-d H:i:s')
        ];
        
        return $this->sendNotification('order_shipped', $variables, $order['user_email'] ?? '');
    }
    
    /**
     * 订单完成通知
     */
    public function orderCompleted($order)
    {
        $variables = [
            'username' => $order['username'] ?? $order['user_name'] ?? '用户',
            'order_no' => $order['order_no'] ?? '',
            'total_amount' => $order['total_amount'] ?? '0.00',
            'completed_at' => $order['completed_at'] ?? date('Y-m-d H:i:s')
        ];
        
        return $this->sendNotification('order_completed', $variables, $order['user_email'] ?? '');
    }
    
    /**
     * 支付失败通知
     */
    public function paymentFailed($order, $error)
    {
        $variables = [
            'username' => $order['username'] ?? $order['user_name'] ?? '用户',
            'order_no' => $order['order_no'] ?? '',
            'amount' => $error['amount'] ?? $order['total_amount'] ?? '0.00',
            'error_message' => $error['message'] ?? '支付失败',
            'failed_at' => $error['failed_at'] ?? date('Y-m-d H:i:s')
        ];
        
        return $this->sendNotification('payment_failed', $variables, $order['user_email'] ?? '');
    }
    
    /**
     * 库存不足通知
     */
    public function lowStock($products)
    {
        $variables = [
            'products' => array_map(function($product) {
                return "<li>{$product['name']} - 当前库存：{$product['stock']} 件</li>";
            }, $products)
        ];
        
        $variables['products'] = implode('', $variables['products']);
        
        return $this->sendNotification('low_stock', $variables);
    }
    
    /**
     * 系统错误通知
     */
    public function systemError($error)
    {
        $variables = [
            'error_type' => $error['type'] ?? '系统错误',
            'error_message' => $error['message'] ?? '未知错误',
            'error_time' => date('Y-m-d H:i:s'),
            'request_uri' => $_SERVER['REQUEST_URI'] ?? '',
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? ''
        ];
        
        return $this->sendNotification('system_error', $variables);
    }
    
    /**
     * 根据IP地址获取地理位置（简单实现）
     */
    private function getLocationByIP($ip)
    {
        if (empty($ip) || $ip === '127.0.0.1') {
            return '本地';
        }
        
        // 这里可以集成第三方IP地址查询API
        // 例如：https://ip.taobao.com/service/getIpInfo.php?ip={$ip}
        
        return '未知';
    }
    
    /**
     * 检查邮件服务是否可用
     */
    public function isServiceAvailable()
    {
        try {
            $pdo = new \PDO(
                "mysql:host=" . config('database.host') . ";dbname=" . config('database.database'),
                config('database.username'),
                config('database.password')
            );
            
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM mall_email_configs WHERE is_enabled = 1");
            $stmt->execute();
            $enabled = $stmt->fetchColumn();
            
            return $enabled > 0;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * 获取通知统计
     */
    public function getNotificationStats($days = 7)
    {
        return $this->emailNotification->getNotificationStats(null, 
            date('Y-m-d', strtotime("-{$days} days")), 
            date('Y-m-d')
        );
    }
}