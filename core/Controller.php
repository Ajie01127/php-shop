<?php

namespace Core;

/**
 * 基础控制器类
 */
class Controller {
    /**
     * 渲染视图
     */
    protected function view($template, $data = []) {
        view($template, $data);
    }
    
    /**
     * JSON响应
     */
    protected function json($data, $code = 200) {
        json($data, $code);
    }
    
    /**
     * 成功响应
     */
    protected function success($message = 'Success', $data = []) {
        $this->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ]);
    }
    
    /**
     * 错误响应
     */
    protected function error($message = 'Error', $code = 400) {
        $this->json([
            'success' => false,
            'message' => $message,
        ], $code);
    }
    
    /**
     * 重定向
     */
    protected function redirect($url) {
        redirect($url);
    }
    
    /**
     * 验证登录
     */
    protected function requireAuth() {
        if (!isAuth()) {
            $this->redirect('/login');
        }
    }
}
