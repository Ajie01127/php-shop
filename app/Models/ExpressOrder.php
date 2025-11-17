<?php

namespace App\Models;

use Core\Model;

/**
 * 快递订单模型
 */
class ExpressOrder extends Model
{
    protected $table = 'express_orders';
    
    // 状态常量
    const STATUS_CREATED = 1;      // 已下单
    const STATUS_COLLECTED = 2;    // 已揽收
    const STATUS_TRANSPORTING = 3; // 运输中
    const STATUS_DELIVERING = 4;   // 派送中
    const STATUS_SIGNED = 5;       // 已签收
    const STATUS_CANCELLED = 6;    // 已取消
    const STATUS_EXCEPTION = 7;    // 异常
    
    /**
     * 创建快递订单
     * 
     * @param array $data 订单数据
     * @return int 快递订单ID
     */
    public function createExpressOrder($data)
    {
        $stmt = $this->db->prepare("
            INSERT INTO {$this->table}
            (order_id, order_no, express_code, express_name, waybill_no, express_type, pay_method,
             cargo_name, cargo_count, cargo_unit, weight, volume,
             consignee_name, consignee_mobile, consignee_province, consignee_city, consignee_county, consignee_address,
             sender_name, sender_mobile, sender_province, sender_city, sender_county, sender_address,
             status, status_desc, api_request, api_response)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $data['order_id'],
            $data['order_no'],
            $data['express_code'],
            $data['express_name'],
            $data['waybill_no'] ?? null,
            $data['express_type'] ?? 1,
            $data['pay_method'] ?? 1,
            $data['cargo_name'] ?? '商品',
            $data['cargo_count'] ?? 1,
            $data['cargo_unit'] ?? '件',
            $data['weight'] ?? null,
            $data['volume'] ?? null,
            $data['consignee_name'],
            $data['consignee_mobile'],
            $data['consignee_province'],
            $data['consignee_city'],
            $data['consignee_county'],
            $data['consignee_address'],
            $data['sender_name'],
            $data['sender_mobile'],
            $data['sender_province'],
            $data['sender_city'],
            $data['sender_county'],
            $data['sender_address'],
            $data['status'] ?? self::STATUS_CREATED,
            $data['status_desc'] ?? null,
            $data['api_request'] ?? null,
            $data['api_response'] ?? null
        ]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * 更新运单号
     * 
     * @param int $id 快递订单ID
     * @param string $waybillNo 运单号
     * @param array $response API响应
     * @return bool
     */
    public function updateWaybillNo($id, $waybillNo, $response = null)
    {
        $stmt = $this->db->prepare("
            UPDATE {$this->table}
            SET waybill_no = ?, api_response = ?, status = ?
            WHERE id = ?
        ");
        
        return $stmt->execute([
            $waybillNo,
            $response ? json_encode($response, JSON_UNESCAPED_UNICODE) : null,
            self::STATUS_CREATED,
            $id
        ]);
    }
    
    /**
     * 更新状态
     * 
     * @param int $id 快递订单ID
     * @param int $status 状态
     * @param string $statusDesc 状态描述
     * @return bool
     */
    public function updateStatus($id, $status, $statusDesc = null)
    {
        $stmt = $this->db->prepare("
            UPDATE {$this->table}
            SET status = ?, status_desc = ?
            WHERE id = ?
        ");
        
        return $stmt->execute([$status, $statusDesc, $id]);
    }
    
    /**
     * 记录错误
     * 
     * @param int $id 快递订单ID
     * @param string $errorMsg 错误信息
     * @return bool
     */
    public function recordError($id, $errorMsg)
    {
        $stmt = $this->db->prepare("
            UPDATE {$this->table}
            SET error_msg = ?, status = ?
            WHERE id = ?
        ");
        
        return $stmt->execute([$errorMsg, self::STATUS_EXCEPTION, $id]);
    }
    
    /**
     * 根据订单ID获取快递订单
     * 
     * @param int $orderId 订单ID
     * @return array|null
     */
    public function getByOrderId($orderId)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE order_id = ?");
        $stmt->execute([$orderId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * 根据运单号获取快递订单
     * 
     * @param string $waybillNo 运单号
     * @return array|null
     */
    public function getByWaybillNo($waybillNo)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE waybill_no = ?");
        $stmt->execute([$waybillNo]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * 获取快递订单列表
     * 
     * @param array $params 查询参数
     * @return array
     */
    public function getList($params = [])
    {
        $where = ['1=1'];
        $values = [];
        
        if (!empty($params['express_code'])) {
            $where[] = 'express_code = ?';
            $values[] = $params['express_code'];
        }
        
        if (!empty($params['status'])) {
            $where[] = 'status = ?';
            $values[] = $params['status'];
        }
        
        if (!empty($params['keyword'])) {
            $where[] = '(order_no LIKE ? OR waybill_no LIKE ? OR consignee_name LIKE ? OR consignee_mobile LIKE ?)';
            $keyword = '%' . $params['keyword'] . '%';
            $values[] = $keyword;
            $values[] = $keyword;
            $values[] = $keyword;
            $values[] = $keyword;
        }
        
        $page = $params['page'] ?? 1;
        $pageSize = $params['page_size'] ?? 20;
        $offset = ($page - 1) * $pageSize;
        
        // 获取总数
        $countSql = "SELECT COUNT(*) FROM {$this->table} WHERE " . implode(' AND ', $where);
        $stmt = $this->db->prepare($countSql);
        $stmt->execute($values);
        $total = $stmt->fetchColumn();
        
        // 获取列表
        $sql = "SELECT * FROM {$this->table} 
                WHERE " . implode(' AND ', $where) . "
                ORDER BY id DESC
                LIMIT ?, ?";
        $values[] = $offset;
        $values[] = $pageSize;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);
        $list = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        return [
            'total' => $total,
            'page' => $page,
            'page_size' => $pageSize,
            'list' => $list
        ];
    }
    
    /**
     * 获取状态列表
     * 
     * @return array
     */
    public static function getStatusList()
    {
        return [
            self::STATUS_CREATED => '已下单',
            self::STATUS_COLLECTED => '已揽收',
            self::STATUS_TRANSPORTING => '运输中',
            self::STATUS_DELIVERING => '派送中',
            self::STATUS_SIGNED => '已签收',
            self::STATUS_CANCELLED => '已取消',
            self::STATUS_EXCEPTION => '异常'
        ];
    }
    
    /**
     * 获取状态名称
     * 
     * @param int $status 状态
     * @return string
     */
    public static function getStatusName($status)
    {
        $list = self::getStatusList();
        return $list[$status] ?? '未知';
    }
}
