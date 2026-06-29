<?php
require_once 'includes/admin_config.php';
requireLogin();
$page_title  = 'Laporan & Statistik';
$active_menu = 'reports';
require_once 'includes/admin_layout.php';

// Revenue per 30 hari
$daily = [];
for ($d = 29; $d >= 0; $d--) {
    $day = date('d/m', strtotime("-$d days"));
    $rev = 0; $cnt = 0;
    foreach ($orders as $o) {
        if ($o['status']==='completed' && date('d/m', strtotime($o['date']))===$day) {
            $rev += $o['price']; $cnt++;
        }
    }
    $daily[] = ['day'=>$day,'rev'=>$rev,'cnt'=>$cnt];
}

// Revenue per game
$by_game = [];
foreach ($orders as $o) {
    if ($o['status']==='completed') {
        $by_game[$o['game']] = ($by_game[$o['game']] ?? 0) + $o['price'];
    }
}
arsort($by_game);

// Revenue per payment
$by_pay = [];
foreach ($orders as $o) {
    $by_pay[$o['payment']] = ($by_pay[$o['payment']] ?? 0) + $o['price'];
}
arsort($by_pay);

// Monthly summary
$this_month = 0; $last_month = 0;
$this_cnt = 0;   $last_cnt = 0;
foreach ($orders as $o) {
    $m = date('m/Y', strtotime($o['date']));
    if ($m === date('m/Y') && $o['status']==='completed') { $this_month += $o['price']; $this_cnt++; }
    if ($m === date('m/Y', strtotime('-1 month')) && $o['status']==='completed') { $last_month += $o['price']; $last_cnt++; }
}
$growth = $last_month > 0 ? round(($this_month - $last_month) / $last_month * 100, 1) : 0;
$max_rev = max(array_column($daily,'rev')) ?: 1;
$pay_colors = ['DANA'=>'#00AAFF','OVO'=>'#6B3FA0','GoPay'=>'#00AED6','ShopeePay'=>'#EE4D2D','Transfer BCA'=>'#005BAA','QRIS'=>'#E31837'];
?>

