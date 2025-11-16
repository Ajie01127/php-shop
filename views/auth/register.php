<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="container">
    <div class="auth-container">
        <div class="auth-card">
            <h2>用户注册</h2>
            
            <form action="/register" method="POST" class="auth-form">
                <div class="form-group">
                    <label>用户名</label>
                    <input type="text" name="username" required placeholder="请输入用户名">
                </div>
                
                <div class="form-group">
                    <label>邮箱</label>
                    <input type="email" name="email" required placeholder="请输入邮箱">
                </div>
                
                <div class="form-group">
                    <label>手机号</label>
                    <input type="text" name="phone" placeholder="请输入手机号(可选)">
                </div>
                
                <div class="form-group">
                    <label>密码</label>
                    <input type="password" name="password" required placeholder="请输入密码(至少6位)">
                </div>
                
                <div class="form-group">
                    <label>确认密码</label>
                    <input type="password" name="confirm_password" required placeholder="请再次输入密码">
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">注册</button>
            </form>
            
            <div class="auth-links">
                <span>已有账号？</span>
                <a href="/login">立即登录</a>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
