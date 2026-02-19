<?php
/**
 * VFM Installation Wizard
 * Setup pertama kali untuk Cloud File Manager
 * 
 * File ini otomatis disabled setelah instalasi selesai.
 * PHP version >= 7.3
 */

// ============================================================
// SECURITY: Block jika sudah terinstall
// ============================================================
define('VFM_APP', true);
error_reporting(E_ALL ^ E_NOTICE);

// Load minimal classes
require_once __DIR__.'/class/class.utils.php';
require_once __DIR__.'/class/class.setup.php';
require_once __DIR__.'/class/class.gatekeeper.php';
require_once __DIR__.'/class/class.admin.php';
require_once __DIR__.'/class/class.updater.php';

// Load config
if (file_exists(__DIR__.'/config.json')) {
    $_CONFIG = json_decode(file_get_contents(__DIR__.'/config.json'), true);
} elseif (file_exists(__DIR__.'/config.php')) {
    require_once __DIR__.'/config.php';
} else {
    if (file_exists(__DIR__.'/config-master.php')) {
        copy(__DIR__.'/config-master.php', __DIR__.'/config.php');
        require_once __DIR__.'/config.php';
    } else {
        exit('Config file not found');
    }
}

$setUp = new SetUp();
$gateKeeper = new GateKeeper();
$_USERS = $gateKeeper->getUsers();

// ============================================================
// AUTO-DISABLE: Jika sudah terinstall, redirect ke homepage
// ============================================================
$firstrun = $setUp->getConfig('firstrun');
$script_url = $setUp->getConfig('script_url');

if ($firstrun !== true && !empty($_USERS[0]['pass']) && !empty($script_url)) {
    header('Location: ' . $script_url);
    exit;
}

// Start session for CSRF
if (!isset($_SESSION)) {
    session_start();
}

// Auto-detect URL
$detected_url = Admin::getAppUrl();

