<?php
/**
 * Member Area - Index
 * Redirect ke register atau dashboard sesuai status login.
 */

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/middleware/guard.php';

if (Auth::check()) {
    header('Location: ' . MEMBER_BASE_URL . '/dashboard.php');
} else {
    header('Location: ' . MEMBER_BASE_URL . '/register.php');
}
exit;
