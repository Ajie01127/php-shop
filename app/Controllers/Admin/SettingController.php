<?php

namespace App\Controllers\Admin;

use Core\Controller;
use App\Models\SiteSetting;

class SettingController extends Controller
{
    private $settingModel;

    public function __construct()
    {
        parent::__construct();
        $this->settingModel = new SiteSetting();
    }

    /**
     * 网站设置首页
     */
    public function index()
    {
        $group = $_GET['group'] ?? 'basic';
        
        $settings = $this->settingModel->getAll($group);
        $groups = $this->settingModel->getGroups();

        return $this->view('admin/settings/index', [
            'settings' => $settings,
            'groups' => $groups,
            'currentGroup' => $group,
        ]);
    }

    /**
     * 更新配置
     */
    public function update()
    {
        $data = $_POST;
        
        // 移除非配置字段
        unset($data['_token']);

        if ($this->settingModel->updateMultiple($data)) {
            // 清除缓存
            SiteSetting::clearCache();
            
            $this->success('保存成功');
        } else {
            $this->error('保存失败');
        }
    }

    /**
     * 获取单个配置
     */
    public function get()
    {
        $key = $_GET['key'] ?? '';
        
        if (empty($key)) {
            $this->error('参数错误');
            return;
        }

        $value = $this->settingModel->get($key);
        $this->success('获取成功', ['value' => $value]);
    }

    /**
     * 批量获取配置
     */
    public function getMultiple()
    {
        $keys = $_GET['keys'] ?? '';
        
        if (empty($keys)) {
            $settings = $this->settingModel->getMultiple();
        } else {
            $keys = explode(',', $keys);
            $settings = $this->settingModel->getMultiple($keys);
        }

        $this->success('获取成功', $settings);
    }

    /**
     * 创建新配置
     */
    public function create()
    {
        return $this->view('admin/settings/create');
    }

    /**
     * 保存新配置
     */
    public function store()
    {
        $data = [
            'key' => $_POST['key'] ?? '',
            'value' => $_POST['value'] ?? '',
            'type' => $_POST['type'] ?? 'text',
            'group' => $_POST['group'] ?? 'other',
            'label' => $_POST['label'] ?? '',
            'description' => $_POST['description'] ?? '',
            'sort' => $_POST['sort'] ?? 0,
        ];

        // 验证必填字段
        if (empty($data['key']) || empty($data['label'])) {
            $this->error('请填写完整信息');
            return;
        }

        // 检查key是否已存在
        if ($this->settingModel->exists($data['key'])) {
            $this->error('配置键已存在');
            return;
        }

        if ($this->settingModel->create($data)) {
            SiteSetting::clearCache();
            $this->success('创建成功');
        } else {
            $this->error('创建失败');
        }
    }

    /**
     * 删除配置
     */
    public function delete()
    {
        $key = $_POST['key'] ?? '';

        if (empty($key)) {
            $this->error('参数错误');
            return;
        }

        if ($this->settingModel->delete($key)) {
            SiteSetting::clearCache();
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }

    /**
     * 上传图片
     */
    public function uploadImage()
    {
        if (!isset($_FILES['image'])) {
            $this->error('请选择图片');
            return;
        }

        $file = $_FILES['image'];
        
        // 检查上传错误
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->error('上传失败');
            return;
        }

        // 获取上传设置
        $maxSize = $this->settingModel->get('upload_max_size', 5) * 1024 * 1024; // MB转字节
        $allowExt = explode(',', $this->settingModel->get('upload_allow_ext', 'jpg,jpeg,png,gif'));
        
        // 验证文件大小
        if ($file['size'] > $maxSize) {
            $this->error('文件大小超过限制');
            return;
        }

        // 验证文件类型
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowExt)) {
            $this->error('不支持的文件类型');
            return;
        }

        // 生成文件名
        $fileName = date('YmdHis') . '_' . uniqid() . '.' . $ext;
        $uploadPath = $this->settingModel->get('upload_path', '/uploads/');
        $fullPath = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . $uploadPath;
        
        // 创建目录
        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0755, true);
        }

        // 移动文件
        if (move_uploaded_file($file['tmp_name'], $fullPath . $fileName)) {
            $url = $uploadPath . $fileName;
            $this->success('上传成功', ['url' => $url]);
        } else {
            $this->error('上传失败');
        }
    }

    /**
     * 清除缓存
     */
    public function clearCache()
    {
        SiteSetting::clearCache();
        $this->success('缓存清除成功');
    }

    /**
     * 导出配置
     */
    public function export()
    {
        $settings = $this->settingModel->getAll();
        
        $data = [];
        foreach ($settings as $setting) {
            $data[] = [
                'key' => $setting['key'],
                'value' => $setting['value'],
                'type' => $setting['type'],
                'group' => $setting['group'],
                'label' => $setting['label'],
                'description' => $setting['description'],
            ];
        }

        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="site_settings_' . date('YmdHis') . '.json"');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * 导入配置
     */
    public function import()
    {
        if (!isset($_FILES['file'])) {
            $this->error('请选择文件');
            return;
        }

        $file = $_FILES['file'];
        $content = file_get_contents($file['tmp_name']);
        $data = json_decode($content, true);

        if (!$data) {
            $this->error('文件格式错误');
            return;
        }

        $count = 0;
        foreach ($data as $setting) {
            if (isset($setting['key']) && isset($setting['value'])) {
                if ($this->settingModel->exists($setting['key'])) {
                    $this->settingModel->set($setting['key'], $setting['value']);
                } else {
                    $this->settingModel->create($setting);
                }
                $count++;
            }
        }

        SiteSetting::clearCache();
        $this->success("成功导入 {$count} 条配置");
    }
}
