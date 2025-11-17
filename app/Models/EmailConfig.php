<?php

namespace App\Models;

use Core\Model;

class EmailConfig extends Model
{
    protected $table = 'email_configs';
    
    /**
     * 获取邮箱配置
     */
    public function getConfig()
    {
        $sql = "SELECT * FROM {$this->getTableName()} ORDER BY id DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * 更新邮箱配置
     */
    public function updateConfig($data)
    {
        // 获取现有配置
        $existing = $this->getConfig();
        
        if ($existing) {
            // 更新现有配置
            $sql = "UPDATE {$this->getTableName()} SET 
                    driver = ?, host = ?, port = ?, encryption = ?, 
                    username = ?, password = ?, from_name = ?, 
                    from_email = ?, is_enabled = ?, updated_at = NOW()
                    WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $data['driver'],
                $data['host'],
                $data['port'],
                $data['encryption'],
                $data['username'],
                $data['password'],
                $data['from_name'],
                $data['from_email'],
                $data['is_enabled'],
                $existing['id']
            ]);
        } else {
            // 创建新配置
            $sql = "INSERT INTO {$this->getTableName()} 
                    (driver, host, port, encryption, username, password, 
                     from_name, from_email, is_enabled, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $data['driver'],
                $data['host'],
                $data['port'],
                $data['encryption'],
                $data['username'],
                $data['password'],
                $data['from_name'],
                $data['from_email'],
                $data['is_enabled']
            ]);
        }
    }
    
    /**
     * 测试邮箱连接
     */
    public function testConnection()
    {
        $config = $this->getConfig();
        
        if (!$config) {
            return ['success' => false, 'message' => '未找到邮箱配置'];
        }
        
        try {
            $mailer = new \PHPMailer\PHPMailer\PHPMailer(true);
            
            // SMTP配置
            $mailer->isSMTP();
            $mailer->Host = $config['host'];
            $mailer->SMTPAuth = true;
            $mailer->Username = $config['username'];
            $mailer->Password = $config['password'];
            $mailer->SMTPSecure = $config['encryption'];
            $mailer->Port = $config['port'];
            
            // 尝试连接
            $mailer->SMTPDebug = 0; // 关闭调试输出
            
            // 设置发件人
            $mailer->setFrom($config['from_email'], $config['from_name']);
            $mailer->addAddress($config['from_email']); // 发送测试邮件给自己
            
            $mailer->Subject = '连接测试邮件';
            $mailer->Body = '这是一封连接测试邮件，如果您收到此邮件，说明邮箱配置正确。';
            
            $result = $mailer->send();
            
            return ['success' => true, 'message' => '邮箱连接测试成功'];
            
        } catch (\Exception $e) {
            return ['success' => false, 'message' => '连接测试失败: ' . $e->getMessage()];
        }
    }
}