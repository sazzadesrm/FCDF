<?php

namespace App\Core;

/**
 * Lightweight transactional mailer. Uses PHP's built-in mail() function by
 * default (works out-of-the-box on most cPanel shared hosting without any
 * Composer dependency). If your host requires authenticated SMTP, drop in
 * PHPMailer under vendor/ (composer require phpmailer/phpmailer) and swap
 * the body of send() to use it — the calling code will not need to change.
 */
class Mailer
{
    public static function send(string $toEmail, string $toName, string $subject, string $htmlBody): bool
    {
        $fromEmail = env('MAIL_FROM_ADDRESS', 'no-reply@example.com');
        $fromName = env('MAIL_FROM_NAME', APP_NAME);

        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: {$fromName} <{$fromEmail}>\r\n";
        $headers .= "Reply-To: {$fromEmail}\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();

        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';

        try {
            return @mail($toEmail, $encodedSubject, $htmlBody, $headers);
        } catch (\Throwable $e) {
            error_log('Mailer error: ' . $e->getMessage());
            return false;
        }
    }

    public static function verificationEmail(string $toEmail, string $toName, string $link): bool
    {
        $body = self::wrap("
            <h2>Welcome to " . APP_NAME . "</h2>
            <p>Hello {$toName},</p>
            <p>Please verify your email address to activate your account:</p>
            <p><a href='{$link}' style='background:#0d3b66;color:#fff;padding:10px 18px;text-decoration:none;border-radius:4px;'>Verify Email</a></p>
            <p>Or copy this link into your browser:<br>{$link}</p>
        ");
        return self::send($toEmail, $toName, 'Verify your ' . APP_NAME . ' account', $body);
    }

    public static function passwordResetEmail(string $toEmail, string $toName, string $link): bool
    {
        $body = self::wrap("
            <h2>Password Reset Request</h2>
            <p>Hello {$toName},</p>
            <p>We received a request to reset your " . APP_NAME . " password. This link expires in 60 minutes.</p>
            <p><a href='{$link}' style='background:#0d3b66;color:#fff;padding:10px 18px;text-decoration:none;border-radius:4px;'>Reset Password</a></p>
            <p>If you did not request this, you can safely ignore this email.</p>
        ");
        return self::send($toEmail, $toName, 'Reset your ' . APP_NAME . ' password', $body);
    }

    private static function wrap(string $inner): string
    {
        return "<div style='font-family:Arial,sans-serif;max-width:560px;margin:0 auto;color:#222;'>{$inner}
            <hr style='border:none;border-top:1px solid #ddd;margin:24px 0;'>
            <p style='font-size:12px;color:#888;'>" . APP_NAME . " &middot; System Admin: Sazzad Kabir &middot; sazzadmbstu@gmail.com</p>
        </div>";
    }
}
