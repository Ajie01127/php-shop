<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">邮件详情</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="/admin/email/logs" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> 返回列表
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <!-- 基本信息 -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">基本信息</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">接收邮箱</label>
                                <div><?php echo e($log['to_email']); ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">邮件主题</label>
                                <div><?php echo e($log['subject']); ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">事件类型</label>
                                <div>
                                    <?php if ($log['event_type']): ?>
                                        <span class="badge bg-info"><?php echo e($log['event_type']); ?></span>
                                        <?php if ($log['notification_name']): ?>
                                            <span class="text-muted ms-2"><?php echo e($log['notification_name']); ?></span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">发送状态</label>
                                <div>
                                    <?php
                                    $statusClass = [
                                        'sent' => 'success',
                                        'failed' => 'danger',
                                        'pending' => 'secondary'
                                    ];
                                    $statusText = [
                                        'sent' => '已发送',
                                        'failed' => '发送失败',
                                        'pending' => '待发送'
                                    ];
                                    ?>
                                    <span class="badge bg-<?php echo $statusClass[$log['status']]; ?>">
                                        <?php echo $statusText[$log['status']]; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">创建时间</label>
                                <div><?php echo date('Y-m-d H:i:s', strtotime($log['created_at'])); ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">发送时间</label>
                                <div>
                                    <?php if ($log['sent_at']): ?>
                                        <?php echo date('Y-m-d H:i:s', strtotime($log['sent_at'])); ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 邮件内容 -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">邮件内容</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <button type="button" class="btn btn-sm btn-outline-primary" id="toggle-html-btn">
                            <i class="fas fa-code"></i> 切换到HTML源码
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-info ms-2" id="preview-btn">
                            <i class="fas fa-eye"></i> 预览效果
                        </button>
                    </div>
                    
                    <div id="content-display">
                        <div id="content-preview">
                            <?php echo $log['content']; ?>
                        </div>
                        <div id="content-source" style="display: none;">
                            <pre class="bg-light p-3 rounded"><code><?php echo htmlspecialchars($log['content']); ?></code></pre>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($log['error_message']): ?>
                <!-- 错误信息 -->
                <div class="card mt-3">
                    <div class="card-header bg-danger text-white">
                        <h5 class="card-title mb-0">错误信息</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-danger">
                            <?php echo e($log['error_message']); ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-md-4">
            <!-- 操作面板 -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">操作</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <?php if ($log['status'] === 'failed'): ?>
                            <button type="button" class="btn btn-warning" id="retry-btn">
                                <i class="fas fa-redo"></i> 重新发送
                            </button>
                        <?php endif; ?>
                        
                        <button type="button" class="btn btn-outline-primary" id="forward-btn">
                            <i class="fas fa-share"></i> 转发邮件
                        </button>
                        
                        <button type="button" class="btn btn-outline-info" id="copy-btn">
                            <i class="fas fa-copy"></i> 复制内容
                        </button>
                        
                        <hr>
                        
                        <a href="/admin/email/logs" class="btn btn-outline-secondary">
                            <i class="fas fa-list"></i> 返回列表
                        </a>
                    </div>
                </div>
            </div>

            <!-- 邮件统计 -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">相关信息</h5>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <label class="form-label fw-bold">内容长度</label>
                        <div><?php echo strlen($log['content']); ?> 字符</div>
                    </div>
                    
                    <div class="mb-2">
                        <label class="form-label fw-bold">预计发送时间</label>
                        <div>
                            <?php if ($log['sent_at']): ?>
                                已发送
                            <?php elseif ($log['status'] === 'pending'): ?>
                                等待发送
                            <?php else: ?>
                                发送失败
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if ($log['event_type']): ?>
                        <div class="mb-2">
                            <label class="form-label fw-bold">相关事件</label>
                            <div>
                                <a href="/admin/email/notifications" class="text-decoration-none">
                                    <i class="fas fa-cog"></i> 通知配置
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 转发邮件模态框 -->
<div class="modal fade" id="forwardModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">转发邮件</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="forward-form">
                    <div class="mb-3">
                        <label for="forward_to" class="form-label">转发到</label>
                        <input type="email" class="form-control" id="forward_to" name="forward_to" 
                               placeholder="输入接收邮箱地址" required>
                    </div>
                    <div class="mb-3">
                        <label for="forward_subject" class="form-label">主题</label>
                        <input type="text" class="form-control" id="forward_subject" name="forward_subject" 
                               value="[转发] <?php echo e($log['subject']); ?>" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" id="confirm-forward-btn">发送</button>
            </div>
        </div>
    </div>
