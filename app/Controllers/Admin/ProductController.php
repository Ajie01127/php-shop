<?php

namespace App\Controllers\Admin;

use Core\Controller;
use Core\Database;
use App\Models\Product;

class ProductController extends Controller {
    private $productModel;
    private $db;
    
    public function __construct() {
        $this->productModel = new Product();
        $this->db = Database::getInstance();
    }
    
    /**
     * 商品列表
     */
    public function index() {
        $page = get('page', 1);
        $keyword = get('keyword');
        $categoryId = get('category_id');
        
        $where = '1=1';
        if ($keyword) {
            $where .= " AND name LIKE '%{$keyword}%'";
        }
        if ($categoryId) {
            $where .= " AND category_id = {$categoryId}";
        }
        
        $result = $this->productModel->paginate($page, 20, $where);
        
        // 获取分类
        $categories = $this->db->select("SELECT * FROM categories ORDER BY sort_order DESC");
        
        $this->view('admin/products/index', [
            'products' => $result['data'],
            'pagination' => $result,
            'categories' => $categories,
            'keyword' => $keyword,
            'categoryId' => $categoryId,
        ]);
    }
    
    /**
     * 创建商品页面
     */
    public function create() {
        $categories = $this->db->select("SELECT * FROM categories ORDER BY sort_order DESC");
        $this->view('admin/products/create', ['categories' => $categories]);
    }
    
    /**
     * 保存商品
     */
    public function store() {
        $data = [
            'name' => post('name'),
            'description' => post('description'),
            'category_id' => post('category_id'),
            'price' => post('price'),
            'original_price' => post('original_price'),
            'cost_price' => post('cost_price'),
            'stock' => post('stock'),
            'images' => json_encode(post('images', [])),
            'specs' => json_encode(post('specs', [])),
            'status' => post('status', 1),
            'sort_order' => post('sort_order', 0),
        ];
        
        $productId = $this->productModel->create($data);
        
        if ($productId) {
            flash('success', '商品创建成功');
            $this->redirect('/admin/products');
        } else {
            flash('error', '商品创建失败');
            $this->redirect('/admin/products/create');
        }
    }
    
    /**
     * 编辑商品页面
     */
    public function edit($id) {
        $product = $this->productModel->find($id);
        $categories = $this->db->select("SELECT * FROM categories ORDER BY sort_order DESC");
        
        $this->view('admin/products/edit', [
            'product' => $product,
            'categories' => $categories,
        ]);
    }
    
    /**
     * 更新商品
     */
    public function update($id) {
        $data = [
            'name' => post('name'),
            'description' => post('description'),
            'category_id' => post('category_id'),
            'price' => post('price'),
            'original_price' => post('original_price'),
            'cost_price' => post('cost_price'),
            'stock' => post('stock'),
            'status' => post('status', 1),
            'sort_order' => post('sort_order', 0),
        ];
        
        if (post('images')) {
            $data['images'] = json_encode(post('images'));
        }
        if (post('specs')) {
            $data['specs'] = json_encode(post('specs'));
        }
        
        $result = $this->productModel->update($id, $data);
        
        if ($result) {
            flash('success', '商品更新成功');
        } else {
            flash('error', '商品更新失败');
        }
        
        $this->redirect('/admin/products');
    }
    
    /**
     * 删除商品
     */
    public function delete($id) {
        $result = $this->productModel->delete($id);
        
        if ($result) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }
}
