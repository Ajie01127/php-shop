<?php

namespace App\Models;

use Core\Model;

class UserToken extends Model {
    protected $table = 'user_tokens';
    
    /**
     * 创建访问令牌
     */
    public function createToken($userId, $deviceInfo = null) {
        // 生成唯一令牌
        $token = $this->generateToken();
        
        // 计算过期时间
        $config = config('miniprogram');
        $expiresAt = date('Y-m-d H:i:s', time() + $config['token_expire']);
        
        $tokenData = [
            'user_id' => $userId,
            'token' => $token,
            'device_type' => 'mini_program',
            'device_info' => $deviceInfo ? json_encode($deviceInfo) : null,
            'ip_address' => $this->getClientIP(),
            'expires_at' => $expiresAt,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $result = $this->create($tokenData);
        
        if ($result) {
            return $token;
        }
        
        return false;
    }
    
    /**
     * 验证访问令牌
     */
    public function validateToken($token) {
        $sql = "SELECT * FROM {$this->table} WHERE token = ? AND expires_at > NOW()";
        $tokenRecord = $this->db->first($sql, [$token]);
        
        if (!$tokenRecord) {
            return false;
        }
        
        // 更新最后使用时间（可选）
        $this->update($tokenRecord['id'], ['last_used_at' => date('Y-m-d H:i:s')]);
        
        return $tokenRecord;
    }
    
    /**
     * 刷新访问令牌
     */
    public function refreshToken($oldToken) {
        // 验证旧令牌
        $tokenRecord = $this->validateToken($oldToken);
        if (!$tokenRecord) {
            return false;
        }
        
        // 创建新令牌
        $newToken = $this->createToken($tokenRecord['user_id'], 
            $tokenRecord['device_info'] ? json_decode($tokenRecord['device_info'], true) : null);
        
        if ($newToken) {
            // 使旧令牌失效
            $this->revokeToken($oldToken);
            return $newToken;
        }
        
        return false;
    }
    
    /**
     * 撤销访问令牌
     */
    public function revokeToken($token) {
        $sql = "UPDATE {$this->table} SET expires_at = NOW() WHERE token = ?";
        return $this->db->update($sql, [$token]);
    }
    
    /**
     * 获取用户的所有有效令牌
     */
    public function getUserTokens($userId) {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = ? AND expires_at > NOW() ORDER BY created_at DESC";
        return $this->db->select($sql, [$userId]);
    }
    
    /**
     * 清理过期令牌
     */
    public function cleanExpiredTokens() {
        $sql = "DELETE FROM {$this->table} WHERE expires_at <= NOW()";
        return $this->db->delete($sql);
    }
    
    /**
     * 生成唯一令牌
     */
    private function generateToken() {
        return md5(uniqid(mt_rand(), true)) . md5(time() . mt_rand());
    }
    
    /**
     * 获取客户端IP
     */
    private function getClientIP() {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        }
        
        return $ip;
    }
    
    /**
     * 根据令牌获取用户信息
     */
    public function getUserByToken($token) {
        $tokenRecord = $this->validateToken($token);
        if (!$tokenRecord) {
            return false;
        }
        
        $userModel = new User();
        return $userModel->find($tokenRecord['user_id']);
    }
}