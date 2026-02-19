#!/usr/bin/env php
<?php
/**
 * Deploy Config Sync Script
 * 
 * Merges config.php defaults into config.json on the server.
 * - New keys from config.php → added to config.json
 * - Keys marked as "force update" → always overwritten from config.php
 * - All other existing keys in config.json → preserved (user settings)
 * 
 * Usage: php admin/deploy-sync-config.php
 * Called automatically by .cpanel.yml after git pull
 */

$basedir = __DIR__;
$phpPath = $basedir . '/config.php';
$jsonPath = $basedir . '/config.json';

// -----------------------------------------------------------
// Keys that should ALWAYS be updated from config.php
// (code-managed values, not user-editable via admin panel)
// -----------------------------------------------------------
$forceUpdateKeys = [
    'upload_allow_type',  // managed in code, not in admin panel
];

// Load config.php defaults
$_CONFIG = false;
if (!file_exists($phpPath)) {
    echo "[deploy-sync] ERROR: config.php not found at {$phpPath}\n";
    exit(1);
}
include $phpPath;
if (!is_array($_CONFIG)) {
    echo "[deploy-sync] ERROR: config.php did not define \$_CONFIG array\n";
    exit(1);
}
$defaults = $_CONFIG;

// Load existing config.json (if exists)
$existing = [];
if (file_exists($jsonPath)) {
    $content = file_get_contents($jsonPath);
    if ($content !== false) {
        $decoded = json_decode($content, true);
        if (is_array($decoded)) {
            $existing = $decoded;
        }
    }
}

if (empty($existing)) {
    // No config.json yet — write full defaults
    $merged = $defaults;
    echo "[deploy-sync] config.json not found, creating from config.php defaults\n";
} else {
    $merged = $existing;
    $added = [];
    $updated = [];

    foreach ($defaults as $key => $value) {
        // Force update keys: always overwrite
        if (in_array($key, $forceUpdateKeys, true)) {
            if (!isset($existing[$key]) || $existing[$key] !== $value) {
                $merged[$key] = $value;
                $updated[] = $key;
            }
            continue;
        }
        // New key: add from defaults
        if (!array_key_exists($key, $existing)) {
            $merged[$key] = $value;
            $added[] = $key;
        }
        // Existing key: keep user's value (no overwrite)
    }

    if (!empty($added)) {
        echo "[deploy-sync] Added new keys: " . implode(', ', $added) . "\n";
    }
    if (!empty($updated)) {
        echo "[deploy-sync] Force-updated keys: " . implode(', ', $updated) . "\n";
    }
    if (empty($added) && empty($updated)) {
        echo "[deploy-sync] config.json is up to date, no changes needed\n";
        exit(0);
    }
}

// Save merged config
$json = json_encode($merged, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
if ($json === false) {
    echo "[deploy-sync] ERROR: json_encode failed\n";
    exit(1);
}
if (file_put_contents($jsonPath, $json, LOCK_EX) === false) {
    echo "[deploy-sync] ERROR: Could not write to {$jsonPath}\n";
    exit(1);
}

echo "[deploy-sync] config.json synced successfully\n";
exit(0);
