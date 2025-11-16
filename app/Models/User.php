<?php

namespace App\Models;

use Core\Database;

/**
 * 用户模型
 * 支持Web用户和小程序用户
 */
class User
{
    private $db;
    private $table = 'users';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * 根据ID查找用户
     */
    public function find($id)
    {
        return $this->db->selectOne("SELECT * FROM {$this->table} WHERE id = ?", [$id]);
    }

    /**
     * 根据字段查找用户
     */
    public function first($field, $value)
    {
        return $this->db->selectOne("SELECT * FROM {$this->table} WHERE {$field} = ?", [$value]);
    }

    /**
     * 创建用户
     */
    public function create($data)
    {
        return $this->db->insert($this->table, $data);
    }

    /**
     * 更新用户
     */
    public function update($id, $data)
    {
        return $this->db->update($this->table, $data, "id = ?", [$id]);
    }

    /**
     * 删除用户
     */
    public function delete($id)
    {
        return $this->db->delete($this->table, "id = ?", [$id]);
    }

    /**
     * 获取所有用户
     */
    public function all($limit = 100, $offset = 0)
    {
        return $this->db->select("SELECT * FROM {$this->table} ORDER BY created_at DESC LIMIT ? OFFSET ?", [$limit, $offset]);
    }

    /**
     * 根据openid查找小程序用户
     */
    public function findByOpenid($openid)
    {
        return $this->db->selectOne("SELECT * FROM {$this->table} WHERE openid = ?", [$openid]);
    }

    /**
     * 根据用户名和密码验证用户
     */
    public function validate($username, $password)
    {
        $user = $this->db->selectOne("SELECT * FROM {$this->table} WHERE username = ? AND status = 1", [$username]);
        
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        
        return false;
    }

    /**
     * 更新用户最后登录时间
     */
    public function updateLastLogin($userId)
    {
        return $this->db->update($this->table, [
            'last_login_at' => date('Y-m-d H:i:s')
        ], "id = ?", [$userId]);
    }

    /**
     * 获取小程序用户列表
     */
    public function getMiniProgramUsers($limit = 50, $offset = 0)
    {
        return $this->db->select("SELECT * FROM {$this->table} WHERE user_type = 'mini_program' ORDER BY created_at DESC LIMIT ? OFFSET ?", [$limit, $offset]);
    }

    /**
     * 获取Web用户列表
     */
    public function getWebUsers($limit = 50, $offset = 0)
    {
        return $this->db->select("SELECT * FROM {$this->table} WHERE user_type = 'web' ORDER BY created_at DESC LIMIT ? OFFSET ?", [$limit, $offset]);
    }

    /**
     * 统计用户数量
     */
    public function count($conditions = [])
    {
        $where = '1=1';
        $params = [];
        
        if (!empty($conditions)) {
            foreach ($conditions as $field => $value) {
                $where .= " AND {$field} = ?";
                $params[] = $value;
            }
        }
        
        $result = $this->db->selectOne("SELECT COUNT(*) as total FROM {$this->table} WHERE {$where}", $params);
        return $result['total'] ?? 0;
    }

    /**
     * 根据用户类型统计
     */
    public function countByType($userType)
    {
        return $this->count(['user_type' => $userType]);
    }

    /**
     * 根据状态统计
     */
    public function countByStatus($status)
    {
        return $this->count(['status' => $status]);
    }

    /**
     * 搜索用户
     */
    public function search($keyword, $limit = 50, $offset = 0)
    {
        $sql = "SELECT * FROM {$this->table} WHERE 
                username LIKE ? OR 
                email LIKE ? OR 
                nickname LIKE ? OR
                real_name LIKE ?
                ORDER BY created_at DESC LIMIT ? OFFSET ?";
        
        $params = [
            "%{$keyword}%",
            "%{$keyword}%", 
            "%{$keyword}%",
            "%{$keyword}%",
            $limit, 
            $offset
        ];
        
        return $this->db->select($sql, $params);
    }
}