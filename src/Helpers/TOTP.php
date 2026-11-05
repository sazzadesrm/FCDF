<?php

namespace App\Helpers;

/**
 * Minimal, dependency-free TOTP (RFC 6238) implementation compatible with
 * Google Authenticator / Microsoft Authenticator / Authy. Used for the
 * optional Two-Factor Authentication feature.
 */
class TOTP
{
    private const BASE32_CHARS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    public static function generateSecret(int $length = 20): string
    {
        $bytes = random_bytes($length);
        return self::base32Encode($bytes);
    }

    public static function getCode(string $secret, ?int $timeSlice = null): string
    {
        $timeSlice = $timeSlice ?? (int) floor(time() / 30);
        $secretKey = self::base32Decode($secret);
        $time = pack('N*', 0) . pack('N*', $timeSlice);
        $hash = hash_hmac('sha1', $time, $secretKey, true);
        $offset = ord(substr($hash, -1)) & 0x0F;
        $part = substr($hash, $offset, 4);
        $value = unpack('N', $part)[1] & 0x7FFFFFFF;
        return str_pad((string) ($value % 1000000), 6, '0', STR_PAD_LEFT);
    }

    public static function verify(string $secret, string $code, int $window = 1): bool
    {
        $currentSlice = (int) floor(time() / 30);
        for ($i = -$window; $i <= $window; $i++) {
            if (hash_equals(self::getCode($secret, $currentSlice + $i), $code)) {
                return true;
            }
        }
        return false;
    }

    public static function getProvisioningUri(string $secret, string $accountEmail, string $issuer = 'FCDF Pro'): string
    {
        $label = rawurlencode($issuer . ':' . $accountEmail);
        $params = http_build_query([
            'secret' => $secret,
            'issuer' => $issuer,
            'algorithm' => 'SHA1',
            'digits' => 6,
            'period' => 30,
        ]);
        return "otpauth://totp/{$label}?{$params}";
    }

    private static function base32Encode(string $data): string
    {
        if ($data === '') return '';
        $binary = '';
        foreach (str_split($data) as $char) {
            $binary .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
        }
        $chunks = str_split($binary, 5);
        $output = '';
        foreach ($chunks as $chunk) {
            $chunk = str_pad($chunk, 5, '0');
            $output .= self::BASE32_CHARS[bindec($chunk)];
        }
        return $output;
    }

    private static function base32Decode(string $b32): string
    {
        $b32 = strtoupper($b32);
        $binary = '';
        foreach (str_split($b32) as $char) {
            $pos = strpos(self::BASE32_CHARS, $char);
            if ($pos === false) continue;
            $binary .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
        }
        $bytes = str_split($binary, 8);
        $output = '';
        foreach ($bytes as $byte) {
            if (strlen($byte) < 8) continue;
            $output .= chr(bindec($byte));
        }
        return $output;
    }
}
