<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">邮箱配置</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <button type="button" class="btn btn-outline-primary" id="test-config-btn">
                <i class="fas fa-plug"></i> 测试连接
            </button>
            <button type="button" class="btn btn-outline-success ms-2" id="send-test-btn">
                <i class="fas fa-envelope"></i> 发送测试邮件
            </button>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo e($_SESSION['success']); unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo e($_SESSION['error']); unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">SMTP 配置</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="/admin/email/config" id="config-form">
                        <div class="mb-3">
                            <label for="driver" class="form-label">邮件驱动</label>
                            <select class="form-select" id="driver" name="driver">
                                <option value="smtp" <?php echo ($config['driver'] ?? '') === 'smtp' ? 'selected' : ''; ?>>SMTP</option>
                                <option value="mail" <?php echo ($config['driver'] ?? '') === 'mail' ? 'selected' : ''; ?>>PHP Mail</option>
                                <option value="sendmail" <?php echo ($config['driver'] ?? '') === 'sendmail' ? 'selected' : ''; ?>>Sendmail</option>
                            </select>
                            <div class="form-text">选择邮件发送方式，推荐使用 SMTP</div>
                        </div>

                        <div id="smtp-config" style="<?php echo ($config['driver'] ?? 'smtp') !== 'smtp' ? 'display: none;' : ''; ?>">
                            <div class="mb-3">
                                <label for="host" class="form-label">SMTP 服务器</label>
                                <input type="text" class="form-control" id="host" name="host" 
                                       value="<?php echo e($config['host'] ?? 'smtp.gmail.com'); ?>" required>
                                <div class="form-text">常用邮箱服务器：smtp.gmail.com (Gmail), smtp.qq.com (QQ邮箱)</div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="port" class="form-label">端口</label>
                                        <input type="number" class="form-control" id="port" name="port" 
                                               value="<?php echo e($config['port'] ?? 587); ?>" required>
                                        <div class="form-text">常用端口：587 (TLS), 465 (SSL)</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="encryption" class="form-label">加密方式</label>
                                        <select class="form-select" id="encryption" name="encryption">
                                            <option value="tls" <?php echo ($config['encryption'] ?? '') === 'tls' ? 'selected' : ''; ?>>TLS</option>
                                            <option value="ssl" <?php echo ($config['encryption'] ?? '') === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                                            <option value="" <?php echo ($config['encryption'] ?? '') === '' ? 'selected' : ''; ?>>无加密</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="username" class="form-label">邮箱用户名</label>
                                <input type="email" class="form-control" id="username" name="username" 
                                       value="<?php echo e($config['username'] ?? ''); ?>" required>
                                <div class="form-text">用于发送邮件的邮箱地址</div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">邮箱密码</label>
                                <input type="password" class="form-control" id="password" name="password" 
                                       value="<?php echo e($config['password'] ?? ''); ?>" required>
                                <div class="form-text">Gmail 需要使用应用专用密码，QQ邮箱需要开启SMTP服务</div>
                            </div>
                        </div>

                        <hr>

                        <div class="mb-3">
                            <label for="from_name" class="form-label">发件人名称</label>
                            <input type="text" class="form-control" id="from_name" name="from_name" 
                                   value="<?php echo e($config['from_name'] ?? '系统通知'); ?>" required>
                            <div class="form-text">邮件中显示的发件人名称</div>
                        </div>

                        <div class="mb-3">
                            <label for="from_email" class="form-label">发件人邮箱</label>
                            <input type="email" class="form-control" id="from_email" name="from_email" 
                                   value="<?php echo e($config['from_email'] ?? ''); ?>" required>
                            <div class="form-text">与邮箱用户名保持一致</div>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="is_enabled" name="is_enabled" 
                                   <?php echo ($config['is_enabled'] ?? 0) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_enabled">
                                启用邮箱服务
                            </label>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="reset" class="btn btn-secondary me-2">重置</button>
                            <button type="submit" class="btn btn-primary">保存配置</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">常用邮箱配置</h5>
                </div>
                <div class="card-body">
                    <h6>Gmail</h6>
                    <ul class="small">
                        <li>服务器：smtp.gmail.com</li>
                        <li>端口：587 (TLS)</li>
                        <li>需要开启两步验证并生成应用专用密码</li>
                    </ul>

                    <h6 class="mt-3">QQ邮箱</h6>
                    <ul class="small">
                        <li>服务器：smtp.qq.com</li>
                        <li>端口：587 (TLS)</li>
                        <li>需要开启SMTP服务并获取授权码</li>
                    </ul>

                    <h6 class="mt-3">163邮箱</h6>
                    <ul class="small">
                        <li>服务器：smtp.163.com</li>
                        <li>端口：587 (TLS)</li>
                        <li>需要开启SMTP服务</li>
                    </ul>

                    <h6 class="mt-3">企业微信邮箱</h6>
                    <ul class="small">
                        <li>服务器：smtp.exmail.qq.com</li>
                        <li>端口：587 (TLS)</li>
                        <li>使用企业邮箱账号密码</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 发送测试邮件模态框 -->
<div class="modal fade" id="testEmailModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">发送测试邮件</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="test-email-form">
                    <div class="mb-3">
                        <label for="test_email" class="form-label">测试邮箱</label>
                        <input type="email" class="form-control" id="test_email" name="test_email" 
                               placeholder="输入接收测试邮件的邮箱地址" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" id="send-test-email-btn">发送测试邮件</button>
            </div>
        </div>
    </div>
</div>

<script>
// 显示/隐藏 SMTP 配置
document.getElementById('driver').addEventListener('change', function() {
    const smtpConfig = document.getElementById('smtp-config');
    if (this.value === 'smtp') {
        smtpConfig.style.display = 'block';
        document.querySelectorAll('#smtp-config input').forEach(input => input.required = true);
    } else {
        smtpConfig.style.display = 'none';
        document.querySelectorAll('#smtp-config input').forEach(input => input.required = false);
    }
});

// 测试连接
document.getElementById('test-config-btn').addEventListener('click', function() {
    const btn = this;
    const originalText = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 测试中...';
    
    fetch('/admin/email/test-config', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
        } else {
            showAlert('error', data.message);
        }
    })
    .catch(error => {
        showAlert('error', '测试失败：' + error.message);
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
});

// 显示发送测试邮件模态框
document.getElementById('send-test-btn').addEventListener('click', function() {
    const modal = new bootstrap.Modal(document.getElementById('testEmailModal'));
    modal.show();
});

// 发送测试邮件
document.getElementById('send-test-email-btn').addEventListener('click', function() {
    const form = document.getElementById('test-email-form');
    const formData = new FormData(form);
    
    if (!formData.get('test_email')) {
        showAlert('error', '请输入测试邮箱地址');
        return;
    }
    
    const btn = this;
    const originalText = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 发送中...';
    
    fetch('/admin/email/send-test-email', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            bootstrap.Modal.getInstance(document.getElementById('testEmailModal')).hide();
        } else {
            showAlert('error', data.message);
        }
    })
    .catch(error => {
        showAlert('error', '发送失败：' + error.message);
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
});

// 显示提示信息
function showAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const container = document.querySelector('.container-fluid');
    container.insertBefore(alertDiv, container.firstChild);
    
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>