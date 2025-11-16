<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? site_config('site_name', '私域商城系统') ?></title>
    <meta name="keywords" content="<?= site_config('site_keywords', '私域商城,电商平台') ?>">
    <meta name="description" content="<?= site_config('site_description', '专业的私域电商平台') ?>">
    <link rel="icon" href="<?= site_config('site_favicon', '/images/favicon.ico') ?>">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="/">
                        <?php if (site_config('site_logo')): ?>
                            <img src="<?= site_config('site_logo') ?>" alt="<?= site_config('site_name') ?>">
                        <?php else: ?>
                            <?= site_config('site_name', '私域商城') ?>
                        <?php endif; ?>
                    </a>
                </div>
                
                <nav class="nav">
                    <a href="/" <?= ($_SERVER['REQUEST_URI'] == '/') ? 'class="active"' : '' ?>>首页</a>
                    <a href="/products" <?= (strpos($_SERVER['REQUEST_URI'], '/products') === 0) ? 'class="active"' : '' ?>>商品中心</a>
                </nav>
                
                <div class="header-actions">
                    <form action="/products" method="GET" class="search-form">
                        <input type="text" name="keyword" placeholder="搜索商品" value="<?= get('keyword', '') ?>">
                        <button type="submit"><i class="fas fa-search"></i></button>
                    </form>
                    
                    <a href="/cart" class="cart-icon">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count" id="cartCount">0</span>
                    </a>
                    
                    <?php if (isAuth()): ?>
                        <div class="user-menu">
                            <button class="user-button">
                                <i class="fas fa-user"></i>
                                <span><?= e(auth()['username']) ?></span>
                            </button>
                            <div class="user-dropdown">
                                <a href="/user/profile">个人中心</a>
                                <a href="/orders">我的订单</a>
                                <a href="/logout">退出登录</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="/login" class="login-btn">登录</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>
    
    <?php if ($message = flash('success')): ?>
        <div class="alert alert-success"><?= e($message) ?></div>
    <?php endif; ?>
    
    <?php if ($message = flash('error')): ?>
        <div class="alert alert-error"><?= e($message) ?></div>
    <?php endif; ?>
    
    <main class="main-content">
