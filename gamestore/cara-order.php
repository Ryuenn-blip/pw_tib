<?php
require_once 'includes/config.php';
$page_title = 'Cara Order';
require_once 'includes/header.php';
?>

<div class="howto-page">
    <div class="container" style="max-width:780px">
        <div style="text-align:center; margin-bottom:3rem">
            <h1 style="font-size:2rem; font-weight:900; margin-bottom:.5rem">⚡ Cara Order</h1>
            <p style="color:var(--gray)">Ikuti langkah-langkah berikut untuk melakukan top up dengan mudah dan aman</p>
        </div>

        <div class="howto-step">
            <div class="howto-num">01</div>
            <div>
                <div class="howto-title">🎮 Pilih Game & Paket</div>
                <div class="howto-desc">
                    Kunjungi halaman <a href="products.php" style="color:var(--cyan)">Semua Produk</a> dan pilih game yang ingin kamu top up.
                    Klik "Lihat Detail" kemudian pilih paket yang sesuai dengan kebutuhanmu.
                    Perhatikan harga dan bonus yang tersedia untuk mendapatkan nilai terbaik.
                </div>
            </div>
        </div>

        <div class="howto-step">
            <div class="howto-num">02</div>
            <div>
                <div class="howto-title">🆔 Masukkan User ID</div>
                <div class="howto-desc">
                    Masukkan User ID / ID karakter game kamu dengan benar dan teliti.
                    Untuk Mobile Legends: buka profil → salin ID (format: 123456789 (1234)).
                    Untuk Free Fire: buka profil → salin UID.
                    Pastikan ID yang dimasukkan sudah benar sebelum melanjutkan.
                </div>
            </div>
        </div>

        <div class="howto-step">
            <div class="howto-num">03</div>
            <div>
                <div class="howto-title">💬 Konfirmasi via WhatsApp</div>
                <div class="howto-desc">
                    Setelah memilih paket dan memasukkan ID, klik tombol "Order via WhatsApp".
                    Kamu akan diarahkan ke WhatsApp Admin dengan detail order yang sudah terisi otomatis.
                    Kirim pesan tersebut dan tunggu konfirmasi dari admin kami.
                </div>
            </div>
        </div>

        <div class="howto-step">
            <div class="howto-num">04</div>
            <div>
                <div class="howto-title">💳 Lakukan Pembayaran</div>
                <div class="howto-desc">
                    Admin akan memberikan nomor rekening / e-wallet untuk pembayaran.
                    Tersedia: DANA, OVO, GoPay, ShopeePay, Transfer BCA/Mandiri/BRI, dan QRIS.
                    Lakukan pembayaran sesuai nominal yang tertera dan kirimkan bukti transfer ke admin.
                </div>
            </div>
        </div>

        <div class="howto-step">
            <div class="howto-num">05</div>
            <div>
                <div class="howto-title">✅ Item Diterima!</div>
                <div class="howto-desc">
                    Setelah pembayaran dikonfirmasi, proses top up akan langsung dijalankan.
                    Item akan masuk ke akun game kamu dalam hitungan menit (biasanya 1-5 menit).
                    Kamu akan mendapatkan notifikasi konfirmasi dari admin setelah proses selesai.
                </div>
            </div>
        </div>

        <div style="background:rgba(37,99,235,.08); border:1px solid rgba(37,99,235,.2);
                    border-radius:var(--radius-lg); padding:1.75rem; margin-top:2rem">
            <h3 style="font-weight:700; margin-bottom:1rem">⚠️ Tips Penting</h3>
            <ul style="display:flex; flex-direction:column; gap:.625rem">
                <?php foreach ([
                    'Selalu pastikan User ID yang kamu masukkan sudah benar sebelum melakukan pembayaran',
                    'Simpan bukti pembayaran hingga item diterima di akun game kamu',
                    'Jika ada kendala, hubungi admin kami yang siap membantu 24/7',
                    'Hati-hati penipuan! GameStore tidak pernah meminta kata sandi akun game kamu',
                    'Transaksi yang sudah dibayar tidak dapat dibatalkan, pastikan pilihan sudah sesuai'
                ] as $tip): ?>
                <li style="font-size:.875rem; color:var(--gray); display:flex; gap:.625rem">
                    <span style="color:var(--cyan); flex-shrink:0">→</span>
                    <?= htmlspecialchars($tip) ?>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div style="text-align:center; margin-top:2.5rem">
            <p style="color:var(--gray); margin-bottom:1rem">Masih ada pertanyaan?</p>
            <a href="https://wa.me/<?= WHATSAPP_NUMBER ?>" target="_blank" class="btn-primary"
               style="display:inline-flex">
                💬 Chat Admin Sekarang
            </a>
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
