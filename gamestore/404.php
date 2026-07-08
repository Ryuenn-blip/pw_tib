<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$page_title = 'Halaman Tidak Ditemukan';
http_response_code(404);
require_once 'includes/header.php';
?>
<style>
@keyframes floatAnim { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-16px)} }
</style>
<div style="min-height:calc(100vh - 68px);display:flex;align-items:center;justify-content:center;padding:100px 1.5rem 4rem">
<div style="text-align:center;max-width:520px">
    <div style="font-size:6rem;margin-bottom:1rem;animation:floatAnim 3s ease infinite">🎮</div>
    <h1 style="font-size:5rem;font-weight:900;background:linear-gradient(135deg,var(--blue),var(--cyan));
        -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
        margin-bottom:.5rem;line-height:1">404</h1>
    <h2 style="font-size:1.5rem;font-weight:800;margin-bottom:.875rem">Halaman Tidak Ditemukan</h2>
    <p style="color:var(--gray);margin-bottom:.75rem;line-height:1.75;font-size:.95rem">
        Halaman yang kamu cari tidak ada, sudah dipindahkan, atau URL-nya salah.
    </p>
    <!-- Search -->
    <div style="position:relative;max-width:380px;margin:1.25rem auto 1.75rem">
        <input type="text" id="s404" class="form-input" placeholder="Cari game..."
               style="padding-left:2.75rem;font-size:.9rem"
               onkeydown="if(event.key==='Enter'&&this.value.trim())location.href='search.php?q='+encodeURIComponent(this.value.trim())">
        <span style="position:absolute;left:.875rem;top:50%;transform:translateY(-50%);opacity:.6">🔍</span>
    </div>
    <div style="display:flex;gap:.875rem;justify-content:center;flex-wrap:wrap">
        <a href="index.php" class="btn-primary" style="display:inline-flex;align-items:center;gap:.5rem;padding:.875rem 1.5rem">
            🏠 Ke Beranda
        </a>
        <a href="products.php" class="btn-outline" style="display:inline-flex;align-items:center;gap:.5rem;padding:.875rem 1.5rem">
            🎮 Lihat Produk
        </a>
    </div>
    <div style="margin-top:1.5rem">
        <a href="javascript:history.back()" style="font-size:.82rem;color:var(--gray);text-decoration:none">← Kembali ke halaman sebelumnya</a>
    </div>
</div>
</div>
<script>
const gamesData = <?= json_encode(array_map(fn($g)=>['name'=>$g['name'],'slug'=>$g['slug'],'icon'=>$g['icon'],'currency'=>$g['currency']],$games)) ?>;
</script>
<?php require_once 'includes/footer.php'; ?>
