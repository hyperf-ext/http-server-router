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

use FastRoute\DataGenerator;
use FastRoute\RouteParser;
use Hyperf\HttpServer\MiddlewareManager;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\HttpServer\Router\RouteCollector as BaseRouteCollector;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

class RouteCollector extends BaseRouteCollector
{
    protected ContainerInterface $container;

    protected string $currentGroupName = '';

    /**
     * @var \HyperfExt\HttpServer\Router\Route[]
     */
    protected array $namedRoutes = [];

    public function __construct(ContainerInterface $container, RouteParser $routeParser, DataGenerator $dataGenerator, string $server = 'http')
    {
        parent::__construct($routeParser, $dataGenerator, $server);
        $this->container = $container;
    }

    public function addRoute($httpMethod, string $route, $handler, array $options = []): Route
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

        return $routeInstance;
    }

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

    public function get(string $route, $handler, array $options = []): Route
    {
        return $this->addRoute('GET', $route, $handler, $options);
    }

    public function post(string $route, $handler, array $options = []): Route
    {
        return $this->addRoute('POST', $route, $handler, $options);
    }

    public function put(string $route, $handler, array $options = []): Route
    {
        return $this->addRoute('PUT', $route, $handler, $options);
    }

    public function delete(string $route, $handler, array $options = []): Route
    {
        return $this->addRoute('DELETE', $route, $handler, $options);
    }

    public function patch(string $route, $handler, array $options = []): Route
    {
        return $this->addRoute('PATCH', $route, $handler, $options);
    }

    public function head(string $route, $handler, array $options = []): Route
    {
        return $this->addRoute('HEAD', $route, $handler, $options);
    }

    /**
     * Get a route instance by its name.
     *
     * @throws \HyperfExt\HttpServer\Router\RouteNotFoundException
     */
    public function getRoute(string $name): Route
    {
        if (isset($this->namedRoutes[$name])) {
            return $this->namedRoutes[$name];
        }

        throw new RouteNotFoundException("Route [{$name}] not defined.");
    }

    /**
     * Get a route instance by current request.
     */
    public function getCurrentRoute(): ?Route
    {
        $dispatched = $this->container->get(ServerRequestInterface::class)->getAttribute(Dispatched::class);
        return $dispatched ? $dispatched->handler->routeInstance : null;
    }
}
