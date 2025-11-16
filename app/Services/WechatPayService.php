<?php

namespace App\Services;

/**
 * 微信支付服务类 - Native支付（支持多商户）
 */
class WechatPayService {
    private $appId;
    private $mchId;
    private $apiKey;
    private $certPath;
    private $keyPath;
    private $notifyUrl;
    private $channelId;
    
    /**
     * 构造函数 - 支持传入支付通道ID
     * @param int|null $channelId 支付通道ID，为空则使用默认通道
     */
    public function __construct($channelId = null) {
        if ($channelId) {
            // 使用指定的支付通道
            $this->initWithChannel($channelId);
        } else {
            // 使用默认配置（兼容旧代码）
            $this->initWithConfig();
        }
    }
    
    /**
     * 使用支付通道初始化
     */
    private function initWithChannel($channelId) {
        require_once __DIR__ . '/../Models/PaymentChannel.php';
        $channelModel = new \App\Models\PaymentChannel();
        
        if (is_numeric($channelId)) {
            // 使用指定ID的通道
            $channel = $channelModel->getById($channelId);
        } else {
            // 获取默认通道
            $channel = $channelModel->getDefault('wechat');
        }
        
        if (!$channel || !$channel['is_active']) {
            throw new \Exception('支付通道不存在或未启用');
        }
        
        $this->channelId = $channel['id'];
        $this->appId = $channel['app_id'];
        $this->mchId = $channel['mch_id'];
        $this->apiKey = $channel['api_key'];
        $this->certPath = $this->getFullPath($channel['cert_path']);
        $this->keyPath = $this->getFullPath($channel['key_path']);
        $this->notifyUrl = $channel['notify_url'] ?: (config('app.url') . '/payment/wechat/notify');
    }
    
    /**
     * 使用配置文件初始化（兼容旧方式）
     */
    private function initWithConfig() {
        $config = config('payment.wechat');
        $this->appId = $config['app_id'];
        $this->mchId = $config['mch_id'];
        $this->apiKey = $config['api_key'];
        $this->certPath = $config['cert_path'] ?? '';
        $this->keyPath = $config['key_path'] ?? '';
        $this->notifyUrl = config('app.url') . '/payment/wechat/notify';
    }
    
    /**
     * 获取完整路径
     */
    private function getFullPath($path) {
        if (empty($path)) {
            return '';
        }
        
        // 如果是绝对路径，直接返回
        if (strpos($path, '/') === 0 || preg_match('/^[a-zA-Z]:/', $path)) {
            return $path;
        }
        
        // 相对路径，拼接项目根目录
        return dirname(dirname(dirname(__FILE__))) . '/' . ltrim($path, '/');
    }
    
    /**
     * 获取当前使用的通道ID
     */
    public function getChannelId() {
        return $this->channelId;
    }
    
    /**
     * 获取商户号
     */
    public function getMchId() {
        return $this->mchId;
    }
    
    /**
     * 获取私钥路径
     */
    public function getKeyPath() {
        return $this->keyPath;
    }
    
    /**
     * Native支付 - 生成支付二维码
     * @param array $orderData 订单数据
     * @return array ['code_url' => '二维码链接', 'prepay_id' => '预支付ID']
     */
    public function nativePay($orderData) {
        $url = 'https://api.mch.weixin.qq.com/v3/pay/transactions/native';
        
        // 构建请求参数
        $params = [
            'appid' => $this->appId,
            'mchid' => $this->mchId,
            'description' => $orderData['description'] ?? '商品支付',
            'out_trade_no' => $orderData['order_no'],
            'notify_url' => $this->notifyUrl,
            'amount' => [
                'total' => (int)($orderData['amount'] * 100), // 转换为分
                'currency' => 'CNY',
            ],
        ];
        
        // 如果有附加数据
        if (isset($orderData['attach'])) {
            $params['attach'] = $orderData['attach'];
        }
        
        // 发送请求
        $result = $this->request($url, $params);
        
        if (isset($result['code_url'])) {
            return [
                'success' => true,
                'code_url' => $result['code_url'],
                'prepay_id' => $result['prepay_id'] ?? '',
            ];
        }
        
        return [
            'success' => false,
            'message' => $result['message'] ?? '支付请求失败',
        ];
    }
    
    /**
     * 查询订单
     * @param string $orderNo 商户订单号
     * @return array
     */
    public function queryOrder($orderNo) {
        $url = "https://api.mch.weixin.qq.com/v3/pay/transactions/out-trade-no/{$orderNo}";
        $url .= '?mchid=' . $this->mchId;
        
        return $this->request($url, null, 'GET');
    }
    
    /**
     * 关闭订单
     * @param string $orderNo 商户订单号
     * @return array
     */
    public function closeOrder($orderNo) {
        $url = "https://api.mch.weixin.qq.com/v3/pay/transactions/out-trade-no/{$orderNo}/close";
        
        $params = [
            'mchid' => $this->mchId,
        ];
        
        return $this->request($url, $params);
    }
    
