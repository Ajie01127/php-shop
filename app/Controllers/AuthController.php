<?php

namespace App\Controllers;

use Core\Controller;
use App\Models\User;

class AuthController extends Controller {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    /**
     * 显示登录表单
     */
    public function loginForm() {
        $this->view('auth/login');
    }
    
    /**
     * 处理登录
     */
    public function login() {
        $email = post('email');
        $password = post('password');
        
        // 验证
        if (empty($email) || empty($password)) {
            flash('error', '邮箱和密码不能为空');
            $this->redirect('/login');
        }
        
        // 查找用户
        $user = $this->userModel->findByEmail($email);
        
        if (!$user || !verifyPassword($password, $user['password'])) {
            flash('error', '邮箱或密码错误');
            $this->redirect('/login');
        }
        
        if ($user['status'] != 1) {
            flash('error', '账户已被禁用');
            $this->redirect('/login');
        }
        
        // 设置session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user'] = $user;
        
        flash('success', '登录成功');
        $this->redirect('/');
    }
    
    /**
     * 显示注册表单
     */
    public function registerForm() {
        $this->view('auth/register');
    }
    
    /**
     * 处理注册
     */
    public function register() {
        $username = post('username');
        $email = post('email');
        $phone = post('phone');
        $password = post('password');
        $confirmPassword = post('confirm_password');
        
        // 验证
        if (empty($username) || empty($email) || empty($password)) {
            flash('error', '请填写完整信息');
            $this->redirect('/register');
        }
        
        if ($password !== $confirmPassword) {
            flash('error', '两次密码不一致');
            $this->redirect('/register');
        }
        
        if (strlen($password) < 6) {
            flash('error', '密码至少6位');
            $this->redirect('/register');
        }
        
        // 检查邮箱是否存在
        if ($this->userModel->findByEmail($email)) {
            flash('error', '邮箱已被注册');
            $this->redirect('/register');
        }
        
        // 检查手机号是否存在
        if ($phone && $this->userModel->findByPhone($phone)) {
            flash('error', '手机号已被注册');
            $this->redirect('/register');
        }
        
        // 创建用户
        $userId = $this->userModel->createUser([
            'username' => $username,
            'email' => $email,
            'phone' => $phone,
            'password' => $password,
        ]);
        
        if ($userId) {
            // 发送欢迎邮件
            $this->sendWelcomeEmail($userId, $username, $email);
            
            flash('success', '注册成功，请登录');
            $this->redirect('/login');
        } else {
            flash('error', '注册失败，请重试');
            $this->redirect('/register');
        }
    }
    
    /**
     * 发送欢迎邮件
     */
    private function sendWelcomeEmail($userId, $username, $email)
    {
        try {
            $emailNotificationService = new \App\Services\EmailNotificationService();
            $emailNotificationService->triggerNotification('user_register', [
                'user_id' => $userId,
                'email' => $email,
                'username' => $username,
                'register_time' => date('Y-m-d H:i:s')
            ]);
            
        } catch (\Exception $e) {
            // 邮件发送失败不影响主流程
            error_log('Welcome email failed: ' . $e->getMessage());
        }
    }
    
    /**
     * 退出登录
     */
    public function logout() {
        session_destroy();
        $this->redirect('/');
    }
}