// ============================================================
// Process form submission
// ============================================================
$errors = array();
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['vfm_install'])) {
    
    // Verify CSRF
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['setup_csrf']) 
        || $_POST['csrf_token'] !== $_SESSION['setup_csrf']) {
        $errors[] = 'Invalid security token. Please refresh and try again.';
    } else {
        // Validate inputs
        $appname = trim($_POST['appname'] ?? '');
        $domain_url = trim($_POST['domain_url'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';
        $email = trim($_POST['email'] ?? '');
        $timezone = trim($_POST['timezone'] ?? 'Asia/Jakarta');
        $lang = trim($_POST['lang'] ?? 'en');
        $require_login = isset($_POST['require_login']) ? true : false;
        $smtp_enable = isset($_POST['smtp_enable']) ? true : false;
        $smtp_server = trim($_POST['smtp_server'] ?? '');
        $smtp_port = trim($_POST['smtp_port'] ?? '587');
        $smtp_secure = trim($_POST['smtp_secure'] ?? 'tls');
        $smtp_user = trim($_POST['smtp_user'] ?? '');
        $smtp_pass = $_POST['smtp_pass'] ?? '';

        // --- Validations ---
        if (strlen($appname) < 1) {
            $errors[] = 'Nama aplikasi wajib diisi.';
        }
        if (strlen($domain_url) < 5) {
            $errors[] = 'URL domain wajib diisi.';
        } else {
            // Ensure URL ends with /
            if (substr($domain_url, -1) !== '/') {
                $domain_url .= '/';
            }
            // Ensure URL has protocol
            if (!preg_match('#^https?://#', $domain_url)) {
                $domain_url = 'https://' . $domain_url;
            }
        }
        if (strlen($username) < 3) {
            $errors[] = 'Username minimal 3 karakter.';
        }
        if (preg_match('/[^a-zA-Z0-9_\-.]/', $username)) {
            $errors[] = 'Username hanya boleh huruf, angka, underscore, strip, dan titik.';
        }
        if (strlen($password) < 6) {
            $errors[] = 'Password minimal 6 karakter.';
        }
        if ($password !== $password_confirm) {
            $errors[] = 'Password dan konfirmasi tidak cocok.';
        }
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Format email tidak valid.';
        }
        if ($smtp_enable && empty($smtp_server)) {
            $errors[] = 'SMTP server wajib diisi jika SMTP diaktifkan.';
        }

        // --- Save if no errors ---
        if (empty($errors)) {
            
            // Generate secure values
            $salt = bin2hex(random_bytes(16));
            $session_name = 'vfm_' . bin2hex(random_bytes(5));
            
            // Update config
            $_CONFIG['firstrun'] = false;
            $_CONFIG['appname'] = $appname;
            $_CONFIG['credits'] = $appname;
            $_CONFIG['script_url'] = $domain_url;
            $_CONFIG['salt'] = $salt;
            $_CONFIG['session_name'] = $session_name;
            $_CONFIG['default_timezone'] = $timezone;
            $_CONFIG['lang'] = $lang;
            $_CONFIG['require_login'] = $require_login;
            $_CONFIG['email_from'] = $email;
            $_CONFIG['email_login'] = $smtp_user ?: $email;
            $_CONFIG['email_pass'] = $smtp_pass;
            $_CONFIG['smtp_enable'] = $smtp_enable;
            $_CONFIG['smtp_server'] = $smtp_server;
            $_CONFIG['port'] = $smtp_port;
            $_CONFIG['smtp_auth'] = $smtp_enable;
            $_CONFIG['secure_conn'] = $smtp_secure;
            $_CONFIG['debug_mode'] = false;

            // Hash admin password
            $hashed_pass = Utils::hashPassword($salt, $password);

            // Build user entry
            $_USERS = array(
                array(
                    'name' => $username,
                    'pass' => $hashed_pass,
                    'role' => 'superadmin',
                    'email' => $email,
                )
            );

            // Save users
            if (!Utils::saveJson(__DIR__.'/_content/users/users.json', $_USERS)) {
                $errors[] = 'Gagal menyimpan data user. Periksa CHMOD pada _content/users/';
            }

            // Save config
            if (!Utils::saveJson(__DIR__.'/config.json', $_CONFIG)) {
                $errors[] = 'Gagal menyimpan konfigurasi. Periksa CHMOD pada admin/';
            }

            if (empty($errors)) {
                // Create uploads directory & .htaccess
                $updater = new Updater();
                $updater->updateUploadsDir();
                $updater->updateHtaccess('./');

                // Create required directories
                $dirs = array(
                    __DIR__.'/_content/thumbs',
                    __DIR__.'/_content/log',
                    __DIR__.'/_content/share',
                    __DIR__.'/_content/avatars',
                    __DIR__.'/tmp',
                );
                foreach ($dirs as $dir) {
                    if (!is_dir($dir)) {
                        @mkdir($dir, 0755, true);
                    }
                }

                $success = true;
            }
        }
    }
}

// Generate CSRF token
$csrf = bin2hex(random_bytes(32));
$_SESSION['setup_csrf'] = $csrf;

// ============================================================
// System checks
// ============================================================
$checks = array();
$checks['php_version'] = array(
    'label' => 'PHP Version',
    'value' => phpversion(),
    'ok' => version_compare(phpversion(), '7.3', '>='),
    'required' => '>= 7.3'
);
$checks['gd'] = array(
    'label' => 'GD Extension',
    'value' => extension_loaded('gd') ? 'Installed' : 'Missing',
    'ok' => extension_loaded('gd'),
    'required' => 'Required'
);
$checks['json'] = array(
    'label' => 'JSON Extension',
    'value' => extension_loaded('json') ? 'Installed' : 'Missing',
    'ok' => extension_loaded('json'),
    'required' => 'Required'
);
$checks['mbstring'] = array(
    'label' => 'Mbstring Extension',
    'value' => extension_loaded('mbstring') ? 'Installed' : 'Missing',
    'ok' => extension_loaded('mbstring'),
    'required' => 'Recommended'
);
$checks['openssl'] = array(
    'label' => 'OpenSSL Extension',
    'value' => extension_loaded('openssl') ? 'Installed' : 'Missing',
    'ok' => extension_loaded('openssl'),
    'required' => 'For SMTP'
);
$checks['fileinfo'] = array(
    'label' => 'Fileinfo Extension',
    'value' => extension_loaded('fileinfo') ? 'Installed' : 'Missing',
    'ok' => extension_loaded('fileinfo'),
    'required' => 'Recommended'
);
$checks['config_writable'] = array(
    'label' => 'admin/ Writable',
    'value' => is_writable(__DIR__) ? 'OK' : 'Not Writable',
    'ok' => is_writable(__DIR__),
    'required' => 'Required'
);
$checks['users_writable'] = array(
    'label' => '_content/users/ Writable',
    'value' => is_writable(__DIR__.'/_content/users') ? 'OK' : 'Not Writable',
    'ok' => is_writable(__DIR__.'/_content/users'),
    'required' => 'Required'
);
$uploadsDir = dirname(__DIR__).'/uploads';
$checks['uploads_writable'] = array(
    'label' => 'uploads/ Writable',
    'value' => (is_dir($uploadsDir) && is_writable($uploadsDir)) ? 'OK' : 'Not Writable',
    'ok' => is_dir($uploadsDir) && is_writable($uploadsDir),
    'required' => 'Required'
);

