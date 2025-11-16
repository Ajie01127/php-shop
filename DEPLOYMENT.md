# 私域商城系统部署教程

本教程详细说明私域商城系统的完整部署流程，包括环境配置、安装步骤、安全配置等内容。

## 📋 部署前准备

### 1. 服务器要求

#### 硬件要求
- **CPU**：2核以上
- **内存**：4GB以上
- **硬盘**：50GB以上可用空间
- **带宽**：5Mbps以上

#### 软件要求
- **操作系统**：CentOS 7+/Ubuntu 18.04+/Windows Server 2012+
- **Web服务器**：Apache 2.4+/Nginx 1.18+
- **PHP**：8.0 或更高版本
- **MySQL**：8.0 或更高版本
- **其他**：SSH访问权限、FTP/SFTP工具

#### PHP扩展要求
```bash
# 必须的扩展
extension=pdo_mysql        # PDO MySQL支持
extension=mbstring         # 多字节字符串处理
extension=json             # JSON处理
extension=gd               # 图像处理
extension=curl             # HTTP请求
extension=openssl          # 加密和安全

# 推荐的扩展
extension=redis            # Redis缓存（可选）
extension=zip              # 压缩解压
extension=zlib             # 压缩支持
```

### 2. 域名和SSL证书
- 准备一个已备案的域名
- 申请SSL证书（推荐Let's Encrypt免费证书）
- 配置域名解析到服务器IP

## 🚀 快速部署方案

### 方案一：宝塔面板部署（推荐新手）

#### 1. 安装宝塔面板
```bash
# CentOS
wget -O install.sh http://download.bt.cn/install/install_6.0.sh && sh install.sh

# Ubuntu
wget -O install.sh http://download.bt.cn/install/install-ubuntu_6.0.sh && sudo bash install.sh
```

#### 2. 配置环境
1. 登录宝塔面板（http://服务器IP:8888）
2. 安装LNMP环境：
   - Nginx 1.20+
   - MySQL 8.0+
   - PHP 8.0+（安装所需扩展）
   - phpMyAdmin（可选）

#### 3. 创建网站
1. 点击"网站" → "添加站点"
2. 填写域名信息
3. 设置根目录为项目目录
4. 开启SSL证书（选择Let's Encrypt）

#### 4. 上传项目文件
1. 使用宝塔文件管理器上传项目文件
2. 或使用FTP工具上传
3. 确保文件权限正确

### 方案二：命令行部署（推荐专业用户）

#### 1. 环境安装
```bash
# Ubuntu/Debian
sudo apt update
sudo apt install nginx mysql-server php8.0 php8.0-fpm php8.0-mysql \
                 php8.0-mbstring php8.0-xml php8.0-curl php8.0-gd \
                 php8.0-zip php8.0-bcmath

# CentOS/RHEL
sudo yum install epel-release
sudo yum install nginx mysql-server php8.0 php8.0-fpm php8.0-mysqlnd \
                 php8.0-mbstring php8.0-xml php8.0-curl php8.0-gd \
                 php8.0-zip php8.0-bcmath
```

#### 2. 配置MySQL
```bash
# 启动MySQL
sudo systemctl start mysqld
sudo systemctl enable mysqld

# 安全配置
sudo mysql_secure_installation

# 创建数据库
mysql -u root -p
CREATE DATABASE private_mall CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'mall_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON private_mall.* TO 'mall_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

#### 3. 配置Nginx
创建配置文件 `/etc/nginx/sites-available/private-mall`：
```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/private-mall;
    index index.php index.html index.htm;

    # 强制HTTPS重定向（生产环境启用）
    # return 301 https://$server_name$request_uri;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # 安全设置
    location ~ /\.ht {
        deny all;
    }

    # 静态资源缓存
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}

# HTTPS配置（生产环境启用）
# server {
#     listen 443 ssl http2;
#     server_name yourdomain.com;
#     
#     ssl_certificate /path/to/your/cert.pem;
#     ssl_certificate_key /path/to/your/private.key;
#     
#     # SSL安全配置
#     ssl_protocols TLSv1.2 TLSv1.3;
#     ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512;
#     ssl_prefer_server_ciphers off;
#     
#     # 其他配置同上
# }
```

启用站点：
```bash
sudo ln -s /etc/nginx/sites-available/private-mall /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

## 🔧 系统安装

### 方法一：在线安装（推荐）

1. **上传文件**
   ```bash
   # 上传所有文件到Web目录
   cd /var/www/
   git clone https://github.com/your-repo/private-mall.git
   # 或使用FTP上传
   ```

2. **设置权限**
   ```bash
   chown -R www-data:www-data /var/www/private-mall
   chmod -R 755 /var/www/private-mall
   chmod 644 /var/www/private-mall/config/config.php
   ```

3. **运行安装向导**
   - 访问 `http://yourdomain.com/install.php`
   - 按照向导完成安装
   - 输入数据库信息和管理员账户

4. **完成安装**
   - 删除安装文件：`rm install.php`
   - 验证安装：访问前台和后台

### 方法二：手动安装

1. **导入数据库**
   ```bash
   mysql -u username -p private_mall < database/schema.sql
   ```

2. **配置环境**
   ```bash
   # 复制配置文件
   cp config/config.php.example config/config.php
   
   # 编辑配置文件
   nano config/config.php
   ```

