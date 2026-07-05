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
               COALESCE(MIN(pk.price), 0) AS min_price,
               COUNT(pk.id)               AS package_count
        FROM   products p
        JOIN   categories c   ON c.id = p.category_id
        LEFT JOIN packages pk ON pk.product_id = p.id AND pk.is_active = 1
        WHERE  p.is_active = 1
        GROUP BY p.id
        ORDER BY p.sort_order, p.id
    ");
}
function get_product_by_slug(string $slug): ?array {
    $product = db_row("
        SELECT p.*, c.name AS category_name
        FROM   products p
        JOIN   categories c ON c.id = p.category_id
        WHERE  p.slug = ? AND p.is_active = 1
    ", [$slug]);
    if (!$product) return null;
    $product['packages'] = db_rows("
        SELECT * FROM packages
        WHERE  product_id = ? AND is_active = 1
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
        WHERE code = ? AND is_active = 1
          AND (valid_from IS NULL OR valid_from <= CURDATE())
          AND (valid_until IS NULL OR valid_until >= CURDATE())
    ", [strtoupper($code)]);
    if (!$promo) return ['valid'=>false,'msg'=>'Kode promo tidak valid atau kedaluwarsa'];
    if ($promo['max_use'] > 0 && $promo['used_count'] >= $promo['max_use'])
        return ['valid'=>false,'msg'=>'Kuota kode promo sudah habis'];
    if ($price < (int)$promo['min_purchase'])
        return ['valid'=>false,'msg'=>'Minimal pembelian '.formatRupiah((int)$promo['min_purchase'])];
    // Hitung diskon
    if ($promo['type'] === 'percent') {
        $disc = (int)round($price * $promo['value'] / 100);
        if (!empty($promo['max_discount'])) $disc = min($disc, (int)$promo['max_discount']);
        $msg = "Diskon {$promo['value']}% berhasil diterapkan!";
    } else {
        $disc = (int)$promo['value'];
        $msg  = 'Diskon '.formatRupiah($disc).' berhasil diterapkan!';
    }
    $disc = min($disc, $price);
    return ['valid'=>true,'discount'=>$disc,'msg'=>$msg,'code'=>strtoupper($code)];
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

function user_login(string $email, string $password, bool $remember = false): array {
    $user = db_row('SELECT * FROM customers WHERE email = ? AND is_active = 1', [$email]);

    if (!$user) {
        return ['success' => false, 'msg' => 'Email atau password salah.'];
    }

    // ── Rate limiting: cek apakah akun terkunci ───────────────
    if (!empty($user['locked_until']) && strtotime($user['locked_until']) > time()) {
        $remaining = ceil((strtotime($user['locked_until']) - time()) / 60);
        return ['success' => false, 'msg' => "Akun terkunci sementara karena terlalu banyak percobaan gagal. Coba lagi dalam {$remaining} menit."];
    }

    if (!password_verify($password, $user['password'])) {
        // Tambah hitungan gagal
        $attempts = (int)($user['failed_attempts'] ?? 0) + 1;
        if ($attempts >= 5) {
            db_exec("UPDATE customers SET failed_attempts=0, locked_until=DATE_ADD(NOW(), INTERVAL 15 MINUTE) WHERE id=?", [$user['id']]);
            return ['success' => false, 'msg' => 'Terlalu banyak percobaan gagal. Akun dikunci selama 15 menit.'];
        }
        db_exec("UPDATE customers SET failed_attempts=? WHERE id=?", [$attempts, $user['id']]);
        $left = 5 - $attempts;
        return ['success' => false, 'msg' => "Email atau password salah. Sisa percobaan: {$left}."];
    }

    // ── Login berhasil: reset percobaan gagal, catat waktu ────
    db_exec("UPDATE customers SET failed_attempts=0, locked_until=NULL, last_login=NOW() WHERE id=?", [$user['id']]);

    // Set session
    $_SESSION['user_id']    = $user['id'];
    $_SESSION['user_name']  = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_phone'] = $user['phone'];

    // ── Remember Me: set cookie token 30 hari ─────────────────
    if ($remember) {
        $token = bin2hex(random_bytes(32));
        db_exec("UPDATE customers SET remember_token=? WHERE id=?", [hash('sha256', $token), $user['id']]);
        setcookie('gs_remember', $user['id'] . ':' . $token, [
            'expires'  => time() + (86400 * 30),
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    return ['success' => true, 'user' => $user];
}

function user_logout(): void {
    // Hapus remember token dari DB & cookie
    if (!empty($_SESSION['user_id'])) {
        db_exec("UPDATE customers SET remember_token=NULL WHERE id=?", [$_SESSION['user_id']]);
    }
    if (isset($_COOKIE['gs_remember'])) {
        setcookie('gs_remember', '', time() - 3600, '/');
    }
    unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_email'], $_SESSION['user_phone']);
}

function user_logged_in(): bool {
    if (!empty($_SESSION['user_id'])) return true;

    // ── Coba auto-login dari cookie "Remember Me" ─────────────
    if (!empty($_COOKIE['gs_remember'])) {
        [$uid, $token] = array_pad(explode(':', $_COOKIE['gs_remember'], 2), 2, '');
        if ($uid && $token) {
            $user = db_row("SELECT * FROM customers WHERE id=? AND remember_token=? AND is_active=1",
                [(int)$uid, hash('sha256', $token)]);
            if ($user) {
                $_SESSION['user_id']    = $user['id'];
                $_SESSION['user_name']  = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_phone'] = $user['phone'];
                return true;
            }
            // Token tidak valid, hapus cookie basi
            setcookie('gs_remember', '', time() - 3600, '/');
        }
    }
    return false;
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


// ── ACTIVITY LOGGING ─────────────────────────────────────────
function log_activity(string $action, string $detail = ''): void {
    try {
        $admin_id = $_SESSION['admin_id'] ?? null;
        $ip       = $_SERVER['HTTP_X_FORWARDED_FOR']
                 ?? $_SERVER['REMOTE_ADDR']
                 ?? null;
        db_exec(
            "INSERT INTO activity_logs (admin_id, action, detail, ip, created_at) VALUES (?,?,?,?,NOW())",
            [$admin_id, $action, $detail ?: null, $ip]
        );
    } catch (\Throwable $e) {
        // Jangan crash aplikasi jika log gagal
        error_log('[LOG_ACTIVITY ERROR] ' . $e->getMessage());
    }
}
