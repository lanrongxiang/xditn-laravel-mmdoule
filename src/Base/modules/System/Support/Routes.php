<?php

namespace Xditn\Base\modules\System\Support;

use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Arr;
use ReflectionClass;
use ReflectionFunction;
use Xditn\Contracts\ModuleRepositoryInterface;

class Routes
{
    public function __construct(protected Router $router, protected ModuleRepositoryInterface $moduleRepository)
    {
    }

    /**
     * Execute the console command.
     *
     * @param  array  $params
     * @return array
     */
    public function all(array $params = []): array
    {
        $routes = [];

        $modules = $this->moduleRepository->all()->keyBy('name')->toArray();
        $modules['common'] = ['title' => '公共模块'];
        $modules['user'] = ['title' => '用户模块'];
        $modules['develop'] = ['title' => '开发工具'];

        $id = 1;
        foreach ($this->getRoutes() as $route) {
            if (! str_contains($route['action'], '@')) {
                continue;
            }

            [$controller, $action] = explode('@', $route['action']);

            $controllers = explode('\\', $controller);
            $controller = array_pop($controllers);
            $module = strtolower($controllers[1]);
            $namespace = implode('\\', $controllers);

            if (! isset($routes[$namespace])) {
                $routes[$namespace] = [
                    'id' => $id++,
                    'controller' => isset($modules[$module]) ? $modules[$module]['title'] : $namespace,
                    'children' => [],
                ];
            }

            // 控制器搜索
            if (isset($params['controller']) && $params['controller'] && ! str_contains($controller, $params['controller'])) {
                continue;
            }
            // 方法搜索
            $methods = explode('|', strtolower($route['method']));
            if (isset($params['method']) && $params['method'] && ! in_array($params['method'], $methods)) {
                continue;
            }
            // 方法搜索
            if (isset($params['uri']) && $params['uri'] && ! str_contains($route['uri'], $params['uri'])) {
                continue;
            }

            $_route = [
                'id' => $id++,
                'namespace' => $namespace,
                'controller' => $controller,
                'action' => $action,
                'method' => $methods,
                'uri' => $route['uri'],
                'name' => $route['name'],
                'middleware' => array_filter(explode("\n", $route['middleware'])),
            ];

            $routes[$namespace]['children'][] = $_route;
        }

        foreach ($routes as $k => $route) {
            if (! count($route['children'])) {
                unset($routes[$k]);
            }
        }

        $routes = array_values($routes);

        foreach ($routes as &$route) {
            $route['action'] = '';
            $route['uri'] = '';
        }

        return $routes;
    }

    protected function getRoutes()
    {
        $routes = collect($this->router->getRoutes())->map(function ($route) {
            return $this->getRouteInformation($route);
        })->filter()->all();

        return $this->sortRoutes('uri', $routes);
    }

    protected function getRouteInformation(Route $route): array
    {
        return [
            'domain' => $route->domain(),
            'method' => implode('|', $route->methods()),
            'uri' => $route->uri(),
            'name' => $route->getName(),
            'action' => ltrim($route->getActionName(), '\\'),
            'middleware' => $this->getMiddleware($route),
            'vendor' => $this->isVendorRoute($route),
        ];
    }

    protected function sortRoutes($sort, array $routes): array
    {
        return Arr::sort($routes, function ($route) use ($sort) {
            return $route[$sort];
        });
    }

    protected function getMiddleware($route): string
    {
        return collect($this->router->gatherRouteMiddleware($route))->map(function ($middleware) {
            return $middleware instanceof \Closure ? 'Closure' : $middleware;
        })->implode("\n");
    }

    protected function isVendorRoute(Route $route): bool
    {
        if ($route->action['uses'] instanceof Closure) {
            $path = (new ReflectionFunction($route->action['uses']))
                ->getFileName();
        } elseif (is_string($route->action['uses']) &&
            str_contains($route->action['uses'], 'SerializableClosure')) {
            return false;
        } elseif (is_string($route->action['uses'])) {
            if ($this->isFrameworkController($route)) {
                return false;
            }

            $path = (new ReflectionClass($route->getControllerClass()))
                ->getFileName();
        } else {
            return false;
        }

        return str_starts_with($path, base_path('vendor'));
    }

    protected function isFrameworkController(Route $route)
    {
        return in_array($route->getControllerClass(), [
            '\Illuminate\Routing\RedirectController',
            '\Illuminate\Routing\ViewController',
        ], true);
    }
}
