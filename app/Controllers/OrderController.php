<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;
use App\Models\Order;
use App\Models\Product;

class OrderController extends Controller {
    private $orderModel;
    private $db;
    
    public function __construct() {
        $this->orderModel = new Order();
        $this->db = Database::getInstance();
    }
    
    /**
     * 结算页面
     */
    public function checkout() {
        $this->requireAuth();
        
        $userId = $_SESSION['user_id'];
        
        // 获取购物车商品
        $sql = "SELECT c.*, p.name, p.price, p.stock, p.images
                FROM cart c
                LEFT JOIN products p ON c.product_id = p.id
                WHERE c.user_id = ? AND p.status = 1";
        
        $cartItems = $this->db->select($sql, [$userId]);
        
        if (empty($cartItems)) {
            flash('error', '购物车为空');
            $this->redirect('/cart');
        }
        
        // 获取默认地址
        $address = $this->db->selectOne(
            "SELECT * FROM addresses WHERE user_id = ? AND is_default = 1 LIMIT 1",
            [$userId]
        );
        
        // 计算总价
        $totalAmount = 0;
        foreach ($cartItems as $item) {
            $totalAmount += $item['price'] * $item['quantity'];
        }
        
        $this->view('orders/checkout', [
            'cartItems' => $cartItems,
            'address' => $address,
            'totalAmount' => $totalAmount,
        ]);
    }
    
    /**
     * 创建订单
     */
    public function create() {
        $this->requireAuth();
        
        $userId = $_SESSION['user_id'];
        $addressId = post('address_id');
        $remark = post('remark');
        
        // 获取购物车商品
        $sql = "SELECT c.*, p.name, p.price, p.stock, p.images
                FROM cart c
                LEFT JOIN products p ON c.product_id = p.id
                WHERE c.user_id = ? AND p.status = 1";
        
        $cartItems = $this->db->select($sql, [$userId]);
        
        if (empty($cartItems)) {
            $this->error('购物车为空');
        }
        
        // 检查库存
        $productModel = new Product();
        foreach ($cartItems as $item) {
            if ($item['stock'] < $item['quantity']) {
                $this->error("商品《{$item['name']}》库存不足");
            }
        }
        
        // 计算总价
        $totalAmount = 0;
        $orderItems = [];
        foreach ($cartItems as $item) {
            $totalAmount += $item['price'] * $item['quantity'];
            $images = json_decode($item['images'], true);
            $orderItems[] = [
                'product_id' => $item['product_id'],
                'product_name' => $item['name'],
                'product_image' => $images[0] ?? '',
                'price' => $item['price'],
                'quantity' => $item['quantity'],
                'total_price' => $item['price'] * $item['quantity'],
            ];
        }
        
        // 创建订单
        $orderId = $this->orderModel->createOrder([
            'user_id' => $userId,
            'total_amount' => $totalAmount,
            'pay_amount' => $totalAmount,
            'status' => 'pending',
            'remark' => $remark,
        ], $orderItems);
        
        if ($orderId) {
            // 扣减库存
            foreach ($cartItems as $item) {
                $productModel->deductStock($item['product_id'], $item['quantity']);
            }
            
            // 清空购物车
            $this->db->delete("DELETE FROM cart WHERE user_id = ?", [$userId]);
            
            // 发送订单创建邮件通知
            $this->sendOrderCreatedEmail($orderId, $userId, $orderItems, $totalAmount);
            
            // 直接跳转到支付页面
            $this->success('订单创建成功', [
                'order_id' => $orderId,
                'redirect' => "/payment/pay?order_id={$orderId}"
            ]);
        } else {
            $this->error('订单创建失败');
        }
    }
    
    /**
     * 用户订单列表
     */
    public function userOrders() {
        $this->requireAuth();
        
        $userId = $_SESSION['user_id'];
        $status = get('status');
        
        $orders = $this->orderModel->getUserOrders($userId, $status);
        
        // 获取订单项
        foreach ($orders as &$order) {
            $order['items'] = $this->db->select(
                "SELECT * FROM order_items WHERE order_id = ?",
                [$order['id']]
            );
        }
        
        $this->view('orders/list', [
            'orders' => $orders,
            'status' => $status,
        ]);
    }
    
    /**
     * 发送订单创建邮件通知
     */
    private function sendOrderCreatedEmail($orderId, $userId, $orderItems, $totalAmount)
    {
        try {
            // 获取用户信息
            $user = $this->db->selectOne(
                "SELECT id, username, email FROM users WHERE id = ?",
                [$userId]
            );
            
            if (!$user || empty($user['email'])) {
                return;
            }
            
            // 获取订单信息
            $order = $this->db->selectOne(
                "SELECT * FROM orders WHERE id = ?",
                [$orderId]
            );
            
            // 发送邮件通知
            $emailNotificationService = new \App\Services\EmailNotificationService();
            $emailNotificationService->triggerNotification('order_created', [
                'user_id' => $userId,
                'email' => $user['email'],
                'username' => $user['username'],
                'order_no' => $order['order_no'],
                'order_id' => $orderId,
                'total_amount' => $totalAmount,
                'items' => $orderItems,
                'created_at' => $order['created_at']
            ]);
            
        } catch (\Exception $e) {
            // 邮件发送失败不影响主流程
            error_log('Order creation email failed: ' . $e->getMessage());
        }
    }
}
