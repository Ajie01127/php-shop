<?php

namespace App\Controllers;

use App\Services\AlipayService;
use App\Services\WechatPayService;

/**
 * 支付控制器 - 支持微信支付和支付宝
 */
class PaymentController
{
    /**
     * 创建支付订单
     */
    public function createOrder()
    {
        // 获取订单数据
        $orderData = [
            'order_no' => $_POST['order_no'] ?? '',
            'amount' => $_POST['amount'] ?? 0,
            'description' => $_POST['description'] ?? '商品支付',
            'type' => $_POST['pay_type'] ?? 'wechat', // wechat 或 alipay
            'channel_id' => $_POST['channel_id'] ?? null,
        ];

        // 验证必填字段
        if (empty($orderData['order_no']) || empty($orderData['amount']) || $orderData['amount'] <= 0) {
            return $this->jsonError('订单参数不完整');
        }

        try {
            if ($orderData['type'] === 'alipay') {
                // 支付宝支付
                $alipayService = new AlipayService($orderData['channel_id']);
                
                if (!$alipayService->isConfigValid()) {
                    return $this->jsonError('支付宝支付通道未配置');
                }

                $result = $alipayService->pagePay($orderData);
                
                if ($result['success']) {
                    return $this->jsonSuccess([
                        'pay_url' => $result['pay_url'],
                        'qr_code' => $alipayService->generateQrCode($result['pay_url']),
                        'type' => 'alipay'
                    ]);
                } else {
                    return $this->jsonError($result['message'] ?? '支付创建失败');
                }
            } else {
                // 微信支付
                $wechatService = new WechatPayService($orderData['channel_id']);
                
                if (!$wechatService->isConfigValid()) {
                    return $this->jsonError('微信支付通道未配置');
                }

                $result = $wechatService->createOrder($orderData);
                
                if ($result['success']) {
                    return $this->jsonSuccess([
                        'pay_url' => $result['pay_url'] ?? '',
                        'code_url' => $result['code_url'] ?? '',
                        'type' => 'wechat'
                    ]);
                } else {
                    return $this->jsonError($result['message'] ?? '支付创建失败');
                }
            }
        } catch (\Exception $e) {
            return $this->jsonError('系统错误: ' . $e->getMessage());
        }
    }

    /**
     * 查询订单状态
     */
    public function queryOrder()
    {
        $orderNo = $_POST['order_no'] ?? $_GET['order_no'] ?? '';
        $type = $_POST['type'] ?? $_GET['type'] ?? 'wechat';

        if (empty($orderNo)) {
            return $this->jsonError('订单号不能为空');
        }

        try {
            if ($type === 'alipay') {
                $alipayService = new AlipayService();
                $result = $alipayService->queryOrder($orderNo);
            } else {
                $wechatService = new WechatPayService();
                $result = $wechatService->queryOrder($orderNo);
            }

            if ($result['success']) {
                return $this->jsonSuccess($result);
            } else {
                return $this->jsonError($result['message'] ?? '查询失败');
            }
        } catch (\Exception $e) {
            return $this->jsonError('查询失败: ' . $e->getMessage());
        }
    }

