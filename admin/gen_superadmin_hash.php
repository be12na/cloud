<?php
require __DIR__ . '/class/class.utils.php';
require __DIR__ . '/config.php';

$salt = $_CONFIG['salt'] ?? '';
if (!$salt) {
    echo "No salt configured\n";
    exit(1);
}

$hash = Utils::hashPassword($salt, 'admin123');
echo $hash . "\n";
