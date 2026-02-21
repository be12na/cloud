<?php
/**
 * RateLimit - Throttling untuk POST requests
 * 
 * Menyimpan attempt di tabel rate_limits dan mengecek 
 * apakah IP tertentu sudah melebihi batas dalam window waktu.
 */

class RateLimit
{
    private PDO $db;

    /**
     * @param int $maxAttempts  Maksimal percobaan dalam window
     * @param int $windowSeconds  Durasi window dalam detik (default: 300 = 5 menit)
     */
    public function __construct(
        private int $maxAttempts = 5,
        private int $windowSeconds = 300
    ) {
        $this->db = Database::getConnection();
    }

    /**
     * Cek apakah IP masih dalam batas rate limit.
     */
    public function isAllowed(string $ipAddress, string $action = 'register'): bool
    {
        $this->cleanup($action);

        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM rate_limits 
             WHERE ip_address = :ip 
             AND action = :action 
             AND attempted_at > DATE_SUB(NOW(), INTERVAL :window SECOND)'
        );
        $stmt->execute([
            ':ip'     => $ipAddress,
            ':action' => $action,
            ':window' => $this->windowSeconds,
        ]);

        return (int) $stmt->fetchColumn() < $this->maxAttempts;
    }

    /**
     * Catat satu attempt.
     */
    public function record(string $ipAddress, string $action = 'register'): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO rate_limits (ip_address, action, attempted_at) VALUES (:ip, :action, NOW())'
        );
        $stmt->execute([
            ':ip'     => $ipAddress,
            ':action' => $action,
        ]);
    }

    /**
     * Hitung sisa waktu (detik) sebelum bisa coba lagi.
     */
    public function getRetryAfter(string $ipAddress, string $action = 'register'): int
    {
        $stmt = $this->db->prepare(
            'SELECT MIN(attempted_at) AS oldest FROM rate_limits 
             WHERE ip_address = :ip 
             AND action = :action 
             AND attempted_at > DATE_SUB(NOW(), INTERVAL :window SECOND)'
        );
        $stmt->execute([
            ':ip'     => $ipAddress,
            ':action' => $action,
            ':window' => $this->windowSeconds,
        ]);

        $row = $stmt->fetch();
        if (!$row || !$row['oldest']) {
            return 0;
        }

        $oldest = strtotime($row['oldest']);
        $retryAt = $oldest + $this->windowSeconds;
        $remaining = $retryAt - time();

        return max(0, $remaining);
    }

    /**
     * Bersihkan record lama yang sudah di luar window.
     */
    private function cleanup(string $action): void
    {
        $stmt = $this->db->prepare(
            'DELETE FROM rate_limits 
             WHERE action = :action 
             AND attempted_at < DATE_SUB(NOW(), INTERVAL :window SECOND)'
        );
        $stmt->execute([
            ':action' => $action,
            ':window' => $this->windowSeconds * 2, // Cleanup records 2x window
        ]);
    }
}
