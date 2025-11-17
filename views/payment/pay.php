<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="container">
    <div class="payment-container">
        <div class="payment-card">
            <h2>订单支付</h2>
            
            <div class="order-info">
                <div class="info-row">
                    <span class="label">订单号:</span>
                    <span class="value"><?= e($order['order_no']) ?></span>
                </div>
                <div class="info-row">
                    <span class="label">订单金额:</span>
                    <span class="value price"><?= formatPrice($order['pay_amount']) ?></span>
                </div>
                <div class="info-row">
                    <span class="label">支付方式:</span>
                    <span class="value">
                        <div class="payment-methods">
                            <label class="payment-method active" data-method="wechat">
                                <input type="radio" name="payment_method" value="wechat" checked>
                                <i class="fab fa-weixin"></i> 微信支付
                            </label>
                            <label class="payment-method" data-method="alipay">
                                <input type="radio" name="payment_method" value="alipay">
                                <i class="fab fa-alipay"></i> 支付宝
                            </label>
                        </div>
                    </span>
                </div>
            </div>
            
            <!-- 微信支付区域 -->
            <div id="wechatPayArea" class="pay-area" style="display: none;">
                <div class="qr-code-container">
                    <img id="qrCodeImage" src="" alt="微信支付二维码">
                    <p class="qr-tips">请使用微信扫描二维码完成支付</p>
                    <div class="payment-status">
                        <div class="loading">
                            <i class="fas fa-spinner fa-spin"></i>
                            等待支付...
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 支付宝支付区域 -->
            <div id="alipayPayArea" class="pay-area" style="display: none;">
                <div class="payment-options">
                    <div class="option-item">
                        <img id="alipayQrCode" src="" alt="支付宝二维码" style="width: 300px; height: 300px; margin: 0 auto; display: block; border: 1px solid #ddd; border-radius: 8px; background-color: #fff;">
                        <p class="qr-tips">请使用支付宝扫描二维码完成支付</p>
                    </div>
                    <div class="option-item" style="margin-top: 20px;">
                        <button id="btnAlipayDirect" class="btn btn-primary btn-large">
                            <i class="fab fa-alipay"></i> 前往支付宝支付
                        </button>
                        <p class="qr-tips" style="margin-top: 10px;">或点击按钮直接跳转支付宝</p>
                    </div>
                </div>
                <div class="payment-status">
                    <div class="loading">
                        <i class="fas fa-spinner fa-spin"></i>
                        等待支付...
                    </div>
                </div>
            </div>
            
            <div class="payment-actions">
                <button id="btnPay" class="btn btn-primary btn-large">
                    <i class="fab fa-weixin"></i> 立即支付
                </button>
                <a href="/orders" class="btn btn-default">返回订单</a>
            </div>
        </div>
    </div>
</div>

<style>
.payment-container {
    display: flex;
    justify-content: center;
    padding: 40px 0;
}

.payment-card {
    background-color: #fff;
    padding: 40px;
    border-radius: 8px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.1);
    width: 100%;
    max-width: 600px;
}

.payment-card h2 {
    text-align: center;
    margin-bottom: 30px;
    color: #333;
}

.order-info {
    background-color: #f5f5f5;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 30px;
}

.info-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 15px;
    font-size: 16px;
}

.info-row:last-child {
    margin-bottom: 0;
}

.info-row .label {
    color: #666;
}

.info-row .value {
    color: #333;
    font-weight: 500;
}

.info-row .price {
    color: #ff4d4f;
    font-size: 24px;
    font-weight: bold;
}

.payment-method {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border: 2px solid #d9d9d9;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.3s;
}

.payment-method input {
    display: none;
}

.payment-methods {
    display: flex;
    gap: 15px;
}

.payment-method.active {
    border-color: #07c160;
    background-color: #f0f9ff;
}

.payment-method[data-method="alipay"].active {
    border-color: #1677ff;
    background-color: #f0f6ff;
}

.payment-method i {
    font-size: 24px;
    color: #07c160;
}

.payment-method[data-method="alipay"] i {
    color: #1677ff;
}

