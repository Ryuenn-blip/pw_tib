<?php
/**
 * Products Engine — DB Version
 * Semua operasi CRUD produk via PDO
 */

require_once dirname(__DIR__, 2) . '/includes/db.php';

function product_get_all(): array {
    return db_rows("
        SELECT p.*, c.name AS category_name,
               COALESCE(MIN(pk.price),0) AS min_price,
               COUNT(pk.id) AS package_count
        FROM   products p
        JOIN   categories c   ON c.id = p.category_id
        LEFT JOIN packages pk ON pk.product_id = p.id AND pk.status = 'active'
        GROUP BY p.id
        ORDER BY p.sort_order, p.id
    ");
}

function product_get(int $id): ?array {
    $p = db_row("SELECT p.*, c.name AS category_name FROM products p
                 JOIN categories c ON c.id = p.category_id WHERE p.id = ?", [$id]);
    if (!$p) return null;
    $p['packages'] = db_rows("SELECT * FROM packages WHERE product_id = ? ORDER BY price", [$id]);
    return $p;
}

function product_create(array $data): int {
    $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $data['name']), '-'));
    // cari category_id
    $cat  = db_row("SELECT id FROM categories WHERE name = ?", [$data['category'] ?? 'Mobile']);
    $cid  = $cat ? $cat['id'] : 1;
    return (int)db_insert("
        INSERT INTO products (category_id,name,slug,currency,icon,img,img_banner,color,badge,description,status)
        VALUES (?,?,?,?,?,?,?,?,?,?,'active')
    ", [$cid, trim($data['name']), $slug, trim($data['currency']),
        $data['icon']??'🎮', trim($data['img']??''), trim($data['img_banner']??''),
        $data['color']??'#2563EB', $data['badge']??'', trim($data['description']??'')]);
}

function product_update(int $id, array $data): void {
    $cat = db_row("SELECT id FROM categories WHERE name = ?", [$data['category'] ?? 'Mobile']);
    $cid = $cat ? $cat['id'] : 1;
    db_exec("UPDATE products SET category_id=?,name=?,currency=?,icon=?,img=?,img_banner=?,color=?,badge=?,description=?
             WHERE id=?",
        [$cid, trim($data['name']), trim($data['currency']),
         $data['icon']??'🎮', trim($data['img']??''), trim($data['img_banner']??''),
         $data['color']??'#2563EB', $data['badge']??'', trim($data['description']??''), $id]);
}

function product_toggle_status(int $id): string {
    $p = db_row("SELECT status FROM products WHERE id = ?", [$id]);
    if (!$p) return 'not_found';
    $new = $p['status'] === 'active' ? 'inactive' : 'active';
    db_exec("UPDATE products SET status=? WHERE id=?", [$new, $id]);
    return $new;
}

function product_delete(int $id): void {
    db_exec("DELETE FROM products WHERE id=?", [$id]);
}

function package_add(int $pid, int $amount, int $price, int $bonus = 0): void {
    db_exec("INSERT INTO packages (product_id,amount,price,bonus) VALUES (?,?,?,?)", [$pid,$amount,$price,$bonus]);
}

function package_update(int $pid, int $pkg_id, int $amount, int $price, int $bonus = 0): void {
    db_exec("UPDATE packages SET amount=?,price=?,bonus=? WHERE id=? AND product_id=?", [$amount,$price,$bonus,$pkg_id,$pid]);
}

function package_delete(int $pkg_id): void {
    db_exec("DELETE FROM packages WHERE id=?", [$pkg_id]);
}
