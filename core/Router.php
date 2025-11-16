<?php

namespace Core;

/**
 * 路由类
 */
class Router {
    private $routes = [];
    
    /**
     * 注册GET路由
     */
    public function get($uri, $action) {
        $this->addRoute('GET', $uri, $action);
    }
    
    /**
     * 注册POST路由
     */
    public function post($uri, $action) {
        $this->addRoute('POST', $uri, $action);
    }
    
    /**
     * 添加路由
     */
    private function addRoute($method, $uri, $action) {
        $this->routes[] = [
            'method' => $method,
            'uri' => $uri,
            'action' => $action,
        ];
    }
    
    /**
     * 路由分发
     */
    public function dispatch($requestUri, $requestMethod) {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $requestMethod) {
                continue;
            }
            
            // 转换路由模式为正则表达式
            $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $route['uri']);
            $pattern = '#^' . $pattern . '$#';
            
            if (preg_match($pattern, $requestUri, $matches)) {
                // 提取路由参数
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                
                // 执行控制器方法
                return $this->callAction($route['action'], $params);
            }
        }
        
        // 404
        http_response_code(404);
        echo '404 Not Found';
    }
    
    /**
     * 调用控制器方法
     */
    private function callAction($action, $params = []) {
        list($controller, $method) = explode('@', $action);
        
        // 添加命名空间
        if (strpos($controller, 'Admin\\') === 0) {
            $controllerClass = 'App\\Controllers\\' . $controller;
        } else {
            $controllerClass = 'App\\Controllers\\' . $controller;
        }
        
        if (!class_exists($controllerClass)) {
            die("Controller not found: $controllerClass");
        }
        
        $controllerInstance = new $controllerClass();
        
        if (!method_exists($controllerInstance, $method)) {
            die("Method not found: $method");
        }
        
        return call_user_func_array([$controllerInstance, $method], $params);
    }
}
