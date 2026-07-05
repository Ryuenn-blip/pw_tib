<?php
require_once 'includes/config.php';
session_start();

// Sudah login → redirect ke dashboard
if (user_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$error    = '';
$redirect = $_GET['redirect'] ?? 'dashboard.php';

// Handle POST login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');
    $remember = !empty($_POST['remember']);

    if (!$email || !$password) {
        $error = 'Email dan password wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } else {
        $result = user_login($email, $password, $remember);
        if ($result['success']) {
            // Regenerate session untuk keamanan
            session_regenerate_id(true);
            header('Location: ' . $redirect);
            exit;
        }
        $error = $result['msg'];
    }
}

$page_title = 'Login';
require_once 'includes/header.php';
?>

<div style="min-height:calc(100vh - 68px);display:flex;align-items:center;
    justify-content:center;padding:100px 1.5rem 4rem">
<div style="width:100%;max-width:420px">

    <div style="text-align:center;margin-bottom:2rem">
        <div style="font-size:3rem;margin-bottom:.75rem">🎮</div>
        <h1 style="font-size:1.75rem;font-weight:900;margin-bottom:.25rem">Masuk ke GameStore</h1>
        <p style="color:var(--gray);font-size:.875rem">Masuk untuk melihat riwayat transaksi kamu</p>
    </div>

    <div style="background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius-lg);padding:2rem">

        <?php if ($error): ?>
        <div style="background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.25);
            border-radius:var(--radius);padding:.875rem 1rem;margin-bottom:1.25rem;
            color:var(--danger);font-size:.875rem;display:flex;align-items:center;gap:.5rem">
            ⚠️ <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <?php if (isset($_GET['registered'])): ?>
        <div style="background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.25);
            border-radius:var(--radius);padding:.875rem 1rem;margin-bottom:1.25rem;
            color:var(--success);font-size:.875rem">
            ✅ Akun berhasil dibuat! Silakan login.
        </div>
        <?php endif; ?>

        <form method="POST" action="login.php?redirect=<?= urlencode($redirect) ?>" id="loginForm" novalidate>

            <div class="form-group">
                <label class="form-label">📧 Email</label>
                <input type="email" name="email" class="form-input"
                    placeholder="email@contoh.com"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                    required autocomplete="email" autofocus>
            </div>

            <div class="form-group" style="position:relative">
                <label class="form-label">🔒 Password</label>
                <div style="position:relative">
                    <input type="password" name="password" id="loginPass" class="form-input"
                        placeholder="Password kamu" required autocomplete="current-password"
                        style="padding-right:3rem">
                    <button type="button" onclick="togglePass()"
                        style="position:absolute;right:.75rem;top:50%;transform:translateY(-50%);
                            background:none;border:none;color:var(--gray);cursor:pointer;font-size:.95rem"
                        id="eyeBtn">👁</button>
                </div>
            </div>

            <div style="display:flex;justify-content:space-between;align-items:center;
                margin-bottom:1.5rem;font-size:.8rem">
                <label style="display:flex;align-items:center;gap:.5rem;color:var(--gray);cursor:pointer">
                    <input type="checkbox" name="remember" style="accent-color:var(--blue)">
                    Ingat saya (30 hari)
                </label>
                <a href="forgot-password.php" style="color:var(--blue)">Lupa password?</a>
            </div>

            <button type="submit" id="loginBtn" class="btn-primary ripple-host"
                style="width:100%;justify-content:center;padding:1rem;font-size:.95rem">
                Masuk →
            </button>

        </form>

        <div style="display:flex;align-items:center;gap:.875rem;margin:1.25rem 0">
            <div style="flex:1;height:1px;background:var(--border)"></div>
            <span style="font-size:.75rem;color:var(--gray2)">atau</span>
            <div style="flex:1;height:1px;background:var(--border)"></div>
        </div>

        <div style="text-align:center;font-size:.875rem;color:var(--gray)">
            Belum punya akun?
            <a href="register.php" style="color:var(--cyan);font-weight:700">Daftar Gratis →</a>
        </div>
    </div>

    <div style="margin-top:1rem;background:rgba(37,99,235,.06);border:1px solid rgba(37,99,235,.15);
        border-radius:var(--radius);padding:1rem;text-align:center;font-size:.8rem;color:var(--gray)">
        💡 Tidak perlu login untuk order — langsung pilih produk dan hubungi admin.
        <a href="products.php" style="color:var(--cyan);font-weight:600;display:block;margin-top:.35rem">
            Lihat Produk →
        </a>
    </div>

</div>
</div>

<script>
function togglePass() {
    const inp = document.getElementById('loginPass');
    const btn = document.getElementById('eyeBtn');
    if (inp.type === 'password') { inp.type = 'text';     btn.textContent = '🙈'; }
    else                         { inp.type = 'password'; btn.textContent = '👁'; }
}
document.getElementById('loginForm')?.addEventListener('submit', function() {
    const btn = document.getElementById('loginBtn');
    btn.style.opacity = '.7';
    btn.style.pointerEvents = 'none';
    btn.innerHTML = '<span style="display:inline-block;animation:spin .8s linear infinite">⏳</span> Memproses...';
});
const gamesData = <?= json_encode(array_map(fn($g)=>['name'=>$g['name'],'slug'=>$g['slug'],'icon'=>$g['icon'],'currency'=>$g['currency']],$games)) ?>;
</script>
<style>
@keyframes spin { to { transform: rotate(360deg); } }
</style>

<?php require_once 'includes/footer.php'; ?>
