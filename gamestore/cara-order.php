<?php
require_once 'includes/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$page_title = 'Cara Order';
require_once 'includes/header.php';
?>
<style>
.how-page{padding:100px 0 4rem}
.steps-wrap{max-width:860px;margin:0 auto;padding:0 1.5rem}
.step-block{display:grid;grid-template-columns:80px 1fr;gap:1.5rem;align-items:start;margin-bottom:2.5rem;position:relative}
.step-block:not(:last-child)::before{content:'';position:absolute;left:39px;top:80px;width:2px;height:calc(100% - 60px);background:linear-gradient(to bottom,var(--blue),transparent);z-index:0}
.step-num{width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,var(--blue),var(--cyan));display:flex;align-items:center;justify-content:center;font-size:1.75rem;font-weight:900;flex-shrink:0;box-shadow:0 0 24px rgba(37,99,235,.35);position:relative;z-index:1}
.step-content{padding-top:.5rem}
.step-title{font-size:1.15rem;font-weight:900;margin-bottom:.5rem}
.step-desc{color:var(--gray);line-height:1.75;font-size:.9rem;margin-bottom:.875rem}
.step-tip{background:rgba(37,99,235,.07);border:1px solid rgba(37,99,235,.18);border-radius:8px;padding:.75rem;font-size:.8rem;color:var(--gray);line-height:1.6}
.step-tip strong{color:var(--white)}
.method-chips{display:flex;flex-wrap:wrap;gap:.4rem;margin-top:.625rem}
.method-chip{background:var(--bg3);border:1px solid var(--border);padding:.3rem .75rem;border-radius:100px;font-size:.75rem;font-weight:600}
.warning-box{background:rgba(245,158,11,.07);border:1px solid rgba(245,158,11,.25);border-radius:var(--radius);padding:1rem;font-size:.82rem;color:var(--gray);line-height:1.7;margin-top:.875rem}
</style>

