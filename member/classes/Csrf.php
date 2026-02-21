<?php
/**
 * CSRF Protection
 * 
 * Menggunakan random_bytes() untuk generate token dan hash_equals() 
 * untuk timing-safe comparison (mencegah timing attacks).
 */

class Csrf
{
    private const TOKEN_KEY = 'member_csrf_token';

    /**
     * Generate CSRF token dan simpan di session.
     * Jika sudah ada, kembalikan yang existing.
     */
    public static function generateToken(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            throw new RuntimeException('Session harus aktif sebelum generate CSRF token.');
        }

        if (empty($_SESSION[self::TOKEN_KEY])) {
            $_SESSION[self::TOKEN_KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::TOKEN_KEY];
    }

    /**
     * Output hidden input field untuk form.
     */
    public static function field(): string
    {
        $token = self::generateToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Verifikasi CSRF token dari POST request.
     * Menggunakan hash_equals() untuk mencegah timing attack.
     */
    public static function verify(?string $token = null): bool
    {
        if ($token === null) {
            $token = $_POST['csrf_token'] ?? '';
        }

        if (empty($_SESSION[self::TOKEN_KEY]) || empty($token)) {
            return false;
        }

        return hash_equals($_SESSION[self::TOKEN_KEY], $token);
    }

    /**
     * Regenerate token (dipanggil setelah form berhasil diproses).
     */
    public static function regenerate(): void
    {
        unset($_SESSION[self::TOKEN_KEY]);
        self::generateToken();
    }
}
