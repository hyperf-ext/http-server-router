# Hyperf HTTP 服务器命名路由扩展组件

## 概要

该组件通过绑定 `HyperfExt\HttpServer\Router\DispatcherFactory` 到 `Hyperf\HttpServer\Router\DispatcherFactory` 来实现扩展路由功能，由于修改了返回类型，PHP 版本必须 >= 7.4。

## 安装

```shell
composer require hyperf-ext/http-server-router
```

## 使用

### 定义命名路由

在路由选项中定义 `name` 参数来对路由命名，支持对路由组命名。

```php
use Hyperf\HttpServer\Router\Router;

Router::addGroup('/users/{id}', function () {
    Router::get('/comments', 'App\Controller\IndexController@index', ['name' => 'comments.index']); // 该路由名称将被组合为 `users.comments.index`
}, ['name' => 'users.']);
```

### 获取路由对象

#### 通过路由名称获取指定路由

```php
use Hyperf\HttpServer\Router\Router;

/** @var \HyperfExt\HttpServer\Router\Route $route */
$route = Router::getNamedRoute('users.comments.index');
```

#### 通过当前请求获取当前路由

```php
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\Utils\ApplicationContext;

/** @var \HyperfExt\HttpServer\Router\Route $route */
$route = ApplicationContext::getContainer()
    ->get(RequestInterface::class)
    ->getAttribute(Dispatched::class)
    ->handler
    ->routeInstance; // 为避免过多修改原始组件，该组件将路由实例放到了 Handler 中
```

### 生成指定路由的 URI

```php
/**
 * @var \HyperfExt\HttpServer\Router\Route $route
 * @var \Hyperf\HttpMessage\Uri\Uri $uri
 */
$uri = $route->createUri([
    'id' => 123,
    'page_num' => 2,
    'page_size' => 20,
]);
$link = (string) $uri; // 结果为 `/users/123/comments?page_num=2&page_size=20`
```
