<?php
/**
 * Member Area - Index
 * Redirect ke dashboard atau login sesuai status login.
 */

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/middleware/guard.php';

if (Auth::check()) {
    header('Location: ' . MEMBER_BASE_URL . '/dashboard.php');
} else {
    header('Location: ' . MEMBER_BASE_URL . '/login.php');
}
exit;
