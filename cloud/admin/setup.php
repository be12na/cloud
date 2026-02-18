<?php
/**
 * VFM Installer / Validator - Optimized
 * PHP version >= 7.0 (Recommended for random_bytes)
 */

define('VFM_APP', true);
require_once __DIR__ . '/config.php';

// Konfigurasi Error Reporting
if (isset($_CONFIG['debug_mode']) && $_CONFIG['debug_mode'] === true) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL ^ E_NOTICE);
}

// Load Core Classes untuk cek status
require_once __DIR__ . '/class/class.setup.php';
require_once __DIR__ . '/class/class.gatekeeper.php';

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
require_once __DIR__ . '/class/class.utils.php';
require_once __DIR__ . '/class/class.admin.php';
require_once __DIR__ . '/class/class.updater.php';
 $updater = new Updater();

 $resetConfig = false;
 $resetUser = false;

/**
 * Set Base URL jika kosong
 */
if ($firstrun || empty($script_url)) {
    $_CONFIG['script_url'] = Admin::getAppUrl();
    $_CONFIG['firstrun'] = false;
    $resetConfig = true;
    $updater->updateUploadsDir();
}

/**
 * Buat Session Name yang unik dan aman
 */
if (strlen($_CONFIG['session_name']) < 5) {
    // Gunakan random_bytes untuk keamanan (PHP 7+)
    // Menghasilkan string random 8 karakter hex
    $_CONFIG['session_name'] = "vfm_" . bin2hex(random_bytes(4));
    $resetConfig = true;
}

/**
 * Buat App Key (Salt) yang kuat
 */
if (strlen($_CONFIG['salt']) < 5) {
    // Ganti md5(mt_rand) yang lemah dengan random_bytes (32 karakter hex)
    $_CONFIG['salt'] = bin2hex(random_bytes(16));
    $resetUser = true;
}

/**
 * Reset password SuperAdmin jika diperlukan
 */
 $adminPass = $_USERS[0]['pass'] ?? '';
if (empty($adminPass) || $resetUser === true) {
    // Gunakan salt baru untuk hash password default 'password'
    // CATATAN: Mempertahankan metode crypt() agar kompatibel dengan class GateKeeper yang ada.
    // Jika GateKeeper diupdate ke password_hash(), ubah baris ini.
    $newPassHash = crypt($_CONFIG['salt'] . urlencode('password'), Utils::randomString());
    $_USERS[0]['pass'] = $newPassHash;
    
    $usrContent = "<?php\n\n\$_USERS = " . var_export($_USERS, true) . ";\n";
    // Gunakan path absolut untuk keamanan penulisan
    if (file_put_contents(__DIR__ . '/_content/users/users.php', $usrContent) === false) {
        Utils::setError("Error writing on <strong>_content/users/users.php</strong>, check CHMOD settings");
    }
}

/**
 * Update file config.php
 */
if ($resetUser === true || $resetConfig === true) {
    $configContent = "<?php\n\n\$_CONFIG = " . var_export($_CONFIG, true) . ";\n";
    if (file_put_contents(__DIR__ . '/config.php', $configContent) === false) {
        Utils::setError("Error writing on <strong>/config.php</strong>, check CHMOD settings");
    }
}

 $updater->updateHtaccess('./');

// Redirect setelah setup selesai
header('Location: ' . $_CONFIG['script_url']);
exit;