3. **修改配置**
   ```php
   return [
       'database' => [
           'host' => 'localhost',
           'port' => '3306',
           'database' => 'private_mall',
           'username' => 'mall_user',
           'password' => 'your_password',
           'charset' => 'utf8mb4',
           'collation' => 'utf8mb4_unicode_ci',
           'prefix' => '',
       ],
       // 其他配置...
   ];
   ```

## 🔒 安全配置

### 1. 文件权限设置
```bash
# 正确的权限设置
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod 755 public/uploads/
chmod 644 config/config.php

# 禁止访问敏感文件
echo "Deny from all" > config/.htaccess
echo "Deny from all" > database/.htaccess
```

### 2. 数据库安全
```sql
-- 创建专用数据库用户
CREATE USER 'mall_app'@'localhost' IDENTIFIED BY 'strong_password';
GRANT SELECT, INSERT, UPDATE, DELETE ON private_mall.* TO 'mall_app'@'localhost';

-- 定期备份
mysqldump -u root -p private_mall > backup_$(date +%Y%m%d).sql
```

### 3. Web服务器安全
```nginx
# Nginx安全配置
server_tokens off;
add_header X-Frame-Options "SAMEORIGIN";
add_header X-Content-Type-Options "nosniff";
add_header X-XSS-Protection "1; mode=block";

# 防止直接访问敏感文件
location ~* \.(env|log|sql)$ {
    deny all;
}
```

### 4. PHP安全配置
```ini
; php.ini安全设置
expose_php = Off
display_errors = Off
log_errors = On
allow_url_fopen = Off
allow_url_include = Off
```

## 🛠 系统优化

### 1. 性能优化
```nginx
# Nginx性能优化
gzip on;
gzip_types text/plain text/css application/json application/javascript text/xml application/xml application/xml+rss text/javascript;

# PHP-FPM优化
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
```

### 2. 缓存配置
```php
// 启用Redis缓存（可选）
'cache' => [
    'driver' => 'redis',
    'host' => '127.0.0.1',
    'port' => 6379,
    'password' => '',
    'database' => 0,
],
```

### 3. 图片优化
- 使用WebP格式图片
- 启用图片懒加载
- 配置CDN加速

## 📊 监控和维护

### 1. 系统监控
```bash
# 安装监控工具
sudo apt install htop iotop nethogs

# 日志监控
tail -f /var/log/nginx/access.log
tail -f /var/log/nginx/error.log
```

### 2. 备份策略
```bash
#!/bin/bash
# 数据库备份脚本
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backup/mall"

# 备份数据库
mysqldump -u root -p private_mall > $BACKUP_DIR/db_$DATE.sql

# 备份文件
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/private-mall

# 清理旧备份（保留7天）
find $BACKUP_DIR -name "*.sql" -mtime +7 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete
```

### 3. 定期维护
- 每周清理临时文件
- 每月优化数据库表
- 定期检查安全更新

## 🐛 故障排除

### 常见问题

#### 1. 安装失败
- **问题**：数据库连接失败
- **解决**：检查数据库配置、用户权限、网络连接

#### 2. 页面空白
- **问题**：PHP错误未显示
- **解决**：检查PHP错误日志，开启调试模式

#### 3. 权限错误
- **问题**：文件写入失败
- **解决**：检查目录权限，确保Web服务器有写入权限

#### 4. 性能问题
- **问题**：页面加载缓慢
- **解决**：启用缓存，优化数据库查询，检查服务器负载

### 日志检查
```bash
# 检查错误日志
tail -f /var/log/nginx/error.log
tail -f /var/log/php8.0-fpm.log

# 检查系统日志
dmesg | tail
```

## 📞 技术支持

### 获取帮助
- 查看项目文档：`README.md`
- 检查常见问题：本项目文档
- 提交Issue：项目Issue页面

### 紧急联系方式
- 系统管理员：admin@yourdomain.com
- 技术支持：support@yourdomain.com

---

## 🔧 高级功能配置

### 微信小程序配置
- 修改 `config/config.php` 中的微信小程序设置
- 上传小程序代码到微信开发者工具
- 配置服务器域名和业务域名

### 短信服务配置
- 在腾讯云SMS控制台获取API密钥
- 配置短信模板和签名
- 修改 `app/Services/TencentSmsService.php` 中的配置

### 支付功能配置
- 申请微信支付商户号
- 配置支付证书和密钥
- 设置支付回调地址

## ⚡ 性能优化建议

### 数据库优化
- 为常用查询字段添加索引
- 定期清理日志和缓存表
- 使用数据库连接池

### 前端优化
- 启用Gzip压缩
- 使用CDN加速静态资源
- 优化图片和资源文件

### 缓存策略
- 配置Redis或Memcached缓存
- 使用浏览器缓存策略
- 实现页面静态化

## 📊 监控和维护

### 系统监控
- 设置性能监控指标
- 配置错误日志收集
- 实施用户行为分析

### 定期维护
- 每周备份数据库
- 每月检查系统更新
- 季度安全审计

## 🔒 安全加固

### 增强安全措施
- 安装完成后立即删除install.php文件
- 配置防火墙和访问控制
- 定期安全扫描和漏洞修复
- 使用强密码并定期更换
- 实施双因素认证
- 定期安全审计和渗透测试

**注意**：生产环境部署前请务必进行充分测试，确保系统稳定性和安全性。