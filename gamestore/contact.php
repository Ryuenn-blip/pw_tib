<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$page_title = 'Hubungi Kami';

$wa_number = WHATSAPP_NUMBER;
$sent = false;

// Handle form submit — kirim ke WA
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama    = trim($_POST['nama']    ?? '');
    $topik   = trim($_POST['topik']   ?? '');
    $pesan   = trim($_POST['pesan']   ?? '');
    if ($nama && $pesan) {
        $wa_msg = "Halo Admin GameStore! 👋\n\n"
                . "Nama  : $nama\n"
                . "Topik : $topik\n"
                . "Pesan :\n$pesan\n\n"
                . "Terima kasih 🙏";
        $wa_url = 'https://wa.me/' . $wa_number . '?text=' . urlencode($wa_msg);
        header('Location: ' . $wa_url);
        exit;
    }
}

require_once 'includes/header.php';
?>

<style>
.contact-page{padding:100px 0 4rem;min-height:calc(100vh - 68px)}
.contact-grid{display:grid;grid-template-columns:1fr 420px;gap:2rem;max-width:1100px;margin:0 auto;padding:0 1.5rem;align-items:start}
.contact-info-card{background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius-lg);padding:1.5rem;margin-bottom:1rem;transition:var(--transition)}
.contact-info-card:hover{border-color:var(--blue);transform:translateY(-2px)}
.contact-icon{width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.4rem;flex-shrink:0}
.faq-mini{background:var(--bg3);border-radius:var(--radius);padding:.875rem 1rem;margin-bottom:.5rem;cursor:pointer}
.faq-mini-q{font-weight:700;font-size:.85rem;display:flex;justify-content:space-between;align-items:center}
.faq-mini-a{font-size:.8rem;color:var(--gray);margin-top:.5rem;line-height:1.6;display:none}
@media(max-width:768px){.contact-grid{grid-template-columns:1fr}}
</style>

