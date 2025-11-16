<?php

namespace App\Models;

use Core\Model;

class SiteSetting extends Model
{
    protected $table = 'site_settings';

    /**
     * 获取所有配置
     */
    public function getAll($group = null)
    {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];

        if ($group) {
            $sql .= " WHERE `group` = ?";
            $params[] = $group;
        }

        $sql .= " ORDER BY `group`, sort ASC, id ASC";

        return $this->db->query($sql, $params);
    }

    /**
     * 获取配置分组
     */
    public function getGroups()
    {
        $sql = "SELECT DISTINCT `group` FROM {$this->table} ORDER BY `group`";
        $result = $this->db->query($sql);
        
        $groups = [
            'basic' => '基本信息',
            'contact' => '联系方式',
            'mall' => '商城设置',
            'order' => '订单设置',
            'upload' => '上传设置',
            'social' => '社交媒体',
            'sms' => '短信设置',
            'miniprogram' => '小程序设置',
            'other' => '其他设置',
        ];

        return $groups;
    }

    /**
     * 根据key获取配置值
     */
    public function get($key, $default = null)
    {
        $sql = "SELECT value FROM {$this->table} WHERE `key` = ?";
        $result = $this->db->query($sql, [$key]);

        if (empty($result)) {
            return $default;
        }

        return $result[0]['value'] ?? $default;
    }

    /**
     * 批量获取配置（返回关联数组）
     */
    public function getMultiple($keys = [])
    {
        if (empty($keys)) {
            $sql = "SELECT `key`, value FROM {$this->table}";
            $result = $this->db->query($sql);
        } else {
            $placeholders = str_repeat('?,', count($keys) - 1) . '?';
            $sql = "SELECT `key`, value FROM {$this->table} WHERE `key` IN ($placeholders)";
            $result = $this->db->query($sql, $keys);
        }

        $settings = [];
        foreach ($result as $row) {
            $settings[$row['key']] = $row['value'];
        }

        return $settings;
    }

    /**
     * 设置配置值
     */
    public function set($key, $value)
    {
        $sql = "UPDATE {$this->table} SET value = ? WHERE `key` = ?";
        return $this->db->update($sql, [$value, $key]);
    }

    /**
     * 批量更新配置
     */
    public function updateMultiple($data)
    {
        $this->db->beginTransaction();

        try {
            foreach ($data as $key => $value) {
                $this->set($key, $value);
            }
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * 创建配置
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} 
                (`key`, value, type, `group`, label, description, sort) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        return $this->db->insert($sql, [
            $data['key'],
            $data['value'] ?? '',
            $data['type'] ?? 'text',
            $data['group'] ?? 'other',
            $data['label'] ?? '',
            $data['description'] ?? '',
            $data['sort'] ?? 0,
        ]);
    }

    /**
     * 删除配置
     */
    public function delete($key)
    {
        $sql = "DELETE FROM {$this->table} WHERE `key` = ?";
        return $this->db->delete($sql, [$key]);
    }

    /**
     * 获取配置详情
     */
    public function getByKey($key)
    {
        $sql = "SELECT * FROM {$this->table} WHERE `key` = ?";
        $result = $this->db->query($sql, [$key]);
        return $result[0] ?? null;
    }

    /**
     * 检查配置是否存在
     */
    public function exists($key)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE `key` = ?";
        $result = $this->db->query($sql, [$key]);
        return $result[0]['count'] > 0;
    }

    /**
     * 获取缓存配置（可扩展为Redis缓存）
     */
    public static function cache()
    {
        static $cache = null;

        if ($cache === null) {
            $model = new self();
            $cache = $model->getMultiple();
        }

        return $cache;
    }

    /**
     * 清除缓存
     */
    public static function clearCache()
    {
        // 如果使用Redis等缓存，在这里清除
        // 目前使用静态变量，重新加载页面即可
    }
}
