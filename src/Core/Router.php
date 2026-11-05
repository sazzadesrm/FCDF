<?php

namespace App\Core;

/**
 * Very small router keyed on the "route" query-string parameter
 * (e.g. index.php?route=deposit/create). This avoids any dependency on
 * Apache mod_rewrite behaving a particular way, while the root .htaccess
 * still provides clean URLs (fcdf-pro.example.com/deposit/create) by
 * rewriting to index.php?route=deposit/create.
 */
class Router
{
    private array $routes = [];

    public function get(string $route, callable $handler): void
    {
        $this->routes['GET'][$route] = $handler;
    }

    public function post(string $route, callable $handler): void
    {
        $this->routes['POST'][$route] = $handler;
    }

    public function any(string $route, callable $handler): void
    {
        $this->routes['GET'][$route] = $handler;
        $this->routes['POST'][$route] = $handler;
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $route = trim((string) ($_GET['route'] ?? 'dashboard'), '/');
        if ($route === '') {
            $route = 'dashboard';
        }

        $handler = $this->routes[$method][$route] ?? null;

        if ($handler === null) {
            http_response_code(404);
            view('errors/404', ['route' => $route], 'main');
            return;
        }

        $handler();
    }
}
