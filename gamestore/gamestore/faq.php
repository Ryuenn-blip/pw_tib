<?php
require_once 'includes/config.php';
$page_title = 'FAQ - Pertanyaan Umum';
require_once 'includes/header.php';

$faqs = [
    'Tentang GameStore' => [
        ['q' => 'Apa itu GameStore?',
         'a' => 'GameStore adalah platform top up game online terpercaya di Indonesia. Kami menyediakan layanan top up diamond, UC, VP, dan berbagai mata uang game lainnya dengan harga terbaik dan proses instan 24 jam.'],
        ['q' => 'Apakah GameStore aman dan terpercaya?',
         'a' => 'Ya, GameStore 100% aman dan terpercaya. Kami telah melayani lebih dari 50.000+ transaksi sejak 2020. Setiap transaksi dijamin keamanannya dan kami tidak pernah meminta data sensitif seperti password akun game kamu.'],
        ['q' => 'Bagaimana cara menghubungi admin GameStore?',
         'a' => 'Kamu bisa menghubungi admin kami melalui fitur Live Chat di pojok kanan bawah website, atau langsung ke WhatsApp di nomor 0812-3456-7890. Admin kami aktif 24 jam / 7 hari termasuk hari libur.'],
    ],
    'Proses Order & Pembayaran' => [
        ['q' => 'Bagaimana cara melakukan top up?',
         'a' => 'Caranya mudah: (1) Pilih game yang ingin di-top up, (2) Pilih paket yang diinginkan, (3) Masukkan User ID game kamu, (4) Klik "Order via WhatsApp", (5) Lakukan pembayaran, (6) Item otomatis masuk ke akun kamu.'],
        ['q' => 'Metode pembayaran apa saja yang tersedia?',
         'a' => 'Kami menerima berbagai metode pembayaran: DANA, OVO, GoPay, ShopeePay, Transfer BCA, Transfer Mandiri, Transfer BRI, dan QRIS. Semua transaksi diproses secara manual oleh admin kami.'],
        ['q' => 'Berapa lama proses top up setelah pembayaran?',
         'a' => 'Proses top up biasanya memakan waktu 1-5 menit setelah pembayaran dikonfirmasi oleh admin. Pada jam sibuk (malam hari) mungkin sedikit lebih lama, namun tidak lebih dari 30 menit.'],
        ['q' => 'Apakah ada minimum order?',
         'a' => 'Tidak ada minimum order! Kamu bisa top up dari paket terkecil yang tersedia, mulai dari Rp 11.000 untuk Free Fire Diamond.'],
        ['q' => 'Bagaimana jika pembayaran sudah tapi item belum masuk?',
         'a' => 'Jika dalam 30 menit item belum masuk setelah konfirmasi pembayaran, segera hubungi admin kami via Live Chat atau WhatsApp dengan menyertakan bukti pembayaran dan ID order kamu. Kami akan segera menanganinya.'],
    ],
    'User ID & Akun Game' => [
        ['q' => 'Dimana saya menemukan User ID Mobile Legends?',
         'a' => 'Buka game Mobile Legends → Klik foto profil kamu → User ID tertera di bawah nama karakter dengan format angka (contoh: 123456789 (1234)). Salin kedua angka tersebut ya!'],
        ['q' => 'Dimana menemukan UID Free Fire?',
         'a' => 'Buka Free Fire → Klik ikon profil di pojok kiri atas → UID tertera di bawah nama akun kamu. Contoh: 123456789.'],
        ['q' => 'Bagaimana jika saya salah input User ID?',
         'a' => 'Jika User ID yang dimasukkan salah, top up akan masuk ke akun orang lain dan kami tidak dapat memproses refund. Pastikan selalu memeriksa kembali User ID sebelum konfirmasi order ke admin.'],
    ],
    'Refund & Garansi' => [
        ['q' => 'Apakah ada garansi jika item tidak masuk?',
         'a' => 'Ya! Kami memberikan garansi 100% uang kembali jika item tidak masuk dalam 24 jam setelah pembayaran dikonfirmasi, dengan catatan User ID yang dimasukkan sudah benar.'],
        ['q' => 'Bagaimana proses refund?',
         'a' => 'Hubungi admin dengan menyertakan: ID order, bukti pembayaran, dan screenshot User ID game kamu. Tim kami akan memverifikasi dan memproses refund ke metode pembayaran asal dalam 1x24 jam.'],
        ['q' => 'Apakah bisa cancel order?',
         'a' => 'Order yang sudah dibayar tidak bisa dibatalkan karena proses top up dilakukan segera setelah pembayaran dikonfirmasi. Pastikan pilihan paket dan User ID sudah benar sebelum melakukan pembayaran.'],
    ],
];
?>

