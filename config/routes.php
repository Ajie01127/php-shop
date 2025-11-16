<?php

/**
 * 路由配置文件
 * 定义系统的所有API路由
 */

return [
    // 前台路由
    'GET /' => 'HomeController@index',
    'GET /product/{id}' => 'ProductController@show',
    'GET /category/{id}' => 'CategoryController@show',
    
    // 小程序API路由
    'POST /api/miniprogram/login' => 'MiniProgramController@login',
    'GET /api/miniprogram/products' => 'MiniProgramController@products',
    'GET /api/miniprogram/product/detail' => 'MiniProgramController@productDetail',
    'POST /api/miniprogram/order/create' => 'MiniProgramController@createOrder',
    'POST /api/miniprogram/order/pay' => 'MiniProgramController@miniPay',
    'GET /api/miniprogram/orders' => 'MiniProgramController@userOrders',
    'GET /api/miniprogram/order/detail' => 'MiniProgramController@orderDetail',
    'POST /api/miniprogram/order/cancel' => 'MiniProgramController@cancelOrder',
    
    // 短信API路由
    'POST /api/sms/send' => 'SmsController@sendCode',
    'POST /api/sms/verify' => 'SmsController@verifyCode',
    'POST /api/sms/order-notice' => 'SmsController@sendOrderNotice',
    'POST /api/sms/test' => 'SmsController@testConnection',
    'GET /api/sms/statistics' => 'SmsController@getStatistics',
    
    // 购物车相关
    'GET /cart' => 'CartController@index',
    'POST /cart/add' => 'CartController@add',
    'POST /cart/update' => 'CartController@update',
    'POST /cart/remove' => 'CartController@remove',
    
    // 订单相关
    'GET /order/checkout' => 'OrderController@checkout',
    'POST /order/create' => 'OrderController@create',
    'GET /order/{id}' => 'OrderController@show',
    'POST /order/{id}/pay' => 'OrderController@pay',
    
    // 用户相关
    'GET /user/profile' => 'UserController@profile',
    'POST /user/update' => 'UserController@update',
    'POST /user/address/add' => 'UserController@addAddress',
    'POST /user/address/update' => 'UserController@updateAddress',
    'POST /user/address/delete' => 'UserController@deleteAddress',
    
    // 支付相关
    'POST /payment/create' => 'PaymentController@createOrder',
    'POST /payment/query' => 'PaymentController@queryOrder',
    'POST /payment/refund' => 'PaymentController@refund',
    'POST /payment/wechat/notify' => 'PaymentController@wechatNotify',
    'POST /payment/alipay/notify' => 'PaymentController@alipayNotify',
    'GET /payment/alipay/return' => 'PaymentController@alipayReturn',
    'GET /payment/success' => 'PaymentController@success',
    'GET /payment/failed' => 'PaymentController@failed',
    'GET /payment/pending' => 'PaymentController@pending',
    
    // 后台管理路由
    'GET /admin' => 'Admin\DashboardController@index',
    'GET /admin/settings' => 'Admin\SettingsController@index',
    'POST /admin/settings/save' => 'Admin\SettingsController@save',
    'GET /admin/settings/get' => 'Admin\SettingsController@get',
    'GET /admin/settings/get-multiple' => 'Admin\SettingsController@getMultiple',
    
    // 商品管理
    'GET /admin/products' => 'Admin\ProductController@index',
    'GET /admin/product/create' => 'Admin\ProductController@create',
    'POST /admin/product/save' => 'Admin\ProductController@save',
    'GET /admin/product/{id}/edit' => 'Admin\ProductController@edit',
    'POST /admin/product/{id}/update' => 'Admin\ProductController@update',
    'POST /admin/product/{id}/delete' => 'Admin\ProductController@delete',
    
    // 订单管理
    'GET /admin/orders' => 'Admin\OrderController@index',
    'GET /admin/order/{id}' => 'Admin\OrderController@show',
    'POST /admin/order/{id}/ship' => 'Admin\OrderController@ship',
    'POST /admin/order/{id}/cancel' => 'Admin\OrderController@cancel',
    
    // 用户管理
    'GET /admin/users' => 'Admin\UserController@index',
    'GET /admin/user/{id}' => 'Admin\UserController@show',
    'POST /admin/user/{id}/update' => 'Admin\UserController@update',
    
    // 快递管理
    'GET /admin/express/orders' => 'Admin\ExpressController@orders',
    'POST /admin/express/orders/create' => 'Admin\ExpressController@createOrder',
    'GET /admin/express/orders/route' => 'Admin\ExpressController@getRoute',
    'POST /admin/express/orders/cancel' => 'Admin\ExpressController@cancelOrder',
    
    // 打印配置
    'GET /admin/express/print-config/get' => 'Admin\ExpressController@getPrintConfig',
    'POST /admin/express/print-config/save' => 'Admin\ExpressController@savePrintConfig',
    
    // 系统日志
    'GET /admin/logs' => 'Admin\LogController@index',
    
    // 安装程序路由
    'GET /install' => 'InstallController@index',
    'POST /install/step/{step}' => 'InstallController@processStep',
];

// 路由分组示例（如果需要）：
/*
'api' => [
    'prefix' => '/api/v1',
    'routes' => [
        'GET /products' => 'Api\ProductController@index',
        'GET /product/{id}' => 'Api\ProductController@show',
    ]
],
*/

?>