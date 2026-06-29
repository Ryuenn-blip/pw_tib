<?php
require_once 'includes/admin_config.php';
requireLogin();
$page_title  = 'Manajemen Pelanggan';
$active_menu = 'customers';
require_once 'includes/admin_layout.php';

// Generate dummy customers dari orders
$customers_raw = [];
foreach ($orders as $o) {
    $uid = $o['user_id'];
    if (!isset($customers_raw[$uid])) {
        $customers_raw[$uid] = [
            'id'           => $uid,
            'name'         => $o['name'],
            'user_id_game' => $uid,
            'total_orders' => 0,
            'total_spent'  => 0,
            'last_game'    => $o['game'],
            'last_order'   => $o['date'],
            'payment'      => $o['payment'],
            'orders'       => [],
        ];
    }
    $customers_raw[$uid]['total_orders']++;
    if ($o['status'] === 'completed') $customers_raw[$uid]['total_spent'] += $o['price'];
    $customers_raw[$uid]['orders'][] = $o;
    if ($o['date'] > $customers_raw[$uid]['last_order']) {
        $customers_raw[$uid]['last_order'] = $o['date'];
        $customers_raw[$uid]['last_game']  = $o['game'];
    }
}
// Sort by total_spent desc
usort($customers_raw, fn($a,$b) => $b['total_spent'] - $a['total_spent']);
$customers = array_values($customers_raw);
$total_customers = count($customers);
$vip_customers   = count(array_filter($customers, fn($c) => $c['total_spent'] >= 100000));
$new_this_month  = count(array_filter($customers, fn($c) => strtotime($c['last_order']) >= strtotime('-30 days')));
?>

<div class="page-content">

    <!-- Stats row -->
    <div class="stats-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:1.5rem">
        <div class="stat-card" style="--accent-color:var(--blue)">
            <span class="stat-icon">👥</span>
            <div class="stat-label">Total Pelanggan</div>
            <div class="stat-value"><?= $total_customers ?></div>
            <div class="stat-change up">↑ Semua waktu</div>
        </div>
        <div class="stat-card" style="--accent-color:var(--warning)">
            <span class="stat-icon">⭐</span>
            <div class="stat-label">Pelanggan VIP</div>
            <div class="stat-value"><?= $vip_customers ?></div>
            <div class="stat-change up">Spent ≥ Rp100rb</div>
        </div>
        <div class="stat-card" style="--accent-color:var(--success)">
            <span class="stat-icon">🆕</span>
            <div class="stat-label">Aktif Bulan Ini</div>
            <div class="stat-value"><?= $new_this_month ?></div>
            <div class="stat-change up">30 hari terakhir</div>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="toolbar">
        <div class="search-box">
            <span class="ico">🔍</span>
            <input type="text" id="tableSearch" placeholder="Cari nama pelanggan...">
        </div>
        <select class="filter-select" id="sortFilter">
            <option value="">Urutkan: Terbanyak Spent</option>
            <option value="orders">Terbanyak Order</option>
            <option value="recent">Terbaru</option>
        </select>
        <button class="btn btn-primary btn-sm" onclick="exportCSV()">📥 Export CSV</button>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">👥 Daftar Pelanggan
                <span style="color:var(--gray);font-weight:400;font-size:.85rem">(<?= $total_customers ?> pelanggan)</span>
            </div>
        </div>
        <div class="table-wrap">
            <table id="custTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama</th>
                        <th>Game Favorit</th>
                        <th>Metode Bayar</th>
                        <th>Total Order</th>
                        <th>Total Spent</th>
                        <th>Terakhir Order</th>
                        <th>Label</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $i => $c):
                        $label = $c['total_spent'] >= 200000 ? ['🥇 Gold', 'warning']
                               : ($c['total_spent'] >= 100000 ? ['🥈 Silver', 'processing']
                               : ['🥉 Bronze', 'inactive']);
                    ?>
                    <tr>
                        <td class="td-id"><?= $i + 1 ?></td>
                        <td>
                            <div style="display:flex;align-items:center;gap:.625rem">
                                <div style="width:34px;height:34px;border-radius:50%;
                                    background:linear-gradient(135deg,var(--blue),var(--cyan));
                                    display:flex;align-items:center;justify-content:center;
                                    font-weight:800;font-size:.85rem;flex-shrink:0">
                                    <?= strtoupper($c['name'][0]) ?>
                                </div>
                                <div>
                                    <div style="font-weight:700;font-size:.875rem"><?= htmlspecialchars($c['name']) ?></div>
                                    <div style="font-size:.7rem;color:var(--gray);font-family:monospace">ID: <?= $c['user_id_game'] ?></div>
                                </div>
                            </div>
                        </td>
                        <td style="font-size:.82rem"><?= htmlspecialchars($c['last_game']) ?></td>
                        <td>
                            <span style="font-size:.75rem;background:var(--bg3);padding:.2rem .5rem;border-radius:5px">
                                <?= htmlspecialchars($c['payment']) ?>
                            </span>
                        </td>
                        <td style="font-weight:700;text-align:center"><?= $c['total_orders'] ?>x</td>
                        <td class="td-price"><?= formatRp($c['total_spent']) ?></td>
                        <td style="font-size:.78rem;color:var(--gray)">
                            <?= date('d/m/Y', strtotime($c['last_order'])) ?>
                        </td>
                        <td>
                            <span class="badge badge-<?= $label[1] ?>"><?= $label[0] ?></span>
                        </td>
                        <td>
                            <div style="display:flex;gap:.375rem">
                                <button class="btn btn-ghost btn-sm"
                                    onclick='openCustModal(<?= htmlspecialchars(json_encode($c), ENT_QUOTES) ?>)'>
                                    👁 Detail
                                </button>
                                <a href="https://wa.me/<?= WA_NUMBER ?>?text=Halo+<?= urlencode($c['name']) ?>!"
                                   target="_blank" class="btn btn-success btn-sm">💬</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="pagination">
            <span>Menampilkan <?= $total_customers ?> pelanggan</span>
            <div class="page-btns">
                <button class="page-btn active">1</button>
                <button class="page-btn">2</button>
                <button class="page-btn">›</button>
            </div>
        </div>
    </div>
