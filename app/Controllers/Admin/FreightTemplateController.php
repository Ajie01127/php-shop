<?php

namespace App\Controllers\Admin;

use Core\Controller;
use App\Models\FreightTemplate;

class FreightTemplateController extends Controller
{
    private $templateModel;

    public function __construct()
    {
        parent::__construct();
        $this->templateModel = new FreightTemplate();
    }

    /**
     * 运费模板列表
     */
    public function index()
    {
        $templates = $this->templateModel->getAll();

        // 获取每个模板的使用统计
        foreach ($templates as &$template) {
            $sql = "SELECT COUNT(*) as count FROM products WHERE freight_template_id = ?";
            $result = $this->db->query($sql, [$template['id']]);
            $template['product_count'] = $result[0]['count'] ?? 0;
        }

        return $this->view('admin/freight/index', [
            'templates' => $templates
        ]);
    }

    /**
     * 创建模板页面
     */
    public function create()
    {
        return $this->view('admin/freight/form', [
            'action' => 'create'
        ]);
    }

    /**
     * 保存模板
     */
    public function store()
    {
        $data = [
            'name' => $_POST['name'] ?? '',
            'type' => $_POST['type'] ?? 'weight',
            'is_free' => isset($_POST['is_free']) ? 1 : 0,
            'free_amount' => $_POST['free_amount'] ?? 0,
            'free_num' => $_POST['free_num'] ?? 0,
            'sort' => $_POST['sort'] ?? 0,
            'is_default' => isset($_POST['is_default']) ? 1 : 0,
        ];

        if (empty($data['name'])) {
            $this->error('请输入模板名称');
            return;
        }

        $templateId = $this->templateModel->create($data);

        if ($templateId) {
            // 保存运费详情
            if (isset($_POST['details'])) {
                $this->saveDetails($templateId, $_POST['details']);
            }
            
            $this->success('创建成功', ['id' => $templateId]);
        } else {
            $this->error('创建失败');
        }
    }

    /**
     * 编辑模板
     */
    public function edit()
    {
        $id = $_GET['id'] ?? 0;
        $template = $this->templateModel->getById($id);

        if (!$template) {
            $this->error('模板不存在');
            return;
        }

        $details = $this->templateModel->getDetails($id);

        return $this->view('admin/freight/form', [
            'action' => 'edit',
            'template' => $template,
            'details' => $details
        ]);
    }

    /**
     * 更新模板
     */
    public function update()
    {
        $id = $_POST['id'] ?? 0;
        
        $data = [
            'name' => $_POST['name'] ?? '',
            'type' => $_POST['type'] ?? 'weight',
            'is_free' => isset($_POST['is_free']) ? 1 : 0,
            'free_amount' => $_POST['free_amount'] ?? 0,
            'free_num' => $_POST['free_num'] ?? 0,
            'sort' => $_POST['sort'] ?? 0,
            'is_default' => isset($_POST['is_default']) ? 1 : 0,
        ];

        if ($this->templateModel->updateTemplate($id, $data)) {
            // 删除旧的详情
            $sql = "DELETE FROM freight_template_details WHERE template_id = ?";
            $this->db->delete($sql, [$id]);
            
            // 保存新的详情
            if (isset($_POST['details'])) {
                $this->saveDetails($id, $_POST['details']);
            }
            
            $this->success('更新成功');
        } else {
            $this->error('更新失败');
        }
    }

    /**
     * 删除模板
     */
    public function delete()
    {
        $id = $_POST['id'] ?? 0;
        $result = $this->templateModel->deleteTemplate($id);

        if ($result['success']) {
            $this->success($result['message']);
        } else {
            $this->error($result['message']);
        }
    }

    /**
     * 保存运费详情
     */
    private function saveDetails($templateId, $details)
    {
        if (!is_array($details)) {
            return;
        }

        foreach ($details as $detail) {
            $sql = "INSERT INTO freight_template_details 
                    (template_id, area_type, area_codes, first_unit, first_price, 
                     continue_unit, continue_price) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";

            $this->db->insert($sql, [
                $templateId,
                $detail['area_type'] ?? 'all',
                json_encode($detail['area_codes'] ?? [], JSON_UNESCAPED_UNICODE),
                $detail['first_unit'] ?? 1,
                $detail['first_price'] ?? 0,
                $detail['continue_unit'] ?? 1,
                $detail['continue_price'] ?? 0,
            ]);
        }
    }

    /**
     * 计算运费接口
     */
    public function calculate()
    {
        $templateId = $_POST['template_id'] ?? 0;
        $weight = $_POST['weight'] ?? 0;
        $volume = $_POST['volume'] ?? 0;
        $quantity = $_POST['quantity'] ?? 1;
        $amount = $_POST['amount'] ?? 0;
        $provinceCode = $_POST['province_code'] ?? null;

        $freight = $this->templateModel->calculateFreight(
            $templateId,
            $weight,
            $volume,
            $quantity,
            $amount,
            $provinceCode
        );

        $this->success('计算成功', ['freight' => $freight]);
    }
}
