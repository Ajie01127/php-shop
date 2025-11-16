<?php

namespace App\Controllers\Admin;

use Core\Controller;
use App\Models\User;

class UserController extends Controller {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    /**
     * 用户列表
     */
    public function index() {
        $page = get('page', 1);
        $keyword = get('keyword');
        
        $where = '1=1';
        if ($keyword) {
            $where .= " AND (username LIKE '%{$keyword}%' OR email LIKE '%{$keyword}%' OR phone LIKE '%{$keyword}%')";
        }
        
        $result = $this->userModel->paginate($page, 20, $where);
        
        $this->view('admin/users/index', [
            'users' => $result['data'],
            'pagination' => $result,
            'keyword' => $keyword,
        ]);
    }
    
    /**
     * 用户详情
     */
    public function show($id) {
        $user = $this->userModel->find($id);
        
        if (!$user) {
            flash('error', '用户不存在');
            $this->redirect('/admin/users');
        }
        
        $this->view('admin/users/show', ['user' => $user]);
    }
    
    /**
     * 更新用户
     */
    public function update($id) {
        $data = [
            'vip_level' => post('vip_level'),
            'status' => post('status'),
        ];
        
        $result = $this->userModel->update($id, $data);
        
        if ($result) {
            $this->success('更新成功');
        } else {
            $this->error('更新失败');
        }
    }
}
