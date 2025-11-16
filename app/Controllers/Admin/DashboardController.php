<?php

namespace App\Controllers\Admin;

use Core\Controller;
use Core\Database;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;

class DashboardController extends Controller {
    /**
     * 数据看板
     */
    public function index() {
        $db = Database::getInstance();
        $orderModel = new Order();
        $productModel = new Product();
        $userModel = new User();
        
        // 今日统计
        $today = date('Y-m-d');
        $todayStart = $today . ' 00:00:00';
        $todayEnd = $today . ' 23:59:59';
        
        $todayStats = $orderModel->getStatistics($todayStart, $todayEnd);
        
        // 总体统计
        $totalStats = [
            'total_users' => $userModel->count(),
            'total_products' => $productModel->count(),
            'total_orders' => $orderModel->count(),
            'total_sales' => $db->selectOne("SELECT SUM(total_amount) as total FROM orders WHERE status != 'cancelled'")['total'] ?? 0,
        ];
        
        // 近7天订单趋势
        $orderTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $dateStart = $date . ' 00:00:00';
            $dateEnd = $date . ' 23:59:59';
            
            $stats = $orderModel->getStatistics($dateStart, $dateEnd);
            $orderTrend[] = [
                'date' => $date,
                'orders' => $stats['total_orders'],
                'sales' => $stats['total_sales'],
            ];
        }
        
        // 热销商品Top10
        $hotProducts = $db->select(
            "SELECT id, name, sales, price FROM products WHERE status = 1 ORDER BY sales DESC LIMIT 10"
        );
        
        // 待处理订单
        $pendingOrders = $db->select(
            "SELECT * FROM orders WHERE status = 'pending' ORDER BY created_at DESC LIMIT 10"
        );
        
        $this->view('admin/dashboard/index', [
            'todayStats' => $todayStats,
            'totalStats' => $totalStats,
            'orderTrend' => $orderTrend,
            'hotProducts' => $hotProducts,
            'pendingOrders' => $pendingOrders,
        ]);
    }
}
