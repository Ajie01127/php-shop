<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>会员等级管理 - 私域商城后台</title>
    <link href="https://cdn.bootcdn.net/ajax/libs/twitter-bootstrap/4.6.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.bootcdn.net/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        .level-card {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 20px;
            transition: all 0.3s;
            position: relative;
        }
        .level-card:hover {
            box-shadow: 0 6px 16px rgba(0,0,0,0.15);
            transform: translateY(-2px);
        }
        .level-badge {
            position: absolute;
            top: -10px;
            right: 20px;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            color: white;
        }
        .level-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        .benefit-tag {
            display: inline-block;
            padding: 4px 12px;
            background: #f0f0f0;
            border-radius: 15px;
            margin: 3px;
            font-size: 12px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid mt-4">
        <div class="row mb-4">
            <div class="col-md-6">
                <h2><i class="fas fa-crown"></i> 会员等级管理</h2>
            </div>
            <div class="col-md-6 text-right">
                <button class="btn btn-success" onclick="location.href='/admin/member/levels/create'">
                    <i class="fas fa-plus"></i> 添加等级
                </button>
                <button class="btn btn-primary" onclick="updateAllLevels()">
                    <i class="fas fa-sync"></i> 更新所有用户等级
                </button>
            </div>
        </div>

        <div class="row">
            <?php foreach ($levels as $level): ?>
                <div class="col-md-4">
                    <div class="level-card" style="border-color: <?= $level['color'] ?>">
                        <div class="level-badge" style="background-color: <?= $level['color'] ?>">
                            LV.<?= $level['level'] ?>
                        </div>
                        
                        <div class="text-center">
                            <div class="level-icon" style="color: <?= $level['color'] ?>">
                                <i class="fas fa-crown"></i>
                            </div>
                            <h4><?= htmlspecialchars($level['level_name']) ?></h4>
                        </div>

                        <hr>

                        <div class="mb-3">
                            <p class="mb-2"><strong>升级条件:</strong></p>
                            <p class="text-muted mb-1">
                                <i class="fas fa-coins"></i> 
                                积分≥<?= number_format($level['min_points']) ?>
                            </p>
                            <p class="text-muted">
                                <i class="fas fa-yen-sign"></i> 
                                消费≥¥<?= number_format($level['min_amount'], 2) ?>
                            </p>
                        </div>

                        <div class="mb-3">
                            <p class="mb-2"><strong>会员折扣:</strong></p>
                            <h3 style="color: <?= $level['color'] ?>">
                                <?= ($level['discount'] * 10) ?>折
                            </h3>
                        </div>

                        <div class="mb-3">
                            <p class="mb-2"><strong>等级权益:</strong></p>
                            <?php foreach ($level['benefits'] as $benefit): ?>
                                <span class="benefit-tag"><?= htmlspecialchars($benefit) ?></span>
                            <?php endforeach; ?>
                        </div>

                        <?php if ($level['description']): ?>
                            <div class="mb-3">
                                <p class="text-muted small">
                                    <i class="fas fa-info-circle"></i> 
                                    <?= htmlspecialchars($level['description']) ?>
                                </p>
                            </div>
                        <?php endif; ?>

                        <div class="alert alert-info mb-3">
                            <i class="fas fa-users"></i> 
                            当前等级用户: <strong><?= $level['user_count'] ?></strong> 人
                        </div>

                        <div class="btn-group btn-group-sm w-100">
                            <button class="btn btn-primary" onclick="location.href='/admin/member/levels/edit?id=<?= $level['id'] ?>'">
                                <i class="fas fa-edit"></i> 编辑
                            </button>
                            <?php if ($level['level'] != 1): ?>
                                <button class="btn btn-danger" onclick="deleteLevel(<?= $level['id'] ?>)">
                                    <i class="fas fa-trash"></i> 删除
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://cdn.bootcdn.net/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        function deleteLevel(id) {
            if (!confirm('确定删除此等级吗？')) return;
            
            $.post('/admin/member/levels/delete', {id: id}, function(res) {
                if (res.code === 200) {
                    alert('删除成功');
                    location.reload();
                } else {
                    alert(res.message || '删除失败');
                }
            });
        }

        function updateAllLevels() {
            if (!confirm('确定更新所有用户的等级吗？此操作可能需要一些时间。')) return;
            
            $.post('/admin/member/update-all-levels', {}, function(res) {
                if (res.code === 200) {
                    alert(res.message);
                    location.reload();
                } else {
                    alert(res.message || '更新失败');
                }
            });
        }
    </script>
</body>
</html>
