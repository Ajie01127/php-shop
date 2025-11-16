<?php

namespace App\Controllers;

use Core\Controller;
use App\Models\Product;
use Core\Database;

class ProductController extends Controller {
    private $productModel;
    
    public function __construct() {
        $this->productModel = new Product();
    }
    
    /**
     * 商品列表
     */
    public function index() {
        $keyword = get('keyword');
        $categoryId = get('category');
        $minPrice = get('min_price');
        $maxPrice = get('max_price');
        $sortBy = get('sort', 'sales DESC');
        $page = get('page', 1);
        $perPage = 20;
        
        // 获取分类列表
        $db = Database::getInstance();
        $categories = $db->select("SELECT * FROM categories WHERE status = 1 ORDER BY sort_order DESC");
        
        // 搜索商品
        $products = $this->productModel->search($keyword, $categoryId, $minPrice, $maxPrice, $sortBy);
        
        // 分页
        $total = count($products);
        $products = array_slice($products, ($page - 1) * $perPage, $perPage);
        
        $this->view('products/index', [
            'products' => $products,
            'categories' => $categories,
            'keyword' => $keyword,
            'categoryId' => $categoryId,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
        ]);
    }
    
    /**
     * 商品详情
     */
    public function show($id) {
        $product = $this->productModel->getDetail($id);
        
        if (!$product || $product['status'] != 1) {
            flash('error', '商品不存在');
            $this->redirect('/products');
        }
        
        // 解析JSON字段
        $product['images'] = json_decode($product['images'], true);
        $product['specs'] = json_decode($product['specs'], true);
        
        // 获取相关商品
        $relatedProducts = $this->productModel->getByCategory($product['category_id'], 4);
        
        $this->view('products/show', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
        ]);
    }
}
