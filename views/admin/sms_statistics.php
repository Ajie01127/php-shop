<?php
// 短信统计页面
include_once '../header.php';

// 检查权限
if (!isset($_SESSION['admin_id'])) {
    header('Location: /admin/login');
    exit;
}

// 获取统计信息从控制器传递
$statistics = $statistics ?? [
    'today_count' => 0,
    'month_count' => 0,
    'success_rate' => 0,
    'total_count' => 0,
    'today_success' => 0,
    'today_fail' => 0
];
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>短信统计 - 私域商城后台</title>
    <link rel="stylesheet" href="/public/admin.css">
    <style>
        .statistics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-value {
            font-size: 28px;
            font-weight: bold;
            color: #1890ff;
            margin-bottom: 5px;
        }
        .stat-label {
            font-size: 14px;
            color: #666;
        }
        .chart-container {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .data-table {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .table th, .table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .table th {
            background: #f5f5f5;
            font-weight: bold;
        }
        .success-badge {
            background: #52c41a;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
        }
        .fail-badge {
            background: #ff4d4f;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
        }
        .refresh-btn {
            background: #1890ff;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            margin-left: 10px;
        }
        .refresh-btn:hover {
            background: #40a9ff;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include_once '../admin_sidebar.php'; ?>
        
        <div class="admin-content">
            <div class="admin-header">
                <h1>短信统计</h1>
                <nav class="breadcrumb">
                    <a href="/admin">首页</a> &gt; 
                    <a href="/admin/sms/settings">短信设置</a> &gt; 
                    <span>短信统计</span>
                </nav>
            </div>

            <div class="admin-body">
                <!-- 统计卡片 -->
                <div class="statistics-grid">
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $statistics['today_count']; ?></div>
                        <div class="stat-label">今日发送量</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $statistics['month_count']; ?></div>
                        <div class="stat-label">本月发送量</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $statistics['success_rate']; ?>%</div>
                        <div class="stat-label">发送成功率</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $statistics['total_count']; ?></div>
                        <div class="stat-label">累计发送量</div>
                    </div>
                </div>

                <!-- 成功/失败统计 -->
                <div class="chart-container">
                    <h3>今日发送状态</h3>
                    <div style="display: flex; align-items: center; gap: 20px;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="color: #52c41a;">●</span>
                            <span>成功: <?php echo $statistics['today_success']; ?> 条</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="color: #ff4d4f;">●</span>
                            <span>失败: <?php echo $statistics['today_fail']; ?> 条</span>
                        </div>
                        <button class="refresh-btn" onclick="refreshData()">刷新数据</button>
                    </div>
                    
                    <!-- 简单的进度条表示 -->
                    <div style="margin-top: 15px; background: #f5f5f5; border-radius: 4px; height: 20px; overflow: hidden;">
                        <div style="height: 100%; background: #52c41a; width: <?php echo $statistics['today_success'] > 0 ? round($statistics['today_success'] / ($statistics['today_success'] + $statistics['today_fail']) * 100) : 0; ?>%"></div>
                    </div>
                </div>

                <!-- 发送记录表格 -->
                <div class="data-table">
                    <h3>最近发送记录</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>手机号</th>
                                <th>场景</th>
                                <th>模板ID</th>
                                <th>状态</th>
                                <th>发送时间</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- 示例数据，实际项目从数据库获取 -->
                            <tr>
                                <td>138****0000</td>
                                <td>用户注册</td>
                                <td>1234567</td>
                                <td><span class="success-badge">成功</span></td>
                                <td><?php echo date('Y-m-d H:i:s'); ?></td>
                            </tr>
                            <tr>
                                <td>139****1111</td>
                                <td>订单通知</td>
                                <td>1234568</td>
                                <td><span class="success-badge">成功</span></td>
                                <td><?php echo date('Y-m-d H:i:s', time() - 3600); ?></td>
                            </tr>
                            <tr>
                                <td>137****2222</td>
                                <td>登录验证</td>
                                <td>1234569</td>
                                <td><span class="fail-badge">失败</span></td>
                                <td><?php echo date('Y-m-d H:i:s', time() - 7200); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- 统计时间段选择 -->
                <div class="chart-container">
                    <h3>统计时间段</h3>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <select id="timeRange" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                            <option value="today">今日</option>
                            <option value="week">本周</option>
                            <option value="month" selected>本月</option>
                            <option value="year">今年</option>
                        </select>
                        <button class="refresh-btn" onclick="applyTimeRange()">应用筛选</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function refreshData() {
            // 这里可以添加AJAX请求刷新数据
            alert('数据刷新功能需要后端API支持');
        }

        function applyTimeRange() {
            const timeRange = document.getElementById('timeRange').value;
            // 这里可以添加时间范围筛选逻辑
            alert('时间段筛选功能需要后端API支持');
        }

        // 页面加载时获取统计信息
        document.addEventListener('DOMContentLoaded', function() {
            // 可以在这里添加实时数据获取逻辑
        });
    </script>
</body>
</html>