<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="container">
    <div class="auth-container">
        <div class="auth-card">
            <h2>用户登录</h2>
            
            <form action="/login" method="POST" class="auth-form">
                <div class="form-group">
                    <label>邮箱</label>
                    <input type="email" name="email" required placeholder="请输入邮箱">
                </div>
                
                <div class="form-group">
                    <label>密码</label>
                    <input type="password" name="password" required placeholder="请输入密码">
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">登录</button>
            </form>
            
            <div class="auth-links">
                <span>还没有账号？</span>
                <a href="/register">立即注册</a>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
