<?php
/**
 * Konfigurasi Database - Member System
 * 
 * Ubah nilai di bawah sesuai environment server Anda.
 * JANGAN commit file ini dengan kredensial production ke Git.
 */

return [
    'host'     => '127.0.0.1',
    'port'     => '3306',
    'dbname'   => 'cloud_member',   // Ganti dengan nama database Anda
    'username' => 'root',           // Ganti dengan username database
    'password' => '',               // Ganti dengan password database
    'charset'  => 'utf8mb4',
    'options'  => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_PERSISTENT         => false,
    ],
];
