<?php

namespace teamones\http;

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
     * @param $body
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

        if ($response instanceof \Yurun\Util\YurunHttp\Http\Response) {
            if ((int)$response->httpCode() !== 200) {
                throw new \RuntimeException($response->getBody(), -4000000);
            } else {
                $body = $response->json(true);
                if (!empty($body['code']) && (int)$body['code'] !== 0) {
                    throw new \RuntimeException($body['msg'], $body['code']);
                }

                return $body;
            }
        } else {
            return $response;
        }
    }

    /**
     * 异步http请求
     * @return array
     */
    protected function asynchHttp()
    {
        $url = $this->generateUrl();

        if (in_array($this->_method, ['POST', 'GET'])) {
            self::instance($this->_method)->request(
                $url,
                [
                    'method' => 'POST',
                    'version' => '1.1',
                    'headers' => $this->_headers,
                    'data' => $this->_body,
                    'success' => function ($response) {
                        echo $response->getBody();
                    },
                    'error' => function ($exception) {
                        echo $exception;
                    }
                ]
            );
        } else {
            return [];
        }
    }

    /**
     * 组装请求, 总超时30s，连接超时500ms
     * @param string $type
     * @return array|mixed
     */
    public function request($type = 'sync')
    {
        switch ($type) {
            case 'asynch':
                // 发起异步http请求
                return $this->asynchHttp();
                break;
            default:
                // 发起同步步http请求
                return $this->syncHttp();
                break;
        }
    }
}