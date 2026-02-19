<?php
/**
 * ajax/usr-check.php
 *
 * Check if username exists before registration
 *
 * PHP version >= 5.3
 *
 *
 */
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) 
    || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest')
) {
    exit;
}
require_once dirname(dirname(__FILE__)).'/class/class.setup.php';
require_once dirname(dirname(__FILE__)).'/class/class.utils.php';
require_once dirname(dirname(__FILE__)).'/class/class.gatekeeper.php';
require_once dirname(dirname(__FILE__)).'/class/class.updater.php';

$updater = new Updater();
$gateKeeper = new GateKeeper();
// $postname = htmlspecialchars($_POST['user_name']);
$postname = filter_input(INPUT_POST, "user_name", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
if ($postname) {
    $postname = preg_replace('/\s+/', '', $postname);
    if ($updater->findUser($postname) || $updater->findUserPre($postname)) {
        echo 'error';
    } else {
        echo 'success';
    }
}
exit();
