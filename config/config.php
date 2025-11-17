<?php
/**
 * 系统配置文件
 */

return [
    // 应用配置
    'app' => [
        'name' => '私域商城系统',
        'env' => 'production', // development, production
        'debug' => false,
        'url' => 'http://localhost',
        'timezone' => 'Asia/Shanghai',
    ],

    // 数据库配置
    'database' => [
        'driver' => 'mysql',
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'port' => $_ENV['DB_PORT'] ?? '3306',
        'database' => $_ENV['DB_DATABASE'] ?? 'private_mall',
        'username' => $_ENV['DB_USERNAME'] ?? 'root',
        'password' => $_ENV['DB_PASSWORD'] ?? '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
    ],

    // 会话配置
    'session' => [
        'lifetime' => 120, // 分钟
        'expire_on_close' => false,
    ],

    // 文件上传配置
    'upload' => [
        'path' => __DIR__ . '/../public/uploads/',
        'max_size' => 2048, // KB
        'allowed_types' => ['jpg', 'jpeg', 'png', 'gif'],
    ],

    // 分页配置
    'pagination' => [
        'per_page' => 20,
    ],

    // 支付配置
    'payment' => [
        'wechat' => [
            'app_id' => $_ENV['WECHAT_APP_ID'] ?? '',  // 微信支付APPID
            'mch_id' => $_ENV['WECHAT_MCH_ID'] ?? '', // 商户号
            'api_key' => $_ENV['WECHAT_API_KEY'] ?? '', // APIv3密钥(32位)
            'cert_path' => __DIR__ . '/../certs/apiclient_cert.pem',  // 商户证书路径
            'key_path' => __DIR__ . '/../certs/apiclient_key.pem',    // 商户私钥路径
        ],
        'alipay' => [
            'app_id' => $_ENV['ALIPAY_APP_ID'] ?? '',
            'private_key' => $_ENV['ALIPAY_PRIVATE_KEY'] ?? '',
            'public_key' => $_ENV['ALIPAY_PUBLIC_KEY'] ?? '',
        ],
    ],

    // 小程序配置
    'miniprogram' => [
        'app_id' => $_ENV['MINIPROGRAM_APP_ID'] ?? '',      // 小程序AppID
        'app_secret' => $_ENV['MINIPROGRAM_APP_SECRET'] ?? '', // 小程序AppSecret
        'token_expire' => 7200,                // token过期时间(秒)
        'api_domain' => $_ENV['API_DOMAIN'] ?? 'https://yourdomain.com', // API域名
        'debug' => false,                      // 调试模式
    ],
];
