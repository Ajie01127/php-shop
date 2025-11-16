<?php
/**
 * 私域商城系统 - 在线安装程序
 * 上传源码后访问此文件进行安装配置
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// 定义常量
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', __DIR__);
}

if (!defined('INSTALL_PATH')) {
    define('INSTALL_PATH', ROOT_PATH . '/install');
}

// 检查是否已安装
if (file_exists(ROOT_PATH . '/config/installed.lock')) {
    ?><!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>系统已安装 - 私域商城系统</title>
    <link rel="stylesheet" href="install/assets/css/style.css">
</head>
<body>
    <div class="install-container">
        <div class="install-header">
            <h1>私域商城系统</h1>
            <p>系统已安装</p>
        </div>
        
        <div class="install-content">
            <div class="error-message">
                <h2>⚠️ 系统已安装完成</h2>
                <p>检测到系统已完成安装，安装向导已自动关闭。</p>
                
                <div class="security-notice">
                    <h3>安全提醒</h3>
                    <p>为了系统安全，请立即删除 install.php 文件：</p>
                    <code>rm install.php</code>
                    <p>或通过FTP工具删除此文件。</p>
                </div>
                
                <div class="install-actions">
                    <a href="/" class="btn btn-primary">访问网站</a>
                    <a href="/admin" class="btn btn-secondary">管理后台</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html><?php
    exit;
}

// 检查系统要求
$requirements = [
    'php_version' => version_compare(PHP_VERSION, '8.0.0', '>='),
    'pdo_mysql' => extension_loaded('pdo_mysql'),
    'json' => extension_loaded('json'),
    'mbstring' => extension_loaded('mbstring'),
    'gd' => extension_loaded('gd'),
    'curl' => extension_loaded('curl'),
];

$allRequirementsMet = !in_array(false, $requirements, true);

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $step = $_POST['step'] ?? 'welcome';
    
    switch ($step) {
        case 'database':
            handleDatabaseSetup();
            break;
        case 'site_config':
            handleSiteConfig();
            break;
        case 'install':
            handleInstallation();
            break;
    }
}

// 获取当前步骤
$currentStep = $_GET['step'] ?? 'welcome';

// 显示安装界面
switch ($currentStep) {
    case 'requirements':
        showRequirementsPage($requirements, $allRequirementsMet);
        break;
    case 'database':
        showDatabasePage();
        break;
    case 'site_config':
        showSiteConfigPage();
        break;
    case 'install':
        showInstallPage();
        break;
    case 'complete':
        showCompletePage();
        break;
    default:
        showWelcomePage();
        break;
}

// 页面函数
function showWelcomePage() {
    ?><!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>私域商城系统 - 安装向导</title>
    <link rel="stylesheet" href="install/assets/css/style.css">
</head>
<body>
    <div class="install-container">
        <div class="install-header">
            <h1>私域商城系统</h1>
            <p>欢迎使用安装向导</p>
        </div>
        
        <div class="install-content">
            <div class="step-indicator">
                <div class="step active">1. 欢迎</div>
                <div class="step">2. 系统要求</div>
                <div class="step">3. 数据库配置</div>
                <div class="step">4. 网站设置</div>
                <div class="step">5. 安装完成</div>
            </div>
            
            <div class="step-content">
                <h2>欢迎安装私域商城系统</h2>
                <p>感谢您选择私域商城系统。此向导将引导您完成系统的安装过程。</p>
                
                <div class="system-info">
                    <h3>系统信息</h3>
                    <ul>
                        <li><strong>PHP版本：</strong><?php echo PHP_VERSION; ?></li>
                        <li><strong>服务器：</strong><?php echo $_SERVER['SERVER_SOFTWARE'] ?? '未知'; ?></li>
                        <li><strong>系统环境：</strong><?php echo PHP_OS; ?></li>
                    </ul>
                </div>
                
                <div class="install-actions">
                    <a href="?step=requirements" class="btn btn-primary">开始安装</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html><?php
}

function showRequirementsPage($requirements, $allRequirementsMet) {
    ?><!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>系统要求检查 - 私域商城系统</title>
    <link rel="stylesheet" href="install/assets/css/style.css">
</head>
<body>
    <div class="install-container">
        <div class="install-header">
            <h1>私域商城系统</h1>
            <p>系统要求检查</p>
        </div>
        
        <div class="install-content">
            <div class="step-indicator">
                <div class="step completed">1. 欢迎</div>
                <div class="step active">2. 系统要求</div>
                <div class="step">3. 数据库配置</div>
                <div class="step">4. 网站设置</div>
                <div class="step">5. 安装完成</div>
            </div>
            
            <div class="step-content">
                <h2>系统要求检查</h2>
                
                <div class="requirements-list">
                    <div class="requirement <?php echo $requirements['php_version'] ? 'met' : 'not-met'; ?>">
                        <span class="status"><?php echo $requirements['php_version'] ? '✓' : '✗'; ?></span>
                        <span class="text">PHP 版本 ≥ 8.0.0</span>
                        <span class="current">当前: <?php echo PHP_VERSION; ?></span>
                    </div>
                    
                    <div class="requirement <?php echo $requirements['pdo_mysql'] ? 'met' : 'not-met'; ?>">
                        <span class="status"><?php echo $requirements['pdo_mysql'] ? '✓' : '✗'; ?></span>
                        <span class="text">PDO MySQL 扩展</span>
                    </div>
                    
                    <div class="requirement <?php echo $requirements['json'] ? 'met' : 'not-met'; ?>">
                        <span class="status"><?php echo $requirements['json'] ? '✓' : '✗'; ?></span>
                        <span class="text">JSON 扩展</span>
                    </div>
                    
                    <div class="requirement <?php echo $requirements['mbstring'] ? 'met' : 'not-met'; ?>">
                        <span class="status"><?php echo $requirements['mbstring'] ? '✓' : '✗'; ?></span>
                        <span class="text">MBString 扩展</span>
                    </div>
                    
                    <div class="requirement <?php echo $requirements['gd'] ? 'met' : 'not-met'; ?>">
                        <span class="status"><?php echo $requirements['gd'] ? '✓' : '✗'; ?></span>
                        <span class="text">GD 图像处理扩展</span>
                    </div>
                    
                    <div class="requirement <?php echo $requirements['curl'] ? 'met' : 'not-met'; ?>">
                        <span class="status"><?php echo $requirements['curl'] ? '✓' : '✗'; ?></span>
                        <span class="text">cURL 扩展</span>
                    </div>
                </div>
                
                <?php if (!$allRequirementsMet): ?>
                    <div class="alert alert-error">
                        <strong>警告：</strong>系统要求不满足，请先解决上述问题再继续安装。
                    </div>
                <?php endif; ?>
                
                <div class="install-actions">
                    <a href="?step=welcome" class="btn btn-secondary">上一步</a>
                    <?php if ($allRequirementsMet): ?>
                        <a href="?step=database" class="btn btn-primary">下一步</a>
                    <?php else: ?>
                        <button class="btn btn-primary" disabled>下一步</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html><?php
}

function showDatabasePage() {
    ?><!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>数据库配置 - 私域商城系统</title>
    <link rel="stylesheet" href="install/assets/css/style.css">
</head>
<body>
    <div class="install-container">
        <div class="install-header">
            <h1>私域商城系统</h1>
            <p>数据库配置</p>
        </div>
        
        <div class="install-content">
            <div class="step-indicator">
                <div class="step completed">1. 欢迎</div>
                <div class="step completed">2. 系统要求</div>
                <div class="step active">3. 数据库配置</div>
                <div class="step">4. 网站设置</div>
                <div class="step">5. 安装完成</div>
            </div>
            
            <div class="step-content">
                <h2>数据库配置</h2>
                
                <form method="POST" class="install-form">
                    <input type="hidden" name="step" value="database">
                    
                    <div class="form-group">
                        <label for="db_host">数据库主机</label>
                        <input type="text" id="db_host" name="db_host" value="localhost" required>
                        <small>通常是 localhost 或 127.0.0.1</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="db_port">数据库端口</label>
                        <input type="number" id="db_port" name="db_port" value="3306" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="db_name">数据库名称</label>
                        <input type="text" id="db_name" name="db_name" value="private_mall" required>
                        <small>请确保数据库已存在</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="db_username">数据库用户名</label>
                        <input type="text" id="db_username" name="db_username" value="root" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="db_password">数据库密码</label>
                        <input type="password" id="db_password" name="db_password">
                    </div>
                    
                    <div class="form-group">
                        <label for="db_prefix">数据表前缀</label>
                        <input type="text" id="db_prefix" name="db_prefix" value="mall_">
                        <small>多个站点使用同一数据库时建议设置不同前缀</small>
                    </div>
                    
                    <div class="install-actions">
                        <a href="?step=requirements" class="btn btn-secondary">上一步</a>
                        <button type="button" id="test-connection" class="btn btn-info">测试连接</button>
                        <button type="submit" class="btn btn-primary">下一步</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
    document.getElementById('test-connection').addEventListener('click', function() {
        const formData = new FormData(document.querySelector('form'));
        const testBtn = this;
        const originalText = testBtn.textContent;
        
        testBtn.disabled = true;
        testBtn.textContent = '测试中...';
        
        fetch('install/core/InstallerAPI.php?action=test_connection', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', '✓ ' + data.message);
            } else {
                showAlert('error', '✗ ' + data.message);
            }
        })
        .catch(error => {
            showAlert('error', '测试失败: ' + error.message);
        })
        .finally(() => {
            testBtn.disabled = false;
            testBtn.textContent = originalText;
        });
    });
    
    function showAlert(type, message) {
        // 移除现有的提示
        const existingAlert = document.querySelector('.connection-alert');
        if (existingAlert) {
            existingAlert.remove();
        }
        
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} connection-alert`;
        alertDiv.innerHTML = message;
        
        const form = document.querySelector('.install-form');
        form.insertBefore(alertDiv, form.firstChild);
        
        // 5秒后自动移除
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }
    </script>
</body>
</html><?php
}

function showSiteConfigPage() {
    ?><!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>网站设置 - 私域商城系统</title>
    <link rel="stylesheet" href="install/assets/css/style.css">
</head>
<body>
    <div class="install-container">
        <div class="install-header">
            <h1>私域商城系统</h1>
            <p>网站设置</p>
        </div>
        
        <div class="install-content">
            <div class="step-indicator">
                <div class="step completed">1. 欢迎</div>
                <div class="step completed">2. 系统要求</div>
                <div class="step completed">3. 数据库配置</div>
                <div class="step active">4. 网站设置</div>
                <div class="step">5. 安装完成</div>
            </div>
            
            <div class="step-content">
                <h2>网站基本信息设置</h2>
                
                <form method="POST" class="install-form">
                    <input type="hidden" name="step" value="site_config">
                    
                    <div class="form-group">
                        <label for="site_name">网站名称</label>
                        <input type="text" id="site_name" name="site_name" value="私域商城系统" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="site_url">网站地址</label>
                        <input type="url" id="site_url" name="site_url" value="<?php echo getCurrentUrl(); ?>" required>
                        <small>请确保填写正确的域名，安装后无法修改</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="admin_username">管理员用户名</label>
                        <input type="text" id="admin_username" name="admin_username" value="admin" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="admin_password">管理员密码</label>
                        <input type="password" id="admin_password" name="admin_password" required>
                        <small>建议使用强密码</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="admin_email">管理员邮箱</label>
                        <input type="email" id="admin_email" name="admin_email" required>
                    </div>
                    
                    <div class="install-actions">
                        <a href="?step=database" class="btn btn-secondary">上一步</a>
                        <button type="submit" class="btn btn-primary">下一步</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html><?php
}

function showInstallPage() {
    ?><!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>正在安装 - 私域商城系统</title>
    <link rel="stylesheet" href="install/assets/css/style.css">
</head>
<body>
    <div class="install-container">
        <div class="install-header">
            <h1>私域商城系统</h1>
            <p>正在安装</p>
        </div>
        
        <div class="install-content">
            <div class="step-indicator">
                <div class="step completed">1. 欢迎</div>
                <div class="step completed">2. 系统要求</div>
                <div class="step completed">3. 数据库配置</div>
                <div class="step completed">4. 网站设置</div>
                <div class="step active">5. 安装完成</div>
            </div>
            
            <div class="step-content">
                <h2>正在安装系统</h2>
                
                <div id="install-progress">
                    <div class="progress-bar">
                        <div class="progress" id="progress-fill" style="width: 0%"></div>
                    </div>
                    <div id="install-messages">
                        <div class="install-message">正在准备安装环境...</div>
                    </div>
                </div>
                
                <form method="POST" id="install-form" style="display: none;">
                    <input type="hidden" name="step" value="install">
                </form>
                
                <div class="install-actions" id="install-actions" style="display: none;">
                    <a href="?step=complete" class="btn btn-primary">完成安装</a>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    // 启动安装过程
    setTimeout(() => {
        startInstallation();
    }, 1000);
    
    async function startInstallation() {
        const messages = [
            '正在创建数据库表结构...',
            '正在导入基础数据...',
            '正在创建管理员账户...',
            '正在生成配置文件...',
            '正在创建安装锁文件...'
        ];
        
        for (let i = 0; i < messages.length; i++) {
            document.getElementById('progress-fill').style.width = ((i + 1) / messages.length * 100) + '%';
            document.getElementById('install-messages').innerHTML = '<div class="install-message">' + messages[i] + '</div>';
            
            // 执行实际的安装步骤
            try {
                const response = await fetch('install/core/Installer.php?action=install_step&step=' + (i + 1), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });
                
                const result = await response.json();
                
                if (!result.success) {
                    document.getElementById('install-messages').innerHTML = 
                        '<div class="install-message error">安装失败: ' + result.message + '</div>';
                    return;
                }
                
                // 等待1秒再执行下一步
                await new Promise(resolve => setTimeout(resolve, 1000));
                
            } catch (error) {
                document.getElementById('install-messages').innerHTML = 
                    '<div class="install-message error">安装错误: ' + error.message + '</div>';
                return;
            }
        }
        
        // 安装完成
        document.getElementById('install-actions').style.display = 'block';
    }
    </script>
</body>
</html><?php
}

function showCompletePage() {
    ?><!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>安装完成 - 私域商城系统</title>
    <link rel="stylesheet" href="install/assets/css/style.css">
</head>
<body>
    <div class="install-container">
        <div class="install-header">
            <h1>私域商城系统</h1>
            <p>安装完成</p>
        </div>
        
        <div class="install-content">
            <div class="step-indicator">
                <div class="step completed">1. 欢迎</div>
                <div class="step completed">2. 系统要求</div>
                <div class="step completed">3. 数据库配置</div>
                <div class="step completed">4. 网站设置</div>
                <div class="step completed">5. 安装完成</div>
            </div>
            
            <div class="step-content">
                <div class="success-message">
                    <h2>🎉 安装成功！</h2>
                    <p>私域商城系统已成功安装并配置完成。</p>
                </div>
                
                <div class="next-steps">
                    <h3>下一步操作</h3>
                    <ul>
                        <li><a href="/admin" target="_blank">访问后台管理</a> - 用户名：admin，密码：您设置的密码</li>
                        <li><a href="/" target="_blank">查看前台网站</a></li>
                        <li><a href="/admin/settings" target="_blank">配置网站设置</a></li>
                    </ul>
                </div>
                
                <div class="security-notice">
                    <h3>安全提醒</h3>
                    <p>为了系统安全，请务删除 install.php 文件：</p>
                    <code>rm install.php</code>
                    <p>或通过FTP工具删除此文件。</p>
                </div>
                
                <div class="install-actions">
                    <a href="/" class="btn btn-primary" target="_blank">访问网站</a>
                    <a href="/admin" class="btn btn-secondary" target="_blank">管理后台</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html><?php
}

function handleDatabaseSetup() {
    // 处理数据库配置
    $_SESSION['db_config'] = [
        'host' => $_POST['db_host'],
        'port' => $_POST['db_port'],
        'database' => $_POST['db_name'],
        'username' => $_POST['db_username'],
        'password' => $_POST['db_password'],
        'prefix' => $_POST['db_prefix']
    ];
    
    header('Location: ?step=site_config');
    exit;
}

function handleSiteConfig() {
    // 处理网站配置
    $_SESSION['site_config'] = [
        'site_name' => $_POST['site_name'],
        'site_url' => $_POST['site_url'],
        'admin_username' => $_POST['admin_username'],
        'admin_password' => password_hash($_POST['admin_password'], PASSWORD_DEFAULT),
        'admin_email' => $_POST['admin_email']
    ];
    
    header('Location: ?step=install');
    exit;
}

function handleInstallation() {
    // 引入安装器类
    require_once INSTALL_PATH . '/core/Installer.php';
    
    // 获取会话中的配置
    $dbConfig = $_SESSION['db_config'] ?? null;
    $siteConfig = $_SESSION['site_config'] ?? null;
    
    if (!$dbConfig || !$siteConfig) {
        $_SESSION['error'] = '配置信息丢失，请重新开始安装。';
        header('Location: ?step=database');
        exit;
    }
    
    // 创建安装器实例
    $installer = new Installer($dbConfig);
    
    // 测试数据库连接
    $testResult = $installer->testDatabaseConnection($dbConfig);
    if (!$testResult['success']) {
        $_SESSION['error'] = $testResult['message'];
        header('Location: ?step=database');
        exit;
    }
    
    // 安装数据库
    $dbResult = $installer->installDatabase($dbConfig);
    if (!$dbResult['success']) {
        $_SESSION['error'] = $dbResult['message'];
        header('Location: ?step=database');
        exit;
    }
    
    // 创建管理员账户
    $adminResult = $installer->createAdminUser($siteConfig);
    if (!$adminResult['success']) {
        $_SESSION['error'] = $adminResult['message'];
        header('Location: ?step=site_config');
        exit;
    }
    
    // 生成配置文件
    $configResult = $installer->generateConfigFile($dbConfig, $siteConfig);
    if (!$configResult['success']) {
        $_SESSION['error'] = $configResult['message'];
        header('Location: ?step=complete');
        exit;
    }
    
    // 创建安装锁文件
    $lockResult = $installer->createInstallLock();
    if (!$lockResult['success']) {
        $_SESSION['error'] = $lockResult['message'];
        header('Location: ?step=complete');
        exit;
    }
    
    // 清理会话数据
    unset($_SESSION['db_config']);
    unset($_SESSION['site_config']);
    
    header('Location: ?step=complete');
    exit;
}

function getCurrentUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $path = dirname($_SERVER['SCRIPT_NAME']);
    
    return $protocol . '://' . $host . $path;
}