<?php
/**
 * 私域商城系统安装器
 * 负责数据库操作和配置文件生成
 */

class Installer {
    private $config;
    private $db;
    private $errors = [];

    public function __construct($config) {
        $this->config = $config;
    }

    /**
     * 测试数据库连接
     */
    public function testDatabaseConnection($dbConfig) {
        try {
            $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $options);
            
            // 检查数据库是否存在
            $stmt = $pdo->query("SHOW DATABASES LIKE '{$dbConfig['database']}'");
            $dbExists = $stmt->fetch();
            
            if (!$dbExists) {
                // 尝试创建数据库
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbConfig['database']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            }
            
            // 测试连接数据库
            $pdo->exec("USE `{$dbConfig['database']}`");
            
            return ['success' => true, 'message' => '数据库连接成功！'];
            
        } catch (PDOException $e) {
            return ['success' => false, 'message' => '数据库连接失败: ' . $e->getMessage()];
        }
    }

    /**
     * 安装数据库表结构
     */
    public function installDatabase($dbConfig) {
        try {
            $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->db = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $options);
            
            $prefix = $dbConfig['prefix'];
            
            // 用户表
            $this->createUserTable($prefix);
            
            // 商品分类表
            $this->createCategoryTable($prefix);
            
            // 商品表
            $this->createProductTable($prefix);
            
            // 购物车表
            $this->createCartTable($prefix);
            
            // 订单表
            $this->createOrderTable($prefix);
            
            // 订单商品表
            $this->createOrderItemTable($prefix);
            
            // 支付记录表
            $this->createPaymentTable($prefix);
            
            // 系统配置表
            $this->createSettingsTable($prefix);
            
            // 插入默认数据
            $this->insertDefaultData($dbConfig);
            
            return ['success' => true, 'message' => '数据库表结构安装成功！'];
            
        } catch (PDOException $e) {
            return ['success' => false, 'message' => '数据库安装失败: ' . $e->getMessage()];
        }
    }

    /**
     * 创建用户表（支持小程序）
     */
    private function createUserTable($prefix) {
        $sql = "CREATE TABLE IF NOT EXISTS `{$prefix}users` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `username` VARCHAR(50) NOT NULL UNIQUE,
            `email` VARCHAR(100) NOT NULL UNIQUE,
            `password` VARCHAR(255) NOT NULL,
            `real_name` VARCHAR(50) DEFAULT NULL,
            `phone` VARCHAR(20) DEFAULT NULL,
            `avatar` VARCHAR(255) DEFAULT NULL,
            `role` ENUM('admin', 'user') DEFAULT 'user',
            `status` TINYINT(1) DEFAULT 1,
            `openid` VARCHAR(128) DEFAULT NULL COMMENT '微信openid',
            `unionid` VARCHAR(128) DEFAULT NULL COMMENT '微信unionid',
            `nickname` VARCHAR(100) DEFAULT NULL COMMENT '昵称',
            `gender` TINYINT DEFAULT 0 COMMENT '性别: 0未知 1男 2女',
            `country` VARCHAR(50) DEFAULT NULL COMMENT '国家',
            `province` VARCHAR(50) DEFAULT NULL COMMENT '省份',
            `city` VARCHAR(50) DEFAULT NULL COMMENT '城市',
            `language` VARCHAR(20) DEFAULT 'zh_CN' COMMENT '语言',
            `user_type` VARCHAR(20) DEFAULT 'web' COMMENT '用户类型: web, mini_program',
            `last_login_at` TIMESTAMP NULL DEFAULT NULL COMMENT '最后登录时间',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_username` (`username`),
            KEY `idx_email` (`email`),
            KEY `idx_openid` (`openid`),
            KEY `idx_user_type` (`user_type`),
            KEY `idx_last_login` (`last_login_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        $this->db->exec($sql);
    }

    /**
     * 创建商品分类表
     */
    private function createCategoryTable($prefix) {
        $sql = "CREATE TABLE IF NOT EXISTS `{$prefix}categories` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `parent_id` INT(11) DEFAULT 0,
            `name` VARCHAR(100) NOT NULL,
            `slug` VARCHAR(100) NOT NULL UNIQUE,
            `description` TEXT,
            `image` VARCHAR(255) DEFAULT NULL,
            `sort_order` INT(11) DEFAULT 0,
            `status` TINYINT(1) DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_parent_id` (`parent_id`),
            KEY `idx_slug` (`slug`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        $this->db->exec($sql);
    }

    /**
     * 创建商品表
     */
    private function createProductTable($prefix) {
        $sql = "CREATE TABLE IF NOT EXISTS `{$prefix}products` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `category_id` INT(11) NOT NULL,
            `name` VARCHAR(200) NOT NULL,
            `slug` VARCHAR(200) NOT NULL UNIQUE,
            `sku` VARCHAR(50) NOT NULL UNIQUE,
            `description` TEXT,
            `price` DECIMAL(10,2) NOT NULL,
            `original_price` DECIMAL(10,2) DEFAULT NULL,
            `stock` INT(11) DEFAULT 0,
            `weight` DECIMAL(8,2) DEFAULT 0,
            `images` TEXT,
            `attributes` JSON,
            `tags` VARCHAR(255) DEFAULT NULL,
            `is_featured` TINYINT(1) DEFAULT 0,
            `is_hot` TINYINT(1) DEFAULT 0,
            `is_new` TINYINT(1) DEFAULT 0,
            `view_count` INT(11) DEFAULT 0,
            `sold_count` INT(11) DEFAULT 0,
            `status` TINYINT(1) DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_category_id` (`category_id`),
            KEY `idx_slug` (`slug`),
            KEY `idx_sku` (`sku`),
            KEY `idx_is_featured` (`is_featured`),
            KEY `idx_is_hot` (`is_hot`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        $this->db->exec($sql);
    }

    /**
     * 创建购物车表
     */
    private function createCartTable($prefix) {
        $sql = "CREATE TABLE IF NOT EXISTS `{$prefix}cart` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `user_id` INT(11) NOT NULL,
            `product_id` INT(11) NOT NULL,
            `quantity` INT(11) NOT NULL,
            `price` DECIMAL(10,2) NOT NULL,
            `attributes` JSON,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `unique_user_product` (`user_id`, `product_id`),
            KEY `idx_user_id` (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        $this->db->exec($sql);
    }

    /**
     * 创建订单表
     */
    private function createOrderTable($prefix) {
        $sql = "CREATE TABLE IF NOT EXISTS `{$prefix}orders` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `order_no` VARCHAR(50) NOT NULL UNIQUE,
            `user_id` INT(11) NOT NULL,
            `total_amount` DECIMAL(10,2) NOT NULL,
            `discount_amount` DECIMAL(10,2) DEFAULT 0,
            `shipping_amount` DECIMAL(10,2) DEFAULT 0,
            `payable_amount` DECIMAL(10,2) NOT NULL,
            `payment_method` VARCHAR(50) DEFAULT NULL,
            `payment_status` ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
            `order_status` ENUM('pending', 'confirmed', 'shipped', 'completed', 'cancelled') DEFAULT 'pending',
            `shipping_name` VARCHAR(100) DEFAULT NULL,
            `shipping_phone` VARCHAR(20) DEFAULT NULL,
            `shipping_address` TEXT,
            `invoice_needed` TINYINT(1) DEFAULT 0,
            `invoice_title` VARCHAR(200) DEFAULT NULL,
            `remark` TEXT,
            `paid_at` TIMESTAMP NULL,
            `completed_at` TIMESTAMP NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_user_id` (`user_id`),
            KEY `idx_order_no` (`order_no`),
            KEY `idx_payment_status` (`payment_status`),
            KEY `idx_order_status` (`order_status`),
            KEY `idx_created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        $this->db->exec($sql);
    }

    /**
     * 创建订单商品表
     */
    private function createOrderItemTable($prefix) {
        $sql = "CREATE TABLE IF NOT EXISTS `{$prefix}order_items` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `order_id` INT(11) NOT NULL,
            `product_id` INT(11) NOT NULL,
            `product_name` VARCHAR(200) NOT NULL,
            `product_image` VARCHAR(255) DEFAULT NULL,
            `sku` VARCHAR(50) NOT NULL,
            `price` DECIMAL(10,2) NOT NULL,
            `quantity` INT(11) NOT NULL,
            `total_amount` DECIMAL(10,2) NOT NULL,
            `attributes` JSON,
            PRIMARY KEY (`id`),
            KEY `idx_order_id` (`order_id`),
            KEY `idx_product_id` (`product_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        $this->db->exec($sql);
    }

    /**
     * 创建支付记录表
     */
    private function createPaymentTable($prefix) {
        $sql = "CREATE TABLE IF NOT EXISTS `{$prefix}payments` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `order_id` INT(11) NOT NULL,
            `payment_no` VARCHAR(50) NOT NULL UNIQUE,
            `payment_method` VARCHAR(50) NOT NULL,
            `amount` DECIMAL(10,2) NOT NULL,
            `status` ENUM('pending', 'success', 'failed') DEFAULT 'pending',
            `transaction_id` VARCHAR(100) DEFAULT NULL,
            `payment_data` JSON,
            `paid_at` TIMESTAMP NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_order_id` (`order_id`),
            KEY `idx_payment_no` (`payment_no`),
            KEY `idx_transaction_id` (`transaction_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        $this->db->exec($sql);
    }

    /**
     * 创建系统配置表
     */
    private function createSettingsTable($prefix) {
        $sql = "CREATE TABLE IF NOT EXISTS `{$prefix}settings` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `key` VARCHAR(100) NOT NULL UNIQUE,
            `value` TEXT,
            `description` VARCHAR(255) DEFAULT NULL,
            `type` ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
            `group` VARCHAR(50) DEFAULT 'general',
            `sort_order` INT(11) DEFAULT 0,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_key` (`key`),
            KEY `idx_group` (`group`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        $this->db->exec($sql);
    }

    /**
     * 插入默认数据
     */
    private function insertDefaultData($dbConfig) {
        $prefix = $dbConfig['prefix'];
        
        // 插入默认分类
        $this->db->exec("INSERT IGNORE INTO `{$prefix}categories` 
            (name, slug, description, sort_order) VALUES 
            ('默认分类', 'default', '系统默认分类', 1)");
        
        // 插入系统设置
        $settings = [
            ['site_name', '私域商城系统', '网站名称', 'string', 'general'],
            ['site_url', $_SERVER['HTTP_HOST'] ?? 'localhost', '网站地址', 'string', 'general'],
            ['site_description', '基于PHP的现代化B2C电商平台', '网站描述', 'string', 'general'],
            ['site_keywords', '商城,电商,B2C', '网站关键词', 'string', 'general'],
            ['currency', 'CNY', '货币单位', 'string', 'payment'],
            ['currency_symbol', '¥', '货币符号', 'string', 'payment'],
            ['order_prefix', 'ORD', '订单前缀', 'string', 'order'],
            ['products_per_page', '20', '每页商品数量', 'number', 'display'],
            ['maintenance_mode', '0', '维护模式', 'boolean', 'system'],
            
            // 小程序设置
            ['enable_miniprogram', '1', '是否启用微信小程序功能', 'switch', 'miniprogram', 1],
            ['miniprogram_name', '私域商城小程序', '小程序名称', 'text', 'miniprogram', 2],
            ['miniprogram_app_id', 'wx1234567890abcdef', '小程序AppID', 'text', 'miniprogram', 3],
            ['miniprogram_app_secret', 'yourappsecret', '小程序AppSecret', 'text', 'miniprogram', 4],
            ['miniprogram_token_expire', '7200', 'Token过期时间', 'number', 'miniprogram', 5],
            ['miniprogram_api_domain', $_SERVER['HTTP_HOST'] ?? 'localhost', 'API域名', 'text', 'miniprogram', 6],
            ['miniprogram_debug', '1', '调试模式', 'switch', 'miniprogram', 7],
            ['miniprogram_welcome_text', '欢迎使用私域商城小程序', '欢迎语', 'textarea', 'miniprogram', 8],
            ['miniprogram_copyright', 'Copyright © 2024 私域商城. All rights reserved.', '小程序版权', 'text', 'miniprogram', 9],
            ['miniprogram_share_title', '私域商城 - 专业电商平台', '分享标题', 'text', 'miniprogram', 10],
            ['miniprogram_share_desc', '发现优质商品，尽在私域商城', '分享描述', 'textarea', 'miniprogram', 11],
            ['miniprogram_share_image', '/images/share-logo.png', '分享图片', 'image', 'miniprogram', 12],
            
            // 短信设置
            ['sms_enable', '0', '是否启用短信服务', 'switch', 'sms', 1],
            ['sms_secret_id', '', '腾讯云SecretId', 'text', 'sms', 2],
            ['sms_secret_key', '', '腾讯云SecretKey', 'password', 'sms', 3],
            ['sms_sdk_app_id', '', '短信应用SDK AppID', 'text', 'sms', 4],
            ['sms_sign_name', '', '短信签名', 'text', 'sms', 5],
            ['sms_template_id_login', '', '登录验证码模板ID', 'text', 'sms', 10],
            ['sms_template_id_register', '', '注册验证码模板ID', 'text', 'sms', 11],
            ['sms_template_id_reset', '', '密码重置模板ID', 'text', 'sms', 12],
            ['sms_template_id_order', '', '订单确认模板ID', 'text', 'sms', 13],
            ['sms_template_id_payment', '', '支付成功模板ID', 'text', 'sms', 14],
            ['sms_template_id_shipping', '', '发货通知模板ID', 'text', 'sms', 15],
            ['sms_template_id_refund', '', '退款通知模板ID', 'text', 'sms', 16]
        ];
        
        foreach ($settings as $setting) {
            $stmt = $this->db->prepare("INSERT IGNORE INTO `{$prefix}settings` 
                (key, value, description, type, group) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute($setting);
        }
    }

    /**
     * 创建管理员账户
     */
    public function createAdminUser($userData) {
        try {
            $prefix = $this->config['prefix'];
            
            $stmt = $this->db->prepare("INSERT INTO `{$prefix}users` 
                (username, email, password, role, real_name) 
                VALUES (?, ?, ?, 'admin', ?)");
            
            $stmt->execute([
                $userData['admin_username'],
                $userData['admin_email'],
                $userData['admin_password'],
                '系统管理员'
            ]);
            
            return ['success' => true, 'message' => '管理员账户创建成功！'];
            
        } catch (PDOException $e) {
            return ['success' => false, 'message' => '管理员创建失败: ' . $e->getMessage()];
        }
    }

    /**
     * 生成配置文件
     */
    public function generateConfigFile($dbConfig, $siteConfig) {
        $configContent = "<?php\n/**\n * 系统配置文件 - 自动生成\n */\n\nreturn [\n";
        
        // 应用配置
        $configContent .= "    // 应用配置\n    'app' => [\n";
        $configContent .= "        'name' => '" . addslashes($siteConfig['site_name']) . "',\n";
        $configContent .= "        'env' => 'production',\n";
        $configContent .= "        'debug' => false,\n";
        $configContent .= "        'url' => '" . addslashes($siteConfig['site_url']) . "',\n";
        $configContent .= "        'timezone' => 'Asia/Shanghai',\n";
        $configContent .= "    ],\n\n";
        
        // 数据库配置
        $configContent .= "    // 数据库配置\n    'database' => [\n";
        $configContent .= "        'driver' => 'mysql',\n";
        $configContent .= "        'host' => '" . addslashes($dbConfig['host']) . "',\n";
        $configContent .= "        'port' => '" . addslashes($dbConfig['port']) . "',\n";
        $configContent .= "        'database' => '" . addslashes($dbConfig['database']) . "',\n";
        $configContent .= "        'username' => '" . addslashes($dbConfig['username']) . "',\n";
        $configContent .= "        'password' => '" . addslashes($dbConfig['password']) . "',\n";
        $configContent .= "        'charset' => 'utf8mb4',\n";
        $configContent .= "        'collation' => 'utf8mb4_unicode_ci',\n";
        $configContent .= "        'prefix' => '" . addslashes($dbConfig['prefix']) . "',\n";
        $configContent .= "    ],\n\n";
        
        // 其他配置
        $configContent .= "    // 会话配置\n    'session' => [\n";
        $configContent .= "        'lifetime' => 120,\n";
        $configContent .= "        'expire_on_close' => false,\n";
        $configContent .= "    ],\n\n";
        
        $configContent .= "    // 文件上传配置\n    'upload' => [\n";
        $configContent .= "        'path' => __DIR__ . '/../public/uploads/',\n";
        $configContent .= "        'max_size' => 2048,\n";
        $configContent .= "        'allowed_types' => ['jpg', 'jpeg', 'png', 'gif'],\n";
        $configContent .= "    ],\n\n";
        
        $configContent .= "    // 分页配置\n    'pagination' => [\n";
        $configContent .= "        'per_page' => 20,\n";
        $configContent .= "    ],\n";
        
        $configContent .= "];\n";
        
        $configFile = ROOT_PATH . '/config/config.php';
        
        if (file_put_contents($configFile, $configContent) === false) {
            return ['success' => false, 'message' => '配置文件生成失败！'];
        }
        
        return ['success' => true, 'message' => '配置文件生成成功！'];
    }

    /**
     * 创建安装锁文件
     */
    public function createInstallLock() {
        $lockFile = ROOT_PATH . '/config/installed.lock';
        $content = "安装时间: " . date('Y-m-d H:i:s') . "\n";
        $content .= "安装版本: 1.0.0\n";
        $content .= "=================================\n";
        
        if (file_put_contents($lockFile, $content) === false) {
            return ['success' => false, 'message' => '安装锁文件创建失败！'];
        }
        
        return ['success' => true, 'message' => '安装锁文件创建成功！'];
    }

    /**
     * 获取错误信息
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * 检查是否已安装
     */
    public static function isInstalled() {
        return file_exists(ROOT_PATH . '/config/installed.lock');
    }
}