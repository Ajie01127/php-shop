<?php

namespace App\Services;

use Exception;

/**
 * 腾讯云短信服务类
 * 基于腾讯云SMS SDK的短信发送服务
 */
class TencentSmsService
{
    private $config;
    private $secretId;
    private $secretKey;
    private $sdkAppId;
    private $signName;
    private $endpoint;

    /**
     * 构造函数
     */
    public function __construct()
    {
        // 从系统配置获取短信设置
        $this->loadConfig();
    }

    /**
     * 加载配置
     */
    private function loadConfig()
    {
        // 使用站点设置获取配置
        $settingModel = new \App\Models\SiteSetting();
        
        $this->secretId = $settingModel->get('sms_secret_id', '');
        $this->secretKey = $settingModel->get('sms_secret_key', '');
        $this->sdkAppId = $settingModel->get('sms_sdk_app_id', '');
        $this->signName = $settingModel->get('sms_sign_name', '');
        $this->endpoint = 'sms.tencentcloudapi.com';
        
        $this->config = [
            'enable' => $settingModel->get('sms_enable', '0') === '1',
            
            // 场景启用设置
            'scene_login' => $settingModel->get('sms_scene_login', '1') === '1',
            'scene_register' => $settingModel->get('sms_scene_register', '1') === '1',
            'scene_reset' => $settingModel->get('sms_scene_reset', '1') === '1',
            'scene_order' => $settingModel->get('sms_scene_order', '1') === '1',
            'scene_payment' => $settingModel->get('sms_scene_payment', '1') === '1',
            'scene_shipping' => $settingModel->get('sms_scene_shipping', '1') === '1',
            'scene_refund' => $settingModel->get('sms_scene_refund', '1') === '1',
            
            // 注册配置
            'register_enable' => $settingModel->get('sms_register_enable', '1') === '1',
            'auto_bind' => $settingModel->get('sms_auto_bind', '1') === '1',
            
            // 模板设置
            'template_id_login' => $settingModel->get('sms_template_id_login', ''),
            'template_id_register' => $settingModel->get('sms_template_id_register', ''),
            'template_id_reset' => $settingModel->get('sms_template_id_reset', ''),
            'template_id_order' => $settingModel->get('sms_template_id_order', ''),
            'template_id_payment' => $settingModel->get('sms_template_id_payment', ''),
            'template_id_shipping' => $settingModel->get('sms_template_id_shipping', ''),
            'template_id_refund' => $settingModel->get('sms_template_id_refund', ''),
        ];
    }

    /**
     * 检查短信服务是否可用
     */
    public function isEnabled()
    {
        return $this->config['enable'] && 
               !empty($this->secretId) && 
               !empty($this->secretKey) && 
               !empty($this->sdkAppId) && 
               !empty($this->signName);
    }

    /**
     * 检查指定场景是否启用
     * @param string $scene 场景标识
     * @return bool
     */
    public function isSceneEnabled($scene)
    {
        if (!$this->isEnabled()) {
            return false;
        }
        
        $sceneConfigMap = [
            'login' => 'scene_login',
            'register' => 'scene_register',
            'reset' => 'scene_reset',
            'order' => 'scene_order',
            'payment' => 'scene_payment',
            'shipping' => 'scene_shipping',
            'refund' => 'scene_refund'
        ];
        
        if (isset($sceneConfigMap[$scene])) {
            return $this->config[$sceneConfigMap[$scene]];
        }
        
        return true; // 默认启用
    }

    /**
     * 检查是否允许短信注册
     * @return bool
     */
    public function isRegisterEnabled()
    {
        return $this->isEnabled() && $this->config['register_enable'];
    }

    /**
     * 检查是否启用小程序手机号自动绑定
     * @return bool
     */
    public function isAutoBindEnabled()
    {
        return $this->isEnabled() && $this->config['auto_bind'];
    }

    /**
     * 发送短信
     * @param string $phone 手机号
     * @param string $templateId 模板ID
     * @param array $params 模板参数
     * @param string $scene 场景标识
     * @return array
     */
    public function sendSms($phone, $templateId, $params = [], $scene = '')
    {
        if (!$this->isEnabled()) {
            return [
                'success' => false,
                'message' => '短信服务未启用或配置不完整'
            ];
        }

        // 检查场景是否启用
        if (!empty($scene) && !$this->isSceneEnabled($scene)) {
            return [
                'success' => false,
                'message' => '该短信场景未启用'
            ];
        }

        // 验证手机号格式
        if (!$this->validatePhone($phone)) {
            return [
                'success' => false,
                'message' => '手机号格式不正确'
            ];
        }

        try {
            // 构建请求参数
            $requestData = [
                'PhoneNumberSet' => ["+86" . $phone],
                'SmsSdkAppId' => $this->sdkAppId,
                'SignName' => $this->signName,
                'TemplateId' => $templateId,
            ];

            // 添加模板参数
            if (!empty($params)) {
                $requestData['TemplateParamSet'] = array_values($params);
            }

            // 记录发送日志
            $this->logSmsAttempt($phone, $templateId, $scene, $params);

            // 使用CURL发送请求到腾讯云API
            $result = $this->callTencentSmsApi($requestData);

            // 记录发送结果
            $this->logSmsResult($phone, $templateId, $result);

            if ($result['success']) {
                return [
                    'success' => true,
                    'message' => '短信发送成功',
                    'data' => $result['data']
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $result['message'] ?? '短信发送失败'
                ];
            }

        } catch (Exception $e) {
            // 记录异常日志
            $this->logSmsError($phone, $templateId, $e->getMessage());
            
            return [
                'success' => false,
                'message' => '短信服务异常: ' . $e->getMessage()
            ];
        }
    }

