<?php

namespace teamones\http;

use Yurun\Util\HttpRequest;

/**
 * 请求基类：保存一次调用的 host/route/method/headers/body。
 *
 * 3.0 起 YurunHttp 源码内置在 third_party/yurun-http（MIT，见其 LICENSE），
 * 不再依赖 workerman/http-client，异步分支已移除。
 */
class Base
{
    /**
     * @var array<string, HttpRequest>
     */
    protected static $_instance = [];

    // 服务地址
    protected $_host = '';

    // 路由地址
    protected $_route = '';

    // 请求方法
    protected $_method = '';

    // Post请求默认header头
    protected $_headers = [];

    // 设置body参数
    protected $_body = null;

    /**
     * 获取底层请求实例（同步）。
     *
     * @param string $type 仅为兼容旧签名，异步已移除，一律返回同步实例
     * @return HttpRequest
     */
    public static function instance($type = 'sync')
    {
        if (empty(self::$_instance['sync'])) {
            self::$_instance['sync'] = new HttpRequest();
        }

        return self::$_instance['sync'];
    }

    /**
     * 静态转发到同步请求实例。
     *
     * @param string $name
     * @param array  $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        return static::instance('sync')->{$name}(... $arguments);
    }
}
