<?php
/**
 * 系统引导文件
 */

// 错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 时区设置
date_default_timezone_set('Asia/Shanghai');

// 加载配置
$config = require __DIR__ . '/../config/config.php';

// 定义常量
define('APP_PATH', dirname(__DIR__));
define('PUBLIC_PATH', APP_PATH . '/public');
define('UPLOAD_PATH', PUBLIC_PATH . '/uploads');

// 自动加载
spl_autoload_register(function ($class) {
    $file = APP_PATH . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// 辅助函数
require_once __DIR__ . '/helpers.php';
