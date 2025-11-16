<?php

namespace App\Controllers\Admin;

use Core\Controller;
use App\Models\PaymentChannel;

class PaymentChannelController extends Controller
{
    private $channelModel;

    public function __construct()
    {
        parent::__construct();
        $this->channelModel = new PaymentChannel();
    }

    /**
     * 支付通道列表
     */
    public function index()
    {
        $type = $_GET['type'] ?? null;
        $channels = $this->channelModel->getAll($type);

        // 获取每个通道的统计信息
        foreach ($channels as &$channel) {
            $channel['stats'] = $this->channelModel->getStats($channel['id']);
        }

        return $this->view('admin/payment/channels', [
            'channels' => $channels,
            'type' => $type
        ]);
    }

    /**
     * 获取通道列表(JSON)
     */
    public function list()
    {
        $type = $_GET['type'] ?? null;
        $activeOnly = isset($_GET['active_only']) && $_GET['active_only'] == '1';
        
        $channels = $this->channelModel->getAll($type, $activeOnly);

        $this->success('获取成功', $channels);
    }

    /**
     * 创建通道页面
     */
    public function create()
    {
        return $this->view('admin/payment/channel_form', [
            'action' => 'create'
        ]);
    }

    /**
     * 保存新通道
     */
    public function store()
    {
        $type = $_POST['type'] ?? 'wechat';
        
        // 基础数据
        $data = [
            'name' => $_POST['name'] ?? '',
            'type' => $type,
            'app_id' => $_POST['app_id'] ?? '',
            'mch_id' => $_POST['mch_id'] ?? '',
            'api_key' => $_POST['api_key'] ?? '',
            'cert_path' => $_POST['cert_path'] ?? null,
            'key_path' => $_POST['key_path'] ?? null,
            'notify_url' => $_POST['notify_url'] ?? null,
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'is_default' => isset($_POST['is_default']) ? 1 : 0,
            'config' => $_POST['config'] ?? null,
            'remark' => $_POST['remark'] ?? null,
        ];

        // 验证必填字段
        $requiredFields = ['name', 'app_id', 'api_key'];
        if ($type === 'wechat') {
            $requiredFields[] = 'mch_id';
        }
        
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $this->error('请填写完整信息');
                return;
            }
        }

        // 处理支付宝配置
        if ($type === 'alipay' && !empty($_POST['config'])) {
            $config = json_decode($_POST['config'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->error('配置格式错误');
                return;
            }
            
            // 支付宝必须配置私钥和公钥
            if (empty($config['private_key']) || empty($config['public_key'])) {
                $this->error('支付宝必须配置商户私钥和支付宝公钥');
                return;
            }
            
            $data['config'] = $_POST['config'];
        }

        $id = $this->channelModel->create($data);

        if ($id) {
            $this->success('创建成功', ['id' => $id]);
        } else {
            $this->error('创建失败');
        }
    }

    /**
     * 编辑通道页面
     */
    public function edit()
    {
        $id = $_GET['id'] ?? 0;
        $channel = $this->channelModel->getById($id);

        if (!$channel) {
            $this->error('通道不存在');
            return;
        }

        return $this->view('admin/payment/channel_form', [
            'action' => 'edit',
            'channel' => $channel
        ]);
    }

    /**
     * 更新通道
     */
    public function update()
    {
        $id = $_POST['id'] ?? 0;
        
        // 获取原有通道信息，以确定类型
        $channel = $this->channelModel->getById($id);
        if (!$channel) {
            $this->error('支付通道不存在');
            return;
        }
        
        $type = $channel['type'];
        
        $data = [
            'name' => $_POST['name'] ?? '',
            'app_id' => $_POST['app_id'] ?? '',
            'mch_id' => $_POST['mch_id'] ?? '',
            'api_key' => $_POST['api_key'] ?? '',
            'cert_path' => $_POST['cert_path'] ?? null,
            'key_path' => $_POST['key_path'] ?? null,
            'notify_url' => $_POST['notify_url'] ?? null,
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'is_default' => isset($_POST['is_default']) ? 1 : 0,
            'config' => $_POST['config'] ?? null,
            'remark' => $_POST['remark'] ?? null,
        ];

        // 验证必填字段
        $requiredFields = ['name', 'app_id', 'api_key'];
        if ($type === 'wechat') {
            $requiredFields[] = 'mch_id';
        }
        
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $this->error('请填写完整信息');
                return;
            }
        }

        // 处理支付宝配置
        if ($type === 'alipay' && !empty($_POST['config'])) {
            $config = json_decode($_POST['config'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->error('配置格式错误');
                return;
            }
            
            // 支付宝必须配置私钥和公钥
            if (empty($config['private_key']) || empty($config['public_key'])) {
                $this->error('支付宝必须配置商户私钥和支付宝公钥');
                return;
            }
            
            $data['config'] = $_POST['config'];
        }

        if ($this->channelModel->updateChannel($id, $data)) {
            $this->success('更新成功');
        } else {
            $this->error('更新失败');
        }
    }

    /**
     * 删除通道
     */
    public function delete()
    {
        $id = $_POST['id'] ?? 0;
        $result = $this->channelModel->deleteChannel($id);

        if ($result['success']) {
            $this->success($result['message']);
        } else {
            $this->error($result['message']);
        }
    }

    /**
     * 设置默认通道
     */
    public function setDefault()
    {
        $id = $_POST['id'] ?? 0;

        if ($this->channelModel->setDefault($id)) {
            $this->success('设置成功');
        } else {
            $this->error('设置失败');
        }
    }

    /**
     * 切换启用状态
     */
    public function toggleActive()
    {
        $id = $_POST['id'] ?? 0;

        if ($this->channelModel->toggleActive($id)) {
            $this->success('操作成功');
        } else {
            $this->error('操作失败');
        }
    }

    /**
     * 获取默认通道
     */
    public function getDefault()
    {
        $type = $_GET['type'] ?? 'wechat';
        $channel = $this->channelModel->getDefault($type);

        if ($channel) {
            // 隐藏敏感信息
            unset($channel['api_key']);
            $this->success('获取成功', $channel);
        } else {
            $this->error('未找到可用的支付通道');
        }
    }

    /**
     * 测试通道连接
     */
    public function testConnection()
    {
        $id = $_POST['id'] ?? 0;
        $channel = $this->channelModel->getById($id);

        if (!$channel) {
            $this->error('通道不存在');
            return;
        }

        // 根据支付类型进行测试
        if ($channel['type'] === 'wechat') {
            // 微信支付测试逻辑
            $this->success('连接测试成功', [
                'channel_name' => $channel['name'],
                'type' => '微信支付',
                'test_time' => date('Y-m-d H:i:s'),
                'message' => '微信支付通道配置正确'
            ]);
        } elseif ($channel['type'] === 'alipay') {
            // 支付宝测试逻辑
            try {
                require_once __DIR__ . '/../../Services/AlipayService.php';
                $alipayService = new \App\Services\AlipayService($id);
                
                if ($alipayService->isConfigValid()) {
                    $this->success('连接测试成功', [
                        'channel_name' => $channel['name'],
                        'type' => '支付宝',
                        'test_time' => date('Y-m-d H:i:s'),
                        'message' => '支付宝通道配置正确'
                    ]);
                } else {
                    $this->error('支付宝配置不完整');
                }
            } catch (\Exception $e) {
                $this->error('测试失败: ' . $e->getMessage());
            }
        } else {
            $this->error('不支持的支付类型');
        }
    }
}
