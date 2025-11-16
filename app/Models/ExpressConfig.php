<?php

namespace App\Models;

use Core\Model;

/**
 * 快递配置模型
 */
class ExpressConfig extends Model
{
    protected $table = 'express_configs';
    
    /**
     * 获取所有启用的快递配置
     * 
     * @return array
     */
    public function getActiveConfigs()
    {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table}
            WHERE status = 1
            ORDER BY sort_order ASC, id ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * 根据快递代码获取配置
     * 
     * @param string $expressCode 快递公司代码
     * @return array|null
     */
    public function getByExpressCode($expressCode)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table}
            WHERE express_code = ? AND status = 1
            LIMIT 1
        ");
        $stmt->execute([$expressCode]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * 创建或更新配置
     * 
     * @param array $data 配置数据
     * @return int 配置ID
     */
    public function save($data)
    {
        if (isset($data['id']) && $data['id']) {
            // 更新
            $fields = [];
            $values = [];
            
            $allowedFields = [
                'express_name', 'partner_id', 'checkword', 'sender_name',
                'sender_mobile', 'sender_province', 'sender_city', 'sender_county',
                'sender_address', 'monthly_account', 'sandbox_mode', 'status',
                'sort_order', 'remark'
            ];
            
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $fields[] = "{$field} = ?";
                    $values[] = $data[$field];
                }
            }
            
            $values[] = $data['id'];
            
            $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($values);
            
            return $data['id'];
        } else {
            // 新增
            $stmt = $this->db->prepare("
                INSERT INTO {$this->table} 
                (express_code, express_name, partner_id, checkword, sender_name, sender_mobile,
                 sender_province, sender_city, sender_county, sender_address, monthly_account,
                 sandbox_mode, status, sort_order, remark)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['express_code'],
                $data['express_name'],
                $data['partner_id'],
                $data['checkword'],
                $data['sender_name'],
                $data['sender_mobile'],
                $data['sender_province'],
                $data['sender_city'],
                $data['sender_county'],
                $data['sender_address'],
                $data['monthly_account'] ?? null,
                $data['sandbox_mode'] ?? 1,
                $data['status'] ?? 1,
                $data['sort_order'] ?? 0,
                $data['remark'] ?? null
            ]);
            
            return $this->db->lastInsertId();
        }
    }
    
    /**
     * 切换状态
     * 
     * @param int $id 配置ID
     * @param int $status 状态
     * @return bool
     */
    public function toggleStatus($id, $status)
    {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }
    
    /**
     * 删除配置
     * 
     * @param int $id 配置ID
     * @return bool
     */
    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * 获取快递公司列表（用于下拉选择）
     * 
     * @return array
     */
    public static function getExpressCompanies()
    {
        return [
            'SF' => '顺丰速运',
            'YTO' => '圆通速递',
            'ZTO' => '中通快递',
            'STO' => '申通快递',
            'YD' => '韵达快递',
            'HTKY' => '百世快递',
            'JD' => '京东物流',
            'EMS' => 'EMS',
            'YZPY' => '邮政快递包裹',
            'DBL' => '德邦快递',
        ];
    }
}
