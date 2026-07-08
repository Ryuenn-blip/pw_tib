<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/config.php';
$page_title = 'Beranda';
require_once 'includes/header.php';
?>

<!-- Hero -->
<section class="hero">
    <canvas class="hero-canvas" id="heroCanvas"></canvas>
    <div class="hero-grid"></div>
    <div class="hero-glow"></div>
    <div class="hero-glow-2"></div>
    <div class="hero-container">
        <div class="hero-content">
            <div class="hero-badge">
                <span class="badge-dot"></span>
                ✨ Terpercaya &amp; Termurah
            </div>
            <h1>Top Up Game &amp; Item<br><span class="accent">Terlengkap &amp; Termurah</span></h1>
            <p class="hero-desc">Dapatkan berbagai item game favoritmu dengan harga terbaik dan proses instan 24 jam. Aman, cepat, dan terpercaya.</p>
            <div class="hero-actions">
                <a href="products.php" class="btn-primary">🎮 Lihat Semua Produk</a>
                <a href="contact.php" class="btn-outline">💬 Chat Admin</a>
            </div>
            <div class="hero-stats">
                <div class="hero-stat">
                    <div class="number">50K+</div>
                    <div class="label">Transaksi Selesai</div>
                </div>
                <div class="hero-stat">
                    <div class="number">100+</div>
                    <div class="label">Game Tersedia</div>
                </div>
                <div class="hero-stat">
                    <div class="number">24/7</div>
                    <div class="label">Layanan Admin</div>
                </div>
            </div>
        </div>

        <div class="hero-features">
            <div class="features-grid">
                <div class="feature-item">
                    <div class="feature-icon-wrap blue">🛡️</div>
                    <div class="feature-text">
                        <div class="title">100% Aman</div>
                        <div class="desc">Transaksi aman &amp; terpercaya</div>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon-wrap purple">⚡</div>
                    <div class="feature-text">
                        <div class="title">Proses Instan</div>
                        <div class="desc">Pengiriman cepat tanpa menunggu</div>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon-wrap green">🎧</div>
                    <div class="feature-text">
                        <div class="title">24/7 Support</div>
                        <div class="desc">Admin siap bantu kapan saja</div>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon-wrap yellow">⭐</div>
                    <div class="feature-text">
                        <div class="title">Harga Terbaik</div>
                        <div class="desc">Jaminan harga paling murah</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Promo Banner -->
<section class="promo-section">
    <div class="container">
        <div class="promo-grid">
            <div class="promo-banner main">
                <div class="promo-content">
                    <div class="promo-tag">🔥 Promo Spesial</div>
                    <div class="promo-title">Diskon 10%<br>Mobile Legends!</div>
                    <div class="promo-desc">Berlaku untuk semua paket Diamond. Gunakan kode: <strong>MLBB10</strong></div>
                </div>
                <div class="promo-deco">💎</div>
            </div>
            <div class="promo-banner secondary">
                <div class="promo-content">
                    <div class="promo-tag">⚡ Flash Sale</div>
                    <div class="promo-title">Free Fire<br>Bonus +20%</div>
                    <div class="promo-desc">Hari ini saja! Stok terbatas.</div>
                </div>
                <div class="promo-deco">🔥</div>
            </div>
        </div>
    </div>
</section>

<!-- Produk Terpopuler -->
<section class="section" style="padding-top:0">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">🔥 Produk Terpopuler</h2>
            <a href="products.php" class="section-link">Lihat Semua →</a>
        </div>

        <div class="filter-tabs">
            <?php foreach ($categories as $cat): ?>
            <button class="filter-tab <?= $cat === 'Semua' ? 'active' : '' ?>" data-cat="<?= $cat ?>">
                <?= $cat ?>
            </button>
            <?php endforeach; ?>
        </div>

        <div class="products-grid gs-reveal-stagger">
            <?php foreach ($games as $game):
                $pkgs      = $game['packages'] ?? [];
                $min_price = !empty($pkgs) ? min(array_column($pkgs, 'price')) : ($game['min_price'] ?? 0);
                $badge_map = ['Hot'=>'hot','Baru'=>'new','Populer'=>'popular','Terlaris'=>'popular'];
                $badge_class = $badge_map[$game['badge'] ?? ''] ?? '';
            ?>
            <div class="product-card" data-cat="<?= $game['category'] ?>"
                 onclick="location.href='detail.php?slug=<?= $game['slug'] ?>'">
                <div class="card-glow"></div>
                <div class="card-image">
                    <img src="<?= htmlspecialchars($game['img']) ?>"
                         alt="<?= htmlspecialchars($game['name']) ?>"
                         loading="lazy"
                         onerror="this.onerror=null;this.src='assets/img/placeholder.png'">
                    <div class="card-img-overlay"></div>
                    <?php if ($game['badge']): ?>
                    <span class="card-badge <?= $badge_class ?>"><?= $game['badge'] ?></span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <div class="card-name"><?= $game['name'] ?></div>
                    <div class="card-currency"><?= $game['currency'] ?></div>
                    <div class="card-price">
                        <?= formatRupiah($min_price) ?>
                        <small> / mulai dari</small>
                    </div>
                    <button class="btn-card">🛒 Lihat Detail</button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Cara Order -->
<section class="section" style="background: var(--bg2); border-top: 1px solid var(--border); border-bottom: 1px solid var(--border);">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">⚡ Cara Order</h2>
            <a href="cara-order.php" class="section-link">Selengkapnya →</a>
        </div>
        <div class="steps-grid gs-reveal-stagger">
            <div class="step-card">
                <div class="step-number">1️⃣</div>
                <div class="step-title">Pilih Game</div>
                <div class="step-desc">Pilih game dan paket top up yang kamu inginkan dari daftar produk kami.</div>
            </div>
            <div class="step-card">
                <div class="step-number">2️⃣</div>
                <div class="step-title">Masukkan ID</div>
                <div class="step-desc">Masukkan User ID atau ID karakter game kamu dengan benar dan teliti.</div>
            </div>
            <div class="step-card">
                <div class="step-number">3️⃣</div>
                <div class="step-title">Bayar & Konfirmasi</div>
                <div class="step-desc">Lakukan pembayaran sesuai nominal dan kirim bukti transfer ke admin.</div>
            </div>
            <div class="step-card">
                <div class="step-number">4️⃣</div>
                <div class="step-title">Item Diterima</div>
                <div class="step-desc">Proses instan! Item langsung masuk ke akun game kamu dalam hitungan menit.</div>
            </div>
        </div>
    </div>
</section>

<!-- Testimoni -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">⭐ Testimoni Pelanggan</h2>
        </div>
        <div class="testimonials-grid gs-reveal-stagger">
            <?php foreach ($testimonials as $t): ?>
            <div class="testi-card">
                <div class="testi-stars"><?= str_repeat('★', $t['rating']) ?></div>
                <p class="testi-text">"<?= htmlspecialchars($t['text']) ?>"</p>
                <div class="testi-author">
                    <div class="testi-avatar"><?= $t['avatar'] ?></div>
                    <div>
                        <div class="testi-name"><?= htmlspecialchars($t['name']) ?></div>
                        <div class="testi-game"><?= htmlspecialchars($t['game']) ?></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<script>
// Pass games data to JS for search
const gamesData = <?= json_encode(array_map(fn($g) => [
    'name' => $g['name'], 'slug' => $g['slug'],
    'icon' => $g['icon'], 'currency' => $g['currency']
], $games)) ?>;
</script>

<?php require_once 'includes/footer.php'; ?>
