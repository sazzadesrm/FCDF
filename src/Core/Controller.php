<?php

namespace App\Core;

abstract class Controller
{
    protected function render(string $view, array $data = [], ?string $layout = 'main'): void
    {
        view($view, $data, $layout);
    }

    protected function input(string $key, $default = null)
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    protected function requireCsrf(): void
    {
        if (!verify_csrf()) {
            http_response_code(419);
            flash('error', 'Your session has expired. Please try again.');
            redirect($_SERVER['HTTP_REFERER'] ?? route_url('dashboard'));
        }
    }
}
