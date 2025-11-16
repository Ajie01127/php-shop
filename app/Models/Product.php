<?php

namespace App\Models;

use Core\Model;

class Product extends Model {
    protected $table = 'products';
    
    /**
     * 获取上架商品
     */
    public function getActiveProducts($limit = null) {
        $sql = "SELECT * FROM {$this->table} WHERE status = 1 ORDER BY sort_order DESC, created_at DESC";
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        return $this->db->select($sql);
    }
    
    /**
     * 根据分类获取商品
     */
    public function getByCategory($categoryId, $limit = null) {
        $sql = "SELECT * FROM {$this->table} WHERE category_id = ? AND status = 1 ORDER BY sort_order DESC";
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        return $this->db->select($sql, [$categoryId]);
    }
    
    /**
     * 搜索商品
     */
    public function search($keyword, $categoryId = null, $minPrice = null, $maxPrice = null, $orderBy = 'sales DESC') {
        $sql = "SELECT * FROM {$this->table} WHERE status = 1";
        $params = [];
        
        if ($keyword) {
            $sql .= " AND name LIKE ?";
            $params[] = "%{$keyword}%";
        }
        
        if ($categoryId) {
            $sql .= " AND category_id = ?";
            $params[] = $categoryId;
        }
        
        if ($minPrice !== null) {
            $sql .= " AND price >= ?";
            $params[] = $minPrice;
        }
        
        if ($maxPrice !== null) {
            $sql .= " AND price <= ?";
            $params[] = $maxPrice;
        }
        
        $sql .= " ORDER BY {$orderBy}";
        
        return $this->db->select($sql, $params);
    }
    
    /**
     * 获取热销商品
     */
    public function getHotProducts($limit = 10) {
        $sql = "SELECT * FROM {$this->table} WHERE status = 1 ORDER BY sales DESC LIMIT ?";
        return $this->db->select($sql, [$limit]);
    }
    
    /**
     * 更新库存
     */
    public function updateStock($productId, $quantity) {
        $sql = "UPDATE {$this->table} SET stock = stock + ? WHERE id = ?";
        return $this->db->update($sql, [$quantity, $productId]);
    }
    
    /**
     * 扣减库存
     */
    public function deductStock($productId, $quantity) {
        $sql = "UPDATE {$this->table} SET stock = stock - ?, sales = sales + ? WHERE id = ? AND stock >= ?";
        return $this->db->update($sql, [$quantity, $quantity, $productId, $quantity]);
    }
    
    /**
     * 获取商品详情(含分类信息)
     */
    public function getDetail($id) {
        $sql = "SELECT p.*, c.name as category_name 
                FROM {$this->table} p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.id = ?";
        return $this->db->selectOne($sql, [$id]);
    }
}
