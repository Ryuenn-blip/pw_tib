<?php
require_once 'includes/config.php';
$page_title = 'Halaman Tidak Ditemukan';
http_response_code(404);
require_once 'includes/header.php';
?>
<div style="min-height:calc(100vh - 68px);display:flex;align-items:center;justify-content:center;padding:100px 1.5rem 4rem">
<div style="text-align:center;max-width:480px">
    <div style="font-size:6rem;margin-bottom:1rem;animation:float 3s ease infinite">🎮</div>
    <h1 style="font-size:5rem;font-weight:900;background:linear-gradient(135deg,var(--blue),var(--cyan));
        -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;margin-bottom:.5rem">404</h1>
    <h2 style="font-size:1.5rem;font-weight:800;margin-bottom:.75rem">Halaman Tidak Ditemukan</h2>
    <p style="color:var(--gray);margin-bottom:2rem;line-height:1.7">
        Halaman yang kamu cari tidak ada atau sudah dipindahkan. Coba kembali ke beranda atau cari game yang kamu inginkan.
    </p>
    <div style="display:flex;gap:.875rem;justify-content:center;flex-wrap:wrap">
        <a href="index.php" class="btn-primary" style="display:inline-flex;align-items:center;gap:.5rem;padding:.875rem 1.5rem">
            🏠 Kembali ke Beranda
        </a>
        <a href="products.php" class="btn-outline" style="display:inline-flex;align-items:center;gap:.5rem;padding:.875rem 1.5rem">
            🎮 Lihat Produk
        </a>
    </div>
</div>
</div>
<script>
const gamesData = <?= json_encode(array_map(fn($g)=>['name'=>$g['name'],'slug'=>$g['slug'],'icon'=>$g['icon'],'currency'=>$g['currency']],$games)) ?>;
</script>
<?php require_once 'includes/footer.php'; ?>
