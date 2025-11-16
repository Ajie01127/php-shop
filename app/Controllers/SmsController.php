<?php

namespace App\Controllers;

use Core\Controller;
use App\Models\SiteSetting;
use App\Services\TencentSmsService;

/**
 * 短信控制器
 * 处理短信发送、配置管理等操作
 */
class SmsController extends Controller
{
    private $smsService;
    
    public function __construct()
    {
        parent::__construct();
        $this->smsService = new TencentSmsService();
    }

    /**
     * 发送短信验证码
     */
    public function sendCode()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(405, '请求方法不允许');
        }

        $phone = $_POST['phone'] ?? '';
        $type = $_POST['type'] ?? 'register'; // register, login, reset
        $captcha = $_POST['captcha'] ?? '';

        // 验证验证码
        if (!$this->validateCaptcha($captcha)) {
            return $this->jsonResponse(400, '图形验证码错误');
        }

        // 验证手机号格式
        if (!preg_match('/^1[3-9]\d{9}$/', $phone)) {
            return $this->jsonResponse(400, '手机号格式不正确');
        }

        // 生成验证码（6位数字）
        $code = sprintf('%06d', mt_rand(0, 999999));
        
        // 发送短信
        $result = $this->smsService->sendSms(
            $phone, 
            $this->getTemplateByType($type),
            ['code' => $code]
        );

        if ($result['success']) {
            // 保存验证码到session（实际项目中可以保存到数据库）
            $_SESSION['sms_code'] = [
                'phone' => $phone,
                'code' => $code,
                'type' => $type,
                'expire_time' => time() + 300 // 5分钟过期
            ];

            return $this->jsonResponse(200, '验证码发送成功', [
                'expire_time' => 300
            ]);
        } else {
            return $this->jsonResponse(500, $result['message']);
        }
    }

    /**
     * 验证短信验证码
     */
    public function verifyCode()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(405, '请求方法不允许');
        }

        $phone = $_POST['phone'] ?? '';
        $code = $_POST['code'] ?? '';
        $type = $_POST['type'] ?? 'register';

        // 检查session中是否存在验证码
        if (!isset($_SESSION['sms_code'])) {
            return $this->jsonResponse(400, '验证码不存在或已过期');
        }

        $smsCode = $_SESSION['sms_code'];

        // 检查是否过期
        if (time() > $smsCode['expire_time']) {
            unset($_SESSION['sms_code']);
            return $this->jsonResponse(400, '验证码已过期');
        }

        // 检查手机号和验证码是否匹配
        if ($smsCode['phone'] !== $phone || $smsCode['code'] !== $code || $smsCode['type'] !== $type) {
            return $this->jsonResponse(400, '验证码错误');
        }

        // 验证成功，清除session
        unset($_SESSION['sms_code']);

        return $this->jsonResponse(200, '验证成功');
    }

    /**
     * 发送订单通知短信
     */
    public function sendOrderNotice()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(405, '请求方法不允许');
        }

        $orderId = $_POST['order_id'] ?? 0;
        $phone = $_POST['phone'] ?? '';
        $type = $_POST['notice_type'] ?? 'order_confirm'; // order_confirm, payment_success, shipping, refund

        // 获取订单信息（这里需要根据实际订单模型调整）
        $orderInfo = $this->getOrderInfo($orderId);
        if (!$orderInfo) {
            return $this->jsonResponse(404, '订单不存在');
        }

        // 发送短信
        $result = $this->smsService->sendSms(
            $phone,
            $this->getTemplateByType($type),
            [
                'order_no' => $orderInfo['order_no'],
                'amount' => $orderInfo['total_amount'],
                'tracking_no' => $orderInfo['tracking_no'] ?? ''
            ]
        );

        if ($result['success']) {
            return $this->jsonResponse(200, '短信发送成功');
        } else {
            return $this->jsonResponse(500, $result['message']);
        }
    }

    /**
     * 测试短信服务连接
     */
    public function testConnection()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(405, '请求方法不允许');
        }

        $result = $this->smsService->testConnection();

        if ($result['success']) {
            return $this->jsonResponse(200, $result['message']);
        } else {
            return $this->jsonResponse(500, $result['message']);
        }
    }

    /**
     * 获取短信发送统计
     */
    public function getStatistics()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            return $this->jsonResponse(405, '请求方法不允许');
        }

        // 这里可以从数据库获取统计信息
        $statistics = [
            'today_count' => 0,
            'month_count' => 0,
            'success_rate' => 0,
            'total_count' => 0
        ];

        return $this->jsonResponse(200, '获取成功', $statistics);
    }

    /**
     * 验证图形验证码
     */
    private function validateCaptcha($captcha)
    {
        // 检查session中的验证码
        if (!isset($_SESSION['captcha']) || empty($_SESSION['captcha'])) {
            return false;
        }

        $sessionCaptcha = $_SESSION['captcha'];
        
        // 验证后清除session
        unset($_SESSION['captcha']);
        
        return strtolower($captcha) === strtolower($sessionCaptcha);
    }

    /**
     * 根据类型获取模板ID
     */
    private function getTemplateByType($type)
    {
        $settingModel = new SiteSetting();
        
        $templateMap = [
            'register' => 'sms_template_id_register',
            'login' => 'sms_template_id_login',
            'reset' => 'sms_template_id_reset',
            'order_confirm' => 'sms_template_id_order',
            'payment_success' => 'sms_template_id_payment',
            'shipping' => 'sms_template_id_shipping',
            'refund' => 'sms_template_id_refund'
        ];

        $templateKey = $templateMap[$type] ?? 'sms_template_id_register';
        return $settingModel->get($templateKey, '');
    }

    /**
     * 获取订单信息
     */
    private function getOrderInfo($orderId)
    {
        // 这里需要根据实际的订单模型调整
        // 返回模拟数据
        return [
            'order_no' => 'ORD' . date('YmdHis') . $orderId,
            'total_amount' => '99.00',
            'tracking_no' => 'SF' . date('Ymd') . str_pad($orderId, 6, '0', STR_PAD_LEFT)
        ];
    }

    /**
     * JSON响应封装
     */
    private function jsonResponse($code, $message, $data = [])
    {
        http_response_code($code);
        header('Content-Type: application/json');
        
        echo json_encode([
            'code' => $code,
            'message' => $message,
            'data' => $data
        ]);
        exit;
    }
}