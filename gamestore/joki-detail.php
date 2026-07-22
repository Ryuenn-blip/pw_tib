<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/config.php';
require_once 'includes/joki_items_data.php';

$slug = $_GET['slug'] ?? '';
$joki = get_joki_by_slug($slug);
if (!$joki) { header('Location: joki.php'); exit; }

$first_tier = $joki['tiers'][0] ?? ['label'=>'', 'price'=>$joki['price']];
$page_title = $joki['title'];
require_once 'includes/header.php';
?>

<div class="detail-hero">
    <div class="container">
        <nav class="breadcrumb">
            <a href="index.php">Beranda</a><span>›</span>
            <a href="joki.php">Jasa Joki</a><span>›</span>
            <span><?= htmlspecialchars($joki['title']) ?></span>
        </nav>

        <div class="detail-grid">
            <!-- Left: info -->
            <div>
                <div class="detail-game-card" style="padding:0;overflow:hidden">
                    <div style="position:relative;height:150px;background:linear-gradient(135deg,rgba(37,99,235,.2),rgba(0,212,255,.08));
                        display:flex;align-items:center;justify-content:center">
                        <span style="font-size:4rem"><?= $joki['icon'] ?></span>
                    </div>
                    <div style="padding:1.125rem 1.25rem">
                        <div style="font-size:.75rem;color:var(--gray);font-weight:600;margin-bottom:.25rem"><?= htmlspecialchars($joki['game']) ?></div>
                        <div style="font-size:1.15rem;font-weight:900;margin-bottom:.75rem"><?= htmlspecialchars($joki['title']) ?></div>
                        <p style="font-size:.85rem;color:var(--gray);line-height:1.6;margin-bottom:1rem"><?= htmlspecialchars($joki['desc']) ?></p>

                        <div class="detail-badges">
                            <span class="detail-badge safe">✅ Aman</span>
                            <span class="detail-badge fast">⏱️ <?= $joki['duration'] ?></span>
                            <span class="detail-badge cheap">⭐ <?= number_format($joki['rating'],1) ?></span>
                        </div>
                    </div>
                </div>

                <div style="margin-top:1rem;display:flex;flex-direction:column;gap:.75rem">
                    <div style="background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.2);border-radius:var(--radius);padding:1rem;font-size:.82rem;color:var(--gray)">
                        ✅ <strong style="color:var(--success)">Aman &amp; Rahasia</strong> — data akun kamu tidak disimpan setelah joki selesai
                    </div>
                    <div style="background:rgba(37,99,235,.08);border:1px solid rgba(37,99,235,.2);border-radius:var(--radius);padding:1rem;font-size:.82rem;color:var(--gray)">
                        👤 <strong style="color:var(--blue-light)">Player Berpengalaman</strong> — dikerjakan tim rank tinggi, bukan bot/cheat
                    </div>
                    <div style="background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.2);border-radius:var(--radius);padding:1rem;font-size:.82rem;color:var(--gray)">
                        📊 <strong style="color:var(--warning)"><?= $joki['done'] ?>+ Order Selesai</strong> — progress bisa dipantau selama proses
                    </div>
                </div>
            </div>

            <!-- Right: order form -->
            <div>
                <div class="packages-title">🎯 Pilih Target Rank</div>
                <div class="packages-grid">
                    <?php foreach ($joki['tiers'] as $i => $tier): ?>
                    <div class="pkg-card <?= $i===0?'selected':'' ?>"
                         data-price="<?= $tier['price'] ?>"
                         data-label="<?= htmlspecialchars($tier['label']) ?>">
                        <div class="pkg-check">✓</div>
                        <div class="pkg-amount" style="font-size:.9rem"><?= htmlspecialchars($tier['label']) ?></div>
                        <div class="pkg-price"><?= formatRupiah($tier['price']) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <form class="order-form" id="jokiForm">
                    <input type="hidden" id="jokiSlug"  value="<?= $joki['slug'] ?>">
                    <input type="hidden" id="jokiTitle" value="<?= htmlspecialchars($joki['title']) ?>">
                    <input type="hidden" id="selectedLabel" value="<?= htmlspecialchars($first_tier['label']) ?>">
                    <input type="hidden" id="selectedPrice" value="<?= $first_tier['price'] ?>">

                    <div class="form-group">
                        <label class="form-label" for="gameUsername">🆔 Username / ID Login Akun *</label>
                        <input type="text" class="form-input" id="gameUsername" placeholder="Username akun game kamu" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="gamePassword">🔒 Password Akun *</label>
                        <input type="password" class="form-input" id="gamePassword" placeholder="Password akun game kamu" required>
                        <small style="color:var(--gray);font-size:.75rem;margin-top:.4rem;display:block">
                            🔐 Password hanya dipakai selama proses joki dan tidak disimpan setelahnya
                        </small>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="jokiNote">📝 Catatan Tambahan (opsional)</label>
                        <textarea class="form-input" id="jokiNote" rows="3" placeholder="Contoh: jangan main hero tertentu, jam aktif tertentu, dll"></textarea>
                    </div>

                    <div class="order-summary">
                        <div class="summary-row"><span>Layanan</span><span><?= htmlspecialchars($joki['title']) ?></span></div>
                        <div class="summary-row"><span>Target</span><span id="summaryItem"><?= htmlspecialchars($first_tier['label']) ?></span></div>
                        <div class="summary-row"><span>Estimasi</span><span><?= $joki['duration'] ?></span></div>
                        <div class="summary-row total">
                            <span>Total Bayar</span>
                            <span class="price" id="totalPrice"><?= formatRupiah($first_tier['price']) ?></span>
                        </div>
                    </div>

                    <button type="submit" class="btn-order">💳 Lanjut ke Pembayaran</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Layanan Lain -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">🚀 Jasa Joki Lainnya</h2>
            <a href="joki.php" class="section-link">Lihat Semua →</a>
        </div>
        <div class="products-grid">
        <?php
        $related = array_filter(get_joki_services(), fn($j) => $j['slug'] !== $joki['slug']);
        foreach (array_slice($related, 0, 3) as $rj):
        ?>
        <div style="background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius-lg);padding:1.125rem;cursor:pointer;transition:var(--transition)"
             onclick="location.href='joki-detail.php?slug=<?= $rj['slug'] ?>'"
             onmouseover="this.style.borderColor='var(--blue)'" onmouseout="this.style.borderColor='var(--border)'">
            <div style="display:flex;align-items:center;gap:.625rem;margin-bottom:.625rem">
                <span style="font-size:1.5rem"><?= $rj['icon'] ?></span>
                <div>
                    <div style="font-size:.7rem;color:var(--gray)"><?= htmlspecialchars($rj['game']) ?></div>
                    <div style="font-size:.85rem;font-weight:800"><?= htmlspecialchars($rj['title']) ?></div>
                </div>
            </div>
            <div style="font-size:.95rem;font-weight:900;color:var(--cyan)"><?= formatRupiah($rj['price']) ?></div>
        </div>
        <?php endforeach; ?>
        </div>
    </div>
