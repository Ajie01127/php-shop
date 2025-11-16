<?php
/**
 * 安全相关函数
 */

/**
 * 强制HTTPS重定向
 * 
 * @return void
 */
function force_https_redirect()
{
    // 检查是否启用强制HTTPS
    $forceHttps = site_config('force_https', '0');
    
    if ($forceHttps != '1') {
        return;
    }
    
    // 检查是否已经是HTTPS
    $isHttps = (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
        (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
        (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on') ||
        (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
    );
    
    if (!$isHttps) {
        // 构建HTTPS URL
        $httpsUrl = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        
        // 301永久重定向
        header('HTTP/1.1 301 Moved Permanently');
        header('Location: ' . $httpsUrl);
        exit;
    }
}

/**
 * 检查是否为HTTPS连接
 * 
 * @return bool
 */
function is_https()
{
    return (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
        (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
        (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on') ||
        (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
    );
}

/**
 * 设置安全响应头
 * 
 * @return void
 */
function set_security_headers()
{
    // 防止点击劫持
    header('X-Frame-Options: SAMEORIGIN');
    
    // 防止MIME类型嗅探
    header('X-Content-Type-Options: nosniff');
    
    // XSS保护
    header('X-XSS-Protection: 1; mode=block');
    
    // 推荐浏览器使用HTTPS
    if (is_https()) {
        // HSTS: 强制浏览器在未来一年内都使用HTTPS访问
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
    }
    
    // CSP内容安全策略（可根据需要调整）
    $csp = site_config('content_security_policy', '');
    if ($csp) {
        header('Content-Security-Policy: ' . $csp);
    }
    
    // 推荐权限策略
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
}

/**
 * 检查请求来源（防CSRF）
 * 
 * @return bool
 */
function verify_referer()
{
    // 对于POST、PUT、DELETE请求检查Referer
    $method = $_SERVER['REQUEST_METHOD'];
    if (!in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH'])) {
        return true;
    }
    
    // 如果没有Referer，可能是直接访问或来自非浏览器
    if (empty($_SERVER['HTTP_REFERER'])) {
        // 可以选择拒绝或允许，这里选择允许（API调用可能没有Referer）
        return true;
    }
    
    // 检查Referer是否来自本站
    $refererHost = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
    $currentHost = $_SERVER['HTTP_HOST'];
    
    return $refererHost === $currentHost;
}

/**
 * 生成CSRF Token
 * 
 * @return string
 */
function generate_csrf_token()
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * 验证CSRF Token
 * 
 * @param string $token
 * @return bool
 */
function verify_csrf_token($token)
{
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * 获取CSRF Token的HTML隐藏字段
 * 
 * @return string
 */
function csrf_field()
{
    $token = generate_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

/**
 * 获取客户端IP地址
 * 
 * @return string
 */
function get_client_ip()
{
    $ip = '';
    
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // 可能包含多个IP，取第一个
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip = trim($ips[0]);
    } elseif (!empty($_SERVER['HTTP_X_REAL_IP'])) {
        $ip = $_SERVER['HTTP_X_REAL_IP'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    }
    
    // 验证IP格式
    if (filter_var($ip, FILTER_VALIDATE_IP)) {
        return $ip;
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * 简单的请求频率限制
 * 
 * @param string $key 限制的键（如用户ID、IP等）
 * @param int $limit 允许的最大请求次数
 * @param int $seconds 时间窗口（秒）
 * @return bool 是否允许请求
 */
function rate_limit($key, $limit = 60, $seconds = 60)
{
    $cacheKey = 'rate_limit:' . $key;
    $requests = $_SESSION[$cacheKey] ?? [];
    
    // 清理过期记录
    $now = time();
    $requests = array_filter($requests, function($timestamp) use ($now, $seconds) {
        return ($now - $timestamp) < $seconds;
    });
    
    // 检查是否超限
    if (count($requests) >= $limit) {
        return false;
    }
    
    // 记录本次请求
    $requests[] = $now;
    $_SESSION[$cacheKey] = $requests;
    
    return true;
}

/**
 * SQL注入防护提示（使用PDO预处理）
 * 
 * @param string $string
 * @return string
 */
function escape_sql($string)
{
    // 注意：这个函数仅用于特殊情况，推荐使用PDO预处理
    return addslashes($string);
}

/**
 * 文件上传安全检查
 * 
 * @param array $file $_FILES中的文件信息
 * @param array $allowedTypes 允许的MIME类型
 * @param int $maxSize 最大文件大小（字节）
 * @return array ['valid' => bool, 'error' => string]
 */
function validate_upload($file, $allowedTypes = [], $maxSize = 5242880)
{
    // 检查是否有上传错误
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => '文件超过系统限制',
            UPLOAD_ERR_FORM_SIZE => '文件超过表单限制',
            UPLOAD_ERR_PARTIAL => '文件上传不完整',
            UPLOAD_ERR_NO_FILE => '没有文件上传',
            UPLOAD_ERR_NO_TMP_DIR => '临时目录不存在',
            UPLOAD_ERR_CANT_WRITE => '文件写入失败',
            UPLOAD_ERR_EXTENSION => 'PHP扩展阻止了上传',
        ];
        
        return [
            'valid' => false,
            'error' => $errors[$file['error']] ?? '上传失败'
        ];
    }
    
    // 检查文件大小
    if ($file['size'] > $maxSize) {
        return [
            'valid' => false,
            'error' => '文件大小超过限制（' . format_bytes($maxSize) . '）'
        ];
    }
    
    // 检查文件类型
    if (!empty($allowedTypes)) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $allowedTypes)) {
            return [
                'valid' => false,
                'error' => '不允许的文件类型'
            ];
        }
    }
    
    // 检查文件扩展名
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $dangerousExt = ['php', 'phtml', 'php3', 'php4', 'php5', 'php7', 'phps', 'pht', 'exe', 'sh', 'bat', 'cmd'];
    
    if (in_array($ext, $dangerousExt)) {
        return [
            'valid' => false,
            'error' => '不允许上传此类型的文件'
        ];
    }
    
    return ['valid' => true, 'error' => ''];
}

/**
 * 格式化字节大小
 * 
 * @param int $bytes
 * @return string
 */
function format_bytes($bytes)
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    
    return round($bytes, 2) . ' ' . $units[$pow];
}
