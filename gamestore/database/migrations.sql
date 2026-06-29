-- ============================================================
--  GAMESTORE DATABASE MIGRATIONS
--  Tambahkan ALTER TABLE, kolom baru, dll di sini.
--  Jalankan setelah update versi baru.
-- ============================================================

-- v1.1 — Tambah kolom verified_at di orders
-- ALTER TABLE `orders` ADD COLUMN `verified_at` TIMESTAMP NULL AFTER `proof_image`;

-- v1.2 — Tambah kolom total_orders di users (denormalisasi untuk performa)
-- ALTER TABLE `users` ADD COLUMN `total_orders` INT UNSIGNED NOT NULL DEFAULT 0;
-- ALTER TABLE `users` ADD COLUMN `total_spent` BIGINT UNSIGNED NOT NULL DEFAULT 0;

-- v1.3 — Tambah tabel wishlist
-- CREATE TABLE IF NOT EXISTS `wishlists` (
--     `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
--     `user_id`    INT UNSIGNED NOT NULL,
--     `product_id` SMALLINT UNSIGNED NOT NULL,
--     `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
--     PRIMARY KEY (`id`),
--     UNIQUE KEY `uq_wishlist` (`user_id`,`product_id`),
--     CONSTRAINT `fk_wl_user`    FOREIGN KEY (`user_id`)    REFERENCES `users`(`id`)    ON DELETE CASCADE,
--     CONSTRAINT `fk_wl_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Catatan: Uncomment dan jalankan baris yang diperlukan sesuai versi update.
