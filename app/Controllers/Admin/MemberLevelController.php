<?php

namespace App\Controllers\Admin;

use Core\Controller;
use App\Models\MemberLevel;

class MemberLevelController extends Controller
{
    private $levelModel;

    public function __construct()
    {
        parent::__construct();
        $this->levelModel = new MemberLevel();
    }

    /**
     * 会员等级列表
     */
    public function index()
    {
        $levels = $this->levelModel->getAll();

        // 获取每个等级的用户数
        foreach ($levels as &$level) {
            $stats = $this->levelModel->getLevelStats($level['id']);
            $level['user_count'] = $stats['user_count'] ?? 0;
            $level['benefits'] = json_decode($level['benefits'], true);
        }

        return $this->view('admin/member/levels', [
            'levels' => $levels
        ]);
    }

    /**
     * 创建等级
     */
    public function create()
    {
        return $this->view('admin/member/level_form', [
            'action' => 'create'
        ]);
    }

    /**
     * 保存等级
     */
    public function store()
    {
        $data = [
            'level_name' => $_POST['level_name'] ?? '',
            'level' => $_POST['level'] ?? 1,
            'min_points' => $_POST['min_points'] ?? 0,
            'min_amount' => $_POST['min_amount'] ?? 0,
            'discount' => $_POST['discount'] ?? 1.00,
            'icon' => $_POST['icon'] ?? null,
            'color' => $_POST['color'] ?? '#999999',
            'benefits' => isset($_POST['benefits']) ? explode(',', $_POST['benefits']) : [],
            'description' => $_POST['description'] ?? null,
            'sort' => $_POST['sort'] ?? 0,
        ];

        if (empty($data['level_name'])) {
            $this->error('请输入等级名称');
            return;
        }

        $id = $this->levelModel->create($data);

        if ($id) {
            $this->success('创建成功', ['id' => $id]);
        } else {
            $this->error('创建失败');
        }
    }

    /**
     * 编辑等级
     */
    public function edit()
    {
        $id = $_GET['id'] ?? 0;
        $level = $this->levelModel->getById($id);

        if (!$level) {
            $this->error('等级不存在');
            return;
        }

        $level['benefits'] = json_decode($level['benefits'], true);

        return $this->view('admin/member/level_form', [
            'action' => 'edit',
            'level' => $level
        ]);
    }

    /**
     * 更新等级
     */
    public function update()
    {
        $id = $_POST['id'] ?? 0;
        
        $data = [
            'level_name' => $_POST['level_name'] ?? '',
            'level' => $_POST['level'] ?? 1,
            'min_points' => $_POST['min_points'] ?? 0,
            'min_amount' => $_POST['min_amount'] ?? 0,
            'discount' => $_POST['discount'] ?? 1.00,
            'icon' => $_POST['icon'] ?? null,
            'color' => $_POST['color'] ?? '#999999',
            'benefits' => isset($_POST['benefits']) ? explode(',', $_POST['benefits']) : [],
            'description' => $_POST['description'] ?? null,
            'sort' => $_POST['sort'] ?? 0,
        ];

        if ($this->levelModel->updateLevel($id, $data)) {
            $this->success('更新成功');
        } else {
            $this->error('更新失败');
        }
    }

    /**
     * 删除等级
     */
    public function delete()
    {
        $id = $_POST['id'] ?? 0;
        $result = $this->levelModel->deleteLevel($id);

        if ($result['success']) {
            $this->success($result['message']);
        } else {
            $this->error($result['message']);
        }
    }

    /**
     * 更新所有用户等级
     */
    public function updateAllLevels()
    {
        $count = $this->levelModel->updateAllUserLevels();
        $this->success("成功更新 {$count} 个用户的等级");
    }
}