    /**
     * 申请退款
     * @param array $refundData 退款数据
     * @return array
     */
    public function refund($refundData) {
        $url = 'https://api.mch.weixin.qq.com/v3/refund/domestic/refunds';
        
        $params = [
            'out_trade_no' => $refundData['order_no'],
            'out_refund_no' => $refundData['refund_no'],
            'reason' => $refundData['reason'] ?? '退款',
            'notify_url' => config('app.url') . '/payment/wechat/refund-notify',
            'amount' => [
                'refund' => (int)($refundData['refund_amount'] * 100),
                'total' => (int)($refundData['total_amount'] * 100),
                'currency' => 'CNY',
            ],
        ];
        
        return $this->request($url, $params);
    }
    
    /**
     * 验证支付回调签名
     * @param array $data 回调数据
     * @return bool
     */
    public function verifyNotify($data) {
        $signature = $data['signature'] ?? '';
        $timestamp = $data['timestamp'] ?? '';
        $nonce = $data['nonce'] ?? '';
        $body = $data['body'] ?? '';
        
        // 构建验签字符串
        $message = $timestamp . "\n" . $nonce . "\n" . $body . "\n";
        
        // 使用商户API证书公钥验签
        // 这里需要根据实际的证书进行验签
        // 简化处理，实际项目中需要完整实现
        
        return true;
    }
    
    /**
     * 解密支付回调数据
     * @param array $encryptedData 加密数据
     * @return array|false
     */
    public function decryptNotify($encryptedData) {
        $ciphertext = base64_decode($encryptedData['ciphertext']);
        $associatedData = $encryptedData['associated_data'] ?? '';
        $nonce = $encryptedData['nonce'];
        
        // 使用APIv3密钥解密
        $decrypted = $this->aesGcmDecrypt(
            $ciphertext,
            $this->apiKey,
            $nonce,
            $associatedData
        );
        
        return $decrypted ? json_decode($decrypted, true) : false;
    }
    
    /**
     * AES-GCM解密
     */
    private function aesGcmDecrypt($ciphertext, $key, $nonce, $associatedData) {
        $tag = substr($ciphertext, -16);
        $ciphertext = substr($ciphertext, 0, -16);
        
        return openssl_decrypt(
            $ciphertext,
            'aes-256-gcm',
            $key,
            OPENSSL_RAW_DATA,
            $nonce,
            $tag,
            $associatedData
        );
    }
    
    /**
     * 发送HTTP请求
     * @param string $url 请求URL
     * @param array|null $data 请求数据
     * @param string $method 请求方法
     * @return array
     */
    private function request($url, $data = null, $method = 'POST') {
        $timestamp = time();
        $nonce = $this->generateNonce();
        $body = $data ? json_encode($data) : '';
        
        // 构建签名
        $signature = $this->sign($method, $url, $timestamp, $nonce, $body);
        
        // 构建Authorization头
        $token = sprintf(
            'WECHATPAY2-SHA256-RSA2048 mchid="%s",nonce_str="%s",timestamp="%d",serial_no="%s",signature="%s"',
            $this->mchId,
            $nonce,
            $timestamp,
            $this->getCertSerialNo(),
            $signature
        );
        
        // 发送请求
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: ' . $token,
            'User-Agent: Mozilla/5.0',
        ];
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200 || $httpCode === 204) {
            return json_decode($response, true) ?: [];
        }
        
        // 错误处理
        $error = json_decode($response, true);
        return [
            'code' => $error['code'] ?? 'ERROR',
            'message' => $error['message'] ?? '请求失败',
        ];
    }
    
    /**
     * 生成签名
     */
    private function sign($method, $url, $timestamp, $nonce, $body) {
        // 解析URL
        $urlParts = parse_url($url);
        $canonicalUrl = ($urlParts['path'] ?? '/') . (isset($urlParts['query']) ? '?' . $urlParts['query'] : '');
        
        // 构建签名字符串
        $message = $method . "\n" .
                   $canonicalUrl . "\n" .
                   $timestamp . "\n" .
                   $nonce . "\n" .
                   $body . "\n";
        
        // 使用商户私钥签名
        if (!file_exists($this->keyPath)) {
            throw new \Exception('商户私钥文件不存在');
        }
        
        $privateKey = openssl_pkey_get_private(file_get_contents($this->keyPath));
        openssl_sign($message, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        openssl_free_key($privateKey);
        
        return base64_encode($signature);
    }
    
    /**
     * 获取证书序列号
     */
    private function getCertSerialNo() {
        if (!file_exists($this->certPath)) {
            return '';
        }
        
        $cert = file_get_contents($this->certPath);
        $ssl = openssl_x509_parse($cert);
        
        return $ssl['serialNumberHex'] ?? '';
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
     * 生成支付二维码(使用第三方库或自己实现)
     * @param string $codeUrl 支付URL
     * @return string base64图片数据
     */
    public function generateQrCode($codeUrl) {
        // 这里使用简单的Google Chart API生成二维码
        // 实际项目中建议使用 endroid/qr-code 或 phpqrcode 等库
        $size = '300x300';
        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size={$size}&data=" . urlencode($codeUrl);
        
        // 获取二维码图片
        $imageData = file_get_contents($qrUrl);
        
        return 'data:image/png;base64,' . base64_encode($imageData);
    }
}
