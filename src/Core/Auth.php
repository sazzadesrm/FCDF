<?php

namespace App\Core;

use App\Config\Database;
use PDO;

/**
 * Handles authentication: login, logout, registration support, password
 * hashing/verification, and brute-force lockout.
 */
class Auth
{
    private const MAX_ATTEMPTS = 5;
    private const LOCKOUT_MINUTES = 15;

    public static function attemptLogin(string $email, string $password): array
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if (!$user) {
            return ['ok' => false, 'message' => 'Invalid email or password.'];
        }

        if (!empty($user['locked_until']) && strtotime($user['locked_until']) > time()) {
            $minutes = ceil((strtotime($user['locked_until']) - time()) / 60);
            return ['ok' => false, 'message' => "Account locked due to repeated failed attempts. Try again in {$minutes} minute(s)."];
        }

        if (!password_verify($password, $user['password_hash'])) {
            self::registerFailedAttempt($pdo, (int) $user['id'], (int) $user['failed_login_count']);
            return ['ok' => false, 'message' => 'Invalid email or password.'];
        }

        if (empty($user['is_active'])) {
            return ['ok' => false, 'message' => 'Your account is inactive. Contact the administrator.'];
        }

        if (empty($user['email_verified_at'])) {
            return ['ok' => false, 'message' => 'Please verify your email address before signing in.'];
        }

        // Reset failed attempts on success
        $upd = $pdo->prepare('UPDATE users SET failed_login_count = 0, locked_until = NULL WHERE id = :id');
        $upd->execute(['id' => $user['id']]);

        if (!empty($user['twofa_enabled'])) {
            return ['ok' => true, 'needs_2fa' => true, 'user' => $user];
        }

        return ['ok' => true, 'needs_2fa' => false, 'user' => $user];
    }

    private static function registerFailedAttempt(PDO $pdo, int $userId, int $currentCount): void
    {
        $newCount = $currentCount + 1;
        if ($newCount >= self::MAX_ATTEMPTS) {
            $lockUntil = date('Y-m-d H:i:s', time() + self::LOCKOUT_MINUTES * 60);
            $stmt = $pdo->prepare('UPDATE users SET failed_login_count = 0, locked_until = :lock WHERE id = :id');
            $stmt->execute(['lock' => $lockUntil, 'id' => $userId]);
        } else {
            $stmt = $pdo->prepare('UPDATE users SET failed_login_count = :c WHERE id = :id');
            $stmt->execute(['c' => $newCount, 'id' => $userId]);
        }
    }

    public static function login(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => (int) $user['id'],
            'full_name' => $user['full_name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'avatar' => $user['avatar'],
        ];
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }
}
