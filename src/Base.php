<?php

namespace teamones\http;

use Yurun\Util\HttpRequest;
use Workerman\Http\Client;

class Base
{
    /**
     * @var array
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
     * @param string $type
     * @return \Yurun\Util\HttpRequest | \Workerman\Http\Client
     */
    public static function instance($type = 'sync')
    {
        if (empty(self::$_instance[$type])) {
            if($type === 'sync'){
                self::$_instance[$type] = new HttpRequest;
            }else{
                self::$_instance[$type] = new Client;
            }
        }
        return self::$_instance[$type];
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        return static::instance('sync')->{$name}(... $arguments);
    }
}