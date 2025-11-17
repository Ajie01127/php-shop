<?php

namespace App\Models;

use Core\Model;

class EmailNotification extends Model
{
    protected $table = 'email_notifications';
    
    /**
     * 获取所有通知场景
     */
    public function getAllNotifications($enabledOnly = false)
    {
        $sql = "SELECT * FROM {$this->getTableName()}";
        
        if ($enabledOnly) {
            $sql .= " WHERE is_enabled = 1";
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * 根据事件类型获取通知配置
     */
    public function getByEventType($eventType)
    {
        $sql = "SELECT * FROM {$this->getTableName()} WHERE event_type = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$eventType]);
        
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * 更新通知配置
     */
    public function updateNotification($eventType, $data)
    {
        // 解析收件人列表
        $recipients = null;
        if (isset($data['recipients'])) {
            if (is_array($data['recipients'])) {
                $recipients = json_encode(array_filter($data['recipients']));
            } else {
                $recipients = json_encode(explode(',', $data['recipients']));
            }
        }
        
        $sql = "UPDATE {$this->getTableName()} SET 
                name = ?, description = ?, template_subject = ?, 
                template_content = ?, is_enabled = ?, recipient_type = ?, 
                recipients = ?, variables = ?, updated_at = NOW()
                WHERE event_type = ?";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            $data['name'],
            $data['description'],
            $data['template_subject'],
            $data['template_content'],
            $data['is_enabled'] ?? 1,
            $data['recipient_type'],
            $recipients,
            json_encode($data['variables'] ?? []),
            $eventType
        ]);
    }
    
    /**
     * 获取通知统计
     */
    public function getNotificationStats($eventType = null, $startDate = null, $endDate = null)
    {
        $sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN l.status = 'sent' THEN 1 ELSE 0 END) as sent,
                SUM(CASE WHEN l.status = 'failed' THEN 1 ELSE 0 END) as failed
                FROM mall_email_logs l";
        
        $params = [];
        
        if ($eventType) {
            $sql .= " WHERE l.event_type = ?";
            $params[] = $eventType;
            
            if ($startDate && $endDate) {
                $sql .= " AND l.created_at BETWEEN ? AND ?";
                $params[] = $startDate;
                $params[] = $endDate;
            }
        } elseif ($startDate && $endDate) {
            $sql .= " WHERE l.created_at BETWEEN ? AND ?";
            $params[] = $startDate;
            $params[] = $endDate;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * 批量更新通知状态
     */
    public function batchUpdateStatus($eventTypes, $enabled)
    {
        if (empty($eventTypes)) {
            return true;
        }
        
        $placeholders = str_repeat('?,', count($eventTypes) - 1) . '?';
        
        $sql = "UPDATE {$this->getTableName()} 
                SET is_enabled = ?, updated_at = NOW() 
                WHERE event_type IN ($placeholders)";
        
        $params = array_merge([$enabled], $eventTypes);
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * 获取可用的变量说明
     */
    public function getVariableDescriptions()
    {
        return [
            'user_register' => [
                'username' => '用户名',
                'email' => '邮箱',
                'created_at' => '注册时间',
                'ip_address' => 'IP地址'
            ],
            'user_login' => [
                'username' => '用户名',
                'login_time' => '登录时间',
                'ip_address' => '登录IP',
                'location' => '登录地点'
            ],
            'order_created' => [
                'username' => '用户名',
                'order_no' => '订单号',
                'total_amount' => '订单金额',
                'product_count' => '商品数量',
                'created_at' => '创建时间'
            ],
            'order_paid' => [
                'username' => '用户名',
                'order_no' => '订单号',
                'paid_amount' => '支付金额',
                'payment_method' => '支付方式',
                'paid_at' => '支付时间'
            ],
            'order_shipped' => [
                'username' => '用户名',
                'order_no' => '订单号',
                'express_company' => '快递公司',
                'tracking_number' => '快递单号',
                'shipped_at' => '发货时间'
            ],
            'order_completed' => [
                'username' => '用户名',
                'order_no' => '订单号',
                'total_amount' => '订单金额',
                'completed_at' => '完成时间'
            ],
            'payment_failed' => [
                'username' => '用户名',
                'order_no' => '订单号',
                'amount' => '金额',
                'error_message' => '失败原因',
                'failed_at' => '失败时间'
            ],
            'low_stock' => [
                'products' => '商品列表（格式：{{#products}}商品名称 - 库存数量{{/products}}）'
            ],
            'system_error' => [
                'error_type' => '错误类型',
                'error_message' => '错误信息',
                'error_time' => '错误时间',
                'request_uri' => '请求URI',
                'ip_address' => '用户IP'
            ]
        ];
    }
    
    /**
     * 复制通知模板
     */
    public function duplicateTemplate($sourceEventType, $newEventType)
    {
        $source = $this->getByEventType($sourceEventType);
        
        if (!$source) {
            return false;
        }
        
        $sql = "INSERT INTO {$this->getTableName()} 
                (event_type, name, description, template_subject, template_content, 
                 is_enabled, recipient_type, recipients, variables, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            $newEventType,
            $source['name'] . ' (副本)',
            $source['description'],
            $source['template_subject'],
            $source['template_content'],
            0, // 新建模板默认不启用
            $source['recipient_type'],
            $source['recipients'],
            $source['variables']
        ]);
    }
}