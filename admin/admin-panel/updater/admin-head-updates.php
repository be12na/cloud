<?php


if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    /**
    * General Settings
    */
    $_CONFIG['license_key'] = filter_input(INPUT_POST, "license_key", FILTER_SANITIZE_URL);
    // Save settings.
    if (!Utils::saveJson('config.json', $_CONFIG)) {
        Utils::setError('Error saving config file');
    } else {
        Utils::setSuccess($setUp->getString('settings_updated'));
        $updater->clearCache('config.php');
    }
    header('Location:'.$script_url.'admin/?section=updates');
    exit();
}