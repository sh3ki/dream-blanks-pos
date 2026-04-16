<?php

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, array|callable $handler, array $middleware = []): void
    {
        $this->addRoute('GET', $path, $handler, $middleware);
    }

    public function post(string $path, array|callable $handler, array $middleware = []): void
    {
        $this->addRoute('POST', $path, $handler, $middleware);
    }

    public function put(string $path, array|callable $handler, array $middleware = []): void
    {
        $this->addRoute('PUT', $path, $handler, $middleware);
    }

    public function delete(string $path, array|callable $handler, array $middleware = []): void
    {
        $this->addRoute('DELETE', $path, $handler, $middleware);
    }

    private function addRoute(string $method, string $path, array|callable $handler, array $middleware): void
    {
        $path = '/' . trim($path, '/');
        $path = $path === '/' ? '/' : rtrim($path, '/');

        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }

    public function dispatch(Request $request): void
    {
        $requestMethod = $request->method();
        $requestPath = $request->path();

        foreach ($this->routes as $route) {
            if ($route['method'] !== $requestMethod) {
                continue;
            }

            $params = [];
            if (!$this->matchPath($route['path'], $requestPath, $params)) {
                continue;
            }

            foreach ($route['middleware'] as $middlewareClass) {
                $middleware = new $middlewareClass();
                if (method_exists($middleware, 'handle')) {
                    $middleware->handle($request);
                }
            }

            $handler = $route['handler'];
            if (is_callable($handler)) {
                $handler($request, $params);
                return;
            }

            [$class, $method] = $handler;
            $controller = new $class();
            $controller->{$method}($request, $params);
            return;
        }

        http_response_code(404);
        echo '404 Not Found';
    }

    private function matchPath(string $pattern, string $path, array &$params): bool
    {
        $regex = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', '(?P<$1>[^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';

        if (!preg_match($regex, $path, $matches)) {
            return false;
        }

        foreach ($matches as $key => $value) {
            if (!is_int($key)) {
                $params[$key] = $value;
            }
        }

        return true;
    }
}
