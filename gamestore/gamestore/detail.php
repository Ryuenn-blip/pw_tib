<?php
require_once 'includes/config.php';

$slug = $_GET['slug'] ?? '';
if (!$slug) { header('Location: products.php'); exit; }

// Load produk + paket dari DB
$game = get_product_by_slug($slug);
if (!$game) { header('Location: products.php'); exit; }

$first_pkg = !empty($game['packages']) ? $game['packages'][0] : ['id'=>0,'amount'=>0,'price'=>0,'bonus'=>0];
$page_title = 'Top Up ' . $game['name'];
require_once 'includes/header.php';
?>

<div class="detail-hero">
    <div class="container">
        <nav class="breadcrumb">
            <a href="index.php">Beranda</a><span>›</span>
            <a href="products.php">Produk</a><span>›</span>
            <span><?= htmlspecialchars($game['name']) ?></span>
        </nav>

        <div class="detail-grid">
            <!-- Left: Game Card -->
            <div>
                <div class="detail-game-card" style="padding:0;overflow:hidden">
                    <div style="position:relative;height:180px;overflow:hidden">
                        <?php if (!empty($game['img_banner'])): ?>
                        <img src="<?= htmlspecialchars($game['img_banner']) ?>"
                             alt="<?= htmlspecialchars($game['name']) ?>"
                             style="width:100%;height:100%;object-fit:cover;object-position:center top;display:block"
                             onerror="this.style.display='none'">
                        <?php endif; ?>
                        <div style="position:absolute;inset:0;background:linear-gradient(180deg,rgba(13,17,23,0) 30%,rgba(13,17,23,.95) 100%)"></div>
                        <div style="position:absolute;bottom:0;left:0;right:0;padding:1rem 1.25rem">
                            <div style="font-size:1.2rem;font-weight:900"><?= htmlspecialchars($game['name']) ?></div>
                            <div style="font-size:.75rem;color:var(--gray)"><?= htmlspecialchars($game['currency']) ?></div>
                        </div>
                    </div>
                    <div style="padding:1.125rem 1.25rem">
                        <div class="detail-badges">
                            <span class="detail-badge safe">✅ Aman</span>
                            <span class="detail-badge fast">⚡ Instan</span>
                            <span class="detail-badge cheap">💰 Termurah</span>
                        </div>
                        <div style="margin-top:1rem;padding-top:.875rem;border-top:1px solid var(--border)">
                            <div style="display:flex;justify-content:space-between;font-size:.8rem;color:var(--gray);margin-bottom:.5rem">
                                <span>Mulai dari</span>
                                <span style="color:var(--cyan);font-weight:700">
                                    <?= $first_pkg['price'] ? formatRupiah($first_pkg['price']) : '—' ?>
                                </span>
                            </div>
                            <div style="display:flex;justify-content:space-between;font-size:.8rem;color:var(--gray)">
                                <span>Kategori</span>
                                <span style="color:var(--white);font-weight:600"><?= htmlspecialchars($game['category_name'] ?? $game['category']) ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Info boxes -->
                <div style="margin-top:1rem;display:flex;flex-direction:column;gap:.75rem">
                    <div style="background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.2);border-radius:var(--radius);padding:1rem;font-size:.82rem;color:var(--gray)">
                        ✅ <strong style="color:var(--success)">100% Aman</strong> — Semua transaksi dijamin keamanannya
                    </div>
                    <div style="background:rgba(37,99,235,.08);border:1px solid rgba(37,99,235,.2);border-radius:var(--radius);padding:1rem;font-size:.82rem;color:var(--gray)">
                        ⚡ <strong style="color:var(--blue-light)">Proses Instan</strong> — Item langsung masuk ke akun game kamu
                    </div>
                    <div style="background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.2);border-radius:var(--radius);padding:1rem;font-size:.82rem;color:var(--gray)">
                        🎧 <strong style="color:var(--warning)">24/7 Support</strong> — Admin siap membantu kapan saja
                    </div>
                </div>
            </div>

            <!-- Right: Order Form -->
            <div>
                <div class="packages-title">💎 Pilih Paket <?= htmlspecialchars($game['currency']) ?></div>
                <div class="packages-grid">
                    <?php foreach ($game['packages'] as $i => $pkg): ?>
                    <div class="pkg-card <?= $i===0?'selected':'' ?>"
                         data-price="<?= $pkg['price'] ?>"
                         data-amount="<?= $pkg['amount'] ?>"
                         data-pkgid="<?= $pkg['id'] ?>"
                         data-currency="<?= htmlspecialchars($game['currency']) ?>">
                        <div class="pkg-check">✓</div>
                        <div class="pkg-amount"><?= number_format($pkg['amount']) ?></div>
                        <div class="pkg-currency"><?= htmlspecialchars($game['currency']) ?></div>
                        <?php if ($pkg['bonus'] > 0): ?>
                        <div class="pkg-bonus">+<?= $pkg['bonus'] ?> Bonus</div>
                        <?php endif; ?>
                        <div class="pkg-price"><?= formatRupiah($pkg['price']) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <form class="order-form" id="orderForm">
                    <input type="hidden" id="gameName"      value="<?= htmlspecialchars($game['name']) ?>">
                    <input type="hidden" id="gameSlug"      value="<?= $game['slug'] ?>">
                    <input type="hidden" id="gameIcon"      value="<?= htmlspecialchars($game['icon']) ?>">
                    <input type="hidden" id="selectedPkg"   value="<?= $first_pkg['amount'] ?>">
                    <input type="hidden" id="selectedPkgId" value="<?= $first_pkg['id'] ?>">
                    <input type="hidden" id="selectedPrice" value="<?= $first_pkg['price'] ?>">

                    <div class="form-group">
                        <label class="form-label" for="userId">🆔 User ID / ID Game *</label>
                        <input type="text" class="form-input" id="userId"
                               placeholder="Contoh: 123456789 (1234)" required>
                        <small style="color:var(--gray);font-size:.75rem;margin-top:.4rem;display:block">
                            Pastikan ID yang kamu masukkan sudah benar
                        </small>
                    </div>

                    <div class="order-summary">
                        <div class="summary-row"><span>Produk</span><span><?= htmlspecialchars($game['name']) ?></span></div>
                        <div class="summary-row"><span>Paket</span><span id="summaryItem"><?= $first_pkg['amount'].' '.$game['currency'] ?></span></div>
                        <div class="summary-row"><span>Harga</span><span id="summaryPrice"><?= formatRupiah($first_pkg['price']) ?></span></div>
                        <div class="summary-row total">
                            <span>Total Bayar</span>
                            <span class="price" id="totalPrice"><?= formatRupiah($first_pkg['price']) ?></span>
                        </div>
                    </div>

                    <button type="submit" class="btn-order">💳 Lanjut ke Pembayaran</button>
                    <button type="button" class="btn-outline"
                            style="width:100%;margin-top:.625rem;justify-content:center;padding:.875rem"
                            onclick="addItemToCart()">
                        🛒 Tambah ke Keranjang
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Related Games -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">🎮 Game Lainnya</h2>
            <a href="products.php" class="section-link">Lihat Semua →</a>
        </div>
        <div class="products-grid">
        <?php
        $related = array_filter($games, fn($g) => $g['slug'] !== $game['slug']);
        foreach (array_slice($related, 0, 6) as $rg):
        ?>
        <div class="product-card" onclick="location.href='detail.php?slug=<?= $rg['slug'] ?>'">
            <div class="card-glow"></div>
            <div class="card-image">
                <img src="<?= htmlspecialchars($rg['img'] ?? '') ?>" alt="<?= htmlspecialchars($rg['name']) ?>"
                     loading="lazy" onerror="this.onerror=null;this.src='assets/img/placeholder.svg'">
                <div class="card-img-overlay"></div>
            </div>
            <div class="card-body">
                <div class="card-name"><?= htmlspecialchars($rg['name']) ?></div>
                <div class="card-currency"><?= htmlspecialchars($rg['currency']) ?></div>
                <div class="card-price"><?= $rg['min_price'] ? formatRupiah((int)$rg['min_price']) : '—' ?> <small>/ mulai dari</small></div>
                <button class="btn-card">🛒 Lihat Detail</button>
            </div>
        </div>
        <?php endforeach; ?>
        </div>
    </div>
