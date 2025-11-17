<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>控制台 - 私域商城后台</title>
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
        .stats-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            border-left: 4px solid #3498db;
        }
        .stats-card.success {
            border-left-color: #27ae60;
        }
        .stats-card.warning {
            border-left-color: #f39c12;
        }
        .stats-card.danger {
            border-left-color: #e74c3c;
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
        .chart-container {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
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
                <a href="/admin/dashboard" class="nav-link active">
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
                    <h4 class="mb-0">控制台</h4>
                    <small class="text-muted">欢迎回来，管理员</small>
                </div>
                <div class="col-md-6 text-right">
                    <span class="text-muted"><?= date('Y-m-d H:i:s') ?></span>
                </div>
            </div>
        </div>
        
        <!-- 统计卡片 -->
        <div class="row">
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="number"><?= $totalStats['total_users'] ?></div>
                    <div class="label">总用户数</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card success">
                    <div class="number"><?= $totalStats['total_products'] ?></div>
                    <div class="label">商品数量</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card warning">
                    <div class="number"><?= $totalStats['total_orders'] ?></div>
                    <div class="label">订单总数</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card danger">
                    <div class="number">¥<?= number_format($totalStats['total_sales'], 2) ?></div>
                    <div class="label">销售总额</div>
                </div>
            </div>
        </div>
        
        <!-- 今日统计 -->
        <div class="row">
            <div class="col-md-6">
                <div class="chart-container">
                    <h5>今日数据</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>今日订单：</strong><?= $todayStats['total_orders'] ?>单</p>
                            <p><strong>今日销售：</strong>¥<?= number_format($todayStats['total_sales'], 2) ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>今日用户：</strong><?= $todayStats['new_users'] ?>人</p>
                            <p><strong>转化率：</strong><?= $todayStats['conversion_rate'] ?>%</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="chart-container">
                    <h5>待处理事项</h5>
                    <ul class="list-unstyled">
                        <?php foreach ($pendingOrders as $order): ?>
                        <li class="mb-2">
                            <i class="fas fa-clock text-warning"></i>
                            订单 <?= $order['order_no'] ?> 待处理
                            <small class="text-muted d-block"><?= $order['created_at'] ?></small>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- 近7天订单趋势 -->
        <div class="row">
            <div class="col-12">
                <div class="chart-container">
                    <h5>近7天订单趋势</h5>
                    <canvas id="orderTrendChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.bootcdn.net/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.bootcdn.net/ajax/libs/twitter-bootstrap/4.6.0/js/bootstrap.min.js"></script>
    <script src="https://cdn.bootcdn.net/ajax/libs/Chart.js/3.7.1/chart.min.js"></script>
    <script>
        // 订单趋势图表
        const ctx = document.getElementById('orderTrendChart').getContext('2d');
        const orderTrend = <?= json_encode($orderTrend) ?>;
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: orderTrend.map(item => item.date),
                datasets: [{
                    label: '订单数量',
                    data: orderTrend.map(item => item.orders),
                    borderColor: '#3498db',
                    backgroundColor: 'rgba(52, 152, 219, 0.1)',
                    tension: 0.4
                }, {
                    label: '销售额',
                    data: orderTrend.map(item => item.sales),
                    borderColor: '#27ae60',
                    backgroundColor: 'rgba(39, 174, 96, 0.1)',
                    tension: 0.4,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        grid: {
                            drawOnChartArea: false,
                        },
                    }
                }
            }
        });
    </script>
</body>
</html>