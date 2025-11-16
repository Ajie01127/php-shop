// 加入购物车
function addToCart(productId, quantity = 1) {
    fetch('/cart/add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `product_id=${productId}&quantity=${quantity}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            updateCartCount();
        } else {
            alert(data.message);
        }
    })
    .catch(err => {
        console.error('Error:', err);
        alert('操作失败，请重试');
    });
}

// 更新购物车数量
function updateCartCount() {
    // 这里可以通过AJAX获取购物车数量
    // 简化处理，暂时不实现
}

// 自动隐藏消息提示
setTimeout(() => {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        alert.style.display = 'none';
    });
}, 3000);
