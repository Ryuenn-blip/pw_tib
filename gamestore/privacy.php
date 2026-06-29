<?php
require_once 'includes/config.php';
$page_title = 'Kebijakan Privasi';
require_once 'includes/header.php';
?>
<div style="padding:100px 0 4rem">
<div class="container" style="max-width:800px">
<div style="text-align:center;margin-bottom:3rem">
    <div style="display:inline-flex;align-items:center;gap:.5rem;background:rgba(37,99,235,.1);
        border:1px solid rgba(37,99,235,.25);color:var(--cyan);
        padding:.35rem 1rem;border-radius:100px;font-size:.8rem;font-weight:600;margin-bottom:1rem">🔒 Privasi</div>
    <h1 style="font-size:2rem;font-weight:900;margin-bottom:.5rem">Kebijakan Privasi</h1>
    <p style="color:var(--gray);font-size:.875rem">Terakhir diperbarui: <?= date('d F Y') ?></p>
</div>
<?php
$sections = [
    ['🗂️','Data yang Kami Kumpulkan','Kami mengumpulkan data yang kamu berikan saat melakukan order: nama, nomor WhatsApp, dan User ID game. Kami tidak mengumpulkan password, data kartu kredit, atau informasi sensitif lainnya.'],
    ['🔒','Cara Kami Melindungi Data','Data kamu disimpan aman di server kami dan tidak pernah dijual atau dibagikan kepada pihak ketiga tanpa persetujuanmu, kecuali diwajibkan oleh hukum.'],
    ['📲','Penggunaan Data','Data kamu hanya digunakan untuk: memproses order top up, menghubungi kamu terkait transaksi, dan meningkatkan layanan kami.'],
    ['🍪','Cookie','Website ini menggunakan cookie sesi untuk keamanan dan pengalaman pengguna yang lebih baik. Cookie tidak menyimpan data pribadi.'],
    ['👶','Anak di Bawah Umur','Layanan kami tidak ditujukan untuk anak di bawah 13 tahun. Jika kamu berusia di bawah 13 tahun, harap dapatkan izin orang tua sebelum menggunakan layanan ini.'],
    ['✉️','Kontak','Pertanyaan tentang privasi? Hubungi kami di admin@gamestore.id atau via Live Chat.'],
];
foreach ($sections as [$icon,$title,$desc]):
?>
<div style="background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius-lg);
    padding:1.5rem;margin-bottom:.875rem">
    <h2 style="font-size:1rem;font-weight:800;margin-bottom:.75rem;display:flex;align-items:center;gap:.5rem">
        <span><?= $icon ?></span><?= $title ?>
    </h2>
    <p style="color:var(--gray);font-size:.875rem;line-height:1.75"><?= $desc ?></p>
</div>
<?php endforeach; ?>
<div style="text-align:center;margin-top:2rem;font-size:.82rem;color:var(--gray)">
    Ada pertanyaan? <a href="contact.php" style="color:var(--cyan);font-weight:600">Hubungi Kami →</a>
</div>
</div>
</div>
<script>
const gamesData = <?= json_encode(array_map(fn($g)=>['name'=>$g['name'],'slug'=>$g['slug'],'icon'=>$g['icon'],'currency'=>$g['currency']],$games)) ?>;
</script>
<?php require_once 'includes/footer.php'; ?>