</div>

<!-- Detail Modal -->
<div class="modal-overlay" id="custModal">
    <div class="modal" style="max-width:560px">
        <div class="modal-header">
            <div class="modal-title">👤 Detail Pelanggan</div>
            <button class="modal-close" onclick="closeModal('custModal')">✕</button>
        </div>
        <div class="modal-body" id="custModalBody"></div>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="closeModal('custModal')">Tutup</button>
            <a id="custWaBtn" href="#" target="_blank" class="btn btn-primary">💬 WhatsApp</a>
        </div>
    </div>
</div>

<script>
function openCustModal(c) {
    const spent = 'Rp ' + Number(c.total_spent).toLocaleString('id-ID');
    const label = c.total_spent >= 200000 ? '🥇 Gold' : c.total_spent >= 100000 ? '🥈 Silver' : '🥉 Bronze';
    document.getElementById('custModalBody').innerHTML = `
        <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.5rem;padding:.875rem;background:var(--bg3);border-radius:var(--radius)">
            <div style="width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg,var(--blue),var(--cyan));
                display:flex;align-items:center;justify-content:center;font-weight:900;font-size:1.25rem;flex-shrink:0">
                ${c.name.charAt(0).toUpperCase()}
            </div>
            <div>
                <div style="font-weight:800;font-size:1rem">${c.name}</div>
                <div style="font-size:.75rem;color:var(--gray)">ID Game: ${c.user_id_game}</div>
                <span style="font-size:.7rem;color:var(--warning);font-weight:700">${label}</span>
            </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.625rem;font-size:.85rem">
            ${info('📦 Total Order', c.total_orders + 'x')}
            ${info('💰 Total Spent', spent)}
            ${info('🎮 Game Favorit', c.last_game)}
            ${info('💳 Pembayaran', c.payment)}
            ${info('📅 Terakhir Order', new Date(c.last_order).toLocaleDateString('id-ID'))}
            ${info('🆔 User ID', c.user_id_game)}
        </div>
        <div style="margin-top:1rem">
            <div style="font-size:.78rem;font-weight:700;color:var(--gray2);text-transform:uppercase;letter-spacing:.5px;margin-bottom:.625rem">
                Riwayat Order Terbaru
            </div>
            ${(c.orders||[]).slice(0,4).map(o=>`
            <div style="display:flex;justify-content:space-between;align-items:center;
                padding:.5rem .75rem;background:var(--bg3);border-radius:7px;margin-bottom:4px;font-size:.8rem">
                <div>
                    <span style="font-family:monospace;color:var(--gray)">${o.id}</span>
                    <span style="margin-left:.5rem">${o.icon} ${o.game}</span>
                </div>
                <div style="display:flex;gap:.5rem;align-items:center">
                    <span style="color:var(--cyan);font-weight:700">Rp ${Number(o.price).toLocaleString('id-ID')}</span>
                    <span class="badge badge-${o.status}" style="font-size:.62rem">${o.status}</span>
                </div>
            </div>`).join('')}
        </div>`;
    document.getElementById('custWaBtn').href =
        `https://wa.me/<?= WA_NUMBER ?>?text=${encodeURIComponent('Halo '+c.name+'! Ada yang bisa kami bantu?')}`;
    openModal('custModal');
}
function info(label, val) {
    return `<div style="background:var(--bg3);border-radius:8px;padding:.625rem .75rem">
        <div style="font-size:.7rem;color:var(--gray);margin-bottom:.2rem">${label}</div>
        <div style="font-weight:700">${val}</div></div>`;
}
function exportCSV() {
    const rows = [['No','Nama','Game Favorit','Total Order','Total Spent','Terakhir Order']];
    document.querySelectorAll('#custTable tbody tr').forEach((tr,i) => {
        const td = tr.querySelectorAll('td');
        rows.push([i+1, td[1].textContent.trim(), td[2].textContent.trim(),
                   td[4].textContent.trim(), td[5].textContent.trim(), td[6].textContent.trim()]);
    });
    const csv = rows.map(r=>r.map(c=>'"'+String(c).replace(/"/g,'""')+'"').join(',')).join('\n');
    const a = document.createElement('a');
    a.href = 'data:text/csv;charset=utf-8,\uFEFF' + encodeURIComponent(csv);
    a.download = 'pelanggan-gamestore.csv'; a.click();
    showToast('✅ Export CSV berhasil!');
}
document.getElementById('sortFilter').addEventListener('change', function() {
    const tbody = document.querySelector('#custTable tbody');
    const rows  = [...tbody.querySelectorAll('tr')];
    rows.sort((a, b) => {
        const v = this.value;
        if (v === 'orders') {
            return parseInt(b.querySelectorAll('td')[4].textContent) - parseInt(a.querySelectorAll('td')[4].textContent);
        }
        if (v === 'recent') {
            const da = a.querySelectorAll('td')[6].textContent.split('/').reverse().join('-');
            const db = b.querySelectorAll('td')[6].textContent.split('/').reverse().join('-');
            return db.localeCompare(da);
        }
        return 0;
    });
    rows.forEach(r => tbody.appendChild(r));
});
</script>

<?php require_once 'includes/admin_footer.php'; ?>
