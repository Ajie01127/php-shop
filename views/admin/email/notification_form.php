<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><?php echo $page_title; ?></h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="/admin/email/notifications" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> 返回列表
            </a>
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
                <div class="card-body">
                    <form method="POST" action="/admin/email/notification/edit<?php echo $eventType ? '/' . e($eventType) : ''; ?>" id="notification-form">
                        <div class="mb-3">
                            <label for="name" class="form-label">场景名称</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?php echo e($notification['name'] ?? ''); ?>" required>
                            <div class="form-text">用于在管理界面中显示的名称</div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">场景描述</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?php echo e($notification['description'] ?? ''); ?></textarea>
                            <div class="form-text">描述该通知场景的用途</div>
                        </div>

                        <div class="mb-3">
                            <label for="template_subject" class="form-label">邮件主题模板</label>
                            <input type="text" class="form-control" id="template_subject" name="template_subject" 
                                   value="<?php echo e($notification['template_subject'] ?? ''); ?>" required>
                            <div class="form-text">支持变量：使用 {{变量名}} 格式</div>
                        </div>

                        <div class="mb-3">
                            <label for="template_content" class="form-label">邮件内容模板</label>
                            <textarea class="form-control" id="template_content" name="template_content" rows="12" 
                                      required><?php echo e($notification['template_content'] ?? ''); ?></textarea>
                            <div class="form-text">支持HTML格式，使用 {{变量名}} 格式插入变量</div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="recipient_type" class="form-label">接收者类型</label>
                                    <select class="form-select" id="recipient_type" name="recipient_type">
                                        <option value="user" <?php echo ($notification['recipient_type'] ?? '') === 'user' ? 'selected' : ''; ?>>用户</option>
                                        <option value="admin" <?php echo ($notification['recipient_type'] ?? '') === 'admin' ? 'selected' : ''; ?>>管理员</option>
                                        <option value="custom" <?php echo ($notification['recipient_type'] ?? '') === 'custom' ? 'selected' : ''; ?>>自定义</option>
                                    </select>
                                    <div class="form-text">邮件的接收者类型</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check mt-4">
                                        <input type="checkbox" class="form-check-input" id="is_enabled" name="is_enabled" 
                                               <?php echo ($notification['is_enabled'] ?? 1) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="is_enabled">
                                            启用该通知场景
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3" id="recipients-group" style="<?php echo ($notification['recipient_type'] ?? '') !== 'custom' ? 'display: none;' : ''; ?>">
                            <label for="recipients" class="form-label">自定义接收者邮箱</label>
                            <input type="text" class="form-control" id="recipients" name="recipients" 
                                   value="<?php echo e($notification['recipients'] ?? ''); ?>" 
                                   placeholder="输入邮箱地址，多个用逗号分隔">
                            <div class="form-text">当接收者类型为自定义时，指定接收邮件的邮箱地址</div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="/admin/email/notifications" class="btn btn-secondary me-2">取消</a>
                            <button type="button" class="btn btn-outline-primary me-2" id="preview-btn">预览</button>
                            <button type="submit" class="btn btn-primary">保存</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- 可用变量 -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">可用变量</h5>
                </div>
                <div class="card-body">
                    <div id="available-variables">
                        <p class="text-muted">选择事件类型后显示可用变量</p>
                    </div>
                </div>
            </div>

            <!-- 模板示例 -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">模板示例</h5>
                </div>
                <div class="card-body">
                    <h6>基本语法</h6>
                    <ul class="small">
                        <li><code>{{username}}</code> - 简单变量</li>
                        <li><code>&lt;h3&gt;{{title}}&lt;/h3&gt;</code> - HTML标签</li>
                        <li><code>&lt;a href="{{url}}"&gt;链接&lt;/a&gt;</code> - 链接</li>
                    </ul>

                    <h6 class="mt-3">完整示例</h6>
                    <pre class="small"><code>&lt;h3&gt;{{subject}}&lt;/h3&gt;
&lt;p&gt;尊敬的 {{username}}，&lt;/p&gt;
&lt;p&gt;{{content}}&lt;/p&gt;
&lt;p&gt;时间：{{created_at}}&lt;/p&gt;</code></pre>
                </div>
            </div>

            <!-- 快速插入 -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">快速插入</h5>
                </div>
                <div class="card-body">
                    <div id="quick-insert">
                        <p class="text-muted">点击下方变量可快速插入到模板中</p>
                    </div>
                </div>
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
// 接收者类型变化时显示/隐藏自定义接收者
document.getElementById('recipient_type').addEventListener('change', function() {
    const recipientsGroup = document.getElementById('recipients-group');
    if (this.value === 'custom') {
        recipientsGroup.style.display = 'block';
        document.getElementById('recipients').required = true;
    } else {
        recipientsGroup.style.display = 'none';
        document.getElementById('recipients').required = false;
    }
});

// 事件类型选择（如果存在选择框）
const eventTypeSelect = document.getElementById('event_type');
if (eventTypeSelect) {
    eventTypeSelect.addEventListener('change', function() {
        updateVariables(this.value);
    });
    
    // 初始化时更新变量
    updateVariables(eventTypeSelect.value);
} else {
    // 如果是编辑模式，根据当前事件类型显示变量
    const currentEventType = '<?php echo e($eventType ?? 'user_register'); ?>';
    updateVariables(currentEventType);
}