$hasBlocker = false;
foreach ($checks as $c) {
    if (!$c['ok'] && $c['required'] === 'Required') {
        $hasBlocker = true;
    }
}

// Load available languages
$translations = array();
$transDir = __DIR__.'/translations/';
if (file_exists($transDir.'index.json')) {
    $translations = json_decode(file_get_contents($transDir.'index.json'), true) ?: array();
}

// Load available timezones
$timezones = DateTimeZone::listIdentifiers(DateTimeZone::ALL);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cloud - Installation Wizard</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="icons/bootstrap-icons.min.css">
    <style>
        :root {
            --vfm-primary: #c00;
            --vfm-primary-dark: #900;
            --vfm-bg: #f5f6fa;
        }
        body {
            background: var(--vfm-bg);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            min-height: 100vh;
        }
        .setup-container {
            max-width: 700px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }
        .setup-header {
            text-align: center;
            padding: 2rem 0 1.5rem;
        }
        .setup-header .logo {
            width: 72px;
            height: 72px;
            background: var(--vfm-primary);
            border-radius: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            box-shadow: 0 4px 24px rgba(204,0,0,0.2);
        }
        .setup-header .logo i {
            font-size: 2rem;
            color: #fff;
        }
        .setup-header h1 {
            font-weight: 700;
            font-size: 1.6rem;
            color: #222;
            margin-bottom: .25rem;
        }
        .setup-header p {
            color: #888;
            font-size: .95rem;
        }
        .setup-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            padding: 2rem;
            margin-bottom: 1.5rem;
        }
        .setup-card h5 {
            font-weight: 700;
            margin-bottom: 1.25rem;
            padding-bottom: .75rem;
            border-bottom: 2px solid #f0f0f0;
            display: flex;
            align-items: center;
            gap: .5rem;
        }
        .setup-card h5 i {
            color: var(--vfm-primary);
        }
        .step-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: var(--vfm-primary);
            color: #fff;
            font-size: .8rem;
            font-weight: 700;
            flex-shrink: 0;
        }
        .form-label {
            font-weight: 600;
            font-size: .88rem;
            color: #444;
        }
        .form-text {
            font-size: .8rem;
        }
        .btn-install {
            background: var(--vfm-primary);
            color: #fff;
            border: none;
            padding: .75rem 2.5rem;
            font-weight: 700;
            font-size: 1.05rem;
            border-radius: 8px;
            transition: all .2s;
        }
        .btn-install:hover {
            background: var(--vfm-primary-dark);
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 4px 16px rgba(204,0,0,0.3);
        }
        .check-table td {
            padding: .4rem .5rem;
            font-size: .88rem;
        }
        .check-ok { color: #198754; }
        .check-fail { color: #dc3545; }
        .check-warn { color: #ffc107; }
        .smtp-section {
            display: none;
            padding-top: 1rem;
            border-top: 1px dashed #e0e0e0;
            margin-top: 1rem;
        }
        .smtp-section.show { display: block; }
        .success-screen {
            text-align: center;
            padding: 3rem 1rem;
        }
        .success-screen .check-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #198754;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
        }
        .success-screen .check-circle i {
            font-size: 2.5rem;
            color: #fff;
        }
        .password-toggle {
            cursor: pointer;
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #888;
            z-index: 5;
        }
        .input-icon-group {
            position: relative;
        }
    </style>
</head>
<body>

<div class="setup-container">
    <div class="setup-header">
        <div class="logo">
            <i class="bi bi-cloud-arrow-up-fill"></i>
        </div>
        <h1>Cloud Installation Wizard</h1>
        <p>Setup aplikasi file manager untuk pertama kali</p>
    </div>

    <?php if ($success): ?>
    <!-- ==================== SUCCESS SCREEN ==================== -->
    <div class="setup-card success-screen">
        <div class="check-circle">
            <i class="bi bi-check-lg"></i>
        </div>
        <h3 class="fw-bold mb-3">Instalasi Berhasil!</h3>
        <p class="text-muted mb-1">Aplikasi <strong><?php echo htmlspecialchars($appname); ?></strong> telah siap digunakan.</p>
        <p class="text-muted mb-4">
            Login sebagai <strong><?php echo htmlspecialchars($username); ?></strong> untuk mulai mengelola file.
        </p>
        <div class="bg-light rounded-3 p-3 mb-4 mx-auto" style="max-width:400px;">
            <table class="w-100" style="font-size:.9rem;">
                <tr>
                    <td class="text-muted py-1">URL</td>
                    <td class="text-end fw-bold py-1">
                        <a href="<?php echo htmlspecialchars($domain_url); ?>"><?php echo htmlspecialchars($domain_url); ?></a>
                    </td>
                </tr>
                <tr>
                    <td class="text-muted py-1">Username</td>
                    <td class="text-end fw-bold py-1"><?php echo htmlspecialchars($username); ?></td>
                </tr>
                <tr>
                    <td class="text-muted py-1">Role</td>
                    <td class="text-end py-1"><span class="badge bg-danger">Super Admin</span></td>
                </tr>
            </table>
        </div>

        <a href="<?php echo htmlspecialchars($domain_url); ?>" class="btn btn-install">
            <i class="bi bi-box-arrow-in-right me-2"></i>Buka Aplikasi
        </a>
        <div class="mt-3">
            <small class="text-muted">
                <i class="bi bi-shield-lock me-1"></i>
                Halaman setup ini sudah otomatis nonaktif.
            </small>
        </div>
    </div>

    <?php else: ?>
    <!-- ==================== INSTALLATION FORM ==================== -->
    
    <?php if (!empty($errors)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong><i class="bi bi-exclamation-triangle me-1"></i>Error:</strong>
        <ul class="mb-0 mt-1">
            <?php foreach ($errors as $err): ?>
            <li><?php echo htmlspecialchars($err); ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- System Check -->
    <div class="setup-card">
        <h5><span class="step-badge">0</span> System Requirements</h5>
        <table class="table table-sm check-table mb-0">
            <?php foreach ($checks as $check): ?>
            <tr>
                <td>
                    <?php if ($check['ok']): ?>
                        <i class="bi bi-check-circle-fill check-ok"></i>
                    <?php else: ?>
                        <?php if ($check['required'] === 'Required'): ?>
                            <i class="bi bi-x-circle-fill check-fail"></i>
                        <?php else: ?>
                            <i class="bi bi-exclamation-circle-fill check-warn"></i>
                        <?php endif; ?>
                    <?php endif; ?>
                </td>
                <td><?php echo $check['label']; ?></td>
                <td class="text-end"><code><?php echo $check['value']; ?></code></td>
                <td class="text-end text-muted" style="font-size:.78rem;"><?php echo $check['required']; ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php if ($hasBlocker): ?>
        <div class="alert alert-danger mt-3 mb-0">
            <i class="bi bi-x-octagon me-1"></i>
            <strong>Instalasi tidak bisa dilanjutkan.</strong> Perbaiki masalah bertanda "Required" di atas terlebih dahulu.
        </div>
        <?php endif; ?>
    </div>

    <?php if (!$hasBlocker): ?>
    <form method="POST" action="" autocomplete="off" id="setupForm">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>">
        <input type="hidden" name="vfm_install" value="1">

        <!-- STEP 1: App Info -->
        <div class="setup-card">
            <h5><span class="step-badge">1</span> Informasi Aplikasi</h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="appname">Nama Aplikasi *</label>
                    <input type="text" class="form-control" id="appname" name="appname" 
                           value="<?php echo htmlspecialchars($_POST['appname'] ?? 'Cloud'); ?>" 
                           placeholder="Cloud" required>
                    <div class="form-text">Ditampilkan di header & title browser</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="domain_url">URL Domain *</label>
                    <input type="url" class="form-control" id="domain_url" name="domain_url" 
                           value="<?php echo htmlspecialchars($_POST['domain_url'] ?? $detected_url); ?>" 
                           placeholder="https://cloud.example.com/" required>
                    <div class="form-text">URL lengkap termasuk https://</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="timezone">Timezone</label>
                    <select class="form-select" id="timezone" name="timezone">
                        <?php foreach ($timezones as $tz): ?>
                        <option value="<?php echo $tz; ?>" <?php echo ($tz === ($_POST['timezone'] ?? 'Asia/Jakarta')) ? 'selected' : ''; ?>>
                            <?php echo $tz; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="lang">Bahasa Default</label>
                    <select class="form-select" id="lang" name="lang">
                        <?php if (!empty($translations)): ?>
                            <?php foreach ($translations as $code => $info): ?>
                            <option value="<?php echo htmlspecialchars($code); ?>" 
                                <?php echo ($code === ($_POST['lang'] ?? 'en')) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars(is_array($info) ? ($info['name'] ?? $code) : $info); ?>
                            </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="en" selected>English</option>
                            <option value="id-ID">Bahasa Indonesia</option>
                        <?php endif; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- STEP 2: Admin Account -->
        <div class="setup-card">
            <h5><span class="step-badge">2</span> Akun Super Admin</h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="username">Username *</label>
                    <input type="text" class="form-control" id="username" name="username" 
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" 
                           placeholder="admin" required minlength="3" autocomplete="new-password">
                    <div class="form-text">Minimal 3 karakter (huruf, angka, _ - .)</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                           placeholder="admin@example.com">
                    <div class="form-text">Untuk notifikasi & recovery password</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="password">Password *</label>
                    <div class="input-icon-group">
                        <input type="password" class="form-control pe-5" id="password" name="password" 
                               placeholder="Minimal 6 karakter" required minlength="6" autocomplete="new-password">
                        <span class="password-toggle" onclick="togglePass('password')">
                            <i class="bi bi-eye"></i>
                        </span>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="password_confirm">Konfirmasi Password *</label>
                    <div class="input-icon-group">
                        <input type="password" class="form-control pe-5" id="password_confirm" name="password_confirm" 
                               placeholder="Ulangi password" required minlength="6" autocomplete="new-password">
                        <span class="password-toggle" onclick="togglePass('password_confirm')">
                            <i class="bi bi-eye"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- STEP 3: Security -->
        <div class="setup-card">
            <h5><span class="step-badge">3</span> Keamanan</h5>
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" id="require_login" name="require_login" 
                       <?php echo (isset($_POST['require_login']) || !isset($_POST['vfm_install'])) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="require_login">
                    <strong>Wajib Login</strong>
                    <div class="form-text mt-0">Jika aktif, file hanya bisa diakses oleh user yang sudah login. <br><strong class="text-danger">Sangat disarankan aktif untuk keamanan.</strong></div>
                </label>
            </div>
        </div>

        <!-- STEP 4: Email / SMTP -->
        <div class="setup-card">
            <h5><span class="step-badge">4</span> Email / SMTP <span class="badge bg-secondary fw-normal ms-auto" style="font-size:.7rem;">Opsional</span></h5>
            <p class="text-muted" style="font-size:.88rem;">
                Konfigurasi SMTP diperlukan untuk fitur kirim file via email, notifikasi, dan reset password.
                Bisa juga diatur nanti di Admin Panel.
            </p>
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" id="smtp_enable" name="smtp_enable" 
                       <?php echo isset($_POST['smtp_enable']) ? 'checked' : ''; ?>
                       onchange="toggleSmtp()">
                <label class="form-check-label" for="smtp_enable"><strong>Aktifkan SMTP</strong></label>
            </div>

            <div class="smtp-section" id="smtpSection">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="smtp_server">SMTP Server</label>
                        <input type="text" class="form-control" id="smtp_server" name="smtp_server" 
                               value="<?php echo htmlspecialchars($_POST['smtp_server'] ?? ''); ?>" 
                               placeholder="smtp.gmail.com">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="smtp_port">Port</label>
                        <input type="text" class="form-control" id="smtp_port" name="smtp_port" 
                               value="<?php echo htmlspecialchars($_POST['smtp_port'] ?? '587'); ?>" 
                               placeholder="587">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="smtp_secure">Encryption</label>
                        <select class="form-select" id="smtp_secure" name="smtp_secure">
                            <option value="tls" <?php echo (($_POST['smtp_secure'] ?? 'tls') === 'tls') ? 'selected' : ''; ?>>TLS</option>
                            <option value="ssl" <?php echo (($_POST['smtp_secure'] ?? '') === 'ssl') ? 'selected' : ''; ?>>SSL</option>
                            <option value="none" <?php echo (($_POST['smtp_secure'] ?? '') === 'none') ? 'selected' : ''; ?>>None</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="smtp_user">SMTP Username</label>
                        <input type="text" class="form-control" id="smtp_user" name="smtp_user" 
                               value="<?php echo htmlspecialchars($_POST['smtp_user'] ?? ''); ?>" 
                               placeholder="user@gmail.com">
                        <div class="form-text">Biasanya alamat email Anda</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="smtp_pass">SMTP Password</label>
                        <div class="input-icon-group">
                            <input type="password" class="form-control pe-5" id="smtp_pass" name="smtp_pass" 
                                   value="<?php echo htmlspecialchars($_POST['smtp_pass'] ?? ''); ?>" 
                                   placeholder="App Password">
                            <span class="password-toggle" onclick="togglePass('smtp_pass')">
                                <i class="bi bi-eye"></i>
                            </span>
                        </div>
                        <div class="form-text">Untuk Gmail, gunakan <a href="https://myaccount.google.com/apppasswords" target="_blank">App Password</a></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SUBMIT -->
        <div class="text-center pb-4">
            <button type="submit" class="btn btn-install" id="btnInstall">
                <i class="bi bi-rocket-takeoff me-2"></i>Install Sekarang
            </button>
            <div class="mt-2">
                <small class="text-muted">
                    Dengan mengklik tombol di atas, konfigurasi akan disimpan dan aplikasi siap digunakan.
                </small>
            </div>
        </div>
    </form>
    <?php endif; ?>

    <?php endif; ?>

    <div class="text-center pb-4">
        <small class="text-muted">Cloud File Manager &middot; Powered by VenoFileManager</small>
    </div>
</div>

<script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
<script>
function togglePass(id) {
    var input = document.getElementById(id);
    var icon = input.parentElement.querySelector('.password-toggle i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye';
    }
}

function toggleSmtp() {
    var section = document.getElementById('smtpSection');
    var checkbox = document.getElementById('smtp_enable');
    section.classList.toggle('show', checkbox.checked);
}

// Init on load
document.addEventListener('DOMContentLoaded', function() {
    toggleSmtp();

    // Client-side form validation
    var form = document.getElementById('setupForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            var pw = document.getElementById('password').value;
            var pwc = document.getElementById('password_confirm').value;
            if (pw !== pwc) {
                e.preventDefault();
                alert('Password dan konfirmasi password tidak sama!');
                return false;
            }
            if (pw.length < 6) {
                e.preventDefault();
                alert('Password minimal 6 karakter!');
                return false;
            }
            // Disable button to prevent double submission
            var btn = document.getElementById('btnInstall');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Installing...';
        });
    }
});
</script>
</body>
</html>