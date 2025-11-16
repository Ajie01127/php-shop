<?php

namespace App\Models;

use Core\Model;

class MemberLevel extends Model
{
    protected $table = 'member_levels';

    /**
     * 获取所有会员等级
     */
    public function getAll()
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY level ASC";
        return $this->db->query($sql);
    }

    /**
     * 根据ID获取等级
     */
    public function getById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        $result = $this->db->query($sql, [$id]);
        return $result[0] ?? null;
    }

    /**
     * 根据等级数字获取等级
     */
    public function getByLevel($level)
    {
        $sql = "SELECT * FROM {$this->table} WHERE level = ?";
        $result = $this->db->query($sql, [$level]);
        return $result[0] ?? null;
    }

    /**
     * 根据积分获取应有等级
     */
    public function getLevelByPoints($points)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE min_points <= ? 
                ORDER BY level DESC LIMIT 1";
        $result = $this->db->query($sql, [$points]);
        return $result[0] ?? null;
    }

    /**
     * 根据消费金额获取应有等级
     */
    public function getLevelByAmount($amount)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE min_amount <= ? 
                ORDER BY level DESC LIMIT 1";
        $result = $this->db->query($sql, [$amount]);
        return $result[0] ?? null;
    }

    /**
     * 获取用户应有的最高等级
     */
    public function getUserLevel($points, $amount)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE min_points <= ? OR min_amount <= ?
                ORDER BY level DESC LIMIT 1";
        $result = $this->db->query($sql, [$points, $amount]);
        return $result[0] ?? $this->getByLevel(1);
    }

    /**
     * 创建等级
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} 
                (level_name, level, min_points, min_amount, discount, 
                 icon, color, benefits, description, sort) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        return $this->db->insert($sql, [
            $data['level_name'],
            $data['level'],
            $data['min_points'] ?? 0,
            $data['min_amount'] ?? 0,
            $data['discount'] ?? 1.00,
            $data['icon'] ?? null,
            $data['color'] ?? null,
            json_encode($data['benefits'] ?? [], JSON_UNESCAPED_UNICODE),
            $data['description'] ?? null,
            $data['sort'] ?? 0,
        ]);
    }

    /**
     * 更新等级
     */
    public function updateLevel($id, $data)
    {
        $sql = "UPDATE {$this->table} SET 
                level_name = ?, level = ?, min_points = ?, min_amount = ?,
                discount = ?, icon = ?, color = ?, benefits = ?, 
                description = ?, sort = ?
                WHERE id = ?";

        return $this->db->update($sql, [
            $data['level_name'],
            $data['level'],
            $data['min_points'],
            $data['min_amount'],
            $data['discount'],
            $data['icon'],
            $data['color'],
            json_encode($data['benefits'] ?? [], JSON_UNESCAPED_UNICODE),
            $data['description'],
            $data['sort'],
            $id
        ]);
    }

    /**
     * 删除等级
     */
    public function deleteLevel($id)
    {
        // 检查是否有用户使用此等级
        $sql = "SELECT COUNT(*) as count FROM users WHERE member_level_id = ?";
        $result = $this->db->query($sql, [$id]);
        
        if ($result[0]['count'] > 0) {
            return ['success' => false, 'message' => '有用户正在使用此等级，无法删除'];
        }

        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $this->db->delete($sql, [$id]);

        return ['success' => true, 'message' => '删除成功'];
    }

    /**
     * 获取等级统计
     */
    public function getLevelStats($levelId)
    {
        $sql = "SELECT COUNT(*) as user_count FROM users WHERE member_level_id = ?";
        $result = $this->db->query($sql, [$levelId]);
        return $result[0] ?? null;
    }

    /**
     * 更新用户等级
     */
    public function updateUserLevel($userId)
    {
        // 获取用户信息
        $sql = "SELECT points, total_amount FROM users WHERE id = ?";
        $result = $this->db->query($sql, [$userId]);
        
        if (empty($result)) {
            return false;
        }

        $user = $result[0];
        
        // 获取应有的等级
        $level = $this->getUserLevel($user['points'], $user['total_amount']);
        
        if (!$level) {
            return false;
        }

        // 更新用户等级
        $sql = "UPDATE users SET member_level_id = ?, level_updated_at = NOW() WHERE id = ?";
        return $this->db->update($sql, [$level['id'], $userId]);
    }

    /**
     * 批量更新所有用户等级
     */
    public function updateAllUserLevels()
    {
        $sql = "SELECT id, points, total_amount FROM users";
        $users = $this->db->query($sql);

        $count = 0;
        foreach ($users as $user) {
            $level = $this->getUserLevel($user['points'], $user['total_amount']);
            if ($level) {
                $updateSql = "UPDATE users SET member_level_id = ?, level_updated_at = NOW() WHERE id = ?";
                $this->db->update($updateSql, [$level['id'], $user['id']]);
                $count++;
            }
        }

        return $count;
    }
}
