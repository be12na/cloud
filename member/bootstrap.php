<?php
/**
 * Bootstrap - Member System
 * 
 * File ini di-require oleh semua halaman member.
 * Menangani:
 * - Session start dengan konfigurasi aman
 * - Autoload classes
 * - Security headers
 */

// Cegah akses langsung ke file ini
if (basename($_SERVER['SCRIPT_FILENAME']) === 'bootstrap.php') {
    http_response_code(403);
    exit('Forbidden');
}

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Session configuration - aman (httponly, samesite)
if (session_status() === PHP_SESSION_NONE) {
    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

    session_set_cookie_params([
        'lifetime' => 0,             // Session cookie (hilang saat browser ditutup)
        'path'     => '/',
        'domain'   => '',
        'secure'   => $isSecure,     // Secure flag jika HTTPS
        'httponly'  => true,          // Tidak bisa diakses JavaScript
        'samesite' => 'Strict',      // Anti CSRF cross-site
    ]);

    session_name('MEMBER_SESSID');
    session_start();
}

// Base path detection
$memberBasePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$siteBasePath = dirname($memberBasePath);
if ($siteBasePath === '\\' || $siteBasePath === '/') {
    $siteBasePath = '';
}

// Definisi konstanta
if (!defined('MEMBER_BASE_URL')) {
    define('MEMBER_BASE_URL', $memberBasePath);
}
if (!defined('SITE_BASE_URL')) {
    define('SITE_BASE_URL', $siteBasePath);
}

// Autoload classes
$classDir = __DIR__ . '/classes';
require_once $classDir . '/Database.php';
require_once $classDir . '/Csrf.php';
require_once $classDir . '/Validator.php';
require_once $classDir . '/RateLimit.php';
require_once $classDir . '/Auth.php';

// Generate CSRF token untuk setiap request
$csrfToken = Csrf::generateToken();
