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
        
        // 重新生成会话ID防止会话固定攻击
        session_regenerate_id(true);
        
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin'] = $admin;
        $_SESSION['login_time'] = time();
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        
        $this->redirect('/admin/dashboard');
    }
    
    /**
     * 退出登录
     */
    public function logout() {
        // 清空所有会话数据
        session_unset();
        session_destroy();
        
        // 删除会话cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        $this->redirect('/admin/login');
    }
}
