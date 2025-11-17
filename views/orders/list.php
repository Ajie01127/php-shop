<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="container">
    <h2 class="page-title">我的订单</h2>
    
    <div class="order-tabs">
        <a href="/orders" class="tab <?= !$status ? 'active' : '' ?>">全部订单</a>
        <a href="/orders?status=pending" class="tab <?= $status === 'pending' ? 'active' : '' ?>">待支付</a>
        <a href="/orders?status=paid" class="tab <?= $status === 'paid' ? 'active' : '' ?>">待发货</a>
        <a href="/orders?status=shipped" class="tab <?= $status === 'shipped' ? 'active' : '' ?>">待收货</a>
        <a href="/orders?status=completed" class="tab <?= $status === 'completed' ? 'active' : '' ?>">已完成</a>
    </div>
    
    <div class="orders-list">
        <?php if (empty($orders)): ?>
            <div class="empty-state">
                <i class="fas fa-inbox fa-3x"></i>
                <p>暂无订单</p>
            </div>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
            <div class="order-card">
                <div class="order-header">
                    <span class="order-no">订单号: <?= e($order['order_no']) ?></span>
                    <span class="order-time"><?= formatDate($order['created_at']) ?></span>
                    <span class="order-status status-<?= $order['status'] ?>">
                        <?php
                        $statusMap = [
                            'pending' => '待支付',
                            'paid' => '待发货',
                            'shipped' => '待收货',
                            'completed' => '已完成',
                            'cancelled' => '已取消',
                            'refunding' => '退款中',
                            'refunded' => '已退款',
                        ];
                        echo $statusMap[$order['status']] ?? $order['status'];
                        ?>
                    </span>
                </div>
                
                <div class="order-items">
                    <?php foreach ($order['items'] as $item): ?>
                    <div class="order-item">
                        <img src="<?= e($item['product_image']) ?>" alt="<?= e($item['product_name']) ?>">
                        <div class="item-info">
                            <div class="item-name"><?= e($item['product_name']) ?></div>
                            <div class="item-price"><?= formatPrice($item['price']) ?> x <?= $item['quantity'] ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="order-footer">
                    <div class="order-total">
                        合计: <span class="amount"><?= formatPrice($order['total_amount']) ?></span>
                    </div>
                    <div class="order-actions">
                        <?php if ($order['status'] === 'pending'): ?>
                            <a href="/payment/pay?order_id=<?= (int)$order['id'] ?>" class="btn btn-primary">
                                <i class="fab fa-weixin"></i> 去支付
                            </a>
                            <button class="btn btn-default" onclick="cancelOrder(<?= (int)$order['id'] ?>)">取消订单</button>
                        <?php endif; ?>
                        
                        <?php if ($order['status'] === 'shipped'): ?>
                            <button class="btn btn-primary" onclick="confirmReceive(<?= (int)$order['id'] ?>)">确认收货</button>
                        <?php endif; ?>
                        
                        <?php if ($order['status'] === 'paid'): ?>
                            <button class="btn btn-default" onclick="applyRefund(<?= (int)$order['id'] ?>)">申请退款</button>
                        <?php endif; ?>
                        
                        <a href="/order/<?= (int)$order['id'] ?>" class="btn btn-default">查看详情</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<style>
.page-title {
    margin-bottom: 30px;
}

.order-tabs {
    display: flex;
    gap: 20px;
    margin-bottom: 30px;
    border-bottom: 2px solid #f0f0f0;
}

.tab {
    padding: 10px 20px;
    color: #666;
    text-decoration: none;
    border-bottom: 2px solid transparent;
    margin-bottom: -2px;
    transition: all 0.3s;
}

.tab:hover,
.tab.active {
    color: #1890ff;
    border-bottom-color: #1890ff;
}

.orders-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.order-card {
    background-color: #fff;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 15px;
    border-bottom: 1px solid #f0f0f0;
    margin-bottom: 15px;
}

.order-no {
    font-weight: 500;
    color: #333;
}

.order-time {
    color: #999;
    font-size: 14px;
}

.order-status {
    padding: 4px 12px;
    border-radius: 4px;
    font-size: 14px;
}

.status-pending {
    background-color: #fff7e6;
    color: #fa8c16;
}

.status-paid {
    background-color: #e6f7ff;
    color: #1890ff;
}

.status-shipped {
    background-color: #f6ffed;
    color: #52c41a;
}

.status-completed {
    background-color: #f0f0f0;
    color: #666;
}

.status-cancelled,
.status-refunded {
    background-color: #fff2f0;
    color: #ff4d4f;
}

.order-items {
    display: flex;
    flex-direction: column;
    gap: 15px;
    margin-bottom: 15px;
}

.order-item {
    display: flex;
    gap: 15px;
}

.order-item img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 4px;
}

.item-info {
    flex: 1;
}

.item-name {
    color: #333;
    margin-bottom: 8px;
}

.item-price {
    color: #999;
    font-size: 14px;
}

.order-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 15px;
    border-top: 1px solid #f0f0f0;
}

.order-total {
    font-size: 16px;
    color: #333;
}

.order-total .amount {
    color: #ff4d4f;
    font-size: 20px;
    font-weight: bold;
}

.order-actions {
    display: flex;
    gap: 10px;
}

.empty-state {
    text-align: center;
    padding: 60px 0;
    color: #999;
}

.empty-state i {
    margin-bottom: 20px;
}
</style>

<script>
function cancelOrder(orderId) {
    if (!confirm('确定要取消订单吗？')) {
        return;
    }
    
    // 实现取消订单逻辑
    alert('订单取消功能待实现');
}

function confirmReceive(orderId) {
    if (!confirm('确认已收到货物吗？')) {
        return;
    }
    
    // 实现确认收货逻辑
    alert('确认收货功能待实现');
}

function applyRefund(orderId) {
    const reason = prompt('请输入退款原因:');
    if (!reason) {
        return;
    }
    
    fetch('/payment/refund', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `order_id=${orderId}&reason=${encodeURIComponent(reason)}`
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message);
        if (data.success) {
            location.reload();
        }
    })
    .catch(err => {
        console.error('Error:', err);
        alert('操作失败，请重试');
    });
}
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
