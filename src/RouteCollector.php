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

use Hyperf\HttpServer\MiddlewareManager;
use Hyperf\HttpServer\Router\RouteCollector as BaseRouteCollector;

class RouteCollector extends BaseRouteCollector
{
    protected string $currentGroupName = '';

    /**
     * @var \HyperfExt\HttpServer\Router\Route[]
     */
    protected array $namedRoutes = [];

    /**
     * Adds a route to the collection.
     *
     * The syntax used in the $route string depends on the used route parser.
     *
     * @param string|string[] $httpMethod
     * @param array|string $handler
     */
    public function addRoute($httpMethod, string $route, $handler, array $options = [])
    {
        if (isset($options['name'])) {
            $name = $options['name'];
            unset($options['name']);
        } else {
            $name = '';
        }

        $route = $this->currentGroupPrefix . $route;
        $routeDatas = $this->routeParser->parse($route);
        $name = $this->currentGroupName . $name;
        $routeInstance = new Route($route, $routeDatas, empty($name) ? null : $name);
        if (! empty($name)) {
            $this->namedRoutes[$name] = $routeInstance;
        }
        $options = $this->mergeOptions($this->currentGroupOptions, $options);
        foreach ((array) $httpMethod as $method) {
            $method = strtoupper($method);
            foreach ($routeDatas as $routeData) {
                $this->dataGenerator->addRoute($method, $routeData, new Handler($handler, $route, $options, $routeInstance));
                MiddlewareManager::addMiddlewares($this->server, $route, $method, $options['middleware'] ?? []);
            }
        }
    }

    /**
     * Create a route group with a common prefix.
     *
     * All routes created in the passed callback will have the given group prefix prepended.
     */
    public function addGroup(string $prefix, callable $callback, array $options = [])
    {
        if (isset($options['name'])) {
            $name = $options['name'];
            unset($options['name']);
        } else {
            $name = '';
        }

        $previousGroupPrefix = $this->currentGroupPrefix;
        $previousGroupName = $this->currentGroupName;
        $currentGroupOptions = $this->currentGroupOptions;

        $this->currentGroupPrefix = $previousGroupPrefix . $prefix;
        $this->currentGroupName = $previousGroupName . $name;
        $this->currentGroupOptions = $this->mergeOptions($currentGroupOptions, $options);
        $callback($this);

        $this->currentGroupPrefix = $previousGroupPrefix;
        $this->currentGroupName = $previousGroupName;
        $this->currentGroupOptions = $currentGroupOptions;
    }

    /**
     * Get a route instance by its name.
     *
     * @throws \HyperfExt\HttpServer\Router\RouteNotFoundException
     */
    public function getNamedRoute(string $name): Route
    {
        if (isset($this->namedRoutes[$name])) {
            return $this->namedRoutes[$name];
        }

        throw new RouteNotFoundException("Route [{$name}] not defined.");
    }
}
