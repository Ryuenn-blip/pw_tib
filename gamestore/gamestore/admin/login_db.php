<?php
require_once '../includes/db.php';
session_start();

define('SITE_NAME', 'GameStore');

if (!empty($_SESSION['admin_logged_in'])) {
    header('Location: index.php'); exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username && $password) {
        $admin = DB::row("SELECT * FROM admins WHERE username = ?", [$username]);
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id']        = $admin['id'];
            $_SESSION['admin_user']      = $admin['username'];
            $_SESSION['admin_name']      = $admin['name'];
            // Update last login
            DB::exec("UPDATE admins SET last_login = NOW() WHERE id = ?", [$admin['id']]);
            header('Location: index.php'); exit;
        }
    }
    $error = 'Username atau password salah!';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Login Admin — <?= SITE_NAME ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/admin.css">
<style>
#gs-progress{position:fixed;top:0;left:0;width:0%;height:3px;z-index:100000;
    background:linear-gradient(90deg,#2563EB,#00D4FF);transition:width .3s ease,opacity .4s ease;
    border-radius:0 2px 2px 0}
#gs-progress.done{width:100%!important;opacity:0}
</style>
</head>
<body style="margin:0">
<div id="gs-progress"></div>
<div class="login-page">
    <div class="login-box">
        <div class="login-logo">
            <span class="icon">🎮</span>
            <h1>Admin Panel</h1>
            <p><?= SITE_NAME ?> — Dashboard Admin</p>
        </div>

        <?php if ($error): ?>
        <div class="login-error">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" autocomplete="off">
            <div class="form-group">
                <label class="form-label">👤 Username</label>
                <input type="text" name="username" class="form-control"
                       placeholder="admin" required autofocus
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </div>
            <div class="form-group" style="margin-bottom:1.5rem">
                <label class="form-label">🔒 Password</label>
                <input type="password" name="password" class="form-control"
                       placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-primary"
                    style="width:100%;justify-content:center;padding:.875rem;font-size:.95rem">
                Masuk ke Dashboard →
            </button>
        </form>

        <p style="text-align:center;margin-top:1.5rem;font-size:.78rem;color:var(--gray2)">
            Default: <strong style="color:var(--gray)">admin</strong> / <strong style="color:var(--gray)">admin123</strong>
        </p>
    </div>
</div>
<script>
var p=document.getElementById('gs-progress');
var v=0,t=setInterval(function(){v=Math.min(v+(v<50?8:2),92);p.style.width=v+'%';},80);
window.addEventListener('load',function(){clearInterval(t);p.style.width='100%';setTimeout(function(){p.classList.add('done');},300);});
</script>
</body>
</html>
