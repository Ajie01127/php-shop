<?php

namespace Core;

/**
 * 基础模型类
 */
class Model {
    protected $table;
    protected $primaryKey = 'id';
    protected $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * 查询所有记录
     */
    public function all($orderBy = null) {
        $sql = "SELECT * FROM {$this->table}";
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        return $this->db->select($sql);
    }
    
    /**
     * 根据ID查询
     */
    public function find($id) {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";
        return $this->db->selectOne($sql, [$id]);
    }
    
    /**
     * 条件查询
     */
    public function where($column, $operator, $value = null) {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }
        
        $sql = "SELECT * FROM {$this->table} WHERE {$column} {$operator} ?";
        return $this->db->select($sql, [$value]);
    }
    
    /**
     * 单条记录查询
     */
    public function first($column, $operator, $value = null) {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }
        
        $sql = "SELECT * FROM {$this->table} WHERE {$column} {$operator} ? LIMIT 1";
        return $this->db->selectOne($sql, [$value]);
    }
    
    /**
     * 插入记录
     */
    public function create($data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        return $this->db->insert($sql, array_values($data));
    }
    
    /**
     * 更新记录
     */
    public function update($id, $data) {
        $sets = [];
        $values = [];
        foreach ($data as $column => $value) {
            $sets[] = "{$column} = ?";
            $values[] = $value;
        }
        $values[] = $id;
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $sets) . " WHERE {$this->primaryKey} = ?";
        return $this->db->update($sql, $values);
    }
    
    /**
     * 删除记录
     */
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        return $this->db->delete($sql, [$id]);
    }
    
    /**
     * 统计记录数
     */
    public function count($where = null) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        if ($where) {
            $sql .= " WHERE {$where}";
        }
        $result = $this->db->selectOne($sql);
        return $result['count'] ?? 0;
    }
    
    /**
     * 分页查询
     */
    public function paginate($page = 1, $perPage = 20, $where = null) {
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT * FROM {$this->table}";
        $params = [];
        
        if ($where) {
            $sql .= " WHERE {$where}";
        }
        
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        
        return [
            'data' => $this->db->select($sql, $params),
            'total' => $this->count($where),
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($this->count($where) / $perPage),
        ];
    }
}
