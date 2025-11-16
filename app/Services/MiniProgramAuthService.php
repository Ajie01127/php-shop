<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserToken;
use GuzzleHttp\Client;
use Core\Log;

class MiniProgramAuthService {
    
    /**
     * 微信小程序登录认证
     */
    public function login($code, $userInfo = null) {
        try {
            // 1. 获取微信openid
            $wxSession = $this->getWechatSession($code);
            if (!$wxSession || !isset($wxSession['openid'])) {
                throw new \Exception('微信登录失败: ' . ($wxSession['errmsg'] ?? '未知错误'));
            }
            
            $openid = $wxSession['openid'];
            $unionid = $wxSession['unionid'] ?? '';
            
            // 2. 查找或创建用户
            $userModel = new User();
            $user = $userModel->findByOpenid($openid);
            
            if (!$user) {
                // 新用户注册
                if (!$userInfo) {
                    // 如果未提供用户信息，创建基础用户
                    $userInfo = [
                        'openid' => $openid,
                        'unionid' => $unionid,
                        'nickName' => '微信用户' . substr($openid, -4),
                        'avatarUrl' => '',
                        'gender' => 0,
                        'country' => '',
                        'province' => '',
                        'city' => ''
                    ];
                }
                
                $userInfo['openid'] = $openid;
                $userInfo['unionid'] = $unionid;
                
                $user = $userModel->createMiniProgramUser($userInfo);
                if (!$user) {
                    throw new \Exception('用户创建失败');
                }
            } else {
                // 更新现有用户信息
                if ($userInfo) {
                    $userModel->updateMiniProgramUser($user['id'], $userInfo);
                }
                
                // 更新最后登录时间
                $userModel->updateUser($user['id'], ['last_login_at' => date('Y-m-d H:i:s')]);
            }
            
            // 3. 生成访问令牌
            $tokenService = new MiniProgramTokenService();
            $token = $tokenService->createToken($user['id']);
            
            if (!$token) {
                throw new \Exception('令牌生成失败');
            }
            
            // 4. 返回用户信息和令牌
            $userInfoResponse = [
                'id' => $user['id'],
                'openid' => $user['openid'],
                'nickname' => $user['nickname'] ?? $user['username'],
                'avatar' => $user['avatar'] ?? '',
                'mobile' => $user['phone'] ?? '',
                'gender' => $user['gender'] ?? 0,
                'points' => $user['points'] ?? 0,
                'balance' => $user['balance'] ?? 0,
                'user_type' => $user['user_type'] ?? 'mini_program'
            ];
            
            return [
                'token' => $token,
                'userInfo' => $userInfoResponse
            ];
            
        } catch (\Exception $e) {
            Log::error('小程序登录失败: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * 获取微信会话信息
     */
    private function getWechatSession($code) {
        $config = config('miniprogram');
        
        $client = new Client([
            'timeout' => 10,
            'verify' => false
        ]);
        
        $url = "https://api.weixin.qq.com/sns/jscode2session";
        $params = [
            'appid' => $config['app_id'],
            'secret' => $config['app_secret'],
            'js_code' => $code,
            'grant_type' => 'authorization_code'
        ];
        
        try {
            $response = $client->get($url, ['query' => $params]);
            $body = $response->getBody()->getContents();
            $result = json_decode($body, true);
            
            if (isset($result['errcode']) && $result['errcode'] != 0) {
                Log::error('微信登录接口错误: ' . json_encode($result));
                return false;
            }
            
            return $result;
            
        } catch (\Exception $e) {
            Log::error('微信登录请求失败: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 验证访问令牌
     */
    public function validateToken($token) {
        $tokenService = new MiniProgramTokenService();
        return $tokenService->validateToken($token);
    }
    
    /**
     * 刷新访问令牌
     */
    public function refreshToken($token) {
        $tokenService = new MiniProgramTokenService();
        return $tokenService->refreshToken($token);
    }
    
    /**
     * 注销登录
     */
    public function logout($token) {
        $tokenService = new MiniProgramTokenService();
        return $tokenService->revokeToken($token);
    }
    
    /**
     * 获取用户ID
     */
    public function getUserId($token) {
        $tokenService = new MiniProgramTokenService();
        $tokenData = $tokenService->validateToken($token);
        return $tokenData ? $tokenData['user_id'] : null;
    }
}