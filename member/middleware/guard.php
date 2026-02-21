<?php
/**
 * Guard - Middleware-style Access Control
 * 
 * Fungsi-fungsi untuk proteksi halaman berdasarkan status login dan role.
 * Dipanggil di awal setiap halaman yang memerlukan proteksi.
 */

/**
 * Hanya user yang sudah login yang bisa akses.
 * Redirect ke login jika belum login.
 */
function guardAuth(): void
{
    if (Auth::isGuest()) {
        $_SESSION['member_flash_error'] = 'Silakan login terlebih dahulu.';
        $_SESSION['member_intended_url'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . MEMBER_BASE_URL . '/login.php');
        exit;
    }
}

/**
 * Hanya user dengan role tertentu yang bisa akses.
 * Redirect ke login jika belum login, atau 403 jika role tidak sesuai.
 */
function guardRole(string $role): void
{
    guardAuth();

    if (!Auth::hasRole($role)) {
        http_response_code(403);
        exit('
        <!DOCTYPE html>
        <html lang="id">
        <head><meta charset="utf-8"><title>403 Forbidden</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"></head>
        <body class="bg-light d-flex align-items-center justify-content-center min-vh-100">
        <div class="text-center">
            <h1 class="display-1 text-danger">403</h1>
            <p class="lead">Akses ditolak. Anda tidak memiliki izin untuk halaman ini.</p>
            <a href="' . htmlspecialchars(MEMBER_BASE_URL, ENT_QUOTES, 'UTF-8') . '/login.php" class="btn btn-primary">Kembali</a>
        </div>
        </body></html>');
    }
}

/**
 * Hanya guest (belum login) yang bisa akses.
 * Redirect ke dashboard jika sudah login.
 */
function guardGuest(): void
{
    if (Auth::check()) {
        header('Location: ' . MEMBER_BASE_URL . '/dashboard.php');
        exit;
    }
}

/**
 * Flash message helpers
 */
function setFlash(string $type, string $message): void
{
    $_SESSION['member_flash_' . $type] = $message;
}

function getFlash(string $type): string
{
    $key = 'member_flash_' . $type;
    $message = $_SESSION[$key] ?? '';
    unset($_SESSION[$key]);
    return $message;
}

function hasFlash(string $type): bool
{
    return !empty($_SESSION['member_flash_' . $type]);
}

/**
 * Sanitize output untuk mencegah XSS.
 */
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/**
 * Ambil old input value (untuk form repopulation setelah error).
 */
function old(string $key, string $default = ''): string
{
    return e($_SESSION['member_old_input'][$key] ?? $default);
}

/**
 * Simpan input ke session untuk form repopulation.
 */
function saveOldInput(array $data, array $except = ['password', 'password_confirmation']): void
{
    foreach ($except as $key) {
        unset($data[$key]);
    }
    $_SESSION['member_old_input'] = $data;
}

/**
 * Hapus old input dari session.
 */
function clearOldInput(): void
{
    unset($_SESSION['member_old_input']);
}

/**
 * Get client IP address.
 */
function getClientIp(): string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    // Jangan percaya X-Forwarded-For kecuali di belakang trusted proxy
    return filter_var($ip, FILTER_VALIDATE_IP) ?: '0.0.0.0';
}
