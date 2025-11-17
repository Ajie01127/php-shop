<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>商品管理 - 私域商城后台</title>
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
        .product-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            transition: transform 0.2s;
        }
        .product-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
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
                <a href="/admin/products" class="nav-link active">
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
                    <h4 class="mb-0">商品管理</h4>
                    <small class="text-muted">管理商城商品信息</small>
                </div>
                <div class="col-md-6 text-right">
                    <a href="/admin/products/create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> 添加商品
                    </a>
                    <button type="button" class="btn btn-outline-success" onclick="exportProducts()">
                        <i class="fas fa-download"></i> 导出商品
                    </button>
                </div>
            </div>
        </div>
        
        <!-- 搜索筛选 -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <input type="text" name="keyword" class="form-control" placeholder="搜索商品名称、SKU" value="<?= htmlspecialchars(get('keyword')) ?>">
                    </div>
                    <div class="col-md-2">
                        <select name="category_id" class="form-control">
                            <option value="">全部分类</option>
                            <!-- 动态加载分类 -->
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-control">
                            <option value="">全部状态</option>
                            <option value="1" <?= get('status') == '1' ? 'selected' : '' ?>>上架</option>
                            <option value="0" <?= get('status') == '0' ? 'selected' : '' ?>>下架</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="stock_status" class="form-control">
                            <option value="">库存状态</option>
                            <option value="in_stock" <?= get('stock_status') == 'in_stock' ? 'selected' : '' ?>>有货</option>
                            <option value="low_stock" <?= get('stock_status') == 'low_stock' ? 'selected' : '' ?>>库存不足</option>
                            <option value="out_of_stock" <?= get('stock_status') == 'out_of_stock' ? 'selected' : '' ?>>缺货</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> 搜索
                        </button>
                        <a href="/admin/products" class="btn btn-outline-secondary">重置</a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- 商品列表 -->
        <div class="row">
            <?php foreach ($products as $product): ?>
            <div class="col-md-4 col-lg-3">
                <div class="product-card">
                    <div class="position-relative">
                        <img src="<?= $product['images'] ? json_decode($product['images'])[0] : '/images/default-product.png' ?>" 
                             class="product-image" alt="<?= htmlspecialchars($product['name']) ?>">
                        <span class="status-badge <?= $product['status'] == 1 ? 'status-active' : 'status-inactive' ?> position-absolute" style="top: 10px; right: 10px;">
                            <?= $product['status'] == 1 ? '上架' : '下架' ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <h6 class="card-title"><?= htmlspecialchars($product['name']) ?></h6>
                        <p class="text-muted small mb-2">SKU: <?= htmlspecialchars($product['sku']) ?></p>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-danger font-weight-bold">¥<?= number_format($product['price'], 2) ?></span>
                            <small class="text-muted">库存: <?= $product['stock'] ?></small>
                        </div>
                        <div class="btn-group w-100" role="group">
                            <a href="/admin/products/<?= $product['id'] ?>" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-eye"></i> 查看
                            </a>
                            <a href="/admin/products/<?= $product['id'] ?>/edit" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-edit"></i> 编辑
                            </a>
                            <button type="button" class="btn btn-outline-warning btn-sm" onclick="toggleProductStatus(<?= $product['id'] ?>, <?= $product['status'] == 1 ? 0 : 1 ?>)">
                                <i class="fas fa-<?= $product['status'] == 1 ? 'eye-slash' : 'eye' ?>"></i>
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteProduct(<?= $product['id'] ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
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
        function toggleProductStatus(productId, newStatus) {
            const action = newStatus == 1 ? '上架' : '下架';
            if (confirm('确定要' + action + '该商品吗？')) {
                $.post('/admin/products/' + productId + '/update', {status: newStatus}, function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('操作失败：' + response.message);
                    }
                });
            }
        }
        
        function deleteProduct(productId) {
            if (confirm('确定要删除该商品吗？此操作不可恢复！')) {
                $.post('/admin/products/' + productId + '/delete', function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('删除失败：' + response.message);
                    }
                });
            }
        }
        
        function exportProducts() {
            const params = new URLSearchParams(window.location.search);
            window.location.href = '/admin/products/export?' + params.toString();
        }
        
        // 加载分类数据
        $(document).ready(function() {
            $.get('/admin/categories', function(data) {
                const categorySelect = $('select[name="category_id"]');
                data.forEach(function(category) {
                    categorySelect.append('<option value="' + category.id + '">' + category.name + '</option>');
                });
            });
        });
    </script>
</body>
</html>