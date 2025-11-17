<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">邮件日志</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <button type="button" class="btn btn-outline-primary" id="retry-failed-btn">
                <i class="fas fa-redo"></i> 重试失败邮件
            </button>
            <button type="button" class="btn btn-outline-warning ms-2" id="batch-delete-btn">
                <i class="fas fa-trash"></i> 批量删除
            </button>
            <button type="button" class="btn btn-outline-danger ms-2" id="clear-logs-btn">
                <i class="fas fa-trash-alt"></i> 清空日志
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

    <!-- 搜索过滤 -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="/admin/email/logs" class="row g-3">
                <div class="col-md-3">
                    <input type="text" class="form-control" name="search" placeholder="搜索邮箱、主题或事件类型" 
                           value="<?php echo e($filters['search']); ?>">
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="status">
                        <option value="">全部状态</option>
                        <option value="sent" <?php echo $filters['status'] === 'sent' ? 'selected' : ''; ?>>已发送</option>
                        <option value="failed" <?php echo $filters['status'] === 'failed' ? 'selected' : ''; ?>>发送失败</option>
                        <option value="pending" <?php echo $filters['status'] === 'pending' ? 'selected' : ''; ?>>待发送</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="event_type">
                        <option value="">全部事件</option>
                        <?php foreach ($notifications as $notif): ?>
                            <option value="<?php echo e($notif['event_type']); ?>" 
                                    <?php echo $filters['event_type'] === $notif['event_type'] ? 'selected' : ''; ?>>
                                <?php echo e($notif['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" name="start_date" 
                           value="<?php echo e($filters['start_date']); ?>" placeholder="开始日期">
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" name="end_date" 
                           value="<?php echo e($filters['end_date']); ?>" placeholder="结束日期">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">搜索</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 数据表格 -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th width="40">
                                <input type="checkbox" id="select-all" class="form-check-input">
                            </th>
                            <th>接收邮箱</th>
                            <th>主题</th>
                            <th>事件类型</th>
                            <th>状态</th>
                            <th>创建时间</th>
                            <th>发送时间</th>
                            <th width="120">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs['data'])): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted">暂无邮件日志</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($logs['data'] as $log): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input log-checkbox" value="<?php echo $log['id']; ?>">
                                    </td>
                                    <td>
                                        <?php echo e($log['to_email']); ?>
                                    </td>
                                    <td>
                                        <span class="d-inline-block text-truncate" style="max-width: 200px;" 
                                              title="<?php echo e($log['subject']); ?>">
                                            <?php echo e($log['subject']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($log['event_type']): ?>
                                            <span class="badge bg-info"><?php echo e($log['event_type']); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
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
                                    </td>
                                    <td>
                                        <?php echo date('Y-m-d H:i:s', strtotime($log['created_at'])); ?>
                                    </td>
                                    <td>
                                        <?php if ($log['sent_at']): ?>
                                            <?php echo date('Y-m-d H:i:s', strtotime($log['sent_at'])); ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="/admin/email/log/view/<?php echo $log['id']; ?>" 
                                               class="btn btn-outline-primary" title="查看详情">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($log['status'] === 'failed'): ?>
                                                <button type="button" class="btn btn-outline-warning retry-btn" 
                                                        data-id="<?php echo $log['id']; ?>" title="重试">
                                                    <i class="fas fa-redo"></i>
                                                </button>
                                            <?php endif; ?>
                                            <button type="button" class="btn btn-outline-danger delete-btn" 
                                                    data-id="<?php echo $log['id']; ?>" title="删除">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- 分页 -->
            <?php if ($logs['pages'] > 1): ?>
                <nav aria-label="分页">
                    <ul class="pagination justify-content-center">
                        <?php if ($logs['page'] > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $logs['page'] - 1; ?>&<?php echo http_build_query($filters); ?>">上一页</a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $logs['pages']; $i++): ?>
                            <?php if ($i == $logs['page']): ?>
                                <li class="page-item active">
                                    <span class="page-link"><?php echo $i; ?></span>
                                </li>
                            <?php else: ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&<?php echo http_build_query($filters); ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($logs['page'] < $logs['pages']): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $logs['page'] + 1; ?>&<?php echo http_build_query($filters); ?>">下一页</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- 删除确认模态框 -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">确认删除</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>确定要删除选中的邮件日志吗？此操作不可恢复。</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                <button type="button" class="btn btn-danger" id="confirm-delete-btn">确认删除</button>
            </div>
        </div>
    </div>
</div>

<script>
// 全选/取消全选
document.getElementById('select-all').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.log-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    updateBatchButtons();
});

// 监听复选框变化
document.querySelectorAll('.log-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', updateBatchButtons);
});

// 更新批量按钮状态
function updateBatchButtons() {
    const checkedBoxes = document.querySelectorAll('.log-checkbox:checked');
    const hasChecked = checkedBoxes.length > 0;
    
    document.getElementById('batch-delete-btn').disabled = !hasChecked;
}

// 批量删除
document.getElementById('batch-delete-btn').addEventListener('click', function() {
    const checkedBoxes = document.querySelectorAll('.log-checkbox:checked');
    if (checkedBoxes.length === 0) {
        showAlert('error', '请选择要删除的日志');
        return;
    }
    
    showDeleteModal();
});

// 单个删除
document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        currentDeleteIds = [this.dataset.id];
        showDeleteModal();
    });
});

let currentDeleteIds = [];

// 显示删除确认模态框
function showDeleteModal() {
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

// 确认删除
document.getElementById('confirm-delete-btn').addEventListener('click', function() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('deleteModal'));
    modal.hide();
    
    if (currentDeleteIds.length > 0) {
        // 单个删除
        deleteLogs(currentDeleteIds);
    } else {
        // 批量删除
        const checkedBoxes = document.querySelectorAll('.log-checkbox:checked');
        const ids = Array.from(checkedBoxes).map(cb => cb.value);
        deleteLogs(ids);
    }
    
    currentDeleteIds = [];
});

// 删除日志
function deleteLogs(ids) {
    fetch('/admin/email/batch-delete-logs', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({
            ids: ids
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert('error', data.message);
        }
    })
    .catch(error => {
        showAlert('error', '删除失败：' + error.message);
    });
}

// 清空日志
document.getElementById('clear-logs-btn').addEventListener('click', function() {
    if (!confirm('确定要清空所有邮件日志吗？此操作不可恢复。')) {
        return;
    }
    
    fetch('/admin/email/clear-logs', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert('error', data.message);
        }
    })
    .catch(error => {
        showAlert('error', '清空失败：' + error.message);
    });
});

// 重试失败邮件
document.getElementById('retry-failed-btn').addEventListener('click', function() {
    retryFailedEmails();
});

// 单个重试
document.querySelectorAll('.retry-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        retryFailedEmails([this.dataset.id]);
    });
});

function retryFailedEmails(ids) {
    const btn = document.getElementById('retry-failed-btn');
    const originalText = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 重试中...';
    
    fetch('/admin/email/retry-failed', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            ids: ids
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            if (data.results) {
                // 显示详细结果
                let resultHtml = '<div class="mt-2"><h6>重试结果：</h6><ul class="small">';
                data.results.forEach(result => {
                    const icon = result.success ? '✅' : '❌';
                    resultHtml += `<li>${icon} ${result.to_email}: ${result.message}</li>`;
                });
                resultHtml += '</ul></div>';
                
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-info alert-dismissible fade show';
                alertDiv.innerHTML = resultHtml + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
                
                const container = document.querySelector('.container-fluid');
                container.insertBefore(alertDiv, container.firstChild);
            }
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
}

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