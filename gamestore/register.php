<?php
require_once 'includes/config.php';
session_start();

if (user_logged_in()) {
    header('Location: dashboard.php'); exit;
}

$error   = '';
$success = '';
$old     = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = [
        'name'  => trim($_POST['name']  ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
    ];
    $password  = $_POST['password']  ?? '';
    $password2 = $_POST['password2'] ?? '';
    $agree     = !empty($_POST['agree']);

    // Validasi
    if (!$old['name'] || strlen($old['name']) < 3)
        $error = 'Nama minimal 3 karakter.';
    elseif (!$old['email'] || !filter_var($old['email'], FILTER_VALIDATE_EMAIL))
        $error = 'Format email tidak valid.';
    elseif (!$old['phone'] || !preg_match('/^[0-9]{9,13}$/', preg_replace('/[-\s]/','',$old['phone'])))
        $error = 'Nomor WhatsApp tidak valid (9-13 digit angka).';
    elseif (strlen($password) < 8)
        $error = 'Password minimal 8 karakter.';
    elseif ($password !== $password2)
        $error = 'Konfirmasi password tidak cocok.';
    elseif (!$agree)
        $error = 'Kamu harus menyetujui Syarat & Ketentuan.';
    else {
        $result = user_register($old['name'], $old['email'], $old['phone'], $password);
        if ($result['success']) {
            header('Location: login.php?registered=1');
            exit;
        }
        $error = $result['msg'];
    }
}

$page_title = 'Daftar Akun';
require_once 'includes/header.php';
?>

<div style="min-height:calc(100vh - 68px);display:flex;align-items:center;
    justify-content:center;padding:100px 1.5rem 4rem">
<div style="width:100%;max-width:460px">

    <div style="text-align:center;margin-bottom:2rem">
        <div style="font-size:3rem;margin-bottom:.75rem">🎮</div>
        <h1 style="font-size:1.75rem;font-weight:900;margin-bottom:.25rem">Buat Akun GameStore</h1>
        <p style="color:var(--gray);font-size:.875rem">Daftar gratis, nikmati kemudahan top up game</p>
    </div>

    <!-- Benefits -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.625rem;margin-bottom:1.75rem">
        <?php foreach ([
            ['⚡','Proses Instan','Top up cepat & mudah'],
            ['📋','Riwayat Order','Pantau semua transaksi'],
            ['💰','Harga Spesial','Diskon member eksklusif'],
            ['🎁','Poin Reward','Kumpulkan poin tiap order'],
        ] as [$icon,$title,$desc]): ?>
        <div style="background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius);
            padding:.875rem;display:flex;gap:.625rem;align-items:flex-start">
            <span style="font-size:1.2rem;flex-shrink:0"><?= $icon ?></span>
            <div>
                <div style="font-weight:700;font-size:.82rem"><?= $title ?></div>
                <div style="font-size:.72rem;color:var(--gray)"><?= $desc ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div style="background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius-lg);padding:2rem">

        <?php if ($error): ?>
        <div style="background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.25);
            border-radius:var(--radius);padding:.875rem 1rem;margin-bottom:1.25rem;
            color:var(--danger);font-size:.875rem;display:flex;align-items:center;gap:.5rem">
            ⚠️ <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="register.php" novalidate id="regForm">

            <div class="form-group">
                <label class="form-label">👤 Nama Lengkap *</label>
                <input type="text" name="name" class="form-input"
                    placeholder="Nama lengkap kamu" maxlength="100"
                    value="<?= htmlspecialchars($old['name'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">📧 Email *</label>
                <input type="email" name="email" class="form-input"
                    placeholder="email@contoh.com"
                    value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                    required autocomplete="email">
            </div>

            <div class="form-group">
                <label class="form-label">📱 Nomor WhatsApp *</label>
                <div style="display:flex;gap:.5rem">
                    <div style="background:var(--bg3);border:1.5px solid var(--border);
                        border-radius:var(--radius);padding:.65rem .875rem;
                        font-size:.875rem;color:var(--gray);flex-shrink:0;white-space:nowrap">
                        🇮🇩 +62
                    </div>
                    <input type="tel" name="phone" class="form-input"
                        placeholder="812-3456-7890" style="flex:1"
                        value="<?= htmlspecialchars($old['phone'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">🔒 Password *</label>
                <div style="position:relative">
                    <input type="password" name="password" id="regPass" class="form-input"
                        placeholder="Min. 8 karakter" style="padding-right:3rem"
                        oninput="checkStrength(this.value)" required>
                    <button type="button" onclick="togglePass('regPass','eye1')"
                        style="position:absolute;right:.75rem;top:50%;transform:translateY(-50%);
                            background:none;border:none;color:var(--gray);cursor:pointer" id="eye1">👁</button>
                </div>
                <!-- Strength bar -->
                <div id="strengthWrap" style="display:none;margin-top:.4rem">
                    <div style="height:4px;background:var(--bg3);border-radius:2px;overflow:hidden">
                        <div id="strengthBar" style="height:100%;border-radius:2px;transition:.3s;width:0"></div>
                    </div>
                    <div id="strengthLabel" style="font-size:.7rem;margin-top:.2rem"></div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">🔒 Konfirmasi Password *</label>
                <div style="position:relative">
                    <input type="password" name="password2" id="regPass2" class="form-input"
                        placeholder="Ulangi password" style="padding-right:3rem" required>
                    <button type="button" onclick="togglePass('regPass2','eye2')"
                        style="position:absolute;right:.75rem;top:50%;transform:translateY(-50%);
                            background:none;border:none;color:var(--gray);cursor:pointer" id="eye2">👁</button>
                </div>
            </div>

            <div style="display:flex;align-items:flex-start;gap:.625rem;margin-bottom:1.5rem">
                <input type="checkbox" name="agree" id="agreeBox"
                    style="accent-color:var(--blue);width:16px;height:16px;margin-top:2px;flex-shrink:0"
                    <?= !empty($_POST['agree']) ? 'checked' : '' ?>>
                <label for="agreeBox" style="font-size:.82rem;color:var(--gray);line-height:1.5;cursor:pointer">
                    Saya menyetujui
                    <a href="terms.php" target="_blank" style="color:var(--cyan)">Syarat & Ketentuan</a>
                    dan
                    <a href="privacy.php" target="_blank" style="color:var(--cyan)">Kebijakan Privasi</a>
                    GameStore
                </label>
            </div>

            <button type="submit" id="regBtn" class="btn-primary ripple-host"
                style="width:100%;justify-content:center;padding:1rem;font-size:.95rem">
                🚀 Buat Akun Sekarang
            </button>

        </form>

        <div style="text-align:center;margin-top:1.25rem;font-size:.875rem;color:var(--gray)">
            Sudah punya akun?
            <a href="login.php" style="color:var(--cyan);font-weight:700">Masuk →</a>
        </div>
    </div>

    <div style="display:flex;align-items:center;gap:.875rem;margin:1.25rem 0">
        <div style="flex:1;height:1px;background:var(--border)"></div>
        <span style="font-size:.75rem;color:var(--gray2)">atau order tanpa daftar</span>
        <div style="flex:1;height:1px;background:var(--border)"></div>
    </div>
    <a href="products.php" class="btn-outline"
        style="display:flex;justify-content:center;padding:.875rem">
        🎮 Langsung Order via WhatsApp
    </a>
