<?php
require_once 'includes/admin_config.php';
requireLogin();
$page_title  = 'Dashboard';
$active_menu = 'dashboard';
require_once 'includes/admin_layout.php';

// Revenue chart 30 hari
$chart_30 = [];
for ($d = 29; $d >= 0; $d--) {
    $day = date('Y-m-d', strtotime("-$d days"));
    $r   = db_row("SELECT COALESCE(SUM(total),0) AS rev, COUNT(*) AS cnt
                   FROM orders WHERE DATE(created_at)=? AND status='completed'", [$day]);
    $chart_30[] = ['day'=>date('d/m', strtotime($day)), 'rev'=>(int)$r['rev'], 'cnt'=>(int)$r['cnt']];
}

$max_rev = max(array_column($chart_30, 'rev')) ?: 1;
$today_stats = db_row("SELECT
    COUNT(*) AS total,
    SUM(status='completed')  AS completed,
    SUM(status='pending')    AS pending,
    SUM(status='cancelled')  AS cancelled,
    COALESCE(SUM(IF(status='completed', total, 0)),0) AS revenue
    FROM orders WHERE DATE(created_at) = CURDATE()");
$processing_count   = (int)(db_row("SELECT COUNT(*) AS c FROM orders WHERE status='processing'")['c'] ?? 0);

// Variabel global stats
$total_stats        = db_row("SELECT COUNT(*) AS total,
    SUM(status='completed') AS completed,
    SUM(status='pending')   AS pending,
    SUM(status='cancelled') AS cancelled
    FROM orders");
$total_orders       = (int)($total_stats['total']     ?? 0);
$completed_orders   = (int)($total_stats['completed'] ?? 0);
$pending_orders     = (int)($total_stats['pending']   ?? 0);
$cancelled_orders   = (int)($total_stats['cancelled'] ?? 0);

// Produk terlaris
$top_products = db_rows("
    SELECT p.name, p.icon,
           COUNT(o.id) AS order_count,
           COALESCE(SUM(o.total),0) AS revenue
    FROM products p
    LEFT JOIN orders o ON o.product_id = p.id AND o.status='completed'
    GROUP BY p.id ORDER BY revenue DESC LIMIT 5");

// Pelanggan baru bulan ini
$new_customers = (int)(db_row("SELECT COUNT(*) AS c FROM customers WHERE MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW())")['c'] ?? 0);

// Pesanan terbaru
$recent_orders = db_rows("
    SELECT o.*, p.name AS game_name, p.icon AS game_icon
    FROM orders o JOIN products p ON p.id=o.product_id
    ORDER BY o.created_at DESC LIMIT 8");
?>
<div class="page-content">

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card" style="--accent-color:var(--cyan)">
        <span class="stat-icon">💰</span>
        <div class="stat-label">Revenue Bulan Ini</div>
        <div class="stat-value"><?= formatNum((int)db_row("SELECT COALESCE(SUM(total),0) AS r FROM orders WHERE MONTH(created_at)=MONTH(NOW()) AND status='completed'")['r']) ?></div>
        <div class="stat-change up">↑ Total pendapatan</div>
    </div>
    <div class="stat-card" style="--accent-color:var(--blue)">
        <span class="stat-icon">📋</span>
        <div class="stat-label">Total Order</div>
        <div class="stat-value"><?= $total_orders ?></div>
        <div class="stat-change up">Semua waktu</div>
    </div>
    <div class="stat-card" style="--accent-color:var(--warning)">
        <span class="stat-icon">⏳</span>
        <div class="stat-label">Perlu Diproses</div>
        <div class="stat-value"><?= $pending_orders + $processing_count ?></div>
        <div class="stat-change down"><?= $pending_orders ?> pending · <?= $processing_count ?> proses</div>
    </div>
    <div class="stat-card" style="--accent-color:var(--success)">
        <span class="stat-icon">👥</span>
        <div class="stat-label">Pelanggan Baru</div>
        <div class="stat-value"><?= $new_customers ?></div>
        <div class="stat-change up">Bulan ini</div>
    </div>
</div>

<!-- Charts Row -->
<div class="charts-grid">
    <div class="card">
        <div class="card-header">
            <div class="card-title">📈 Revenue 30 Hari Terakhir</div>
            <span style="font-size:.8rem;color:var(--gray)">
                Total: <strong style="color:var(--cyan)"><?= formatRp(array_sum(array_column($chart_30,'rev'))) ?></strong>
            </span>
        </div>
        <div class="card-body">
            <div style="display:flex;align-items:flex-end;gap:3px;height:140px;padding-top:.5rem">
                <?php foreach ($chart_30 as $pt):
                    $pct = max(round($pt['rev']/$max_rev*100), $pt['rev']>0?3:1);
                    $isToday = $pt['day']===date('d/m');
                ?>
                <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:.2rem;height:100%;justify-content:flex-end"
                     title="<?= $pt['day'] ?>: <?= formatRp($pt['rev']) ?> (<?= $pt['cnt'] ?> order)">
                    <div style="width:100%;border-radius:3px 3px 0 0;min-height:2px;
                        background:<?= $isToday?'var(--cyan)':'linear-gradient(180deg,var(--blue),#1e40af)' ?>;
                        height:<?= $pct ?>%"></div>
                    <?php if ($isToday): ?>
                    <div style="font-size:.58rem;color:var(--cyan);font-weight:700">Hari ini</div>
                    <?php else: ?>
                    <div style="font-size:.58rem;color:transparent">-</div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Status donut -->
    <div class="card">
        <div class="card-header"><div class="card-title">🍩 Status Order</div></div>
        <div class="card-body">
            <svg id="donutChart" width="120" height="120" viewBox="0 0 120 120" style="display:block;margin:0 auto .875rem"></svg>
            <div style="display:flex;flex-direction:column;gap:.4rem">
                <?php
                $segs = [
                    ['Selesai',   $completed_orders,'#22C55E'],
                    ['Proses',    $processing_count,'#3B82F6'],
                    ['Pending',   $pending_orders,  '#F59E0B'],
                    ['Batal',     $cancelled_orders,'#EF4444'],
                ];
                foreach ($segs as [$lbl,$val,$col]):
                    $pct2 = $total_orders ? round($val/$total_orders*100) : 0;
                ?>
                <div style="display:flex;align-items:center;justify-content:space-between;font-size:.8rem">
                    <div style="display:flex;align-items:center;gap:.4rem;color:var(--gray)">
                        <span style="width:9px;height:9px;border-radius:50%;background:<?= $col ?>;flex-shrink:0"></span>
                        <?= $lbl ?>
                    </div>
                    <div style="display:flex;gap:.625rem">
                        <span style="color:var(--gray);font-size:.73rem"><?= $val ?></span>
                        <span style="font-weight:700;color:var(--white)"><?= $pct2 ?>%</span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Bottom: Recent Orders + Top Products -->
<div style="display:grid;grid-template-columns:2fr 1fr;gap:1rem">
    <div class="card">
        <div class="card-header">
            <div class="card-title">📋 Pesanan Terbaru</div>
            <a href="orders.php" class="btn btn-ghost btn-sm">Lihat Semua →</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>ID</th><th>Pelanggan</th><th>Game</th><th>Total</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                <?php foreach ($recent_orders as $o):
                    $stmap = ['pending'=>['badge-pending','⏳'],'processing'=>['badge-processing','⚡'],
                              'completed'=>['badge-completed','✅'],'cancelled'=>['badge-cancelled','❌'],
                              'refunded'=>['badge-inactive','🔄']];
                    [$bcls,$bico] = $stmap[$o['status']] ?? ['badge-inactive','?'];
                ?>
                <tr>
                    <td class="td-id"><?= $o['id'] ?></td>
                    <td>
                        <div style="font-weight:600;font-size:.85rem"><?= htmlspecialchars($o['customer_name']) ?></div>
                        <div style="font-size:.72rem;color:var(--gray)"><?= date('d/m H:i', strtotime($o['created_at'])) ?></div>
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;gap:.4rem">
                            <span><?= $o['game_icon'] ?></span>
                            <div>
                                <div style="font-size:.83rem;font-weight:600"><?= htmlspecialchars($o['game_name']) ?></div>
                                <div style="font-size:.7rem;color:var(--gray)"><?= $o['package_amount'] ?> <?= htmlspecialchars($o['currency']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td style="font-weight:700;color:var(--cyan)"><?= formatRp($o['total']) ?></td>
                    <td><span class="badge <?= $bcls ?>"><span class="badge-dot"></span><?= ucfirst($o['status']) ?></span></td>
                    <td><a href="orders.php?id=<?= $o['id'] ?>" class="btn btn-ghost btn-sm">Detail</a></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><div class="card-title">🏆 Produk Terlaris</div></div>
        <div class="card-body">
            <?php $max_g = max(array_column($top_products,'revenue')+[0]) ?: 1;
            foreach ($top_products as $i=>$tp): ?>
            <div style="display:flex;align-items:center;gap:.625rem;margin-bottom:.875rem">
                <div style="font-size:.75rem;font-weight:800;color:var(--gray2);width:16px"><?= $i+1 ?></div>
                <div style="font-size:1.3rem"><?= $tp['icon'] ?></div>
                <div style="flex:1;min-width:0">
                    <div style="font-size:.83rem;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                        <?= htmlspecialchars($tp['name']) ?>
                    </div>
                    <div style="height:4px;background:var(--bg3);border-radius:2px;margin-top:.3rem;overflow:hidden">
                        <div style="height:100%;background:linear-gradient(90deg,var(--blue),var(--cyan));
                            width:<?= round($tp['revenue']/$max_g*100) ?>%;border-radius:2px"></div>
                    </div>
                </div>
                <div style="font-size:.8rem;font-weight:700;color:var(--cyan);flex-shrink:0"><?= formatNum($tp['revenue']) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

</div>

<script>
const donutData = [
    {value:<?= $completed_orders ?>,color:'#22C55E'},
    {value:<?= $processing_count ?>,color:'#3B82F6'},
    {value:<?= $pending_orders ?>,  color:'#F59E0B'},
    {value:<?= $cancelled_orders ?>,color:'#EF4444'},
];
if(typeof buildDonut==='function') buildDonut('donutChart', donutData);
</script>
<?php require_once 'includes/admin_footer.php'; ?>
