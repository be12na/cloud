<?php
/**
 * VFM Installer / Validator
 * PHP version >= 7.0
 */

define('VFM_APP', true);
require_once 'config.php';

// Konfigurasi Error Reporting
if ($_CONFIG['debug_mode'] === true) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL ^ E_NOTICE);
}

// Load Core Classes (Minimal)
require_once 'class/class.setup.php';
require_once 'class/class.gatekeeper.php';

 $setUp = new setUp();
 $gateKeeper = new GateKeeper();

 $_USERS = $gateKeeper->getUsers();
 $firstrun = $setUp->getConfig('firstrun');
 $script_url = $setUp->getConfig('script_url');

// ==========================================
// CHECK 1: Jika sudah terinstal, langsung redirect
// ==========================================
if ($firstrun !== true && !empty($_USERS[0]['pass']) && !empty($script_url)) {
    header('Location: ' . $script_url);
    exit;
}

// ==========================================
// JIKA BELUM TERINSTAL, Load Class Tambahan
// ==========================================
require_once 'class/class.utils.php';
require_once 'class/class.admin.php';
require_once 'class/class.updater.php';
 $updater = new Updater();

 $resetconfig = false;
 $resetusr = false;

/**
 * Set Base URL jika kosong
 */
if ($firstrun || empty($script_url)) {
    $actual_link = Admin::getAppUrl();
    $_CONFIG['script_url'] = $actual_link;
    $_CONFIG['firstrun'] = false;
    $resetconfig = true;
    $updater->updateUploadsDir();
}

/**
 * Buat Session Name yang unik dan aman
 */
if (strlen($_CONFIG['session_name']) < 5) {
    // Gunakan random_bytes untuk keamanan yang lebih baik (PHP 7+)
    $_CONFIG['session_name'] = "vfm_" . bin2hex(random_bytes(5));
    $resetconfig = true;
}

/**
 * Buat App Key (Salt) yang unik dan kuat
 */
if (strlen($_CONFIG['salt']) < 5) {
    // Ganti md5(mt_rand) yang lemah dengan random_bytes
    $_CONFIG['salt'] = bin2hex(random_bytes(16));
    $resetusr = true;
}

/**
 * Reset password SuperAdmin jika diperlukan
 */
 $adminPass = $_USERS[0]['pass'] ?? '';
if (empty($adminPass) || $resetusr === true) {
    // Gunakan password_hash (bcrypt) untuk hash password default 'password'
    $reset = Utils::hashPassword($_CONFIG['salt'], 'password');
    $_USERS[0]['pass'] = $reset;
    
    if (!Utils::saveJson('_content/users/users.json', $_USERS)) {
        Utils::setError("Error writing on <strong>_content/users/users.json</strong>, check CHMOD settings");
    }
}

/**
 * Update file config.php
 */
if ($resetusr === true || $resetconfig === true) {
    if (!Utils::saveJson('config.json', $_CONFIG)) {
        Utils::setError("Error writing on <strong>/config.json</strong>, check CHMOD settings");
    }
}

 $updater->updateHtaccess('./');

// Redirect setelah setup selesai
header('Location: ' . $_CONFIG['script_url']);
exit;