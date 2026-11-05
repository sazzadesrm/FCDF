<?php
/**
 * FCDF Pro - Application Bootstrap / Configuration
 * Loads .env, sets up autoloading, session, error handling.
 */

error_reporting(E_ALL);

// ---------------------------------------------------------------------------
// Output buffering: guarantees header()/redirect() calls always work even if
// a warning, deprecation notice, or stray whitespace was already printed
// earlier in the request. Flushed automatically at script end.
// ---------------------------------------------------------------------------
if (!ob_get_level()) {
    ob_start();
}

// ---------------------------------------------------------------------------
// Polyfills for PHP 8.0+ string functions, in case the hosting account's
// PHP version is set below 8.0 in cPanel's "Select PHP Version" tool.
// (PRD targets PHP 8.5, but this keeps the app from hard-crashing with a
// fatal error if a host defaults to an older version.)
// ---------------------------------------------------------------------------
if (!function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle): bool
    {
        return $needle === '' || strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}
if (!function_exists('str_ends_with')) {
    function str_ends_with(string $haystack, string $needle): bool
    {
        return $needle === '' || substr($haystack, -strlen($needle)) === $needle;
    }
}
if (!function_exists('str_contains')) {
    function str_contains(string $haystack, string $needle): bool
    {
        return $needle === '' || strpos($haystack, $needle) !== false;
    }
}

// ---------------------------------------------------------------------------
// Paths
// ---------------------------------------------------------------------------
define('BASE_PATH', dirname(__DIR__, 2));
define('SRC_PATH', BASE_PATH . '/src');
define('STORAGE_PATH', BASE_PATH . '/storage');
// FLAT DEPLOYMENT LAYOUT: index.php, assets/, uploads/ all live directly at
// the project root (which is also the web server's Document Root), not in
// a separate /public subfolder. PUBLIC_PATH therefore equals BASE_PATH.
define('PUBLIC_PATH', BASE_PATH);

// ---------------------------------------------------------------------------
// Minimal .env loader (no external dependency required)
// ---------------------------------------------------------------------------
function env(string $key, $default = null)
{
    static $vars = null;
    if ($vars === null) {
        $vars = [];
        $envFile = BASE_PATH . '/.env';
        if (is_file($envFile)) {
            foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                $line = trim($line);
                if ($line === '' || str_starts_with($line, '#')) {
                    continue;
                }
                if (strpos($line, '=') === false) {
                    continue;
                }
                [$k, $v] = explode('=', $line, 2);
                $k = trim($k);
                $v = trim($v);
                $v = trim($v, "\"'");
                $vars[$k] = $v;
            }
        }
    }
    return $vars[$key] ?? $default;
}

// ---------------------------------------------------------------------------
// App constants
// ---------------------------------------------------------------------------
define('APP_NAME', env('APP_NAME', 'FCDF Pro'));
define('APP_ENV', env('APP_ENV', 'production'));
define('APP_DEBUG', filter_var(env('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN));
define('APP_URL', rtrim(env('APP_URL', ''), '/'));

if (APP_DEBUG) {
    ini_set('display_errors', '1');
} else {
    ini_set('display_errors', '0');
}
ini_set('log_errors', '1');
ini_set('error_log', STORAGE_PATH . '/logs/php-error.log');

// ---------------------------------------------------------------------------
// PSR-4-ish manual autoloader (works without `composer install` on hosts
// where CLI composer is unavailable). If vendor/autoload.php exists (i.e.
// composer WAS run), that is preferred and this is skipped.
// ---------------------------------------------------------------------------
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $path = SRC_PATH . '/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($path)) {
        require $path;
    }
});

if (is_file(BASE_PATH . '/vendor/autoload.php')) {
    require BASE_PATH . '/vendor/autoload.php';
}

require_once SRC_PATH . '/Helpers/functions.php';

// ---------------------------------------------------------------------------
// Session
// ---------------------------------------------------------------------------
$lifetime = (int) env('SESSION_LIFETIME', 120) * 60;
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => $lifetime,
        'path' => '/',
        'httponly' => true,
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'samesite' => 'Lax',
    ]);
    session_name('fcdf_pro_session');
    session_start();
}

// ---------------------------------------------------------------------------
// Default timezone
// ---------------------------------------------------------------------------
date_default_timezone_set('Asia/Dhaka');
