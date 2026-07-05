<?php
require_once 'includes/config.php';
$page_title  = 'Semua Produk';
$active_cat  = $_GET['cat'] ?? 'Semua';
require_once 'includes/header.php';
?>

<div class="products-page-header">
    <div class="container">
        <h1 class="products-page-title">🎮 Semua Produk</h1>
        <p class="products-page-desc">Top up game favoritmu dengan harga terbaik dan proses instan</p>
    </div>
</div>

<section class="section">
    <div class="container">
        <div class="filter-tabs">
            <?php foreach ($categories as $cat): ?>
            <button class="filter-tab <?= $cat === $active_cat || ($active_cat === '' && $cat === 'Semua') ? 'active' : '' ?>"
                    data-cat="<?= $cat ?>">
                <?= $cat ?>
            </button>
            <?php endforeach; ?>
        </div>

        <div class="products-grid">
            <?php foreach ($games as $game):
                $pkgs      = $game['packages'] ?? [];
                $min_price = !empty($pkgs) ? min(array_column($pkgs, 'price')) : ($game['min_price'] ?? 0);
                $badge_map = ['Hot'=>'hot','Baru'=>'new','Populer'=>'popular','Terlaris'=>'popular'];
                $badge_class = $badge_map[$game['badge'] ?? ''] ?? '';
                $hidden = ($active_cat !== 'Semua' && $active_cat !== '' && $game['category'] !== $active_cat)
                          ? 'style="display:none"' : '';
            ?>
            <div class="product-card" data-cat="<?= $game['category'] ?>" <?= $hidden ?>
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
                    <div class="card-name"><?= htmlspecialchars($game['name']) ?></div>
                    <div class="card-currency"><?= htmlspecialchars($game['currency']) ?></div>
                    <div class="card-price">
                        <?= formatRupiah($min_price) ?> <small>/ mulai dari</small>
                    </div>
                    <button class="btn-card">🛒 Lihat Detail</button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<script>
const gamesData = <?= json_encode(array_map(fn($g) => [
    'name' => $g['name'], 'slug' => $g['slug'],
    'icon' => $g['icon'], 'currency' => $g['currency']
], $games)) ?>;
</script>

<?php require_once 'includes/footer.php'; ?>
