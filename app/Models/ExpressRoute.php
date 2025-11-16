<?php

namespace App\Models;

use Core\Model;

/**
 * 快递路由模型（物流轨迹）
 */
class ExpressRoute extends Model
{
    protected $table = 'express_routes';
    
    /**
     * 批量添加路由
     * 
     * @param int $expressOrderId 快递订单ID
     * @param string $waybillNo 运单号
     * @param array $routes 路由数据数组
     * @return bool
     */
    public function batchAdd($expressOrderId, $waybillNo, $routes)
    {
        if (empty($routes)) {
            return false;
        }
        
        // 先删除旧路由
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE express_order_id = ?");
        $stmt->execute([$expressOrderId]);
        
        // 插入新路由
        $sql = "INSERT INTO {$this->table} 
                (express_order_id, waybill_no, route_time, route_desc, route_code, location, operator)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($routes as $route) {
            $stmt->execute([
                $expressOrderId,
                $waybillNo,
                $route['route_time'] ?? date('Y-m-d H:i:s'),
                $route['route_desc'] ?? '',
                $route['route_code'] ?? null,
                $route['location'] ?? null,
                $route['operator'] ?? null
            ]);
        }
        
        return true;
    }
    
    /**
     * 获取路由列表
     * 
     * @param int $expressOrderId 快递订单ID
     * @return array
     */
    public function getByExpressOrderId($expressOrderId)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table}
            WHERE express_order_id = ?
            ORDER BY route_time DESC, id DESC
        ");
        $stmt->execute([$expressOrderId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * 获取最新路由
     * 
     * @param int $expressOrderId 快递订单ID
     * @return array|null
     */
    public function getLatest($expressOrderId)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table}
            WHERE express_order_id = ?
            ORDER BY route_time DESC, id DESC
            LIMIT 1
        ");
        $stmt->execute([$expressOrderId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}
