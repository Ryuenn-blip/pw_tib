<?php
/**
 * GameStore Database Connection (PDO)
 * Include file ini di semua halaman yang butuh DB
 */

// ── Konfigurasi — sesuaikan dengan hosting kamu ──────────────
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'gamestore');
define('DB_USER', 'root');        // ganti dengan user DB hosting
define('DB_PASS', '');            // ganti dengan password DB hosting
define('DB_CHARSET', 'utf8mb4');

// ── Singleton PDO connection ──────────────────────────────────
function db(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
    );

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
        ]);
    } catch (PDOException $e) {
        error_log('[DB ERROR] ' . $e->getMessage());
        die('Koneksi database gagal. Cek konfigurasi DB di includes/db.php');
    }

    return $pdo;
}

// ── Helpers ───────────────────────────────────────────────────
function db_row(string $sql, array $p = []): ?array {
    $st = db()->prepare($sql); $st->execute($p);
    $r  = $st->fetch(); return $r ?: null;
}
function db_rows(string $sql, array $p = []): array {
    $st = db()->prepare($sql); $st->execute($p);
    return $st->fetchAll();
}
function db_exec(string $sql, array $p = []): int {
    $st = db()->prepare($sql); $st->execute($p);
    return $st->rowCount();
}
function db_insert(string $sql, array $p = []): string {
    $st = db()->prepare($sql); $st->execute($p);
    return db()->lastInsertId();
}
function get_setting(string $key, string $default = ''): string {
    static $cache = [];
    if (!isset($cache[$key])) {
        $row = db_row('SELECT `value` FROM `settings` WHERE `key` = ?', [$key]);
        $cache[$key] = $row ? (string)$row['value'] : $default;
    }
    return $cache[$key];
}
function generate_order_id(): string {
    $date  = date('ymd');
    $count = db_row('SELECT COUNT(*) as c FROM `orders` WHERE DATE(created_at) = CURDATE()');
    $seq   = str_pad((int)($count['c'] ?? 0) + 1, 4, '0', STR_PAD_LEFT);
    return 'GS' . $date . $seq;
}
function generate_id(string $prefix = 'id_'): string {
    return $prefix . substr(md5(uniqid((string)rand(), true)), 0, 10);
}
function get_active_products(): array {
    return db_rows("
        SELECT p.*, c.name AS category_name,
               MIN(pk.price) AS min_price,
               COUNT(pk.id)  AS package_count
        FROM   products p
        JOIN   categories c   ON c.id = p.category_id
        LEFT JOIN packages pk ON pk.product_id = p.id AND pk.status = 'active'
        WHERE  p.status = 'active'
        GROUP BY p.id
        ORDER BY p.sort_order, p.id
    ");
}
function get_product_by_slug(string $slug): ?array {
    $product = db_row("
        SELECT p.*, c.name AS category_name
        FROM   products p
        JOIN   categories c ON c.id = p.category_id
        WHERE  p.slug = ? AND p.status = 'active'
    ", [$slug]);
    if (!$product) return null;
    $product['packages'] = db_rows("
        SELECT * FROM packages
        WHERE  product_id = ? AND status = 'active'
        ORDER BY price ASC
    ", [$product['id']]);
    return $product;
}
function get_payment_methods_db(): array {
    $rows    = db_rows("SELECT * FROM payment_methods WHERE status='active' ORDER BY sort_order");
    $grouped = ['ewallet' => [], 'bank' => [], 'qris' => []];
    foreach ($rows as $r) $grouped[$r['type']][$r['name']] = $r;
    return $grouped;
}
function validate_promo(string $code, int $price): array {
    $promo = db_row("
        SELECT * FROM promo_codes
        WHERE  code = ? AND status = 'active'
          AND  valid_from <= CURDATE() AND valid_until >= CURDATE()
    ", [strtoupper($code)]);
    if (!$promo)                              return ['valid'=>false,'msg'=>'Kode promo tidak valid'];
    if ($promo['max_use']>0 && $promo['used_count']>=$promo['max_use'])
                                              return ['valid'=>false,'msg'=>'Kode promo sudah habis'];
    if ($price < (int)$promo['min_purchase']) return ['valid'=>false,'msg'=>'Minimal pembelian tidak terpenuhi'];
    $disc = (int)round($price * $promo['discount_pct'] / 100);
    return ['valid'=>true,'discount'=>$disc,'pct'=>$promo['discount_pct'],
            'msg'=>"Diskon {$promo['discount_pct']}% berhasil!"];
}

// ── USER AUTH FUNCTIONS ───────────────────────────────────────
function user_register(string $name, string $email, string $phone, string $password): array {
    // Cek email sudah ada
    if (db_row('SELECT id FROM customers WHERE email = ?', [$email])) {
        return ['success' => false, 'msg' => 'Email sudah terdaftar. Silakan login.'];
    }
    $hash = password_hash($password, PASSWORD_DEFAULT);
    db_insert(
        'INSERT INTO customers (name, email, phone, password) VALUES (?,?,?,?)',
        [$name, $email, $phone, $hash]
    );
    return ['success' => true, 'msg' => 'Akun berhasil dibuat!'];
}

function user_login(string $email, string $password): array {
    $user = db_row('SELECT * FROM customers WHERE email = ? AND is_active = 1', [$email]);
    if (!$user || !password_verify($password, $user['password'])) {
        return ['success' => false, 'msg' => 'Email atau password salah.'];
    }
    // Set session
    $_SESSION['user_id']    = $user['id'];
    $_SESSION['user_name']  = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_phone'] = $user['phone'];
    return ['success' => true, 'user' => $user];
}

function user_logout(): void {
    unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_email'], $_SESSION['user_phone']);
}

function user_logged_in(): bool {
    return !empty($_SESSION['user_id']);
}

function user_get_orders(int $user_id): array {
    return db_rows(
        'SELECT * FROM orders WHERE customer_id = ? ORDER BY created_at DESC',
        [$user_id]
    );
}

function user_require_login(): void {
    if (!user_logged_in()) {
        header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

// ── PROMO VALIDATE (fix field name) ──────────────────────────
function validate_promo_code(string $code, int $price): array {
    $promo = db_row("
        SELECT * FROM promo_codes
        WHERE code = ? AND is_active = 1
          AND (valid_until IS NULL OR valid_until >= CURDATE())
    ", [strtoupper($code)]);
    if (!$promo) return ['valid'=>false,'msg'=>'Kode promo tidak valid atau sudah kedaluwarsa'];
    if ($promo['max_use'] && $promo['used_count'] >= $promo['max_use'])
        return ['valid'=>false,'msg'=>'Kuota kode promo sudah habis'];
    if ($price < (int)$promo['min_purchase'])
        return ['valid'=>false,'msg'=>'Minimal pembelian Rp '.number_format($promo['min_purchase'],0,',','.')];
    // Hitung diskon
    if ($promo['type'] === 'percent') {
        $disc = (int)round($price * $promo['value'] / 100);
        if ($promo['max_discount']) $disc = min($disc, (int)$promo['max_discount']);
        $msg = "Diskon {$promo['value']}% berhasil diterapkan!";
    } else {
        $disc = (int)$promo['value'];
        $msg  = 'Diskon Rp '.number_format($disc,0,',','.').' berhasil diterapkan!';
    }
    $disc = min($disc, $price); // tidak boleh melebihi harga
    return ['valid'=>true,'discount'=>$disc,'msg'=>$msg,'code'=>strtoupper($code)];
}
