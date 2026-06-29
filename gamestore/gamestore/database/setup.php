<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>GameStore — Setup Database</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Segoe UI',system-ui,sans-serif;background:#0D1117;color:#F0F6FF;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem}
.setup-box{width:100%;max-width:580px}
.logo{text-align:center;margin-bottom:2rem}
.logo-icon{font-size:3rem;display:block;margin-bottom:.5rem}
.logo h1{font-size:1.6rem;font-weight:900}
.logo h1 span{color:#00D4FF}
.logo p{color:#8B949E;font-size:.875rem;margin-top:.25rem}
.card{background:#161B22;border:1px solid #30363D;border-radius:16px;overflow:hidden}
.card-header{padding:1.25rem 1.5rem;border-bottom:1px solid #30363D;background:rgba(37,99,235,.08)}
.card-header h2{font-size:1rem;font-weight:800;display:flex;align-items:center;gap:.5rem}
.card-body{padding:1.5rem;display:flex;flex-direction:column;gap:1rem}
.form-group label{display:block;font-size:.8rem;font-weight:600;color:#8B949E;margin-bottom:.4rem}
.form-group input{width:100%;background:#1C2333;border:1.5px solid #30363D;border-radius:8px;padding:.65rem 1rem;color:#F0F6FF;font-size:.9rem;outline:none;transition:.2s;font-family:inherit}
.form-group input:focus{border-color:#2563EB;box-shadow:0 0 0 3px rgba(37,99,235,.12)}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:.75rem}
.btn{width:100%;padding:1rem;border:none;border-radius:10px;font-size:.95rem;font-weight:800;cursor:pointer;font-family:inherit;transition:.2s;display:flex;align-items:center;justify-content:center;gap:.5rem}
.btn-primary{background:linear-gradient(135deg,#2563EB,#3B82F6);color:#fff;box-shadow:0 4px 20px rgba(37,99,235,.4)}
.btn-primary:hover{transform:translateY(-1px);box-shadow:0 8px 28px rgba(37,99,235,.5)}
.btn-primary:disabled{opacity:.5;cursor:default;transform:none}
.alert{padding:.875rem 1rem;border-radius:8px;font-size:.85rem;line-height:1.5}
.alert-success{background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.3);color:#22C55E}
.alert-error  {background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#EF4444}
.alert-info   {background:rgba(37,99,235,.1);border:1px solid rgba(37,99,235,.3);color:#60A5FA}
.step-list{display:flex;flex-direction:column;gap:.5rem;font-size:.85rem;color:#8B949E}
.step-item{display:flex;align-items:center;gap:.625rem;padding:.5rem .75rem;background:#1C2333;border-radius:6px}
.step-item .ico{flex-shrink:0;font-size:1rem}
#results{display:flex;flex-direction:column;gap:.5rem}
.res-item{padding:.625rem .875rem;border-radius:6px;font-size:.82rem;font-family:monospace}
.res-ok  {background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.25);color:#22C55E}
.res-err {background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);color:#EF4444}
.res-info{background:rgba(37,99,235,.1);border:1px solid rgba(37,99,235,.25);color:#60A5FA}
.done-actions{display:flex;flex-direction:column;gap:.625rem}
</style>
</head>
<body>
<div class="setup-box">
    <div class="logo">
        <span class="logo-icon">🎮</span>
        <h1>Game<span>Store</span></h1>
        <p>Setup Database Wizard</p>
    </div>

<?php
$step = $_POST['step'] ?? 'form';

/* ── STEP 1: Form koneksi ── */
if ($step === 'form'):
?>
    <div class="card">
        <div class="card-header">
            <h2>🗄️ Konfigurasi Database MySQL</h2>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                Masukkan kredensial database MySQL kamu. Data ini akan disimpan ke
                <code>database/db.php</code> dan tidak dikirim ke mana pun.
            </div>

            <form method="POST">
                <input type="hidden" name="step" value="install">
                <div class="form-row">
                    <div class="form-group">
                        <label>Host Database</label>
                        <input name="host" value="localhost" required>
                    </div>
                    <div class="form-group">
                        <label>Port</label>
                        <input name="port" value="3306" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Nama Database</label>
                    <input name="dbname" placeholder="gamestore_db" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Username</label>
                        <input name="username" placeholder="root" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" placeholder="(kosong jika tidak ada)">
                    </div>
                </div>
                <div class="form-group">
                    <label>Password Admin Panel</label>
                    <input type="password" name="admin_pass" placeholder="Password untuk login admin" required>
                </div>
                <div class="form-group">
                    <label>Nomor WhatsApp Admin</label>
                    <input name="wa_number" value="6281234567890" required>
                </div>

                <div style="margin-top:.5rem">
                    <div class="step-list">
                        <div class="step-item"><span class="ico">📋</span> Membuat semua tabel database</div>
                        <div class="step-item"><span class="ico">🌱</span> Mengisi data awal (produk, paket, promo)</div>
                        <div class="step-item"><span class="ico">⚙️</span> Menyimpan konfigurasi ke db.php</div>
                        <div class="step-item"><span class="ico">🔒</span> Membuat akun admin dengan password kamu</div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="margin-top:.5rem">
                    🚀 Mulai Instalasi Database
                </button>
            </form>
        </div>
    </div>

<?php
/* ── STEP 2: Proses instalasi ── */
elseif ($step === 'install'):
    $host      = trim($_POST['host']      ?? 'localhost');
    $port      = (int)($_POST['port']     ?? 3306);
    $dbname    = trim($_POST['dbname']    ?? '');
    $username  = trim($_POST['username']  ?? '');
    $password  = $_POST['password']       ?? '';
    $adminPass = $_POST['admin_pass']     ?? 'admin123';
    $waNumber  = trim($_POST['wa_number'] ?? '6281234567890');

    $results = [];
    $success = true;

    function res(array &$arr, bool $ok, string $msg): void {
        $arr[] = ['ok'=>$ok,'msg'=>$msg];
    }

    // 1. Test koneksi
    try {
        $dsn = "mysql:host=$host;port=$port;charset=utf8mb4";
        $pdo = new PDO($dsn, $username, $password, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
        res($results, true, "✅ Koneksi ke MySQL berhasil ($host:$port)");
    } catch (PDOException $e) {
        res($results, false, "❌ Gagal koneksi: " . $e->getMessage());
        $success = false;
    }

    // 2. Buat database jika belum ada
    if ($success) {
        try {
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `$dbname`");
            res($results, true, "✅ Database `$dbname` siap");
        } catch (PDOException $e) {
            res($results, false, "❌ Gagal buat database: " . $e->getMessage());
            $success = false;
        }
    }

    // 3. Jalankan schema.sql
    if ($success) {
        $schemaFile = __DIR__ . '/schema.sql';
        if (!file_exists($schemaFile)) {
            res($results, false, "❌ File schema.sql tidak ditemukan di folder database/");
            $success = false;
        } else {
            try {
                $sql = file_get_contents($schemaFile);
                // Hapus komentar dan split by ;
                $sql = preg_replace('/--[^\n]*\n/', "\n", $sql);
                $statements = array_filter(array_map('trim', explode(';', $sql)));
                $tableCount = 0;
                foreach ($statements as $stmt) {
                    if (empty($stmt) || $stmt === "\n") continue;
                    $pdo->exec($stmt);
                    if (stripos($stmt, 'CREATE TABLE') !== false) $tableCount++;
                }
                res($results, true, "✅ Schema berhasil ($tableCount tabel dibuat)");
            } catch (PDOException $e) {
                res($results, false, "❌ Error schema: " . $e->getMessage());
                $success = false;
            }
        }
    }

    // 4. Jalankan seed.sql
    if ($success) {
        $seedFile = __DIR__ . '/seed.sql';
        if (!file_exists($seedFile)) {
            res($results, false, "❌ File seed.sql tidak ditemukan");
            $success = false;
        } else {
            try {
                // Cek apakah sudah ada data
                $existingCats = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
                if ($existingCats > 0) {
                    res($results, true, "ℹ️ Data seed sudah ada, dilewati (database tidak dikosongkan)");
                } else {
                    $sql = file_get_contents($seedFile);
                    $sql = preg_replace('/--[^\n]*\n/', "\n", $sql);
                    $statements = array_filter(array_map('trim', explode(';', $sql)));
                    foreach ($statements as $stmt) {
                        if (empty($stmt) || $stmt === "\n") continue;
                        try { $pdo->exec($stmt); } catch (PDOException $e) {
                            // Skip non-critical seed errors (duplicate key, etc)
                            if ($e->getCode() !== '23000') throw $e;
                        }
                    }
                    res($results, true, "✅ Data awal berhasil diisi (produk, paket, promo)");
                }
            } catch (PDOException $e) {
                res($results, false, "❌ Error seed: " . $e->getMessage());
            }
        }
    }

    // 5. Buat/update admin user dengan password baru
    if ($success) {
        try {
            $hash = password_hash($adminPass, PASSWORD_BCRYPT, ['cost'=>12]);
            $existing = $pdo->prepare("SELECT id FROM users WHERE role='admin' LIMIT 1");
            $existing->execute();
            $admin = $existing->fetch(PDO::FETCH_ASSOC);
            if ($admin) {
                $stmt = $pdo->prepare("UPDATE users SET password_hash=? WHERE id=?");
                $stmt->execute([$hash, $admin['id']]);
                res($results, true, "✅ Password admin diperbarui");
            } else {
                $stmt = $pdo->prepare("INSERT INTO users (name,email,password_hash,role,status) VALUES (?,?,?,?,?)");
                $stmt->execute(['Admin','admin@gamestore.id',$hash,'admin','active']);
                res($results, true, "✅ Akun admin dibuat (admin@gamestore.id)");
            }
        } catch (PDOException $e) {
            res($results, false, "❌ Error admin: " . $e->getMessage());
        }
    }

    // 6. Update settings (WA number, dll)
    if ($success) {
        try {
            $stmt = $pdo->prepare("INSERT INTO settings (`key`,`value`,`group`) VALUES (?,?,?) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)");
            $stmt->execute(['wa_number', $waNumber, 'general']);
            $stmt->execute(['wa_number_display', '0' . ltrim(preg_replace('/^62/', '', $waNumber), '0'), 'general']);
            res($results, true, "✅ Pengaturan disimpan ke database");
        } catch (PDOException $e) {
            res($results, false, "⚠️ Warning settings: " . $e->getMessage());
        }
    }

    // 7. Tulis ulang db.php dengan kredensial yang baru
    if ($success) {
        $dbPhpContent = <<<PHP
<?php
// Auto-generated by GameStore Setup Wizard
// Generated: <?= date('Y-m-d H:i:s') ?>

define('DB_HOST',    '$host');
define('DB_NAME',    '$dbname');
define('DB_USER',    '$username');
define('DB_PASS',    '$password');
define('DB_CHARSET', 'utf8mb4');
define('DB_PORT',    $port);

class DB
{
    private static ?\PDO \$pdo = null;
    public static function conn(): \PDO
    {
        if (self::\$pdo === null) {
            \$dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', DB_HOST, DB_PORT, DB_NAME, DB_CHARSET);
            self::\$pdo = new \PDO(\$dsn, DB_USER, DB_PASS, [
                \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES   => false,
                \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
            ]);
        }
        return self::\$pdo;
    }
    public static function query(string \$sql, array \$params = []): \PDOStatement { \$s = self::conn()->prepare(\$sql); \$s->execute(\$params); return \$s; }
    public static function rows(string \$sql, array \$p = []): array { return self::query(\$sql,\$p)->fetchAll(); }
    public static function row(string \$sql, array \$p = []): ?array { \$r = self::query(\$sql,\$p)->fetch(); return \$r===false?null:\$r; }
    public static function value(string \$sql, array \$p = []): mixed { \$r = self::query(\$sql,\$p)->fetchColumn(); return \$r===false?null:\$r; }
    public static function count(string \$sql, array \$p = []): int { return (int)self::value(\$sql,\$p); }
    public static function insert(string \$sql, array \$p = []): int { self::query(\$sql,\$p); return (int)self::conn()->lastInsertId(); }
    public static function insertRow(string \$t, array \$d): int { \$c=implode(',',array_map(fn(\$k)=>"\`\$k\`",array_keys(\$d))); \$ph=implode(',',array_fill(0,count(\$d),'?')); return self::insert("INSERT INTO \`\$t\` (\$c) VALUES (\$ph)",array_values(\$d)); }
    public static function updateRow(string \$t, array \$d, string \$w, array \$wp=[]): int { \$s=implode(',',array_map(fn(\$k)=>"\`\$k\`=?",array_keys(\$d))); return self::query("UPDATE \`\$t\` SET \$s WHERE \$w",array_merge(array_values(\$d),\$wp))->rowCount(); }
    public static function begin(): void { self::conn()->beginTransaction(); }
    public static function commit(): void { self::conn()->commit(); }
    public static function rollback(): void { self::conn()->rollBack(); }
    public static function transaction(callable \$fn): mixed { self::begin(); try { \$r=\$fn(self::conn()); self::commit(); return \$r; } catch (\Throwable \$e) { self::rollback(); throw \$e; } }
}
function gs_setting(string \$key, mixed \$default=null): mixed { try { \$v=DB::value("SELECT \`value\` FROM \`settings\` WHERE \`key\`=?",[\$key]); return \$v!==null?\$v:\$default; } catch (\Throwable) { return \$default; } }
function generate_order_id(): string { return 'GS'.date('ymd').strtoupper(substr(uniqid(),-6)); }
PHP;

        $written = file_put_contents(__DIR__ . '/db.php', $dbPhpContent);
        if ($written !== false) {
            res($results, true, "✅ File database/db.php berhasil diperbarui dengan kredensial baru");
        } else {
            res($results, false, "⚠️ Gagal tulis db.php — pastikan folder database/ bisa ditulis (chmod 755)");
        }
    }

    $allOk = !in_array(false, array_column($results, 'ok'));
?>
    <div class="card">
        <div class="card-header">
            <h2><?= $allOk ? '✅ Instalasi Berhasil!' : '⚠️ Instalasi Selesai dengan Peringatan' ?></h2>
        </div>
        <div class="card-body">
            <div id="results">
                <?php foreach ($results as $r): ?>
                <div class="res-item <?= $r['ok'] ? 'res-ok' : 'res-err' ?>">
                    <?= htmlspecialchars($r['msg']) ?>
                </div>
                <?php endforeach; ?>
            </div>

            <?php if ($allOk): ?>
            <div class="alert alert-success">
                🎉 Database berhasil disiapkan! Sekarang kamu bisa menggunakan GameStore dengan MySQL.
            </div>
            <div class="done-actions">
                <a href="../admin/" style="display:flex;align-items:center;justify-content:center;gap:.5rem;
                    background:linear-gradient(135deg,#2563EB,#3B82F6);color:#fff;padding:1rem;
                    border-radius:10px;font-weight:800;text-decoration:none;font-size:.95rem">
                    🔑 Login ke Admin Panel
                </a>
                <a href="../" style="display:flex;align-items:center;justify-content:center;gap:.5rem;
                    background:transparent;border:1.5px solid #30363D;color:#8B949E;padding:.875rem;
                    border-radius:10px;font-weight:700;text-decoration:none;font-size:.875rem">
                    🌐 Lihat Website
                </a>
            </div>
            <div class="alert alert-info" style="font-size:.78rem">
                ⚠️ <strong>Keamanan:</strong> Hapus atau proteksi file
                <code>database/setup.php</code> setelah instalasi selesai!
            </div>
            <?php else: ?>
            <div class="alert alert-error">
                Ada error saat instalasi. Periksa pesan di atas dan coba lagi.
            </div>
            <form method="POST">
                <input type="hidden" name="step" value="form">
                <button type="submit" class="btn btn-primary">← Kembali & Perbaiki</button>
            </form>
            <?php endif; ?>
        </div>
    </div>

<?php endif; ?>

</div>
</body>
</html>
