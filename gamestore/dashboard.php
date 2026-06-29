<?php
require_once 'includes/config.php';
session_start();
user_require_login();

$user_id   = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_email= $_SESSION['user_email'];

// Ambil data user + orders
$user   = db_row('SELECT * FROM customers WHERE id = ?', [$user_id]);
$orders = user_get_orders($user_id);

// Stats
$total_orders    = count($orders);
$total_spent     = array_sum(array_column(array_filter($orders, fn($o)=>$o['status']==='completed'), 'total'));
$pending_orders  = count(array_filter($orders, fn($o)=>$o['status']==='pending'));
$completed_orders= count(array_filter($orders, fn($o)=>$o['status']==='completed'));

// Status badge
function statusBadge(string $s): string {
    $map = [
        'pending'    => ['⏳ Pending',    'rgba(245,158,11,.15)',  '#F59E0B', 'rgba(245,158,11,.3)'],
        'paid'       => ['💳 Dibayar',    'rgba(37,99,235,.15)',   '#60A5FA', 'rgba(37,99,235,.3)'],
        'processing' => ['⚡ Diproses',   'rgba(37,99,235,.15)',   '#3B82F6', 'rgba(37,99,235,.3)'],
        'completed'  => ['✅ Selesai',    'rgba(34,197,94,.15)',   '#22C55E', 'rgba(34,197,94,.3)'],
        'cancelled'  => ['❌ Dibatalkan', 'rgba(239,68,68,.15)',   '#EF4444', 'rgba(239,68,68,.3)'],
        'refunded'   => ['🔄 Refund',     'rgba(139,92,246,.15)',  '#8B5CF6', 'rgba(139,92,246,.3)'],
    ];
    [$label,$bg,$color,$border] = $map[$s] ?? [$s,'var(--bg3)','var(--gray)','var(--border)'];
    return "<span style=\"display:inline-flex;align-items:center;padding:.25rem .625rem;border-radius:100px;
        font-size:.7rem;font-weight:700;background:{$bg};color:{$color};border:1px solid {$border}\">{$label}</span>";
}

$page_title = 'Dashboard — ' . htmlspecialchars($user_name);
require_once 'includes/header.php';
?>

<style>
.dash-page { padding: 100px 0 4rem; }
.dash-grid { display: grid; grid-template-columns: 280px 1fr; gap: 1.5rem; max-width: 1100px; margin: 0 auto; padding: 0 1.5rem; }
.dash-sidebar { position: sticky; top: 88px; }
.dash-avatar {
    width: 72px; height: 72px; border-radius: 50%;
    background: linear-gradient(135deg, var(--blue), var(--cyan));
    display: flex; align-items: center; justify-content: center;
    font-size: 2rem; font-weight: 900; margin: 0 auto 1rem;
    border: 3px solid rgba(37,99,235,.4);
}
.dash-nav a {
    display: flex; align-items: center; gap: .625rem;
    padding: .625rem .875rem; border-radius: var(--radius);
    font-size: .875rem; font-weight: 500; color: var(--gray);
    text-decoration: none; transition: var(--transition);
    margin-bottom: 2px;
}
.dash-nav a:hover, .dash-nav a.active { background: var(--bg3); color: var(--white); }
.dash-nav a.active { color: var(--cyan); }
.stat-mini {
    background: var(--bg3); border: 1px solid var(--border);
    border-radius: var(--radius); padding: .875rem;
    text-align: center; transition: var(--transition);
}
.stat-mini:hover { border-color: var(--blue); }
.stat-mini .val { font-size: 1.5rem; font-weight: 900; color: var(--white); }
.stat-mini .lbl { font-size: .72rem; color: var(--gray); margin-top: .2rem; }
.order-row {
    display: flex; align-items: center; gap: 1rem;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid rgba(48,54,61,.5);
    transition: var(--transition);
}
.order-row:last-child { border-bottom: none; }
.order-row:hover { background: rgba(255,255,255,.02); }
@media(max-width:768px) {
    .dash-grid { grid-template-columns: 1fr; }
    .dash-sidebar { position: static; }
}
</style>

