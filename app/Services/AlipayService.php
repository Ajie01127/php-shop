<?php

namespace App\Services;

/**
 * 支付宝支付服务类 - 支持多商户
 */
class AlipayService {
    private $appId;
    private $privateKey;
    private $publicKey;
    private $notifyUrl;
    private $returnUrl;
    private $channelId;
    
    // 支付宝网关
    private $gatewayUrl = 'https://openapi.alipay.com/gateway.do';
    
    /**
     * 构造函数 - 支持传入支付通道ID
     * @param int|null $channelId 支付通道ID，为空则使用默认通道
     */
    public function __construct($channelId = null) {
        if ($channelId) {
            // 使用指定的支付通道
            $this->initWithChannel($channelId);
        } else {
            // 使用默认配置
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
            $channel = $channelModel->getDefault('alipay');
        }
        
        if (!$channel || !$channel['is_active']) {
            throw new \Exception('支付宝支付通道不存在或未启用');
        }
        
        $this->channelId = $channel['id'];
        $this->appId = $channel['app_id'];
        
        // 支付宝配置从config字段获取
        $config = $channel['config'] ? json_decode($channel['config'], true) : [];
        $this->privateKey = $config['private_key'] ?? '';
        $this->publicKey = $config['public_key'] ?? '';
        $this->notifyUrl = $channel['notify_url'] ?: (config('app.url') . '/payment/alipay/notify');
        $this->returnUrl = $config['return_url'] ?? (config('app.url') . '/payment/alipay/return');
    }
    
    /**
     * 使用配置文件初始化
     */
    private function initWithConfig() {
        $config = config('payment.alipay');
        $this->appId = $config['app_id'];
        $this->privateKey = $config['private_key'];
        $this->publicKey = $config['public_key'];
        $this->notifyUrl = config('app.url') . '/payment/alipay/notify';
        $this->returnUrl = config('app.url') . '/payment/alipay/return';
    }
    
    /**
     * 获取当前使用的通道ID
     */
    public function getChannelId() {
        return $this->channelId;
    }
    
