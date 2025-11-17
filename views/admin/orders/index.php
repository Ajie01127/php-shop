<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>订单管理 - 私域商城后台</title>
    <link href="https://cdn.bootcdn.net/ajax/libs/twitter-bootstrap/4.6.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.bootcdn.net/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 250px;
            background: #2c3e50;
            color: white;
            z-index: 1000;
            overflow-y: auto;
        }
        .sidebar .logo {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid #34495e;
        }
        .sidebar .nav {
            padding: 0;
        }
        .sidebar .nav-item {
            border-bottom: 1px solid #34495e;
        }
        .sidebar .nav-link {
            color: #ecf0f1;
            padding: 15px 20px;
            display: block;
            text-decoration: none;
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: #34495e;
            color: #3498db;
        }
        .sidebar .nav-link i {
            width: 20px;
            margin-right: 10px;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
            min-height: 100vh;
            background: #f8f9fa;
        }
        .top-header {
            background: white;
            padding: 20px;
            margin: -20px -20px 20px;
            border-bottom: 1px solid #dee2e6;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .order-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            transition: transform 0.2s;
        }
        .order-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-paid { background: #cce5ff; color: #004085; }
        .status-shipped { background: #d1ecf1; color: #0c5460; }
        .status-completed { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <!-- 侧边栏 -->
    <div class="sidebar">
        <div class="logo">
            <h4>私域商城管理</h4>
        </div>
        <nav class="nav">
            <div class="nav-item">
                <a href="/admin/dashboard" class="nav-link">
                    <i class="fas fa-tachometer-alt"></i>
                    控制台
                </a>
            </div>
            
            <div class="nav-item">
                <a href="/admin/products" class="nav-link">
                    <i class="fas fa-box"></i>
                    商品管理
                </a>
            </div>
            
            <div class="nav-item">
                <a href="/admin/orders" class="nav-link active">
                    <i class="fas fa-shopping-cart"></i>
                    订单管理
                </a>
            </div>
            
            <div class="nav-item">
                <a href="/admin/users" class="nav-link">
                    <i class="fas fa-users"></i>
                    用户管理
                </a>
            </div>
            
            <div class="nav-item">
                <a href="/admin/marketing" class="nav-link">
                    <i class="fas fa-bullhorn"></i>
                    营销活动
                </a>
            </div>
            
            <div class="nav-item">
                <a href="/admin/member/levels" class="nav-link">
                    <i class="fas fa-crown"></i>
                    会员等级
                </a>
            </div>
            
            <div class="nav-item">
                <a href="#" class="nav-link">
                    <i class="fas fa-credit-card"></i>
                    支付管理
                    <i class="fas fa-chevron-down float-right"></i>
                </a>
                <nav class="nav ml-3">
                    <div class="nav-item">
                        <a href="/admin/payment/channels" class="nav-link">
                            <i class="fas fa-university"></i>
                            支付通道
                        </a>
                    </div>
                </nav>
            </div>
            
            <div class="nav-item">
                <a href="#" class="nav-link">
                    <i class="fas fa-truck"></i>
                    物流管理
                    <i class="fas fa-chevron-down float-right"></i>
                </a>
                <nav class="nav ml-3">
                    <div class="nav-item">
                        <a href="/admin/express/configs" class="nav-link">
                            <i class="fas fa-cog"></i>
                            快递配置
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="/admin/express/orders" class="nav-link">
                            <i class="fas fa-list"></i>
                            发货单
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="/admin/express/print-config" class="nav-link">
                            <i class="fas fa-print"></i>
                            面单打印
                        </a>
                    </div>
                </nav>
            </div>
            
            <div class="nav-item">
                <a href="#" class="nav-link">
                    <i class="fas fa-envelope"></i>
                    通知管理
                    <i class="fas fa-chevron-down float-right"></i>
                </a>
                <nav class="nav ml-3">
                    <div class="nav-item">
                        <a href="/admin/sms/settings" class="nav-link">
                            <i class="fas fa-comment"></i>
                            短信设置
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="/admin/sms/statistics" class="nav-link">
                            <i class="fas fa-chart-bar"></i>
                            短信统计
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="/admin/email/config" class="nav-link">
                            <i class="fas fa-at"></i>
                            邮箱配置
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="/admin/email/notifications" class="nav-link">
                            <i class="fas fa-bell"></i>
                            邮件通知
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="/admin/email/logs" class="nav-link">
                            <i class="fas fa-history"></i>
                            邮件日志
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="/admin/email/statistics" class="nav-link">
                            <i class="fas fa-chart-line"></i>
                            邮件统计
                        </a>
                    </div>
                </nav>
            </div>
            
            <div class="nav-item">
                <a href="/admin/freight/templates" class="nav-link">
                    <i class="fas fa-shipping-fast"></i>
                    运费模板
                </a>
            </div>
            
            <div class="nav-item">
                <a href="/admin/settings" class="nav-link">
                    <i class="fas fa-cogs"></i>
                    系统设置
                </a>
            </div>
            
            <div class="nav-item">
                <a href="/admin/logout" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i>
                    退出登录
                </a>
            </div>
        </nav>
    </div>
    
    <!-- 主要内容区域 -->
    <div class="main-content">
        <div class="top-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h4 class="mb-0">订单管理</h4>
                    <small class="text-muted">管理商城订单信息</small>
                </div>
                <div class="col-md-6 text-right">
                    <button type="button" class="btn btn-outline-success" onclick="exportOrders()">
                        <i class="fas fa-download"></i> 导出订单
                    </button>
                    <button type="button" class="btn btn-outline-warning" onclick="batchProcess()">
                        <i class="fas fa-tasks"></i> 批量处理
                    </button>
                </div>
            </div>
        </div>
        
        <!-- 搜索筛选 -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <input type="text" name="order_no" class="form-control" placeholder="订单号" value="<?= htmlspecialchars(get('order_no')) ?>">
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-control">
                            <option value="">全部状态</option>
                            <option value="pending" <?= get('status') == 'pending' ? 'selected' : '' ?>>待付款</option>
                            <option value="paid" <?= get('status') == 'paid' ? 'selected' : '' ?>>已付款</option>
                            <option value="shipped" <?= get('status') == 'shipped' ? 'selected' : '' ?>>已发货</option>
                            <option value="completed" <?= get('status') == 'completed' ? 'selected' : '' ?>>已完成</option>
                            <option value="cancelled" <?= get('status') == 'cancelled' ? 'selected' : '' ?>>已取消</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars(get('start_date')) ?>">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars(get('end_date')) ?>">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> 搜索
                        </button>
                        <a href="/admin/orders" class="btn btn-outline-secondary">重置</a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- 订单列表 -->
        <div class="order-card">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                            </th>
                            <th>订单号</th>
                            <th>用户</th>
                            <th>商品数量</th>
                            <th>订单金额</th>
                            <th>支付方式</th>
                            <th>订单状态</th>
                            <th>下单时间</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>
                                <input type="checkbox" name="order_ids[]" value="<?= $order['id'] ?>" class="order-checkbox">
                            </td>
                            <td>
                                <a href="/admin/orders/<?= $order['id'] ?>" class="text-decoration-none">
                                    <?= htmlspecialchars($order['order_no']) ?>
                                </a>
                            </td>
                            <td>
                                <img src="<?= $order['user_avatar'] ?: '/images/default-avatar.png' ?>" 
                                     class="rounded-circle" width="24" height="24" style="object-fit: cover;">
                                <?= htmlspecialchars($order['user_name']) ?>
                            </td>
                            <td><?= $order['item_count'] ?>件</td>
                            <td class="text-danger font-weight-bold">¥<?= number_format($order['total_amount'], 2) ?></td>
                            <td><?= $order['pay_type'] ?: '-' ?></td>
                            <td>
                                <span class="status-badge status-<?= $order['status'] ?>">
                                    <?= getOrderStatusText($order['status']) ?>
                                </span>
                            </td>
                            <td><?= $order['created_at'] ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="/admin/orders/<?= $order['id'] ?>" class="btn btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-info" onclick="editOrder(<?= $order['id'] ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-success" onclick="updateOrderStatus(<?= $order['id'] ?>)">
                                        <i class="fas fa-sync"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger" onclick="cancelOrder(<?= $order['id'] ?>)">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- 分页 -->
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <?= generatePagination($pagination) ?>
                </ul>
            </nav>
        </div>
    </div>
    
    <!-- 订单状态更新模态框 -->
    <div class="modal fade" id="statusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">更新订单状态</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="statusForm">
                        <input type="hidden" name="order_id" id="orderId">
                        <div class="form-group">
                            <label for="status">订单状态</label>
                            <select name="status" id="status" class="form-control" required>
                                <option value="">请选择状态</option>
                                <option value="pending">待付款</option>
                                <option value="paid">已付款</option>
                                <option value="shipped">已发货</option>
                                <option value="completed">已完成</option>
                                <option value="cancelled">已取消</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="remark">备注</label>
                            <textarea name="remark" id="remark" class="form-control" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-primary" onclick="saveStatus()">保存</button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.bootcdn.net/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.bootcdn.net/ajax/libs/twitter-bootstrap/4.6.0/js/bootstrap.min.js"></script>
    
    <script>
        function toggleSelectAll() {
            const checkboxes = document.querySelectorAll('.order-checkbox');
            const selectAll = document.getElementById('selectAll');
            
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAll.checked;
            });
        }
        
        function updateOrderStatus(orderId) {
            $('#orderId').val(orderId);
            $('#statusModal').modal('show');
        }
        
        function saveStatus() {
            const orderId = $('#orderId').val();
            const status = $('#status').val();
            const remark = $('#remark').val();
            
            if (!status) {
                alert('请选择订单状态');
                return;
            }
            
            $.post('/admin/orders/' + orderId + '/status', {
                status: status,
                remark: remark
            }, function(response) {
                if (response.success) {
                    $('#statusModal').modal('hide');
                    location.reload();
                } else {
                    alert('更新失败：' + response.message);
                }
            });
        }
        
        function editOrder(orderId) {
            window.location.href = '/admin/orders/' + orderId + '/edit';
        }
        
        function cancelOrder(orderId) {
            if (confirm('确定要取消该订单吗？')) {
                $.post('/admin/orders/' + orderId + '/status', {
                    status: 'cancelled',
                    remark: '管理员取消'
                }, function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('取消失败：' + response.message);
                    }
                });
            }
        }
        
        function exportOrders() {
            const params = new URLSearchParams(window.location.search);
            window.location.href = '/admin/orders/export?' + params.toString();
        }
        
        function batchProcess() {
            const selectedOrders = [];
            $('.order-checkbox:checked').each(function() {
                selectedOrders.push($(this).val());
            });
            
            if (selectedOrders.length === 0) {
                alert('请选择要处理的订单');
                return;
            }
            
            alert('批量处理功能开发中...');
        }
        
        function getOrderStatusText(status) {
            const statusMap = {
                'pending': '待付款',
                'paid': '已付款',
                'shipped': '已发货',
                'completed': '已完成',
                'cancelled': '已取消'
            };
            return statusMap[status] || status;
        }
    </script>
</body>
</html>