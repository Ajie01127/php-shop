<?php

namespace App\Services;

/**
 * 顺丰快递API服务类
 * 支持下单、查询、取消等功能
 * 
 * 文档：https://open.sf-express.com/
 */
class SfExpressService
{
    private $config;
    private $sandboxMode; // 是否沙箱模式
    
    // API端点
    const PRODUCTION_URL = 'https://bsp-oisp.sf-express.com/bsp-oisp/sfexpressService';
    const SANDBOX_URL = 'https://bsp-oisp.test.sf-express.com/bsp-oisp/sfexpressService';
    
    // 服务代码
    const SERVICE_EXP_RECE_CREATE_ORDER = 'EXP_RECE_CREATE_ORDER'; // 下单
    const SERVICE_EXP_RECE_SEARCH_ORDER_RESP = 'EXP_RECE_SEARCH_ORDER_RESP'; // 查询订单
    const SERVICE_EXP_RECE_UPDATE_ORDER = 'EXP_RECE_UPDATE_ORDER'; // 取消订单
    const SERVICE_EXP_RECE_SEARCH_ROUTES = 'EXP_RECE_SEARCH_ROUTES'; // 查询路由
    
    /**
     * 构造函数
     * @param array|null $config 配置数组或从数据库加载
     */
    public function __construct($config = null)
    {
        if ($config) {
            $this->config = $config;
        } else {
            $this->loadConfigFromDB();
        }
        
        $this->sandboxMode = $this->config['sandbox_mode'] ?? true;
    }
    
    /**
     * 从数据库加载配置
     */
    private function loadConfigFromDB()
    {
        $db = \Core\Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM express_configs WHERE express_code = 'SF' AND status = 1 LIMIT 1");
        $stmt->execute();
        $config = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$config) {
            throw new \Exception('顺丰快递配置未找到或未启用');
        }
        