</div>

<script>
// 切换HTML显示
document.getElementById('toggle-html-btn').addEventListener('click', function() {
    const preview = document.getElementById('content-preview');
    const source = document.getElementById('content-source');
    const btn = this;
    
    if (preview.style.display !== 'none') {
        preview.style.display = 'none';
        source.style.display = 'block';
        btn.innerHTML = '<i class="fas fa-eye"></i> 切换到预览效果';
    } else {
        preview.style.display = 'block';
        source.style.display = 'none';
        btn.innerHTML = '<i class="fas fa-code"></i> 切换到HTML源码';
    }
});

// 预览效果
document.getElementById('preview-btn').addEventListener('click', function() {
    const content = <?php echo json_encode($log['content']); ?>;
    const newWindow = window.open('', '_blank');
    newWindow.document.write(content);
    newWindow.document.close();
});

// 重试发送
<?php if ($log['status'] === 'failed'): ?>
document.getElementById('retry-btn').addEventListener('click', function() {
    const btn = this;
    const originalText = btn.innerHTML;
    
    if (!confirm('确定要重新发送这封邮件吗？')) {
        return;
    }
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 发送中...';
    
    fetch('/admin/email/retry-failed', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            ids: [<?php echo $log['id']; ?>]
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            setTimeout(() => location.reload(), 2000);
        } else {
            showAlert('error', data.message);
        }
    })
    .catch(error => {
        showAlert('error', '重试失败：' + error.message);
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
});
<?php endif; ?>

// 转发邮件
document.getElementById('forward-btn').addEventListener('click', function() {
    const modal = new bootstrap.Modal(document.getElementById('forwardModal'));
    modal.show();
});

document.getElementById('confirm-forward-btn').addEventListener('click', function() {
    const form = document.getElementById('forward-form');
    const formData = new FormData(form);
    
    if (!formData.get('forward_to') || !formData.get('forward_subject')) {
        showAlert('error', '请填写完整信息');
        return;
    }
    
    const btn = this;
    const originalText = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 发送中...';
    
    const content = <?php echo json_encode($log['content']); ?>;
    
    // 这里应该调用发送邮件的API
    fetch('/admin/email/send-test-email', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({
            test_email: formData.get('forward_to'),
            subject: formData.get('forward_subject'),
            content: content
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', '邮件转发成功');
            bootstrap.Modal.getInstance(document.getElementById('forwardModal')).hide();
        } else {
            showAlert('error', '转发失败：' + data.message);
        }
    })
    .catch(error => {
        showAlert('error', '转发失败：' + error.message);
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
});

// 复制内容
document.getElementById('copy-btn').addEventListener('click', function() {
    const content = <?php echo json_encode($log['content']); ?>;
    
    if (navigator.clipboard) {
        navigator.clipboard.writeText(content).then(() => {
            showAlert('success', '内容已复制到剪贴板');
        }).catch(err => {
            showAlert('error', '复制失败：' + err.message);
        });
    } else {
        // 降级方案
        const textarea = document.createElement('textarea');
        textarea.value = content;
        document.body.appendChild(textarea);
        textarea.select();
        
        try {
            document.execCommand('copy');
            showAlert('success', '内容已复制到剪贴板');
        } catch (err) {
            showAlert('error', '复制失败');
        }
        
        document.body.removeChild(textarea);
    }
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