    /**
     * 支付宝支付回调通知
     */
    public function alipayNotify()
    {
        try {
            // 获取支付宝回调数据
            $notifyData = $_POST;
            
            if (empty($notifyData)) {
                // 有些情况可能是GET方式
                $notifyData = $_GET;
            }

            if (empty($notifyData)) {
                file_put_contents('alipay_notify.log', 'Empty notify data
', FILE_APPEND);
                echo 'success';
                return;
            }

            // 记录回调数据
            file_put_contents('alipay_notify.log', 
                date('Y-m-d H:i:s') . ' - ' . json_encode($notifyData, JSON_UNESCAPED_UNICODE) . "
", 
                FILE_APPEND
            );

            $alipayService = new AlipayService();
            
            // 验证签名
            if (!$alipayService->verifyNotify($notifyData)) {
                file_put_contents('alipay_notify.log', 'Invalid signature
', FILE_APPEND);
                echo 'fail';
                return;
            }

            // 验证回调参数
            if (empty($notifyData['out_trade_no']) || empty($notifyData['trade_status'])) {
                file_put_contents('alipay_notify.log', 'Invalid notify data
', FILE_APPEND);
                echo 'fail';
                return;
            }

            $orderNo = $notifyData['out_trade_no'];
            $tradeStatus = $notifyData['trade_status'];
            $tradeNo = $notifyData['trade_no'] ?? '';
            $totalAmount = $notifyData['total_amount'] ?? 0;

            // 根据交易状态处理业务逻辑
            if ($tradeStatus === 'TRADE_SUCCESS' || $tradeStatus === 'TRADE_FINISHED') {
                // 支付成功，更新订单状态
                $this->handlePaymentSuccess($orderNo, $tradeNo, $totalAmount, 'alipay');
            } elseif ($tradeStatus === 'TRADE_CLOSED') {
                // 交易关闭，可能是退款或用户取消
                $this->handlePaymentClosed($orderNo, 'alipay');
            }

            // 返回success告诉支付宝已经成功处理
            echo 'success';
        } catch (\Exception $e) {
            file_put_contents('alipay_notify.log', 
                'Error: ' . $e->getMessage() . "
", 
                FILE_APPEND
            );
            echo 'fail';
        }
    }

    /**
     * 支付宝支付同步返回
     */
    public function alipayReturn()
    {
        try {
            $returnData = $_GET;
            
            // 记录返回数据
            file_put_contents('alipay_return.log', 
                date('Y-m-d H:i:s') . ' - ' . json_encode($returnData, JSON_UNESCAPED_UNICODE) . "\n", 
                FILE_APPEND
            );

            if (empty($returnData['out_trade_no'])) {
                // 重定向到失败页面
                header('Location: /payment/failed');
                exit;
            }

            $orderNo = $returnData['out_trade_no'];
            
            // 查询订单状态
            $alipayService = new AlipayService();
            $result = $alipayService->queryOrder($orderNo);

            if ($result['success'] && in_array($result['trade_status'], ['TRADE_SUCCESS', 'TRADE_FINISHED'])) {
                // 支付成功，重定向到成功页面
                header('Location: /payment/success?order_no=' . urlencode($orderNo));
            } else {
                // 支付失败或未完成
                header('Location: /payment/pending?order_no=' . urlencode($orderNo));
            }
            exit;
        } catch (\Exception $e) {
            file_put_contents('alipay_return.log', 
                'Error: ' . $e->getMessage() . "\n", 
                FILE_APPEND
            );
            header('Location: /payment/failed');
            exit;
        }
    }

    /**
     * 支付成功页面
     */
    public function success()
    {
        $orderNo = $_GET['order_no'] ?? '';
        
        // 显示支付成功页面
        echo '<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>支付成功</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
        .success { color: #52c41a; font-size: 24px; }
        .order-info { margin: 20px 0; }
        .btn { display: inline-block; padding: 10px 20px; background: #1890ff; color: white; text-decoration: none; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="success">✓ 支付成功</div>
    <div class="order-info">订单号：' . htmlspecialchars($orderNo) . '</div>
    <a href="/" class="btn">返回首页</a>
</body>
</html>';
    }

    /**
     * 支付失败页面
     */
    public function failed()
    {
        // 显示支付失败页面
        echo '<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>支付失败</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
        .failed { color: #f5222d; font-size: 24px; }
        .btn { display: inline-block; padding: 10px 20px; background: #1890ff; color: white; text-decoration: none; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="failed">✗ 支付失败</div>
    <p>支付过程中出现问题，请稍后重试或联系客服</p>
    <a href="/" class="btn">返回首页</a>
</body>
</html>';
    }

    /**
     * 支付处理中页面
     */
    public function pending()
    {
        $orderNo = $_GET['order_no'] ?? '';
        
        // 显示支付处理中页面
        echo '<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>支付处理中</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
        .pending { color: #faad14; font-size: 24px; }
        .order-info { margin: 20px 0; }
        .btn { display: inline-block; padding: 10px 20px; background: #1890ff; color: white; text-decoration: none; border-radius: 4px; }
        .refresh { margin-top: 20px; }
    </style>
</head>
<body>
    <div class="pending">⏳ 支付处理中</div>
    <div class="order-info">订单号：' . htmlspecialchars($orderNo) . '</div>
    <p>支付正在处理中，请稍后刷新页面查看结果</p>
    <a href="/" class="btn">返回首页</a>
    <div class="refresh">
        <button onclick="location.reload()" class="btn">刷新状态</button>
    </div>
    <script>
        // 5秒后自动刷新
        setTimeout(function() {
            location.reload();
        }, 5000);
    </script>
</body>
</html>';
    }

    /**
     * 支付成功页面
     */
    public function success()
    {
        $orderNo = $_GET['order_no'] ?? '';
        
        // 显示支付成功页面
        echo '<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>支付成功</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
        .success { color: #52c41a; font-size: 24px; }
        .order-info { margin: 20px 0; }
        .btn { display: inline-block; padding: 10px 20px; background: #1890ff; color: white; text-decoration: none; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="success">✓ 支付成功</div>
    <div class="order-info">订单号：' . htmlspecialchars($orderNo) . '</div>
    <a href="/" class="btn">返回首页</a>
</body>
</html>';
    }

    /**
     * 支付失败页面
     */
    public function failed()
    {
        // 显示支付失败页面
        echo '<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>支付失败</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
        .failed { color: #f5222d; font-size: 24px; }
        .btn { display: inline-block; padding: 10px 20px; background: #1890ff; color: white; text-decoration: none; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="failed">✗ 支付失败</div>
    <p>支付过程中出现问题，请稍后重试或联系客服</p>
    <a href="/" class="btn">返回首页</a>
</body>
</html>';
    }

    /**
     * 支付处理中页面
     */
    public function pending()
    {
        $orderNo = $_GET['order_no'] ?? '';
        
        // 显示支付处理中页面
        echo '<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>支付处理中</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
        .pending { color: #faad14; font-size: 24px; }
        .order-info { margin: 20px 0; }
        .btn { display: inline-block; padding: 10px 20px; background: #1890ff; color: white; text-decoration: none; border-radius: 4px; }
        .refresh { margin-top: 20px; }
    </style>
</head>
<body>
    <div class="pending">⏳ 支付处理中</div>
    <div class="order-info">订单号：' . htmlspecialchars($orderNo) . '</div>
    <p>支付正在处理中，请稍后刷新页面查看结果</p>
    <a href="/" class="btn">返回首页</a>
    <div class="refresh">
        <button onclick="location.reload()" class="btn">刷新状态</button>
    </div>
    <script>
        // 5秒后自动刷新
        setTimeout(function() {
            location.reload();
        }, 5000);
    </script>
</body>
</html>';
    }

    /**
     * 处理支付成功业务
     */
    private function handlePaymentSuccess($orderNo, $tradeNo, $amount, $payType)
    {
        // 这里实现具体的业务逻辑
        // 例如：更新订单状态、发送通知、记录日志等
        
        // 记录支付成功日志
        file_put_contents('payment_success.log', 
            date('Y-m-d H:i:s') . ' - ' . 
            'Order: ' . $orderNo . ', ' .
            'Trade: ' . $tradeNo . ', ' .
            'Amount: ' . $amount . ', ' .
            'Type: ' . $payType . "
", 
            FILE_APPEND
        );

        // TODO: 调用业务逻辑处理支付成功
        // $this->orderService->updateOrderStatus($orderNo, 'paid', $tradeNo, $amount);
    }

    /**
     * 处理支付关闭业务
     */
    private function handlePaymentClosed($orderNo, $payType)
    {
        // 记录支付关闭日志
        file_put_contents('payment_closed.log', 
            date('Y-m-d H:i:s') . ' - ' . 
            'Order: ' . $orderNo . ', ' .
            'Type: ' . $payType . "
", 
            FILE_APPEND
        );

        // TODO: 调用业务逻辑处理支付关闭
        // $this->orderService->updateOrderStatus($orderNo, 'closed');
    }

    /**
     * 返回JSON成功响应
     */
    private function jsonSuccess($data = [])
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'code' => 200,
            'message' => 'success',
            'data' => $data
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * 返回JSON错误响应
     */
    private function jsonError($message, $code = 400)
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'code' => $code,
            'message' => $message
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}