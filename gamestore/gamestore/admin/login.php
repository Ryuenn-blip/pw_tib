<?php
session_start();
require_once dirname(__DIR__) . '/includes/db.php';

if (!empty($_SESSION['admin_logged_in'])) { header('Location: index.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['username'] ?? '');
    $pass = trim($_POST['password'] ?? '');

    $admin = db_row('SELECT * FROM admins WHERE username = ?', [$user]);

    if ($admin && password_verify($pass, $admin['password'])) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user']      = $admin['username'];
        $_SESSION['admin_name']      = $admin['name'];
        // Update last_login
        db_exec('UPDATE admins SET last_login = NOW() WHERE id = ?', [$admin['id']]);
        header('Location: index.php'); exit;
    }
    $error = 'Username atau password salah!';
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
        <form method="POST">
            <div class="form-group">
                <label class="form-label">👤 Username</label>
                <input type="text" name="username" class="form-control"
                       placeholder="admin" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </div>
            <div class="form-group" style="margin-bottom:1.5rem">
                <label class="form-label">🔒 Password</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:.875rem;font-size:.95rem">
                Masuk ke Dashboard
            </button>
        </form>
        <p style="text-align:center;margin-top:1.5rem;font-size:.78rem;color:var(--gray2)">
            Default: <strong style="color:var(--gray)">admin</strong> / <strong style="color:var(--gray)">admin123</strong>
        </p>
    </div>
</div>
</body>
</html>
