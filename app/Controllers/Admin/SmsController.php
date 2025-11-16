<?php

namespace App\Controllers\Admin;

use Core\Controller;
use App\Models\SiteSetting;
use App\Services\TencentSmsService;

/**
 * 后台短信控制器
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
     * 短信设置页面
     */
    public function settings()
    {
        // 检查管理员权限
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header('Location: /admin/login');
            exit;
        }

        // 获取当前短信设置
        $settingModel = new SiteSetting();
        $smsSettings = $settingModel->getMultiple([
            'sms_enable',
            'sms_secret_id',
            'sms_secret_key',
            'sms_sdk_app_id',
            'sms_sign_name',
            
            // 场景启用设置
            'sms_scene_login',
            'sms_scene_register',
            'sms_scene_reset',
            'sms_scene_order',
            'sms_scene_payment',
            'sms_scene_shipping',
            'sms_scene_refund',
            
            // 注册配置
            'sms_register_enable',
            'sms_auto_bind',
            
            // 模板设置
            'sms_template_id_login',
            'sms_template_id_register',
            'sms_template_id_reset',
            'sms_template_id_order',
            'sms_template_id_payment',
            'sms_template_id_shipping',
            'sms_template_id_refund'
        ]);

        $data = [
            'title' => '短信设置 - 私域商城后台',
            'settings' => $smsSettings
        ];

        // 渲染模板
        $this->render('admin/sms_settings', $data);
    }

    /**
     * 更新短信设置
     */
    public function updateSettings()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(405, '请求方法不允许');
        }

        // 检查管理员权限
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            $this->jsonResponse(403, '无权限操作');
        }

        $updateData = [
            // 基本设置
            'sms_enable' => $_POST['sms_enable'] ?? '0',
            'sms_secret_id' => trim($_POST['sms_secret_id'] ?? ''),
            'sms_secret_key' => trim($_POST['sms_secret_key'] ?? ''),
            'sms_sdk_app_id' => trim($_POST['sms_sdk_app_id'] ?? ''),
            'sms_sign_name' => trim($_POST['sms_sign_name'] ?? ''),
            
            // 场景启用设置
            'sms_scene_login' => $_POST['sms_scene_login'] ?? '0',
            'sms_scene_register' => $_POST['sms_scene_register'] ?? '0',
            'sms_scene_reset' => $_POST['sms_scene_reset'] ?? '0',
            'sms_scene_order' => $_POST['sms_scene_order'] ?? '0',
            'sms_scene_payment' => $_POST['sms_scene_payment'] ?? '0',
            'sms_scene_shipping' => $_POST['sms_scene_shipping'] ?? '0',
            'sms_scene_refund' => $_POST['sms_scene_refund'] ?? '0',
            
            // 注册配置
            'sms_register_enable' => $_POST['sms_register_enable'] ?? '0',
            'sms_auto_bind' => $_POST['sms_auto_bind'] ?? '0',
            
            // 模板设置
            'sms_template_id_login' => trim($_POST['sms_template_id_login'] ?? ''),
            'sms_template_id_register' => trim($_POST['sms_template_id_register'] ?? ''),
            'sms_template_id_reset' => trim($_POST['sms_template_id_reset'] ?? ''),
            'sms_template_id_order' => trim($_POST['sms_template_id_order'] ?? ''),
            'sms_template_id_payment' => trim($_POST['sms_template_id_payment'] ?? ''),
            'sms_template_id_shipping' => trim($_POST['sms_template_id_shipping'] ?? ''),
            'sms_template_id_refund' => trim($_POST['sms_template_id_refund'] ?? ''),
        ];

        $settingModel = new SiteSetting();
        
        if ($settingModel->updateMultiple($updateData)) {
            $this->jsonResponse(200, '短信设置保存成功');
        } else {
            $this->jsonResponse(500, '保存失败，请重试');
        }
    }

    /**
     * 测试短信服务连接
     */
    public function testConnection()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(405, '请求方法不允许');
        }

        // 检查管理员权限
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            $this->jsonResponse(403, '无权限操作');
        }

        // 临时更新设置用于测试
        $testData = [
            'sms_secret_id' => trim($_POST['sms_secret_id'] ?? ''),
            'sms_secret_key' => trim($_POST['sms_secret_key'] ?? ''),
            'sms_sdk_app_id' => trim($_POST['sms_sdk_app_id'] ?? ''),
            'sms_sign_name' => trim($_POST['sms_sign_name'] ?? ''),
        ];

        // 测试连接
        $result = $this->smsService->testConnection();

        if ($result['success']) {
            $this->jsonResponse(200, $result['message']);
        } else {
            $this->jsonResponse(500, $result['message']);
        }
    }

    /**
     * 短信统计页面
     */
    public function statistics()
    {
        // 检查管理员权限
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header('Location: /admin/login');
            exit;
        }

        // 获取统计信息（这里可以从数据库获取实际数据）
        $statistics = [
            'today_count' => 0,
            'month_count' => 0,
            'success_rate' => 0,
            'total_count' => 0,
            'today_success' => 0,
            'today_fail' => 0
        ];

        $data = [
            'title' => '短信统计 - 私域商城后台',
            'statistics' => $statistics
        ];

        // 渲染模板
        $this->render('admin/sms_statistics', $data);
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

    /**
     * 渲染模板
     */
    private function render($view, $data = [])
    {
        extract($data);
        include_once __DIR__ . '/../../views/' . $view . '.php';
        exit;
    }
}