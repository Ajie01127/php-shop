<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>营销活动 - 私域商城后台</title>
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
        .marketing-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            transition: transform 0.2s;
        }
        .marketing-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        .status-active { background: #d4edda; color: #155724; }
        .status-inactive { background: #f8d7da; color: #721c24; }
        .status-pending { background: #fff3cd; color: #856404; }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stats-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stats-card .number {
            font-size: 2rem;
            font-weight: bold;
            color: #2c3e50;
        }
        .stats-card .label {
            color: #7f8c8d;
            margin-top: 5px;
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
                <a href="/admin/users" class="nav-link">
                    <i class="fas fa-users"></i>
                    用户管理
                </a>
            </div>
            
            <div class="nav-item">
                <a href="/admin/marketing" class="nav-link active">
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
                    <h4 class="mb-0">营销活动</h4>
                    <small class="text-muted">管理营销活动和促销策略</small>
                </div>
                <div class="col-md-6 text-right">
                    <a href="/admin/marketing/create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> 创建活动
                    </a>
                    <button type="button" class="btn btn-outline-success" onclick="exportMarketing()">
                        <i class="fas fa-download"></i> 导出数据
                    </button>
                </div>
            </div>
        </div>
        
        <!-- 统计数据 -->
        <div class="stats-grid">
            <div class="stats-card">
                <div class="number"><?= $stats['total_activities'] ?></div>
                <div class="label">活动总数</div>
            </div>
            <div class="stats-card">
                <div class="number"><?= $stats['active_activities'] ?></div>
                <div class="label">进行中活动</div>
            </div>
            <div class="stats-card">
                <div class="number">¥<?= number_format($stats['total_sales'], 2) ?></div>
                <div class="label">营销销售额</div>
            </div>
            <div class="stats-card">
                <div class="number"><?= $stats['total_orders'] ?></div>
                <div class="label">营销订单数</div>
            </div>
        </div>
        
        <!-- 活动筛选 -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <input type="text" name="keyword" class="form-control" placeholder="搜索活动名称" value="<?= htmlspecialchars(get('keyword')) ?>">
                    </div>
                    <div class="col-md-2">
                        <select name="type" class="form-control">
                            <option value="">全部类型</option>
                            <option value="discount" <?= get('type') == 'discount' ? 'selected' : '' ?>>折扣活动</option>
                            <option value="coupon" <?= get('type') == 'coupon' ? 'selected' : '' ?>>优惠券</option>
                            <option value="flash_sale" <?= get('type') == 'flash_sale' ? 'selected' : '' ?>>限时秒杀</option>
                            <option value="bundle" <?= get('type') == 'bundle' ? 'selected' : '' ?>>套餐活动</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-control">
                            <option value="">全部状态</option>
                            <option value="active" <?= get('status') == 'active' ? 'selected' : '' ?>>进行中</option>
                            <option value="pending" <?= get('status') == 'pending' ? 'selected' : '' ?>>未开始</option>
                            <option value="ended" <?= get('status') == 'ended' ? 'selected' : '' ?>>已结束</option>
                            <option value="cancelled" <?= get('status') == 'cancelled' ? 'selected' : '' ?>>已取消</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="date" class="form-control" value="<?= htmlspecialchars(get('date')) ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> 搜索
                        </button>
                        <a href="/admin/marketing" class="btn btn-outline-secondary">重置</a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- 活动列表 -->
        <div class="row">
            <?php foreach ($activities as $activity): ?>
            <div class="col-md-6 col-lg-4">
                <div class="marketing-card">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h6 class="mb-1"><?= htmlspecialchars($activity['title']) ?></h6>
                            <span class="badge badge-<?= getActivityTypeBadge($activity['type']) ?>">
                                <?= getActivityTypeName($activity['type']) ?>
                            </span>
                        </div>
                        <span class="status-badge status-<?= getActivityStatus($activity) ?>">
                            <?= getActivityStatusText($activity) ?>
                        </span>
                    </div>
                    
                    <p class="text-muted small mb-3"><?= htmlspecialchars($activity['description']) ?></p>
                    
                    <div class="row text-center mb-3">
                        <div class="col-6">
                            <small class="text-muted">开始时间</small>
                            <div><?= $activity['start_time'] ?></div>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">结束时间</small>
                            <div><?= $activity['end_time'] ?></div>
                        </div>
                    </div>
                    
                    <div class="row text-center mb-3">
                        <div class="col-6">
                            <small class="text-muted">参与人数</small>
                            <div class="font-weight-bold"><?= $activity['participant_count'] ?></div>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">销售额</small>
                            <div class="font-weight-bold text-danger">¥<?= number_format($activity['sales_amount'], 2) ?></div>
                        </div>
                    </div>
                    
                    <div class="btn-group w-100" role="group">
                        <a href="/admin/marketing/<?= $activity['id'] ?>" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-eye"></i> 查看
                        </a>
                        <a href="/admin/marketing/<?= $activity['id'] ?>/edit" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-edit"></i> 编辑
                        </a>
                        <button type="button" class="btn btn-outline-warning btn-sm" onclick="toggleActivityStatus(<?= $activity['id'] ?>, <?= $activity['status'] == 1 ? 0 : 1 ?>)">
                            <i class="fas fa-<?= $activity['status'] == 1 ? 'pause' : 'play' ?>"></i>
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteActivity(<?= $activity['id'] ?>)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- 分页 -->
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <?= generatePagination($pagination) ?>
            </ul>
        </nav>
    </div>
    
    <script src="https://cdn.bootcdn.net/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.bootcdn.net/ajax/libs/twitter-bootstrap/4.6.0/js/bootstrap.min.js"></script>
    
    <script>
        function toggleActivityStatus(activityId, newStatus) {
            const action = newStatus == 1 ? '启用' : '禁用';
            if (confirm('确定要' + action + '该活动吗？')) {
                $.post('/admin/marketing/' + activityId + '/toggle', {status: newStatus}, function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('操作失败：' + response.message);
                    }
                });
            }
        }
        
        function deleteActivity(activityId) {
            if (confirm('确定要删除该活动吗？此操作不可恢复！')) {
                $.post('/admin/marketing/' + activityId + '/delete', function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('删除失败：' + response.message);
                    }
                });
            }
        }
        
        function exportMarketing() {
            const params = new URLSearchParams(window.location.search);
            window.location.href = '/admin/marketing/export?' + params.toString();
        }
        
        function getActivityTypeBadge(type) {
            const badges = {
                'discount': 'primary',
                'coupon': 'success',
                'flash_sale': 'danger',
                'bundle': 'info'
            };
            return badges[type] || 'secondary';
        }
        
        function getActivityTypeName(type) {
            const types = {
                'discount': '折扣活动',
                'coupon': '优惠券',
                'flash_sale': '限时秒杀',
                'bundle': '套餐活动'
            };
            return types[type] || type;
        }
        
        function getActivityStatus(activity) {
            const now = new Date();
            const start = new Date(activity.start_time);
            const end = new Date(activity.end_time);
            
            if (activity.status == 0) return 'inactive';
            if (now < start) return 'pending';
            if (now > end) return 'ended';
            return 'active';
        }
        
        function getActivityStatusText(activity) {
            const status = getActivityStatus(activity);
            const texts = {
                'active': '进行中',
                'pending': '未开始',
                'ended': '已结束',
                'inactive': '已禁用'
            };
            return texts[status] || status;
        }
    </script>
</body>
</html>