.pay-area {
    margin-bottom: 20px;
}

.payment-options {
    text-align: center;
}

.option-item {
    margin-bottom: 20px;
}

.qr-code-container {
    text-align: center;
    padding: 30px;
    background-color: #f5f5f5;
    border-radius: 8px;
    margin-bottom: 20px;
}

.qr-code-container img {
    width: 300px;
    height: 300px;
    margin: 0 auto;
    display: block;
    border: 1px solid #ddd;
    border-radius: 8px;
    background-color: #fff;
}

.qr-tips {
    margin-top: 15px;
    color: #666;
    font-size: 14px;
}

.payment-status {
    margin-top: 20px;
}

.loading {
    color: #1890ff;
    font-size: 16px;
}

.loading i {
    margin-right: 8px;
}

.payment-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
}

.btn-large {
    padding: 12px 40px;
    font-size: 16px;
}

.btn-default {
    background-color: #fff;
    color: #333;
    border: 1px solid #d9d9d9;
}

.btn-default:hover {
    background-color: #f5f5f5;
}
</style>

<script>
let orderId = <?= (int)$order['id'] ?>;
let orderNo = '<?= e($order['order_no']) ?>';
let paymentTimer = null;
let currentPayType = 'wechat';

// 初始化支付方式选择
document.addEventListener('DOMContentLoaded', function() {
    // 支付方式切换
    const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
    paymentMethods.forEach(method => {
        method.addEventListener('change', function() {
            updatePaymentMethod(this.value);
        });
    });
    
    // 立即支付按钮
    document.getElementById('btnPay').addEventListener('click', function() {
        createPayment();
    });
    
    // 支付宝直接支付按钮
    document.getElementById('btnAlipayDirect').addEventListener('click', function() {
        window.open(this.dataset.payUrl, '_blank');
    });
});

// 更新支付方式
function updatePaymentMethod(payType) {
    currentPayType = payType;
    
    // 更新按钮图标和文本
    const btnPay = document.getElementById('btnPay');
    const icon = btnPay.querySelector('i');
    const text = btnPay.querySelector('span') || btnPay;
    
    if (payType === 'alipay') {
        icon.className = 'fab fa-alipay';
        text.innerHTML = text.innerHTML.replace('微信', '支付宝');
    } else {
        icon.className = 'fab fa-weixin';
        text.innerHTML = text.innerHTML.replace('支付宝', '微信');
    }
    
    // 移除所有支付区域的激活状态
    document.querySelectorAll('.pay-area').forEach(area => {
        area.style.display = 'none';
    });
    
    // 隐藏之前的支付区域
    document.getElementById('btnPay').style.display = 'inline-block';
}

// 创建支付订单
function createPayment() {
    const payType = document.querySelector('input[name="payment_method"]:checked').value;
    
    fetch('/payment/create', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `order_no=${orderNo}&pay_type=${payType}&amount=<?= number_format($order['pay_amount'], 2, '.', '') ?>`
    })
    .then(res => res.json())
    .then(data => {
        if (data.code === 200) {
            // 根据支付类型显示不同的支付区域
            if (payType === 'wechat') {
                showWechatPayArea(data.data);
            } else if (payType === 'alipay') {
                showAlipayPayArea(data.data);
            }
            
            // 开始轮询支付状态
            startPolling();
        } else {
            alert(data.message || '支付请求失败');
        }
    })
    .catch(err => {
        console.error('Error:', err);
        alert('支付请求失败，请重试');
    });
}

// 开始轮询支付状态
function startPolling() {
    paymentTimer = setInterval(checkPaymentStatus, 2000); // 每2秒查询一次
}

// 查询支付状态
function checkPaymentStatus() {
    fetch('/payment/query', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `order_id=${orderId}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            if (data.data.status === 'paid') {
                clearInterval(paymentTimer);
                alert('支付成功！');
                window.location.href = '/orders';
            }
        }
    })
    .catch(err => {
        console.error('Error:', err);
    });
}

// 页面关闭时清除定时器
window.addEventListener('beforeunload', function() {
    if (paymentTimer) {
        clearInterval(paymentTimer);
    }
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
