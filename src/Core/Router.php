<?php

namespace App\Core;

class Router {
    private array $routes = [];

    public function addRoute(string $method, string $path, callable|array $handler): void {
        // Convert route path to regex (e.g., /product/{slug} -> #^/product/([^/]+)$#)
        $path = preg_replace('/\{([a-zA-Z0-9_-]+)\}/', '([^/]+)', $path);
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => "#^" . $path . "$#",
            'handler' => $handler
        ];
    }

    public function get(string $path, callable|array $handler): void {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, callable|array $handler): void {
        $this->addRoute('POST', $path, $handler);
    }

    public function dispatch(string $requestUri, string $requestMethod): void {
        $parsedUrl = parse_url($requestUri);
        $path = $parsedUrl['path'] ?? '/';

        foreach ($this->routes as $route) {
            if ($route['method'] === $requestMethod && preg_match($route['path'], $path, $matches)) {
                array_shift($matches); // Remove full match

                if (is_array($route['handler']) && count($route['handler']) === 2) {
                    $controller = new $route['handler'][0]();
                    $method = $route['handler'][1];
                    // Pass $_POST or $_GET implicitly via superglobals, but pass regex matches as args
                    call_user_func_array([$controller, $method], $matches);
                } else {
                    call_user_func_array($route['handler'], $matches);
                }
                return;
            }
        }

        // Handle 404
        header("HTTP/1.0 404 Not Found");
        if (file_exists(__DIR__ . '/../Views/errors/404.php')) {
            require_once __DIR__ . '/../Views/errors/404.php';
        } else {
            echo "404 Not Found";
        }
    }
}
