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

use Hyperf\HttpServer\Router\DispatcherFactory as HyperfDispatcherFactory;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                HyperfDispatcherFactory::class => DispatcherFactory::class,
            ],
        ];
    }
}
