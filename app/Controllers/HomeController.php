<?php

namespace App\Controllers;

use Core\Controller;
use App\Models\Product;
use App\Models\User;
use Core\Database;

class HomeController extends Controller {
    /**
     * 首页
     */
    public function index() {
        $db = Database::getInstance();
        $productModel = new Product();
        
        // 获取轮播图
        $banners = $db->select("SELECT * FROM banners WHERE status = 1 ORDER BY sort_order DESC LIMIT 5");
        
        // 获取分类
        $categories = $db->select("SELECT * FROM categories WHERE status = 1 AND parent_id = 0 ORDER BY sort_order DESC");
        
        // 获取热销商品
        $hotProducts = $productModel->getHotProducts(8);
        
        // 统计数据
        $stats = [
            'total_products' => $productModel->count('status = 1'),
            'total_users' => (new User())->count('status = 1'),
        ];
        
        $this->view('home/index', [
            'banners' => $banners,
            'categories' => $categories,
            'hotProducts' => $hotProducts,
            'stats' => $stats,
        ]);
    }
}