// 更新可用变量显示
function updateVariables(eventType) {
    const variablesData = <?php echo json_encode($variableDescriptions); ?>;
    const variablesDiv = document.getElementById('available-variables');
    const quickInsertDiv = document.getElementById('quick-insert');
    
    if (variablesData[eventType]) {
        let html = '<h6>' + getEventName(eventType) + '</h6><ul class="small">';
        let quickHtml = '<p class="mb-2">点击插入到模板中：</p><div class="d-flex flex-wrap gap-1">';
        
        Object.entries(variablesData[eventType]).forEach(([key, desc]) => {
            html += `<li><code>{{${key}}}</code> - ${desc}</li>`;
            quickHtml += `<button type="button" class="btn btn-sm btn-outline-secondary variable-btn" data-variable="${key}">{{${key}}}</button>`;
        });
        
        html += '</ul>';
        quickHtml += '</div>';
        
        variablesDiv.innerHTML = html;
        quickInsertDiv.innerHTML = quickHtml;
        
        // 绑定快速插入事件
        document.querySelectorAll('.variable-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const variable = this.dataset.variable;
                const textarea = document.getElementById('template_content');
                const start = textarea.selectionStart;
                const end = textarea.selectionEnd;
                const text = textarea.value;
                
                textarea.value = text.substring(0, start) + '{{' + variable + '}}' + text.substring(end);
                textarea.focus();
                textarea.setSelectionRange(start + variable.length + 4, start + variable.length + 4);
            });
        });
    } else {
        variablesDiv.innerHTML = '<p class="text-muted">暂无可用的变量</p>';
        quickInsertDiv.innerHTML = '<p class="text-muted">暂无可快速插入的变量</p>';
    }
}

// 获取事件名称
function getEventName(eventType) {
    const names = {
        'user_register': '用户注册',
        'user_login': '用户登录',
        'order_created': '订单创建',
        'order_paid': '订单支付',
        'order_shipped': '订单发货',
        'order_completed': '订单完成',
        'payment_failed': '支付失败',
        'low_stock': '库存不足',
        'system_error': '系统错误'
    };
    return names[eventType] || eventType;
}

// 预览邮件
document.getElementById('preview-btn').addEventListener('click', function() {
    const eventType = document.getElementById('event_type')?.value || '<?php echo e($eventType ?? 'user_register'); ?>';
    const subject = document.getElementById('template_subject').value;
    const content = document.getElementById('template_content').value;
    
    if (!subject || !content) {
        showAlert('error', '请填写邮件主题和内容');
        return;
    }
    
    previewEmail(eventType, subject, content);
});

function previewEmail(eventType, subject, content) {
    const modal = new bootstrap.Modal(document.getElementById('previewModal'));
    const contentDiv = document.getElementById('preview-content');
    
    contentDiv.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> 加载中...</div>';
    modal.show();
    
    // 构造测试变量数据
    const testVariables = {
        'user_register': {
            'username': '测试用户',
            'email': 'test@example.com',
            'created_at': new Date().toLocaleString('zh-CN'),
            'ip_address': '127.0.0.1'
        },
        'user_login': {
            'username': '测试用户',
            'login_time': new Date().toLocaleString('zh-CN'),
            'ip_address': '127.0.0.1',
            'location': '本地'
        },
        'order_created': {
            'username': '测试用户',
            'order_no': 'ORD202401001',
            'total_amount': '299.00',
            'product_count': '3',
            'created_at': new Date().toLocaleString('zh-CN')
        },
        'order_paid': {
            'username': '测试用户',
            'order_no': 'ORD202401001',
            'paid_amount': '299.00',
            'payment_method': '微信支付',
            'paid_at': new Date().toLocaleString('zh-CN')
        },
        'order_shipped': {
            'username': '测试用户',
            'order_no': 'ORD202401001',
            'express_company': '顺丰快递',
            'tracking_number': 'SF1234567890',
            'shipped_at': new Date().toLocaleString('zh-CN')
        },
        'order_completed': {
            'username': '测试用户',
            'order_no': 'ORD202401001',
            'total_amount': '299.00',
            'completed_at': new Date().toLocaleString('zh-CN')
        },
        'payment_failed': {
            'username': '测试用户',
            'order_no': 'ORD202401001',
            'amount': '299.00',
            'error_message': '余额不足',
            'failed_at': new Date().toLocaleString('zh-CN')
        },
        'low_stock': {
            'products': '<li>测试商品1 - 当前库存：5 件</li><li>测试商品2 - 当前库存：2 件</li>'
        },
        'system_error': {
            'error_type': '数据库错误',
            'error_message': '连接数据库失败',
            'error_time': new Date().toLocaleString('zh-CN'),
            'request_uri': '/test',
            'ip_address': '127.0.0.1'
        }
    };
    
    const variables = testVariables[eventType] || {};
    
    // 本地替换变量
    let previewSubject = subject;
    let previewContent = content;
    
    Object.keys(variables).forEach(key => {
        const regex = new RegExp('{{' + key + '}}', 'g');
        previewSubject = previewSubject.replace(regex, variables[key]);
        previewContent = previewContent.replace(regex, variables[key]);
    });
    
    // 显示预览
    contentDiv.innerHTML = `
        <div class="mb-3">
            <h6>主题：</h6>
            <p>${previewSubject}</p>
        </div>
        <div>
            <h6>内容：</h6>
            <div class="border p-3 bg-light">
                ${previewContent}
            </div>
        </div>
    `;
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