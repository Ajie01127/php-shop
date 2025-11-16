<?php

namespace App\Services;

use App\Models\User;

/**
 * 小程序支付服务类 - JSAPI支付
 */
class MiniProgramPayService {
    
    private $wechatPayService;
    private $config;
    
    public function __construct() {
        $this->config = config('miniprogram');
        // 使用默认的微信支付服务，可以支持多商户配置
        $this->wechatPayService = new WechatPayService();
    }
    
    /**
     * 小程序支付 - 生成支付参数
     * @param array $orderData 订单数据
     * @param string $openid 用户openid
     * @return array
     */
    public function jsapiPay($orderData, $openid) {
        $url = 'https://api.mch.weixin.qq.com/v3/pay/transactions/jsapi';
        
        // 构建请求参数
        $params = [
            'appid' => $this->config['app_id'],
            'mchid' => $this->wechatPayService->getMchId(),
            'description' => $orderData['description'] ?? '商品支付',
            'out_trade_no' => $orderData['order_no'],
            'notify_url' => config('app.url') . '/payment/wechat/notify',
            'amount' => [
                'total' => (int)($orderData['amount'] * 100), // 转换为分
                'currency' => 'CNY',
            ],
            'payer' => [
                'openid' => $openid,
            ],
        ];
        
        // 如果有附加数据
        if (isset($orderData['attach'])) {
            $params['attach'] = $orderData['attach'];
        }
        
        // 发送请求
        $result = $this->wechatPayService->request($url, $params);
        
        if (isset($result['prepay_id'])) {
            return [
                'success' => true,
                'prepay_id' => $result['prepay_id'],
                'pay_params' => $this->generatePayParams($result['prepay_id']),
            ];
        }
        
        return [
            'success' => false,
            'message' => $result['message'] ?? '支付请求失败',
        ];
    }
    
    /**
     * 生成小程序支付参数
     */
    private function generatePayParams($prepayId) {
        $timestamp = time();
        $nonceStr = $this->generateNonce(32);
        
        // 构建支付参数
        $package = "prepay_id={$prepayId}";
        
        // 构建签名字符串
        $message = $this->config['app_id'] . "\n" .
                   $timestamp . "\n" .
                   $nonceStr . "\n" .
                   $package . "\n";
        
        // 使用商户私钥签名
        $signature = $this->sign($message);
        
        return [
            'timeStamp' => (string)$timestamp,
            'nonceStr' => $nonceStr,
            'package' => $package,
            'signType' => 'RSA',
            'paySign' => $signature,
        ];
    }
    
    /**
     * 查询订单状态
     */
    public function queryOrder($orderNo) {
        return $this->wechatPayService->queryOrder($orderNo);
    }
    
    /**
     * 关闭订单
     */
    public function closeOrder($orderNo) {
        return $this->wechatPayService->closeOrder($orderNo);
    }
    
    /**
     * 申请退款
     */
    public function refund($refundData) {
        return $this->wechatPayService->refund($refundData);
    }
    
    /**
     * 签名方法
     */
    private function sign($message) {
        // 这里需要实现RSA签名
        // 实际项目中需要根据商户证书进行签名
        $privateKey = $this->getPrivateKey();
        
        if (!$privateKey) {
            throw new \Exception('商户私钥获取失败');
        }
        
        openssl_sign($message, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        return base64_encode($signature);
    }
    
    /**
     * 获取商户私钥
     */
    private function getPrivateKey() {
        $keyPath = $this->wechatPayService->getKeyPath();
        
        if (!file_exists($keyPath)) {
            return false;
        }
        
        return openssl_pkey_get_private(file_get_contents($keyPath));
    }
    
    /**
     * 生成随机字符串
     */
    private function generateNonce($length = 32) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $nonce = '';
        for ($i = 0; $i < $length; $i++) {
            $nonce .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $nonce;
    }
    
    /**
     * 验证支付回调
     */
    public function verifyNotify($data) {
        return $this->wechatPayService->verifyNotify($data);
    }
    
    /**
     * 解密支付回调数据
     */
    public function decryptNotify($encryptedData) {
        return $this->wechatPayService->decryptNotify($encryptedData);
    }
    
    /**
     * 获取用户openid
     */
    public function getUserOpenid($userId) {
        $userModel = new User();
        $user = $userModel->find($userId);
        
        if ($user && isset($user['openid'])) {
            return $user['openid'];
        }
        
        return false;
    }
    
    /**
     * 检查支付状态
     */
    public function checkPaymentStatus($orderNo) {
        $result = $this->queryOrder($orderNo);
        
        if (!$result || isset($result['code'])) {
            return 'error';
        }
        
        $tradeState = $result['trade_state'] ?? '';
        
        switch ($tradeState) {
            case 'SUCCESS':
                return 'paid';
            case 'REFUND':
                return 'refunded';
            case 'CLOSED':
                return 'closed';
            case 'NOTPAY':
                return 'unpaid';
            case 'PAYERROR':
                return 'error';
            default:
                return 'unknown';
        }
    }
}