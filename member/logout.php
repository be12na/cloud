<?php
/**
 * Logout - /member/logout.php
 * 
 * Menghapus session dan redirect ke halaman login.
 */

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/middleware/guard.php';

// Jalankan logout
Auth::logout();

// Regenerate CSRF
Csrf::regenerate();

setFlash('success', 'Anda berhasil logout.');

// Redirect ke login
header('Location: ' . MEMBER_BASE_URL . '/login.php');
exit;
