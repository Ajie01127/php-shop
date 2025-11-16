<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="container">
    <h2 class="page-title">确认订单</h2>
    
    <div class="checkout-container">
        <!-- 收货地址 -->
        <div class="section">
            <h3 class="section-title">收货地址</h3>
            <?php if ($address): ?>
            <div class="address-card">
                <div class="address-info">
                    <strong><?= e($address['name']) ?></strong>
                    <span><?= e($address['phone']) ?></span>
                </div>
                <div class="address-detail">
                    <?= e($address['province']) ?> 
                    <?= e($address['city']) ?> 
                    <?= e($address['district']) ?> 
                    <?= e($address['detail']) ?>
                </div>
            </div>
            <?php else: ?>
            <div class="empty-address">
                <p>暂无收货地址，请先添加地址</p>
                <button class="btn btn-primary" onclick="alert('添加地址功能待实现')">添加地址</button>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- 商品列表 -->
        <div class="section">
            <h3 class="section-title">商品清单</h3>
            <div class="cart-items">
                <?php foreach ($cartItems as $item): ?>
                <div class="cart-item">
                    <?php 
                    $images = json_decode($item['images'], true);
                    $image = $images[0] ?? '';
                    ?>
                    <img src="<?= e($image) ?>" alt="<?= e($item['name']) ?>">
                    <div class="item-info">
                        <div class="item-name"><?= e($item['name']) ?></div>
                        <div class="item-price"><?= formatPrice($item['price']) ?></div>
                    </div>
                    <div class="item-quantity">x <?= $item['quantity'] ?></div>
                    <div class="item-total"><?= formatPrice($item['price'] * $item['quantity']) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- 订单信息 -->
        <div class="section">
            <h3 class="section-title">订单备注</h3>
            <form id="orderForm" method="POST" action="/order/create">
                <input type="hidden" name="address_id" value="<?= $address['id'] ?? '' ?>">
                
                <div class="form-group">
                    <textarea name="remark" placeholder="选填：给商家留言" rows="3"></textarea>
                </div>
                
                <div class="order-summary">
                    <div class="summary-row">
                        <span>商品总额:</span>
                        <span><?= formatPrice($totalAmount) ?></span>
                    </div>
                    <div class="summary-row">
                        <span>运费:</span>
                        <span>¥0.00</span>
                    </div>
                    <div class="summary-row total">
                        <strong>应付金额:</strong>
                        <strong class="amount"><?= formatPrice($totalAmount) ?></strong>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block btn-large">
                    <i class="fab fa-weixin"></i> 提交订单并支付
                </button>
            </form>
        </div>
    </div>
</div>

<style>
.checkout-container {
    max-width: 900px;
    margin: 0 auto;
}

.section {
    background-color: #fff;
    padding: 25px;
    border-radius: 8px;
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.section-title {
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 1px solid #f0f0f0;
}

.address-card {
    padding: 15px;
    background-color: #f5f5f5;
    border-radius: 8px;
    border-left: 3px solid #1890ff;
}

.address-info {
    margin-bottom: 10px;
}

.address-info strong {
    margin-right: 15px;
}

.address-detail {
    color: #666;
}

.empty-address {
    text-align: center;
    padding: 30px;
    color: #999;
}

.cart-items {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.cart-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    background-color: #fafafa;
    border-radius: 8px;
}

.cart-item img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 4px;
}

.cart-item .item-info {
    flex: 1;
}

.cart-item .item-name {
    margin-bottom: 8px;
    color: #333;
}

.cart-item .item-price {
    color: #999;
}

.cart-item .item-quantity {
    color: #666;
}

.cart-item .item-total {
    color: #ff4d4f;
    font-weight: bold;
    min-width: 100px;
    text-align: right;
}

.form-group textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #d9d9d9;
    border-radius: 4px;
    resize: vertical;
    font-family: inherit;
}

.order-summary {
    margin: 30px 0;
    padding: 20px;
    background-color: #fafafa;
    border-radius: 8px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 15px;
    color: #666;
}

.summary-row.total {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #e0e0e0;
    font-size: 18px;
}

.summary-row .amount {
    color: #ff4d4f;
    font-size: 24px;
}

.btn-large {
    padding: 15px;
    font-size: 18px;
}
</style>

<script>
document.getElementById('orderForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('/order/create', {
        method: 'POST',
        body: new URLSearchParams(formData)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // 跳转到支付页面
            window.location.href = data.data.redirect;
        } else {
            alert(data.message);
        }
    })
    .catch(err => {
        console.error('Error:', err);
        alert('提交失败，请重试');
    });
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
