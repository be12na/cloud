-- =====================================================
-- Member Registration System - Database Migration
-- =====================================================
-- Jalankan SQL ini di MySQL/MariaDB untuk membuat tabel users.
--
-- Via terminal:
--   mysql -u root -p nama_database < member/setup.sql
--
-- Atau via phpMyAdmin: Copy-paste dan execute.
-- =====================================================

CREATE TABLE IF NOT EXISTS `members` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `password` VARCHAR(255) NOT NULL COMMENT 'Bcrypt hash via password_hash()',
    `role` VARCHAR(50) NOT NULL DEFAULT 'member',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_members_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel untuk rate limiting (throttle) POST register
CREATE TABLE IF NOT EXISTS `rate_limits` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `ip_address` VARCHAR(45) NOT NULL,
    `action` VARCHAR(50) NOT NULL DEFAULT 'register',
    `attempted_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_rate_limits_lookup` (`ip_address`, `action`, `attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