<div class="dash-page">
<div class="dash-grid">

    <!-- Sidebar -->
    <div class="dash-sidebar">
        <div style="background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius-lg);padding:1.5rem;margin-bottom:1rem;text-align:center">
            <div class="dash-avatar"><?= strtoupper($user_name[0]) ?></div>
            <div style="font-weight:800;font-size:1rem"><?= htmlspecialchars($user_name) ?></div>
            <div style="font-size:.78rem;color:var(--gray);margin-top:.2rem"><?= htmlspecialchars($user_email) ?></div>
            <div style="display:inline-flex;align-items:center;gap:.3rem;background:rgba(34,197,94,.1);
                border:1px solid rgba(34,197,94,.25);color:var(--success);padding:.25rem .625rem;
                border-radius:100px;font-size:.7rem;font-weight:700;margin-top:.625rem">
                ● Member Aktif
            </div>
        </div>

        <div style="background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius-lg);padding:1rem;margin-bottom:1rem">
            <nav class="dash-nav">
                <a href="dashboard.php" class="active">📊 Overview</a>
                <a href="dashboard.php?tab=orders">📋 Riwayat Order</a>
                <a href="dashboard.php?tab=profile">👤 Edit Profil</a>
                <a href="products.php">🎮 Top Up Sekarang</a>
                <a href="contact.php">💬 Hubungi Admin</a>
            </nav>
        </div>

        <a href="logout-user.php"
           style="display:flex;align-items:center;justify-content:center;gap:.5rem;
               background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);
               color:var(--danger);padding:.75rem;border-radius:var(--radius);
               font-size:.85rem;font-weight:700;text-decoration:none;transition:.2s"
           onmouseover="this.style.background='rgba(239,68,68,.15)'"
           onmouseout="this.style.background='rgba(239,68,68,.08)'">
            🚪 Logout
        </a>
    </div>

    <!-- Main Content -->
    <div>

        <!-- Stats row -->
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:.875rem;margin-bottom:1.25rem">
            <div class="stat-mini">
                <div class="val"><?= $total_orders ?></div>
                <div class="lbl">Total Order</div>
            </div>
            <div class="stat-mini">
                <div class="val"><?= $completed_orders ?></div>
                <div class="lbl">Selesai</div>
            </div>
            <div class="stat-mini">
                <div class="val"><?= $pending_orders ?></div>
                <div class="lbl">Pending</div>
            </div>
            <div class="stat-mini">
                <div class="val" style="font-size:1.1rem;color:var(--cyan)"><?= formatRupiah($total_spent) ?></div>
                <div class="lbl">Total Spent</div>
            </div>
        </div>

        <!-- Recent orders -->
        <div style="background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden;margin-bottom:1.25rem">
            <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border);
                display:flex;align-items:center;justify-content:space-between">
                <div style="font-weight:800">📋 Riwayat Order</div>
                <?php if ($total_orders > 5): ?>
                <a href="dashboard.php?tab=orders" style="font-size:.8rem;color:var(--blue)">Lihat Semua →</a>
                <?php endif; ?>
            </div>

            <?php if (empty($orders)): ?>
            <div style="text-align:center;padding:3rem;color:var(--gray)">
                <div style="font-size:3rem;margin-bottom:.75rem">📦</div>
                <div style="font-weight:600;color:var(--white);margin-bottom:.5rem">Belum ada order</div>
                <p style="font-size:.85rem;margin-bottom:1.25rem">Yuk mulai top up game favoritmu!</p>
                <a href="products.php" class="btn-primary"
                    style="display:inline-flex;align-items:center;gap:.5rem;padding:.75rem 1.5rem">
                    🎮 Lihat Produk
                </a>
            </div>
            <?php else: ?>
            <?php foreach (array_slice($orders, 0, 5) as $o): ?>
            <div class="order-row">
                <div style="flex:1;min-width:0">
                    <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.25rem">
                        <span style="font-weight:700;font-size:.875rem"><?= htmlspecialchars($o['product_name']) ?></span>
                        <?= statusBadge($o['status']) ?>
                    </div>
                    <div style="font-size:.75rem;color:var(--gray)">
                        <?= htmlspecialchars($o['package_info'] ?? '') ?>
                        · ID: <span style="font-family:monospace"><?= htmlspecialchars($o['game_user_id']) ?></span>
                        · <?= date('d/m/Y H:i', strtotime($o['created_at'])) ?>
                    </div>
                </div>
                <div style="text-align:right;flex-shrink:0">
                    <div style="font-weight:800;color:var(--cyan);font-size:.9rem"><?= formatRupiah($o['total']) ?></div>
                    <div style="font-size:.7rem;color:var(--gray);margin-top:.1rem;font-family:monospace"><?= $o['id'] ?></div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Quick actions -->
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.875rem">
            <?php foreach ([
                ['🎮','Top Up Game','Pilih game & paket','products.php','var(--blue)'],
                ['💬','Chat Admin','Tanya atau konfirmasi','contact.php','#25D366'],
                ['❓','FAQ','Pertanyaan umum','faq.php','var(--purple)'],
            ] as [$ic,$title,$desc,$url,$col]): ?>
            <a href="<?= $url ?>" style="background:var(--bg2);border:1px solid var(--border);
                border-radius:var(--radius-lg);padding:1.25rem;text-decoration:none;
                display:flex;flex-direction:column;align-items:center;gap:.5rem;text-align:center;
                transition:var(--transition)"
                onmouseover="this.style.borderColor='<?= $col ?>'"
                onmouseout="this.style.borderColor='var(--border)'">
                <span style="font-size:2rem"><?= $ic ?></span>
                <div style="font-weight:700;font-size:.875rem;color:var(--white)"><?= $title ?></div>
                <div style="font-size:.72rem;color:var(--gray)"><?= $desc ?></div>
            </a>
            <?php endforeach; ?>
        </div>

    </div>
</div>
</div>

<script>
const gamesData = <?= json_encode(array_map(fn($g)=>['name'=>$g['name'],'slug'=>$g['slug'],'icon'=>$g['icon'],'currency'=>$g['currency']],$games)) ?>;
</script>
<?php require_once 'includes/footer.php'; ?>
