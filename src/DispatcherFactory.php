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

use FastRoute\DataGenerator\GroupCountBased as DataGenerator;
use FastRoute\RouteParser\Std;
use Hyperf\HttpServer\Router\DispatcherFactory as HyperfDispatcherFactory;

class DispatcherFactory extends HyperfDispatcherFactory
{
    public function getRouter(string $serverName): RouteCollector
    {
        if (isset($this->routers[$serverName])) {
            return $this->routers[$serverName];
        }

        $parser = new Std();
        $generator = new DataGenerator();
        return $this->routers[$serverName] = new RouteCollector($parser, $generator, $serverName);
    }
}
