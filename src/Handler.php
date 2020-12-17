<?php

declare(strict_types=1);
/**
 * This file is part of hyperf-ext/http-server-router.
 *
 * @link     https://github.com/hyperf-ext/http-server-router
 * @contact  eric@zhu.email
 * @license  https://github.com/hyperf-ext/http-server-router/blob/master/LICENSE
 */
namespace HyperfExt\HttpServer\Router;

use Hyperf\HttpServer\Router\Handler as HyperfHandler;

class Handler extends HyperfHandler
{
    public Route $routeInstance;

    public function __construct($callback, string $route, array $options, Route $routeInstance)
    {
        parent::__construct($callback, $route, $options);
        $this->routeInstance = $routeInstance;
    }
}
