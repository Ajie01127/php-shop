<?php

namespace App\Controllers\Admin;

use Core\Controller;
use Core\Database;

class MarketingController extends Controller {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * 营销活动列表
     */
    public function index() {
        $activities = $this->db->select(
            "SELECT * FROM marketing ORDER BY created_at DESC"
        );
        
        $this->view('admin/marketing/index', [
            'activities' => $activities,
        ]);
    }
    
    /**
     * 创建活动页面
     */
    public function create() {
        $products = $this->db->select("SELECT id, name FROM products WHERE status = 1");
        $this->view('admin/marketing/create', ['products' => $products]);
    }
    
    /**
     * 保存活动
     */
    public function store() {
        $data = [
            'type' => post('type'),
            'title' => post('title'),
            'description' => post('description'),
            'start_time' => post('start_time'),
            'end_time' => post('end_time'),
            'status' => post('status', 'draft'),
            'rules' => json_encode(post('rules', [])),
        ];
        
        $sql = "INSERT INTO marketing (type, title, description, start_time, end_time, status, rules) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $result = $this->db->insert($sql, array_values($data));
        
        if ($result) {
            flash('success', '活动创建成功');
            $this->redirect('/admin/marketing');
        } else {
            flash('error', '活动创建失败');
            $this->redirect('/admin/marketing/create');
        }
    }
}
