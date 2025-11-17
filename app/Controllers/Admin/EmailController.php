<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\EmailConfig;
use App\Models\EmailNotification;
use App\Models\EmailLog;
use App\Services\EmailService;

class EmailController extends BaseController
{
    private $emailConfig;
    private $emailNotification;
    private $emailLog;
    
    public function __construct()
    {
        parent::__construct();
        $this->emailConfig = new EmailConfig();
        $this->emailNotification = new EmailNotification();
        $this->emailLog = new EmailLog();
    }
    
    /**
     * 邮箱配置页面
     */
    public function config()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->updateConfig();
        }
        
        $config = $this->emailConfig->getConfig();
        
        return $this->render('admin/email/config', [
            'config' => $config,
            'page_title' => '邮箱配置'
        ]);
    }
    
    /**
     * 更新邮箱配置
     */
    private function updateConfig()
    {
        $data = [
            'driver' => $_POST['driver'] ?? 'smtp',
            'host' => $_POST['host'] ?? '',
            'port' => (int)($_POST['port'] ?? 587),
            'encryption' => $_POST['encryption'] ?? 'tls',
            'username' => $_POST['username'] ?? '',
            'password' => $_POST['password'] ?? '',
            'from_name' => $_POST['from_name'] ?? '',
            'from_email' => $_POST['from_email'] ?? '',
            'is_enabled' => isset($_POST['is_enabled']) ? 1 : 0
        ];
        
        // 验证必填字段
        if (empty($data['host']) || empty($data['username']) || empty($data['from_email'])) {
            $_SESSION['error'] = '请填写完整的邮箱配置信息';
            return redirect('/admin/email/config');
        }
        
        // 验证邮箱格式
        if (!filter_var($data['from_email'], FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = '发件人邮箱格式不正确';
            return redirect('/admin/email/config');
        }
        
        try {
            $result = $this->emailConfig->updateConfig($data);
            
            if ($result) {
                $_SESSION['success'] = '邮箱配置更新成功';
            } else {
                $_SESSION['error'] = '邮箱配置更新失败';
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = '邮箱配置更新失败：' . $e->getMessage();
        }
        
        return redirect('/admin/email/config');
    }
    
    /**
     * 测试邮箱配置
     */
    public function testConfig()
    {
        try {
            $result = $this->emailConfig->testConnection();
            
            if ($result['success']) {
                $this->jsonResponse(['success' => true, 'message' => $result['message']]);
            } else {
                $this->jsonResponse(['success' => false, 'message' => $result['message']]);
            }
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => '测试失败：' . $e->getMessage()]);
        }
    }
    
    /**
     * 发送测试邮件
     */
    public function sendTestEmail()
    {
        $toEmail = $_POST['test_email'] ?? '';
        
        if (empty($toEmail)) {
            $this->jsonResponse(['success' => false, 'message' => '请输入测试邮箱']);
        }
        
        if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            $this->jsonResponse(['success' => false, 'message' => '邮箱格式不正确']);
        }
        
        try {
            $emailService = new EmailService();
            $result = $emailService->testConfig($toEmail);
            
            $this->jsonResponse($result);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => '发送失败：' . $e->getMessage()]);
        }
    }
    
    /**
     * 通知场景列表
     */
    public function notifications()
    {
        $notifications = $this->emailNotification->getAllNotifications();
        $variableDescriptions = $this->emailNotification->getVariableDescriptions();
        
        return $this->render('admin/email/notifications', [
            'notifications' => $notifications,
            'variableDescriptions' => $variableDescriptions,
            'page_title' => '通知场景配置'
        ]);
    }
    
    /**
     * 编辑通知场景
     */
    public function editNotification($eventType = null)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->updateNotification($eventType);
        }
        
        $notification = null;
        if ($eventType) {
            $notification = $this->emailNotification->getByEventType($eventType);
        }
        
        $variableDescriptions = $this->emailNotification->getVariableDescriptions();
        
        return $this->render('admin/email/notification_form', [
            'notification' => $notification,
            'eventType' => $eventType,
            'variableDescriptions' => $variableDescriptions,
            'page_title' => $notification ? '编辑通知场景' : '新增通知场景'
        ]);
    }
    
    /**
     * 更新通知场景
     */
    private function updateNotification($eventType)
    {
        $data = [
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'template_subject' => $_POST['template_subject'] ?? '',
            'template_content' => $_POST['template_content'] ?? '',
            'is_enabled' => isset($_POST['is_enabled']) ? 1 : 0,
            'recipient_type' => $_POST['recipient_type'] ?? 'user',
            'recipients' => $_POST['recipients'] ?? [],
            'variables' => $_POST['variables'] ?? []
        ];
        
        // 验证必填字段
        if (empty($data['name']) || empty($data['template_subject']) || empty($data['template_content'])) {
            $_SESSION['error'] = '请填写完整的通知场景信息';
            return redirect('/admin/email/notifications');
        }
        
        try {
            $result = $this->emailNotification->updateNotification($eventType, $data);
            
            if ($result) {
                $_SESSION['success'] = '通知场景更新成功';
            } else {
                $_SESSION['error'] = '通知场景更新失败';
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = '通知场景更新失败：' . $e->getMessage();
        }
        
        return redirect('/admin/email/notifications');
    }
    
    /**
     * 批量更新通知状态
     */
    public function batchUpdateStatus()
    {
        $eventTypes = $_POST['event_types'] ?? [];
        $enabled = isset($_POST['enable']) ? 1 : 0;
        
        if (empty($eventTypes)) {
            $this->jsonResponse(['success' => false, 'message' => '请选择要操作的通知场景']);
        }
        
        try {
            $result = $this->emailNotification->batchUpdateStatus($eventTypes, $enabled);
            
            if ($result) {
                $this->jsonResponse(['success' => true, 'message' => '批量更新成功']);
            } else {
                $this->jsonResponse(['success' => false, 'message' => '批量更新失败']);
            }
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => '批量更新失败：' . $e->getMessage()]);
        }
    }
    
    /**
     * 邮件日志
     */
    public function logs()
    {
        $page = (int)($_GET['page'] ?? 1);
        $limit = (int)($_GET['limit'] ?? 20);
        
        $filters = [
            'search' => $_GET['search'] ?? '',
            'status' => $_GET['status'] ?? '',
            'event_type' => $_GET['event_type'] ?? '',
            'start_date' => $_GET['start_date'] ?? '',
            'end_date' => $_GET['end_date'] ?? ''
        ];
        
        $logs = $this->emailLog->getLogs($page, $limit, $filters);
        $notifications = $this->emailNotification->getAllNotifications();
        
        return $this->render('admin/email/logs', [
            'logs' => $logs,
            'notifications' => $notifications,
            'filters' => $filters,
            'page_title' => '邮件日志'
        ]);
    }
    
    /**
     * 查看邮件详情
     */
    public function viewLog($id)
    {
        $log = $this->emailLog->getLogById($id);
        
        if (!$log) {
            $_SESSION['error'] = '邮件日志不存在';
            return redirect('/admin/email/logs');
        }
        
        return $this->render('admin/email/log_detail', [
            'log' => $log,
            'page_title' => '邮件详情'
        ]);
    }
    
    /**
     * 删除邮件日志
     */
    public function deleteLog($id)
    {
        try {
            $result = $this->emailLog->deleteLogs($id);
            
            if ($result) {
                $_SESSION['success'] = '日志删除成功';
            } else {
                $_SESSION['error'] = '日志删除失败';
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = '日志删除失败：' . $e->getMessage();
        }
        
        return redirect('/admin/email/logs');
    }
    
    /**
     * 批量删除日志
     */
    public function batchDeleteLogs()
    {
        $ids = $_POST['ids'] ?? [];
        
        if (empty($ids)) {
            $this->jsonResponse(['success' => false, 'message' => '请选择要删除的日志']);
        }
        
        try {
            $result = $this->emailLog->deleteLogs($ids);
            
            if ($result) {
                $this->jsonResponse(['success' => true, 'message' => '批量删除成功']);
            } else {
                $this->jsonResponse(['success' => false, 'message' => '批量删除失败']);
            }
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => '批量删除失败：' . $e->getMessage()]);
        }
    }
    
    /**
     * 清空日志
     */
    public function clearLogs()
    {
        try {
            $result = $this->emailLog->clearAllLogs();
            
            if ($result) {
                $_SESSION['success'] = '日志清空成功';
            } else {
                $_SESSION['error'] = '日志清空失败';
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = '日志清空失败：' . $e->getMessage();
        }
        
        return redirect('/admin/email/logs');
    }
    
    /**
     * 重试失败邮件
     */
    public function retryFailed()
    {
        $ids = $_POST['ids'] ?? null;
        
        try {
            $results = $this->emailLog->retryFailedEmails($ids);
            
            $successCount = count(array_filter($results, function($result) {
                return $result['success'];
            }));
            
            $totalCount = count($results);
            
            $this->jsonResponse([
                'success' => true,
                'message' => "重试完成，成功 {$successCount}/{$totalCount} 封邮件",
                'results' => $results
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => '重试失败：' . $e->getMessage()]);
        }
    }
    
    /**
     * 统计信息
     */
    public function statistics()
    {
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        
        $overallStats = $this->emailLog->getStatistics($startDate, $endDate);
        $eventStats = $this->emailLog->getEventStatistics(10);
        $dailyStats = $this->emailLog->getDailyStatistics(30);
        
        return $this->render('admin/email/statistics', [
            'overallStats' => $overallStats,
            'eventStats' => $eventStats,
            'dailyStats' => $dailyStats,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'page_title' => '邮件统计'
        ]);
    }
    
    /**
     * 预览邮件模板
     */
    public function previewTemplate()
    {
        $eventType = $_GET['event_type'] ?? '';
        $variables = $_GET['variables'] ?? [];
        
        if (empty($eventType)) {
            $this->jsonResponse(['success' => false, 'message' => '事件类型不能为空']);
        }
        
        try {
            $notification = $this->emailNotification->getByEventType($eventType);
            
            if (!$notification) {
                $this->jsonResponse(['success' => false, 'message' => '通知场景不存在']);
            }
            
            $emailService = new EmailService();
            $subject = $emailService->replaceVariables($notification['template_subject'], $variables);
            $content = $emailService->replaceVariables($notification['template_content'], $variables);
            
            $this->jsonResponse([
                'success' => true,
                'subject' => $subject,
                'content' => $content
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => '预览失败：' . $e->getMessage()]);
        }
    }
}