<?php
/**
 * 辅助函数库
 */

/**
 * 安全输出函数 - 防止XSS攻击
 */
function e($string, $encoding = 'UTF-8') {
    return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, $encoding);
}

/**
 * 获取输入值并进行安全处理
 */
function input($key, $default = '', $filter = FILTER_SANITIZE_STRING) {
    $value = $_POST[$key] ?? $_GET[$key] ?? $default;
    if (is_string($value)) {
        $value = trim($value);
        if ($filter !== null) {
            $value = filter_var($value, $filter);
        }
    }
    return $value;
}

/**
 * 获取配置值
 */
function config($key, $default = null) {
    static $config = null;
    
    if ($config === null) {
        $config = require __DIR__ . '/../config/config.php';
    }
    
    $keys = explode('.', $key);
    $value = $config;
    
    foreach ($keys as $k) {
        if (!isset($value[$k])) {
            return $default;
        }
        $value = $value[$k];
    }
    
    return $value;
}

/**
 * 获取网站配置
 */
function site_config($key, $default = null) {
    static $cache = null;
    
    if ($cache === null) {
        require_once __DIR__ . '/../app/Models/SiteSetting.php';
        $cache = \App\Models\SiteSetting::cache();
    }
    
    return $cache[$key] ?? $default;
}

/**
 * 获取POST参数
 */
function post($key, $default = null) {
    return $_POST[$key] ?? $default;
}

/**
 * 获取GET参数
 */
function get($key, $default = null) {
    return $_GET[$key] ?? $default;
}

/**
 * URL生成
 */
function url($path) {
    return rtrim(config('app.url', ''), '/') . '/' . ltrim($path, '/');
}

/**
 * 资源URL
 */
function asset($path) {
    return rtrim(config('app.url', ''), '/') . '/public/' . ltrim($path, '/');
}

/**
 * 重定向
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * JSON响应
 */
function json($data, $code = 200) {
    header('Content-Type: application/json');
    echo json_encode([
        'code' => $code,
        'data' => $data,
    ]);
    exit;
}

/**
 * 格式化价格
 */
function format_price($price) {
    return '¥' . number_format($price, 2);
}

/**
 * 格式化时间
 */
function format_time($time) {
    if (is_numeric($time)) {
        $time = date('Y-m-d H:i:s', $time);
    }
    return $time;
}

/**
 * 截取字符串
 */
function str_limit($str, $length = 50, $suffix = '...') {
    if (mb_strlen($str) > $length) {
        return mb_substr($str, 0, $length) . $suffix;
    }
    return $str;
}

/**
 * XSS过滤
 */
function clean($data) {
    if (is_array($data)) {
        return array_map('clean', $data);
    }
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * 生成随机字符串
 */
function random_string($length = 16) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $str = '';
    for ($i = 0; $i < $length; $i++) {
        $str .= $chars[mt_rand(0, strlen($chars) - 1)];
    }
    return $str;
}

/**
 * 密码加密
 */
function password_encrypt($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * 密码验证
 */
function password_check($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * 生成订单号
 */
function generate_order_no() {
    return date('YmdHis') . mt_rand(1000, 9999);
}

/**
 * 获取客户端IP
 */
function get_client_ip() {
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * 日志记录
 */
function log_message($message, $level = 'info', $file = 'app.log') {
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logFile = $logDir . '/' . date('Y-m-d') . '_' . $file;
    $time = date('Y-m-d H:i:s');
    $logContent = "[{$time}] [{$level}] {$message}\n";
    
    file_put_contents($logFile, $logContent, FILE_APPEND);
}

/**
 * 文件大小格式化
 */
function format_bytes($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    
    return round($bytes, 2) . ' ' . $units[$pow];
}

/**
 * 检查是否为移动端
 */
function is_mobile() {
    return isset($_SERVER['HTTP_USER_AGENT']) && 
           preg_match('/(android|iphone|ipad|mobile)/i', $_SERVER['HTTP_USER_AGENT']);
}

/**
 * 数组转查询字符串
 */
function http_build_query_custom($data) {
    return http_build_query($data);
}

/**
 * 获取分页HTML
 */
function pagination($total, $page, $pageSize, $url) {
    $pages = ceil($total / $pageSize);
    if ($pages <= 1) {
        return '';
    }
    
    $html = '<nav><ul class="pagination">';
    
    // 上一页
    if ($page > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $url . '?page=' . ($page - 1) . '">上一页</a></li>';
    }
    
    // 页码
    for ($i = 1; $i <= $pages; $i++) {
        $active = $i == $page ? 'active' : '';
        $html .= '<li class="page-item ' . $active . '"><a class="page-link" href="' . $url . '?page=' . $i . '">' . $i . '</a></li>';
    }
    
    // 下一页
    if ($page < $pages) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $url . '?page=' . ($page + 1) . '">下一页</a></li>';
    }
    
    $html .= '</ul></nav>';
    
    return $html;
}
