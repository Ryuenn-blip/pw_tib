<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/config.php';
require_once 'includes/joki_items_data.php';

$page_title = 'Jasa Joki Akun';
$active_cat = $_GET['game'] ?? 'Semua';
$services   = get_joki_services();
$game_list  = array_merge(['Semua'], joki_games_list());

require_once 'includes/header.php';
?>

<div class="products-page-header">
    <div class="container">
        <h1 class="products-page-title">🚀 Jasa Joki Akun</h1>
        <p class="products-page-desc">Push rank aman &amp; cepat, dikerjakan langsung oleh player berpengalaman</p>
    </div>
</div>

<section class="section">
    <div class="container">
        <div class="filter-tabs">
            <?php foreach ($game_list as $g): ?>
            <a href="joki.php?game=<?= urlencode($g) ?>"
               class="filter-tab <?= $g === $active_cat ? 'active' : '' ?>"><?= htmlspecialchars($g) ?></a>
            <?php endforeach; ?>
        </div>

        <div class="products-grid gs-reveal-stagger">
            <?php foreach ($services as $j):
                if ($active_cat !== 'Semua' && $j['game'] !== $active_cat) continue;
            ?>
            <div style="background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius-lg);
                overflow:hidden;transition:var(--transition);position:relative;cursor:pointer"
                onclick="location.href='joki-detail.php?slug=<?= $j['slug'] ?>'"
                onmouseover="this.style.borderColor='var(--blue)';this.style.transform='translateY(-4px)'"
                onmouseout="this.style.borderColor='var(--border)';this.style.transform='translateY(0)'">

                <?php if ($j['badge']): ?>
                <span style="position:absolute;top:.75rem;right:.75rem;background:linear-gradient(135deg,var(--blue),var(--cyan));
                    color:#fff;font-size:.68rem;font-weight:800;padding:.25rem .625rem;border-radius:100px;z-index:1">
                    <?= $j['badge'] ?>
                </span>
                <?php endif; ?>

                <div style="padding:1.25rem">
                    <div style="display:flex;align-items:center;gap:.625rem;margin-bottom:.875rem">
                        <span style="font-size:1.75rem"><?= $j['icon'] ?></span>
                        <div>
                            <div style="font-size:.72rem;color:var(--gray);font-weight:600"><?= htmlspecialchars($j['game']) ?></div>
                            <div style="font-size:.95rem;font-weight:800;line-height:1.3"><?= htmlspecialchars($j['title']) ?></div>
                        </div>
                    </div>

                    <p style="font-size:.8rem;color:var(--gray);line-height:1.5;margin-bottom:1rem"><?= htmlspecialchars($j['desc']) ?></p>

                    <div style="display:flex;gap:.75rem;margin-bottom:1rem;font-size:.75rem;color:var(--gray)">
                        <span>⏱️ <?= $j['duration'] ?></span>
                        <span>⭐ <?= number_format($j['rating'],1) ?></span>
                        <span>✅ <?= $j['done'] ?>+ selesai</span>
                    </div>

                    <div style="display:flex;align-items:center;justify-content:space-between;
                        padding-top:.875rem;border-top:1px solid var(--border)">
                        <div>
                            <div style="font-size:.68rem;color:var(--gray)">Mulai dari</div>
                            <div style="font-size:1.05rem;font-weight:900;color:var(--cyan)"><?= formatRupiah($j['price']) ?></div>
                        </div>
                        <a href="joki-detail.php?slug=<?= $j['slug'] ?>" class="btn-primary" style="padding:.6rem 1.125rem;font-size:.82rem">Pesan →</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if (empty(array_filter($services, fn($j)=>$active_cat==='Semua'||$j['game']===$active_cat))): ?>
        <div style="text-align:center;padding:4rem 0;color:var(--gray)">
            <div style="font-size:3rem;margin-bottom:1rem">🔍</div>
            <p>Belum ada jasa joki untuk game ini.</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<script>
const gamesData = <?= json_encode(array_map(fn($g) => ['name'=>$g['name'],'slug'=>$g['slug'],'icon'=>$g['icon'],'currency'=>$g['currency']], $games)) ?>;
</script>

<?php require_once 'includes/footer.php'; ?>