<div style="padding:100px 0 4rem">
    <div class="container" style="max-width:860px">

        <!-- Header -->
        <div style="text-align:center;margin-bottom:3rem">
            <div style="display:inline-flex;align-items:center;gap:.5rem;background:rgba(37,99,235,.1);
                border:1px solid rgba(37,99,235,.25);color:var(--cyan);
                padding:.35rem 1rem;border-radius:100px;font-size:.8rem;font-weight:600;margin-bottom:1rem">
                ❓ Pusat Bantuan
            </div>
            <h1 style="font-size:2rem;font-weight:900;margin-bottom:.5rem">Pertanyaan yang Sering Ditanyakan</h1>
            <p style="color:var(--gray)">Temukan jawaban atas pertanyaan umum seputar layanan GameStore</p>
        </div>

        <!-- Search FAQ -->
        <div style="position:relative;margin-bottom:2.5rem">
            <input type="text" id="faqSearch" placeholder="🔍  Cari pertanyaan..."
                style="width:100%;background:var(--bg2);border:1.5px solid var(--border);
                    border-radius:var(--radius-lg);padding:1rem 1.25rem 1rem 3rem;
                    color:var(--white);font-size:.95rem;outline:none;font-family:inherit;
                    transition:.2s"
                oninput="filterFAQ(this.value)">
            <span style="position:absolute;left:1rem;top:50%;transform:translateY(-50%);font-size:1.1rem">🔍</span>
        </div>

        <!-- FAQ Accordion -->
        <div id="faqContainer">
            <?php foreach ($faqs as $category => $items): ?>
            <div class="faq-section" style="margin-bottom:2rem" data-category="<?= htmlspecialchars($category) ?>">
                <h2 style="font-size:1rem;font-weight:800;color:var(--cyan);text-transform:uppercase;
                    letter-spacing:.5px;margin-bottom:1rem;display:flex;align-items:center;gap:.5rem">
                    <span style="width:3px;height:18px;background:var(--cyan);border-radius:2px;display:block"></span>
                    <?= htmlspecialchars($category) ?>
                </h2>
                <?php foreach ($items as $idx => $faq): ?>
                <div class="faq-item" style="background:var(--bg2);border:1px solid var(--border);
                    border-radius:var(--radius-lg);margin-bottom:.5rem;overflow:hidden;
                    transition:.25s" data-q="<?= htmlspecialchars(strtolower($faq['q'])) ?>" data-a="<?= htmlspecialchars(strtolower($faq['a'])) ?>">
                    <button onclick="toggleFAQ(this)"
                        style="width:100%;display:flex;justify-content:space-between;align-items:center;
                            padding:1.125rem 1.25rem;background:none;border:none;
                            color:var(--white);font-size:.9rem;font-weight:600;cursor:pointer;
                            text-align:left;gap:1rem;font-family:inherit">
                        <span><?= htmlspecialchars($faq['q']) ?></span>
                        <span class="faq-arrow" style="font-size:1rem;transition:transform .25s;flex-shrink:0">▾</span>
                    </button>
                    <div class="faq-answer" style="max-height:0;overflow:hidden;transition:max-height .35s ease">
                        <div style="padding:0 1.25rem 1.125rem;color:var(--gray);font-size:.875rem;line-height:1.75;
                            border-top:1px solid var(--border)">
                            <div style="padding-top:1rem"><?= htmlspecialchars($faq['a']) ?></div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- No results state -->
        <div id="faqEmpty" style="display:none;text-align:center;padding:3rem;color:var(--gray)">
            <div style="font-size:3rem;margin-bottom:1rem">🔍</div>
            <p style="font-size:1rem;font-weight:600;color:var(--white);margin-bottom:.5rem">Tidak ada hasil</p>
            <p>Coba kata kunci lain, atau hubungi admin kami langsung.</p>
        </div>

        <!-- CTA -->
        <div style="margin-top:3rem;background:linear-gradient(135deg,rgba(37,99,235,.12),rgba(0,212,255,.06));
            border:1px solid rgba(37,99,235,.25);border-radius:var(--radius-lg);
            padding:2rem;text-align:center">
            <div style="font-size:1.5rem;margin-bottom:.75rem">🤔</div>
            <h3 style="font-weight:800;margin-bottom:.5rem">Tidak menemukan jawaban?</h3>
            <p style="color:var(--gray);font-size:.875rem;margin-bottom:1.25rem">
                Tim admin kami siap membantu kamu 24 jam sehari, 7 hari seminggu.
            </p>
            <div style="display:flex;gap:.75rem;justify-content:center;flex-wrap:wrap">
                <a href="contact.php" class="btn-primary"
                   style="display:inline-flex;align-items:center;gap:.5rem;padding:.75rem 1.5rem">
                    💬 Chat dengan Admin
                </a>
                <a href="https://wa.me/<?= WHATSAPP_NUMBER ?>" target="_blank" class="btn-outline"
                   style="display:inline-flex;align-items:center;gap:.5rem;padding:.75rem 1.5rem">
                    📱 WhatsApp Langsung
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function toggleFAQ(btn) {
    const item   = btn.closest('.faq-item');
    const answer = item.querySelector('.faq-answer');
    const arrow  = btn.querySelector('.faq-arrow');
    const isOpen = answer.style.maxHeight !== '0px' && answer.style.maxHeight !== '';

    // Close all others
    document.querySelectorAll('.faq-answer').forEach(a => { a.style.maxHeight = '0px'; });
    document.querySelectorAll('.faq-arrow').forEach(a => { a.style.transform = ''; });
    document.querySelectorAll('.faq-item').forEach(i => { i.style.borderColor = 'var(--border)'; });

    if (!isOpen) {
        answer.style.maxHeight = answer.scrollHeight + 'px';
        arrow.style.transform  = 'rotate(180deg)';
        item.style.borderColor = 'var(--blue)';
        item.scrollIntoView({ behavior:'smooth', block:'nearest' });
    }
}

function filterFAQ(q) {
    const query   = q.toLowerCase().trim();
    let   visible = 0;
    document.querySelectorAll('.faq-item').forEach(item => {
        const match = !query || item.dataset.q.includes(query) || item.dataset.a.includes(query);
        item.style.display = match ? '' : 'none';
        if (match) visible++;
    });
    document.querySelectorAll('.faq-section').forEach(sec => {
        const shown = [...sec.querySelectorAll('.faq-item')].some(i => i.style.display !== 'none');
        sec.style.display = shown ? '' : 'none';
    });
    document.getElementById('faqEmpty').style.display = visible === 0 ? 'block' : 'none';
}

// Open first item by default
const first = document.querySelector('.faq-item button');
if (first) toggleFAQ(first);

const gamesData = <?= json_encode(array_map(fn($g) => ['name'=>$g['name'],'slug'=>$g['slug'],'icon'=>$g['icon'],'currency'=>$g['currency']], $games)) ?>;
</script>

<?php require_once 'includes/footer.php'; ?>
