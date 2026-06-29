-- ============================================================
--  GAMESTORE DATABASE SCHEMA
--  MySQL 5.7+ / MariaDB 10.3+
--  Charset : utf8mb4
--  Engine  : InnoDB
-- ============================================================
-- Jalankan file ini di phpMyAdmin atau CLI:
--   mysql -u root -p gamestore_db < schema.sql
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';

-- ── 1. CATEGORIES ────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `categories` (
    `id`         TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(50)  NOT NULL,
    `slug`       VARCHAR(60)  NOT NULL,
    `icon`       VARCHAR(10)  NOT NULL DEFAULT '🎮',
    `sort_order` TINYINT      NOT NULL DEFAULT 0,
    `is_active`  TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_category_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 2. PRODUCTS ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `products` (
    `id`          SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `category_id` TINYINT UNSIGNED  NOT NULL,
    `name`        VARCHAR(100) NOT NULL,
    `slug`        VARCHAR(120) NOT NULL,
    `currency`    VARCHAR(50)  NOT NULL,
    `description` TEXT,
    `icon`        VARCHAR(10)  NOT NULL DEFAULT '🎮',
    `img`         VARCHAR(500) DEFAULT NULL,
    `img_banner`  VARCHAR(500) DEFAULT NULL,
    `color`       VARCHAR(10)  NOT NULL DEFAULT '#2563EB',
    `badge`       VARCHAR(30)  DEFAULT NULL,
    `status`      ENUM('active','inactive') NOT NULL DEFAULT 'active',
    `sort_order`  SMALLINT     NOT NULL DEFAULT 0,
    `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_product_slug` (`slug`),
    KEY `idx_product_status` (`status`),
    KEY `idx_product_category` (`category_id`),
    CONSTRAINT `fk_product_category`
        FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 3. PACKAGES ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `packages` (
    `id`         INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    `product_id` SMALLINT UNSIGNED NOT NULL,
    `amount`     INT UNSIGNED      NOT NULL COMMENT 'Jumlah item (diamond, UC, dll)',
    `price`      INT UNSIGNED      NOT NULL COMMENT 'Harga dalam Rupiah',
    `bonus`      INT UNSIGNED      NOT NULL DEFAULT 0,
    `is_active`  TINYINT(1)        NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_package_product` (`product_id`),
    KEY `idx_package_active`  (`is_active`),
    CONSTRAINT `fk_package_product`
        FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 4. USERS (customers) ─────────────────────────────────────
CREATE TABLE IF NOT EXISTS `users` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`            VARCHAR(100) NOT NULL,
    `email`           VARCHAR(150) DEFAULT NULL,
    `phone`           VARCHAR(20)  DEFAULT NULL,
    `password_hash`   VARCHAR(255) DEFAULT NULL,
    `role`            ENUM('customer','admin') NOT NULL DEFAULT 'customer',
    `status`          ENUM('active','banned','unverified') NOT NULL DEFAULT 'active',
    `avatar`          VARCHAR(500) DEFAULT NULL,
    `last_login_at`   TIMESTAMP    DEFAULT NULL,
    `email_verified_at` TIMESTAMP  DEFAULT NULL,
    `remember_token`  VARCHAR(100) DEFAULT NULL,
    `created_at`      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_user_email` (`email`),
    KEY `idx_user_role`   (`role`),
    KEY `idx_user_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 5. ORDERS ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `orders` (
    `id`               VARCHAR(20)   NOT NULL COMMENT 'Format: GS240101XXXX',
    `user_id`          INT UNSIGNED  DEFAULT NULL,
    `customer_name`    VARCHAR(100)  NOT NULL,
    `customer_phone`   VARCHAR(20)   DEFAULT NULL,
    `product_id`       SMALLINT UNSIGNED DEFAULT NULL,
    `package_id`       INT UNSIGNED  DEFAULT NULL,
    `product_name`     VARCHAR(100)  NOT NULL COMMENT 'Snapshot nama produk',
    `package_info`     VARCHAR(100)  NOT NULL COMMENT 'Snapshot info paket: "86 Diamond"',
    `game_user_id`     VARCHAR(100)  NOT NULL COMMENT 'User ID akun game customer',
    `price`            INT UNSIGNED  NOT NULL,
    `payment_method`   VARCHAR(50)   NOT NULL,
    `payment_account`  VARCHAR(50)   DEFAULT NULL COMMENT 'Nomor rekening tujuan',
    `promo_code`       VARCHAR(30)   DEFAULT NULL,
    `discount`         INT UNSIGNED  NOT NULL DEFAULT 0,
    `total`            INT UNSIGNED  NOT NULL,
    `proof_image`      VARCHAR(500)  DEFAULT NULL COMMENT 'URL/path bukti transfer',
    `note`             TEXT          DEFAULT NULL,
    `status`           ENUM('pending','processing','completed','cancelled','refunded')
                       NOT NULL DEFAULT 'pending',
    `admin_note`       TEXT          DEFAULT NULL,
    `completed_at`     TIMESTAMP     DEFAULT NULL,
    `created_at`       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_order_user`    (`user_id`),
    KEY `idx_order_product` (`product_id`),
    KEY `idx_order_status`  (`status`),
    KEY `idx_order_created` (`created_at`),
    CONSTRAINT `fk_order_user`
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_order_product`
        FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_order_package`
        FOREIGN KEY (`package_id`) REFERENCES `packages`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 6. PROMO CODES ───────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `promo_codes` (
    `id`           SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `code`         VARCHAR(30)  NOT NULL,
    `description`  VARCHAR(200) DEFAULT NULL,
    `type`         ENUM('percent','fixed') NOT NULL DEFAULT 'percent',
    `value`        INT UNSIGNED NOT NULL COMMENT 'Persen atau nominal Rupiah',
    `max_discount` INT UNSIGNED DEFAULT NULL COMMENT 'Maks diskon untuk tipe persen',
    `min_purchase` INT UNSIGNED NOT NULL DEFAULT 0,
    `max_use`      INT UNSIGNED DEFAULT NULL COMMENT 'NULL = unlimited',
    `used_count`   INT UNSIGNED NOT NULL DEFAULT 0,
    `valid_from`   DATE         DEFAULT NULL,
    `valid_until`  DATE         DEFAULT NULL,
    `is_active`    TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_promo_code` (`code`),
    KEY `idx_promo_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 7. CHAT SESSIONS ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `chat_sessions` (
    `id`               VARCHAR(20)  NOT NULL,
    `user_id`          INT UNSIGNED DEFAULT NULL,
    `customer_name`    VARCHAR(100) NOT NULL,
    `customer_email`   VARCHAR(150) DEFAULT NULL,
    `topic`            VARCHAR(100) DEFAULT 'Umum',
    `status`           ENUM('open','pending','resolved') NOT NULL DEFAULT 'open',
    `last_message`     VARCHAR(200) DEFAULT NULL,
    `unread_admin`     SMALLINT     NOT NULL DEFAULT 0,
    `unread_customer`  SMALLINT     NOT NULL DEFAULT 0,
    `admin_typing`     TINYINT(1)   NOT NULL DEFAULT 0,
    `customer_typing`  TINYINT(1)   NOT NULL DEFAULT 0,
    `typing_ts`        INT UNSIGNED DEFAULT NULL,
    `last_time`        INT UNSIGNED NOT NULL,
    `created_at`       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_chat_status`  (`status`),
    KEY `idx_chat_user`    (`user_id`),
    KEY `idx_chat_time`    (`last_time`),
    CONSTRAINT `fk_chat_user`
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 8. CHAT MESSAGES ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `chat_messages` (
    `id`          VARCHAR(20)  NOT NULL,
    `session_id`  VARCHAR(20)  NOT NULL,
    `sender`      ENUM('admin','customer') NOT NULL,
    `sender_name` VARCHAR(100) NOT NULL,
    `message`     TEXT         NOT NULL,
    `type`        ENUM('text','image','file') NOT NULL DEFAULT 'text',
    `is_system`   TINYINT(1)   NOT NULL DEFAULT 0,
    `is_read`     TINYINT(1)   NOT NULL DEFAULT 0,
    `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_msg_session`  (`session_id`),
    KEY `idx_msg_created`  (`created_at`),
    CONSTRAINT `fk_msg_session`
        FOREIGN KEY (`session_id`) REFERENCES `chat_sessions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 9. SETTINGS ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `settings` (
    `key`         VARCHAR(100) NOT NULL,
    `value`       TEXT         DEFAULT NULL,
    `group`       VARCHAR(50)  NOT NULL DEFAULT 'general',
    `updated_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`key`),
    KEY `idx_setting_group` (`group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 10. ADMIN ACTIVITY LOG ───────────────────────────────────
CREATE TABLE IF NOT EXISTS `admin_logs` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `admin_id`   INT UNSIGNED DEFAULT NULL,
    `action`     VARCHAR(100) NOT NULL,
    `target`     VARCHAR(100) DEFAULT NULL,
    `detail`     TEXT         DEFAULT NULL,
    `ip_address` VARCHAR(45)  DEFAULT NULL,
    `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_log_admin`   (`admin_id`),
    KEY `idx_log_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
