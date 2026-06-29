<?php
/**
 * GameStore — Products Engine (MySQL version)
 * Menggantikan products_engine.php yang berbasis JSON
 */

require_once dirname(__DIR__, 2) . '/includes/db.php';

// ── PRODUCT CRUD ──────────────────────────────────────────────
function product_get_all(): array {
    return DB::rows("SELECT * FROM v_products ORDER BY sort_order, id");
}

function product_get(int $id): ?array {
    $p = DB::row("SELECT * FROM v_products WHERE id = ?", [$id]);
    if (!$p) return null;
    $p['packages'] = DB::rows("SELECT * FROM packages WHERE product_id = ? ORDER BY sort_order, price", [$id]);
    return $p;
}

function product_create(array $data): int {
    // Cari category_id dari nama kategori
    $cat_id = DB::val("SELECT id FROM categories WHERE name = ?", [$data['category'] ?? 'Mobile']);
    if (!$cat_id) $cat_id = 1;

    $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $data['name'] ?? ''), '-'));
    // Pastikan slug unik
    $existing = DB::val("SELECT COUNT(*) FROM products WHERE slug = ?", [$slug]);
    if ($existing) $slug .= '-' . time();

    return DB::insert("
        INSERT INTO products (name, slug, category_id, currency, icon, img, img_banner, color, badge, description, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')
    ", [
        trim($data['name']),
        $slug,
        $cat_id,
        trim($data['currency'] ?? ''),
        $data['icon']        ?? '🎮',
        trim($data['img']    ?? ''),
        trim($data['img_banner'] ?? ''),
        $data['color']       ?? '#2563EB',
        $data['badge']       ?? '',
        trim($data['description'] ?? ''),
    ]);
}

function product_update(int $id, array $data): void {
    $cat_id = DB::val("SELECT id FROM categories WHERE name = ?", [$data['category'] ?? 'Mobile']);
    if (!$cat_id) $cat_id = 1;

    DB::exec("
        UPDATE products SET
            name = ?, category_id = ?, currency = ?, icon = ?,
            img = ?, img_banner = ?, color = ?, badge = ?, description = ?,
            updated_at = NOW()
        WHERE id = ?
    ", [
        trim($data['name']),
        $cat_id,
        trim($data['currency'] ?? ''),
        $data['icon']    ?? '🎮',
        trim($data['img'] ?? ''),
        trim($data['img_banner'] ?? ''),
        $data['color']   ?? '#2563EB',
        $data['badge']   ?? '',
        trim($data['description'] ?? ''),
        $id,
    ]);
}

function product_toggle_status(int $id): void {
    DB::exec("
        UPDATE products
        SET status = IF(status='active','inactive','active'), updated_at = NOW()
        WHERE id = ?
    ", [$id]);
}

function product_delete(int $id): void {
    // packages dihapus otomatis oleh CASCADE
    DB::exec("DELETE FROM products WHERE id = ?", [$id]);
}

// ── PACKAGE CRUD ──────────────────────────────────────────────
function package_add(int $product_id, int $amount, int $price, int $bonus = 0): void {
    $sort = DB::val("SELECT COALESCE(MAX(sort_order),0)+1 FROM packages WHERE product_id = ?", [$product_id]);
    DB::exec("
        INSERT INTO packages (product_id, amount, bonus, price, sort_order)
        VALUES (?, ?, ?, ?, ?)
    ", [$product_id, $amount, $bonus, $price, $sort]);
}

function package_update(int $product_id, int $pkg_id, int $amount, int $price, int $bonus = 0): void {
    DB::exec("
        UPDATE packages SET amount = ?, price = ?, bonus = ?
        WHERE id = ? AND product_id = ?
    ", [$amount, $price, $bonus, $pkg_id, $product_id]);
}

function package_delete(int $pkg_id): void {
    DB::exec("DELETE FROM packages WHERE id = ?", [$pkg_id]);
}
