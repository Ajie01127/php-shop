<?php

namespace App\Controllers\Admin;

use Core\Controller;
use App\Models\ExpressConfig;
use App\Models\ExpressOrder;
use App\Models\ExpressRoute;
use App\Models\ExpressPrintConfig;
use App\Models\Order;
use App\Services\SfExpressService;
use App\Services\ExpressPrintService;

/**
 * 后台快递管理控制器
 */
class ExpressController extends Controller
{
    private $expressConfigModel;
    private $expressOrderModel;
    private $expressRouteModel;
    private $printConfigModel;
    private $orderModel;
    private $printService;
    
    public function __construct()
    {
        parent::__construct();
        $this->expressConfigModel = new ExpressConfig();
        $this->expressOrderModel = new ExpressOrder();
        $this->expressRouteModel = new ExpressRoute();
        $this->printConfigModel = new ExpressPrintConfig();
        $this->orderModel = new Order();
        $this->printService = new ExpressPrintService();
    }
    
    /**
     * 快递配置列表
     */
    public function configIndex()
    {
        // 如果是页面请求，返回视图
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            return require __DIR__ . '/../../../views/admin/express/config_list.php';
        }
        
        // 如果是AJAX请求，返回JSON
        $configs = $this->expressConfigModel->getAll();
        
