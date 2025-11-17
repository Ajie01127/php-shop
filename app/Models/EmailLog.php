<?php

namespace App\Models;

use Core\Model;

class EmailLog extends Model
{
    protected $table = 'email_logs';
    
    /**
     * 获取邮件日志列表
     */
    public function getLogs($page = 1, $limit = 20, $filters = [])
    {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT l.* FROM {$this->getTableName()} l WHERE 1=1";
        $params = [];
        
        // 搜索条件
        if (!empty($filters['search'])) {
            $sql .= " AND (l.to_email LIKE ? OR l.subject LIKE ? OR l.event_type LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND l.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['event_type'])) {
            $sql .= " AND l.event_type = ?";
            $params[] = $filters['event_type'];
        }
        
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $sql .= " AND l.created_at BETWEEN ? AND ?";
            $params[] = $filters['start_date'] . ' 00:00:00';
            $params[] = $filters['end_date'] . ' 23:59:59';
        }
        
        // 排序
        $sql .= " ORDER BY l.created_at DESC";
        
        // 获取总数
        $countSql = str_replace("SELECT l.*", "SELECT COUNT(*)", $sql);
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetchColumn();
        
        // 分页
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return [
            'data' => $stmt->fetchAll(\PDO::FETCH_ASSOC),
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ];
    }
    
    /**
     * 获取单个日志详情
     */
    public function getLogById($id)
    {
        $sql = "SELECT l.*, 
                       e.event_type as event_name, e.name as notification_name
                FROM {$this->getTableName()} l
                LEFT JOIN {$this->getTableName('email_notifications')} e ON l.event_type = e.event_type
                WHERE l.id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * 删除日志
     */
    public function deleteLogs($ids)
    {
        if (!is_array($ids)) {
            $ids = [$ids];
        }
        
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        
        $sql = "DELETE FROM {$this->getTableName()} WHERE id IN ($placeholders)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($ids);
    }
    
    /**
     * 清空日志
     */
    public function clearAllLogs()
    {
        $sql = "TRUNCATE TABLE {$this->getTableName()}";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute();
    }
    
    /**
     * 清空过期日志
     */
    public function clearExpiredLogs($days = 30)
    {
        $sql = "DELETE FROM {$this->getTableName()} 
                WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$days]);
    }
    
    /**
     * 获取统计信息
     */
    public function getStatistics($startDate = null, $endDate = null)
    {
        $sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                COUNT(DISTINCT DATE(created_at)) as active_days
                FROM {$this->getTableName()}";
        
        $params = [];
        
        if ($startDate && $endDate) {
            $sql .= " WHERE created_at BETWEEN ? AND ?";
            $params[] = $startDate . ' 00:00:00';
            $params[] = $endDate . ' 23:59:59';
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        $stats = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        // 计算成功率
        if ($stats['total'] > 0) {
            $stats['success_rate'] = round(($stats['sent'] / $stats['total']) * 100, 2);
        } else {
            $stats['success_rate'] = 0;
        }
        
        return $stats;
    }
    
    /**
     * 按事件类型统计
     */
    public function getEventStatistics($limit = 10)
    {
        $sql = "SELECT 
                event_type,
                COUNT(*) as total,
                SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed
                FROM {$this->getTableName()}
                WHERE event_type IS NOT NULL
                GROUP BY event_type
                ORDER BY total DESC
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * 按日期统计
     */
    public function getDailyStatistics($days = 7)
    {
        $sql = "SELECT 
                DATE(created_at) as date,
                COUNT(*) as total,
                SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed
                FROM {$this->getTableName()}
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY DATE(created_at)
                ORDER BY date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$days]);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * 重试发送失败的邮件
     */
    public function retryFailedEmails($ids = null)
    {
        if ($ids) {
            if (!is_array($ids)) {
                $ids = [$ids];
            }
            
            $placeholders = str_repeat('?,', count($ids) - 1) . '?';
            $sql = "SELECT * FROM {$this->getTableName()} 
                    WHERE id IN ($placeholders) AND status = 'failed'";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($ids);
        } else {
            $sql = "SELECT * FROM {$this->getTableName()} 
                    WHERE status = 'failed' 
                    AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
        }
        
        $failedEmails = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $results = [];
        
        foreach ($failedEmails as $email) {
            try {
                $emailService = new \App\Services\EmailService();
                $result = $emailService->send($email['to_email'], $email['subject'], $email['content']);
                
                if ($result['success']) {
                    // 更新状态为已发送
                    $updateSql = "UPDATE {$this->getTableName()} 
                                  SET status = 'sent', sent_at = NOW(), error_message = NULL 
                                  WHERE id = ?";
                    $updateStmt = $this->db->prepare($updateSql);
                    $updateStmt->execute([$email['id']]);
                }
                
                $results[] = [
                    'id' => $email['id'],
                    'to_email' => $email['to_email'],
                    'success' => $result['success'],
                    'message' => $result['message']
                ];
                
            } catch (\Exception $e) {
                $results[] = [
                    'id' => $email['id'],
                    'to_email' => $email['to_email'],
                    'success' => false,
                    'message' => $e->getMessage()
                ];
            }
        }
        
        return $results;
    }
}