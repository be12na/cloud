<?php
/**
 * ajax/save-cloud-settings.php
 *
 * Save Cloud Name and Domain URL settings (SuperAdmin only)
 *
 * PHP version >= 7.3
 */

// Block non-AJAX requests
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) 
    || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest'
) {
    http_response_code(403);
    exit;
}

require_once dirname(dirname(__FILE__)).'/class/class.utils.php';
require_once dirname(dirname(__FILE__)).'/class/class.setup.php';
require_once dirname(dirname(__FILE__)).'/class/class.gatekeeper.php';

$setUp = new SetUp();
$gateKeeper = new GateKeeper();

header('Content-Type: application/json');

// Must be logged in
if (!$gateKeeper->isUserLoggedIn()) {
    echo json_encode(array('status' => 'error', 'message' => 'Unauthorized'));
    exit;
}

// Verify CSRF token
if (!Utils::verifyCsrfToken()) {
    echo json_encode(array('status' => 'error', 'message' => 'Invalid security token'));
    exit;
}

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(array('status' => 'error', 'message' => 'Invalid request'));
    exit;
}

$appname = filter_input(INPUT_POST, 'appname', FILTER_DEFAULT);
$appname = $appname !== null ? htmlspecialchars(trim($appname), ENT_QUOTES, 'UTF-8') : '';
$script_url = filter_input(INPUT_POST, 'script_url', FILTER_SANITIZE_URL);

$errors = array();

// Validate App Name
if (!$appname || strlen(trim($appname)) < 1) {
    $errors[] = 'Nama aplikasi wajib diisi.';
}

// Validate URL
if (!$script_url || strlen(trim($script_url)) < 5) {
    $errors[] = 'URL domain wajib diisi.';
} else {
    // Ensure URL has protocol
    if (!preg_match('#^https?://#', $script_url)) {
        $script_url = 'https://' . $script_url;
    }
    // Ensure URL ends with /
    if (substr($script_url, -1) !== '/') {
        $script_url .= '/';
    }
    // Basic URL validation
    if (!filter_var($script_url, FILTER_VALIDATE_URL)) {
        $errors[] = 'Format URL tidak valid.';
    }
}

if (!empty($errors)) {
    echo json_encode(array('status' => 'error', 'message' => implode(' ', $errors)));
    exit;
}

// Load current config
$configPath = dirname(dirname(__FILE__));
if (file_exists($configPath.'/config.json')) {
    $_CONFIG = json_decode(file_get_contents($configPath.'/config.json'), true);
} elseif (file_exists($configPath.'/config.php')) {
    require $configPath.'/config.php';
} else {
    echo json_encode(array('status' => 'error', 'message' => 'Config file not found'));
    exit;
}

if (!is_array($_CONFIG)) {
    echo json_encode(array('status' => 'error', 'message' => 'Config data is corrupt'));
    exit;
}

// Update values
$_CONFIG['appname'] = trim($appname);
$_CONFIG['credits'] = trim($appname);
$_CONFIG['script_url'] = $script_url;

// Save config
if (Utils::saveJson($configPath.'/config.json', $_CONFIG)) {
    echo json_encode(array(
        'status' => 'success', 
        'message' => 'Pengaturan berhasil disimpan.',
        'appname' => htmlspecialchars(trim($appname)),
        'script_url' => htmlspecialchars($script_url),
    ));
} else {
    echo json_encode(array('status' => 'error', 'message' => 'Gagal menyimpan konfigurasi. Periksa permission folder.'));
}
