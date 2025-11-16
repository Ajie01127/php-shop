<?php

namespace App\Models;

use Core\Model;

class FreightTemplate extends Model
{
    protected $table = 'freight_templates';

    /**
     * 获取所有运费模板
     */
    public function getAll()
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY is_default DESC, sort ASC";
        return $this->db->query($sql);
    }

    /**
     * 根据ID获取模板
     */
    public function getById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        $result = $this->db->query($sql, [$id]);
        return $result[0] ?? null;
    }

    /**
     * 获取默认模板
     */
    public function getDefault()
    {
        $sql = "SELECT * FROM {$this->table} WHERE is_default = 1 LIMIT 1";
        $result = $this->db->query($sql);
        
        if (empty($result)) {
            $sql = "SELECT * FROM {$this->table} ORDER BY id ASC LIMIT 1";
            $result = $this->db->query($sql);
        }
        
        return $result[0] ?? null;
    }

    /**
     * 获取模板详情
     */
    public function getDetails($templateId)
    {
        $sql = "SELECT * FROM freight_template_details WHERE template_id = ?";
        return $this->db->query($sql, [$templateId]);
    }

    /**
     * 创建模板
     */
    public function create($data)
    {
        // 如果设为默认，先取消其他默认
        if (!empty($data['is_default'])) {
            $this->clearDefault();
        }

        $sql = "INSERT INTO {$this->table} 
                (name, type, is_free, free_amount, free_num, sort, is_default) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        return $this->db->insert($sql, [
            $data['name'],
            $data['type'] ?? 'weight',
            $data['is_free'] ?? 0,
            $data['free_amount'] ?? 0,
            $data['free_num'] ?? 0,
            $data['sort'] ?? 0,
            $data['is_default'] ?? 0,
        ]);
    }

    /**
     * 更新模板
     */
    public function updateTemplate($id, $data)
    {
        if (!empty($data['is_default'])) {
            $this->clearDefault();
        }

        $sql = "UPDATE {$this->table} SET 
                name = ?, type = ?, is_free = ?, free_amount = ?, 
                free_num = ?, sort = ?, is_default = ?
                WHERE id = ?";

        return $this->db->update($sql, [
            $data['name'],
            $data['type'],
            $data['is_free'],
            $data['free_amount'],
            $data['free_num'],
            $data['sort'],
            $data['is_default'],
            $id
        ]);
    }

    /**
     * 删除模板
     */
    public function deleteTemplate($id)
    {
        // 检查是否有商品使用
        $sql = "SELECT COUNT(*) as count FROM products WHERE freight_template_id = ?";
        $result = $this->db->query($sql, [$id]);
        
        if ($result[0]['count'] > 0) {
            return ['success' => false, 'message' => '有商品正在使用此模板，无法删除'];
        }

        // 删除模板详情
        $sql = "DELETE FROM freight_template_details WHERE template_id = ?";
        $this->db->delete($sql, [$id]);

        // 删除模板
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $this->db->delete($sql, [$id]);

        return ['success' => true, 'message' => '删除成功'];
    }

    /**
     * 取消默认
     */
    private function clearDefault()
    {
        $sql = "UPDATE {$this->table} SET is_default = 0";
        $this->db->update($sql);
    }

    /**
     * 计算运费
     */
    public function calculateFreight($templateId, $weight, $volume, $quantity, $amount, $provinceCode = null)
    {
        $template = $this->getById($templateId);
        if (!$template) {
            return 0;
        }

        // 判断是否包邮
        if ($template['is_free']) {
            return 0;
        }

        // 判断满额包邮
        if ($template['free_amount'] > 0 && $amount >= $template['free_amount']) {
            return 0;
        }

        // 判断满件包邮
        if ($template['free_num'] > 0 && $quantity >= $template['free_num']) {
            return 0;
        }

        // 获取运费详情
        $details = $this->getDetails($templateId);
        if (empty($details)) {
            return 0;
        }

        // 查找匹配的运费规则
        $matchedDetail = null;
        foreach ($details as $detail) {
            if ($detail['area_type'] === 'all') {
                $matchedDetail = $detail;
                break;
            }
            
            if ($provinceCode && $detail['area_type'] === 'include') {
                $areaCodes = json_decode($detail['area_codes'], true);
                if (in_array($provinceCode, $areaCodes)) {
                    $matchedDetail = $detail;
                    break;
                }
            }
        }

        if (!$matchedDetail) {
            return 0;
        }

        // 根据计费方式计算运费
        $unit = 0;
        switch ($template['type']) {
            case 'weight':
                $unit = $weight;
                break;
            case 'volume':
                $unit = $volume;
                break;
            case 'piece':
                $unit = $quantity;
                break;
        }

        $freight = $matchedDetail['first_price'];
        
        if ($unit > $matchedDetail['first_unit']) {
            $continueUnit = ceil(($unit - $matchedDetail['first_unit']) / $matchedDetail['continue_unit']);
            $freight += $continueUnit * $matchedDetail['continue_price'];
        }

        return max(0, $freight);
    }
}
