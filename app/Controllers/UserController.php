<?php

namespace App\Controllers;

use Core\Controller;
use App\Models\User;

class UserController extends Controller {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    /**
     * 个人中心
     */
    public function profile() {
        $this->requireAuth();
        
        $userId = $_SESSION['user_id'];
        $user = $this->userModel->find($userId);
        
        $this->view('user/profile', [
            'user' => $user,
        ]);
    }
    
    /**
     * 更新个人信息
     */
    public function update() {
        $this->requireAuth();
        
        $userId = $_SESSION['user_id'];
        $username = post('username');
        $phone = post('phone');
        $avatar = post('avatar');
        
        $data = [];
        if ($username) $data['username'] = $username;
        if ($phone) $data['phone'] = $phone;
        if ($avatar) $data['avatar'] = $avatar;
        
        if (empty($data)) {
            $this->error('没有可更新的信息');
        }
        
        $result = $this->userModel->update($userId, $data);
        
        if ($result) {
            // 更新session
            $_SESSION['user'] = $this->userModel->find($userId);
            $this->success('更新成功');
        } else {
            $this->error('更新失败');
        }
    }
}
