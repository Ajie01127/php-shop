<?php

namespace App\Services;

use App\Models\UserToken;

class MiniProgramTokenService {
    
    /**
     * 创建访问令牌
     */
    public function createToken($userId, $deviceInfo = null) {
        $tokenModel = new UserToken();
        return $tokenModel->createToken($userId, $deviceInfo);
    }
    
    /**
     * 验证访问令牌
     */
    public function validateToken($token) {
        $tokenModel = new UserToken();
        return $tokenModel->validateToken($token);
    }
    
    /**
     * 刷新访问令牌
     */
    public function refreshToken($token) {
        $tokenModel = new UserToken();
        return $tokenModel->refreshToken($token);
    }
    
    /**
     * 撤销访问令牌
     */
    public function revokeToken($token) {
        $tokenModel = new UserToken();
        return $tokenModel->revokeToken($token);
    }
    
    /**
     * 根据令牌获取用户ID
     */
    public function getUserIdByToken($token) {
        $tokenModel = new UserToken();
        $tokenRecord = $tokenModel->validateToken($token);
        
        if ($tokenRecord) {
            return $tokenRecord['user_id'];
        }
        
        return false;
    }
    
    /**
     * 获取用户的所有有效令牌
     */
    public function getUserTokens($userId) {
        $tokenModel = new UserToken();
        return $tokenModel->getUserTokens($userId);
    }
    
    /**
     * 清理过期令牌
     */
    public function cleanExpiredTokens() {
        $tokenModel = new UserToken();
        return $tokenModel->cleanExpiredTokens();
    }
    
    /**
     * 获取令牌信息
     */
    public function getTokenInfo($token) {
        $tokenModel = new UserToken();
        return $tokenModel->validateToken($token);
    }
}