    /**
     * 调用腾讯云短信API
     */
    private function callTencentSmsApi($data)
    {
        // 构建签名和时间戳
        $timestamp = time();
        $nonce = uniqid();
        
        // 构建规范化请求字符串
        $canonicalRequest = $this->buildCanonicalRequest($data, $timestamp);
        
        // 构建签名字符串
        $stringToSign = "TC3-HMAC-SHA256\n" . $timestamp . "\n" . 
                       $this->buildCredentialScope($timestamp) . "\n" . 
                       hash('SHA256', $canonicalRequest);
        
        // 计算签名
        $signature = $this->calculateSignature($stringToSign, $timestamp);
        
        // 构建Authorization头
        $authorization = 'TC3-HMAC-SHA256 ' . 
                        'Credential=' . $this->secretId . '/' . $this->buildCredentialScope($timestamp) . ', ' .
                        'SignedHeaders=content-type;host, ' .
                        'Signature=' . $signature;

        // 发送HTTP请求
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://' . $this->endpoint . '/',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Authorization: ' . $authorization,
                'Content-Type: application/json',
                'X-TC-Action: SendSms',
                'X-TC-Version: 2021-01-11',
                'X-TC-Timestamp: ' . $timestamp,
                'X-TC-Region: ap-guangzhou',
            ],
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception('CURL请求失败: ' . $error);
        }

        $responseData = json_decode($response, true);
        
        if ($httpCode === 200 && isset($responseData['Response']['SendStatusSet'][0]['Code'])) {
            $sendStatus = $responseData['Response']['SendStatusSet'][0];
            
            if ($sendStatus['Code'] === 'Ok') {
                return [
                    'success' => true,
                    'data' => [
                        'request_id' => $responseData['Response']['RequestId'] ?? '',
                        'serial_no' => $sendStatus['SerialNo'] ?? '',
                        'phone' => $sendStatus['PhoneNumber'] ?? ''
                    ]
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $this->getErrorMessage($sendStatus['Code'])
                ];
            }
        } else {
            return [
                'success' => false,
                'message' => 'API请求失败: ' . ($responseData['Response']['Error']['Message'] ?? '未知错误')
            ];
        }
    }

    /**
     * 构建规范化请求字符串
     */
    private function buildCanonicalRequest($data, $timestamp)
    {
        $httpRequestMethod = 'POST';
        $canonicalUri = '/';
        $canonicalQueryString = '';
        $canonicalHeaders = "content-type:application/json\nhost:{$this->endpoint}\n";
        $signedHeaders = 'content-type;host';
        $hashedRequestPayload = hash('SHA256', json_encode($data));
        
        return $httpRequestMethod . "\n" .
               $canonicalUri . "\n" .
               $canonicalQueryString . "\n" .
               $canonicalHeaders . "\n" .
               $signedHeaders . "\n" .
               $hashedRequestPayload;
    }

    /**
     * 构建凭证范围
     */
    private function buildCredentialScope($timestamp)
    {
        $date = gmdate('Y-m-d', $timestamp);
        return $date . '/sms/tc3_request';
    }

    /**
     * 计算签名
     */
    private function calculateSignature($stringToSign, $timestamp)
    {
        $date = gmdate('Y-m-d', $timestamp);
        
        // 计算日期密钥
        $secretDate = hash_hmac('SHA256', $date, "TC3" . $this->secretKey, true);
        // 计算服务密钥
        $secretService = hash_hmac('SHA256', 'sms', $secretDate, true);
        // 计算签名密钥
        $secretSigning = hash_hmac('SHA256', 'tc3_request', $secretService, true);
        // 计算签名
        $signature = hash_hmac('SHA256', $stringToSign, $secretSigning);
        
        return $signature;
    }

    /**
     * 验证手机号格式
     */
    private function validatePhone($phone)
    {
        return preg_match('/^1[3-9]\d{9}$/', $phone) === 1;
    }

    /**
     * 获取错误信息
     */
    private function getErrorMessage($code)
    {
        $errorMessages = [
            'FailedOperation.PhoneNumberInBlacklist' => '手机号在黑名单中',
            'FailedOperation.ContainSensitiveWord' => '短信内容包含敏感词',
            'FailedOperation.InsufficientBalanceInSmsPackage' => '套餐包余量不足',
            'FailedOperation.MarketingSendTimeConstraint' => '营销短信发送时间限制',
            'FailedOperation.SignatureIncorrectOrUnapproved' => '签名不正确或未审核通过',
            'FailedOperation.TemplateIncorrectOrUnapproved' => '模板不正确或未审核通过',
            'FailedOperation.ExceededSendLimit' => '发送频率超限',
            'FailedOperation.DailySendLimitExceeded' => '日发送量超限',
            'InvalidParameterValue.IncorrectPhoneNumber' => '手机号格式错误',
            'InvalidParameterValue.TemplateParameterFormatError' => '模板参数格式错误',
        ];

        return $errorMessages[$code] ?? '短信发送失败: ' . $code;
    }

    /**
     * 记录短信发送尝试
     */
    private function logSmsAttempt($phone, $templateId, $scene, $params)
    {
        // 记录到数据库或日志文件
        $logData = [
            'phone' => substr($phone, 0, 3) . '****' . substr($phone, -4),
            'template_id' => $templateId,
            'scene' => $scene,
            'params' => json_encode($params),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // 这里可以记录到数据库或日志文件
        // file_put_contents(__DIR__ . '/../../logs/sms.log', 
        //     date('Y-m-d H:i:s') . " SMS Attempt: " . json_encode($logData) . "\n", 
        //     FILE_APPEND);
    }

    /**
     * 记录短信发送结果
     */
    private function logSmsResult($phone, $templateId, $result)
    {
        $logData = [
            'phone' => substr($phone, 0, 3) . '****' . substr($phone, -4),
            'template_id' => $templateId,
            'success' => $result['success'],
            'message' => $result['message'] ?? '',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // file_put_contents(__DIR__ . '/../../logs/sms.log', 
        //     date('Y-m-d H:i:s') . " SMS Result: " . json_encode($logData) . "\n", 
        //     FILE_APPEND);
    }

    /**
     * 记录短信错误
     */
    private function logSmsError($phone, $templateId, $error)
    {
        $logData = [
            'phone' => substr($phone, 0, 3) . '****' . substr($phone, -4),
            'template_id' => $templateId,
            'error' => $error,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // file_put_contents(__DIR__ . '/../../logs/sms_error.log', 
        //     date('Y-m-d H:i:s') . " SMS Error: " . json_encode($logData) . "\n", 
        //     FILE_APPEND);
    }

    /**
     * 发送登录验证码
     */
    public function sendLoginCode($phone, $code)
    {
        return $this->sendSms($phone, $this->config['template_id_login'], ['code' => $code], 'login');
    }

    /**
     * 发送注册验证码
     */
    public function sendRegisterCode($phone, $code)
    {
        return $this->sendSms($phone, $this->config['template_id_register'], ['code' => $code], 'register');
    }

    /**
     * 发送订单确认短信
     */
    public function sendOrderConfirm($phone, $orderNo, $amount)
    {
        return $this->sendSms($phone, $this->config['template_id_order'], 
            ['order_no' => $orderNo, 'amount' => $amount], 'order_confirm');
    }

    /**
     * 发送支付成功短信
     */
    public function sendPaymentSuccess($phone, $orderNo, $amount)
    {
        return $this->sendSms($phone, $this->config['template_id_payment'], 
            ['order_no' => $orderNo, 'amount' => $amount], 'payment_success');
    }

    /**
     * 发送发货通知短信
     */
    public function sendShippingNotice($phone, $orderNo, $trackingNo)
    {
        return $this->sendSms($phone, $this->config['template_id_shipping'], 
            ['order_no' => $orderNo, 'tracking_no' => $trackingNo], 'shipping');
    }

    /**
     * 发送退款通知短信
     */
    public function sendRefundNotice($phone, $orderNo, $refundAmount)
    {
        return $this->sendSms($phone, $this->config['template_id_refund'], 
            ['order_no' => $orderNo, 'amount' => $refundAmount], 'refund');
    }

    /**
     * 测试短信服务
     */
    public function testConnection()
    {
        if (!$this->isEnabled()) {
            return [
                'success' => false,
                'message' => '短信服务未启用或配置不完整'
            ];
        }

        try {
            // 发送测试短信（使用虚拟模板）
            $testResult = $this->callTencentSmsApi([
                'PhoneNumberSet' => ["+8613800138000"], // 测试号码
                'SmsSdkAppId' => $this->sdkAppId,
                'SignName' => $this->signName,
                'TemplateId' => '000000', // 测试模板
            ]);

            if ($testResult['success']) {
                return [
                    'success' => true,
                    'message' => '短信服务连接正常'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => '短信服务连接失败: ' . $testResult['message']
                ];
            }

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => '短信服务异常: ' . $e->getMessage()
            ];
        }
    }
}