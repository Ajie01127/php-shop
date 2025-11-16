<?php

namespace App\Models;

use Core\Model;

class ProductSku extends Model
{
    protected $table = 'product_skus';

    /**
     * 获取商品的所有SKU
     */
    public function getByProductId($productId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE product_id = ? ORDER BY is_default DESC, id ASC";
        return $this->db->query($sql, [$productId]);
    }

    /**
     * 根据SKU ID获取SKU
     */
    public function getById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        $result = $this->db->query($sql, [$id]);
        return $result[0] ?? null;
    }

    /**
     * 根据SKU编码获取SKU
     */
    public function getByCode($skuCode)
    {
        $sql = "SELECT * FROM {$this->table} WHERE sku_code = ?";
        $result = $this->db->query($sql, [$skuCode]);
        return $result[0] ?? null;
    }

    /**
     * 获取默认SKU
     */
    public function getDefault($productId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE product_id = ? AND is_default = 1 LIMIT 1";
        $result = $this->db->query($sql, [$productId]);
        
        if (empty($result)) {
            // 如果没有默认SKU，返回第一个
            $sql = "SELECT * FROM {$this->table} WHERE product_id = ? LIMIT 1";
            $result = $this->db->query($sql, [$productId]);
        }
        
        return $result[0] ?? null;
    }

    /**
     * 创建SKU
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} 
                (product_id, sku_code, spec_info, price, original_price, cost_price, 
                 stock, weight, volume, image, is_default, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        return $this->db->insert($sql, [
            $data['product_id'],
            $data['sku_code'],
            json_encode($data['spec_info'] ?? [], JSON_UNESCAPED_UNICODE),
            $data['price'],
            $data['original_price'] ?? null,
            $data['cost_price'] ?? null,
            $data['stock'] ?? 0,
            $data['weight'] ?? 0,
            $data['volume'] ?? 0,
            $data['image'] ?? null,
            $data['is_default'] ?? 0,
            $data['status'] ?? 1,
        ]);
    }

    /**
     * 更新SKU
     */
    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table} SET 
                spec_info = ?, price = ?, original_price = ?, cost_price = ?,
                stock = ?, weight = ?, volume = ?, image = ?, is_default = ?, status = ?
                WHERE id = ?";

        return $this->db->update($sql, [
            json_encode($data['spec_info'] ?? [], JSON_UNESCAPED_UNICODE),
            $data['price'],
            $data['original_price'] ?? null,
            $data['cost_price'] ?? null,
            $data['stock'] ?? 0,
            $data['weight'] ?? 0,
            $data['volume'] ?? 0,
            $data['image'] ?? null,
            $data['is_default'] ?? 0,
            $data['status'] ?? 1,
            $id
        ]);
    }

    /**
     * 删除商品的所有SKU
     */
    public function deleteByProductId($productId)
    {
        $sql = "DELETE FROM {$this->table} WHERE product_id = ?";
        return $this->db->delete($sql, [$productId]);
    }

    /**
     * 扣减库存
     */
    public function deductStock($id, $quantity)
    {
        $sql = "UPDATE {$this->table} SET stock = stock - ? WHERE id = ? AND stock >= ?";
        return $this->db->update($sql, [$quantity, $id, $quantity]);
    }

    /**
     * 增加库存
     */
    public function addStock($id, $quantity)
    {
        $sql = "UPDATE {$this->table} SET stock = stock + ? WHERE id = ?";
        return $this->db->update($sql, [$quantity, $id]);
    }

    /**
     * 获取SKU价格区间
     */
    public function getPriceRange($productId)
    {
        $sql = "SELECT MIN(price) as min_price, MAX(price) as max_price 
                FROM {$this->table} WHERE product_id = ? AND status = 1";
        $result = $this->db->query($sql, [$productId]);
        return $result[0] ?? null;
    }

    /**
     * 批量创建SKU
     */
    public function batchCreate($skus)
    {
        $this->db->beginTransaction();
        
        try {
            foreach ($skus as $sku) {
                $this->create($sku);
            }
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}
