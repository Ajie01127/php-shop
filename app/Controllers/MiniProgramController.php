<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\SiteSetting;
use App\Services\WechatPayService;

/**
 * 小程序专用API控制器
 * 提供小程序端所有业务接口
 */
class MiniProgramController extends Controller
{
    private $db;
    private $userModel;
    private $productModel;
    private $orderModel;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->userModel = new User();
        $this->productModel = new Product();
        $this->orderModel = new Order();
        
        // 设置JSON响应头
        header('Content-Type: application/json; charset=utf-8');
    }
    
    /**
     * 小程序登录接口
     * @param string $code 微信登录code
     * @param string $iv 加密数据IV
     * @param string $encryptedData 加密数据
     */
    public function login()
    {
        // 检查小程序是否开启
        if (!$this->isMiniProgramEnabled()) {
            $this->jsonError('小程序功能未开启');
        }
        
        $code = post('code');
        $iv = post('iv');
        $encryptedData = post('encryptedData');
        $userInfo = post('userInfo'); // 基础用户信息
        
        if (!$code) {
            $this->jsonError('登录code不能为空');
        }
        
        // 调用微信API获取session_key和openid
        $wxResult = $this->getWxSession($code);
        
        if (!$wxResult || !isset($wxResult['openid'])) {
            $this->jsonError('微信登录失败');
        }
        
        $openid = $wxResult['openid'];
        $sessionKey = $wxResult['session_key'];
        
        // 查找或创建用户
        $user = $this->userModel->first('openid', $openid);
        
        if (!$user) {
            // 新用户注册
            if ($iv && $encryptedData) {
                // 解密用户信息
                $decryptedData = $this->decryptWxData($encryptedData, $iv, $sessionKey);
                if ($decryptedData) {
                    $userInfo = $decryptedData;
                }
            }
            
            $userData = [
                'openid' => $openid,
                'nickname' => $userInfo['nickName'] ?? '微信用户',
                'avatar' => $userInfo['avatarUrl'] ?? '',
                'gender' => $userInfo['gender'] ?? 0,
                'country' => $userInfo['country'] ?? '',
                'province' => $userInfo['province'] ?? '',
                'city' => $userInfo['city'] ?? '',
                'language' => $userInfo['language'] ?? 'zh_CN',
                'user_type' => 'mini_program',
                'status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ];
            
            $userId = $this->userModel->create($userData);
            $user = $this->userModel->find($userId);
        } else {
            // 更新用户信息
            if ($userInfo) {
                $updateData = [
                    'nickname' => $userInfo['nickName'] ?? $user['nickname'],
                    'avatar' => $userInfo['avatarUrl'] ?? $user['avatar'],
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
                $this->userModel->update($user['id'], $updateData);
            }
        }
        
        // 生成小程序token
        $token = $this->generateToken($user['id']);
        
        $this->jsonSuccess('登录成功', [
            'token' => $token,
            'userInfo' => [
                'id' => $user['id'],
                'openid' => $user['openid'],
                'nickname' => $user['nickname'],
                'avatar' => $user['avatar'],
                'mobile' => $user['mobile'],
                'gender' => $user['gender'],
                'points' => $user['points'],
            ],
        ]);
    }
    
    /**
     * 获取商品列表
     */
    public function products()
    {
        // 检查小程序是否开启
        if (!$this->isMiniProgramEnabled()) {
            $this->jsonError('小程序功能未开启');
        }
        
        $page = get('page', 1);
        $limit = get('limit', 20);
        $categoryId = get('category_id');
        $keyword = get('keyword');
        
        $offset = ($page - 1) * $limit;
        
        $where = 'p.status = 1';
        $params = [];
        
        if ($categoryId) {
            $where .= ' AND p.category_id = ?';
            $params[] = $categoryId;
        }
        
        if ($keyword) {
            $where .= ' AND (p.name LIKE ? OR p.description LIKE ?)';
            $params[] = "%{$keyword}%";
            $params[] = "%{$keyword}%";
        }
        
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE {$where} 
                ORDER BY p.sort_order DESC, p.id DESC 
                LIMIT ?, ?";
        
        $params[] = $offset;
        $params[] = $limit;
        
        $products = $this->db->select($sql, $params);
        
        // 处理图片
        foreach ($products as &$product) {
            $product['images'] = json_decode($product['images'], true) ?? [];
            $product['main_image'] = $product['images'][0] ?? '';
            unset($product['images']);
        }
        
        // 获取总数
        $countSql = "SELECT COUNT(*) as total FROM products p WHERE {$where}";
        $total = $this->db->selectOne($countSql, array_slice($params, 0, -2))['total'];
        
        $this->jsonSuccess('获取成功', [
            'list' => $products,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit),
            ],
        ]);
    }
    
    /**
     * 获取商品详情
     */
    public function productDetail()
    {
        // 检查小程序是否开启
        if (!$this->isMiniProgramEnabled()) {
            $this->jsonError('小程序功能未开启');
        }
        
        $productId = get('id');
        
        if (!$productId) {
            $this->jsonError('商品ID不能为空');
        }
        
        $product = $this->productModel->find($productId);
        
        if (!$product || $product['status'] != 1) {
            $this->jsonError('商品不存在');
        }
        
        // 处理图片
        $product['images'] = json_decode($product['images'], true) ?? [];
        
        // 获取SKU信息
        $skus = $this->db->select(
            "SELECT * FROM product_skus WHERE product_id = ? AND status = 1 ORDER BY sort_order ASC",
            [$productId]
        );
        
        // 获取规格信息
        $specs = $this->db->select(
            "SELECT * FROM product_specs WHERE product_id = ? ORDER BY sort_order ASC",
            [$productId]
        );
        
        // 增加浏览量
        $this->db->update(
            "UPDATE products SET views = views + 1 WHERE id = ?",
            [$productId]
        );
        
        $this->jsonSuccess('获取成功', [
            'product' => $product,
            'skus' => $skus,
            'specs' => $specs,
        ]);
    }
    
    /**
     * 创建订单
     */
    public function createOrder()
    {
        $this->requireMiniAuth();
        
        $userId = $this->getUserId();
        $items = post('items'); // [{product_id: 1, sku_id: 1, quantity: 1}]
        $addressId = post('address_id');
        $remark = post('remark', '');
        
        if (!$items || !is_array($items)) {
            $this->jsonError('请选择商品');
        }
        
        if (!$addressId) {
            $this->jsonError('请选择收货地址');
        }
        
        $this->db->beginTransaction();
        
        try {
            // 验证收货地址
            $address = $this->db->selectOne(
                "SELECT * FROM addresses WHERE id = ? AND user_id = ?",
                [$addressId, $userId]
            );
            
            if (!$address) {
                throw new \Exception('收货地址不存在');
            }
            
            // 计算订单金额
            $totalAmount = 0;
            $orderItems = [];
            
            foreach ($items as $item) {
                // 获取商品信息
                $product = $this->productModel->find($item['product_id']);
                if (!$product || $product['status'] != 1) {
                    throw new \Exception("商品《{$product['name']}》已下架");
                }
                
                // 获取SKU信息
                $sku = null;
                if ($item['sku_id']) {
                    $sku = $this->db->selectOne(
                        "SELECT * FROM product_skus WHERE id = ? AND product_id = ? AND status = 1",
                        [$item['sku_id'], $item['product_id']]
                    );
                    if (!$sku) {
                        throw new \Exception("商品规格不存在");
                    }
                }
                
                $price = $sku ? $sku['price'] : $product['price'];
                $quantity = $item['quantity'];
                
                // 检查库存
                $stock = $sku ? $sku['stock'] : $product['stock'];
                if ($stock < $quantity) {
                    throw new \Exception("商品《{$product['name']}》库存不足");
                }
                
                $itemTotal = $price * $quantity;
                $totalAmount += $itemTotal;
                
                $orderItems[] = [
                    'product_id' => $product['id'],
                    'product_name' => $product['name'],
                    'sku_id' => $sku ? $sku['id'] : null,
                    'sku_name' => $sku ? $sku['name'] : '',
                    'price' => $price,
                    'quantity' => $quantity,
                    'total_price' => $itemTotal,
                    'product_image' => json_decode($product['images'], true)[0] ?? '',
                ];
            }
            
            // 创建订单
            $orderData = [
                'user_id' => $userId,
                'order_no' => 'MP' . date('YmdHis') . rand(1000, 9999),
                'total_amount' => $totalAmount,
                'pay_amount' => $totalAmount,
                'status' => 'pending',
                'remark' => $remark,
                'created_at' => date('Y-m-d H:i:s'),
                'address_id' => $addressId,
                'platform' => 'mini_program',
            ];
            
            $orderId = $this->orderModel->create($orderData);
            
            // 创建订单项
            foreach ($orderItems as $item) {
                $this->db->insert('order_items', array_merge($item, [
                    'order_id' => $orderId,
                ]));
                
                // 扣减库存
                if ($item['sku_id']) {
                    $this->db->update(
                        "UPDATE product_skus SET stock = stock - ? WHERE id = ?",
                        [$item['quantity'], $item['sku_id']]
                    );
                } else {
                    $this->db->update(
                        "UPDATE products SET stock = stock - ? WHERE id = ?",
                        [$item['quantity'], $item['product_id']]
                    );
                }
            }
            
            $this->db->commit();
            
            $this->jsonSuccess('订单创建成功', [
                'order_id' => $orderId,
                'order_no' => $orderData['order_no'],
                'total_amount' => $totalAmount,
            ]);
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->jsonError($e->getMessage());
        }
    }
    
    /**
     * 小程序支付接口
     */
    public function miniPay()
    {
        $this->requireMiniAuth();
        
        $orderId = post('order_id');
        
        if (!$orderId) {
            $this->jsonError('订单ID不能为空');
        }
        
        $order = $this->orderModel->find($orderId);
        $userId = $this->getUserId();
        
        if (!$order || $order['user_id'] != $userId) {
            $this->jsonError('订单不存在');
        }
        
        if ($order['status'] !== 'pending') {
            $this->jsonError('订单状态不正确');
        }
        
        try {
            $wechatPay = new WechatPayService();
            
            // 小程序支付需要openid
            $user = $this->userModel->find($userId);
            if (!$user || !$user['openid']) {
                $this->jsonError('用户信息不完整');
            }
            
            // 调用JSAPI支付
            $result = $this->jsapiPay($wechatPay, $order, $user['openid']);
            
            if ($result['success']) {
                $this->jsonSuccess('支付参数获取成功', $result['data']);
            } else {
                $this->jsonError($result['message']);
            }
            
        } catch (\Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }
    
    /**
     * JSAPI支付（小程序支付）
     */
    private function jsapiPay($wechatPay, $order, $openid)
    {
        $params = [
            'appid' => $wechatPay->getAppId(),
            'mchid' => $wechatPay->getMchId(),
            'description' => '小程序订单支付',
            'out_trade_no' => $order['order_no'],
            'notify_url' => config('app.url') . '/payment/wechat/notify',
            'amount' => [
                'total' => (int)($order['pay_amount'] * 100),
            ],
            'payer' => [
                'openid' => $openid,
            ],
        ];
        
        // 实际项目中需要调用微信支付JSAPI接口
        // 这里简化处理，返回支付参数
        
        return [
            'success' => true,
            'data' => [
                'timeStamp' => time(),
                'nonceStr' => $this->generateNonce(),
                'package' => "prepay_id={$this->generatePrepayId()}",
                'signType' => 'RSA',
                'paySign' => $this->generateSign(),
            ],
        ];
    }
    
    /**
     * 获取用户订单列表
     */
    public function userOrders()
    {
        $this->requireMiniAuth();
        
        $userId = $this->getUserId();
        $status = get('status');
        $page = get('page', 1);
        $limit = get('limit', 10);
        
        $offset = ($page - 1) * $limit;
        
        $where = 'user_id = ?';
        $params = [$userId];
        
        if ($status) {
            $where .= ' AND status = ?';
            $params[] = $status;
        }
        
        $sql = "SELECT * FROM orders WHERE {$where} ORDER BY created_at DESC LIMIT ?, ?";
        $params[] = $offset;
        $params[] = $limit;
        
        $orders = $this->db->select($sql, $params);
        
        // 获取订单项
        foreach ($orders as &$order) {
            $order['items'] = $this->db->select(
                "SELECT * FROM order_items WHERE order_id = ?",
                [$order['id']]
            );
        }
        
        // 获取总数
        $countSql = "SELECT COUNT(*) as total FROM orders WHERE {$where}";
        $total = $this->db->selectOne($countSql, array_slice($params, 0, -2))['total'];
        
        $this->jsonSuccess('获取成功', [
            'list' => $orders,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit),
            ],
        ]);
    }
    
    /**
     * 获取订单详情
     */
    public function orderDetail()
    {
        $this->requireMiniAuth();
        
        $orderId = get('id');
        $userId = $this->getUserId();
        
        $order = $this->orderModel->find($orderId);
        
        if (!$order || $order['user_id'] != $userId) {
            $this->jsonError('订单不存在');
        }
        
        // 获取订单项
        $order['items'] = $this->db->select(
            "SELECT * FROM order_items WHERE order_id = ?",
            [$orderId]
        );
        
        // 获取收货地址
        if ($order['address_id']) {
            $order['address'] = $this->db->selectOne(
                "SELECT * FROM addresses WHERE id = ?",
                [$order['address_id']]
            );
        }
        
        $this->jsonSuccess('获取成功', $order);
    }
    
    /**
     * 取消订单
     */
    public function cancelOrder()
    {
        $this->requireMiniAuth();
        
        $orderId = post('order_id');
        $reason = post('reason', '用户取消');
        $userId = $this->getUserId();
        
        $order = $this->orderModel->find($orderId);
        
        if (!$order || $order['user_id'] != $userId) {
            $this->jsonError('订单不存在');
        }
        
        if ($order['status'] !== 'pending') {
            $this->jsonError('订单状态不允许取消');
        }
        
        $this->db->beginTransaction();
        
        try {
            // 更新订单状态
            $this->orderModel->update($orderId, [
                'status' => 'cancelled',
                'cancel_reason' => $reason,
                'cancelled_at' => date('Y-m-d H:i:s'),
            ]);
            
            // 恢复库存
            $items = $this->db->select(
                "SELECT * FROM order_items WHERE order_id = ?",
                [$orderId]
            );
            
            foreach ($items as $item) {
                if ($item['sku_id']) {
                    $this->db->update(
                        "UPDATE product_skus SET stock = stock + ? WHERE id = ?",
                        [$item['quantity'], $item['sku_id']]
                    );
                } else {
                    $this->db->update(
                        "UPDATE products SET stock = stock + ? WHERE id = ?",
                        [$item['quantity'], $item['product_id']]
                    );
                }
            }
            
            $this->db->commit();
            $this->jsonSuccess('订单取消成功');
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->jsonError($e->getMessage());
        }
    }
    
    /**
     * 微信session获取
     */
    private function getWxSession($code)
    {
        $appId = config('miniprogram.app_id');
        $appSecret = config('miniprogram.app_secret');
        
        if (!$appId || !$appSecret) {
            return null;
        }
        
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid={$appId}&secret={$appSecret}&js_code={$code}&grant_type=authorization_code";
        
        $response = file_get_contents($url);
        return json_decode($response, true);
    }
    
    /**
     * 微信数据解密
     */
    private function decryptWxData($encryptedData, $iv, $sessionKey)
    {
        if (strlen($sessionKey) != 24) {
            return false;
        }
        
        $aesKey = base64_decode($sessionKey);
        $aesIV = base64_decode($iv);
        $aesCipher = base64_decode($encryptedData);
        
        $result = openssl_decrypt($aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);
        $dataObj = json_decode($result, true);
        
        if ($dataObj == null) {
            return false;
        }
        
        return $dataObj;
    }
    
    /**
     * 生成小程序token
     */
    private function generateToken($userId)
    {
        $token = md5($userId . time() . rand(1000, 9999));
        
        // 保存token到数据库
        $this->db->insert('user_tokens', [
            'user_id' => $userId,
            'token' => $token,
            'expires_at' => date('Y-m-d H:i:s', time() + 7200), // 2小时过期
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        
        return $token;
    }
    
    /**
     * 验证小程序token
     */
    private function verifyToken($token)
    {
        $tokenData = $this->db->selectOne(
            "SELECT * FROM user_tokens WHERE token = ? AND expires_at > NOW()",
            [$token]
        );
        
        return $tokenData ? $tokenData['user_id'] : false;
    }
    
    /**
     * 要求小程序认证
     */
    private function requireMiniAuth()
    {
        $token = $this->getTokenFromHeader();
        
        if (!$token || !$this->verifyToken($token)) {
            $this->jsonError('登录已过期，请重新登录', 401);
        }
    }
    
    /**
     * 从header获取token
     */
    private function getTokenFromHeader()
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? '';
        
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $matches[1];
        }
        
        return post('token') ?: get('token');
    }
    
    /**
     * 获取当前用户ID
     */
    private function getUserId()
    {
        $token = $this->getTokenFromHeader();
        return $this->verifyToken($token);
    }
    
    /**
     * 生成随机字符串
     */
    private function generateNonce($length = 16)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $nonce = '';
        for ($i = 0; $i < $length; $i++) {
            $nonce .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $nonce;
    }
    
    /**
     * 生成预支付ID（模拟）
     */
    private function generatePrepayId()
    {
        return 'prepay_' . time() . '_' . rand(1000, 9999);
    }
    
    /**
     * 生成签名（模拟）
     */
    private function generateSign()
    {
        return md5(time() . rand(1000, 9999));
    }
    
    /**
     * JSON成功响应
     */
    private function jsonSuccess($message, $data = [])
    {
        echo json_encode([
            'code' => 200,
            'message' => $message,
            'data' => $data,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * 检查小程序是否开启
     */
    private function isMiniProgramEnabled()
    {
        // 从配置文件获取小程序开关状态
        $settingModel = new SiteSetting();
        $enabled = $settingModel->get('enable_miniprogram', '0');
        
        return $enabled === '1' || $enabled === 1;
    }
    
    /**
     * JSON错误响应
     */
    private function jsonError($message, $code = 400)
    {
        echo json_encode([
            'code' => $code,
            'message' => $message,
            'data' => null,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}