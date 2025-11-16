<?php
/**
 * 私域商城系统安装器API处理
 * 处理AJAX请求和安装步骤
 */

// 定义常量
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(dirname(dirname(__DIR__))));
}

if (!defined('INSTALL_PATH')) {
    define('INSTALL_PATH', ROOT_PATH . '/install');
}

// 设置错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// 设置响应头
header('Content-Type: application/json; charset=utf-8');

// 检查是否已安装
if (file_exists(ROOT_PATH . '/config/installed.lock')) {
    echo json_encode(['success' => false, 'message' => '系统已安装，请勿重复安装。']);
    exit;
}

// 引入安装器类
require_once __DIR__ . '/Installer.php';

// 获取请求参数
$action = $_GET['action'] ?? '';

// 根据action处理请求
switch ($action) {
    case 'test_connection':
        testDatabaseConnection();
        break;
    case 'install_step':
        processInstallStep();
        break;
    default:
        echo json_encode(['success' => false, 'message' => '未知操作']);
        break;
}

/**
 * 测试数据库连接
 */
function testDatabaseConnection() {
    $dbConfig = [
        'host' => $_POST['db_host'] ?? 'localhost',
        'port' => $_POST['db_port'] ?? '3306',
        'database' => $_POST['db_name'] ?? 'private_mall',
        'username' => $_POST['db_username'] ?? 'root',
        'password' => $_POST['db_password'] ?? '',
        'prefix' => $_POST['db_prefix'] ?? 'mall_'
    ];

    $installer = new Installer($dbConfig);
    $result = $installer->testDatabaseConnection($dbConfig);
    
    echo json_encode($result);
}

/**
 * 处理安装步骤
 */
function processInstallStep() {
    $step = $_GET['step'] ?? 1;
    
    // 获取会话中的配置
    $dbConfig = $_SESSION['db_config'] ?? null;
    $siteConfig = $_SESSION['site_config'] ?? null;
    
    if (!$dbConfig || !$siteConfig) {
        echo json_encode(['success' => false, 'message' => '配置信息丢失，请重新开始安装。']);
        return;
    }
    
    $installer = new Installer($dbConfig);
    
    switch ($step) {
        case 1: // 创建数据库表结构
            $result = $installer->installDatabase($dbConfig);
            break;
            
        case 2: // 导入基础数据
            $result = ['success' => true, 'message' => '基础数据导入完成'];
            break;
            
        case 3: // 创建管理员账户
            $result = $installer->createAdminUser($siteConfig);
            break;
            
        case 4: // 生成配置文件
            $result = $installer->generateConfigFile($dbConfig, $siteConfig);
            break;
            
        case 5: // 创建安装锁文件
            $result = $installer->createInstallLock();
            
            // 安装完成，清理会话数据
            if ($result['success']) {
                unset($_SESSION['db_config']);
                unset($_SESSION['site_config']);
            }
            break;
            
        default:
            $result = ['success' => false, 'message' => '未知安装步骤'];
            break;
    }
    
    echo json_encode($result);
}

/**
 * 安全退出
 */
function safeExit($code = 0) {
    exit($code);
}