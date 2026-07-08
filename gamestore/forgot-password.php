<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/config.php';

if (user_logged_in()) { header('Location: dashboard.php'); exit; }

$step    = $_GET['step'] ?? 'request';
$token   = $_GET['token'] ?? '';
$message = '';
$error   = '';

// ── STEP 1: Request reset ─────────────────────────────────────
if ($step === 'request' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Masukkan email yang valid.';
    } else {
        $user = db_row("SELECT * FROM customers WHERE email=? AND is_active=1", [$email]);
        if ($user) {
            // Buat token reset
            $reset_token  = bin2hex(random_bytes(32));
            $expires_at   = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Simpan token ke DB (kolom reset_token di tabel customers)
            // Jika kolom belum ada, tambahkan via migration
            try {
                db_exec("UPDATE customers SET reset_token=?, reset_expires=? WHERE id=?",
                    [$reset_token, $expires_at, $user['id']]);

                // Dalam produksi: kirim email dengan link reset
                // Untuk demo: simpan di session
                $_SESSION['reset_token_demo'] = $reset_token;
                $_SESSION['reset_email_demo'] = $email;

                $message = "Link reset password sudah dikirim ke <strong>$email</strong>.<br>
                    Cek kotak masuk atau folder spam kamu.<br><br>
                    <small style='color:var(--gray2)'>Link berlaku selama 1 jam.</small>";
            } catch (\Exception $e) {
                // Kolom reset_token mungkin belum ada — gunakan session saja
                $_SESSION['reset_token_demo'] = bin2hex(random_bytes(32));
                $_SESSION['reset_email_demo'] = $email;
                $message = "Link reset password sudah dikirim ke <strong>$email</strong>.<br>
                    <small style='color:var(--gray2)'>Cek kotak masuk kamu.</small>";
            }
        } else {
            // Jangan bocorkan apakah email terdaftar
            $message = "Jika email tersebut terdaftar, kami akan mengirimkan link reset.<br>
                <small style='color:var(--gray2)'>Cek kotak masuk atau folder spam kamu.</small>";
        }
    }
}

// ── STEP 2: Set new password ──────────────────────────────────
if ($step === 'reset' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $token_in = trim($_POST['token'] ?? '');
    $new_pass = trim($_POST['password'] ?? '');
    $confirm  = trim($_POST['confirm']  ?? '');

    $valid_token = !empty($_SESSION['reset_token_demo']) &&
                   hash_equals($_SESSION['reset_token_demo'], $token_in);

    if (!$valid_token)         { $error = 'Token tidak valid atau sudah kedaluwarsa.'; }
    elseif (strlen($new_pass) < 8) { $error = 'Password minimal 8 karakter.'; }
    elseif ($new_pass !== $confirm) { $error = 'Konfirmasi password tidak cocok.'; }
    else {
        $email = $_SESSION['reset_email_demo'] ?? '';
        if ($email) {
            db_exec("UPDATE customers SET password=?, reset_token=NULL, reset_expires=NULL WHERE email=?",
                [password_hash($new_pass, PASSWORD_DEFAULT), $email]);
            unset($_SESSION['reset_token_demo'], $_SESSION['reset_email_demo']);
            $message = 'success';
        } else {
            $error = 'Sesi tidak valid. Silakan mulai ulang proses reset.';
        }
    }
}

// Validasi token di GET (step=reset)
$token_valid = false;
if ($step === 'reset' && $token) {
    $token_valid = !empty($_SESSION['reset_token_demo']) &&
                   hash_equals($_SESSION['reset_token_demo'], $token);
    if (!$token_valid) {
        $error = 'Link reset tidak valid atau sudah kedaluwarsa.';
        $step  = 'request';
    }
}

$page_title = 'Lupa Password';
require_once 'includes/header.php';
?>

<div style="min-height:calc(100vh - 68px);display:flex;align-items:center;
    justify-content:center;padding:100px 1.5rem 4rem">
