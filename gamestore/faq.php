<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$page_title = 'FAQ — Pertanyaan Umum';
require_once 'includes/header.php';

$faqs = [
    'Top Up & Order' => [
        ['Berapa lama proses top up?','Proses top up biasanya <strong style="color:var(--white)">1-5 menit</strong> setelah pembayaran dikonfirmasi. Pada jam sibuk bisa 5-15 menit. Kami akan memberi tahu jika ada keterlambatan.'],
        ['Bagaimana cara mengetahui User ID saya?','Setiap game berbeda: <br>• <strong>Mobile Legends:</strong> Profil → ID tertera di bawah nama (format: 123456789 (1234))<br>• <strong>Free Fire:</strong> Profil → Player ID<br>• <strong>PUBG Mobile:</strong> Inventory → Character ID<br>• <strong>Genshin:</strong> Menu → UID'],
        ['Apakah bisa top up untuk akun orang lain?','Bisa! Cukup masukkan User ID akun yang ingin di-top up. Tidak perlu login ke akun tersebut.'],
        ['Bisakah saya membatalkan order?','Order yang sudah dikonfirmasi dan diproses tidak bisa dibatalkan. Pastikan semua detail sudah benar sebelum konfirmasi.'],
        ['Mengapa top up saya belum masuk padahal sudah bayar?','Kemungkinan penyebab: (1) Pembayaran belum dikonfirmasi admin, (2) Sedang jam sibuk. Hubungi admin via WA/Live Chat dengan bukti pembayaran.'],
    ],
    'Pembayaran' => [
        ['Metode pembayaran apa saja yang diterima?','Kami menerima: DANA, OVO, GoPay, ShopeePay, LinkAja, Transfer BCA, Transfer Mandiri, Transfer BRI, Transfer BNI, dan QRIS.'],
        ['Apakah ada biaya tambahan?','Tidak ada biaya tambahan atau biaya layanan. Harga yang tertera adalah harga final yang kamu bayar.'],
        ['Mengapa harus transfer nominal tepat?','Untuk mempermudah verifikasi dan menghindari kesalahan. Jika jumlahnya tidak sama, admin akan kesulitan mengidentifikasi pembayaranmu.'],
        ['Transaksi saya gagal, bagaimana?','Jika transaksi gagal di e-wallet atau mobile banking, pastikan saldonya cukup. Coba metode lain jika masih gagal. Hubungi admin jika sudah bayar tapi order tidak diproses.'],
    ],
    'Keamanan & Akun' => [
        ['Apakah data saya aman?','Ya. Kami tidak pernah menyimpan password game kamu. Data pembayaran hanya digunakan untuk keperluan verifikasi order.'],
        ['GameStore pernah minta password akun?','Tidak pernah! Jika ada yang mengatasnamakan GameStore dan meminta password akun game kamu, itu penipuan. Laporkan segera.'],
        ['Apakah saya perlu membuat akun?','Tidak wajib. Kamu bisa langsung order via WhatsApp tanpa akun. Akun berguna untuk melacak riwayat order.'],
        ['Bagaimana cara reset password akun GameStore?','Buka halaman login → klik "Lupa Password" → masukkan email → cek email untuk link reset.'],
    ],
    'Refund & Klaim' => [
        ['Apa syarat untuk refund?','Refund diberikan jika: (1) Top up gagal karena kesalahan sistem kami, (2) Item tidak masuk dalam 30 menit dan terbukti sudah bayar. Refund tidak diberikan jika User ID salah.'],
        ['Berapa lama proses refund?','Proses refund biasanya 1x24 jam setelah klaim diverifikasi. Dana dikembalikan ke metode pembayaran yang sama.'],
        ['Bagaimana cara mengajukan klaim?','Hubungi admin via WhatsApp dengan: bukti pembayaran, User ID game, paket yang dibeli, dan screenshot jika item tidak masuk.'],
    ],
    'Promo & Diskon' => [
        ['Dimana saya bisa dapat kode promo?','Ikuti media sosial GameStore atau daftar sebagai member untuk mendapat notifikasi promo terbaru.'],
        ['Apakah kode promo bisa digabungkan?','Satu order hanya bisa menggunakan satu kode promo. Pilih kode yang memberikan diskon paling besar.'],
        ['Berapa lama kode promo berlaku?','Setiap kode promo memiliki masa berlaku yang berbeda. Cek batas waktu sebelum menggunakannya.'],
    ],
];
?>