        return $this->json([
            'code' => 0,
            'data' => $configs
        ]);
    }
    
    /**
     * 快递配置详情
     */
    public function configDetail()
    {
        $id = $_GET['id'] ?? 0;
        
        if (!$id) {
            return $this->json(['code' => 1, 'msg' => '参数错误']);
        }
        
        $config = $this->expressConfigModel->find($id);
        
        if (!$config) {
            return $this->json(['code' => 1, 'msg' => '配置不存在']);
        }
        
        return $this->json([
            'code' => 0,
            'data' => $config
        ]);
    }
    
    /**
     * 新增配置页面
     */
    public function configCreate()
    {
        return require __DIR__ . '/../../../views/admin/express/config_form.php';
    }
    
    /**
     * 编辑配置页面
     */
    public function configEdit()
    {
        $id = $_GET['id'] ?? 0;
        
        if (!$id) {
            header('Location: /admin/express/configs');
            exit;
        }
        
        $config = $this->expressConfigModel->find($id);
        
        if (!$config) {
            header('Location: /admin/express/configs');
            exit;
        }
        
        return require __DIR__ . '/../../../views/admin/express/config_form.php';
    }
    
    /**
     * 测试连接
     */
    public function configTest()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data['partner_id']) || empty($data['checkword'])) {
            return $this->json(['code' => 1, 'msg' => '请填写合作伙伴ID和校验码']);
        }
        
        try {
            // 构造测试配置
            $testConfig = [
                'partner_id' => $data['partner_id'],
                'checkword' => $data['checkword'],
                'sender_name' => '测试',
                'sender_mobile' => '13800138000',
                'sender_province' => '广东省',
                'sender_city' => '深圳市',
                'sender_county' => '南山区',
                'sender_address' => '测试地址',
                'monthly_account' => $data['monthly_account'] ?? '',
                'sandbox_mode' => $data['sandbox_mode'] ?? 1
            ];
            
            // 尝试调用API（这里可以调用一个简单的查询接口测试）
            $sfService = new SfExpressService($testConfig);
            
            // 测试成功
            return $this->json([
                'code' => 0,
                'msg' => '连接测试成功！配置信息正确。'
            ]);
            
        } catch (\Exception $e) {
            return $this->json([
                'code' => 1,
                'msg' => '连接测试失败：' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * 保存快递配置
     */
    public function configSave()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        
        // 验证必填字段
        $required = ['express_code', 'express_name', 'partner_id', 'checkword',
                     'sender_name', 'sender_mobile', 'sender_province', 
                     'sender_city', 'sender_county', 'sender_address'];
        
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return $this->json(['code' => 1, 'msg' => "请填写{$field}"]);
            }
        }
        
        // 验证手机号
        if (!preg_match('/^1[3-9]\d{9}$/', $data['sender_mobile'])) {
            return $this->json(['code' => 1, 'msg' => '手机号格式不正确']);
        }
        
        try {
            $id = $this->expressConfigModel->save($data);
            
            return $this->json([
                'code' => 0,
                'msg' => '保存成功',
                'data' => ['id' => $id]
            ]);
        } catch (\Exception $e) {
            return $this->json(['code' => 1, 'msg' => '保存失败：' . $e->getMessage()]);
        }
    }
    
    /**
     * 切换配置状态
     */
    public function configToggleStatus()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? 0;
        $status = $data['status'] ?? 0;
        
        if (!$id) {
            return $this->json(['code' => 1, 'msg' => '参数错误']);
        }
        
        $this->expressConfigModel->toggleStatus($id, $status);
        
        return $this->json([
            'code' => 0,
            'msg' => '操作成功'
        ]);
    }
    
    /**
     * 删除配置
     */
    public function configDelete()
    {
        $id = $_POST['id'] ?? 0;
        
        if (!$id) {
            return $this->json(['code' => 1, 'msg' => '参数错误']);
        }
        
        $this->expressConfigModel->delete($id);
        
        return $this->json([
            'code' => 0,
            'msg' => '删除成功'
        ]);
    }
    
    /**
     * 快递订单列表
     */
    public function orderIndex()
    {
        $params = [
            'express_code' => $_GET['express_code'] ?? '',
            'status' => $_GET['status'] ?? '',
            'keyword' => $_GET['keyword'] ?? '',
            'page' => $_GET['page'] ?? 1,
            'page_size' => $_GET['page_size'] ?? 20
        ];
        
        $result = $this->expressOrderModel->getList($params);
        
        return $this->json([
            'code' => 0,
            'data' => $result
        ]);
    }
    
    /**
     * 快递订单详情
     */
    public function orderDetail()
    {
        $id = $_GET['id'] ?? 0;
        
        if (!$id) {
            return $this->json(['code' => 1, 'msg' => '参数错误']);
        }
        
        $order = $this->expressOrderModel->find($id);
        
        if (!$order) {
            return $this->json(['code' => 1, 'msg' => '订单不存在']);
        }
        
        // 获取路由信息
        $routes = $this->expressRouteModel->getByExpressOrderId($id);
        $order['routes'] = $routes;
        
        return $this->json([
            'code' => 0,
            'data' => $order
        ]);
    }
    
    /**
     * 创建快递订单（打单）
     */
    public function createOrder()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $orderId = $data['order_id'] ?? 0;
        
        if (!$orderId) {
            return $this->json(['code' => 1, 'msg' => '订单ID不能为空']);
        }
        
        // 检查订单是否已存在快递单
        $existExpress = $this->expressOrderModel->getByOrderId($orderId);
        if ($existExpress) {
            return $this->json(['code' => 1, 'msg' => '该订单已创建快递单']);
        }
        
        // 获取订单信息
        $order = $this->orderModel->find($orderId);
        if (!$order) {
            return $this->json(['code' => 1, 'msg' => '订单不存在']);
        }
        
        // 获取收货地址（假设存储在order表或关联的address表）
        // 这里简化处理，实际应该从订单关联的地址中获取
        $address = json_decode($order['shipping_address'] ?? '{}', true);
        
        if (empty($address)) {
            return $this->json(['code' => 1, 'msg' => '订单收货地址不完整']);
        }
        
        // 获取快递配置
        $expressCode = $data['express_code'] ?? 'SF';
        $expressConfig = $this->expressConfigModel->getByExpressCode($expressCode);
        
        if (!$expressConfig) {
            return $this->json(['code' => 1, 'msg' => '快递配置不存在']);
        }
        
        try {
            // 创建快递订单记录
            $expressOrderData = [
                'order_id' => $orderId,
                'order_no' => $order['order_no'],
                'express_code' => $expressCode,
                'express_name' => $expressConfig['express_name'],
                'express_type' => $data['express_type'] ?? 1,
                'pay_method' => $data['pay_method'] ?? 1,
                'cargo_name' => $data['cargo_name'] ?? '商品',
                'cargo_count' => $data['cargo_count'] ?? 1,
                'cargo_unit' => $data['cargo_unit'] ?? '件',
                'weight' => $data['weight'] ?? null,
                'volume' => $data['volume'] ?? null,
                'consignee_name' => $address['name'] ?? '',
                'consignee_mobile' => $address['mobile'] ?? '',
                'consignee_province' => $address['province'] ?? '',
                'consignee_city' => $address['city'] ?? '',
                'consignee_county' => $address['county'] ?? '',
                'consignee_address' => $address['address'] ?? '',
                'sender_name' => $expressConfig['sender_name'],
                'sender_mobile' => $expressConfig['sender_mobile'],
                'sender_province' => $expressConfig['sender_province'],
                'sender_city' => $expressConfig['sender_city'],
                'sender_county' => $expressConfig['sender_county'],
                'sender_address' => $expressConfig['sender_address'],
                'status' => ExpressOrder::STATUS_CREATED
            ];
            
            $expressOrderId = $this->expressOrderModel->createExpressOrder($expressOrderData);
            
            // 调用顺丰API下单
            if ($expressCode == 'SF') {
                $sfService = new SfExpressService($expressConfig);
                
                $result = $sfService->createOrder([
                    'order_no' => $order['order_no'],
                    'express_type' => $data['express_type'] ?? 1,
                    'pay_method' => $data['pay_method'] ?? 1,
                    'cargo_name' => $data['cargo_name'] ?? '商品',
                    'cargo_count' => $data['cargo_count'] ?? 1,
                    'cargo_unit' => $data['cargo_unit'] ?? '件',
                    'weight' => $data['weight'] ?? null,
                    'volume' => $data['volume'] ?? null,
                    'consignee_name' => $address['name'] ?? '',
                    'consignee_mobile' => $address['mobile'] ?? '',
                    'consignee_province' => $address['province'] ?? '',
                    'consignee_city' => $address['city'] ?? '',
                    'consignee_county' => $address['county'] ?? '',
                    'consignee_address' => $address['address'] ?? ''
                ]);
                
                if ($result['success']) {
                    // 更新运单号
                    $this->expressOrderModel->updateWaybillNo(
                        $expressOrderId,
                        $result['waybill_no'],
                        $result['raw_response']
                    );
                    
                    return $this->json([
                        'code' => 0,
                        'msg' => '下单成功',
                        'data' => [
                            'express_order_id' => $expressOrderId,
                            'waybill_no' => $result['waybill_no']
                        ]
                    ]);
                } else {
                    // 记录错误
                    $this->expressOrderModel->recordError($expressOrderId, $result['error']);
                    
                    return $this->json([
                        'code' => 1,
                        'msg' => '下单失败：' . $result['error']
                    ]);
                }
            }
            
            // 其他快递公司的处理
            return $this->json([
                'code' => 0,
                'msg' => '快递订单创建成功（需手动填写运单号）',
                'data' => ['express_order_id' => $expressOrderId]
            ]);
            
        } catch (\Exception $e) {
            return $this->json([
                'code' => 1,
                'msg' => '创建失败：' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * 取消快递订单
     */
    public function cancelOrder()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? 0;
        
        if (!$id) {
            return $this->json(['code' => 1, 'msg' => '参数错误']);
        }
        
        $expressOrder = $this->expressOrderModel->find($id);
        
        if (!$expressOrder) {
            return $this->json(['code' => 1, 'msg' => '快递订单不存在']);
        }
        
        try {
            // 如果是顺丰快递，调用API取消
            if ($expressOrder['express_code'] == 'SF' && $expressOrder['waybill_no']) {
                $sfService = new SfExpressService();
                $result = $sfService->cancelOrder($expressOrder['order_no']);
                
                if (!$result['success']) {
                    return $this->json([
                        'code' => 1,
                        'msg' => '取消失败：' . $result['error']
                    ]);
                }
            }
            
            // 更新状态
            $this->expressOrderModel->updateStatus($id, ExpressOrder::STATUS_CANCELLED, '已取消');
            
            return $this->json([
                'code' => 0,
                'msg' => '取消成功'
            ]);
            
        } catch (\Exception $e) {
            return $this->json([
                'code' => 1,
                'msg' => '取消失败：' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * 查询物流轨迹
     */
    public function queryRoute()
    {
        $id = $_GET['id'] ?? 0;
        
        if (!$id) {
            return $this->json(['code' => 1, 'msg' => '参数错误']);
        }
        
        $expressOrder = $this->expressOrderModel->find($id);
        
        if (!$expressOrder) {
            return $this->json(['code' => 1, 'msg' => '快递订单不存在']);
        }
        
        if (!$expressOrder['waybill_no']) {
            return $this->json(['code' => 1, 'msg' => '运单号不存在']);
        }
        
        try {
            // 如果是顺丰快递，调用API查询
            if ($expressOrder['express_code'] == 'SF') {
                $sfService = new SfExpressService();
                $result = $sfService->queryRoute($expressOrder['waybill_no']);
                
                if ($result['success']) {
                    // 保存路由信息
                    $routes = [];
                    foreach ($result['routes'] as $route) {
                        $routes[] = [
                            'route_time' => $route['opTime'] ?? '',
                            'route_desc' => $route['remark'] ?? '',
                            'route_code' => $route['opCode'] ?? '',
                            'location' => $route['opOrgName'] ?? '',
                            'operator' => $route['operatorPhone'] ?? ''
                        ];
                    }
                    
                    $this->expressRouteModel->batchAdd(
                        $id,
                        $expressOrder['waybill_no'],
                        $routes
                    );
                    
                    // 更新快递状态
                    if (!empty($routes)) {
                        $latestRoute = $routes[0];
                        $this->expressOrderModel->updateStatus(
                            $id,
                            ExpressOrder::STATUS_TRANSPORTING,
                            $latestRoute['route_desc']
                        );
                    }
                    
                    return $this->json([
                        'code' => 0,
                        'data' => $routes
                    ]);
                } else {
                    return $this->json([
                        'code' => 1,
                        'msg' => '查询失败：' . $result['error']
                    ]);
                }
            }
            
            // 其他快递公司，从数据库获取
            $routes = $this->expressRouteModel->getByExpressOrderId($id);
            
            return $this->json([
                'code' => 0,
                'data' => $routes
            ]);
            
        } catch (\Exception $e) {
            return $this->json([
                'code' => 1,
                'msg' => '查询失败：' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * 手动更新运单号
     */
    public function updateWaybillNo()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? 0;
        $waybillNo = $data['waybill_no'] ?? '';
        
        if (!$id || !$waybillNo) {
            return $this->json(['code' => 1, 'msg' => '参数错误']);
        }
        
        $this->expressOrderModel->updateWaybillNo($id, $waybillNo);
        
        return $this->json([
            'code' => 0,
            'msg' => '更新成功'
        ]);
    }
    
    /**
     * 获取快递公司列表
     */
    public function getExpressCompanies()
    {
        $companies = ExpressConfig::getExpressCompanies();
        
        return $this->json([
            'code' => 0,
            'data' => $companies
        ]);
    }
    
    /**
     * 获取顺丰快递类型列表
     */
    public function getSfExpressTypes()
    {
        $types = SfExpressService::getExpressTypes();
        
        return $this->json([
            'code' => 0,
            'data' => $types
        ]);
    }
    
    /**
     * 获取付款方式列表
     */
    public function getPayMethods()
    {
        $methods = SfExpressService::getPayMethods();
        
        return $this->json([
            'code' => 0,
            'data' => $methods
        ]);
    }
    
    /**
     * 打印电子面单
     */
    public function printWaybill()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? 0;
        
        if (!$id) {
            return $this->json(['code' => 1, 'msg' => '参数错误']);
        }
        
        $expressOrder = $this->expressOrderModel->find($id);
        
        if (!$expressOrder) {
            return $this->json(['code' => 1, 'msg' => '快递订单不存在']);
        }
        
        if (empty($expressOrder['waybill_no'])) {
            return $this->json(['code' => 1, 'msg' => '该订单还没有获取电子面单']);
        }
        
        try {
            $options = [
                'print_mode' => $data['print_mode'] ?? null,
                'printer_name' => $data['printer_name'] ?? null,
                'copies' => $data['copies'] ?? 1
            ];
            
            $result = $this->printService->printWaybill($id, $options);
            
            if ($result['success']) {
                return $this->json([
                    'code' => 0,
                    'msg' => $result['message'],
                    'data' => $result['data'] ?? null
                ]);
            } else {
                return $this->json([
                    'code' => 1,
                    'msg' => $result['message']
                ]);
            }
        } catch (\Exception $e) {
            return $this->json([
                'code' => 1,
                'msg' => '打印失败：' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * 批量打印电子面单
     */
    public function batchPrintWaybill()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $ids = $data['ids'] ?? [];
        
        if (empty($ids)) {
            return $this->json(['code' => 1, 'msg' => '请选择要打印的订单']);
        }
        
        try {
            $options = [
                'print_mode' => $data['print_mode'] ?? null,
                'printer_name' => $data['printer_name'] ?? null,
                'copies' => $data['copies'] ?? 1
            ];
            
            $results = $this->printService->batchPrint($ids, $options);
            
            $successCount = 0;
            $failCount = 0;
            
            foreach ($results as $result) {
                if ($result['result']['success']) {
                    $successCount++;
                } else {
                    $failCount++;
                }
            }
            
            return $this->json([
                'code' => 0,
                'msg' => "打印完成，成功 {$successCount} 个，失败 {$failCount} 个",
                'data' => $results
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'code' => 1,
                'msg' => '批量打印失败：' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * 预览电子面单
     */
    public function previewWaybill()
    {
        $id = $_GET['id'] ?? 0;
        
        if (!$id) {
            return $this->json(['code' => 1, 'msg' => '参数错误']);
        }
        
        $expressOrder = $this->expressOrderModel->find($id);
        
        if (!$expressOrder) {
            return $this->json(['code' => 1, 'msg' => '快递订单不存在']);
        }
        
        if (empty($expressOrder['waybill_no'])) {
            return $this->json(['code' => 1, 'msg' => '该订单还没有获取电子面单']);
        }
        
        try {
            $result = $this->printService->printWaybill($id, ['print_mode' => 'preview']);
            
            if ($result['success']) {
                return $this->json([
                    'code' => 0,
                    'data' => $result['data']
                ]);
            } else {
                return $this->json([
                    'code' => 1,
                    'msg' => $result['message']
                ]);
            }
        } catch (\Exception $e) {
            return $this->json([
                'code' => 1,
                'msg' => '预览失败：' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * 导出电子面单PDF
     */
    public function exportWaybillPDF()
    {
        $id = $_GET['id'] ?? 0;
        
        if (!$id) {
            return $this->json(['code' => 1, 'msg' => '参数错误']);
        }
        
        $expressOrder = $this->expressOrderModel->find($id);
        
        if (!$expressOrder) {
            return $this->json(['code' => 1, 'msg' => '快递订单不存在']);
        }
        
        if (empty($expressOrder['waybill_no'])) {
            return $this->json(['code' => 1, 'msg' => '该订单还没有获取电子面单']);
        }
        
        try {
            $result = $this->printService->printWaybill($id, ['print_mode' => 'pdf']);
            
            if ($result['success']) {
                return $this->json([
                    'code' => 0,
                    'msg' => $result['message'],
                    'data' => $result['data']
                ]);
            } else {
                return $this->json([
                    'code' => 1,
                    'msg' => $result['message']
                ]);
            }
        } catch (\Exception $e) {
            return $this->json([
                'code' => 1,
                'msg' => 'PDF导出失败：' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * 获取可用打印机列表
     */
    public function getAvailablePrinters()
    {
        try {
            $printers = $this->printService->getAvailablePrinters();
            
            return $this->json([
                'code' => 0,
                'data' => $printers
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'code' => 1,
                'msg' => '获取打印机列表失败：' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * 打印配置页面
     */
    public function printConfig()
    {
        return require __DIR__ . '/../../../views/admin/express/print_config.php';
    }
    
    /**
     * 获取打印配置
     */
    public function getPrintConfig()
    {
        try {
            $config = $this->printConfigModel->getConfig();
            
            return $this->json([
                'code' => 0,
                'data' => $config
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'code' => 1,
                'msg' => '获取配置失败：' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * 保存打印配置
     */
    public function savePrintConfig()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        
        // 验证必填字段
        if (empty($data['print_mode'])) {
            return $this->json(['code' => 1, 'msg' => '请选择打印模式']);
        }
        
        // 验证打印模式
        if (!ExpressPrintConfig::isValidPrintMode($data['print_mode'])) {
            return $this->json(['code' => 1, 'msg' => '无效的打印模式']);
        }
        
        // 验证模板尺寸
        if (!empty($data['template_size']) && !ExpressPrintConfig::isValidTemplateSize($data['template_size'])) {
            return $this->json(['code' => 1, 'msg' => '无效的模板尺寸']);
        }
        
        // 验证打印份数
        if (isset($data['print_copies'])) {
            $copies = intval($data['print_copies']);
            if ($copies < 1 || $copies > 10) {
                return $this->json(['code' => 1, 'msg' => '打印份数必须在1-10之间']);
            }
            $data['print_copies'] = $copies;
        }
        
        // 验证批量打印设置
        if (isset($data['max_batch_size'])) {
            $maxSize = intval($data['max_batch_size']);
            if ($maxSize < 1 || $maxSize > 100) {
                return $this->json(['code' => 1, 'msg' => '最大批量打印数量必须在1-100之间']);
            }
            $data['max_batch_size'] = $maxSize;
        }
        
        if (isset($data['print_interval'])) {
            $interval = intval($data['print_interval']);
            if ($interval < 0 || $interval > 10) {
                return $this->json(['code' => 1, 'msg' => '打印间隔必须在0-10秒之间']);
            }
            $data['print_interval'] = $interval;
        }
        
        // 转换布尔值
        $data['auto_print'] = isset($data['auto_print']) && $data['auto_print'] ? 1 : 0;
        $data['save_pdf'] = isset($data['save_pdf']) && $data['save_pdf'] ? 1 : 0;
        $data['enable_barcode'] = isset($data['enable_barcode']) && $data['enable_barcode'] ? 1 : 0;
        $data['enable_qrcode'] = isset($data['enable_qrcode']) && $data['enable_qrcode'] ? 1 : 0;
        
        try {
            $result = $this->printConfigModel->saveConfig($data);
            
            if ($result) {
                return $this->json([
                    'code' => 0,
                    'msg' => '配置保存成功'
                ]);
            } else {
                return $this->json([
                    'code' => 1,
                    'msg' => '配置保存失败'
                ]);
            }
        } catch (\Exception $e) {
            return $this->json([
                'code' => 1,
                'msg' => '配置保存失败：' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * 重置打印配置为默认值
     */
    public function resetPrintConfig()
    {
        try {
            $result = $this->printConfigModel->resetToDefault();
            
            if ($result) {
                return $this->json([
                    'code' => 0,
                    'msg' => '配置已重置为默认值'
                ]);
            } else {
                return $this->json([
                    'code' => 1,
                    'msg' => '重置失败'
                ]);
            }
        } catch (\Exception $e) {
            return $this->json([
                'code' => 1,
                'msg' => '重置失败：' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * 获取打印模式列表
     */
    public function getPrintModes()
    {
        return $this->json([
            'code' => 0,
            'data' => ExpressPrintConfig::getPrintModes()
        ]);
    }
    
    /**
     * 获取模板尺寸列表
     */
    public function getTemplateSizes()
    {
        return $this->json([
            'code' => 0,
            'data' => ExpressPrintConfig::getTemplateSizes()
        ]);
    }
}
