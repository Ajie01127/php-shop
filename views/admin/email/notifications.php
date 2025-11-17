<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">通知场景配置</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <button type="button" class="btn btn-primary" id="batch-enable-btn">
                <i class="fas fa-check"></i> 批量启用
            </button>
            <button type="button" class="btn btn-warning ms-2" id="batch-disable-btn">
                <i class="fas fa-times"></i> 批量禁用
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

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th width="40">
                                <input type="checkbox" id="select-all" class="form-check-input">
                            </th>
                            <th>场景名称</th>
                            <th>事件类型</th>
                            <th>接收者类型</th>
                            <th>状态</th>
                            <th>描述</th>
                            <th width="180">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($notifications as $notification): ?>
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input notification-checkbox" 
                                           value="<?php echo e($notification['event_type']); ?>">
                                </td>
                                <td>
                                    <strong><?php echo e($notification['name']); ?></strong>
                                </td>
                                <td>
                                    <code><?php echo e($notification['event_type']); ?></code>
                                </td>
                                <td>
                                    <?php
                                    $recipientTypes = [
                                        'admin' => '管理员',
                                        'user' => '用户',
                                        'custom' => '自定义'
                                    ];
                                    echo $recipientTypes[$notification['recipient_type']] ?? $notification['recipient_type'];
                                    ?>
                                </td>
                                <td>
                                    <?php if ($notification['is_enabled']): ?>
                                        <span class="badge bg-success">已启用</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">已禁用</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo e($notification['description']); ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="/admin/email/notification/edit/<?php echo e($notification['event_type']); ?>" 
                                           class="btn btn-outline-primary" title="编辑">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-info preview-btn" 
                                                data-event-type="<?php echo e($notification['event_type']); ?>" 
                                                title="预览">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary duplicate-btn" 
                                                data-event-type="<?php echo e($notification['event_type']); ?>" 
                                                title="复制">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                        <?php if ($notification['is_enabled']): ?>
                                            <button type="button" class="btn btn-outline-warning toggle-status-btn" 
                                                    data-event-type="<?php echo e($notification['event_type']); ?>" 
                                                    data-enabled="0" title="禁用">
                                                <i class="fas fa-toggle-on"></i>
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-outline-success toggle-status-btn" 
                                                    data-event-type="<?php echo e($notification['event_type']); ?>" 
                                                    data-enabled="1" title="启用">
                                                <i class="fas fa-toggle-off"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 可用变量说明 -->
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="card-title mb-0">可用变量说明</h5>
        </div>
        <div class="card-body">
            <div class="accordion" id="variablesAccordion">
                <?php foreach ($variableDescriptions as $eventType => $variables): ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading-<?php echo e($eventType); ?>">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                    data-bs-target="#collapse-<?php echo e($eventType); ?>">
                                <strong><?php echo e($eventType); ?></strong> - 
                                <?php 
                                $names = [
                                    'user_register' => '用户注册',
                                    'user_login' => '用户登录',
                                    'order_created' => '订单创建',
                                    'order_paid' => '订单支付',
                                    'order_shipped' => '订单发货',
                                    'order_completed' => '订单完成',
                                    'payment_failed' => '支付失败',
                                    'low_stock' => '库存不足',
                                    'system_error' => '系统错误'
                                ];
                                echo $names[$eventType] ?? $eventType;
                                ?>
                            </button>
                        </h2>
                        <div id="collapse-<?php echo e($eventType); ?>" class="accordion-collapse collapse" 
                             data-bs-parent="#variablesAccordion">
                            <div class="accordion-body">
                                <div class="row">
                                    <?php foreach ($variables as $key => $desc): ?>
                                        <div class="col-md-6 mb-2">
                                            <code>{{<?php echo e($key); ?>}}</code> - <?php echo e($desc); ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- 预览模态框 -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">邮件预览</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="preview-content"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">关闭</button>
            </div>
        </div>
    </div>
</div>

<script>
// 全选/取消全选
document.getElementById('select-all').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.notification-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    updateBatchButtons();
});

// 监听复选框变化
document.querySelectorAll('.notification-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', updateBatchButtons);
});

// 更新批量按钮状态
function updateBatchButtons() {
    const checkedBoxes = document.querySelectorAll('.notification-checkbox:checked');
    const hasChecked = checkedBoxes.length > 0;
    
    document.getElementById('batch-enable-btn').disabled = !hasChecked;
    document.getElementById('batch-disable-btn').disabled = !hasChecked;
}

