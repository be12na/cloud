<?php
/**
 * Halaman register dinonaktifkan.
 * Redirect semua akses ke halaman login member.
 */

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/middleware/guard.php';

setFlash('error', 'Registrasi member saat ini tidak tersedia. Silakan login dengan akun yang sudah ada.');
header('Location: ' . MEMBER_BASE_URL . '/login.php');
exit;
