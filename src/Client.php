<?php

namespace teamones\http;

use Yurun\Util\YurunHttp\Http\Response;

/**
 * 业务层 HTTP 客户端：按 host + route + method 组装请求，并把 JSON 响应解码为数组。
 *
 * 3.0 起底层为内置的 YurunHttp（third_party/yurun-http），对外方法保持不变。
 */
class Client extends Base
{
    /**
     * 生成请求url
     * @return string
     */
    protected function generateUrl()
    {
        if (!empty($this->_host)) {
            if (!empty($this->_route)) {
                return $this->_host . '/' . $this->_route;
            } else {
                return $this->_host;
            }
        } else {
            throw new \RuntimeException('', -4000000);
        }
    }

    /**
     * 设置路由
     * @param string $route
     * @return $this
     */
    public function setRoute($route = '')
    {
        $this->_route = (string)$route;
        return $this;
    }

    /**
     * 设置请求方法
     * @param string $method
     * @return $this
     */
    public function setMethod($method = 'POST')
    {
        $this->_method = ucwords($method);
        return $this;
    }

    /**
     * 设置请求头
     * @param array $headers
     * @return $this
     */
    public function setHeader($headers = [])
    {
        if ($this->_method === 'POST') {
            $this->_headers = array_merge($this->_headers, $headers);
        } else {
            $this->_headers = $headers;
        }
        return $this;
    }

    /**
     * 设置host参数
     * @param $host
     * @return $this
     */
    public function setHost($host)
    {
        $this->_host = $host;

        return $this;
    }

    /**
     * 设置body参数
     * @param $body
     * @return $this
     */
    public function setBody($body)
    {
        $this->_body = $body;

        return $this;
    }

    /**
     * 同步http请求
     *
     * 总超时 30s、连接超时 500ms（与 0.x 保持一致）。
     *
     * @return array|mixed
     */
    protected function syncHttp()
    {
        $url = $this->generateUrl();

        switch ($this->_method) {
            case 'POST':
                $response = self::instance('sync')->timeout(30000, 500)
                    ->headers($this->_headers)
                    ->post($url, $this->_body, 'json');
                break;
            case 'GET':
                $response = self::instance('sync')->timeout(30000, 500)
                    ->headers($this->_headers)
                    ->get($url, $this->_body);
                break;
            default:
                $response = [];
                break;
        }

        if ($response instanceof Response) {
            if ((int)$response->httpCode() !== 200) {
                throw new \RuntimeException($response->getBody(), -4000000);
            }

            $body = $response->json(true);
            if (!empty($body['code']) && (int)$body['code'] !== 0) {
                throw new \RuntimeException((string)($body['msg'] ?? ''), (int)$body['code']);
            }

            return $body;
        }

        return $response;
    }

    /**
     * 组装请求
     *
     * 3.0 起只支持同步：原异步分支依赖 workerman/http-client 0.1.7，在 Workerman 5 下
     * 会因 Workerman\Lib\Timer 不存在而失败，且业务侧无调用点，故移除。
     *
     * @param string $type
     * @return array|mixed
     */
    public function request($type = 'sync')
    {
        if ($type === 'asynch') {
            throw new \RuntimeException(
                'Async http client has been removed since teamones/http 3.0, use "sync" instead.',
                -4000000
            );
        }

        return $this->syncHttp();
    }
}