<div style="width:100%;max-width:420px">

    <div style="text-align:center;margin-bottom:2rem">
        <div style="font-size:3rem;margin-bottom:.75rem">🔐</div>
        <h1 style="font-size:1.75rem;font-weight:900;margin-bottom:.25rem">
            <?= $step==='reset' ? 'Buat Password Baru' : 'Lupa Password?' ?>
        </h1>
        <p style="color:var(--gray);font-size:.875rem">
            <?= $step==='reset'
                ? 'Masukkan password baru untuk akunmu'
                : 'Masukkan email kamu dan kami akan mengirimkan link reset' ?>
        </p>
    </div>

    <div style="background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius-lg);padding:2rem">

        <?php if ($error): ?>
        <div style="background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.25);
            border-radius:var(--radius);padding:.875rem 1rem;margin-bottom:1.25rem;
            color:var(--danger);font-size:.875rem">⚠️ <?= $error ?></div>
        <?php endif; ?>

        <?php if ($message === 'success'): ?>
        <!-- Password berhasil direset -->
        <div style="text-align:center;padding:1rem 0">
            <div style="font-size:3rem;margin-bottom:1rem">✅</div>
            <h3 style="font-weight:800;margin-bottom:.5rem">Password Berhasil Direset!</h3>
            <p style="color:var(--gray);font-size:.875rem;margin-bottom:1.5rem">
                Password kamu sudah diperbarui. Silakan login dengan password baru.
            </p>
            <a href="login.php" class="btn-primary"
               style="display:flex;justify-content:center;padding:1rem">Masuk Sekarang →</a>
        </div>

        <?php elseif ($message && $step === 'request'): ?>
        <!-- Email terkirim -->
        <div style="text-align:center;padding:1rem 0">
            <div style="font-size:3rem;margin-bottom:1rem">📧</div>
            <p style="font-size:.9rem;line-height:1.7;margin-bottom:1.5rem"><?= $message ?></p>
            <!-- Demo: tampilkan link langsung karena tidak ada email server -->
            <?php if (!empty($_SESSION['reset_token_demo'])): ?>
            <div style="background:rgba(37,99,235,.08);border:1px solid rgba(37,99,235,.2);
                border-radius:var(--radius);padding:.875rem;margin-bottom:1rem;font-size:.78rem;color:var(--gray)">
                🔧 <strong style="color:var(--white)">Mode Demo:</strong>
                <a href="forgot-password.php?step=reset&token=<?= $_SESSION['reset_token_demo'] ?>"
                   style="color:var(--cyan);display:block;margin-top:.375rem;word-break:break-all">
                    Klik link reset →
                </a>
            </div>
            <?php endif; ?>
            <a href="forgot-password.php" style="font-size:.85rem;color:var(--blue)">← Kirim Ulang</a>
        </div>

        <?php elseif ($step === 'reset' && $token_valid): ?>
        <!-- Form reset password -->
        <form method="POST" action="forgot-password.php?step=reset">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
            <div class="form-group">
                <label class="form-label">🔒 Password Baru *</label>
                <div style="position:relative">
                    <input type="password" name="password" id="np" class="form-input"
                           placeholder="Min. 8 karakter" style="padding-right:3rem" required
                           oninput="checkStr(this.value)">
                    <button type="button" onclick="tp('np','e1')"
                        style="position:absolute;right:.75rem;top:50%;transform:translateY(-50%);
                            background:none;border:none;color:var(--gray);cursor:pointer" id="e1">👁</button>
                </div>
                <div id="strWrap" style="display:none;margin-top:.4rem">
                    <div style="height:4px;background:var(--bg3);border-radius:2px;overflow:hidden">
                        <div id="strBar" style="height:100%;border-radius:2px;transition:.3s;width:0"></div>
                    </div>
                    <div id="strLabel" style="font-size:.7rem;margin-top:.2rem"></div>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">🔒 Konfirmasi Password *</label>
                <div style="position:relative">
                    <input type="password" name="confirm" id="cp" class="form-input"
                           placeholder="Ulangi password" style="padding-right:3rem" required>
                    <button type="button" onclick="tp('cp','e2')"
                        style="position:absolute;right:.75rem;top:50%;transform:translateY(-50%);
                            background:none;border:none;color:var(--gray);cursor:pointer" id="e2">👁</button>
                </div>
            </div>
            <button type="submit" class="btn-primary"
                    style="width:100%;justify-content:center;padding:1rem;margin-top:.5rem">
                ✅ Simpan Password Baru
            </button>
        </form>

        <?php else: ?>
        <!-- Form request email -->
        <form method="POST" action="forgot-password.php">
            <div class="form-group">
                <label class="form-label">📧 Email Terdaftar *</label>
                <input type="email" name="email" class="form-input"
                       placeholder="email@contoh.com" required autofocus autocomplete="email"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <button type="submit" class="btn-primary"
                    style="width:100%;justify-content:center;padding:1rem">
                📧 Kirim Link Reset
            </button>
        </form>
        <?php endif; ?>

        <?php if ($step === 'request' && !$message): ?>
        <div style="text-align:center;margin-top:1.25rem;font-size:.85rem;color:var(--gray)">
            Ingat password?
            <a href="login.php" style="color:var(--cyan);font-weight:700">Masuk →</a>
        </div>
        <?php endif; ?>
    </div>

    <div style="margin-top:1rem;background:rgba(37,99,235,.06);border:1px solid rgba(37,99,235,.15);
        border-radius:var(--radius);padding:1rem;text-align:center;font-size:.8rem;color:var(--gray)">
        💬 Butuh bantuan?
        <a href="https://wa.me/<?= WHATSAPP_NUMBER ?>" target="_blank"
           style="color:var(--cyan);font-weight:600">Chat Admin WA →</a>
    </div>

</div>
</div>

<script>
function tp(id,eid){const i=document.getElementById(id),b=document.getElementById(eid);i.type=i.type==='password'?'text':'password';b.textContent=i.type==='password'?'👁':'🙈';}
function checkStr(v){
    const w=document.getElementById('strWrap'),bar=document.getElementById('strBar'),lbl=document.getElementById('strLabel');
    if(!v){w.style.display='none';return;}w.style.display='block';
    let sc=0;if(v.length>=8)sc++;if(v.length>=12)sc++;if(/[A-Z]/.test(v))sc++;if(/[0-9]/.test(v))sc++;if(/[^A-Za-z0-9]/.test(v))sc++;
    const lv=[[20,'#EF4444','Sangat Lemah'],[40,'#F59E0B','Lemah'],[60,'#EAB308','Cukup'],[80,'#22C55E','Kuat'],[100,'#00D4FF','Sangat Kuat']];
    const [p,c,t]=lv[Math.min(sc,4)];bar.style.width=p+'%';bar.style.background=c;lbl.textContent='Kekuatan: '+t;lbl.style.color=c;
}
const gamesData = <?= json_encode(array_map(fn($g)=>['name'=>$g['name'],'slug'=>$g['slug'],'icon'=>$g['icon'],'currency'=>$g['currency']],$games)) ?>;
</script>
<?php require_once 'includes/footer.php'; ?>
