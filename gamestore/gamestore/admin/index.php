<?php
require_once 'includes/admin_config.php';
requireLogin();
$page_title  = 'Dashboard';
$active_menu = 'dashboard';
require_once 'includes/admin_layout.php';

$max_rev = max(array_column($revenue_chart, 'rev')) ?: 1;
$total_all_revenue = array_sum(array_column($orders, 'price'));
?>

<div class="page-content">

    <!-- Stat Cards -->
    <div class="stats-grid">
        <div class="stat-card" style="--accent-color:var(--cyan)">
            <span class="stat-icon">💰</span>
            <div class="stat-label">Total Pendapatan</div>
            <div class="stat-value"><?= formatNum($total_revenue) ?></div>
            <div class="stat-change up">↑ 12% dari bulan lalu</div>
        </div>
        <div class="stat-card" style="--accent-color:var(--blue)">
            <span class="stat-icon">📋</span>
            <div class="stat-label">Total Pesanan</div>
            <div class="stat-value"><?= $total_orders ?></div>
            <div class="stat-change up">↑ 8% dari bulan lalu</div>
        </div>
        <div class="stat-card" style="--accent-color:var(--warning)">
            <span class="stat-icon">⏳</span>
            <div class="stat-label">Pesanan Pending</div>
            <div class="stat-value"><?= $pending_orders ?></div>
            <div class="stat-change down">Perlu segera diproses</div>
        </div>
        <div class="stat-card" style="--accent-color:var(--success)">
            <span class="stat-icon">✅</span>
            <div class="stat-label">Pesanan Selesai</div>
            <div class="stat-value"><?= $completed_orders ?></div>
            <div class="stat-change up">↑ 15% dari bulan lalu</div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="charts-grid">

        <!-- Revenue Bar Chart -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">📈 Pendapatan 7 Hari Terakhir</div>
                <span style="font-size:.8rem;color:var(--gray)">Pesanan selesai</span>
            </div>
            <div class="card-body">
                <div class="bar-chart">
                    <?php foreach ($revenue_chart as $point):
                        $pct = $max_rev ? round(($point['rev'] / $max_rev) * 100) : 4;
                        $pct = max($pct, 4);
                    ?>
                    <div class="bar-wrap">
                        <div class="bar" data-pct="<?= $pct ?>" style="height:<?= $pct ?>%">
                            <div class="bar-tooltip"><?= formatRp($point['rev']) ?></div>
                        </div>
                        <div class="bar-label"><?= $point['day'] ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Status Donut -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">🍩 Status Pesanan</div>
            </div>
            <div class="card-body">
                <div class="donut-wrap">
                    <svg id="donutChart" class="donut-svg" width="120" height="120" viewBox="0 0 120 120"></svg>
                    <div class="donut-legend">
                        <?php
                        $seg_data = [
                            ['label'=>'Selesai',    'value'=>$completed_orders, 'color'=>'#22C55E'],
                            ['label'=>'Processing', 'value'=>count(array_filter($orders,fn($o)=>$o['status']==='processing')), 'color'=>'#3B82F6'],
                            ['label'=>'Pending',    'value'=>$pending_orders,   'color'=>'#F59E0B'],
                            ['label'=>'Dibatalkan', 'value'=>$cancelled_orders, 'color'=>'#EF4444'],
                        ];
                        foreach ($seg_data as $seg):
                            $pct2 = $total_orders ? round($seg['value']/$total_orders*100) : 0;
                        ?>
                        <div class="legend-item">
                            <div class="legend-name">
                                <span class="legend-dot" style="background:<?= $seg['color'] ?>"></span>
                                <?= $seg['label'] ?>
                            </div>
                            <div style="display:flex;gap:.75rem;align-items:center">
                                <span style="font-size:.75rem;color:var(--gray)"><?= $seg['value'] ?></span>
                                <span class="legend-pct"><?= $pct2 ?>%</span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Row: Recent Orders + Top Games -->
    <div style="display:grid;grid-template-columns:2fr 1fr;gap:1rem">

        <!-- Recent Orders -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">📋 Pesanan Terbaru</div>
                <a href="orders.php" class="btn btn-ghost btn-sm">Lihat Semua →</a>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Pelanggan</th>
                            <th>Game</th>
                            <th>Harga</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($orders, 0, 8) as $o): ?>
                        <tr>
                            <td class="td-id"><?= $o['id'] ?></td>
                            <td>
                                <div style="font-weight:600;font-size:.85rem"><?= htmlspecialchars($o['name']) ?></div>
                                <div style="font-size:.72rem;color:var(--gray)"><?= $o['payment'] ?></div>
                            </td>
                            <td>
                                <div class="td-game">
                                    <span class="td-icon"><?= $o['icon'] ?></span>
                                    <div>
                                        <div class="td-name"><?= htmlspecialchars($o['game']) ?></div>
                                        <div class="td-sub"><?= $o['amount'] ?> <?= $o['currency'] ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="td-price"><?= formatRp($o['price']) ?></td>
                            <td>
                                <span class="badge badge-<?= $o['status'] ?>">
                                    <span class="badge-dot"></span>
                                    <?= ucfirst($o['status']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="orders.php?id=<?= $o['id'] ?>" class="btn btn-ghost btn-sm">Detail</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top Games -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">🏆 Game Terlaris</div>
            </div>
            <div class="card-body">
                <?php
                $max_g = max(array_values($top_games)) ?: 1;
                $rank  = 1;
                foreach ($top_games as $gname => $grev):
                    $g = array_filter($game_list, fn($x)=>$x['name']===$gname);
                    $icon = array_values($g)[0]['icon'] ?? '🎮';
                ?>
                <div class="top-game-item">
                    <div class="tgi-rank"><?= $rank++ ?></div>
                    <div class="tgi-icon"><?= $icon ?></div>
                    <div class="tgi-info">
                        <div class="tgi-name"><?= htmlspecialchars($gname) ?></div>
                        <div class="tgi-bar-wrap">
                            <div class="tgi-bar" style="width:<?= round($grev/$max_g*100) ?>%"></div>
                        </div>
                    </div>
                    <div class="tgi-revenue"><?= formatNum($grev) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

</div>

<script>
const donutData = [
    {value: <?= $completed_orders ?>, color: '#22C55E'},
    {value: <?= count(array_filter($orders,fn($o)=>$o['status']==='processing')) ?>, color: '#3B82F6'},
    {value: <?= $pending_orders ?>,   color: '#F59E0B'},
    {value: <?= $cancelled_orders ?>, color: '#EF4444'},
];
</script>

<?php require_once 'includes/admin_footer.php'; ?>
