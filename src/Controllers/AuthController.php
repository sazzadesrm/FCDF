<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\AuditLogger;
use App\Core\Controller;
use App\Core\Mailer;
use App\Core\Validator;
use App\Helpers\TOTP;
use App\Models\User;
use App\Config\Database;

class AuthController extends Controller
{
    public function showLogin(): void
    {
        if (is_logged_in()) { redirect(route_url('dashboard')); }
        $this->render('auth/login', [], 'auth');
    }

    public function login(): void
    {
        $this->requireCsrf();
        $email = trim((string) $this->input('email', ''));
        $password = (string) $this->input('password', '');

        $result = Auth::attemptLogin($email, $password);

        if (!$result['ok']) {
            flash('error', $result['message']);
            $_SESSION['_old']['email'] = $email;
            redirect(route_url('login'));
        }

        if (!empty($result['needs_2fa'])) {
            $_SESSION['_2fa_pending_user_id'] = $result['user']['id'];
            redirect(route_url('login/2fa'));
        }

        Auth::login($result['user']);
        AuditLogger::log('login', 'user', (int) $result['user']['id'], 'User signed in');
        flash('success', 'Welcome back, ' . $result['user']['full_name'] . '!');
        redirect(route_url('dashboard'));
    }

    public function show2fa(): void
    {
        if (empty($_SESSION['_2fa_pending_user_id'])) {
            redirect(route_url('login'));
        }
        $this->render('auth/2fa-verify', [], 'auth');
    }

    public function verify2fa(): void
    {
        $this->requireCsrf();
        $userId = $_SESSION['_2fa_pending_user_id'] ?? null;
        if (!$userId) { redirect(route_url('login')); }

        $user = User::find((int) $userId);
        $code = trim((string) $this->input('code', ''));

        if (!$user || empty($user['twofa_secret']) || !TOTP::verify($user['twofa_secret'], $code)) {
            flash('error', 'Invalid authentication code. Please try again.');
            redirect(route_url('login/2fa'));
        }

        unset($_SESSION['_2fa_pending_user_id']);
        Auth::login($user);
        AuditLogger::log('login_2fa', 'user', (int) $user['id'], 'User signed in with 2FA');
        redirect(route_url('dashboard'));
    }

    public function showRegister(): void
    {
        if (is_logged_in()) { redirect(route_url('dashboard')); }
        $this->render('auth/register', [], 'auth');
    }

    public function register(): void
    {
        $this->requireCsrf();
        $data = [
            'full_name' => trim((string) $this->input('full_name', '')),
            'email' => trim((string) $this->input('email', '')),
            'password' => (string) $this->input('password', ''),
            'password_confirmation' => (string) $this->input('password_confirmation', ''),
        ];

        $v = new Validator($data);
        $v->required('full_name', 'Full name')
          ->required('email', 'Email')->email('email')
          ->required('password', 'Password')->minLength('password', 8)
          ->matches('password', 'password_confirmation', 'Passwords do not match.');

        if ($v->fails()) {
            flash('error', $v->firstErrorMessage());
            $_SESSION['_old'] = $data;
            redirect(route_url('register'));
        }

        if (User::emailExists($data['email'])) {
            flash('error', 'An account with this email already exists.');
            redirect(route_url('register'));
        }

        $userId = User::insert([
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'password_hash' => Auth::hashPassword($data['password']),
            'role' => 'user',
            'is_active' => 1,
        ]);

        $token = bin2hex(random_bytes(32));
        $pdo = Database::connect();
        $stmt = $pdo->prepare('INSERT INTO email_verifications (user_id, token, expires_at) VALUES (:uid, :token, :exp)');
        $stmt->execute([
            'uid' => $userId,
            'token' => $token,
            'exp' => date('Y-m-d H:i:s', time() + 86400),
        ]);

        $link = route_url('verify-email', ['token' => $token]);
        Mailer::verificationEmail($data['email'], $data['full_name'], $link);

        AuditLogger::log('register', 'user', $userId, 'New account registered, verification email sent');
        flash('success', 'Account created. Please check your email to verify your account before signing in.');
        redirect(route_url('login'));
    }

