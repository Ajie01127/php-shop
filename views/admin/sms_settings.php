<?php
// 短信设置页面
include_once '../header.php';

// 检查权限
if (!isset($_SESSION['admin_id'])) {
    header('Location: /admin/login');
    exit;
}

// 设置数据从控制器传递
$currentSettings = $settings ?? [];

// 处理表单提交逻辑
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 表单提交将通过路由处理，这里主要用于显示状态
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>短信设置 - 私域商城后台</title>
    <link rel="stylesheet" href="/public/admin.css">
    <style>
        .form-section {
            background: #fff;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .form-group input[type="checkbox"] {
            width: auto;
            margin-right: 10px;
        }
        .checkbox-label {
            display: flex;
            align-items: center;
        }
        .help-text {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        .test-btn {
            background: #1890ff;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
        }
        .test-btn:hover {
            background: #40a9ff;
        }
        .form-actions {
            text-align: center;
            margin-top: 20px;
        }
        .submit-btn {
            background: #52c41a;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .submit-btn:hover {
            background: #73d13d;
        }
        .alert {
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .alert-success {
            background: #f6ffed;
            border: 1px solid #b7eb8f;
            color: #52c41a;
        }
        .alert-error {
            background: #fff2f0;
            border: 1px solid #ffccc7;
            color: #ff4d4f;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include_once '../admin_sidebar.php'; ?>
        
        <div class="admin-content">
            <div class="admin-header">
                <h1>短信设置</h1>
                <nav class="breadcrumb">
                    <a href="/admin">首页</a> &gt; 
                    <a href="/admin/settings.php">系统设置</a> &gt; 
                    <span>短信设置</span>
                </nav>
            </div>

            <div class="admin-body">
                <?php if (isset($successMessage)): ?>
                    <div class="alert alert-success">
                        <?php echo htmlspecialchars($successMessage); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($errorMessage)): ?>
                    <div class="alert alert-error">
                        <?php echo htmlspecialchars($errorMessage); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" id="smsForm">
                    
                    <!-- 基本设置 -->
                    <div class="form-section">
                        <h2>基本设置</h2>
                        
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="sms_enable" value="1" 
                                    <?php echo ($currentSettings['sms_enable'] ?? '0') === '1' ? 'checked' : ''; ?>>
                                <span>启用短信服务</span>
                            </label>
                            <div class="help-text">启用后系统将使用腾讯云短信服务发送验证码和通知</div>
                        </div>

                        <div class="form-group">
                            <label>SecretId</label>
                            <input type="text" name="sms_secret_id" 
                                   value="<?php echo htmlspecialchars($currentSettings['sms_secret_id'] ?? ''); ?>"
                                   placeholder="请输入腾讯云SecretId">
                            <div class="help-text">腾讯云访问密钥中的SecretId</div>
                        </div>

                        <div class="form-group">
                            <label>SecretKey</label>
                            <input type="password" name="sms_secret_key" 
                                   value="<?php echo htmlspecialchars($currentSettings['sms_secret_key'] ?? ''); ?>"
                                   placeholder="请输入腾讯云SecretKey">
                            <div class="help-text">腾讯云访问密钥中的SecretKey</div>
                        </div>

                        <div class="form-group">
                            <label>SDK AppID</label>
                            <input type="text" name="sms_sdk_app_id" 
                                   value="<?php echo htmlspecialchars($currentSettings['sms_sdk_app_id'] ?? ''); ?>"
                                   placeholder="请输入短信应用SDK AppID">
                            <div class="help-text">短信控制台中的应用ID</div>
                        </div>

                        <div class="form-group">
                            <label>短信签名</label>
                            <input type="text" name="sms_sign_name" 
                                   value="<?php echo htmlspecialchars($currentSettings['sms_sign_name'] ?? ''); ?>"
                                   placeholder="请输入短信签名内容">
                            <div class="help-text">短信签名内容（需在腾讯云短信控制台审核通过）</div>
                        </div>

                        <div class="form-actions">
                            <button type="button" class="test-btn" onclick="testConnection()">测试连接</button>
                        </div>
                    </div>

                    <!-- 场景启用设置 -->
                    <div class="form-section">
                        <h2>场景启用设置</h2>
                        
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="sms_scene_login" value="1" 
                                    <?php echo ($currentSettings['sms_scene_login'] ?? '1') === '1' ? 'checked' : ''; ?>>
                                <span>启用登录验证码</span>
                            </label>
                            <div class="help-text">用户登录时是否需要短信验证码</div>
                        </div>

                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="sms_scene_register" value="1" 
                                    <?php echo ($currentSettings['sms_scene_register'] ?? '1') === '1' ? 'checked' : ''; ?>>
                                <span>启用注册验证码</span>
                            </label>
                            <div class="help-text">用户注册时是否需要短信验证码</div>
                        </div>

                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="sms_scene_reset" value="1" 
                                    <?php echo ($currentSettings['sms_scene_reset'] ?? '1') === '1' ? 'checked' : ''; ?>>
                                <span>启用密码重置</span>
                            </label>
                            <div class="help-text">用户重置密码时是否需要短信验证码</div>
                        </div>

                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="sms_scene_order" value="1" 
                                    <?php echo ($currentSettings['sms_scene_order'] ?? '1') === '1' ? 'checked' : ''; ?>>
                                <span>启用订单通知</span>
                            </label>
                            <div class="help-text">用户下单后是否发送订单确认短信</div>
                        </div>

                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="sms_scene_payment" value="1" 
                                    <?php echo ($currentSettings['sms_scene_payment'] ?? '1') === '1' ? 'checked' : ''; ?>>
                                <span>启用支付通知</span>
                            </label>
                            <div class="help-text">用户支付成功后是否发送支付成功短信</div>
                        </div>

                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="sms_scene_shipping" value="1" 
                                    <?php echo ($currentSettings['sms_scene_shipping'] ?? '1') === '1' ? 'checked' : ''; ?>>
                                <span>启用发货通知</span>
                            </label>
                            <div class="help-text">订单发货后是否发送发货通知短信</div>
                        </div>

                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="sms_scene_refund" value="1" 
                                    <?php echo ($currentSettings['sms_scene_refund'] ?? '1') === '1' ? 'checked' : ''; ?>>
                                <span>启用退款通知</span>
                            </label>
                            <div class="help-text">退款处理完成后是否发送退款通知短信</div>
                        </div>
                    </div>

                    <!-- 注册配置设置 -->
                    <div class="form-section">
                        <h2>注册配置设置</h2>
                        
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="sms_register_enable" value="1" 
                                    <?php echo ($currentSettings['sms_register_enable'] ?? '1') === '1' ? 'checked' : ''; ?>>
                                <span>全站开放短信注册</span>
                            </label>
                            <div class="help-text">是否允许用户通过短信验证码注册新账户</div>
                        </div>

                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="sms_auto_bind" value="1" 
                                    <?php echo ($currentSettings['sms_auto_bind'] ?? '1') === '1' ? 'checked' : ''; ?>>
                                <span>小程序用户手机号自动绑定</span>
                            </label>
                            <div class="help-text">小程序用户通过快捷授权登录后，手机号是否自动绑定到用户信息</div>
                        </div>
                    </div>

                    <!-- 模板设置 -->
                    <div class="form-section">
                        <h2>模板设置</h2>
                        
                        <div class="form-group">
                            <label>登录验证码模板ID</label>
                            <input type="text" name="sms_template_id_login" 
                                   value="<?php echo htmlspecialchars($currentSettings['sms_template_id_login'] ?? ''); ?>"
                                   placeholder="请输入登录验证码模板ID">
                            <div class="help-text">示例：{code}为验证码参数，模板内容："您的登录验证码是{code}，5分钟内有效"</div>
                        </div>

                        <div class="form-group">
                            <label>注册验证码模板ID</label>
                            <input type="text" name="sms_template_id_register" 
                                   value="<?php echo htmlspecialchars($currentSettings['sms_template_id_register'] ?? ''); ?>"
                                   placeholder="请输入注册验证码模板ID">
                            <div class="help-text">示例：{code}为验证码参数</div>
                        </div>

                        <div class="form-group">
                            <label>订单确认模板ID</label>
                            <input type="text" name="sms_template_id_order" 
                                   value="<?php echo htmlspecialchars($currentSettings['sms_template_id_order'] ?? ''); ?>"
                                   placeholder="请输入订单确认模板ID">
                            <div class="help-text">示例：{order_no}为订单号，{amount}为金额</div>
                        </div>

                        <div class="form-group">
                            <label>支付成功模板ID</label>
                            <input type="text" name="sms_template_id_payment" 
                                   value="<?php echo htmlspecialchars($currentSettings['sms_template_id_payment'] ?? ''); ?>"
                                   placeholder="请输入支付成功模板ID">
                        </div>

                        <div class="form-group">
                            <label>发货通知模板ID</label>
                            <input type="text" name="sms_template_id_shipping" 
                                   value="<?php echo htmlspecialchars($currentSettings['sms_template_id_shipping'] ?? ''); ?>"
                                   placeholder="请输入发货通知模板ID">
                            <div class="help-text">示例：{order_no}为订单号，{tracking_no}为物流单号</div>
                        </div>

                        <div class="form-group">
                            <label>退款通知模板ID</label>
                            <input type="text" name="sms_template_id_refund" 
                                   value="<?php echo htmlspecialchars($currentSettings['sms_template_id_refund'] ?? ''); ?>"
                                   placeholder="请输入退款通知模板ID">
                            <div class="help-text">示例：{order_no}为订单号，{amount}为退款金额</div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="submit-btn">保存设置</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function testConnection() {
            const form = document.getElementById('smsForm');
            const formData = new FormData(form);
            
            fetch('/api/sms/test', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.code === 200) {
                    alert('连接测试成功：' + data.message);
                } else {
                    alert('连接测试失败：' + data.message);
                }
            })
            .catch(error => {
                alert('测试请求失败：' + error.message);
            });
        }
    </script>
</body>
</html>