        $this->config = [
            'partner_id' => $config['partner_id'],
            'checkword' => $config['checkword'],
            'sender_name' => $config['sender_name'],
            'sender_mobile' => $config['sender_mobile'],
            'sender_province' => $config['sender_province'],
            'sender_city' => $config['sender_city'],
            'sender_county' => $config['sender_county'],
            'sender_address' => $config['sender_address'],
            'monthly_account' => $config['monthly_account'] ?? '',
            'sandbox_mode' => $config['sandbox_mode'] ?? 1
        ];
    }
    
    /**
     * 创建订单（下单）
     * 
     * @param array $orderData 订单数据
     * @return array 返回运单号等信息
     */
    public function createOrder($orderData)
    {
        $msgData = [
            'orderId' => $orderData['order_no'], // 客户订单号
            'expressType' => $orderData['express_type'] ?? 1, // 1-标准快递 2-顺丰特惠
            'payMethod' => $orderData['pay_method'] ?? 1, // 1-寄付 2-到付 3-月结
            'cargoDetails' => [
                [
                    'name' => $orderData['cargo_name'] ?? '商品',
                    'count' => $orderData['cargo_count'] ?? 1,
                    'unit' => $orderData['cargo_unit'] ?? '件',
                ]
            ],
            'consigneeInfo' => [
                'contact' => $orderData['consignee_name'],
                'mobile' => $orderData['consignee_mobile'],
                'province' => $orderData['consignee_province'],
                'city' => $orderData['consignee_city'],
                'county' => $orderData['consignee_county'],
                'address' => $orderData['consignee_address'],
            ],
            'senderInfo' => [
                'contact' => $this->config['sender_name'],
                'mobile' => $this->config['sender_mobile'],
                'province' => $this->config['sender_province'],
                'city' => $this->config['sender_city'],
                'county' => $this->config['sender_county'],
                'address' => $this->config['sender_address'],
            ]
        ];
        
        // 如果是月结，添加月结账号（必填）
        if (isset($orderData['pay_method']) && $orderData['pay_method'] == 3) {
            if (empty($this->config['monthly_account'])) {
                throw new \Exception('月结付款需要配置月结账号');
            }
            $msgData['monthlyAccount'] = $this->config['monthly_account'];
        }
        
        // 月结账户默认使用月结付款
        if (!empty($this->config['monthly_account']) && !isset($orderData['pay_method'])) {
            $msgData['payMethod'] = 3;
            $msgData['monthlyAccount'] = $this->config['monthly_account'];
        }
        
        // 重量和体积（可选）
        if (isset($orderData['weight'])) {
            $msgData['cargoDetails'][0]['weight'] = $orderData['weight'];
        }
        if (isset($orderData['volume'])) {
            $msgData['cargoDetails'][0]['volume'] = $orderData['volume'];
        }
        
        $response = $this->request(self::SERVICE_EXP_RECE_CREATE_ORDER, $msgData);
        
        if ($response['success']) {
            return [
                'success' => true,
                'waybill_no' => $response['data']['waybillNoInfoList'][0]['waybillNo'] ?? '',
                'order_id' => $response['data']['orderId'],
                'raw_response' => $response['data']
            ];
        }
        
        return [
            'success' => false,
            'error' => $response['error'] ?? '下单失败'
        ];
    }
    
    /**
     * 查询订单
     * 
     * @param string $orderNo 客户订单号或运单号
     * @param int $queryType 1-客户订单号 2-运单号
     * @return array
     */
    public function queryOrder($orderNo, $queryType = 1)
    {
        $msgData = [
            'orderid' => $queryType == 1 ? $orderNo : '',
            'waybillNoList' => $queryType == 2 ? [$orderNo] : []
        ];
        
        $response = $this->request(self::SERVICE_EXP_RECE_SEARCH_ORDER_RESP, $msgData);
        
        if ($response['success']) {
            return [
                'success' => true,
                'data' => $response['data']
            ];
        }
        
        return [
            'success' => false,
            'error' => $response['error'] ?? '查询失败'
        ];
    }
    
    /**
     * 取消订单
     * 
     * @param string $orderNo 客户订单号
     * @param int $dealType 1-确认 2-取消
     * @return array
     */
    public function cancelOrder($orderNo, $dealType = 2)
    {
        $msgData = [
            'orderId' => $orderNo,
            'dealType' => $dealType,
            'orderSource' => 'ORDER_SOURCE_ONLINE'
        ];
        
        $response = $this->request(self::SERVICE_EXP_RECE_UPDATE_ORDER, $msgData);
        
        if ($response['success']) {
            return [
                'success' => true,
                'message' => '订单已取消'
            ];
        }
        
        return [
            'success' => false,
            'error' => $response['error'] ?? '取消失败'
        ];
    }
    
    /**
     * 查询路由（物流轨迹）
     * 
     * @param string $waybillNo 运单号
     * @return array
     */
    public function queryRoute($waybillNo)
    {
        $msgData = [
            'trackingNumber' => $waybillNo,
            'trackingType' => 1, // 1-根据顺丰运单号查询
            'methodType' => 1 // 1-标准路由查询
        ];
        
        $response = $this->request(self::SERVICE_EXP_RECE_SEARCH_ROUTES, $msgData);
        
        if ($response['success']) {
            return [
                'success' => true,
                'routes' => $response['data']['routeResps'] ?? []
            ];
        }
        
        return [
            'success' => false,
            'error' => $response['error'] ?? '查询失败'
        ];
    }
    
    /**
     * 发送API请求
     * 
     * @param string $serviceCode 服务代码
     * @param array $msgData 消息数据
     * @return array
     */
    private function request($serviceCode, $msgData)
    {
        $url = $this->sandboxMode ? self::SANDBOX_URL : self::PRODUCTION_URL;
        
        $msgDataJson = json_encode($msgData, JSON_UNESCAPED_UNICODE);
        $timestamp = time();
        
        // 生成签名
        $msgDigest = $this->generateSign($msgDataJson, $timestamp);
        
        $requestData = [
            'partnerID' => $this->config['partner_id'],
            'requestID' => $this->generateRequestId(),
            'serviceCode' => $serviceCode,
            'timestamp' => $timestamp,
            'msgDigest' => $msgDigest,
            'msgData' => $msgDataJson
        ];
        
        // 发送请求
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($requestData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded; charset=UTF-8'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode != 200) {
            return [
                'success' => false,
                'error' => "HTTP错误: {$httpCode}"
            ];
        }
        
        $result = json_decode($response, true);
        
        if (!$result) {
            return [
                'success' => false,
                'error' => '响应解析失败'
            ];
        }
        
        // 验证响应签名
        if (!$this->verifySign($result['apiResultData'], $result['msgDigest'])) {
            return [
                'success' => false,
                'error' => '签名验证失败'
            ];
        }
        
        $apiResult = json_decode($result['apiResultData'], true);
        
        if ($apiResult['success'] === false || $apiResult['apiResultCode'] != 'A1000') {
            return [
                'success' => false,
                'error' => $apiResult['errorMsg'] ?? '请求失败',
                'code' => $apiResult['apiResultCode'] ?? ''
            ];
        }
        
        return [
            'success' => true,
            'data' => $apiResult['apiResultData'] ?? []
        ];
    }
    
    /**
     * 生成签名
     * 
     * @param string $msgData 消息数据JSON
     * @param int $timestamp 时间戳
     * @return string
     */
    private function generateSign($msgData, $timestamp)
    {
        $str = $msgData . $timestamp . $this->config['checkword'];
        return base64_encode(md5($str, true));
    }
    
    /**
     * 验证响应签名
     * 
     * @param string $apiResultData API返回数据
     * @param string $msgDigest 签名
     * @return bool
     */
    private function verifySign($apiResultData, $msgDigest)
    {
        $str = $apiResultData . $this->config['checkword'];
        $expectedSign = base64_encode(md5($str, true));
        return $expectedSign === $msgDigest;
    }
    
    /**
     * 生成请求ID
     * 
     * @return string
     */
    private function generateRequestId()
    {
        return date('YmdHis') . sprintf('%06d', mt_rand(0, 999999));
    }
    
    /**
     * 获取快递类型列表
     * 
     * @return array
     */
    public static function getExpressTypes()
    {
        return [
            1 => '标准快递',
            2 => '顺丰特惠',
            3 => '电商特惠',
            5 => '顺丰次晨',
            6 => '顺丰即日',
            7 => '电商速配',
            15 => '生鲜速配',
            17 => '重货专运',
            19 => '冷运零担',
            25 => '顺丰微小件',     // 适用于小件商品（最大2kg）
            26 => '填仓标快',       // 非时效性要求，成本优化
        ];
    }
    
    /**
     * 获取付款方式列表
     * 
     * @return array
     */
    public static function getPayMethods()
    {
        return [
            1 => '寄付',
            2 => '到付',
            3 => '月结',
        ];
    }
}
