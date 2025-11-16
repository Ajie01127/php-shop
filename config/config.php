<?php
/**
 * 系统配置文件
 */

return [
    // 应用配置
    'app' => [
        'name' => '私域商城系统',
        'env' => 'development', // development, production
        'debug' => true,
        'url' => 'http://localhost',
        'timezone' => 'Asia/Shanghai',
    ],

    // 数据库配置
    'database' => [
        'driver' => 'mysql',
        'host' => 'localhost',
        'port' => '3306',
        'database' => 'private_mall',
        'username' => 'root',
        'password' => '',
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
            'app_id' => 'wx1234567890abcdef',  // 微信支付APPID
            'mch_id' => '1234567890',          // 商户号
            'api_key' => 'your_api_v3_key_32_characters', // APIv3密钥(32位)
            'cert_path' => __DIR__ . '/../certs/apiclient_cert.pem',  // 商户证书路径
            'key_path' => __DIR__ . '/../certs/apiclient_key.pem',    // 商户私钥路径
        ],
        'alipay' => [
            'app_id' => '',
            'private_key' => '',
            'public_key' => '',
        ],
    ],

    // 小程序配置
    'miniprogram' => [
        'app_id' => 'wx1234567890abcdef',      // 小程序AppID
        'app_secret' => 'yourappsecret',       // 小程序AppSecret
        'token_expire' => 7200,                // token过期时间(秒)
        'api_domain' => 'https://yourdomain.com', // API域名
        'debug' => true,                       // 调试模式
    ],
];
