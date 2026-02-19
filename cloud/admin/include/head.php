<?php
/**
 *
 * PHP version >= 5.3
 *
 *
 */
if (file_exists(dirname(dirname(dirname(__FILE__))).'/.maintenance')) {
    exit('<h2>Briefly unavailable for scheduled maintenance. Check back in a minute.</h2>');
}
if (!defined('VFM_APP')) {
    return false;
}

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

$php_min = '7.3';
if (version_compare(phpversion(), $php_min, '<')) {
    // PHP version too low.
    header('Content-type: text/html; charset=utf-8');
    exit('<h2>Veno File Manager 4 requires PHP >= '.$php_min.'</h2><p>Current: PHP '.phpversion().', please update your server settings.</p>');
}
if (!file_exists('admin/config.json') && !file_exists('admin/config.php')) {
    if (!copy('admin/config-master.php', 'admin/config.php')) {
        exit("failed to create the main config.php file, check CHMOD on /admin/");
    }
}

if (!file_exists('admin/_content/users/users.json') && !file_exists('admin/_content/users/users.php')) {
    if (!copy('admin/_content/users/users-master.php', 'admin/_content/users/users.php')) {
        exit("failed to create the main users.php file, check CHMOD on /admin/_content/users/");
    }
}

require_once 'admin/class.php';

$setUp = new SetUp();

if ($setUp->getConfig('debug_mode') === true) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL ^ E_NOTICE);
}

// Redirect blacklisted IPs.
Utils::checkIP();
global $translations_index;
$translations_index = json_decode(file_get_contents('admin/translations/index.json'), true);

$gateKeeper = new GateKeeper();
$_USERS = $gateKeeper->getUsers();

// Generate CSRF token for all forms
$csrf_token = Utils::generateCsrfToken();

if ($setUp->getConfig("firstrun") === true || strlen($_USERS[0]['pass']) < 1) {
    header('Location:admin/setup.php');
    exit;
}

$updater = new Updater();
$location = new Location();
$downloader = new Downloader();
$imageServer = new ImageServer();
$resetter = new Resetter();

$gateKeeper->init();
$updater->init();
$resetter->init();

$updater->updateUploadsDir();

if ($gateKeeper->isAccessAllowed()) {
    new Actions($location);
};

$template = new Template();

$getdownloadlist = filter_input(INPUT_GET, "dl", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$getrp = filter_input(INPUT_GET, "rp", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$getreg = filter_input(INPUT_GET, "reg", FILTER_SANITIZE_FULL_SPECIAL_CHARS);

$rtl_ext = '';
$rtl_att = '';
$rtl_class = '';
if ($setUp->getConfig("txt_direction") == "RTL") {
    $rtl_att = ' dir="rtl"';
    $rtl_ext = '.rtl';
    $rtl_class = ' rtl';
}
$bodyclass = 'vfm-body d-flex flex-column justify-content-between min-vh-100';
$bodyclass .= ($setUp->getConfig('inline_thumbs') == true) ? ' inlinethumbs' : '';
$bodyclass .= (!$gateKeeper->isAccessAllowed()) ? ' unlogged' : '';
$bodyclass .= ($setUp->getConfig('header_position') == 'below') ? ' pt-5' : '';
$bodyclass .= ' header-'.$setUp->getConfig('header_position');
$bodyclass .= ' role-'.$gateKeeper->getUserInfo('role');
$bodyclass .= $rtl_class;
$bodydata = $setUp->getConfig('audio_notification') ? ' data-ping="'.$setUp->getConfig('audio_notification').'"' : '';