</section>

<script>
const WA_NUMBER = '<?= WHATSAPP_NUMBER ?>';
const gamesData = <?= json_encode(array_map(fn($g)=>['name'=>$g['name'],'slug'=>$g['slug'],'icon'=>$g['icon'],'currency'=>$g['currency']],$games)) ?>;

document.querySelectorAll('.pkg-card').forEach(card => {
    card.addEventListener('click', function() {
        document.querySelectorAll('.pkg-card').forEach(c => c.classList.remove('selected'));
        this.classList.add('selected');
        const price = this.dataset.price;
        const label = this.dataset.label;
        document.getElementById('summaryItem').textContent  = label;
        document.getElementById('totalPrice').textContent   = 'Rp ' + Number(price).toLocaleString('id-ID');
        document.getElementById('selectedLabel').value      = label;
        document.getElementById('selectedPrice').value      = price;
    });
});

document.getElementById('jokiForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const uname = document.getElementById('gameUsername').value.trim();
    const pass  = document.getElementById('gamePassword').value.trim();
    if (!uname) { showToast('⚠️ Masukkan username akun!', 'error'); return; }
    if (!pass)  { showToast('⚠️ Masukkan password akun!', 'error'); return; }

    // Kirim via POST (bukan query string di URL) supaya password akun
    // tidak tercatat di history browser / log server.
    const fields = {
        type:  'joki',
        slug:  '<?= $joki['slug'] ?>',
        title: '<?= htmlspecialchars($joki['title']) ?>',
        game:  '<?= htmlspecialchars($joki['game']) ?>',
        icon:  '<?= $joki['icon'] ?>',
        label: document.getElementById('selectedLabel').value,
        price: document.getElementById('selectedPrice').value,
        uname, pass,
        note:  document.getElementById('jokiNote').value.trim(),
    };
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'pembayaran.php';
    for (const [k,v] of Object.entries(fields)) {
        const inp = document.createElement('input');
        inp.type = 'hidden'; inp.name = k; inp.value = v;
        form.appendChild(inp);
    }
    document.body.appendChild(form);
    form.submit();
});
</script>
<?php require_once 'includes/footer.php'; ?>
