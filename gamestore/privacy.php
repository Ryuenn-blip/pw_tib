<?php
require_once 'includes/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$page_title = 'Kebijakan Privasi';
require_once 'includes/header.php';
?>
<style>
.policy-page{padding:100px 0 4rem;min-height:calc(100vh - 68px)}
.policy-wrap{max-width:820px;margin:0 auto;padding:0 1.5rem}
.policy-section{background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius-lg);padding:1.5rem;margin-bottom:1rem;transition:var(--transition)}
.policy-section:hover{border-color:rgba(37,99,235,.3)}
.policy-section h2{font-size:1rem;font-weight:800;margin-bottom:.875rem;display:flex;align-items:center;gap:.5rem}
.policy-section p,.policy-section li{color:var(--gray);font-size:.875rem;line-height:1.8}
.policy-section ul{padding-left:1.25rem;margin-top:.5rem}
.policy-section li{margin-bottom:.3rem}
.policy-toc{background:var(--bg3);border:1px solid var(--border);border-radius:var(--radius-lg);padding:1.25rem;margin-bottom:1.5rem}
.policy-toc a{display:block;font-size:.82rem;color:var(--gray);text-decoration:none;padding:.2rem 0;transition:.2s}
.policy-toc a:hover{color:var(--cyan)}
</style>

