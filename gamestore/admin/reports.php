<?php
require_once 'includes/admin_config.php';
requireLogin();
$page_title  = 'Laporan';
$active_menu = 'reports';

// Periode filter
$period = $_GET['period'] ?? '30';
$days   = in_array($period,['7','14','30','90']) ? (int)$period : 30;

// Revenue per hari
$daily = db_rows("
    SELECT DATE(created_at) AS day,
           COALESCE(SUM(IF(status='completed',total,0)),0) AS revenue,
           COUNT(*) AS orders,
           SUM(status='completed') AS completed
    FROM orders
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL $days DAY)
    GROUP BY DATE(created_at)
    ORDER BY day ASC");

// Isi hari yang kosong
$daily_map = [];
foreach ($daily as $d) $daily_map[$d['day']] = $d;
$daily_full = [];
for ($i=$days-1;$i>=0;$i--) {
    $day = date('Y-m-d', strtotime("-$i days"));
    $daily_full[] = $daily_map[$day] ?? ['day'=>$day,'revenue'=>0,'orders'=>0,'completed'=>0];
}
$max_rev = max(array_column($daily_full,'revenue')) ?: 1;

// Revenue per game
$by_game = db_rows("
    SELECT p.name, p.icon, COUNT(o.id) AS orders,
           COALESCE(SUM(IF(o.status='completed',o.total,0)),0) AS revenue
    FROM products p
    LEFT JOIN orders o ON o.product_id=p.id AND o.created_at >= DATE_SUB(NOW(), INTERVAL $days DAY)
    GROUP BY p.id ORDER BY revenue DESC LIMIT 8");

// Revenue per payment method
$by_pay = db_rows("
    SELECT payment_method, COUNT(*) AS cnt,
           COALESCE(SUM(IF(status='completed',total,0)),0) AS revenue
    FROM orders WHERE created_at >= DATE_SUB(NOW(), INTERVAL $days DAY)
    GROUP BY payment_method ORDER BY revenue DESC");

// Summary
$summary = db_row("
    SELECT COUNT(*) AS total_orders,
           COALESCE(SUM(IF(status='completed',total,0)),0)  AS revenue,
           COALESCE(AVG(IF(status='completed',total,NULL)),0) AS avg_order,
           SUM(status='completed')  AS completed,
           SUM(status='pending')    AS pending,
           SUM(status='cancelled')  AS cancelled
    FROM orders WHERE created_at >= DATE_SUB(NOW(), INTERVAL $days DAY)");

// vs periode sebelumnya
$prev = db_row("SELECT COALESCE(SUM(IF(status='completed',total,0)),0) AS rev FROM orders
    WHERE created_at BETWEEN DATE_SUB(NOW(), INTERVAL ".($days*2)." DAY) AND DATE_SUB(NOW(), INTERVAL $days DAY)");
$growth = $prev['rev']>0 ? round(($summary['revenue']-$prev['rev'])/$prev['rev']*100,1) : 0;

$pay_colors=['DANA'=>'#00AAFF','OVO'=>'#6B3FA0','GoPay'=>'#00AED6','ShopeePay'=>'#EE4D2D',
             'Transfer BCA'=>'#005BAA','Transfer BRI'=>'#0066AE','Transfer Mandiri'=>'#003087','QRIS'=>'#E31837'];

require_once 'includes/admin_layout.php';
?>
<div class="page-content">

<!-- Period filter -->
<div style="display:flex;gap:.5rem;margin-bottom:1.25rem;align-items:center">
    <span style="font-size:.82rem;color:var(--gray)">Periode:</span>
    <?php foreach (['7'=>'7 Hari','14'=>'14 Hari','30'=>'30 Hari','90'=>'3 Bulan'] as $val=>$lbl): ?>
    <a href="?period=<?= $val ?>" class="btn btn-sm <?= $period===$val?'btn-primary':'btn-ghost' ?>"><?= $lbl ?></a>
    <?php endforeach; ?>
</div>

<!-- Summary Cards -->
<div class="stats-grid" style="margin-bottom:1.25rem">
    <div class="stat-card" style="--accent-color:var(--cyan)">
        <span class="stat-icon">💰</span>
        <div class="stat-label">Total Revenue</div>
        <div class="stat-value"><?= formatNum((int)$summary['revenue']) ?></div>
        <div class="stat-change <?= $growth>=0?'up':'down' ?>"><?= $growth>=0?'↑':'↓' ?> <?= abs($growth) ?>% vs sebelumnya</div>
    </div>
    <div class="stat-card" style="--accent-color:var(--blue)">
        <span class="stat-icon">📦</span>
        <div class="stat-label">Total Order</div>
        <div class="stat-value"><?= $summary['total_orders'] ?></div>
        <div class="stat-change up"><?= $summary['completed'] ?> selesai</div>
    </div>
    <div class="stat-card" style="--accent-color:var(--success)">
        <span class="stat-icon">💳</span>
        <div class="stat-label">Rata-rata / Order</div>
        <div class="stat-value"><?= formatNum((int)$summary['avg_order']) ?></div>
        <div class="stat-change up">Per transaksi selesai</div>
    </div>
    <div class="stat-card" style="--accent-color:var(--warning)">
        <span class="stat-icon">📊</span>
        <div class="stat-label">Conversion Rate</div>
        <div class="stat-value"><?= $summary['total_orders']>0?round($summary['completed']/$summary['total_orders']*100).'%':'—' ?></div>
        <div class="stat-change up">Order selesai / total</div>
    </div>
</div>

<!-- Revenue Chart -->
<div class="card" style="margin-bottom:1rem">
    <div class="card-header">
        <div class="card-title">📈 Revenue <?= $days ?> Hari</div>
        <span style="font-size:.8rem;color:var(--gray)">Total: <strong style="color:var(--cyan)"><?= formatRp((int)$summary['revenue']) ?></strong></span>
    </div>
    <div class="card-body">
        <div style="display:flex;align-items:flex-end;gap:3px;height:150px">
            <?php foreach ($daily_full as $pt):
                $pct = max(round($pt['revenue']/$max_rev*100), $pt['revenue']>0?3:1);
                $isToday = $pt['day']===date('Y-m-d');
            ?>
            <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:.2rem;height:100%;justify-content:flex-end"
                 title="<?= $pt['day'] ?>: <?= formatRp($pt['revenue']) ?> — <?= $pt['orders'] ?> order">
                <div style="width:100%;border-radius:3px 3px 0 0;min-height:2px;
                    background:<?= $isToday?'var(--cyan)':'linear-gradient(180deg,var(--blue),#1e40af)' ?>;
                    height:<?= $pct ?>%;transition:height .6s ease"></div>
            </div>
            <?php endforeach; ?>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:.68rem;color:var(--gray2);margin-top:.375rem">
            <span><?= date('d/m', strtotime("-".($days-1)." days")) ?></span>
            <span>Hari ini</span>
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
    <!-- By Game -->
    <div class="card">
        <div class="card-header"><div class="card-title">🎮 Revenue per Game</div></div>
        <div class="card-body">
            <?php $max_g=max(array_column($by_game,'revenue')+[0])?:1;
            $total_g=array_sum(array_column($by_game,'revenue'));
            foreach ($by_game as $g):
                $pct=round($g['revenue']/$total_g*100); $wpct=round($g['revenue']/$max_g*100); ?>
            <div style="margin-bottom:.875rem">
                <div style="display:flex;justify-content:space-between;font-size:.82rem;margin-bottom:.3rem">
                    <span><?= $g['icon'] ?> <?= htmlspecialchars($g['name']) ?></span>
                    <div style="display:flex;gap:.625rem">
                        <span style="color:var(--gray);font-size:.72rem"><?= $pct ?>% · <?= $g['orders'] ?>x</span>
                        <span style="color:var(--cyan);font-weight:700"><?= formatNum($g['revenue']) ?></span>
                    </div>
                </div>
                <div style="height:6px;background:var(--bg3);border-radius:3px;overflow:hidden">
                    <div style="height:100%;width:<?= $wpct ?>%;border-radius:3px;
                        background:linear-gradient(90deg,var(--blue),var(--cyan))"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- By Payment -->
    <div class="card">
        <div class="card-header"><div class="card-title">💳 Metode Pembayaran</div></div>
        <div class="card-body">
            <?php $max_p=max(array_column($by_pay,'revenue')+[0])?:1;
            $total_p=array_sum(array_column($by_pay,'revenue'));
            foreach ($by_pay as $bp):
                $pct=round($bp['revenue']/$total_p*100); $wpct=round($bp['revenue']/$max_p*100);
                $col=$pay_colors[$bp['payment_method']]??'var(--blue)'; ?>
            <div style="margin-bottom:.875rem">
                <div style="display:flex;justify-content:space-between;font-size:.82rem;margin-bottom:.3rem">
                    <div style="display:flex;align-items:center;gap:.4rem">
                        <div style="width:8px;height:8px;border-radius:50%;background:<?= $col ?>;flex-shrink:0"></div>
                        <?= htmlspecialchars($bp['payment_method']) ?>
                    </div>
                    <div style="display:flex;gap:.625rem">
                        <span style="color:var(--gray);font-size:.72rem"><?= $pct ?>% · <?= $bp['cnt'] ?>x</span>
                        <span style="color:var(--cyan);font-weight:700"><?= formatNum($bp['revenue']) ?></span>
                    </div>
                </div>
                <div style="height:6px;background:var(--bg3);border-radius:3px;overflow:hidden">
                    <div style="height:100%;width:<?= $wpct ?>%;border-radius:3px;background:<?= $col ?>"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
</div>
<?php require_once 'includes/admin_footer.php'; ?>
