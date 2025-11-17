<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用户管理 - 私域商城后台</title>
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
        .user-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            transition: transform 0.2s;
        }
        .user-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }
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
                <a href="/admin/orders" class="nav-link">
                    <i class="fas fa-shopping-cart"></i>
                    订单管理
                </a>
            </div>
            
            <div class="nav-item">
                <a href="/admin/users" class="nav-link active">
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
                    <h4 class="mb-0">用户管理</h4>
                    <small class="text-muted">管理系统用户信息</small>
                </div>
                <div class="col-md-6 text-right">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="exportUsers()">
                            <i class="fas fa-download"></i> 导出用户
                        </button>
                        <button type="button" class="btn btn-outline-success btn-sm" onclick="showImportModal()">
                            <i class="fas fa-upload"></i> 导入用户
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 搜索筛选 -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="keyword" class="form-control" placeholder="搜索用户名、邮箱、手机号" value="<?= htmlspecialchars(get('keyword')) ?>">
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-control">
                            <option value="">全部状态</option>
                            <option value="1" <?= get('status') == '1' ? 'selected' : '' ?>>正常</option>
                            <option value="0" <?= get('status') == '0' ? 'selected' : '' ?>>禁用</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="member_level" class="form-control">
                            <option value="">全部等级</option>
                            <option value="0" <?= get('member_level') == '0' ? 'selected' : '' ?>>普通会员</option>
                            <option value="1" <?= get('member_level') == '1' ? 'selected' : '' ?>>银卡会员</option>
                            <option value="2" <?= get('member_level') == '2' ? 'selected' : '' ?>>金卡会员</option>
                            <option value="3" <?= get('member_level') == '3' ? 'selected' : '' ?>>钻石会员</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="register_time" class="form-control">
                            <option value="">注册时间</option>
                            <option value="today" <?= get('register_time') == 'today' ? 'selected' : '' ?>>今天</option>
                            <option value="week" <?= get('register_time') == 'week' ? 'selected' : '' ?>>本周</option>
                            <option value="month" <?= get('register_time') == 'month' ? 'selected' : '' ?>>本月</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> 搜索
                        </button>
                        <a href="/admin/users" class="btn btn-outline-secondary">重置</a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- 用户列表 -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>
                                    <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                                </th>
                                <th>ID</th>
                                <th>用户名</th>
                                <th>邮箱</th>
                                <th>手机号</th>
                                <th>会员等级</th>
                                <th>状态</th>
                                <th>注册时间</th>
                                <th>最后登录</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <input type="checkbox" name="user_ids[]" value="<?= $user['id'] ?>" class="user-checkbox">
                                </td>
                                <td><?= $user['id'] ?></td>
                                <td>
                                    <img src="<?= $user['avatar'] ?: '/images/default-avatar.png' ?>" 
                                         class="rounded-circle" width="32" height="32" style="object-fit: cover;">
                                    <?= htmlspecialchars($user['username']) ?>
                                </td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td><?= htmlspecialchars($user['phone']) ?></td>
                                <td>
                                    <span class="badge badge-<?= getMemberLevelBadge($user['member_level']) ?>">
                                        <?= getMemberLevelName($user['member_level']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge <?= $user['status'] == 1 ? 'status-active' : 'status-inactive' ?>">
                                        <?= $user['status'] == 1 ? '正常' : '禁用' ?>
                                    </span>
                                </td>
                                <td><?= $user['created_at'] ?></td>
                                <td><?= $user['last_login_at'] ?: '-' ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-primary" onclick="viewUser(<?= $user['id'] ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-info" onclick="editUser(<?= $user['id'] ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-warning" onclick="toggleUserStatus(<?= $user['id'] ?>, <?= $user['status'] == 1 ? 0 : 1 ?>)">
                                            <i class="fas fa-<?= $user['status'] == 1 ? 'ban' : 'check' ?>"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger" onclick="deleteUser(<?= $user['id'] ?>)">
                                            <i class="fas fa-trash"></i>
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
    </div>
    
    <!-- 用户详情模态框 -->
    <div class="modal fade" id="userModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">用户详情</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="userModalBody">
                    <!-- 内容通过AJAX加载 -->
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.bootcdn.net/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.bootcdn.net/ajax/libs/twitter-bootstrap/4.6.0/js/bootstrap.min.js"></script>
    
    <script>
        function toggleSelectAll() {
            const checkboxes = document.querySelectorAll('.user-checkbox');
            const selectAll = document.getElementById('selectAll');
            
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAll.checked;
            });
        }
        
        function viewUser(userId) {
            $.get('/admin/users/' + userId, function(data) {
                $('#userModalBody').html(data);
                $('#userModal').modal('show');
            });
        }
        
        function editUser(userId) {
            window.location.href = '/admin/users/' + userId + '/edit';
        }
        
        function toggleUserStatus(userId, newStatus) {
            if (confirm('确定要' + (newStatus == 1 ? '启用' : '禁用') + '该用户吗？')) {
                $.post('/admin/users/' + userId + '/update', {status: newStatus}, function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('操作失败：' + response.message);
                    }
                });
            }
        }
        
        function deleteUser(userId) {
            if (confirm('确定要删除该用户吗？此操作不可恢复！')) {
                $.post('/admin/users/' + userId + '/delete', function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('删除失败：' + response.message);
                    }
                });
            }
        }
        
        function exportUsers() {
            const params = new URLSearchParams(window.location.search);
            window.location.href = '/admin/users/export?' + params.toString();
        }
        
        function showImportModal() {
            // 显示导入模态框
            alert('导入功能开发中...');
        }
        
        function getMemberLevelName(level) {
            const levels = ['普通会员', '银卡会员', '金卡会员', '钻石会员'];
            return levels[level] || '未知';
        }
        
        function getMemberLevelBadge(level) {
            const badges = ['secondary', 'info', 'warning', 'success'];
            return badges[level] || 'secondary';
        }
    </script>
</body>
</html>