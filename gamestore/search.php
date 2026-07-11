<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$q      = trim($_GET['q'] ?? '');
$cat    = trim($_GET['cat'] ?? '');
$results = [];
$total   = 0;

if ($q !== '') {
    // Cari produk dari DB
    $where  = "p.is_active=1 AND (p.name LIKE ? OR p.currency LIKE ? OR c.name LIKE ?)";
    $params = ["%$q%", "%$q%", "%$q%"];

    if ($cat && $cat !== 'Semua') {
        $where  .= " AND c.name = ?";
        $params[] = $cat;
    }

    $results = db_rows("
        SELECT p.*,
               c.name AS category_name,
               COALESCE(MIN(pk.price), 0) AS min_price,
               COUNT(pk.id) AS pkg_count
        FROM   products p
        JOIN   categories c ON c.id = p.category_id
        LEFT JOIN packages pk ON pk.product_id = p.id AND pk.is_active=1
        WHERE  $where
        GROUP  BY p.id
        ORDER  BY p.sort_order, p.name
    ", $params);

    $total = count($results);
}

$categories = db_rows("SELECT name FROM categories WHERE is_active=1 ORDER BY sort_order");
$page_title  = $q ? "Hasil pencarian: $q" : 'Cari Produk';
require_once 'includes/header.php';
?>

<div style="padding:100px 0 4rem;min-height:calc(100vh - 68px)">
<div class="container" style="max-width:1100px">

    <!-- Search Box -->
    <div style="text-align:center;margin-bottom:2.5rem">
        <h1 style="font-size:2rem;font-weight:900;margin-bottom:1.25rem">🔍 Cari Game</h1>
        <form method="GET" action="search.php" style="display:flex;gap:.625rem;max-width:600px;margin:0 auto">
            <div style="position:relative;flex:1">
                <input type="text" name="q" value="<?= htmlspecialchars($q) ?>"
                       placeholder="Nama game, mata uang..."
                       class="form-input" style="padding-right:3rem;font-size:1rem"
                       autofocus autocomplete="off">
                <?php if ($q): ?>
                <a href="search.php" style="position:absolute;right:.75rem;top:50%;transform:translateY(-50%);
                    color:var(--gray);text-decoration:none;font-size:1rem">✕</a>
                <?php endif; ?>
            </div>
            <button type="submit" class="btn-primary" style="padding:.75rem 1.5rem;font-size:.95rem">Cari</button>
        </form>

        <!-- Category filter -->
        <?php if ($q): ?>
        <div style="display:flex;gap:.5rem;justify-content:center;flex-wrap:wrap;margin-top:1rem">
            <a href="search.php?q=<?= urlencode($q) ?>"
               class="filter-tab <?= !$cat?'active':'' ?>">Semua</a>
            <?php foreach ($categories as $c): ?>
            <a href="search.php?q=<?= urlencode($q) ?>&cat=<?= urlencode($c['name']) ?>"
               class="filter-tab <?= $cat===$c['name']?'active':'' ?>"><?= htmlspecialchars($c['name']) ?></a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($q === ''): ?>
    <!-- Belum ada pencarian — tampilkan popular -->
    <div class="section-header">
        <h2 class="section-title">🔥 Game Terpopuler</h2>
    </div>
    <div class="products-grid">
        <?php foreach ($games as $g):
            $mp = !empty($g['packages']) ? min(array_column($g['packages'],'price')) : 0; ?>
        <div class="product-card" onclick="location.href='detail.php?slug=<?= $g['slug'] ?>'">
            <div class="card-glow"></div>
            <div class="card-image">
                <img src="<?= htmlspecialchars($g['img']??'') ? loading="lazy">" alt="<?= htmlspecialchars($g['name']) ?>"
                     loading="lazy" onerror="this.style.display='none'">
                <div class="card-img-overlay"></div>
                <?php if (!empty($g['badge'])): ?><span class="card-badge"><?= $g['badge'] ?></span><?php endif; ?>
            </div>
            <div class="card-body">
                <div class="card-name"><?= htmlspecialchars($g['name']) ?></div>
                <div class="card-currency"><?= htmlspecialchars($g['currency']) ?></div>
                <div class="card-price"><?= $mp ? formatRupiah($mp).' <small>/ mulai</small>' : '—' ?></div>
                <button class="btn-card">🛒 Lihat Detail</button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php elseif (empty($results)): ?>
    <!-- Tidak ada hasil -->
    <div style="text-align:center;padding:4rem 0;color:var(--gray)">
        <div style="font-size:4rem;margin-bottom:1rem">🔍</div>
        <h2 style="font-weight:800;color:var(--white);margin-bottom:.5rem">
            Tidak ada hasil untuk "<?= htmlspecialchars($q) ?>"
        </h2>
        <p style="margin-bottom:1.5rem">Coba kata kunci lain atau lihat semua produk kami.</p>
        <div style="display:flex;gap:.75rem;justify-content:center;flex-wrap:wrap">
            <a href="products.php" class="btn-primary"
               style="display:inline-flex;align-items:center;gap:.5rem;padding:.875rem 1.5rem">
               🎮 Semua Produk
            </a>
            <a href="contact.php" class="btn-outline"
               style="display:inline-flex;align-items:center;gap:.5rem;padding:.875rem 1.5rem">
               💬 Tanya Admin
            </a>
        </div>
    </div>

    <?php else: ?>
    <!-- Hasil pencarian -->
    <div style="margin-bottom:1.5rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem">
        <div style="font-size:.9rem;color:var(--gray)">
            Ditemukan <strong style="color:var(--white)"><?= $total ?></strong> produk
            untuk "<strong style="color:var(--cyan)"><?= htmlspecialchars($q) ?></strong>"
            <?= $cat ? ' di kategori <strong style="color:var(--white)">'.htmlspecialchars($cat).'</strong>' : '' ?>
        </div>
        <a href="products.php" style="font-size:.8rem;color:var(--blue)">Lihat Semua Produk →</a>
    </div>

    <div class="products-grid">
        <?php foreach ($results as $g): ?>
        <div class="product-card" onclick="location.href='detail.php?slug=<?= htmlspecialchars($g['slug']) ?>'">
            <div class="card-glow"></div>
            <div class="card-image">
                <?php if (!empty($g['img'])): ?>
                <img src="<?= htmlspecialchars($g['img']) ? loading="lazy">" alt="<?= htmlspecialchars($g['name']) ?>"
                     loading="lazy" onerror="this.style.display='none'">
                <div class="card-img-overlay"></div>
                <?php else: ?>
                <div style="display:flex;align-items:center;justify-content:center;height:100%;font-size:4rem"><?= $g['icon'] ?></div>
                <?php endif; ?>
                <?php if (!empty($g['badge'])): ?>
                <span class="card-badge"><?= htmlspecialchars($g['badge']) ?></span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <div class="card-name">
                    <?php
                    // Highlight kata kunci
                    echo preg_replace('/('.preg_quote($q,'/').')/i',
                        '<mark style="background:rgba(37,99,235,.25);color:var(--cyan);border-radius:3px;padding:0 2px">$1</mark>',
                        htmlspecialchars($g['name']));
                    ?>
                </div>
                <div class="card-currency"><?= htmlspecialchars($g['currency']) ?></div>
                <div class="card-price">
                    <?= $g['min_price'] ? formatRupiah($g['min_price']).' <small>/ mulai</small>' : '—' ?>
                </div>
                <button class="btn-card">🛒 Lihat Detail</button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</div>
</div>

<script>
const gamesData = <?= json_encode(array_map(fn($g)=>['name'=>$g['name'],'slug'=>$g['slug'],'icon'=>$g['icon'],'currency'=>$g['currency']],$games)) ?>;
</script>
<?php require_once 'includes/footer.php'; ?>
