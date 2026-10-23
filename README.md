# http

Teamones 框架 HTTP 客户端（同步）。

## 版本

- **3.0**（当前）：把 [YurunHttp](https://github.com/Yurunsoft/YurunHttp) 源码内置到 `third_party/yurun-http`（MIT），移除 `yurunsoft/yurun-http` 与 `workerman/http-client` 两个外部依赖，并对 PHP 8.5 做了兼容改造。
- 0.x：外部依赖 `yurunsoft/yurun-http`。

## 3.0 改动

| 变化 | 说明 |
|---|---|
| 源码内置 | `third_party/yurun-http/src`，autoload 映射 `Yurun\Util\` → 该目录；原 `LICENSE` 与 `README.upstream.md` 一并保留 |
| 依赖 | 仅需 `php >=8.1`、`ext-curl`、`ext-json`、`psr/http-message` |
| `curl_close()` | 3 处调用已移除（PHP 8.5 弃用，8.0 起为空操作），改为置空引用交由 GC |
| 异步 | `request('asynch')` 已移除（其依赖的 `workerman/http-client 0.1.7` 在 Workerman 5 下不可用，且无调用点） |
| 公开 API | `setHost/setRoute/setMethod/setHeader/setBody/request('sync')` 保持不变 |

行为与 0.x 一致：超时单位为毫秒（总 30s / 连接 500ms）、`headers()` 为合并语义、GET 参数拼 query、POST 且 `json` 时自动 `json_encode`、HTTP 4xx/5xx 不抛异常、传输失败返回 `httpCode()=0` 且 `getErrno()>0`。

## 用法

```php
use teamones\http\Client;

$data = (new Client())
    ->setHost('http://127.0.0.1:8080')
    ->setRoute('api/user')
    ->setMethod('POST')
    ->setHeader(['X-Trace-Id' => 'abc'])
    ->setBody(['id' => 1])
    ->request('sync');   // 返回解码后的数组；非 200 或 code != 0 时抛 RuntimeException
```

底层用法（与 YurunHttp 一致）：

```php
use Yurun\Util\HttpRequest;

$response = (new HttpRequest())
    ->timeout(30000, 500)          // 毫秒
    ->headers(['Accept' => 'application/json'])
    ->post('http://127.0.0.1:8080/api/user', ['id' => 1], 'json');

$response->httpCode();   // 200
$response->getErrno();   // 0（传输层错误时 > 0）
$response->getBody();    // 原始正文
$response->json(true);   // 解码为数组
```

## 内置源码的维护约定

`third_party/yurun-http` 是上游 4.3.14 的完整拷贝，改动仅限 PHP 8.5 兼容性修复，不调整其对外行为。
升级上游时，请覆盖后重新检查 `curl_close(` 等弃用调用。
