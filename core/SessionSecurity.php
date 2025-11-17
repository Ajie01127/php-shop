<?php

namespace Core;

/**
 * 会话安全管理类
 */
class SessionSecurity {
    
    /**
     * 初始化安全会话
     */
    public static function init() {
        // 设置安全的会话cookie参数
        $cookieParams = [
            'lifetime' => 7200, // 2小时过期
            'path' => '/',
            'domain' => $_SERVER['HTTP_HOST'] ?? '',
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
            'httponly' => true, // 防止JavaScript访问
            'samesite' => 'Lax' // CSRF防护
        ];
        
        session_set_cookie_params($cookieParams);
        
        // 使用安全的会话名称
        session_name('SECURE_SESSION_ID');
        
        session_start();
        
        // 防止会话固定攻击
        if (!isset($_SESSION['initiated'])) {
            session_regenerate_id(true);
            $_SESSION['initiated'] = true;
        }
        
        // 会话劫持检测
        if (self::isSessionHijacked()) {
            self::destroySession();
            self::redirectWithError('会话安全检测失败，请重新登录');
        }
    }
    
    /**
     * 检测会话劫持
     */
    private static function isSessionHijacked() {
        // 检查IP地址是否变化
        if (isset($_SESSION['ip_address']) && $_SESSION['ip_address'] !== $_SERVER['REMOTE_ADDR']) {
            return true;
        }
        
        // 检查User-Agent是否变化
        if (isset($_SESSION['user_agent']) && $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
            return true;
        }
        
        // 检查会话时间
        if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > 7200) {
            return true;
        }
        
        return false;
    }
    
    /**
     * 销毁会话
     */
    private static function destroySession() {
        session_unset();
        session_destroy();
        
        // 删除会话cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
    }
    
    /**
     * 带错误消息重定向
     */
    private static function redirectWithError($message) {
        $_SESSION['error'] = $message;
        header('Location: /admin/login');
        exit;
    }
    
    /**
     * 设置安全会话变量
     */
    public static function setSecure($key, $value) {
        $_SESSION[$key] = $value;
    }
    
    /**
     * 获取安全会话变量
     */
    public static function getSecure($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }
    
    /**
     * 检查管理员是否登录
     */
    public static function isAdminLoggedIn() {
        return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
    }
}