</section>

<script>
const WA_NUMBER = '<?= WHATSAPP_NUMBER ?>';
const gamesData = <?= json_encode(array_map(fn($g)=>['name'=>$g['name'],'slug'=>$g['slug'],'icon'=>$g['icon'],'currency'=>$g['currency']],$games)) ?>;

// Package selector
document.querySelectorAll('.pkg-card').forEach(card => {
    card.addEventListener('click', function() {
        document.querySelectorAll('.pkg-card').forEach(c => c.classList.remove('selected'));
        this.classList.add('selected');
        const price  = this.dataset.price;
        const amount = this.dataset.amount;
        const cur    = this.dataset.currency;
        const pkgId  = this.dataset.pkgid;
        const el = id => document.getElementById(id);
        if (el('summaryItem'))   el('summaryItem').textContent   = amount + ' ' + cur;
        if (el('summaryPrice'))  el('summaryPrice').textContent  = 'Rp ' + Number(price).toLocaleString('id-ID');
        if (el('totalPrice'))    el('totalPrice').textContent    = 'Rp ' + Number(price).toLocaleString('id-ID');
        if (el('selectedPkg'))   el('selectedPkg').value         = amount;
        if (el('selectedPkgId'))el('selectedPkgId').value        = pkgId;
        if (el('selectedPrice')) el('selectedPrice').value       = price;
    });
});

// Form submit → ke pembayaran.php
document.getElementById('orderForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const userId = document.getElementById('userId')?.value.trim();
    const pkg    = document.getElementById('selectedPkg')?.value;
    const pkgId  = document.getElementById('selectedPkgId')?.value;
    const price  = document.getElementById('selectedPrice')?.value;
    if (!userId) { showToast('⚠️ Masukkan User ID kamu!','error'); document.getElementById('userId').focus(); return; }
    if (!pkg)    { showToast('⚠️ Pilih paket terlebih dahulu!','error'); return; }
    const params = new URLSearchParams({
        game:  '<?= htmlspecialchars($game['name']) ?>',
        slug:  '<?= $game['slug'] ?>',
        cur:   '<?= htmlspecialchars($game['currency']) ?>',
        pkg, pkgId, price, uid: userId,
        icon: '<?= $game['icon'] ?>',
    });
    window.location.href = 'pembayaran.php?' + params.toString();
});

function addItemToCart() {
    const pkg   = document.getElementById('selectedPkg')?.value;
    const price = document.getElementById('selectedPrice')?.value;
    const uid   = document.getElementById('userId')?.value.trim();
    if (!pkg)  { showToast('⚠️ Pilih paket dulu!','error'); return; }
    if (!uid)  { showToast('⚠️ Masukkan User ID dulu!','error'); document.getElementById('userId').focus(); return; }
    addToCart({ game:'<?= htmlspecialchars($game['name']) ?>', pkg, currency:'<?= htmlspecialchars($game['currency']) ?>',
                price:Number(price), userId:uid, icon:'<?= $game['icon'] ?>', slug:'<?= $game['slug'] ?>' });
}
</script>
<?php require_once 'includes/footer.php'; ?>
