<?php
/**
 * GameStore — config.php (MySQL version)
 * Semua data produk, settings, dll dibaca dari database
 */

require_once __DIR__ . '/db.php';

// ── Site settings dari DB ──────────────────────────────────────
function gs_setting(string $key, string $default = ''): string {
    static $cache = null;
    if ($cache === null) {
        $rows  = DB::rows("SELECT `key`, `value` FROM settings");
        $cache = array_column($rows, 'value', 'key');
    }
    return $cache[$key] ?? $default;
}

define('SITE_NAME',      gs_setting('site_name',  'GameStore'));
define('SITE_TAGLINE',   gs_setting('tagline',    'Top Up Game Terlengkap & Termurah'));
define('WHATSAPP_NUMBER',gs_setting('wa_number',  '6281234567890'));

// ── Format helpers ─────────────────────────────────────────────
function formatRupiah(int $n): string {
    return 'Rp ' . number_format($n, 0, ',', '.');
}
function formatNum(int $n): string {
    if ($n >= 1_000_000) return round($n / 1_000_000, 1) . 'jt';
    if ($n >= 1_000)     return round($n / 1_000, 1)     . 'rb';
    return (string)$n;
}

// ── Products & packages dari DB ────────────────────────────────
function gs_get_products(bool $active_only = true): array {
    $where = $active_only ? "AND p.status = 'active'" : '';
    $products = DB::rows("
        SELECT p.*, c.name AS category, c.slug AS category_slug
        FROM   products p
        JOIN   categories c ON c.id = p.category_id
        WHERE  1=1 $where
        ORDER  BY p.sort_order, p.id
    ");
    if (empty($products)) return [];

    // Ambil semua paket sekaligus (N+1 prevention)
    $pids     = implode(',', array_column($products, 'id'));
    $packages = DB::rows("
        SELECT * FROM packages
        WHERE product_id IN ($pids)
        ORDER BY sort_order, price
    ");

    // Group packages by product_id
    $pkg_map = [];
    foreach ($packages as $pkg) {
        $pkg_map[$pkg['product_id']][] = $pkg;
    }

    foreach ($products as &$p) {
        $p['packages'] = $pkg_map[$p['id']] ?? [];
    }
    unset($p);

    return $products;
}

function gs_get_product_by_slug(string $slug): ?array {
    $product = DB::row("
        SELECT p.*, c.name AS category, c.slug AS category_slug
        FROM   products p
        JOIN   categories c ON c.id = p.category_id
        WHERE  p.slug = ? AND p.status = 'active'
    ", [$slug]);
    if (!$product) return null;

    $product['packages'] = DB::rows("
        SELECT * FROM packages WHERE product_id = ? ORDER BY sort_order, price
    ", [$product['id']]);

    return $product;
}

function gs_get_product_by_id(int $id): ?array {
    $product = DB::row("
        SELECT p.*, c.name AS category
        FROM   products p
        JOIN   categories c ON c.id = p.category_id
        WHERE  p.id = ?
    ", [$id]);
    if (!$product) return null;

    $product['packages'] = DB::rows("
        SELECT * FROM packages WHERE product_id = ? ORDER BY sort_order, price
    ", [$id]);

    return $product;
}

// ── Categories ─────────────────────────────────────────────────
function gs_get_categories(): array {
    return DB::rows("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order");
}

// ── Payment methods dari DB ────────────────────────────────────
function gs_payment_methods(): array {
    $rows = DB::rows("SELECT * FROM payment_methods WHERE is_active = 1 ORDER BY sort_order");
    $result = [];
    foreach ($rows as $r) {
        $result[$r['type']][$r['name']] = [
            'number' => $r['number'],
            'name'   => $r['holder'],
            'color'  => $r['color'],
            'icon'   => $r['icon'],
            'logo'   => $r['name'],
        ];
    }
    return $result;
}

// ── Orders ─────────────────────────────────────────────────────
function order_generate_id(): string {
    $prefix = 'GS' . date('ymd');
    $count  = DB::val("SELECT COUNT(*) FROM orders WHERE id LIKE ?", [$prefix . '%']);
    return $prefix . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
}

function order_save(array $data): string {
    $id = order_generate_id();
    DB::exec("
        INSERT INTO orders
            (id, user_id, product_id, package_id, product_name, currency,
             pkg_amount, pkg_bonus, price, customer_name, customer_phone,
             game_user_id, payment_method, proof_image, note, status)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,'pending')
    ", [
        $id,
        $data['user_id']       ?? null,
        $data['product_id'],
        $data['package_id'],
        $data['product_name'],
        $data['currency'],
        $data['pkg_amount'],
        $data['pkg_bonus']     ?? 0,
        $data['price'],
        $data['customer_name'],
        $data['customer_phone'] ?? null,
        $data['game_user_id'],
        $data['payment_method'],
        $data['proof_image']   ?? null,
        $data['note']          ?? null,
    ]);
    return $id;
}

function order_get(string $id): ?array {
    return DB::row("SELECT * FROM orders WHERE id = ?", [$id]);
}

function order_update(string $id, array $fields): bool {
    $sets   = implode(', ', array_map(fn($k) => "`$k` = ?", array_keys($fields)));
    $vals   = array_values($fields);
    $vals[] = $id;
    return DB::exec("UPDATE orders SET $sets WHERE id = ?", $vals) > 0;
}

function order_get_all(int $limit = 100, int $offset = 0): array {
    return DB::rows("
        SELECT o.*, p.icon AS product_icon
        FROM   orders o
        JOIN   products p ON p.id = o.product_id
        ORDER  BY o.created_at DESC
        LIMIT  ? OFFSET ?
    ", [$limit, $offset]);
}

// ── Promo codes ────────────────────────────────────────────────
function promo_validate(string $code, int $order_total): array {
    $promo = DB::row("
        SELECT * FROM promo_codes
        WHERE  code = ? AND is_active = 1
          AND  (expires_at IS NULL OR expires_at > NOW())
          AND  (max_uses = 0 OR used_count < max_uses)
    ", [strtoupper($code)]);

    if (!$promo) return ['valid' => false, 'message' => 'Kode promo tidak valid atau sudah kedaluwarsa'];
    if ($order_total < $promo['min_order'])
        return ['valid' => false, 'message' => 'Minimum order ' . formatRupiah($promo['min_order'])];

    $disc = $promo['type'] === 'percent'
        ? (int)round($order_total * $promo['value'] / 100)
        : (int)$promo['value'];

    return [
        'valid'    => true,
        'discount' => min($disc, $order_total),
        'type'     => $promo['type'],
        'value'    => $promo['value'],
        'message'  => "Diskon {$promo['value']}" . ($promo['type'] === 'percent' ? '%' : ' Rupiah') . " berhasil diterapkan!",
    ];
}

function promo_use(string $code): void {
    DB::exec("UPDATE promo_codes SET used_count = used_count + 1 WHERE code = ?", [strtoupper($code)]);
}

// ── Testimonials ───────────────────────────────────────────────
function gs_testimonials(): array {
    return DB::rows("SELECT * FROM testimonials WHERE is_active = 1 ORDER BY sort_order");
}

// ── Backward-compatible $games array ──────────────────────────
$games      = gs_get_products(true);
$_cats      = array_unique(array_column($games, 'category'));
sort($_cats);
$categories = array_merge(['Semua'], $_cats);
$testimonials = gs_testimonials();