<div class="page-content">

    <!-- Summary Cards -->
    <div class="stats-grid" style="margin-bottom:1.5rem">
        <div class="stat-card" style="--accent-color:var(--cyan)">
            <span class="stat-icon">💰</span>
            <div class="stat-label">Bulan Ini</div>
            <div class="stat-value"><?= formatNum($this_month) ?></div>
            <div class="stat-change <?= $growth >= 0 ? 'up' : 'down' ?>">
                <?= $growth >= 0 ? '↑' : '↓' ?> <?= abs($growth) ?>% vs bulan lalu
            </div>
        </div>
        <div class="stat-card" style="--accent-color:var(--blue)">
            <span class="stat-icon">📦</span>
            <div class="stat-label">Order Bulan Ini</div>
            <div class="stat-value"><?= $this_cnt ?></div>
            <div class="stat-change up">↑ Transaksi selesai</div>
        </div>
        <div class="stat-card" style="--accent-color:var(--success)">
            <span class="stat-icon">📊</span>
            <div class="stat-label">Rata-rata / Order</div>
            <div class="stat-value"><?= $this_cnt ? formatNum(intdiv($this_month,$this_cnt)) : '0' ?></div>
            <div class="stat-change up">Nilai rata-rata transaksi</div>
        </div>
        <div class="stat-card" style="--accent-color:var(--warning)">
            <span class="stat-icon">💎</span>
            <div class="stat-label">Total Pendapatan</div>
            <div class="stat-value"><?= formatNum($total_revenue) ?></div>
            <div class="stat-change up">Semua waktu</div>
        </div>
    </div>

    <!-- Bar Chart 30 hari -->
    <div class="card" style="margin-bottom:1rem">
        <div class="card-header">
            <div class="card-title">📈 Pendapatan 30 Hari Terakhir</div>
            <div style="display:flex;gap:.5rem;font-size:.78rem;color:var(--gray)">
                <span>Total: <strong style="color:var(--cyan)"><?= formatRp(array_sum(array_column($daily,'rev'))) ?></strong></span>
                <span>•</span>
                <span>Order: <strong style="color:var(--white)"><?= array_sum(array_column($daily,'cnt')) ?></strong></span>
            </div>
        </div>
        <div class="card-body">
            <div style="display:flex;align-items:flex-end;gap:3px;height:140px;padding-top:.5rem">
                <?php foreach ($daily as $point):
                    $pct = max(round($point['rev']/$max_rev*100), $point['rev']>0?4:1);
                    $isToday = $point['day'] === date('d/m');
                ?>
                <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:.25rem;height:100%;justify-content:flex-end;cursor:pointer"
                     title="<?= $point['day'] ?>: <?= formatRp($point['rev']) ?> (<?= $point['cnt'] ?> order)">
                    <div style="width:100%;border-radius:4px 4px 0 0;transition:height .6s ease;min-height:3px;
                        background:<?= $isToday ? 'var(--cyan)' : 'linear-gradient(180deg,var(--blue),#1e40af)' ?>;
                        height:<?= $pct ?>%;position:relative;" class="rep-bar">
                    </div>
                    <?php if ($isToday): ?>
                    <div style="font-size:.6rem;color:var(--cyan);font-weight:700"><?= $point['day'] ?></div>
                    <?php elseif ($point['day'] === date('d/m', strtotime('-14 days')) || $point['day'] === date('d/m', strtotime('-7 days'))): ?>
                    <div style="font-size:.6rem;color:var(--gray2)"><?= $point['day'] ?></div>
                    <?php else: ?>
                    <div style="font-size:.6rem;color:transparent">-</div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Two columns: Game breakdown + Payment methods -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem">

        <!-- Revenue by Game -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">🎮 Revenue per Game</div>
            </div>
            <div class="card-body">
                <?php
                $max_g = max(array_values($by_game)+[0]) ?: 1;
                $total_g = array_sum($by_game);
                $gicons = ['Mobile Legends'=>'⚔️','Free Fire'=>'🔥','PUBG Mobile'=>'🎯','Genshin Impact'=>'✨','Valorant'=>'🎮','CODM'=>'🎖️'];
                $gi = 0;
                foreach ($by_game as $gname => $grev):
                    $pct = round($grev/$total_g*100);
                    $wpct = round($grev/$max_g*100);
                    $gi++;
                ?>
                <div style="margin-bottom:.875rem">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.3rem;font-size:.82rem">
                        <span><?= ($gicons[$gname]??'🎮') . ' ' . htmlspecialchars($gname) ?></span>
                        <div style="display:flex;gap:.75rem;align-items:center">
                            <span style="color:var(--gray);font-size:.72rem"><?= $pct ?>%</span>
                            <span style="color:var(--cyan);font-weight:700"><?= formatNum($grev) ?></span>
                        </div>
                    </div>
                    <div style="height:6px;background:var(--bg3);border-radius:3px;overflow:hidden">
                        <div style="height:100%;width:<?= $wpct ?>%;border-radius:3px;
                            background:linear-gradient(90deg,var(--blue),var(--cyan));
                            transition:width .8s ease"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Revenue by Payment Method -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">💳 Metode Pembayaran</div>
            </div>
            <div class="card-body">
                <?php
                $max_p   = max(array_values($by_pay)+[0]) ?: 1;
                $total_p = array_sum($by_pay);
                foreach ($by_pay as $pname => $prev):
                    $pct  = round($prev/$total_p*100);
                    $wpct = round($prev/$max_p*100);
                    $col  = $pay_colors[$pname] ?? 'var(--blue)';
                ?>
                <div style="margin-bottom:.875rem">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.3rem;font-size:.82rem">
                        <div style="display:flex;align-items:center;gap:.5rem">
                            <div style="width:8px;height:8px;border-radius:50%;background:<?= $col ?>;flex-shrink:0"></div>
                            <span><?= htmlspecialchars($pname) ?></span>
                        </div>
                        <div style="display:flex;gap:.75rem;align-items:center">
                            <span style="color:var(--gray);font-size:.72rem"><?= $pct ?>%</span>
                            <span style="color:var(--cyan);font-weight:700"><?= formatNum($prev) ?></span>
                        </div>
                    </div>
                    <div style="height:6px;background:var(--bg3);border-radius:3px;overflow:hidden">
                        <div style="height:100%;width:<?= $wpct ?>%;border-radius:3px;background:<?= $col ?>;transition:width .8s ease"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Order status breakdown -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">📊 Ringkasan Status Order</div>
            <button class="btn btn-ghost btn-sm" onclick="window.print()">🖨️ Print</button>
        </div>
        <div class="card-body">
            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem">
                <?php
                $status_info = [
                    'completed'  => ['label'=>'Selesai',   'icon'=>'✅','color'=>'var(--success)','count'=>$completed_orders],
                    'processing' => ['label'=>'Proses',    'icon'=>'⚡','color'=>'var(--blue-l)','count'=>count(array_filter($orders,fn($o)=>$o['status']==='processing'))],
                    'pending'    => ['label'=>'Pending',   'icon'=>'⏳','color'=>'var(--warning)','count'=>$pending_orders],
                    'cancelled'  => ['label'=>'Batal',     'icon'=>'❌','color'=>'var(--danger)', 'count'=>$cancelled_orders],
                ];
                foreach ($status_info as $st => $si):
                    $pct = $total_orders > 0 ? round($si['count']/$total_orders*100) : 0;
                ?>
                <div style="background:var(--bg3);border-radius:var(--radius);padding:1.25rem;text-align:center;border:1px solid var(--border)">
                    <div style="font-size:2rem;margin-bottom:.5rem"><?= $si['icon'] ?></div>
                    <div style="font-size:1.5rem;font-weight:900;color:<?= $si['color'] ?>"><?= $si['count'] ?></div>
                    <div style="font-size:.78rem;color:var(--gray);margin:.2rem 0"><?= $si['label'] ?></div>
                    <div style="font-size:.7rem;color:var(--gray2)"><?= $pct ?>% dari total</div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

</div>
<?php require_once 'includes/admin_footer.php'; ?>
