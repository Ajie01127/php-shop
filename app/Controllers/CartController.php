<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;
use App\Models\Product;

class CartController extends Controller {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * 购物车页面
     */
    public function index() {
        $this->requireAuth();
        
        $userId = $_SESSION['user_id'];
        
        // 获取购物车商品
        $sql = "SELECT c.*, p.name, p.price, p.stock, p.images, p.status
                FROM cart c
                LEFT JOIN products p ON c.product_id = p.id
                WHERE c.user_id = ?
                ORDER BY c.created_at DESC";
        
        $cartItems = $this->db->select($sql, [$userId]);
        
        // 解析图片
        foreach ($cartItems as &$item) {
            $images = json_decode($item['images'], true);
            $item['image'] = $images[0] ?? '';
        }
        
        $this->view('cart/index', [
            'cartItems' => $cartItems,
        ]);
    }
    
    /**
     * 添加到购物车
     */
    public function add() {
        $this->requireAuth();
        
        $productId = post('product_id');
        $quantity = post('quantity', 1);
        $userId = $_SESSION['user_id'];
        
        // 检查商品
        $productModel = new Product();
        $product = $productModel->find($productId);
        
        if (!$product || $product['status'] != 1) {
            $this->error('商品不存在');
        }
        
        if ($product['stock'] < $quantity) {
            $this->error('库存不足');
        }
        
        // 检查是否已在购物车
        $existing = $this->db->selectOne(
            "SELECT * FROM cart WHERE user_id = ? AND product_id = ?",
            [$userId, $productId]
        );
        
        if ($existing) {
            // 更新数量
            $this->db->update(
                "UPDATE cart SET quantity = quantity + ? WHERE id = ?",
                [$quantity, $existing['id']]
            );
        } else {
            // 添加新记录
            $this->db->insert(
                "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)",
                [$userId, $productId, $quantity]
            );
        }
        
        $this->success('已添加到购物车');
    }
    
    /**
     * 更新购物车
     */
    public function update() {
        $this->requireAuth();
        
        $cartId = post('cart_id');
        $quantity = post('quantity', 1);
        
        if ($quantity < 1) {
            $this->error('数量不能小于1');
        }
        
        $this->db->update(
            "UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?",
            [$quantity, $cartId, $_SESSION['user_id']]
        );
        
        $this->success('更新成功');
    }
    
    /**
     * 移除商品
     */
    public function remove() {
        $this->requireAuth();
        
        $cartId = post('cart_id');
        
        $this->db->delete(
            "DELETE FROM cart WHERE id = ? AND user_id = ?",
            [$cartId, $_SESSION['user_id']]
        );
        
        $this->success('已移除');
    }
}
