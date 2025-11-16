    </main>
    
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>关于我们</h3>
                    <p><?= site_config('site_description', '致力于提供优质的私域商城解决方案') ?></p>
                </div>
                <div class="footer-section">
                    <h3>帮助中心</h3>
                    <ul>
                        <li><a href="/help/shipping">配送说明</a></li>
                        <li><a href="/help/return">退换货政策</a></li>
                        <li><a href="/help/faq">常见问题</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>客户服务</h3>
                    <ul>
                        <li><a href="/contact">在线客服</a></li>
                        <li><a href="/contact">联系我们</a></li>
                        <li><a href="/help/after-sales">售后服务</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>联系方式</h3>
                    <?php if (site_config('contact_phone')): ?>
                        <p><i class="fas fa-phone"></i> <?= site_config('contact_phone') ?></p>
                    <?php endif; ?>
                    <?php if (site_config('contact_email')): ?>
                        <p><i class="fas fa-envelope"></i> <?= site_config('contact_email') ?></p>
                    <?php endif; ?>
                    <?php if (site_config('work_time')): ?>
                        <p><i class="fas fa-clock"></i> <?= site_config('work_time') ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="footer-bottom">
                <p><?= site_config('site_copyright', '&copy; 2024 私域商城系统. All rights reserved.') ?></p>
                <?php if (site_config('site_icp')): ?>
                    <p><?= site_config('site_icp') ?></p>
                <?php endif; ?>
            </div>
        </div>
    </footer>
    
    <script src="<?= asset('js/main.js') ?>"></script>
</body>
</html>
