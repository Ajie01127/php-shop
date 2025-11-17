<?php

namespace Core;

/**
 * 安全头管理类
 */
class SecurityHeaders {
    
    /**
     * 发送所有安全头
     */
    public static function sendAll() {
        // 防止XSS攻击
        header('X-XSS-Protection: 1; mode=block');
        
        // 防止MIME类型嗅探
        header('X-Content-Type-Options: nosniff');
        
        // 防止点击劫持
        header('X-Frame-Options: DENY');
        
        // 强制HTTPS传输
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        }
        
        // 内容安全策略
        self::sendCSP();
        
        // 权限策略
        self::sendPermissionsPolicy();
        
        // 引用策略
        header('Referrer-Policy: strict-origin-when-cross-origin');
    }
    
    /**
     * 内容安全策略
     */
    private static function sendCSP() {
        $csp = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdn.tailwindcss.com",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
            "img-src 'self' data: https:",
            "font-src 'self' https://fonts.gstatic.com",
            "connect-src 'self' https://api.weixin.qq.com",
            "frame-src 'none'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "upgrade-insecure-requests"
        ];
        
        header('Content-Security-Policy: ' . implode('; ', $csp));
    }
    
    /**
     * 权限策略
     */
    private static function sendPermissionsPolicy() {
        $permissions = [
            'geolocation=()',
            'microphone=()',
            'camera=()',
            'payment=()',
            'usb=()',
            'magnetometer=()',
            'gyroscope=()',
            'accelerometer=()'
        ];
        
        header('Permissions-Policy: ' . implode(', ', $permissions));
    }
    
    /**
     * 登录时的额外安全头
     */
    public static function forAuth() {
        self::sendAll();
        
        // 防止密码自动填充
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
    }
    
    /**
     * API响应的安全头
     */
    public static function forAPI() {
        header('X-XSS-Protection: 1; mode=block');
        header('X-Content-Type-Options: nosniff');
        header('Content-Type: application/json; charset=utf-8');
        
        // CORS头（如果需要）
        if (isset($_SERVER['HTTP_ORIGIN'])) {
            $allowedOrigins = [
                'https://yourdomain.com',
                'https://www.yourdomain.com'
            ];
            
            if (in_array($_SERVER['HTTP_ORIGIN'], $allowedOrigins)) {
                header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
                header('Access-Control-Allow-Credentials: true');
                header('Access-Control-Max-Age: 86400');
            }
        }
        
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
                header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
            }
            
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
                header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
            }
            
            exit(0);
        }
    }
}