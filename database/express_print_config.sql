-- 快递面单打印配置表
CREATE TABLE IF NOT EXISTS `express_print_config` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '配置ID',
  `print_mode` varchar(20) NOT NULL DEFAULT 'preview' COMMENT '打印模式: local/pdf/image/preview',
  `printer_name` varchar(255) DEFAULT NULL COMMENT '默认打印机名称',
  `template_size` varchar(20) NOT NULL DEFAULT '100,150' COMMENT '面单模板尺寸(mm): 100,150 / 100,100 / 76,130',
  `print_copies` tinyint NOT NULL DEFAULT '1' COMMENT '默认打印份数',
  `auto_print` tinyint(1) NOT NULL DEFAULT '0' COMMENT '自动打印: 0-否 1-是',
  `save_pdf` tinyint(1) NOT NULL DEFAULT '1' COMMENT '自动保存PDF: 0-否 1-是',
  `pdf_path` varchar(255) NOT NULL DEFAULT '/storage/express/pdf/' COMMENT 'PDF保存路径',
  `print_interval` int NOT NULL DEFAULT '1' COMMENT '批量打印间隔(秒)',
  `max_batch_size` int NOT NULL DEFAULT '50' COMMENT '最大批量打印数量',
  `enable_barcode` tinyint(1) NOT NULL DEFAULT '1' COMMENT '启用条形码: 0-否 1-是',
  `enable_qrcode` tinyint(1) NOT NULL DEFAULT '0' COMMENT '启用二维码: 0-否 1-是',
  `custom_fields` text COMMENT '自定义字段(JSON格式)',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='快递面单打印配置表';

-- 插入默认配置
INSERT INTO `express_print_config` (`print_mode`, `template_size`, `print_copies`, `auto_print`, `save_pdf`, `pdf_path`, `print_interval`, `max_batch_size`)
VALUES ('preview', '100,150', 1, 0, 1, '/storage/express/pdf/', 1, 50);