// 批量启用
document.getElementById('batch-enable-btn').addEventListener('click', function() {
    batchUpdateStatus(true);
});

// 批量禁用
document.getElementById('batch-disable-btn').addEventListener('click', function() {
    batchUpdateStatus(false);
});

// 批量更新状态
function batchUpdateStatus(enable) {
    const checkedBoxes = document.querySelectorAll('.notification-checkbox:checked');
    const eventTypes = Array.from(checkedBoxes).map(cb => cb.value);
    
    if (eventTypes.length === 0) {
        showAlert('error', '请选择要操作的通知场景');
        return;
    }
    
    const btn = enable ? document.getElementById('batch-enable-btn') : document.getElementById('batch-disable-btn');
    const originalText = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 处理中...';
    
    fetch('/admin/email/batch-update-status', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({
            event_types: eventTypes,
            enable: enable
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
        showAlert('error', '操作失败：' + error.message);
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

// 切换单个状态
document.querySelectorAll('.toggle-status-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const eventType = this.dataset.eventType;
        const enabled = this.dataset.enabled;
        
        batchUpdateStatusSingle(eventType, enabled === '1');
    });
});

// 更新单个状态
function batchUpdateStatusSingle(eventType, enable) {
    fetch('/admin/email/batch-update-status', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({
            event_types: [eventType],
            enable: enable
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
        showAlert('error', '操作失败：' + error.message);
    });
}

// 预览邮件
document.querySelectorAll('.preview-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const eventType = this.dataset.eventType;
        previewEmail(eventType);
    });
});

function previewEmail(eventType) {
    const modal = new bootstrap.Modal(document.getElementById('previewModal'));
    const contentDiv = document.getElementById('preview-content');
    
    contentDiv.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> 加载中...</div>';
    modal.show();
    
    // 构造测试变量数据
    const testVariables = {
        'user_register': {
            'username': '测试用户',
            'email': 'test@example.com',
            'created_at': date('Y-m-d H:i:s'),
            'ip_address': '127.0.0.1'
        },
        'user_login': {
            'username': '测试用户',
            'login_time': date('Y-m-d H:i:s'),
            'ip_address': '127.0.0.1',
            'location': '本地'
        },
        'order_created': {
            'username': '测试用户',
            'order_no': 'ORD202401001',
            'total_amount': '299.00',
            'product_count': '3',
            'created_at': date('Y-m-d H:i:s')
        },
        'order_paid': {
            'username': '测试用户',
            'order_no': 'ORD202401001',
            'paid_amount': '299.00',
            'payment_method': '微信支付',
            'paid_at': date('Y-m-d H:i:s')
        },
        'order_shipped': {
            'username': '测试用户',
            'order_no': 'ORD202401001',
            'express_company': '顺丰快递',
            'tracking_number': 'SF1234567890',
            'shipped_at': date('Y-m-d H:i:s')
        },
        'order_completed': {
            'username': '测试用户',
            'order_no': 'ORD202401001',
            'total_amount': '299.00',
            'completed_at': date('Y-m-d H:i:s')
        },
        'payment_failed': {
            'username': '测试用户',
            'order_no': 'ORD202401001',
            'amount': '299.00',
            'error_message': '余额不足',
            'failed_at': date('Y-m-d H:i:s')
        },
        'low_stock': {
            'products': '<li>测试商品1 - 当前库存：5 件</li><li>测试商品2 - 当前库存：2 件</li>'
        },
        'system_error': {
            'error_type': '数据库错误',
            'error_message': '连接数据库失败',
            'error_time': date('Y-m-d H:i:s'),
            'request_uri': '/test',
            'ip_address': '127.0.0.1'
        }
    };
    
    const variables = testVariables[eventType] || {};
    const params = new URLSearchParams({
        event_type: eventType
    });
    
    // 添加变量参数
    Object.keys(variables).forEach(key => {
        params.append(`variables[${key}]`, variables[key]);
    });
    
    fetch('/admin/email/preview-template?' + params.toString(), {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            contentDiv.innerHTML = `
                <div class="mb-3">
                    <h6>主题：</h6>
                    <p>${data.subject}</p>
                </div>
                <div>
                    <h6>内容：</h6>
                    <div class="border p-3 bg-light">
                        ${data.content}
                    </div>
                </div>
            `;
        } else {
            contentDiv.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
        }
    })
    .catch(error => {
        contentDiv.innerHTML = `<div class="alert alert-danger">预览失败：${error.message}</div>`;
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