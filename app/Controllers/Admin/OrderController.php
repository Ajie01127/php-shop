<?php

namespace App\Controllers\Admin;

use Core\Controller;
use App\Models\Order;

class OrderController extends Controller {
    private $orderModel;
    
    public function __construct() {
        $this->orderModel = new Order();
    }
    
    /**
     * 订单列表
     */
    public function index() {
        $page = get('page', 1);
        $status = get('status');
        $orderNo = get('order_no');
        
        $where = '1=1';
        if ($status) {
            $where .= " AND status = '{$status}'";
        }
        if ($orderNo) {
            $where .= " AND order_no LIKE '%{$orderNo}%'";
        }
        
        $result = $this->orderModel->paginate($page, 20, $where);
        
        $this->view('admin/orders/index', [
            'orders' => $result['data'],
            'pagination' => $result,
            'status' => $status,
            'orderNo' => $orderNo,
        ]);
    }
    
    /**
     * 订单详情
     */
    public function show($id) {
        $order = $this->orderModel->getOrderDetail($id);
        
        if (!$order) {
            flash('error', '订单不存在');
            $this->redirect('/admin/orders');
        }
        
        $this->view('admin/orders/show', ['order' => $order]);
    }
    
    /**
     * 更新订单状态
     */
    public function updateStatus($id) {
        $status = post('status');
        
        $allowedStatus = ['pending', 'paid', 'shipped', 'completed', 'cancelled'];
        if (!in_array($status, $allowedStatus)) {
            $this->error('无效的状态');
        }
        
        $result = $this->orderModel->updateStatus($id, $status);
        
        if ($result) {
            $this->success('状态更新成功');
        } else {
            $this->error('状态更新失败');
        }
    }
}