<div style="padding:100px 0 4rem;min-height:calc(100vh - 68px)">
<div class="container" style="max-width:860px">

    <div style="text-align:center;margin-bottom:2.5rem">
        <div style="display:inline-flex;align-items:center;gap:.5rem;background:rgba(37,99,235,.1);
            border:1px solid rgba(37,99,235,.25);color:var(--cyan);
            padding:.35rem 1rem;border-radius:100px;font-size:.8rem;font-weight:600;margin-bottom:1rem">
            ❓ Pusat Bantuan
        </div>
        <h1 style="font-size:2rem;font-weight:900;margin-bottom:.75rem">Pertanyaan yang Sering Ditanyakan</h1>

        <!-- Search FAQ -->
        <div style="max-width:480px;margin:0 auto;position:relative">
            <input type="text" id="faqSearch" class="form-input"
                   placeholder="Cari pertanyaan..." autocomplete="off"
                   style="padding-left:2.75rem;font-size:.9rem"
                   oninput="filterFaq(this.value)">
            <span style="position:absolute;left:.875rem;top:50%;transform:translateY(-50%);font-size:1rem;opacity:.6">🔍</span>
            <button id="faqClear" onclick="clearSearch()" style="display:none;position:absolute;right:.875rem;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--gray);cursor:pointer;font-size:1rem">✕</button>
        </div>
    </div>

    <!-- No results msg -->
    <div id="noResults" style="display:none;text-align:center;padding:3rem;color:var(--gray)">
        <div style="font-size:3rem;margin-bottom:.75rem">🔍</div>
        <div style="font-weight:600;color:var(--white);margin-bottom:.5rem">Tidak ditemukan</div>
        <p style="font-size:.875rem;margin-bottom:1.25rem">Coba kata kunci lain atau tanya langsung ke admin.</p>
        <a href="https://wa.me/<?= WHATSAPP_NUMBER ?>" target="_blank" class="btn-primary"
           style="display:inline-flex;align-items:center;gap:.5rem;padding:.75rem 1.5rem">
            💬 Tanya Admin WA
        </a>
    </div>

    <!-- FAQ Categories -->
    <?php foreach ($faqs as $category => $items): ?>
    <div class="faq-category" data-cat="<?= htmlspecialchars($category) ?>" style="margin-bottom:1.5rem">
        <h2 style="font-size:.82rem;font-weight:800;text-transform:uppercase;letter-spacing:.75px;
            color:var(--gray2);margin-bottom:.75rem;padding:.5rem 0;border-bottom:1px solid var(--border)">
            <?= htmlspecialchars($category) ?>
        </h2>
        <?php foreach ($items as [$q,$a]): ?>
        <div class="faq-item" data-q="<?= strtolower(htmlspecialchars($q)) ?>"
             style="background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius);
                 margin-bottom:.5rem;overflow:hidden;transition:var(--transition)">
            <button onclick="toggleFaq(this)"
                style="width:100%;text-align:left;padding:.875rem 1rem;background:none;border:none;
                    color:var(--white);cursor:pointer;display:flex;justify-content:space-between;
                    align-items:center;gap:.5rem;font-family:inherit">
                <span style="font-weight:600;font-size:.875rem"><?= $q ?></span>
                <span class="faq-arrow" style="font-size:1.1rem;color:var(--blue);flex-shrink:0;transition:.25s">+</span>
            </button>
            <div class="faq-answer" style="display:none;padding:.125rem 1rem 1rem;
                font-size:.85rem;color:var(--gray);line-height:1.75">
                <?= $a ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endforeach; ?>

    <!-- Still need help -->
    <div style="background:linear-gradient(135deg,rgba(37,99,235,.1),rgba(0,212,255,.05));
        border:1px solid rgba(37,99,235,.2);border-radius:var(--radius-lg);padding:2rem;
        text-align:center;margin-top:2rem">
        <div style="font-size:2rem;margin-bottom:.75rem">🎧</div>
        <h3 style="font-weight:800;font-size:1.1rem;margin-bottom:.5rem">Masih ada pertanyaan?</h3>
        <p style="color:var(--gray);font-size:.875rem;margin-bottom:1.25rem">
            Tim support kami siap membantu kamu
        </p>
        <div style="display:flex;gap:.75rem;justify-content:center;flex-wrap:wrap">
            <a href="https://wa.me/<?= WHATSAPP_NUMBER ?>?text=Halo+admin,+saya+punya+pertanyaan"
               target="_blank" class="btn-primary"
               style="display:inline-flex;align-items:center;gap:.5rem;padding:.875rem 1.5rem">
               💬 Chat WA Admin
            </a>
            <a href="contact.php" class="btn-outline"
               style="display:inline-flex;align-items:center;gap:.5rem;padding:.875rem 1.5rem">
               ✉️ Kirim Pesan
            </a>
        </div>
    </div>

</div>
</div>

<script>
function toggleFaq(btn) {
    const item   = btn.closest('.faq-item');
    const answer = item.querySelector('.faq-answer');
    const arrow  = btn.querySelector('.faq-arrow');
    const isOpen = answer.style.display === 'block';
    answer.style.display = isOpen ? 'none' : 'block';
    arrow.textContent    = isOpen ? '+' : '−';
    arrow.style.transform = isOpen ? 'rotate(0)' : 'rotate(45deg)';
    item.style.borderColor = isOpen ? 'var(--border)' : 'var(--blue)';
}

function filterFaq(q) {
    const query = q.toLowerCase().trim();
    let anyVisible = false;
    document.getElementById('faqClear').style.display = query ? '' : 'none';
    document.querySelectorAll('.faq-category').forEach(cat => {
        let catHas = false;
        cat.querySelectorAll('.faq-item').forEach(item => {
            const text = item.dataset.q + ' ' + item.querySelector('.faq-answer').textContent.toLowerCase();
            const show = !query || text.includes(query);
            item.style.display = show ? '' : 'none';
            if (show) catHas = anyVisible = true;
        });
        cat.style.display = catHas ? '' : 'none';
    });
    document.getElementById('noResults').style.display = (!anyVisible && query) ? 'block' : 'none';
}

function clearSearch() {
    document.getElementById('faqSearch').value = '';
    filterFaq('');
}

const gamesData = <?= json_encode(array_map(fn($g)=>['name'=>$g['name'],'slug'=>$g['slug'],'icon'=>$g['icon'],'currency'=>$g['currency']],$games)) ?>;
</script>
<?php require_once 'includes/footer.php'; ?>