    /**
     * 生成签名
     */
    private function sign($data) {
        $privateKey = "-----BEGIN RSA PRIVATE KEY-----\n" . 
                      wordwrap($this->privateKey, 64, "\n", true) . 
                      "\n-----END RSA PRIVATE KEY-----";
        
        openssl_sign($data, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        return base64_encode($signature);
    }
    
    /**
     * 验证签名
     */
    public function verify($data, $signature) {
        $publicKey = "-----BEGIN PUBLIC KEY-----\n" . 
                     wordwrap($this->publicKey, 64, "\n", true) . 
                     "\n-----END PUBLIC KEY-----";
        
        return openssl_verify($data, base64_decode($signature), $publicKey, OPENSSL_ALGO_SHA256) === 1;
    }
    
    /**
     * 构造请求参数
     */
    private function buildRequestParams($method, $bizContent) {
        $params = [
            'app_id' => $this->appId,
            'method' => $method,
            'format' => 'JSON',
            'charset' => 'utf-8',
            'sign_type' => 'RSA2',
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0',
            'biz_content' => json_encode($bizContent, JSON_UNESCAPED_UNICODE),
        ];
        
        // 如果有回调地址
        if (strpos($method, 'pay') !== false) {
            $params['notify_url'] = $this->notifyUrl;
            $params['return_url'] = $this->returnUrl;
        }
        
        // 生成签名
        $params['sign'] = $this->sign($this->buildSignString($params));
        
        return $params;
    }
    
    /**
     * 构造签名字符串
     */
    private function buildSignString($params) {
        ksort($params);
        $stringToBeSigned = "";
        foreach ($params as $k => $v) {
            if ($v === "" || $v === null || $k === "sign") {
                continue;
            }
            $stringToBeSigned .= "$k=$v&";
        }
        return rtrim($stringToBeSigned, "&");
    }
    
    /**
     * 电脑网站支付 - 生成支付链接
     * @param array $orderData 订单数据
     * @return array ['pay_url' => '支付链接']
     */
    public function pagePay($orderData) {
        $bizContent = [
            'out_trade_no' => $orderData['order_no'],
            'total_amount' => number_format($orderData['amount'], 2, '.', ''),
            'subject' => $orderData['description'] ?? '商品支付',
            'product_code' => 'FAST_INSTANT_TRADE_PAY',
        ];
        
        if (isset($orderData['attach'])) {
            $bizContent['passback_params'] = urlencode($orderData['attach']);
        }
        
        $params = $this->buildRequestParams('alipay.trade.page.pay', $bizContent);
        
        return [
            'success' => true,
            'pay_url' => $this->gatewayUrl . '?' . http_build_query($params),
        ];
    }
    
    /**
     * APP支付 - 生成支付参数
     * @param array $orderData 订单数据
     * @return array ['order_string' => '订单字符串']
     */
    public function appPay($orderData) {
        $bizContent = [
            'out_trade_no' => $orderData['order_no'],
            'total_amount' => number_format($orderData['amount'], 2, '.', ''),
            'subject' => $orderData['description'] ?? '商品支付',
            'product_code' => 'QUICK_MSECURITY_PAY',
        ];
        
        $params = $this->buildRequestParams('alipay.trade.app.pay', $bizContent);
        
        return [
            'success' => true,
            'order_string' => http_build_query($params),
        ];
    }
    
    /**
     * 查询订单
     * @param string $orderNo 商户订单号
     * @return array
     */
    public function queryOrder($orderNo) {
        $bizContent = [
            'out_trade_no' => $orderNo,
        ];
        
        $params = $this->buildRequestParams('alipay.trade.query', $bizContent);
        
        $result = $this->request($params);
        
        if (isset($result['alipay_trade_query_response'])) {
            $response = $result['alipay_trade_query_response'];
            if ($response['code'] === '10000') {
                return [
                    'success' => true,
                    'trade_status' => $response['trade_status'],
                    'trade_no' => $response['trade_no'] ?? '',
                    'total_amount' => $response['total_amount'] ?? 0,
                    'buyer_id' => $response['buyer_user_id'] ?? '',
                ];
            }
        }
        
        return [
            'success' => false,
            'message' => $response['sub_msg'] ?? '查询失败',
        ];
    }
    
    /**
     * 关闭订单
     * @param string $orderNo 商户订单号
     * @return array
     */
    public function closeOrder($orderNo) {
        $bizContent = [
            'out_trade_no' => $orderNo,
        ];
        
        $params = $this->buildRequestParams('alipay.trade.close', $bizContent);
        
        $result = $this->request($params);
        
        if (isset($result['alipay_trade_close_response'])) {
            $response = $result['alipay_trade_close_response'];
            if ($response['code'] === '10000') {
                return ['success' => true];
            }
        }
        
        return [
            'success' => false,
            'message' => $response['sub_msg'] ?? '关闭失败',
        ];
    }
    
    /**
     * 申请退款
     * @param array $refundData 退款数据
     * @return array
     */
    public function refund($refundData) {
        $bizContent = [
            'out_trade_no' => $refundData['order_no'],
            'refund_amount' => number_format($refundData['refund_amount'], 2, '.', ''),
            'out_request_no' => $refundData['refund_no'],
            'refund_reason' => $refundData['reason'] ?? '退款',
        ];
        
        $params = $this->buildRequestParams('alipay.trade.refund', $bizContent);
        
        $result = $this->request($params);
        
        if (isset($result['alipay_trade_refund_response'])) {
            $response = $result['alipay_trade_refund_response'];
            if ($response['code'] === '10000') {
                return [
                    'success' => true,
                    'refund_amount' => $response['refund_fee'],
                    'trade_no' => $response['trade_no'],
                ];
            }
        }
        
        return [
            'success' => false,
            'message' => $response['sub_msg'] ?? '退款失败',
        ];
    }
    
    /**
     * 验证支付回调签名
     * @param array $data 回调数据
     * @return bool
     */
    public function verifyNotify($data) {
        if (empty($data['sign'])) {
            return false;
        }
        
        $sign = $data['sign'];
        unset($data['sign'], $data['sign_type']);
        
        $signData = $this->buildSignString($data);
        
        return $this->verify($signData, $sign);
    }
    
    /**
     * 发送HTTP请求
     * @param array $params 请求参数
     * @return array
     */
    private function request($params) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->gatewayUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            return json_decode($response, true) ?: [];
        }
        
        return [
            'code' => 'REQUEST_FAILED',
            'msg' => '网络请求失败',
            'sub_msg' => 'HTTP ' . $httpCode,
        ];
    }
    
    /**
     * 生成支付二维码
     * @param string $payUrl 支付URL
     * @return string base64图片数据
     */
    public function generateQrCode($payUrl) {
        // 这里使用简单的Google Chart API生成二维码
        $size = '300x300';
        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size={$size}&data=" . urlencode($payUrl);
        
        // 获取二维码图片
        $imageData = file_get_contents($qrUrl);
        
        return 'data:image/png;base64,' . base64_encode($imageData);
    }
    
    /**
     * 检查支付宝配置是否完整
     * @return bool
     */
    public function isConfigValid() {
        return !empty($this->appId) && !empty($this->privateKey) && !empty($this->publicKey);
    }
    
    /**
     * 获取沙箱环境URL
     */
    public function getSandboxUrl() {
        return 'https://openapi.alipaydev.com/gateway.do';
    }
    
    /**
     * 切换到沙箱环境
     */
    public function useSandbox() {
        $this->gatewayUrl = $this->getSandboxUrl();
    }
}