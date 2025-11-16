<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="container">
    <?php if (site_config('mall_notice')): ?>
    <!-- 商城公告 -->
    <div class="alert alert-info mall-notice">
        <i class="fas fa-bullhorn"></i>
        <?= site_config('mall_notice') ?>
    </div>
    <?php endif; ?>
    
    <!-- 轮播图 -->
    <div class="banner-slider">
        <?php foreach ($banners as $index => $banner): ?>
        <div class="banner-slide <?= $index === 0 ? 'active' : '' ?>">
            <img src="<?= e($banner['image']) ?>" alt="<?= e($banner['title']) ?>">
            <div class="banner-content">
                <h2><?= e($banner['title']) ?></h2>
                <p><?= e($banner['description']) ?></p>
                <a href="/products" class="btn btn-primary">立即选购</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <!-- 商品分类 -->
    <section class="categories-section">
        <h2 class="section-title">商品分类</h2>
        <div class="categories-grid">
            <?php foreach ($categories as $category): ?>
            <a href="/products?category=<?= $category['id'] ?>" class="category-card">
                <div class="category-icon"><?= $category['icon'] ?></div>
                <h3><?= e($category['name']) ?></h3>
            </a>
            <?php endforeach; ?>
        </div>
    </section>
    
    <!-- 热销商品 -->
    <section class="products-section">
        <div class="section-header">
            <h2 class="section-title">
                <i class="fas fa-fire" style="color: #ff4d4f;"></i>
                热销商品
            </h2>
            <a href="/products" class="view-more">查看更多 →</a>
        </div>
        
        <div class="products-grid">
            <?php foreach ($hotProducts as $product): ?>
            <div class="product-card">
                <a href="/product/<?= $product['id'] ?>" class="product-image">
                    <?php 
                    $images = json_decode($product['images'], true);
                    $image = $images[0] ?? '';
                    ?>
                    <img src="<?= e($image) ?>" alt="<?= e($product['name']) ?>">
                </a>
                <div class="product-info">
                    <a href="/product/<?= $product['id'] ?>" class="product-name"><?= e($product['name']) ?></a>
                    <div class="product-price">
                        <span class="current-price"><?= formatPrice($product['price']) ?></span>
                        <?php if ($product['original_price']): ?>
                        <span class="original-price"><?= formatPrice($product['original_price']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="product-meta">
                        <span><i class="fas fa-star"></i> <?= $product['rating'] ?></span>
                        <span>已售 <?= $product['sales'] ?></span>
                    </div>
                    <button class="btn btn-primary btn-add-cart" data-id="<?= $product['id'] ?>">
                        <i class="fas fa-shopping-cart"></i> 加入购物车
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    
    <!-- 优势特点 -->
    <section class="features-section">
        <h2 class="section-title">为什么选择我们</h2>
        <div class="features-grid">
            <div class="feature-item">
                <h3><?= $stats['total_products'] ?>+</h3>
                <p>商品种类</p>
            </div>
            <div class="feature-item">
                <h3><?= $stats['total_users'] ?>+</h3>
                <p>服务用户</p>
            </div>
            <div class="feature-item">
                <h3>98.5%</h3>
                <p>好评率</p>
            </div>
            <div class="feature-item">
                <h3>50+</h3>
                <p>合作品牌</p>
            </div>
        </div>
    </section>
</div>

<script>
// 轮播图自动播放
let currentSlide = 0;
const slides = document.querySelectorAll('.banner-slide');
const slideCount = slides.length;

function showSlide(index) {
    slides.forEach(s => s.classList.remove('active'));
    slides[index].classList.add('active');
}

function nextSlide() {
    currentSlide = (currentSlide + 1) % slideCount;
    showSlide(currentSlide);
}

setInterval(nextSlide, 5000);

// 加入购物车
document.querySelectorAll('.btn-add-cart').forEach(btn => {
    btn.addEventListener('click', function() {
        const productId = this.dataset.id;
        addToCart(productId);
    });
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