</div>
</div>

<script>
function togglePass(id, eyeId) {
    const inp = document.getElementById(id);
    const btn = document.getElementById(eyeId);
    if (inp.type === 'password') { inp.type = 'text';     btn.textContent = '🙈'; }
    else                         { inp.type = 'password'; btn.textContent = '👁'; }
}
function checkStrength(v) {
    const wrap  = document.getElementById('strengthWrap');
    const bar   = document.getElementById('strengthBar');
    const label = document.getElementById('strengthLabel');
    if (!v) { wrap.style.display = 'none'; return; }
    wrap.style.display = 'block';
    let score = 0;
    if (v.length >= 8)         score++;
    if (v.length >= 12)        score++;
    if (/[A-Z]/.test(v))       score++;
    if (/[0-9]/.test(v))       score++;
    if (/[^A-Za-z0-9]/.test(v))score++;
    const levels = [
        [20,'#EF4444','Sangat Lemah'],
        [40,'#F59E0B','Lemah'],
        [60,'#EAB308','Cukup'],
        [80,'#22C55E','Kuat'],
        [100,'#00D4FF','Sangat Kuat'],
    ];
    const [pct, color, text] = levels[Math.min(score, 4)];
    bar.style.width      = pct + '%';
    bar.style.background = color;
    label.textContent    = 'Kekuatan: ' + text;
    label.style.color    = color;
}
document.getElementById('regForm')?.addEventListener('submit', function(e) {
    // Validasi dasar di client sebelum submit (server tetap validasi ulang)
    const pass  = document.getElementById('regPass').value;
    const pass2 = document.getElementById('regPass2').value;
    if (pass !== pass2) {
        e.preventDefault();
        alert('⚠️ Konfirmasi password tidak cocok!');
        return;
    }
    const btn = document.getElementById('regBtn');
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