<div class="contact-page">
<div class="container" style="max-width:1100px">

    <div style="text-align:center;margin-bottom:2.5rem;padding:0 1.5rem">
        <div style="display:inline-flex;align-items:center;gap:.5rem;background:rgba(37,99,235,.1);
            border:1px solid rgba(37,99,235,.25);color:var(--cyan);
            padding:.35rem 1rem;border-radius:100px;font-size:.8rem;font-weight:600;margin-bottom:1rem">
            💬 Kontak & Support
        </div>
        <h1 style="font-size:2rem;font-weight:900;margin-bottom:.5rem">Hubungi Kami</h1>
        <p style="color:var(--gray);font-size:.95rem">Siap membantu kamu 24 jam sehari, 7 hari seminggu</p>
    </div>

    <div class="contact-grid">

        <!-- LEFT: Info kontak + FAQ -->
        <div>
            <!-- Info cards -->
            <?php foreach ([
                ['💬','WhatsApp (Utama)','Chat langsung dengan admin','wa.me/'.$wa_number,'bg:rgba(37,211,102,.1)','Chat Sekarang','#25D366'],
                ['📧','Email','Untuk pertanyaan non-urgent','mailto:admin@gamestore.id','bg:rgba(37,99,235,.1)','Kirim Email','var(--blue)'],
                ['🎮','Live Chat Website','Chat langsung di website ini','#chat-widget','bg:rgba(0,212,255,.1)','Buka Chat','var(--cyan)'],
            ] as [$icon,$title,$desc,$href,$bg,$btn,$col]): ?>
            <div class="contact-info-card">
                <div style="display:flex;align-items:center;gap:.875rem;margin-bottom:.875rem">
                    <div class="contact-icon" style="background:<?= str_replace('bg:','',$bg) ?>">
                        <?= $icon ?>
                    </div>
                    <div>
                        <div style="font-weight:800;font-size:.95rem"><?= $title ?></div>
                        <div style="font-size:.78rem;color:var(--gray)"><?= $desc ?></div>
                    </div>
                </div>
                <a href="<?= $href ?>" target="<?= strpos($href,'http')===0 || strpos($href,'mailto')===0 ? '_blank' : '_self' ?>"
                   onclick="<?= $href==='#chat-widget' ? 'event.preventDefault();document.getElementById(\'chat-toggle\')?.click()' : '' ?>"
                   style="display:inline-flex;align-items:center;gap:.4rem;background:<?= $col ?>;color:#fff;
                       padding:.5rem 1.125rem;border-radius:8px;font-size:.8rem;font-weight:700;
                       text-decoration:none;transition:.2s"
                   onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
                    <?= $btn ?> →
                </a>
            </div>
            <?php endforeach; ?>

            <!-- Jam operasional -->
            <div style="background:rgba(34,197,94,.06);border:1px solid rgba(34,197,94,.2);border-radius:var(--radius-lg);padding:1.25rem;margin-bottom:1rem">
                <div style="font-weight:800;margin-bottom:.75rem;font-size:.9rem">⏰ Jam Operasional</div>
                <?php foreach ([
                    ['Senin – Jumat','08.00 – 22.00 WIB','✅ Buka'],
                    ['Sabtu – Minggu','09.00 – 21.00 WIB','✅ Buka'],
                    ['Hari Libur Nasional','10.00 – 18.00 WIB','⚠️ Terbatas'],
                ] as [$day,$time,$status]): ?>
                <div style="display:flex;justify-content:space-between;align-items:center;
                    padding:.45rem 0;border-bottom:1px solid rgba(48,54,61,.4);font-size:.82rem">
                    <span style="color:var(--gray)"><?= $day ?></span>
                    <div style="text-align:right">
                        <div style="font-weight:600"><?= $time ?></div>
                        <div style="font-size:.7rem;color:var(--success)"><?= $status ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
                <div style="font-size:.75rem;color:var(--gray);margin-top:.75rem;line-height:1.5">
                    💡 WA & Live Chat tetap bisa dikirim kapanpun, admin akan membalas pada jam operasional.
                </div>
            </div>

            <!-- FAQ mini -->
            <div style="background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius-lg);padding:1.25rem">
                <div style="font-weight:800;margin-bottom:.875rem;font-size:.9rem">❓ Pertanyaan Umum</div>
                <?php foreach ([
                    ['Berapa lama proses top up?','Proses top up biasanya instan (1-5 menit) setelah pembayaran dikonfirmasi admin.'],
                    ['Metode pembayaran apa saja?','DANA, OVO, GoPay, ShopeePay, LinkAja, Transfer BCA/Mandiri/BRI/BNI, dan QRIS.'],
                    ['Bagaimana jika top up gagal?','Hubungi admin via WA dengan screenshot bukti bayar. Dana akan dikembalikan/diproses ulang.'],
                    ['Apakah ada jaminan uang kembali?','Ya, jika top up gagal karena kesalahan kami, dana 100% dikembalikan.'],
                ] as [$q,$a]): ?>
                <div class="faq-mini" onclick="this.querySelector('.faq-mini-a').style.display=this.querySelector('.faq-mini-a').style.display==='block'?'none':'block';this.querySelector('.faq-mini-ico').textContent=this.querySelector('.faq-mini-a').style.display==='block'?'−':'+'">
                    <div class="faq-mini-q"><?= $q ?> <span class="faq-mini-ico" style="color:var(--blue);font-size:1.1rem;font-weight:900">+</span></div>
                    <div class="faq-mini-a"><?= $a ?></div>
                </div>
                <?php endforeach; ?>
                <div style="margin-top:.75rem;text-align:center;font-size:.8rem">
                    <a href="faq.php" style="color:var(--blue)">Lihat semua FAQ →</a>
                </div>
            </div>
        </div>

        <!-- RIGHT: Form kirim pesan -->
        <div>
            <div style="background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden;position:sticky;top:88px">
                <div style="background:linear-gradient(135deg,#0f1f4a,#1a3080);padding:1.25rem 1.5rem;border-bottom:1px solid rgba(255,255,255,.08)">
                    <div style="font-weight:800;font-size:.975rem">✉️ Kirim Pesan</div>
                    <div style="font-size:.78rem;color:rgba(255,255,255,.55);margin-top:.2rem">Pesan akan dikirim ke WhatsApp admin</div>
                </div>
                <div style="padding:1.5rem">
                    <form method="POST" action="contact.php" id="contactForm">
                        <div class="form-group">
                            <label class="form-label">👤 Nama *</label>
                            <input type="text" name="nama" class="form-input" placeholder="Nama kamu" required maxlength="100">
                        </div>
                        <div class="form-group">
                            <label class="form-label">📋 Topik</label>
                            <select name="topik" class="form-input">
                                <option value="Top Up & Pembayaran">Top Up & Pembayaran</option>
                                <option value="Order Bermasalah">Order Bermasalah</option>
                                <option value="Refund & Klaim">Refund & Klaim</option>
                                <option value="Saran & Masukan">Saran & Masukan</option>
                                <option value="Kerjasama">Kerjasama</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">💬 Pesan *</label>
                            <textarea name="pesan" class="form-input" rows="5" required
                                placeholder="Ceritakan masalah atau pertanyaanmu di sini..."
                                style="resize:vertical" maxlength="1000"></textarea>
                        </div>
                        <button type="submit" id="contactBtn" class="btn-primary"
                            style="width:100%;justify-content:center;padding:1rem;font-size:.95rem">
                            💬 Kirim via WhatsApp
                        </button>
                    </form>

                    <div style="display:flex;align-items:center;gap:.75rem;margin:1rem 0">
                        <div style="flex:1;height:1px;background:var(--border)"></div>
                        <span style="font-size:.72rem;color:var(--gray2)">atau langsung</span>
                        <div style="flex:1;height:1px;background:var(--border)"></div>
                    </div>

                    <a href="https://wa.me/<?= $wa_number ?>?text=Halo+admin+GameStore!+Saya+butuh+bantuan."
                       target="_blank"
                       style="display:flex;align-items:center;justify-content:center;gap:.625rem;
                           background:#25D366;color:#fff;padding:.875rem;border-radius:var(--radius);
                           font-weight:700;text-decoration:none;font-size:.9rem;transition:.2s"
                       onmouseover="this.style.opacity='.9'" onmouseout="this.style.opacity='1'">
                        <span style="font-size:1.2rem">💬</span> Chat Langsung di WhatsApp
                    </a>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;margin-top:.875rem">
                        <?php foreach ([
                            ['⚡','Respon Cepat','< 5 menit'],
                            ['🔒','Aman','Data terjaga'],
                            ['🎧','Ramah','Tim profesional'],
                            ['✅','Terpercaya','1000+ pelanggan'],
                        ] as [$ic,$t,$s]): ?>
                        <div style="background:var(--bg3);border:1px solid var(--border);border-radius:8px;
                            padding:.5rem .625rem;text-align:center;font-size:.7rem;color:var(--gray)">
                            <div style="font-size:.9rem;margin-bottom:.1rem"><?= $ic ?></div>
                            <div style="font-weight:600;color:var(--white)"><?= $t ?></div>
                            <div><?= $s ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
</div>

<script>
document.getElementById('contactForm')?.addEventListener('submit', function() {
    const btn = document.getElementById('contactBtn');
    btn.style.opacity = '.7';
    btn.style.pointerEvents = 'none';
    btn.innerHTML = '⏳ Membuka WhatsApp...';
});
const gamesData = <?= json_encode(array_map(fn($g)=>['name'=>$g['name'],'slug'=>$g['slug'],'icon'=>$g['icon'],'currency'=>$g['currency']],$games)) ?>;
</script>
<?php require_once 'includes/footer.php'; ?>
