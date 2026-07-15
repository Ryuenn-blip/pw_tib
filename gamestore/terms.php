<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/config.php';
$page_title = 'Syarat & Ketentuan';
require_once 'includes/header.php';
?>

<div style="padding:100px 0 4rem">
    <div class="container" style="max-width:800px">

        <div style="text-align:center;margin-bottom:3rem">
            <div style="display:inline-flex;align-items:center;gap:.5rem;background:rgba(37,99,235,.1);
                border:1px solid rgba(37,99,235,.25);color:var(--cyan);
                padding:.35rem 1rem;border-radius:100px;font-size:.8rem;font-weight:600;margin-bottom:1rem">
                📜 Legal
            </div>
            <h1 style="font-size:2rem;font-weight:900;margin-bottom:.5rem">Syarat & Ketentuan</h1>
            <p style="color:var(--gray);font-size:.875rem">Terakhir diperbarui: <?= date('d F Y') ?></p>
        </div>

        <!-- TOC -->
        <div style="background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius-lg);
            padding:1.25rem 1.5rem;margin-bottom:2.5rem">
            <div style="font-weight:700;font-size:.875rem;margin-bottom:.875rem;color:var(--gray2);
                text-transform:uppercase;letter-spacing:.5px">📋 Daftar Isi</div>
            <div style="display:flex;flex-direction:column;gap:.4rem">
                <?php
                $sections = [
                    '1' => 'Ketentuan Umum',
                    '2' => 'Layanan GameStore',
                    '3' => 'Proses Pemesanan & Pembayaran',
                    '4' => 'Hak & Kewajiban Pengguna',
                    '5' => 'Kebijakan Refund',
                    '6' => 'Keamanan & Privasi',
                    '7' => 'Larangan Penggunaan',
                    '8' => 'Penyelesaian Sengketa',
                    '9' => 'Perubahan Ketentuan',
                ];
                foreach ($sections as $num => $title): ?>
                <a href="#section-<?= $num ?>"
                   style="font-size:.85rem;color:var(--gray);display:flex;gap:.625rem;
                       align-items:center;padding:.25rem 0;transition:.2s"
                   onmouseover="this.style.color='var(--cyan)'" onmouseout="this.style.color='var(--gray)'">
                    <span style="color:var(--blue);font-weight:700;font-size:.75rem;width:20px"><?= $num ?>.</span>
                    <?= $title ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>

        <?php
        $content = [
            '1' => ['Ketentuan Umum', '📌', "Dengan mengakses dan menggunakan layanan GameStore (gamestore.id), Anda dianggap telah membaca, memahami, dan menyetujui semua Syarat & Ketentuan yang berlaku. Jika Anda tidak setuju dengan ketentuan ini, mohon untuk tidak menggunakan layanan kami.

GameStore adalah platform jual beli item game digital yang dioperasikan secara resmi di Indonesia. Layanan kami mencakup penjualan diamond, UC, VP, crystal, dan berbagai mata uang in-game lainnya.", [
                '• Pengguna harus berusia minimal 13 tahun atau mendapat izin orang tua/wali.',
                '• Pengguna bertanggung jawab atas keamanan data akun yang digunakan.',
                '• GameStore berhak menolak atau membatalkan order jika ditemukan indikasi penipuan.',
                '• Layanan GameStore tunduk pada hukum yang berlaku di Republik Indonesia.',
            ]],
            '2' => ['Layanan GameStore', '🎮', "GameStore menyediakan layanan top up item game digital secara online. Semua transaksi diproses oleh admin kami secara manual untuk memastikan keamanan dan keakuratan setiap order.", [
                '• Layanan tersedia 24 jam / 7 hari / 365 hari termasuk hari libur nasional.',
                '• Harga dapat berubah sewaktu-waktu tanpa pemberitahuan sebelumnya.',
                '• GameStore tidak menjamin ketersediaan semua item jika terjadi gangguan dari pihak developer game.',
                '• Kami berhak menambah, mengubah, atau menghentikan layanan tertentu kapan saja.',
            ]],
            '3' => ['Proses Pemesanan & Pembayaran', '💳', "Setiap pemesanan dilakukan melalui WhatsApp atau Live Chat dengan admin GameStore. Pembayaran harus dilakukan sesuai nominal yang tertera dan dikonfirmasi kepada admin.", [
                '• Order dianggap valid setelah pembayaran dikonfirmasi oleh admin.',
                '• User ID yang dimasukkan sepenuhnya merupakan tanggung jawab pengguna.',
                '• GameStore tidak bertanggung jawab atas top up yang salah akibat User ID yang keliru.',
                '• Bukti pembayaran harus disimpan hingga item diterima di akun game.',
                '• Pembayaran harus dilakukan dalam 30 menit setelah order dikonfirmasi, lewat dari itu order otomatis dibatalkan.',
            ]],
            '4' => ['Hak & Kewajiban Pengguna', '👤', "Pengguna memiliki hak untuk mendapatkan layanan sesuai yang dijanjikan dan berhak mengajukan komplain jika terjadi masalah. Di sisi lain, pengguna juga memiliki kewajiban yang harus dipenuhi.", [
                '• Wajib memberikan informasi yang benar dan akurat saat melakukan order.',
                '• Wajib melakukan pembayaran sesuai nominal yang disepakati.',
                '• Berhak mendapat pelayanan ramah dan profesional dari admin kami.',
                '• Berhak mengajukan refund sesuai ketentuan kebijakan refund yang berlaku.',
                '• Dilarang menggunakan layanan untuk tujuan yang melanggar hukum.',
            ]],
            '5' => ['Kebijakan Refund', '💰', "GameStore memberikan jaminan refund dalam kondisi tertentu. Kami berkomitmen untuk menyelesaikan setiap masalah dengan adil dan transparan.", [
                '• Refund 100% diberikan jika item tidak masuk dalam 24 jam setelah konfirmasi pembayaran.',
                '• Refund tidak berlaku jika kesalahan disebabkan oleh User ID yang salah dari pengguna.',
                '• Proses refund memakan waktu 1x24 jam ke metode pembayaran asal.',
                '• Refund hanya dapat diproses dengan bukti pembayaran yang valid.',
                '• Double order (beli 2x paket yang sama) tidak dapat di-refund kecuali terbukti kesalahan sistem.',
            ]],
            '6' => ['Keamanan & Privasi', '🔒', "GameStore berkomitmen menjaga keamanan dan privasi data pengguna. Data yang Anda berikan hanya digunakan untuk keperluan proses transaksi.", [
                '• Data pribadi (nama, nomor WA) tidak akan dijual atau dibagikan ke pihak ketiga.',
                '• GameStore TIDAK PERNAH meminta password akun game Anda.',
                '• Jika ada pihak yang mengaku sebagai admin GameStore dan meminta password, segera laporkan.',
                '• Transaksi dilakukan melalui WhatsApp resmi yang tertera di website.',
            ]],
            '7' => ['Larangan Penggunaan', '🚫', "Pengguna dilarang keras melakukan hal-hal berikut yang dapat merugikan GameStore maupun pengguna lain:", [
                '• Menggunakan layanan untuk aktivitas penipuan atau ilegal.',
                '• Memberikan User ID palsu atau milik orang lain tanpa izin.',
                '• Melakukan chargeback (penarikan pembayaran paksa) setelah item diterima.',
                '• Mencoba meretas atau mengganggu sistem GameStore.',
                '• Menyebarkan informasi palsu atau menyesatkan tentang GameStore.',
            ]],
            '8' => ['Penyelesaian Sengketa', '⚖️', "Jika terjadi perselisihan antara pengguna dan GameStore, penyelesaian akan dilakukan melalui musyawarah untuk mufakat terlebih dahulu.", [
                '• Komplain dapat diajukan melalui Live Chat, WhatsApp, atau email admin@gamestore.id.',
                '• Admin akan merespons komplain dalam 1x24 jam.',
                '• Jika tidak ada kesepakatan, sengketa akan diselesaikan sesuai hukum yang berlaku di Indonesia.',
                '• GameStore berkomitmen untuk menyelesaikan setiap sengketa secara adil dan transparan.',
            ]],
            '9' => ['Perubahan Ketentuan', '🔄', "GameStore berhak mengubah Syarat & Ketentuan ini kapan saja tanpa pemberitahuan sebelumnya. Perubahan akan efektif segera setelah dipublikasikan di halaman ini.", [
                '• Pengguna disarankan untuk memeriksa halaman ini secara berkala.',
                '• Penggunaan layanan setelah perubahan dianggap sebagai persetujuan terhadap ketentuan baru.',
                '• Untuk pertanyaan tentang Syarat & Ketentuan, silakan hubungi admin kami.',
            ]],
        ];
        foreach ($content as $num => [$title, $icon, $desc, $points]):
        ?>
        <div id="section-<?= $num ?>"
             style="background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius-lg);
                 padding:1.75rem;margin-bottom:1rem">
            <h2 style="font-size:1.05rem;font-weight:800;margin-bottom:1rem;
                display:flex;align-items:center;gap:.625rem">
                <span style="width:32px;height:32px;border-radius:8px;background:rgba(37,99,235,.15);
                    display:flex;align-items:center;justify-content:center;font-size:.95rem;flex-shrink:0"><?= $icon ?></span>
                <?= $num ?>. <?= $title ?>
            </h2>
            <p style="color:var(--gray);font-size:.875rem;line-height:1.8;margin-bottom:1rem;
                white-space:pre-line"><?= htmlspecialchars($desc) ?></p>
            <ul style="display:flex;flex-direction:column;gap:.5rem">
                <?php foreach ($points as $pt): ?>
                <li style="font-size:.85rem;color:var(--gray);display:flex;gap:.625rem;line-height:1.6">
                    <span style="color:var(--blue);flex-shrink:0;margin-top:.1rem">→</span>
                    <?= htmlspecialchars(ltrim($pt, '• ')) ?>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endforeach; ?>

        <!-- Footer note -->
        <div style="margin-top:2rem;padding:1.25rem;background:rgba(37,99,235,.06);
            border:1px solid rgba(37,99,235,.2);border-radius:var(--radius-lg);
            text-align:center;font-size:.82rem;color:var(--gray)">
            Dengan menggunakan layanan GameStore, Anda telah menyetujui seluruh Syarat & Ketentuan di atas.<br>
            Ada pertanyaan? <a href="contact.php" style="color:var(--cyan);font-weight:600">Hubungi Admin Kami →</a>
        </div>
    </div>
</div>

<script>
const gamesData = <?= json_encode(array_map(fn($g) => ['name'=>$g['name'],'slug'=>$g['slug'],'icon'=>$g['icon'],'currency'=>$g['currency']], $games)) ?>;
// Smooth scroll for TOC links
document.querySelectorAll('a[href^="#section-"]').forEach(a => {
    a.addEventListener('click', e => {
        e.preventDefault();
        const el = document.querySelector(a.getAttribute('href'));
        if (el) el.scrollIntoView({ behavior:'smooth', block:'start' });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
