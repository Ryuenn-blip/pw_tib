<?php
require_once 'includes/config.php';
$page_title = 'Login';
require_once 'includes/header.php';
?>

<div style="min-height:calc(100vh - 68px); display:flex; align-items:center; justify-content:center; padding:100px 1.5rem 4rem">
    <div style="width:100%; max-width:420px">
        <div style="text-align:center; margin-bottom:2rem">
            <div style="font-size:3rem; margin-bottom:.75rem">🎮</div>
            <h1 style="font-size:1.75rem; font-weight:900; margin-bottom:.25rem">Masuk ke GameStore</h1>
            <p style="color:var(--gray); font-size:.875rem">Masuk untuk melihat riwayat transaksi</p>
        </div>

        <div style="background:var(--bg2); border:1px solid var(--border); border-radius:var(--radius-lg); padding:2rem">
            <div class="form-group">
                <label class="form-label">📧 Email / Username</label>
                <input type="email" class="form-input" placeholder="email@contoh.com">
            </div>
            <div class="form-group">
                <label class="form-label">🔒 Password</label>
                <input type="password" class="form-input" placeholder="••••••••">
            </div>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; font-size:.8rem">
                <label style="display:flex; align-items:center; gap:.5rem; color:var(--gray); cursor:pointer">
                    <input type="checkbox" style="accent-color:var(--blue)"> Ingat saya
                </label>
                <a href="#" style="color:var(--blue)">Lupa password?</a>
            </div>
            <button class="btn-primary" style="width:100%; justify-content:center; padding:1rem"
                    onclick="showToast('⚠️ Fitur login akan segera hadir!', 'error')">
                Masuk
            </button>
            <div style="text-align:center; margin-top:1.25rem; font-size:.85rem; color:var(--gray)">
                Belum punya akun? <a href="register.php" style="color:var(--cyan); font-weight:600">Daftar</a>
            </div>
        </div>

        <div style="margin-top:1.25rem; background:rgba(37,99,235,.08); border:1px solid rgba(37,99,235,.2);
                    border-radius:var(--radius); padding:1rem; text-align:center; font-size:.8rem; color:var(--gray)">
            💡 Kamu tidak perlu login untuk melakukan order. Cukup pilih produk dan hubungi admin via WhatsApp.
            <a href="products.php" style="color:var(--cyan); font-weight:600; display:block; margin-top:.5rem">Lihat Produk →</a>
        </div>
    </div>
</div>

<script>
const gamesData = <?= json_encode(array_map(fn($g) => [
    'name' => $g['name'], 'slug' => $g['slug'],
    'icon' => $g['icon'], 'currency' => $g['currency']
], $games)) ?>;
</script>

<?php require_once 'includes/footer.php'; ?>
