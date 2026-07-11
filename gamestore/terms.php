<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/config.php';
$page_title = 'Syarat & Ketentuan';
require_once 'includes/header.php';
?>
<style>
.terms-page{padding:100px 0 4rem}
.terms-wrap{max-width:820px;margin:0 auto;padding:0 1.5rem}
.terms-section{background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius-lg);padding:1.5rem;margin-bottom:1rem}
.terms-section h2{font-size:1rem;font-weight:800;margin-bottom:.875rem;display:flex;align-items:center;gap:.5rem;color:var(--white)}
.terms-section p,.terms-section li{color:var(--gray);font-size:.875rem;line-height:1.85}
.terms-section ul{padding-left:1.25rem;margin-top:.5rem}
.terms-section li{margin-bottom:.375rem}
.terms-toc{background:var(--bg3);border:1px solid var(--border);border-radius:var(--radius-lg);padding:1.25rem;margin-bottom:1.5rem}
.terms-toc a{display:block;font-size:.82rem;color:var(--gray);text-decoration:none;padding:.2rem 0;transition:.2s}
.terms-toc a:hover{color:var(--cyan)}
.highlight-box{background:rgba(37,99,235,.07);border:1px solid rgba(37,99,235,.2);border-radius:8px;padding:.875rem;margin-top:.75rem;font-size:.82rem;color:var(--gray);line-height:1.7}
</style>

