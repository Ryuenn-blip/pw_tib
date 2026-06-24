<?php
require_once 'includes/config.php';
$page_title = 'Chat Admin';
require_once 'includes/header.php';
?>

<div class="howto-page">
    <div class="container" style="max-width:800px">
        <div style="text-align:center; margin-bottom:3rem">
            <h1 style="font-size:2rem; font-weight:900; margin-bottom:.5rem">💬 Hubungi Admin</h1>
            <p style="color:var(--gray)">Tim kami siap membantu kamu 24 jam sehari, 7 hari seminggu</p>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:2rem">
            <?php
            $channels = [
                ['icon'=>'💬','label'=>'WhatsApp','value'=>'0812-3456-7890','desc'=>'Respon tercepat, aktif 24/7','link'=>'https://wa.me/'.WHATSAPP_NUMBER.'?text=Halo+Admin!','btn'=>'Chat WhatsApp','color'=>'#25D366'],
                ['icon'=>'✈️','label'=>'Telegram','value'=>'@gamestore_id','desc'=>'Alternatif chat & info promo','link'=>'https://t.me/gamestore_id','btn'=>'Buka Telegram','color'=>'#2AABEE'],
                ['icon'=>'📸','label'=>'Instagram','value'=>'@gamestore.id','desc'=>'Update promo dan produk baru','link'=>'https://instagram.com/gamestore.id','btn'=>'Follow IG','color'=>'#E1306C'],
                ['icon'=>'📧','label'=>'Email','value'=>'admin@gamestore.id','desc'=>'Untuk pertanyaan formal','link'=>'mailto:admin@gamestore.id','btn'=>'Kirim Email','color'=>'#2563EB'],
            ];
            foreach ($channels as $ch):
            ?>
            <div style="background:var(--bg2); border:1px solid var(--border); border-radius:var(--radius-lg); padding:1.5rem; transition:var(--transition)"
                 onmouseover="this.style.borderColor='<?= $ch['color'] ?>'" onmouseout="this.style.borderColor='var(--border)'">
                <div style="font-size:2.5rem; margin-bottom:.75rem"><?= $ch['icon'] ?></div>
                <div style="font-weight:700; margin-bottom:.25rem"><?= $ch['label'] ?></div>
                <div style="color:var(--cyan); font-weight:600; font-size:.9rem; margin-bottom:.4rem"><?= $ch['value'] ?></div>
                <div style="color:var(--gray); font-size:.8rem; margin-bottom:1rem"><?= $ch['desc'] ?></div>
                <a href="<?= $ch['link'] ?>" target="_blank"
                   style="display:inline-block; background:<?= $ch['color'] ?>22; border:1px solid <?= $ch['color'] ?>44;
                          color:<?= $ch['color'] ?>; padding:.5rem 1rem; border-radius:8px;
                          font-size:.8rem; font-weight:700; transition:var(--transition)"
                   onmouseover="this.style.background='<?= $ch['color'] ?>33'"
                   onmouseout="this.style.background='<?= $ch['color'] ?>22'">
                    <?= $ch['btn'] ?>
                </a>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Quick Message -->
        <div style="background:var(--bg2); border:1px solid var(--border); border-radius:var(--radius-lg); padding:2rem">
            <h3 style="font-weight:700; margin-bottom:1.5rem; display:flex; align-items:center; gap:.5rem">
                📝 Kirim Pesan Cepat ke WhatsApp
            </h3>
            <div style="display:flex; flex-direction:column; gap:1rem" id="quickForm">
                <div class="form-group" style="margin:0">
                    <label class="form-label">Nama Kamu</label>
                    <input type="text" class="form-input" id="qName" placeholder="Contoh: Budi">
                </div>
                <div class="form-group" style="margin:0">
                    <label class="form-label">Topik</label>
                    <select class="form-select" id="qTopic">
                        <option>Tanya Produk / Harga</option>
                        <option>Konfirmasi Pembayaran</option>
                        <option>Kendala Top Up</option>
                        <option>Kerjasama / Reseller</option>
                        <option>Lainnya</option>
                    </select>
                </div>
                <div class="form-group" style="margin:0">
                    <label class="form-label">Pesan</label>
                    <textarea class="form-input" id="qMsg" rows="4"
                              placeholder="Tulis pesanmu di sini..."
                              style="resize:vertical; min-height:100px"></textarea>
                </div>
                <button onclick="sendQuickWA()" class="btn-primary" style="align-self:flex-start">
                    💬 Kirim ke WhatsApp
                </button>
            </div>
        </div>

        <div style="background:var(--bg2); border:1px solid var(--border); border-radius:var(--radius-lg);
                    padding:1.5rem; margin-top:1rem; text-align:center">
            <div style="font-size:1.5rem; margin-bottom:.5rem">⏰</div>
            <div style="font-weight:700; margin-bottom:.25rem">Jam Operasional</div>
            <div style="color:var(--gray); font-size:.875rem">
                Admin aktif <strong style="color:var(--cyan)">24 Jam / 7 Hari</strong> termasuk hari libur nasional
            </div>
        </div>
    </div>
</div>

<script>
const gamesData = <?= json_encode(array_map(fn($g) => [
    'name' => $g['name'], 'slug' => $g['slug'],
    'icon' => $g['icon'], 'currency' => $g['currency']
], $games)) ?>;

function sendQuickWA() {
    const name  = document.getElementById('qName').value.trim() || 'Pelanggan';
    const topic = document.getElementById('qTopic').value;
    const msg   = document.getElementById('qMsg').value.trim();
    if (!msg) { alert('Mohon isi pesan terlebih dahulu!'); return; }
    const text = `Halo Admin GameStore!\n\nNama: ${name}\nTopik: ${topic}\n\nPesan:\n${msg}`;
    window.open('https://wa.me/<?= WHATSAPP_NUMBER ?>?text=' + encodeURIComponent(text), '_blank');
}
</script>

<?php require_once 'includes/footer.php'; ?>
