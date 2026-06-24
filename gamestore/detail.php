<?php
require_once 'includes/config.php';

$slug = $_GET['slug'] ?? '';
$game = null;
foreach ($games as $g) {
    if ($g['slug'] === $slug) { $game = $g; break; }
}

if (!$game) {
    header('Location: products.php');
    exit;
}

$page_title = 'Top Up ' . $game['name'];
$first_pkg  = $game['packages'][0];
require_once 'includes/header.php';
?>

<div class="detail-hero">
    <div class="container">
        <!-- Breadcrumb -->
        <nav class="breadcrumb">
            <a href="index.php">Beranda</a>
            <span>›</span>
            <a href="products.php">Produk</a>
            <span>›</span>
            <span><?= htmlspecialchars($game['name']) ?></span>
        </nav>

        <div class="detail-grid">
            <!-- Left: Game Info Card -->
            <div>
                <div class="detail-game-card">
                    <div class="detail-game-icon"
                         style="filter: drop-shadow(0 0 20px <?= $game['color'] ?>66)">
                        <?= $game['icon'] ?>
                    </div>
                    <div class="detail-game-name"><?= htmlspecialchars($game['name']) ?></div>
                    <div class="detail-game-currency">Mata uang: <?= htmlspecialchars($game['currency']) ?></div>
                    <div class="detail-badges">
                        <span class="detail-badge safe">✅ Aman</span>
                        <span class="detail-badge fast">⚡ Instan</span>
                        <span class="detail-badge cheap">💰 Termurah</span>
                    </div>
                    <div style="margin-top:1.5rem; padding-top:1rem; border-top:1px solid var(--border)">
                        <div style="display:flex; justify-content:space-between; font-size:.8rem; color:var(--gray); margin-bottom:.5rem">
                            <span>Mulai dari</span>
                            <span style="color:var(--cyan); font-weight:700">
                                <?= formatRupiah(min(array_column($game['packages'], 'price'))) ?>
                            </span>
                        </div>
                        <div style="display:flex; justify-content:space-between; font-size:.8rem; color:var(--gray)">
                            <span>Kategori</span>
                            <span style="color:var(--white); font-weight:600"><?= $game['category'] ?></span>
                        </div>
                    </div>
                </div>

                <!-- Info boxes -->
                <div style="margin-top:1rem; display:flex; flex-direction:column; gap:.75rem">
                    <div style="background:rgba(34,197,94,.08); border:1px solid rgba(34,197,94,.2); border-radius:var(--radius); padding:1rem; font-size:.82rem; color:var(--gray)">
                        ✅ <strong style="color:var(--success)">100% Aman</strong> — Semua transaksi dijamin keamanannya
                    </div>
                    <div style="background:rgba(37,99,235,.08); border:1px solid rgba(37,99,235,.2); border-radius:var(--radius); padding:1rem; font-size:.82rem; color:var(--gray)">
                        ⚡ <strong style="color:var(--blue-light)">Proses Instan</strong> — Item langsung masuk ke akun game kamu
                    </div>
                    <div style="background:rgba(245,158,11,.08); border:1px solid rgba(245,158,11,.2); border-radius:var(--radius); padding:1rem; font-size:.82rem; color:var(--gray)">
                        🎧 <strong style="color:var(--warning)">24/7 Support</strong> — Admin siap membantu kapan saja
                    </div>
                </div>
            </div>

            <!-- Right: Order Form -->
            <div>
                <!-- Package Selection -->
                <div class="packages-title">
                    💎 Pilih Paket <?= htmlspecialchars($game['currency']) ?>
                </div>
                <div class="packages-grid">
                    <?php foreach ($game['packages'] as $i => $pkg): ?>
                    <div class="pkg-card <?= $i === 0 ? 'selected' : '' ?>"
                         data-price="<?= $pkg['price'] ?>"
                         data-amount="<?= $pkg['amount'] ?>"
                         data-currency="<?= htmlspecialchars($game['currency']) ?>">
                        <div class="pkg-check">✓</div>
                        <div class="pkg-amount"><?= $pkg['amount'] ?></div>
                        <div class="pkg-currency"><?= htmlspecialchars($game['currency']) ?></div>
                        <?php if ($pkg['bonus'] > 0): ?>
                        <div class="pkg-bonus">+<?= $pkg['bonus'] ?> Bonus</div>
                        <?php endif; ?>
                        <div class="pkg-price"><?= formatRupiah($pkg['price']) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Order Form -->
                <form class="order-form" id="orderForm">
                    <input type="hidden" id="gameName" value="<?= htmlspecialchars($game['name']) ?>">
                    <input type="hidden" id="selectedPkg"   value="<?= $first_pkg['amount'] ?>">
                    <input type="hidden" id="selectedPrice" value="<?= $first_pkg['price'] ?>">

                    <div class="form-group">
                        <label class="form-label" for="userId">
                            🆔 User ID / ID Game *
                        </label>
                        <input type="text" class="form-input" id="userId"
                               placeholder="Contoh: 123456789 (1234)" required>
                        <small style="color:var(--gray); font-size:.75rem; margin-top:.4rem; display:block">
                            Pastikan ID yang kamu masukkan sudah benar
                        </small>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="paymentMethod">💳 Metode Pembayaran</label>
                        <select class="form-select" id="paymentMethod">
                            <option>DANA</option>
                            <option>OVO</option>
                            <option>GoPay</option>
                            <option>ShopeePay</option>
                            <option>Transfer BCA</option>
                            <option>Transfer Mandiri</option>
                            <option>Transfer BRI</option>
                            <option>QRIS</option>
                        </select>
                    </div>

                    <div class="order-summary">
                        <div class="summary-row">
                            <span>Produk</span>
                            <span><?= htmlspecialchars($game['name']) ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Paket</span>
                            <span id="summaryItem"><?= $first_pkg['amount'] . ' ' . $game['currency'] ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Harga</span>
                            <span id="summaryPrice"><?= formatRupiah($first_pkg['price']) ?></span>
                        </div>
                        <div class="summary-row total">
                            <span>Total Bayar</span>
                            <span class="price" id="totalPrice"><?= formatRupiah($first_pkg['price']) ?></span>
                        </div>
                    </div>

                    <button type="submit" class="btn-order">
                        💬 Order via WhatsApp
                    </button>
                    <p style="text-align:center; font-size:.75rem; color:var(--gray); margin-top:.75rem">
                        Kamu akan diarahkan ke WhatsApp Admin untuk konfirmasi order
                    </p>
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
            $related = array_slice($related, 0, 6);
            foreach ($related as $rg):
                $min_price = min(array_column($rg['packages'], 'price'));
            ?>
            <div class="product-card" onclick="location.href='detail.php?slug=<?= $rg['slug'] ?>'">
                <div class="card-glow"></div>
                <div class="card-image" style="background: linear-gradient(135deg, <?= $rg['color'] ?>22, <?= $rg['color'] ?>44)">
                    <div class="game-emoji"><?= $rg['icon'] ?></div>
                </div>
                <div class="card-body">
                    <div class="card-name"><?= htmlspecialchars($rg['name']) ?></div>
                    <div class="card-currency"><?= htmlspecialchars($rg['currency']) ?></div>
                    <div class="card-price"><?= formatRupiah($min_price) ?> <small>/ mulai dari</small></div>
                    <button class="btn-card">🛒 Lihat Detail</button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<script>
const WA_NUMBER = '<?= WHATSAPP_NUMBER ?>';
const gamesData = <?= json_encode(array_map(fn($g) => [
    'name' => $g['name'], 'slug' => $g['slug'],
    'icon' => $g['icon'], 'currency' => $g['currency']
], $games)) ?>;
</script>

<?php require_once 'includes/footer.php'; ?>
