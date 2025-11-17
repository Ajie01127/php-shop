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
        
        // 获取更新前的订单信息
        $order = $this->orderModel->getOrderDetail($id);
        if (!$order) {
            $this->error('订单不存在');
        }
        
        $result = $this->orderModel->updateStatus($id, $status);
        
        if ($result) {
            // 发送状态变更邮件通知
            $this->sendOrderStatusChangeEmail($order, $status);
            
            $this->success('状态更新成功');
        } else {
            $this->error('状态更新失败');
        }
    }
    
    /**
     * 发送订单状态变更邮件通知
     */
    private function sendOrderStatusChangeEmail($order, $newStatus)
    {
        try {
            $eventType = '';
            $subject = '';
            
            switch ($newStatus) {
                case 'shipped':
                    $eventType = 'order_shipped';
                    $subject = '订单已发货';
                    break;
                case 'completed':
                    $eventType = 'order_completed';
                    $subject = '订单已完成';
                    break;
                case 'cancelled':
                    $eventType = 'order_cancelled';
                    $subject = '订单已取消';
                    break;
                default:
                    return; // 其他状态不发送邮件
            }
            
            if (empty($eventType) || empty($order['user_email'])) {
                return;
            }
            
            $emailNotificationService = new \App\Services\EmailNotificationService();
            $emailNotificationService->triggerNotification($eventType, [
                'user_id' => $order['user_id'],
                'email' => $order['user_email'],
                'username' => $order['user_name'],
                'order_no' => $order['order_no'],
                'order_id' => $order['id'],
                'status' => $newStatus,
                'total_amount' => $order['total_amount'],
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
        } catch (\Exception $e) {
            // 邮件发送失败不影响主流程
            error_log('Order status change email failed: ' . $e->getMessage());
        }
    }
}
