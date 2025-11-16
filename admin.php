<?php
/**
 * 私域商城系统 - 管理后台入口
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

// 检查管理员登录
if (!isset($_SESSION['admin_id']) && !in_array($_SERVER['REQUEST_URI'], ['/admin/login'])) {
    header('Location: /admin/login');
    exit;
}

// 获取请求路径
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// 路由分发
$router = new Router();

// 后台路由
$router->get('/admin', 'Admin\\DashboardController@index');
$router->get('/admin/login', 'Admin\\AuthController@loginForm');
$router->post('/admin/login', 'Admin\\AuthController@login');
$router->get('/admin/logout', 'Admin\\AuthController@logout');

// 数据看板
$router->get('/admin/dashboard', 'Admin\\DashboardController@index');

// 商品管理
$router->get('/admin/products', 'Admin\\ProductController@index');
$router->get('/admin/products/create', 'Admin\\ProductController@create');
$router->post('/admin/products/store', 'Admin\\ProductController@store');
$router->get('/admin/products/{id}/edit', 'Admin\\ProductController@edit');
$router->post('/admin/products/{id}/update', 'Admin\\ProductController@update');
$router->post('/admin/products/{id}/delete', 'Admin\\ProductController@delete');

// 订单管理
$router->get('/admin/orders', 'Admin\\OrderController@index');
$router->get('/admin/orders/{id}', 'Admin\\OrderController@show');
$router->post('/admin/orders/{id}/status', 'Admin\\OrderController@updateStatus');

// 用户管理
$router->get('/admin/users', 'Admin\\UserController@index');
$router->get('/admin/users/{id}', 'Admin\\UserController@show');
$router->post('/admin/users/{id}/update', 'Admin\\UserController@update');

// 营销活动
$router->get('/admin/marketing', 'Admin\\MarketingController@index');
$router->get('/admin/marketing/create', 'Admin\\MarketingController@create');
$router->post('/admin/marketing/store', 'Admin\\MarketingController@store');

// 支付通道管理路由
$router->get('/admin/payment/channels', 'Admin\\PaymentChannelController@index');
$router->get('/admin/payment/channels/list', 'Admin\\PaymentChannelController@list');
$router->get('/admin/payment/channels/create', 'Admin\\PaymentChannelController@create');
$router->post('/admin/payment/channels/store', 'Admin\\PaymentChannelController@store');
$router->get('/admin/payment/channels/edit', 'Admin\\PaymentChannelController@edit');
$router->post('/admin/payment/channels/update', 'Admin\\PaymentChannelController@update');
$router->post('/admin/payment/channels/delete', 'Admin\\PaymentChannelController@delete');
$router->post('/admin/payment/channels/set-default', 'Admin\\PaymentChannelController@setDefault');
$router->post('/admin/payment/channels/toggle-active', 'Admin\\PaymentChannelController@toggleActive');
$router->get('/admin/payment/channels/default', 'Admin\\PaymentChannelController@getDefault');
$router->post('/admin/payment/channels/test', 'Admin\\PaymentChannelController@testConnection');

// 网站设置路由
$router->get('/admin/settings', 'Admin\\SettingController@index');
$router->post('/admin/settings/update', 'Admin\\SettingController@update');
$router->get('/admin/settings/get', 'Admin\\SettingController@get');
$router->get('/admin/settings/get-multiple', 'Admin\\SettingController@getMultiple');
$router->get('/admin/settings/create', 'Admin\\SettingController@create');
$router->post('/admin/settings/store', 'Admin\\SettingController@store');
$router->post('/admin/settings/delete', 'Admin\\SettingController@delete');
$router->post('/admin/settings/upload', 'Admin\\SettingController@uploadImage');
$router->get('/admin/settings/export', 'Admin\\SettingController@export');
$router->post('/admin/settings/import', 'Admin\\SettingController@import');
$router->post('/admin/settings/clear-cache', 'Admin\\SettingController@clearCache');

// 运费模板路由
$router->get('/admin/freight/templates', 'Admin\\FreightTemplateController@index');
$router->get('/admin/freight/templates/create', 'Admin\\FreightTemplateController@create');
$router->post('/admin/freight/templates/store', 'Admin\\FreightTemplateController@store');
$router->get('/admin/freight/templates/edit', 'Admin\\FreightTemplateController@edit');
$router->post('/admin/freight/templates/update', 'Admin\\FreightTemplateController@update');
$router->post('/admin/freight/templates/delete', 'Admin\\FreightTemplateController@delete');
$router->post('/admin/freight/calculate', 'Admin\\FreightTemplateController@calculate');

// 会员等级路由
$router->get('/admin/member/levels', 'Admin\\MemberLevelController@index');
$router->get('/admin/member/levels/create', 'Admin\\MemberLevelController@create');
$router->post('/admin/member/levels/store', 'Admin\\MemberLevelController@store');
$router->get('/admin/member/levels/edit', 'Admin\\MemberLevelController@edit');
$router->post('/admin/member/levels/update', 'Admin\\MemberLevelController@update');
$router->post('/admin/member/levels/delete', 'Admin\\MemberLevelController@delete');
$router->post('/admin/member/update-all-levels', 'Admin\\MemberLevelController@updateAllLevels');

// 快递管理路由
$router->get('/admin/express/configs', 'Admin\\ExpressController@configIndex');
$router->get('/admin/express/configs/create', 'Admin\\ExpressController@configCreate');
$router->get('/admin/express/configs/edit', 'Admin\\ExpressController@configEdit');
$router->get('/admin/express/configs/detail', 'Admin\\ExpressController@configDetail');
$router->post('/admin/express/configs/save', 'Admin\\ExpressController@configSave');
$router->post('/admin/express/configs/test', 'Admin\\ExpressController@configTest');
$router->post('/admin/express/configs/toggle-status', 'Admin\\ExpressController@configToggleStatus');
$router->post('/admin/express/configs/delete', 'Admin\\ExpressController@configDelete');
$router->get('/admin/express/orders', 'Admin\\ExpressController@orderIndex');
$router->get('/admin/express/orders/detail', 'Admin\\ExpressController@orderDetail');
$router->post('/admin/express/orders/create', 'Admin\\ExpressController@createOrder');
$router->post('/admin/express/orders/cancel', 'Admin\\ExpressController@cancelOrder');
$router->get('/admin/express/orders/route', 'Admin\\ExpressController@queryRoute');
$router->post('/admin/express/orders/update-waybill', 'Admin\\ExpressController@updateWaybillNo');
$router->get('/admin/express/companies', 'Admin\\ExpressController@getExpressCompanies');
$router->get('/admin/express/sf-types', 'Admin\\ExpressController@getSfExpressTypes');
$router->get('/admin/express/pay-methods', 'Admin\\ExpressController@getPayMethods');

// 短信管理路由
$router->get('/admin/sms/settings', 'Admin\\SmsController@settings');
$router->post('/admin/sms/settings/update', 'Admin\\SmsController@updateSettings');
$router->post('/admin/sms/settings/test', 'Admin\\SmsController@testConnection');
$router->get('/admin/sms/statistics', 'Admin\\SmsController@statistics');
// 电子面单打印路由
$router->get('/admin/express/print-config', 'Admin\\ExpressController@printConfig');
$router->get('/admin/express/print-config/get', 'Admin\\ExpressController@getPrintConfig');
$router->post('/admin/express/print-config/save', 'Admin\\ExpressController@savePrintConfig');
$router->post('/admin/express/print-config/reset', 'Admin\\ExpressController@resetPrintConfig');
$router->get('/admin/express/print-modes', 'Admin\\ExpressController@getPrintModes');
$router->get('/admin/express/template-sizes', 'Admin\\ExpressController@getTemplateSizes');
$router->post('/admin/express/print-waybill', 'Admin\\ExpressController@printWaybill');
$router->post('/admin/express/batch-print-waybill', 'Admin\\ExpressController@batchPrintWaybill');
$router->get('/admin/express/preview-waybill', 'Admin\\ExpressController@previewWaybill');
$router->get('/admin/express/export-waybill-pdf', 'Admin\\ExpressController@exportWaybillPDF');
$router->get('/admin/express/printers', 'Admin\\ExpressController@getAvailablePrinters');

// 执行路由
$router->dispatch($uri, $method);
