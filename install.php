<?php
/**
 * FCDF Pro — One-time installation helper.
 *
 * This project uses a flat deployment layout: this file lives at the
 * project root, which is also your web server's Document Root, so it is
 * directly reachable in the browser at:
 *
 *     https://your-domain.example.com/install.php
 *
 * Run this ONCE immediately after importing database.sql and creating your
 * .env file. It creates/updates the initial Administrator account with a
 * securely generated password hash, then reminds you to delete this file.
 *
 * SECURITY: Delete this file from the server immediately after running it.
 * Visit with ?confirm=yes to actually perform the install (a plain visit
 * shows instructions only, so it can't run by accident e.g. via a crawler).
 */

require __DIR__ . '/src/Config/config.php';

use App\Config\Database;
use App\Core\Auth;

header('Content-Type: text/plain; charset=utf-8');

if (($_GET['confirm'] ?? '') !== 'yes') {
    echo "FCDF Pro Installer\n";
    echo "===================\n\n";
    echo "This will create/reset the initial Administrator account:\n";
    echo "  Email: sazzadmbstu@gmail.com\n";
    echo "  Temporary password: Walton@7130#BD\n\n";
    echo "Make sure you have already:\n";
    echo "  1. Imported database.sql via phpMyAdmin\n";
    echo "  2. Created your .env file with correct DB credentials\n\n";
    echo "To proceed, open this URL:\n";
    echo "  " . (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'your-domain') . $_SERVER['SCRIPT_NAME'] . "?confirm=yes\n";
    exit;
}

$adminEmail = 'sazzadmbstu@gmail.com';
$adminName = 'Sazzad Kabir';
$tempPassword = 'Walton@7130#BD';

try {
    $pdo = Database::connect();

    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email');
    $stmt->execute(['email' => $adminEmail]);
    $existing = $stmt->fetch();

    $hash = Auth::hashPassword($tempPassword);

    if ($existing) {
        $upd = $pdo->prepare('UPDATE users SET password_hash = :hash, role = "admin", is_active = 1, email_verified_at = NOW() WHERE id = :id');
        $upd->execute(['hash' => $hash, 'id' => $existing['id']]);
        $message = "Administrator account updated for {$adminEmail}.";
    } else {
        $ins = $pdo->prepare('INSERT INTO users (full_name, email, password_hash, role, is_active, email_verified_at)
                               VALUES (:name, :email, :hash, "admin", 1, NOW())');
        $ins->execute(['name' => $adminName, 'email' => $adminEmail, 'hash' => $hash]);
        $message = "Administrator account created for {$adminEmail}.";
    }

    echo "{$message}\n";
    echo "Temporary password: {$tempPassword}\n\n";
    echo "IMPORTANT:\n";
    echo "  1. Go to https://" . ($_SERVER['HTTP_HOST'] ?? 'your-domain') . "/login and sign in.\n";
    echo "  2. Change this password immediately from My Profile.\n";
    echo "  3. DELETE this file (install.php) from the server now.\n";
} catch (\Throwable $e) {
    http_response_code(500);
    echo "Installation failed: " . $e->getMessage() . "\n";
    echo "Check that .env has the correct DB_HOST, DB_NAME, DB_USER, DB_PASS and that database.sql was imported.\n";
}