    public function verifyEmail(): void
    {
        $token = (string) $this->input('token', '');
        $pdo = Database::connect();
        $stmt = $pdo->prepare('SELECT * FROM email_verifications WHERE token = :t AND verified_at IS NULL AND expires_at > NOW() LIMIT 1');
        $stmt->execute(['t' => $token]);
        $row = $stmt->fetch();

        if (!$row) {
            flash('error', 'This verification link is invalid or has expired.');
            redirect(route_url('login'));
        }

        $pdo->prepare('UPDATE email_verifications SET verified_at = NOW() WHERE id = :id')->execute(['id' => $row['id']]);
        $pdo->prepare('UPDATE users SET email_verified_at = NOW() WHERE id = :id')->execute(['id' => $row['user_id']]);

        AuditLogger::log('email_verified', 'user', (int) $row['user_id']);
        flash('success', 'Email verified successfully. You may now sign in.');
        redirect(route_url('login'));
    }

    public function showForgotPassword(): void
    {
        $this->render('auth/forgot-password', [], 'auth');
    }

    public function sendResetLink(): void
    {
        $this->requireCsrf();
        $email = trim((string) $this->input('email', ''));
        $user = User::findByEmail($email);

        // Always show the same message to prevent user enumeration.
        if ($user) {
            $token = bin2hex(random_bytes(32));
            $pdo = Database::connect();
            $stmt = $pdo->prepare('INSERT INTO password_resets (user_id, token, expires_at) VALUES (:uid, :token, :exp)');
            $stmt->execute([
                'uid' => $user['id'],
                'token' => $token,
                'exp' => date('Y-m-d H:i:s', time() + 3600),
            ]);
            $link = route_url('reset-password', ['token' => $token]);
            Mailer::passwordResetEmail($user['email'], $user['full_name'], $link);
            AuditLogger::log('password_reset_requested', 'user', (int) $user['id']);
        }

        flash('success', 'If that email is registered, a password reset link has been sent.');
        redirect(route_url('login'));
    }

    public function showResetPassword(): void
    {
        $token = (string) $this->input('token', '');
        $this->render('auth/reset-password', ['token' => $token], 'auth');
    }

    public function resetPassword(): void
    {
        $this->requireCsrf();
        $token = (string) $this->input('token', '');
        $password = (string) $this->input('password', '');
        $confirm = (string) $this->input('password_confirmation', '');

        $pdo = Database::connect();
        $stmt = $pdo->prepare('SELECT * FROM password_resets WHERE token = :t AND used_at IS NULL AND expires_at > NOW() LIMIT 1');
        $stmt->execute(['t' => $token]);
        $row = $stmt->fetch();

        if (!$row) {
            flash('error', 'This password reset link is invalid or has expired.');
            redirect(route_url('forgot-password'));
        }

        if (strlen($password) < 8 || $password !== $confirm) {
            flash('error', 'Passwords must match and be at least 8 characters.');
            redirect(route_url('reset-password', ['token' => $token]));
        }

        $pdo->prepare('UPDATE users SET password_hash = :h WHERE id = :id')
            ->execute(['h' => Auth::hashPassword($password), 'id' => $row['user_id']]);
        $pdo->prepare('UPDATE password_resets SET used_at = NOW() WHERE id = :id')->execute(['id' => $row['id']]);

        AuditLogger::log('password_reset', 'user', (int) $row['user_id']);
        flash('success', 'Your password has been reset. Please sign in.');
        redirect(route_url('login'));
    }

    public function logout(): void
    {
        $user = current_user();
        if ($user) {
            AuditLogger::log('logout', 'user', (int) $user['id']);
        }
        Auth::logout();
        redirect(route_url('login'));
    }
}