<div class="terms-page">
<div class="terms-wrap">

    <div style="text-align:center;margin-bottom:2rem">
        <div style="display:inline-flex;align-items:center;gap:.5rem;background:rgba(37,99,235,.1);
            border:1px solid rgba(37,99,235,.25);color:var(--cyan);
            padding:.35rem 1rem;border-radius:100px;font-size:.8rem;font-weight:600;margin-bottom:1rem">
            📄 Legal
        </div>
        <h1 style="font-size:1.75rem;font-weight:900;margin-bottom:.375rem">Syarat & Ketentuan</h1>
        <p style="color:var(--gray);font-size:.82rem">Terakhir diperbarui: <?= date('d F Y') ?> | Wajib dibaca sebelum menggunakan layanan</p>
    </div>

    <!-- TOC -->
    <div class="terms-toc">
        <div style="font-weight:700;font-size:.82rem;color:var(--gray2);margin-bottom:.5rem;text-transform:uppercase;letter-spacing:.5px">Daftar Isi</div>
        <?php foreach ([
            ['#penerimaan','1. Penerimaan Syarat'],
            ['#layanan','2. Deskripsi Layanan'],
            ['#akun','3. Akun Pengguna'],
            ['#order','4. Order & Pembayaran'],
            ['#proses','5. Proses Top Up'],
            ['#refund','6. Kebijakan Refund'],
            ['#larangan','7. Larangan Penggunaan'],
            ['#hak','8. Hak Kekayaan Intelektual'],
            ['#batasan','9. Batasan Tanggung Jawab'],
            ['#perubahan','10. Perubahan Syarat'],
        ] as [$href,$label]): ?>
        <a href="<?= $href ?>"><?= $label ?></a>
        <?php endforeach; ?>
    </div>

    <div class="terms-section" id="penerimaan">
        <h2>✅ 1. Penerimaan Syarat</h2>
        <p>Dengan mengakses atau menggunakan layanan GameStore, kamu menyatakan telah membaca, memahami, dan menyetujui untuk terikat oleh Syarat & Ketentuan ini. Jika kamu tidak menyetujui syarat ini, mohon untuk tidak menggunakan layanan kami.</p>
        <div class="highlight-box">
            Syarat & Ketentuan ini berlaku untuk semua pengguna GameStore, termasuk pengunjung, pelanggan terdaftar, dan siapa pun yang mengakses website ini.
        </div>
    </div>

    <div class="terms-section" id="layanan">
        <h2>🎮 2. Deskripsi Layanan</h2>
        <p>GameStore adalah platform jual beli digital item game (top up) yang menyediakan:</p>
        <ul>
            <li>Top up diamond, UC, VP, dan mata uang dalam game lainnya</li>
            <li>Proses transaksi melalui konfirmasi manual admin</li>
            <li>Layanan pelanggan via WhatsApp dan Live Chat</li>
        </ul>
        <p style="margin-top:.75rem">Kami bukan afiliasi resmi dari developer game manapun. Layanan ini beroperasi sebagai reseller pihak ketiga.</p>
    </div>

    <div class="terms-section" id="akun">
        <h2>👤 3. Akun Pengguna</h2>
        <ul>
            <li>Kamu bertanggung jawab atas keamanan akun dan password GameStore milikmu</li>
            <li>Satu orang hanya boleh memiliki satu akun GameStore</li>
            <li>Informasi yang diberikan saat pendaftaran harus akurat dan terkini</li>
            <li>GameStore berhak menangguhkan atau menghapus akun yang melanggar ketentuan</li>
            <li>Minimal usia untuk mendaftar adalah 13 tahun (di bawah 17 tahun perlu persetujuan orang tua)</li>
        </ul>
    </div>

    <div class="terms-section" id="order">
        <h2>💳 4. Order & Pembayaran</h2>
        <ul>
            <li>Semua harga yang tercantum sudah termasuk pajak dan biaya layanan</li>
            <li>Pembayaran harus dilakukan sesuai nominal <strong style="color:var(--white)">tepat</strong> yang tertera</li>
            <li>Order yang sudah dikonfirmasi dan diproses <strong style="color:var(--white)">tidak dapat dibatalkan</strong></li>
            <li>Metode pembayaran yang tersedia: DANA, OVO, GoPay, ShopeePay, Transfer Bank, QRIS</li>
            <li>GameStore tidak menyimpan data kartu kredit atau rekening bank pengguna</li>
            <li>Bukti pembayaran wajib disimpan hingga transaksi selesai</li>
        </ul>
        <div class="highlight-box">
            ⚠️ GameStore tidak bertanggung jawab atas keterlambatan atau kegagalan pembayaran yang disebabkan oleh masalah di sisi penyedia layanan pembayaran.
        </div>
    </div>

    <div class="terms-section" id="proses">
        <h2>⚡ 5. Proses Top Up</h2>
        <ul>
            <li>Item akan diproses setelah pembayaran <strong style="color:var(--white)">dikonfirmasi admin</strong></li>
            <li>Waktu proses normal: <strong style="color:var(--white)">1-5 menit</strong> pada jam operasional</li>
            <li>Pengguna bertanggung jawab atas kebenaran User ID yang dimasukkan</li>
            <li>GameStore <strong style="color:var(--white)">tidak bertanggung jawab</strong> atas kesalahan User ID yang dimasukkan pengguna</li>
            <li>Item yang sudah masuk ke akun game <strong style="color:var(--white)">tidak dapat dikembalikan</strong></li>
        </ul>
    </div>

    <div class="terms-section" id="refund">
        <h2>🔄 6. Kebijakan Refund</h2>
        <p>Refund <strong style="color:var(--white)">HANYA</strong> diberikan dalam kondisi:</p>
        <ul>
            <li>Top up gagal karena kesalahan sistem GameStore (bukan kesalahan pengguna)</li>
            <li>Item tidak masuk dalam waktu 30 menit setelah pembayaran terkonfirmasi</li>
            <li>Terjadi duplikasi order akibat kesalahan sistem</li>
        </ul>
        <p style="margin-top:.75rem">Refund <strong style="color:var(--danger)">TIDAK</strong> diberikan jika:</p>
        <ul>
            <li>User ID yang dimasukkan salah</li>
            <li>Pengguna menyesal setelah order dikonfirmasi</li>
            <li>Akun game dalam kondisi banned/terkunci</li>
        </ul>
        <div class="highlight-box">
            Proses refund membutuhkan waktu 1x24 jam setelah verifikasi. Dana dikembalikan via metode pembayaran yang sama.
        </div>
    </div>

    <div class="terms-section" id="larangan">
        <h2>🚫 7. Larangan Penggunaan</h2>
        <p>Pengguna <strong style="color:var(--danger)">dilarang</strong> untuk:</p>
        <ul>
            <li>Menggunakan layanan untuk tujuan ilegal atau penipuan</li>
            <li>Melakukan chargebacks/dispute tanpa menghubungi GameStore terlebih dahulu</li>
            <li>Membuat akun palsu atau menggunakan identitas orang lain</li>
            <li>Mencoba meretas atau mengganggu sistem GameStore</li>
            <li>Menyebarkan informasi palsu tentang GameStore</li>
            <li>Memanfaatkan bug atau celah sistem untuk keuntungan tidak sah</li>
        </ul>
        <p style="margin-top:.75rem">Pelanggaran dapat mengakibatkan penangguhan akun dan/atau tuntutan hukum.</p>
    </div>

    <div class="terms-section" id="hak">
        <h2>©️ 8. Hak Kekayaan Intelektual</h2>
        <p>Seluruh konten website GameStore — termasuk teks, grafik, logo, dan desain — adalah milik GameStore dan dilindungi hak cipta. Kamu tidak diperkenankan menyalin, mendistribusikan, atau menggunakan konten ini tanpa izin tertulis.</p>
    </div>

    <div class="terms-section" id="batasan">
        <h2>⚠️ 9. Batasan Tanggung Jawab</h2>
        <p>GameStore tidak bertanggung jawab atas:</p>
        <ul>
            <li>Kerugian akibat kesalahan User ID dari pengguna</li>
            <li>Gangguan server game dari pihak developer</li>
            <li>Keterlambatan yang disebabkan force majeure (bencana alam, gangguan internet, dll)</li>
            <li>Keputusan developer game yang mempengaruhi item yang dibeli</li>
        </ul>
        <p style="margin-top:.75rem">Total tanggung jawab GameStore tidak akan melebihi nilai transaksi yang bersangkutan.</p>
    </div>

    <div class="terms-section" id="perubahan">
        <h2>📝 10. Perubahan Syarat</h2>
        <p>GameStore berhak mengubah Syarat & Ketentuan ini sewaktu-waktu. Perubahan signifikan akan diberitahukan melalui website atau WhatsApp. Dengan terus menggunakan layanan setelah perubahan, kamu dianggap menyetujui syarat yang baru.</p>
    </div>

    <div style="text-align:center;margin-top:2rem;padding:1.25rem;background:var(--bg3);border-radius:var(--radius-lg);font-size:.82rem;color:var(--gray)">
        Ada pertanyaan? <a href="contact.php" style="color:var(--cyan)">Hubungi kami</a> atau baca
        <a href="privacy.php" style="color:var(--cyan)">Kebijakan Privasi</a> kami.
    </div>

</div>
</div>

<script>
const gamesData = <?= json_encode(array_map(fn($g)=>['name'=>$g['name'],'slug'=>$g['slug'],'icon'=>$g['icon'],'currency'=>$g['currency']],$games)) ?>;
</script>
<?php require_once 'includes/footer.php'; ?>
