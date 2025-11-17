<?php

namespace App\Services;

use Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class EmailService
{
    private $config;
    private $mailer;
    
    public function __construct()
    {
        $this->config = $this->getEmailConfig();
        $this->mailer = new PHPMailer(true);
    }
    
    /**
     * 获取邮箱配置
     */
    private function getEmailConfig()
    {
        try {
            $pdo = new \PDO(
                "mysql:host=" . config('database.host') . ";dbname=" . config('database.database'),
                config('database.username'),
                config('database.password')
            );
            
            $stmt = $pdo->prepare("SELECT * FROM mall_email_configs ORDER BY id DESC LIMIT 1");
            $stmt->execute();
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * 配置PHPMailer
     */
    private function configureMailer()
    {
        if (!$this->config || !$this->config['is_enabled']) {
            throw new Exception('邮箱服务未配置或未启用');
        }
        
        // SMTP配置
        $this->mailer->isSMTP();
        $this->mailer->Host = $this->config['host'];
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $this->config['username'];
        $this->mailer->Password = $this->config['password'];
        $this->mailer->SMTPSecure = $this->config['encryption'];
        $this->mailer->Port = $this->config['port'];
        
        // 发件人设置
        $this->mailer->setFrom($this->config['from_email'], $this->config['from_name']);
        
        // 字符编码
        $this->mailer->CharSet = 'UTF-8';
        
        // 调试模式（生产环境关闭）
        if (config('app.debug')) {
            $this->mailer->SMTPDebug = SMTP::DEBUG_SERVER;
            $this->mailer->Debugoutput = function($str, $level) {
                error_log("SMTP Debug Level $level: $str");
            };
        }
    }
    
    /**
     * 发送邮件
     */
    public function send($to, $subject, $content, $isHtml = true, $attachments = [])
    {
        try {
            $this->configureMailer();
            
            // 接收者
            $this->mailer->addAddress($to);
            
            // 邮件内容
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $content;
            
            if ($isHtml) {
                $this->mailer->isHTML(true);
                // 添加纯文本版本
                $this->mailer->AltText = strip_tags($content);
            } else {
                $this->mailer->isHTML(false);
            }
            
            // 添加附件
            foreach ($attachments as $attachment) {
                if (is_array($attachment)) {
                    $this->mailer->addAttachment($attachment['path'], $attachment['name'] ?? basename($attachment['path']));
                } else {
                    $this->mailer->addAttachment($attachment);
                }
            }
            
            $result = $this->mailer->send();
            
            // 记录发送日志
            $this->logEmail($to, $subject, $content, 'sent', null);
            
            return ['success' => true, 'message' => '邮件发送成功'];
            
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            
            // 记录失败日志
            $this->logEmail($to, $subject, $content, 'failed', $errorMessage);
            
            return ['success' => false, 'message' => $errorMessage];
        }
    }
    
    /**
     * 批量发送邮件
     */
    public function sendBatch($recipients, $subject, $content, $isHtml = true, $delay = 1)
    {
        $results = [];
        
        foreach ($recipients as $to) {
            $result = $this->send($to, $subject, $content, $isHtml);
            $results[$to] = $result;
            
            // 延迟发送，避免被限制
            if ($delay > 0) {
                sleep($delay);
            }
        }
        
        return $results;
    }
    
    /**
     * 发送模板邮件
     */
    public function sendTemplate($to, $eventType, $variables = [])
    {
        try {
            $template = $this->getNotificationTemplate($eventType);
            
            if (!$template || !$template['is_enabled']) {
                return ['success' => false, 'message' => '邮件模板不存在或未启用'];
            }
            
            // 替换模板变量
            $subject = $this->replaceVariables($template['template_subject'], $variables);
            $content = $this->replaceVariables($template['template_content'], $variables);
            
            return $this->send($to, $subject, $content, true);
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * 获取通知模板
     */
    private function getNotificationTemplate($eventType)
    {
        try {
            $pdo = new \PDO(
                "mysql:host=" . config('database.host') . ";dbname=" . config('database.database'),
                config('database.username'),
                config('database.password')
            );
            
            $stmt = $pdo->prepare("SELECT * FROM mall_email_notifications WHERE event_type = ?");
            $stmt->execute([$eventType]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * 替换模板变量
     */
    private function replaceVariables($template, $variables)
    {
        foreach ($variables as $key => $value) {
            $template = str_replace('{{' . $key . '}}', $value, $template);
        }
        return $template;
    }
    
    /**
     * 记录邮件发送日志
     */
    private function logEmail($to, $subject, $content, $status, $errorMessage = null, $eventType = null)
    {
        try {
            $pdo = new \PDO(
                "mysql:host=" . config('database.host') . ";dbname=" . config('database.database'),
                config('database.username'),
                config('database.password')
            );
            
            $stmt = $pdo->prepare("
                INSERT INTO mall_email_logs 
                (to_email, subject, content, status, error_message, sent_at, event_type) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $to,
                $subject,
                $content,
                $status,
                $errorMessage,
                $status === 'sent' ? date('Y-m-d H:i:s') : null,
                $eventType
            ]);
        } catch (Exception $e) {
            error_log("记录邮件日志失败: " . $e->getMessage());
        }
    }
    
    /**
     * 测试邮箱配置
     */
    public function testConfig($testEmail = null)
    {
        try {
            $this->configureMailer();
            
            if (!$testEmail) {
                $testEmail = $this->config['from_email'];
            }
            
            $subject = '邮箱配置测试';
            $content = '<h3>邮箱配置测试</h3><p>这是一封测试邮件，如果您收到此邮件，说明邮箱配置正确。</p><p>测试时间：' . date('Y-m-d H:i:s') . '</p>';
            
            $result = $this->send($testEmail, $subject, $content);
            
            if ($result['success']) {
                return ['success' => true, 'message' => '配置测试成功，测试邮件已发送'];
            } else {
                return ['success' => false, 'message' => '配置测试失败：' . $result['message']];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => '配置测试失败：' . $e->getMessage()];
        }
    }
    
    /**
     * 获取邮件发送统计
     */
    public function getStatistics($startDate = null, $endDate = null)
    {
        try {
            $pdo = new \PDO(
                "mysql:host=" . config('database.host') . ";dbname=" . config('database.database'),
                config('database.username'),
                config('database.password')
            );
            
            $sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending
                FROM mall_email_logs";
            
            $params = [];
            
            if ($startDate && $endDate) {
                $sql .= " WHERE created_at BETWEEN ? AND ?";
                $params[] = $startDate;
                $params[] = $endDate;
            }
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetch(\PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            return ['total' => 0, 'sent' => 0, 'failed' => 0, 'pending' => 0];
        }
    }
    
    /**
     * 获取最近的邮件日志
     */
    public function getRecentLogs($limit = 20)
    {
        try {
            $pdo = new \PDO(
                "mysql:host=" . config('database.host') . ";dbname=" . config('database.database'),
                config('database.username'),
                config('database.password')
            );
            
            $stmt = $pdo->prepare("
                SELECT * FROM mall_email_logs 
                ORDER BY created_at DESC 
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            return [];
        }
    }
}