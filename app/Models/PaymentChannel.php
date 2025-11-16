<?php

namespace App\Models;

use Core\Model;

class PaymentChannel extends Model
{
    protected $table = 'payment_channels';

    /**
     * 获取所有支付通道
     */
    public function getAll($type = null, $activeOnly = false)
    {
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];

        if ($type) {
            $sql .= " AND type = ?";
            $params[] = $type;
        }

        if ($activeOnly) {
            $sql .= " AND is_active = 1";
        }

        $sql .= " ORDER BY is_default DESC, id ASC";

        return $this->db->query($sql, $params);
    }

    /**
     * 获取默认支付通道
     */
    public function getDefault($type = 'wechat')
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE type = ? AND is_active = 1 AND is_default = 1 
                LIMIT 1";
        
        $result = $this->db->query($sql, [$type]);
        
        // 如果没有默认通道，返回第一个启用的通道
        if (empty($result)) {
            $sql = "SELECT * FROM {$this->table} 
                    WHERE type = ? AND is_active = 1 
                    ORDER BY id ASC LIMIT 1";
            $result = $this->db->query($sql, [$type]);
        }

        return $result[0] ?? null;
    }

    /**
     * 根据ID获取支付通道
     */
    public function getById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        $result = $this->db->query($sql, [$id]);
        return $result[0] ?? null;
    }

    /**
     * 创建支付通道
     */
    public function create($data)
    {
        // 如果设置为默认，先取消其他默认通道
        if (!empty($data['is_default'])) {
            $this->clearDefault($data['type']);
        }

        $sql = "INSERT INTO {$this->table} 
                (name, type, app_id, mch_id, api_key, cert_path, key_path, 
                 notify_url, is_active, is_default, config, remark) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        return $this->db->insert($sql, [
            $data['name'],
            $data['type'] ?? 'wechat',
            $data['app_id'],
            $data['mch_id'],
            $data['api_key'],
            $data['cert_path'] ?? null,
            $data['key_path'] ?? null,
            $data['notify_url'] ?? null,
            $data['is_active'] ?? 1,
            $data['is_default'] ?? 0,
            $data['config'] ?? null,
            $data['remark'] ?? null,
        ]);
    }

    /**
     * 更新支付通道
     */
    public function updateChannel($id, $data)
    {
        // 如果设置为默认，先取消其他默认通道
        if (!empty($data['is_default'])) {
            $channel = $this->getById($id);
            if ($channel) {
                $this->clearDefault($channel['type']);
            }
        }

        $sql = "UPDATE {$this->table} SET 
                name = ?, app_id = ?, mch_id = ?, api_key = ?, 
                cert_path = ?, key_path = ?, notify_url = ?,
                is_active = ?, is_default = ?, config = ?, remark = ?
                WHERE id = ?";

        return $this->db->update($sql, [
            $data['name'],
            $data['app_id'],
            $data['mch_id'],
            $data['api_key'],
            $data['cert_path'] ?? null,
            $data['key_path'] ?? null,
            $data['notify_url'] ?? null,
            $data['is_active'] ?? 1,
            $data['is_default'] ?? 0,
            $data['config'] ?? null,
            $data['remark'] ?? null,
            $id
        ]);
    }

    /**
     * 删除支付通道
     */
    public function deleteChannel($id)
    {
        // 检查是否有订单使用此通道
        $sql = "SELECT COUNT(*) as count FROM orders WHERE payment_channel_id = ?";
        $result = $this->db->query($sql, [$id]);
        
        if ($result[0]['count'] > 0) {
            return ['success' => false, 'message' => '该支付通道已被使用，无法删除'];
        }

        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $this->db->delete($sql, [$id]);
        
        return ['success' => true, 'message' => '删除成功'];
    }

    /**
     * 设置默认通道
     */
    public function setDefault($id)
    {
        $channel = $this->getById($id);
        if (!$channel) {
            return false;
        }

        // 取消同类型的其他默认通道
        $this->clearDefault($channel['type']);

        // 设置为默认
        $sql = "UPDATE {$this->table} SET is_default = 1 WHERE id = ?";
        return $this->db->update($sql, [$id]);
    }

    /**
     * 取消默认通道
     */
    private function clearDefault($type)
    {
        $sql = "UPDATE {$this->table} SET is_default = 0 WHERE type = ?";
        $this->db->update($sql, [$type]);
    }

    /**
     * 切换启用状态
     */
    public function toggleActive($id)
    {
        $sql = "UPDATE {$this->table} SET is_active = IF(is_active = 1, 0, 1) WHERE id = ?";
        return $this->db->update($sql, [$id]);
    }

    /**
     * 获取通道统计
     */
    public function getStats($channelId)
    {
        $sql = "SELECT 
                COUNT(*) as total_orders,
                SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid_orders,
                SUM(CASE WHEN status = 'paid' THEN total_amount ELSE 0 END) as total_amount
                FROM orders 
                WHERE payment_channel_id = ?";
        
        $result = $this->db->query($sql, [$channelId]);
        return $result[0] ?? null;
    }
}
