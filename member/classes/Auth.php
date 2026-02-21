<?php
/**
 * Auth - Authentication Manager
 * 
 * Menangani:
 * - Register (dengan password_hash bcrypt)
 * - Login (dengan password_verify)
 * - Session management (regenerate, destroy)
 * - Auto-login setelah register
 */

class Auth
{
    private const SESSION_USER_ID   = 'member_user_id';
    private const SESSION_USER_NAME = 'member_user_name';
    private const SESSION_USER_ROLE = 'member_user_role';
    private const SESSION_LOGGED_IN = 'member_logged_in';

    /**
     * Register user baru dan langsung login.
     * 
     * @param string $name
     * @param string $email  
     * @param string $password  Plain text (akan di-hash)
     * @param string $role      Default 'member'
     * @return array User data yang baru dibuat
     * @throws RuntimeException Jika gagal insert
     */
    public static function register(string $name, string $email, string $password, string $role = 'member'): array
    {
        $db = Database::getConnection();

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

        if ($hashedPassword === false) {
            throw new RuntimeException('Gagal hashing password.');
        }

        $stmt = $db->prepare(
            'INSERT INTO members (name, email, password, role, created_at, updated_at) 
             VALUES (:name, :email, :password, :role, NOW(), NOW())'
        );

        $stmt->execute([
            ':name'     => $name,
            ':email'    => $email,
            ':password' => $hashedPassword,
            ':role'     => $role,
        ]);

        $userId = (int) $db->lastInsertId();

        $user = [
            'id'    => $userId,
            'name'  => $name,
            'email' => $email,
            'role'  => $role,
        ];

        // Auto-login setelah register
        self::loginUser($user);

        return $user;
    }

    /**
     * Attempt login dengan email dan password.
     * 
     * @param string $email
     * @param string $password
     * @return array|false User data jika berhasil, false jika gagal
     */
    public static function attempt($email, $password)
    {
        $db = Database::getConnection();

        $stmt = $db->prepare('SELECT id, name, email, password, role FROM members WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);

        $user = $stmt->fetch();

        if (!$user) {
            return false;
        }

        // Verifikasi password dengan password_verify (timing-safe)
        if (!password_verify($password, $user['password'])) {
            return false;
        }

        // Jangan simpan hash password di session
        unset($user['password']);

        self::loginUser($user);

        return $user;
    }

    /**
     * Set session variables setelah berhasil login.
     * Regenerate session ID untuk mencegah session fixation.
     */
    private static function loginUser(array $user): void
    {
        // Regenerate session ID (anti session fixation)
        session_regenerate_id(true);

        $_SESSION[self::SESSION_USER_ID]   = $user['id'];
        $_SESSION[self::SESSION_USER_NAME] = $user['name'];
        $_SESSION[self::SESSION_USER_ROLE] = $user['role'];
        $_SESSION[self::SESSION_LOGGED_IN] = true;
    }

    /**
     * Cek apakah user sudah login.
     */
    public static function check(): bool
    {
        return !empty($_SESSION[self::SESSION_LOGGED_IN]) 
            && $_SESSION[self::SESSION_LOGGED_IN] === true
            && !empty($_SESSION[self::SESSION_USER_ID]);
    }

    /**
     * Cek apakah user login dan memiliki role tertentu.
     */
    public static function hasRole(string $role): bool
    {
        return self::check() && ($_SESSION[self::SESSION_USER_ROLE] ?? '') === $role;
    }

    /**
     * Ambil info user yang sedang login.
     */
    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }

        return [
            'id'   => $_SESSION[self::SESSION_USER_ID],
            'name' => $_SESSION[self::SESSION_USER_NAME],
            'role' => $_SESSION[self::SESSION_USER_ROLE],
        ];
    }

    /**
     * Ambil ID user yang sedang login.
     */
    public static function id(): ?int
    {
        return self::check() ? (int) $_SESSION[self::SESSION_USER_ID] : null;
    }

    /**
     * Logout: destroy session dan regenerate ID.
     */
    public static function logout(): void
    {
        // Hapus semua session variables member
        unset(
            $_SESSION[self::SESSION_USER_ID],
            $_SESSION[self::SESSION_USER_NAME],
            $_SESSION[self::SESSION_USER_ROLE],
            $_SESSION[self::SESSION_LOGGED_IN]
        );

        // Regenerate session ID
        session_regenerate_id(true);
    }

    /**
     * Cek apakah user adalah guest (belum login).
     */
    public static function isGuest(): bool
    {
        return !self::check();
    }
}