<div class="policy-page">
<div class="policy-wrap">

    <div style="text-align:center;margin-bottom:2rem">
        <div style="display:inline-flex;align-items:center;gap:.5rem;background:rgba(37,99,235,.1);
            border:1px solid rgba(37,99,235,.25);color:var(--cyan);
            padding:.35rem 1rem;border-radius:100px;font-size:.8rem;font-weight:600;margin-bottom:1rem">
            🔒 Legal
        </div>
        <h1 style="font-size:1.75rem;font-weight:900;margin-bottom:.375rem">Kebijakan Privasi</h1>
        <p style="color:var(--gray);font-size:.82rem">Terakhir diperbarui: <?= date('d F Y') ?> | Berlaku untuk semua pengguna GameStore</p>
    </div>

    <!-- TOC -->
    <div class="policy-toc">
        <div style="font-weight:700;font-size:.82rem;color:var(--gray2);margin-bottom:.5rem;text-transform:uppercase;letter-spacing:.5px">Daftar Isi</div>
        <?php foreach ([
            '#data','1. Data yang Kami Kumpulkan',
            '#use','2. Penggunaan Data',
            '#protect','3. Perlindungan Data',
            '#share','4. Berbagi Data',
            '#cookie','5. Cookie & Pelacakan',
            '#rights','6. Hak Pengguna',
            '#minor','7. Pengguna di Bawah Umur',
            '#contact-us','8. Kontak Kami',
        ] as $i => $item):
            if ($i % 2 === 0) $anchor = $item;
            else: ?>
        <a href="<?= $anchor ?>"><?= $item ?></a>
        <?php endif; endforeach; ?>
    </div>

    <div class="policy-section" id="data">
        <h2>📊 1. Data yang Kami Kumpulkan</h2>
        <p>Kami mengumpulkan data yang diperlukan untuk menyediakan layanan top up game:</p>
        <ul>
            <li><strong style="color:var(--white)">Data Identitas:</strong> Nama, alamat email, nomor WhatsApp</li>
            <li><strong style="color:var(--white)">Data Transaksi:</strong> User ID game, paket yang dibeli, metode pembayaran, nominal transaksi</li>
            <li><strong style="color:var(--white)">Data Teknis:</strong> Alamat IP, browser, waktu akses (untuk keamanan sistem)</li>
            <li><strong style="color:var(--white)">Bukti Pembayaran:</strong> Foto/screenshot transfer yang kamu upload</li>
        </ul>
        <p style="margin-top:.75rem">Kami <strong style="color:var(--white)">tidak pernah</strong> mengumpulkan password akun game atau data kartu kredit/debit.</p>
    </div>

    <div class="policy-section" id="use">
        <h2>⚙️ 2. Penggunaan Data</h2>
        <p>Data kamu digunakan untuk:</p>
        <ul>
            <li>Memproses dan memverifikasi transaksi top up</li>
            <li>Menghubungi kamu terkait status pesanan</li>
            <li>Mengirimkan notifikasi promo dan penawaran (jika disetujui)</li>
            <li>Meningkatkan keamanan dan mencegah penipuan</li>
            <li>Memenuhi kewajiban hukum yang berlaku</li>
        </ul>
    </div>

    <div class="policy-section" id="protect">
        <h2>🛡️ 3. Perlindungan Data</h2>
        <p>Kami menerapkan langkah-langkah keamanan berikut:</p>
        <ul>
            <li>Password dienkripsi menggunakan <strong style="color:var(--white)">bcrypt</strong> — tidak ada yang bisa membaca password aslimu, termasuk admin</li>
            <li>Koneksi menggunakan HTTPS (enkripsi SSL/TLS)</li>
            <li>Akses database dibatasi hanya untuk sistem yang berwenang</li>
            <li>Bukti pembayaran disimpan di folder terproteksi, tidak dapat diakses publik</li>
            <li>Log aktivitas dipantau untuk mendeteksi akses mencurigakan</li>
        </ul>
    </div>

    <div class="policy-section" id="share">
        <h2>🤝 4. Berbagi Data</h2>
        <p>Kami <strong style="color:var(--white)">tidak menjual atau menyewakan</strong> data pribadimu kepada pihak ketiga. Data hanya dibagikan dalam kondisi:</p>
        <ul>
            <li>Diwajibkan oleh hukum atau perintah pengadilan</li>
            <li>Diperlukan untuk mencegah penipuan atau kejahatan</li>
            <li>Kamu secara eksplisit memberikan persetujuan</li>
        </ul>
    </div>

    <div class="policy-section" id="cookie">
        <h2>🍪 5. Cookie & Pelacakan</h2>
        <p>Website ini menggunakan cookie untuk:</p>
        <ul>
            <li><strong style="color:var(--white)">Session Cookie:</strong> Menjaga status login kamu (dihapus saat browser ditutup)</li>
            <li><strong style="color:var(--white)">Remember Me Cookie:</strong> Menyimpan sesi login selama 30 hari (jika kamu memilih opsi ini)</li>
        </ul>
        <p style="margin-top:.75rem">Kami tidak menggunakan cookie pelacak iklan atau analitik pihak ketiga.</p>
    </div>

    <div class="policy-section" id="rights">
        <h2>✅ 6. Hak Pengguna</h2>
        <p>Kamu berhak untuk:</p>
        <ul>
            <li><strong style="color:var(--white)">Mengakses</strong> data pribadi yang kami simpan tentang kamu</li>
            <li><strong style="color:var(--white)">Memperbarui</strong> data yang tidak akurat melalui halaman profil</li>
            <li><strong style="color:var(--white)">Menghapus</strong> akun dan data kamu (hubungi admin)</li>
            <li><strong style="color:var(--white)">Menarik persetujuan</strong> penggunaan data untuk pemasaran kapan saja</li>
        </ul>
    </div>

    <div class="policy-section" id="minor">
        <h2>👶 7. Pengguna di Bawah Umur</h2>
        <p>Layanan kami tidak ditujukan untuk anak di bawah usia 13 tahun. Jika kamu berusia di bawah 17 tahun, harap mendapatkan izin orang tua atau wali sebelum mendaftar dan melakukan transaksi.</p>
    </div>

    <div class="policy-section" id="contact-us">
        <h2>📬 8. Kontak Kami</h2>
        <p>Ada pertanyaan tentang kebijakan privasi ini? Hubungi kami:</p>
        <ul>
            <li>📱 WhatsApp: <a href="https://wa.me/<?= WHATSAPP_NUMBER ?>" target="_blank" style="color:var(--cyan)">+<?= WHATSAPP_NUMBER ?></a></li>
            <li>📧 Email: <a href="mailto:admin@gamestore.id" style="color:var(--cyan)">admin@gamestore.id</a></li>
            <li>💬 Live Chat: Tersedia di website ini</li>
        </ul>
        <p style="margin-top:.75rem">Kami akan merespons pertanyaan dalam 1x24 jam pada hari kerja.</p>
    </div>

    <div style="text-align:center;margin-top:2rem;padding:1.25rem;background:var(--bg3);border-radius:var(--radius-lg);font-size:.82rem;color:var(--gray)">
        Dengan menggunakan layanan GameStore, kamu menyetujui kebijakan privasi ini.<br>
        Lihat juga <a href="terms.php" style="color:var(--cyan)">Syarat & Ketentuan</a> kami.
    </div>

</div>
</div>
<script>
const gamesData = <?= json_encode(array_map(fn($g)=>['name'=>$g['name'],'slug'=>$g['slug'],'icon'=>$g['icon'],'currency'=>$g['currency']],$games)) ?>;
</script>
<?php require_once 'includes/footer.php'; ?>
