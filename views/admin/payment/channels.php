<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>支付通道管理 - 私域商城后台</title>
    <link href="https://cdn.bootcdn.net/ajax/libs/twitter-bootstrap/4.6.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.bootcdn.net/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        .channel-card {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s;
        }
        .channel-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .channel-card.default {
            border-color: #28a745;
            background-color: #f0fff4;
        }
        .channel-card.inactive {
            opacity: 0.6;
            background-color: #f8f9fa;
        }
        .badge-type {
            font-size: 12px;
            padding: 4px 10px;
        }
        .stats-box {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
        }
        .btn-action {
            margin: 2px;
        }
    </style>
</head>
<body>
    <div class="container-fluid mt-4">
        <div class="row mb-4">
            <div class="col-md-6">
                <h2><i class="fas fa-credit-card"></i> 支付通道管理</h2>
            </div>
            <div class="col-md-6 text-right">
                <button class="btn btn-primary" onclick="createChannel()">
                    <i class="fas fa-plus"></i> 添加支付通道
                </button>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-12">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-primary <?= empty($type) ? 'active' : '' ?>" 
                            onclick="filterChannels('')">
                        全部
                    </button>
                    <button type="button" class="btn btn-outline-success <?= $type == 'wechat' ? 'active' : '' ?>" 
                            onclick="filterChannels('wechat')">
                        <i class="fab fa-weixin"></i> 微信支付
                    </button>
                    <button type="button" class="btn btn-outline-primary <?= $type == 'alipay' ? 'active' : '' ?>" 
                            onclick="filterChannels('alipay')">
                        <i class="fab fa-alipay"></i> 支付宝
                    </button>
                </div>
            </div>
        </div>

        <div class="row">
            <?php if (empty($channels)): ?>
                <div class="col-md-12">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 暂无支付通道，请先添加支付通道配置。
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($channels as $channel): ?>
                    <div class="col-md-6">
                        <div class="channel-card <?= $channel['is_default'] ? 'default' : '' ?> <?= !$channel['is_active'] ? 'inactive' : '' ?>">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h5>
                                        <?= htmlspecialchars($channel['name']) ?>
                                        <?php if ($channel['is_default']): ?>
                                            <span class="badge badge-success">默认</span>
                                        <?php endif; ?>
                                        <?php if (!$channel['is_active']): ?>
                                            <span class="badge badge-secondary">已禁用</span>
                                        <?php endif; ?>
                                    </h5>
                                    <p class="text-muted mb-2">
                                        <?php if ($channel['type'] == 'wechat'): ?>
                                            <i class="fab fa-weixin text-success"></i> 微信支付
                                        <?php else: ?>
                                            <i class="fab fa-alipay text-primary"></i> 支付宝
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <div>
                                    <span class="badge badge-<?= $channel['is_active'] ? 'success' : 'secondary' ?>">
                                        <?= $channel['is_active'] ? '启用中' : '已禁用' ?>
                                    </span>
                                </div>
                            </div>

                            <hr>

                            <div class="row">
                                <div class="col-md-6">
                                    <small class="text-muted">APPID</small>
                                    <p class="mb-1"><code><?= htmlspecialchars($channel['app_id']) ?></code></p>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted">商户号</small>
                                    <p class="mb-1"><code><?= htmlspecialchars($channel['mch_id']) ?></code></p>
                                </div>
                            </div>

                            <?php if (!empty($channel['remark'])): ?>
                                <p class="text-muted mt-2 mb-2">
                                    <i class="fas fa-comment"></i> <?= htmlspecialchars($channel['remark']) ?>
                                </p>
                            <?php endif; ?>

                            <?php if (isset($channel['stats'])): ?>
                                <div class="stats-box">
                                    <div class="row text-center">
                                        <div class="col-4">
                                            <strong><?= $channel['stats']['total_orders'] ?? 0 ?></strong>
                                            <br><small class="text-muted">订单数</small>
                                        </div>
                                        <div class="col-4">
                                            <strong><?= $channel['stats']['paid_orders'] ?? 0 ?></strong>
                                            <br><small class="text-muted">已支付</small>
                                        </div>
                                        <div class="col-4">
                                            <strong>¥<?= number_format($channel['stats']['total_amount'] ?? 0, 2) ?></strong>
                                            <br><small class="text-muted">总金额</small>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="mt-3">
                                <button class="btn btn-sm btn-primary btn-action" onclick="editChannel(<?= $channel['id'] ?>)">
                                    <i class="fas fa-edit"></i> 编辑
                                </button>
                                
                                <?php if (!$channel['is_default']): ?>
                                    <button class="btn btn-sm btn-success btn-action" onclick="setDefault(<?= $channel['id'] ?>)">
                                        <i class="fas fa-star"></i> 设为默认
                                    </button>
                                <?php endif; ?>
                                
                                <button class="btn btn-sm btn-<?= $channel['is_active'] ? 'warning' : 'info' ?> btn-action" 
                                        onclick="toggleActive(<?= $channel['id'] ?>)">
                                    <i class="fas fa-power-off"></i> <?= $channel['is_active'] ? '禁用' : '启用' ?>
                                </button>
                                
                                <button class="btn btn-sm btn-secondary btn-action" onclick="testConnection(<?= $channel['id'] ?>)">
                                    <i class="fas fa-plug"></i> 测试
                                </button>
                                
                                <button class="btn btn-sm btn-danger btn-action" onclick="deleteChannel(<?= $channel['id'] ?>)">
                                    <i class="fas fa-trash"></i> 删除
                                </button>
                            </div>

                            <small class="text-muted d-block mt-2">
                                创建时间: <?= $channel['created_at'] ?>
                            </small>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.bootcdn.net/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.bootcdn.net/ajax/libs/twitter-bootstrap/4.6.0/js/bootstrap.bundle.min.js"></script>
    <script>
        function filterChannels(type) {
            window.location.href = '/admin/payment/channels' + (type ? '?type=' + type : '');
        }

        function createChannel() {
            window.location.href = '/admin/payment/channels/create';
        }

        function editChannel(id) {
            window.location.href = '/admin/payment/channels/edit?id=' + id;
        }

        function setDefault(id) {
            if (!confirm('确定设置为默认支付通道吗？')) return;
            
            $.post('/admin/payment/channels/set-default', {id: id}, function(res) {
                if (res.code === 200) {
                    alert('设置成功');
                    location.reload();
                } else {
                    alert(res.message || '设置失败');
                }
            });
        }

        function toggleActive(id) {
            $.post('/admin/payment/channels/toggle-active', {id: id}, function(res) {
                if (res.code === 200) {
                    location.reload();
                } else {
                    alert(res.message || '操作失败');
                }
            });
        }

        function testConnection(id) {
            $.post('/admin/payment/channels/test', {id: id}, function(res) {
                if (res.code === 200) {
                    alert('测试成功！\n通道名称: ' + res.data.channel_name + '\n测试时间: ' + res.data.test_time);
                } else {
                    alert('测试失败: ' + (res.message || '未知错误'));
                }
            });
        }

        function deleteChannel(id) {
            if (!confirm('确定删除此支付通道吗？删除后无法恢复！')) return;
            
            $.post('/admin/payment/channels/delete', {id: id}, function(res) {
                if (res.code === 200) {
                    alert('删除成功');
                    location.reload();
                } else {
                    alert(res.message || '删除失败');
                }
            });
        }
    </script>
</body>
</html>
