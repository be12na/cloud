<?php
/**
 * Database - PDO Singleton Connection
 * 
 * Menggunakan PDO dengan prepared statements untuk mencegah SQL injection.
 * Pattern: Singleton agar satu koneksi per request.
 */

class Database
{
    private static ?PDO $instance = null;

    /**
     * Mendapatkan koneksi PDO (singleton).
     */
    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $config = require __DIR__ . '/../config/database.php';

            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $config['host'],
                $config['port'],
                $config['dbname'],
                $config['charset']
            );

            try {
                self::$instance = new PDO($dsn, $config['username'], $config['password'], $config['options']);
            } catch (PDOException $e) {
                // Jangan tampilkan detail error koneksi ke user (keamanan)
                error_log('Database connection failed: ' . $e->getMessage());
                throw new RuntimeException('Koneksi database gagal. Silakan hubungi administrator.');
            }
        }

        return self::$instance;
    }

    /**
     * Mencegah clone dan unserialize.
     */
    private function __construct() {}
    private function __clone() {}
    public function __wakeup()
    {
        throw new RuntimeException('Cannot unserialize singleton');
    }
}