<div class="how-page">
<div class="steps-wrap">

    <div style="text-align:center;margin-bottom:3rem">
        <div style="display:inline-flex;align-items:center;gap:.5rem;background:rgba(37,99,235,.1);
            border:1px solid rgba(37,99,235,.25);color:var(--cyan);
            padding:.35rem 1rem;border-radius:100px;font-size:.8rem;font-weight:600;margin-bottom:1rem">
            📋 Panduan Lengkap
        </div>
        <h1 style="font-size:2rem;font-weight:900;margin-bottom:.5rem">Cara Order Top Up</h1>
        <p style="color:var(--gray)">Ikuti langkah berikut untuk top up game dengan mudah dan aman</p>
    </div>

    <?php
    $steps = [
        ['1','🎮','Pilih Game & Paket',
         'Buka halaman <a href="products.php" style="color:var(--cyan)">Produk</a> dan pilih game yang ingin kamu top up. Klik game untuk melihat semua paket yang tersedia beserta harganya.',
         '💡 <strong>Tips:</strong> Bandingkan paket dengan bonus untuk mendapatkan nilai terbaik. Paket berlabel "Popular" adalah yang paling banyak dibeli.',
         [['🔥 Free Fire'],['⚔️ Mobile Legends'],['🎯 PUBG Mobile'],['✨ Genshin Impact'],['🎮 Valorant'],['⭐ Honkai Star Rail']]],

        ['2','🆔','Masukkan User ID Game',
         'Setiap game memiliki format User ID yang berbeda. Pastikan ID yang kamu masukkan benar karena item tidak bisa dikembalikan jika salah ID.',
         '⚠️ <strong>Penting:</strong> Untuk Mobile Legends, masukkan ID + Server ID (contoh: 123456789 (1234)). Untuk Free Fire, cukup Player ID tanpa server.',
         []],

        ['3','💳','Pilih Metode Pembayaran',
         'Pilih metode pembayaran yang paling nyaman untukmu. Semua metode diproses dengan aman dan cepat.',
         '💡 <strong>Tips:</strong> Transfer QRIS biasanya paling cepat diverifikasi karena otomatis terkonfirmasi.',
         [['💙 DANA'],['💜 OVO'],['💚 GoPay'],['🧡 ShopeePay'],['🏦 BCA'],['🏦 Mandiri'],['🏦 BRI'],['📱 QRIS']]],

        ['4','💰','Lakukan Pembayaran',
         'Transfer tepat sesuai nominal yang tertera. Jangan kurangi atau lebihkan karena admin memverifikasi berdasarkan nominal tepat.',
         '⚠️ <strong>Penting:</strong> Simpan bukti transfer berupa screenshot/foto. Bukti diperlukan untuk konfirmasi.',
         []],

        ['5','📸','Upload Bukti & Konfirmasi',
         'Setelah transfer, upload foto bukti pembayaran di halaman konfirmasi. Atau kirim langsung ke WhatsApp admin beserta detail ordermu.',
         '💡 <strong>Tips:</strong> Kirim pesan ke WA admin dengan format: Nama | ID Game | Paket | Nominal | Metode Bayar',
         []],

        ['6','⚡','Tunggu Proses (1-5 Menit)',
         'Admin akan memverifikasi pembayaran dan memproses top up. Normalnya selesai dalam 1-5 menit. Kamu akan mendapat konfirmasi via WA atau Live Chat.',
         '💡 <strong>Info:</strong> Proses lebih cepat di jam operasional (08.00-22.00 WIB). Di luar jam tsb, akan diproses saat admin online.',
         []],
    ];

    foreach ($steps as [$num,$emoji,$title,$desc,$tip,$chips]): ?>
    <div class="step-block">
        <div class="step-num"><?= $emoji ?></div>
        <div class="step-content">
            <div style="display:flex;align-items:center;gap:.625rem;margin-bottom:.5rem">
                <span style="background:rgba(37,99,235,.15);border:1px solid rgba(37,99,235,.3);color:var(--cyan);
                    font-size:.72rem;font-weight:800;padding:.2rem .625rem;border-radius:100px">
                    LANGKAH <?= $num ?>
                </span>
            </div>
            <div class="step-title"><?= $title ?></div>
            <div class="step-desc"><?= $desc ?></div>
            <?php if (!empty($chips)): ?>
            <div class="method-chips">
                <?php foreach ($chips as [$chip]): ?>
                <span class="method-chip"><?= $chip ?></span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <?php if ($tip): ?>
            <div class="step-tip" style="margin-top:.75rem"><?= $tip ?></div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- Warning box -->
    <div class="warning-box">
        <div style="font-weight:800;color:var(--warning);margin-bottom:.5rem">⚠️ Hal yang Perlu Diperhatikan</div>
        <?php foreach ([
            'Pastikan User ID game <strong style="color:var(--white)">100% benar</strong> sebelum order — item tidak bisa dikembalikan jika ID salah.',
            'Transfer <strong style="color:var(--white)">sesuai nominal tepat</strong> — jangan lebih/kurang.',
            'GameStore <strong style="color:var(--white)">tidak pernah</strong> meminta password akun game kamu.',
            'Jika ada masalah, hubungi kami via WA atau Live Chat <strong style="color:var(--white)">sebelum melakukan order ulang</strong>.',
        ] as $item): ?>
        <div style="display:flex;align-items:flex-start;gap:.5rem;margin-bottom:.4rem">
            <span style="color:var(--warning);flex-shrink:0">•</span>
            <span><?= $item ?></span>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- CTA -->
    <div style="text-align:center;margin-top:3rem">
        <div style="font-size:.9rem;color:var(--gray);margin-bottom:1.25rem">
            Sudah paham cara ordernya?
        </div>
        <div style="display:flex;gap:.875rem;justify-content:center;flex-wrap:wrap">
            <a href="products.php" class="btn-primary"
               style="display:inline-flex;align-items:center;gap:.5rem;padding:.875rem 1.75rem">
               🎮 Mulai Top Up
            </a>
            <a href="https://wa.me/<?= WHATSAPP_NUMBER ?>?text=Halo+admin,+saya+mau+tanya+dulu+sebelum+order"
               target="_blank" class="btn-outline"
               style="display:inline-flex;align-items:center;gap:.5rem;padding:.875rem 1.75rem">
               💬 Tanya Admin Dulu
            </a>
        </div>
    </div>

</div>
</div>

<script>
const gamesData = <?= json_encode(array_map(fn($g)=>['name'=>$g['name'],'slug'=>$g['slug'],'icon'=>$g['icon'],'currency'=>$g['currency']],$games)) ?>;
</script>
<?php require_once 'includes/footer.php'; ?>
