<?php
/**
 * Global helper functions used across views/controllers.
 */

function asset_url(string $path): string
{
    $fullPath = PUBLIC_PATH . '/' . ltrim($path, '/');
    $version = is_file($fullPath) ? filemtime($fullPath) : time();
    return base_url($path) . '?v=' . $version;
}

function base_url(string $path = ''): string
{
    $path = ltrim($path, '/');
    if (defined('APP_URL') && APP_URL !== '') {
        return APP_URL . '/' . $path;
    }
    // Fallback: derive from current request (relative to /public)
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $scheme . '://' . $host . '/' . $path;
}

function route_url(string $route, array $params = []): string
{
    $url = base_url('index.php?route=' . urlencode($route));
    foreach ($params as $k => $v) {
        $url .= '&' . urlencode($k) . '=' . urlencode((string) $v);
    }
    return $url;
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function old(string $key, $default = '')
{
    return $_SESSION['_old'][$key] ?? $default;
}

function flash(string $type, string $message): void
{
    $_SESSION['_flash'][$type][] = $message;
}

function get_flashes(): array
{
    $flashes = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $flashes;
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . csrf_token() . '">';
}

function verify_csrf(): bool
{
    $token = $_POST['_csrf'] ?? '';
    return !empty($token) && !empty($_SESSION['_csrf_token']) && hash_equals($_SESSION['_csrf_token'], $token);
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool
{
    return !empty($_SESSION['user']);
}

function is_admin(): bool
{
    return is_logged_in() && ($_SESSION['user']['role'] ?? '') === 'admin';
}

function require_login(): void
{
    if (!is_logged_in()) {
        flash('error', 'Please sign in to continue.');
        redirect(route_url('login'));
    }
}

function require_admin(): void
{
    require_login();
    if (!is_admin()) {
        http_response_code(403);
        flash('error', 'You do not have permission to access that page.');
        redirect(route_url('dashboard'));
    }
}

function client_ip(): string
{
    foreach (['HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'] as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = explode(',', $_SERVER[$key])[0];
            return trim($ip);
        }
    }
    return '0.0.0.0';
}

function format_money(float $amount): string
{
    // Whole-number BDT/currency amounts only — no fractional/decimal part.
    return number_format(round($amount), 0);
}

function format_date(?string $date, string $fmt = 'd M Y'): string
{
    if (!$date) return '';
    $ts = strtotime($date);
    return $ts ? date($fmt, $ts) : '';
}

/**
 * Render a view with a layout.
 */
function view(string $view, array $data = [], ?string $layout = 'main'): void
{
    extract($data);
    $viewFile = SRC_PATH . '/Views/' . $view . '.php';
    if (!is_file($viewFile)) {
        http_response_code(404);
        echo "View not found: " . e($view);
        return;
    }
    if ($layout === null) {
        require $viewFile;
        return;
    }
    $content = function () use ($viewFile, $data) {
        extract($data);
        require $viewFile;
    };
    require SRC_PATH . '/Views/layouts/' . $layout . '.php';
}

function json_response($data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
