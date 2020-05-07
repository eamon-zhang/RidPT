<?php

namespace Rid\Http;

use Rid\Base\Component;
use Rid\Helpers\ContainerHelper;
use Rid\Utils\Text;

/**
 * App类
 *
 * @property \Rid\Http\Error $error
 * @property \Rid\Http\Session $session
 * @property \Rid\Http\Route $route
 * @property \Rid\Http\Message\Request $request
 * @property \Rid\Http\Message\Response $response
 * @property \App\Components\Auth $auth
 */
class Application extends \Rid\Base\Application
{

    // 控制器命名空间
    public $controllerNamespace = '';

    // 全局中间件
    public $middleware = [];

    // 执行功能
    public function run()
    {
        $server = \Rid::app()->request->server->all();
        $method = strtoupper($server['REQUEST_METHOD']);
        $action = empty($server['PATH_INFO']) ? '' : substr($server['PATH_INFO'], 1);
        $content = $this->runAction($method, $action);
        if (is_array($content)) {
            \Rid::app()->response->setJson($content);
        } else {
            \Rid::app()->response->setContent($content);
        }

        \Rid::app()->response->prepare(\Rid::app()->request);
        \Rid::app()->response->send();
    }

    // 执行功能并返回
    public function runAction($method, $action)
    {
        $action = "{$method} {$action}";
        // 路由匹配
        $result = \Rid::app()->route->match($action);
        foreach ($result as $item) {
            list($route, $queryParams) = $item;
            // 路由参数导入请求类
            \Rid::app()->request->attributes->set('route', $queryParams);
            // 实例化控制器
            list($shortClass, $shortAction) = $route;
            $controllerDir    = \Rid\Helpers\FileSystemHelper::dirname($shortClass);
            $controllerDir    = $controllerDir == '.' ? '' : "$controllerDir\\";
            $controllerName   = Text::toPascalName(\Rid\Helpers\FileSystemHelper::basename($shortClass));
            $controllerClass  = "{$this->controllerNamespace}\\{$controllerDir}{$controllerName}Controller";
            $shortAction      = Text::toPascalName($shortAction);
            $controllerAction = "action{$shortAction}";
            // 判断类是否存在
            if (class_exists($controllerClass)) {
                $controllerInstance = ContainerHelper::getContainer()->make($controllerClass);
                // 判断方法是否存在
                if (method_exists($controllerInstance, $controllerAction)) {
                    // 执行中间件
                    $middleware = $this->newMiddlewareInstance($route['middleware']);
                    if (!empty($middleware)) {
                        return $this->runMiddleware([$controllerInstance, $controllerAction], $middleware);
                    }
                    // 直接返回执行结果
                    return $controllerInstance->$controllerAction();
                }
            }
            // 不带路由参数的路由规则找不到时，直接抛出错误
            if (empty($queryParams)) {
                break;
            }
        }
        throw new \Rid\Exceptions\NotFoundException('Not Found (#404)');
    }

    // 执行中间件
    protected function runMiddleware($callable, $middleware)
    {
        $item = array_shift($middleware);
        if (empty($item)) {
            return call_user_func($callable);
        }
        return $item->handle($callable, function () use ($callable, $middleware) {
            return $this->runMiddleware($callable, $middleware);
        });
    }

    // 实例化中间件
    protected function newMiddlewareInstance($routeMiddleware)
    {
        $middleware = [];
        foreach (array_merge($this->middleware, $routeMiddleware) as $key => $class) {
            $middleware[$key] = ContainerHelper::getContainer()->make($class);
        }
        return $middleware;
    }

    // 获取组件
    public function __get($name)
    {
        // 获取全名
        if (!is_null($this->_componentPrefix)) {
            $name = "{$this->_componentPrefix}.{$name}";
        }
        $this->setComponentPrefix(null);
        /* 常驻模式 */
        // 返回单例
        if (isset($this->_components[$name])) {
            // 触发请求前置事件
            $this->triggerRequestBefore($this->_components[$name]);
            // 返回对象
            return $this->_components[$name];
        }
        return $this->_components[$name];
    }

    // 装载全部组件
    public function loadAllComponents($components = null)
    {
        $components = $components ?? $this->components;
        foreach (array_keys($components) as $name) {
            $this->loadComponent($name);
        }
    }

    // 清扫组件容器
    public function cleanComponents()
    {
        // 触发请求后置事件
        foreach ($this->_components as $component) {
            $this->triggerRequestAfter($component);
        }
    }

    /** 触发请求前置事件
     * @param \Rid\Base\Component $component
     */
    protected function triggerRequestBefore($component)
    {
        if ($component->getStatus() == Component::STATUS_READY) {
            $component->onRequestBefore();
        }
    }

    /** 触发请求后置事件
     * @param \Rid\Base\Component $component
     */
    protected function triggerRequestAfter($component)
    {
        if ($component->getStatus() == Component::STATUS_RUNNING) {
            $component->onRequestAfter();
        }
    }
}
