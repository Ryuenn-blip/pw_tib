<?php
session_start();
require_once dirname(__DIR__) . '/includes/db.php';

if (!empty($_SESSION['admin_logged_in'])) { header('Location: index.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['username'] ?? '');
    $pass = trim($_POST['password'] ?? '');

    if (!$user || !$pass) {
        $error = 'Username dan password wajib diisi.';
    } else {
        $admin = db_row('SELECT * FROM admins WHERE username = ?', [$user]);

        // ── Rate limiting sederhana via session ───────────────
        $key = 'login_fail_' . md5($user . $_SERVER['REMOTE_ADDR']);
        $fail_data = $_SESSION[$key] ?? ['count' => 0, 'locked_until' => 0];

        if ($fail_data['locked_until'] > time()) {
            $remaining = ceil(($fail_data['locked_until'] - time()) / 60);
            $error = "Terlalu banyak percobaan gagal. Coba lagi dalam {$remaining} menit.";
        } elseif ($admin && password_verify($pass, $admin['password'])) {
            // ── Login berhasil ─────────────────────────────────
            unset($_SESSION[$key]);
            session_regenerate_id(true);
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id']        = $admin['id'];
            $_SESSION['admin_user']      = $admin['username'];
            $_SESSION['admin_name']      = $admin['name'];
            db_exec('UPDATE admins SET last_login = NOW() WHERE id = ?', [$admin['id']]);
            log_activity('login', 'Admin login berhasil dari IP: '.($_SERVER['REMOTE_ADDR']??'unknown'));
            header('Location: index.php'); exit;
        } else {
            // ── Login gagal ─────────────────────────────────────
            $fail_data['count']++;
            if ($fail_data['count'] >= 5) {
                $fail_data['locked_until'] = time() + (15 * 60);
                $fail_data['count'] = 0;
                $error = 'Terlalu banyak percobaan gagal. Akun dikunci selama 15 menit.';
            } else {
                $left = 5 - $fail_data['count'];
                $error = "Username atau password salah! Sisa percobaan: {$left}.";
            }
            $_SESSION[$key] = $fail_data;
        }
    }
}
$site_name = db_row("SELECT `value` FROM settings WHERE `key`='site_name'")['value'] ?? 'GameStore';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Login Admin — <?= htmlspecialchars($site_name) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/admin.css">
<style>
.eye-toggle{position:absolute;right:.75rem;top:50%;transform:translateY(-50%);
    background:none;border:none;color:var(--gray);cursor:pointer;font-size:.95rem}
.btn-loading{opacity:.7;cursor:default;pointer-events:none}
.spin{display:inline-block;animation:spin .8s linear infinite}
@keyframes spin{to{transform:rotate(360deg)}}
</style>
</head>
<body style="margin:0">
<div class="login-page">
    <div class="login-box">
        <div class="login-logo">
            <span class="icon">🎮</span>
            <h1>Admin Panel</h1>
            <p><?= htmlspecialchars($site_name) ?> — Dashboard Admin</p>
        </div>
        <?php if ($error): ?>
        <div class="login-error">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST" id="adminLoginForm">
            <div class="form-group">
                <label class="form-label">👤 Username</label>
                <input type="text" name="username" class="form-control"
                       placeholder="admin" required autofocus
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </div>
            <div class="form-group" style="margin-bottom:1.5rem;position:relative">
                <label class="form-label">🔒 Password</label>
                <div style="position:relative">
                    <input type="password" name="password" id="adminPass" class="form-control"
                           placeholder="••••••••" required style="padding-right:2.75rem">
                    <button type="button" class="eye-toggle" onclick="toggleAdminPass()" id="adminEye">👁</button>
                </div>
            </div>
            <button type="submit" id="adminLoginBtn" class="btn btn-primary"
                    style="width:100%;justify-content:center;padding:.875rem;font-size:.95rem">
                Masuk ke Dashboard
            </button>
        </form>
        <p style="text-align:center;margin-top:1.5rem;font-size:.78rem;color:var(--gray2)">
            Default: <strong style="color:var(--gray)">admin</strong> / <strong style="color:var(--gray)">admin123</strong>
        </p>
    </div>
</div>
<script>
function toggleAdminPass() {
    const inp = document.getElementById('adminPass');
    const btn = document.getElementById('adminEye');
    if (inp.type === 'password') { inp.type = 'text';     btn.textContent = '🙈'; }
    else                         { inp.type = 'password'; btn.textContent = '👁'; }
}
document.getElementById('adminLoginForm').addEventListener('submit', function() {
    const btn = document.getElementById('adminLoginBtn');
    btn.classList.add('btn-loading');
    btn.innerHTML = '<span class="spin">⏳</span> Memproses...';
});
</script>
</body>
</html>
