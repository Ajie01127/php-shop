<?php

namespace App\Models;

use Core\Model;
use PDO;

/**
 * 快递面单打印配置模型
 */
class ExpressPrintConfig extends Model
{
    protected $table = 'express_print_config';
    
    // 打印模式常量
    const MODE_LOCAL = 'local';
    const MODE_PDF = 'pdf';
    const MODE_IMAGE = 'image';
    const MODE_PREVIEW = 'preview';
    
    // 模板尺寸常量
    const SIZE_100x150 = '100,150';
    const SIZE_100x100 = '100,100';
    const SIZE_76x130 = '76,130';
    
    /**
     * 获取当前配置
     */
    public function getConfig()
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY id DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        $config = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($config) {
            // 解析自定义字段
            if (!empty($config['custom_fields'])) {
                $config['custom_fields'] = json_decode($config['custom_fields'], true);
            }
            
            return $config;
        }
        
        // 返回默认配置
        return $this->getDefaultConfig();
    }
    
    /**
     * 获取默认配置
     */
    public function getDefaultConfig()
    {
        return [
            'id' => 0,
            'print_mode' => self::MODE_PREVIEW,
            'printer_name' => null,
            'template_size' => self::SIZE_100x150,
            'print_copies' => 1,
            'auto_print' => 0,
            'save_pdf' => 1,
            'pdf_path' => '/storage/express/pdf/',
            'print_interval' => 1,
            'max_batch_size' => 50,
            'enable_barcode' => 1,
            'enable_qrcode' => 0,
            'custom_fields' => null
        ];
    }
    
    /**
     * 保存配置
     */
    public function saveConfig($data)
    {
        // 处理自定义字段
        if (isset($data['custom_fields']) && is_array($data['custom_fields'])) {
            $data['custom_fields'] = json_encode($data['custom_fields']);
        }
        
        // 检查是否已存在配置
        $existingConfig = $this->getConfig();
        
        if ($existingConfig && $existingConfig['id'] > 0) {
            // 更新现有配置
            return $this->updateConfig($existingConfig['id'], $data);
        } else {
            // 创建新配置
            return $this->createConfig($data);
        }
    }
    
    /**
     * 创建配置
     */
    private function createConfig($data)
    {
        $fields = [
            'print_mode' => $data['print_mode'] ?? self::MODE_PREVIEW,
            'printer_name' => $data['printer_name'] ?? null,
            'template_size' => $data['template_size'] ?? self::SIZE_100x150,
            'print_copies' => $data['print_copies'] ?? 1,
            'auto_print' => $data['auto_print'] ?? 0,
            'save_pdf' => $data['save_pdf'] ?? 1,
            'pdf_path' => $data['pdf_path'] ?? '/storage/express/pdf/',
            'print_interval' => $data['print_interval'] ?? 1,
            'max_batch_size' => $data['max_batch_size'] ?? 50,
            'enable_barcode' => $data['enable_barcode'] ?? 1,
            'enable_qrcode' => $data['enable_qrcode'] ?? 0,
            'custom_fields' => $data['custom_fields'] ?? null
        ];
        
        $columns = array_keys($fields);
        $placeholders = array_map(function($col) { return ":$col"; }, $columns);
        
        $sql = "INSERT INTO {$this->table} (" . implode(', ', $columns) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($fields as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        
        $stmt->execute();
        
        return $this->db->lastInsertId();
    }
    
    /**
     * 更新配置
     */
    private function updateConfig($id, $data)
    {
        $fields = [];
        $allowedFields = [
            'print_mode', 'printer_name', 'template_size', 'print_copies',
            'auto_print', 'save_pdf', 'pdf_path', 'print_interval',
            'max_batch_size', 'enable_barcode', 'enable_qrcode', 'custom_fields'
        ];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[$field] = $data[$field];
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $setParts = array_map(function($key) {
            return "$key = :$key";
        }, array_keys($fields));
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $setParts) . " WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        
        foreach ($fields as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        
        return $stmt->execute();
    }
    
    /**
     * 获取所有可用的打印模式
     */
    public static function getPrintModes()
    {
        return [
            self::MODE_LOCAL => '本地打印机',
            self::MODE_PDF => 'PDF导出',
            self::MODE_IMAGE => '图片导出',
            self::MODE_PREVIEW => '仅预览'
        ];
    }
    
    /**
     * 获取所有可用的模板尺寸
     */
    public static function getTemplateSizes()
    {
        return [
            self::SIZE_100x150 => '100mm x 150mm（标准快递面单）',
            self::SIZE_100x100 => '100mm x 100mm（正方形面单）',
            self::SIZE_76x130 => '76mm x 130mm（小尺寸面单）'
        ];
    }
    
    /**
     * 验证打印模式
     */
    public static function isValidPrintMode($mode)
    {
        return in_array($mode, [
            self::MODE_LOCAL,
            self::MODE_PDF,
            self::MODE_IMAGE,
            self::MODE_PREVIEW
        ]);
    }
    
    /**
     * 验证模板尺寸
     */
    public static function isValidTemplateSize($size)
    {
        return in_array($size, [
            self::SIZE_100x150,
            self::SIZE_100x100,
            self::SIZE_76x130
        ]);
    }
    
    /**
     * 解析模板尺寸为数组
     */
    public static function parseTemplateSize($size)
    {
        $parts = explode(',', $size);
        return [
            'width' => intval($parts[0] ?? 100),
            'height' => intval($parts[1] ?? 150)
        ];
    }
    
    /**
     * 重置为默认配置
     */
    public function resetToDefault()
    {
        $config = $this->getConfig();
        
        if ($config && $config['id'] > 0) {
            $defaultConfig = $this->getDefaultConfig();
            return $this->updateConfig($config['id'], $defaultConfig);
        }
        
        return false;
    }
}
