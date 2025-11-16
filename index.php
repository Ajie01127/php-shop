<?php
/**
 * 私域商城系统 - 入口文件
 * 前台商城入口
 */

session_start();
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/core/bootstrap.php';
require_once __DIR__ . '/core/security.php';

use Core\Router;
use Core\Database;

// 加载配置
$config = require __DIR__ . '/config/config.php';

// 初始化数据库连接
Database::init($config['database']);

// 强制HTTPS（如果在后台设置中启用）
force_https_redirect();

// 设置安全响应头
set_security_headers();

// 获取请求路径
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// 路由分发
$router = new Router();

// 前台路由
$router->get('/', 'HomeController@index');
$router->get('/products', 'ProductController@index');
$router->get('/product/{id}', 'ProductController@show');
$router->get('/cart', 'CartController@index');
$router->post('/cart/add', 'CartController@add');
$router->post('/cart/update', 'CartController@update');
$router->post('/cart/remove', 'CartController@remove');
$router->get('/checkout', 'OrderController@checkout');
$router->post('/order/create', 'OrderController@create');
$router->get('/user/orders', 'OrderController@userOrders');
$router->get('/user/profile', 'UserController@profile');
$router->post('/user/update', 'UserController@update');
$router->get('/login', 'AuthController@loginForm');
$router->post('/login', 'AuthController@login');
$router->get('/register', 'AuthController@registerForm');
$router->post('/register', 'AuthController@register');
$router->get('/logout', 'AuthController@logout');

// 支付路由
$router->get('/payment/pay', 'PaymentController@pay');
$router->post('/payment/create', 'PaymentController@createPay');
$router->post('/payment/query', 'PaymentController@queryStatus');
$router->post('/payment/wechat/notify', 'PaymentController@wechatNotify');
$router->post('/payment/wechat/refund-notify', 'PaymentController@refundNotify');
$router->post('/payment/refund', 'PaymentController@refund');

// 小程序API路由
$router->post('/api/miniprogram/login', 'MiniProgramController@login');
$router->get('/api/miniprogram/products', 'MiniProgramController@products');
$router->get('/api/miniprogram/product/detail', 'MiniProgramController@productDetail');
$router->post('/api/miniprogram/order/create', 'MiniProgramController@createOrder');
$router->post('/api/miniprogram/order/pay', 'MiniProgramController@miniPay');
$router->get('/api/miniprogram/orders', 'MiniProgramController@userOrders');
$router->get('/api/miniprogram/order/detail', 'MiniProgramController@orderDetail');
$router->post('/api/miniprogram/order/cancel', 'MiniProgramController@cancelOrder');

// 执行路由
$router->dispatch($uri, $method);
