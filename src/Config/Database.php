<?php

namespace App\Config;

use PDO;
use PDOException;

/**
 * PDO Database connection singleton. Always uses prepared statements
 * (see App\Core\Model) to prevent SQL injection.
 */
class Database
{
    private static ?PDO $instance = null;

    public static function connect(): PDO
    {
        if (self::$instance === null) {
            $host = env('DB_HOST', 'localhost');
            $db   = env('DB_NAME', 'fcdf_pro');
            $user = env('DB_USER', 'root');
            $pass = env('DB_PASS', '');
            $charset = env('DB_CHARSET', 'utf8mb4');

            $dsn = "mysql:host={$host};dbname={$db};charset={$charset}";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            try {
                self::$instance = new PDO($dsn, $user, $pass, $options);
                // Align MySQL's clock with Dhaka time (UTC+06:00, no DST) so
                // CURRENT_TIMESTAMP / ON UPDATE stamps (created_at, updated_at)
                // match PHP's Asia/Dhaka timezone.
                self::$instance->exec("SET time_zone = '+06:00'");
            } catch (PDOException $e) {
                error_log('DB Connection failed: ' . $e->getMessage());
                http_response_code(500);
                if (APP_DEBUG) {
                    die('Database connection failed: ' . htmlspecialchars($e->getMessage()));
                }
                die('A system error occurred. Please try again later or contact support.');
            }
        }
        return self::$instance;
    }
}
