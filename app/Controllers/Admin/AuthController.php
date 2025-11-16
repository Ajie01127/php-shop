<?php

namespace App\Controllers\Admin;

use Core\Controller;
use Core\Database;

class AuthController extends Controller {
    /**
     * 登录表单
     */
    public function loginForm() {
        $this->view('admin/auth/login');
    }
    
    /**
     * 处理登录
     */
    public function login() {
        $username = post('username');
        $password = post('password');
        
        if (empty($username) || empty($password)) {
            flash('error', '用户名和密码不能为空');
            $this->redirect('/admin/login');
        }
        
        $db = Database::getInstance();
        $admin = $db->selectOne(
            "SELECT * FROM admins WHERE username = ?",
            [$username]
        );
        
        if (!$admin || !verifyPassword($password, $admin['password'])) {
            flash('error', '用户名或密码错误');
            $this->redirect('/admin/login');
        }
        
        if ($admin['status'] != 1) {
            flash('error', '账户已被禁用');
            $this->redirect('/admin/login');
        }
        
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin'] = $admin;
        
        $this->redirect('/admin/dashboard');
    }
    
    /**
     * 退出登录
     */
    public function logout() {
        unset($_SESSION['admin_id']);
        unset($_SESSION['admin']);
        $this->redirect('/admin/login');
    }
}
