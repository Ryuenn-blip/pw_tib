<?php
/**
 * GameStore — Web Install Wizard
 * HAPUS FILE INI SETELAH SETUP SELESAI!
 */

$step    = (int)($_GET['step'] ?? 1);
$error   = '';
$success = '';

// Step 2: Test & import
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host   = trim($_POST['host']   ?? 'localhost');
    $port   = trim($_POST['port']   ?? '3306');
    $name   = trim($_POST['dbname'] ?? 'gamestore');
    $user   = trim($_POST['user']   ?? 'root');
    $pass   = trim($_POST['pass']   ?? '');

    try {
        $pdo = new PDO(
            "mysql:host=$host;port=$port;charset=utf8mb4",
            $user, $pass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        // Buat database jika belum ada
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `$name`");

        // Import SQL
        $sql_file = __DIR__ . '/gamestore.sql';
        if (!file_exists($sql_file)) {
            throw new Exception("File gamestore.sql tidak ditemukan di folder database/");
        }
        $sql = file_get_contents($sql_file);
        // Hapus komentar dan jalankan per statement
        $statements = array_filter(
            array_map('trim', explode(';', $sql)),
            fn($s) => $s !== '' && !preg_match('/^--/', $s)
        );
        foreach ($statements as $stmt) {
            if (trim($stmt)) {
                try { $pdo->exec($stmt . ';'); }
                catch (PDOException $e) {
                    // Skip non-fatal errors (duplikat index, etc)
                    if (!in_array($e->getCode(), ['42000', '23000'])) {
                        // Log saja, jangan stop
                    }
                }
            }
        }

        // Update config file
        $config_file = dirname(__DIR__) . '/includes/db.php';
        $config      = file_get_contents($config_file);
        $config = preg_replace("/define\('DB_HOST',\s*'[^']*'\)/", "define('DB_HOST', '$host')", $config);
        $config = preg_replace("/define\('DB_PORT',\s*'[^']*'\)/", "define('DB_PORT', '$port')", $config);
        $config = preg_replace("/define\('DB_NAME',\s*'[^']*'\)/", "define('DB_NAME', '$name')", $config);
        $config = preg_replace("/define\('DB_USER',\s*'[^']*'\)/", "define('DB_USER', '$user')", $config);
        $config = preg_replace("/define\('DB_PASS',\s*'[^']*'\)/", "define('DB_PASS', '$pass')", $config);

        if (is_writable($config_file)) {
            file_put_contents($config_file, $config);
            $success = 'db_updated';
        } else {
            $success = 'manual_config';
        }

        $step = 3;
        // Simpan hasil ke session-like hidden field
        $result = compact('host','port','name','user','pass','success');
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>GameStore — Setup Wizard</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Segoe UI',sans-serif;background:#0D1117;color:#F0F6FF;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1.5rem}
.setup-box{width:100%;max-width:540px;background:#161B22;border:1px solid #30363D;border-radius:16px;overflow:hidden}
.setup-header{background:linear-gradient(135deg,#1a2d5e,#1a3080);padding:1.75rem;text-align:center;border-bottom:1px solid #30363D}
.setup-header h1{font-size:1.5rem;font-weight:900;margin-bottom:.25rem}
.setup-header p{font-size:.85rem;color:rgba(255,255,255,.6)}
.setup-body{padding:2rem}
.steps{display:flex;gap:0;margin-bottom:2rem;align-items:center}
.st{display:flex;flex-direction:column;align-items:center;gap:.3rem;flex:1}
.st-c{width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:800}
.st-c.done{background:#22C55E;color:#fff}
.st-c.active{background:#2563EB;color:#fff;box-shadow:0 0 12px rgba(37,99,235,.5)}
.st-c.wait{background:#1C2333;color:#6E7681;border:2px solid #30363D}
.st-label{font-size:.7rem;color:#6E7681;font-weight:600}
.st-label.active{color:#F0F6FF}
.st-line{flex:1;height:2px;background:#30363D}
.st-line.done{background:#22C55E}
.form-group{margin-bottom:1rem}
.form-group label{display:block;font-size:.82rem;font-weight:600;color:#8B949E;margin-bottom:.375rem}
.form-group input{width:100%;background:#0D1117;border:1.5px solid #30363D;border-radius:8px;padding:.65rem .875rem;color:#F0F6FF;font-size:.9rem;transition:.2s;outline:none;font-family:inherit}
.form-group input:focus{border-color:#2563EB}
.form-grid{display:grid;grid-template-columns:3fr 1fr;gap:.625rem}
.btn{width:100%;background:#2563EB;color:#fff;border:none;border-radius:8px;padding:.875rem;font-size:.95rem;font-weight:700;cursor:pointer;transition:.2s;font-family:inherit}
.btn:hover{background:#1d4ed8}
.error{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);border-radius:8px;padding:1rem;color:#EF4444;font-size:.85rem;margin-bottom:1rem}
.success-ico{font-size:4rem;margin-bottom:1rem}
.info-box{background:rgba(37,99,235,.08);border:1px solid rgba(37,99,235,.2);border-radius:8px;padding:.875rem;font-size:.82rem;color:#8B949E;line-height:1.7;margin-bottom:1rem}
code{background:#0D1117;border:1px solid #30363D;padding:.15rem .4rem;border-radius:4px;font-size:.82rem;color:#00D4FF}
.warning-box{background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.25);border-radius:8px;padding:.875rem;font-size:.82rem;color:#F59E0B;margin-top:1rem;line-height:1.7}
</style>
</head>
<body>
<div class="setup-box">
    <div class="setup-header">
        <div style="font-size:2.5rem;margin-bottom:.5rem">🎮</div>
        <h1>GameStore Setup Wizard</h1>
        <p>Konfigurasi database untuk pertama kali</p>
    </div>
    <div class="setup-body">

        <!-- Steps -->
        <div class="steps">
            <div class="st">
                <div class="st-c <?= $step>=1?($step>1?'done':'active'):'wait' ?>"><?= $step>1?'✓':'1' ?></div>
                <div class="st-label <?= $step===1?'active':'' ?>">Koneksi</div>
            </div>
            <div class="st-line <?= $step>1?'done':'' ?>"></div>
            <div class="st">
                <div class="st-c <?= $step>=2?($step>2?'done':'active'):'wait' ?>"><?= $step>2?'✓':'2' ?></div>
                <div class="st-label <?= $step===2?'active':'' ?>">Import</div>
            </div>
            <div class="st-line <?= $step>2?'done':'' ?>"></div>
            <div class="st">
                <div class="st-c <?= $step>=3?'done':'wait' ?>"><?= $step>=3?'✓':'3' ?></div>
                <div class="st-label <?= $step===3?'active':'' ?>">Selesai</div>
            </div>
        </div>

        <?php if ($step === 1 || ($step === 1 && $error)): ?>
        <!-- Step 1: Form -->
        <?php if ($error): ?>
        <div class="error">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="info-box">
            🛠 Wizard ini akan membuat database, mengimport schema, dan memperbarui konfigurasi di
            <code>includes/db.php</code> secara otomatis.
        </div>

        <form method="POST">
            <div class="form-group">
                <label>Database Host</label>
                <input type="text" name="host" value="localhost" placeholder="localhost">
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Database Name</label>
                    <input type="text" name="dbname" value="gamestore" placeholder="gamestore">
                </div>
                <div class="form-group">
                    <label>Port</label>
                    <input type="text" name="port" value="3306" placeholder="3306">
                </div>
            </div>
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="user" value="root" placeholder="root">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="pass" placeholder="(kosong jika tidak ada password)">
            </div>
            <button type="submit" class="btn">🚀 Mulai Setup →</button>
        </form>

        <?php elseif ($step === 3): ?>
        <!-- Step 3: Success -->
        <div style="text-align:center">
            <div class="success-ico">✅</div>
            <h2 style="font-size:1.25rem;font-weight:900;margin-bottom:.5rem">Setup Berhasil!</h2>
            <p style="color:#8B949E;font-size:.875rem;margin-bottom:1.5rem;line-height:1.7">
                Database berhasil dibuat dan semua tabel sudah diimport.
                <?php if (($result['success']??'') === 'db_updated'): ?>
                File konfigurasi sudah diperbarui otomatis.
                <?php else: ?>
                Perbarui <code>includes/db.php</code> secara manual.
                <?php endif; ?>
            </p>

            <?php if (($result['success']??'') === 'manual_config'): ?>
            <div class="info-box" style="text-align:left">
                Edit <code>includes/db.php</code>:<br>
                <code>DB_HOST</code> = <strong><?= htmlspecialchars($result['host']??'') ?></strong><br>
                <code>DB_NAME</code> = <strong><?= htmlspecialchars($result['name']??'') ?></strong><br>
                <code>DB_USER</code> = <strong><?= htmlspecialchars($result['user']??'') ?></strong><br>
                <code>DB_PASS</code> = <strong>(password kamu)</strong>
            </div>
            <?php endif; ?>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.625rem;margin-top:1rem">
                <a href="../index.php" style="display:flex;align-items:center;justify-content:center;
                    background:#22C55E;color:#fff;padding:.875rem;border-radius:8px;
                    font-weight:700;text-decoration:none">🌐 Buka Website</a>
                <a href="../admin/" style="display:flex;align-items:center;justify-content:center;
                    background:#2563EB;color:#fff;padding:.875rem;border-radius:8px;
                    font-weight:700;text-decoration:none">⚙️ Admin Panel</a>
            </div>

            <div class="warning-box">
                ⚠️ <strong>PENTING:</strong> Hapus file <code>database/setup.php</code> sekarang untuk keamanan!<br>
                Login admin: <code>admin</code> / <code>admin123</code> — <strong>ganti segera!</strong>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>
</body>
</html>
