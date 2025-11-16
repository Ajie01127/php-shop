<?php

namespace App\Models;

use Core\Model;

class Order extends Model {
    protected $table = 'orders';
    
    /**
     * 创建订单
     */
    public function createOrder($orderData, $items) {
        $this->db->beginTransaction();
        
        try {
            // 生成订单号
            $orderData['order_no'] = generateOrderNo();
            $orderData['created_at'] = date('Y-m-d H:i:s');
            
            // 插入订单
            $orderId = $this->create($orderData);
            
            // 插入订单明细
            $orderItemModel = new OrderItem();
            foreach ($items as $item) {
                $item['order_id'] = $orderId;
                $orderItemModel->create($item);
            }
            
            $this->db->commit();
            return $orderId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    /**
     * 获取用户订单
     */
    public function getUserOrders($userId, $status = null) {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = ?";
        $params = [$userId];
        
        if ($status) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        return $this->db->select($sql, $params);
    }
    
    /**
     * 获取订单详情(含订单项)
     */
    public function getOrderDetail($id) {
        $order = $this->find($id);
        if ($order) {
            $sql = "SELECT * FROM order_items WHERE order_id = ?";
            $order['items'] = $this->db->select($sql, [$id]);
        }
        return $order;
    }
    
    /**
     * 更新订单状态
     */
    public function updateStatus($orderId, $status) {
        $data = ['status' => $status];
        
        switch ($status) {
            case 'paid':
                $data['paid_at'] = date('Y-m-d H:i:s');
                break;
            case 'shipped':
                $data['shipped_at'] = date('Y-m-d H:i:s');
                break;
            case 'completed':
                $data['completed_at'] = date('Y-m-d H:i:s');
                break;
            case 'cancelled':
                $data['cancelled_at'] = date('Y-m-d H:i:s');
                break;
        }
        
        return $this->update($orderId, $data);
    }
    
    /**
     * 获取订单统计
     */
    public function getStatistics($startDate = null, $endDate = null) {
        $sql = "SELECT 
                COUNT(*) as total_orders,
                SUM(total_amount) as total_sales,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
                SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid_orders,
                SUM(CASE WHEN status = 'shipped' THEN 1 ELSE 0 END) as shipped_orders,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_orders
                FROM {$this->table}";
        
        $params = [];
        if ($startDate && $endDate) {
            $sql .= " WHERE created_at BETWEEN ? AND ?";
            $params = [$startDate, $endDate];
        }
        
        return $this->db->selectOne($sql, $params);
    }
}
