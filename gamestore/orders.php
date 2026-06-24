<?php
require_once 'includes/admin_config.php';
requireLogin();
$page_title  = 'Manajemen Pesanan';
$active_menu = 'orders';
require_once 'includes/admin_layout.php';

// Detail modal data
$detail_id = $_GET['id'] ?? null;
$detail_order = null;
if ($detail_id) {
    foreach ($orders as $o) {
        if ($o['id'] === $detail_id) { $detail_order = $o; break; }
    }
}

$status_labels = [
    'pending'    => ['label'=>'Pending',    'class'=>'badge-pending'],
    'processing' => ['label'=>'Processing', 'class'=>'badge-processing'],
    'completed'  => ['label'=>'Selesai',    'class'=>'badge-completed'],
    'cancelled'  => ['label'=>'Dibatalkan', 'class'=>'badge-cancelled'],
];
?>

<div class="page-content">

    <!-- Toolbar -->
    <div class="toolbar">
        <div class="search-box">
            <span class="ico">🔍</span>
            <input type="text" id="tableSearch" placeholder="Cari ID, nama, game...">
        </div>
        <select class="filter-select" id="statusFilter">
            <option value="">Semua Status</option>
            <option value="pending">Pending</option>
            <option value="processing">Processing</option>
            <option value="completed">Selesai</option>
            <option value="cancelled">Dibatalkan</option>
        </select>
        <select class="filter-select" id="gameFilter">
            <option value="">Semua Game</option>
            <?php foreach ($game_list as $gl): ?>
            <option><?= $gl['name'] ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn btn-primary btn-sm" onclick="exportTable()">📥 Export CSV</button>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">📋 Daftar Pesanan <span style="color:var(--gray);font-weight:400;font-size:.85rem">(<?= count($orders) ?> total)</span></div>
            <div style="display:flex;gap:.5rem">
                <?php foreach ($status_labels as $sk=>$sv): ?>
                <span class="badge badge-<?= $sk ?>"><?= count(array_filter($orders,fn($o)=>$o['status']===$sk)) ?> <?= $sv['label'] ?></span>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="table-wrap">
            <table id="ordersTable">
                <thead>
                    <tr>
                        <th>ID Order</th>
                        <th>Tanggal</th>
                        <th>Pelanggan</th>
                        <th>Game</th>
                        <th>Paket</th>
                        <th>Pembayaran</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $o): ?>
                    <tr data-status="<?= $o['status'] ?>">
                        <td class="td-id"><?= $o['id'] ?></td>
                        <td style="font-size:.78rem;color:var(--gray);white-space:nowrap">
                            <?= date('d/m/Y', strtotime($o['date'])) ?><br>
                            <span style="font-size:.7rem"><?= date('H:i', strtotime($o['date'])) ?></span>
                        </td>
                        <td>
                            <div style="font-weight:600;font-size:.85rem"><?= htmlspecialchars($o['name']) ?></div>
                            <div style="font-size:.72rem;color:var(--gray)">ID: <?= $o['user_id'] ?></div>
                        </td>
                        <td>
                            <div class="td-game">
                                <span class="td-icon"><?= $o['icon'] ?></span>
                                <span class="td-name"><?= htmlspecialchars($o['game']) ?></span>
                            </div>
                        </td>
                        <td style="font-size:.82rem">
                            <strong><?= $o['amount'] ?></strong>
                            <span style="color:var(--gray)"> <?= $o['currency'] ?></span>
                        </td>
                        <td>
                            <span style="font-size:.78rem;background:var(--bg3);padding:.2rem .5rem;border-radius:5px">
                                <?= $o['payment'] ?>
                            </span>
                        </td>
                        <td class="td-price"><?= formatRp($o['price']) ?></td>
                        <td>
                            <select class="filter-select status-select" data-order="<?= $o['id'] ?>"
                                    style="padding:.3rem .5rem;font-size:.75rem">
                                <?php foreach ($status_labels as $sk=>$sv): ?>
                                <option value="<?= $sk ?>" <?= $o['status']===$sk?'selected':'' ?>><?= $sv['label'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <div style="display:flex;gap:.4rem">
                                <button class="btn btn-ghost btn-sm"
                                        onclick="openOrderModal(<?= htmlspecialchars(json_encode($o)) ?>)">
                                    👁
                                </button>
                                <a href="https://wa.me/<?= WA_NUMBER ?>?text=<?= urlencode('Halo '.$o['name'].', pesanan '.$o['id'].' sudah kami proses!') ?>"
                                   target="_blank" class="btn btn-success btn-sm">💬</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="pagination">
            <span>Menampilkan <?= count($orders) ?> pesanan</span>
            <div class="page-btns">
                <button class="page-btn active">1</button>
                <button class="page-btn">2</button>
                <button class="page-btn">3</button>
                <button class="page-btn">›</button>
            </div>
        </div>
    </div>
</div>

<!-- Order Detail Modal -->
<div class="modal-overlay" id="orderModal">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title">📋 Detail Pesanan</div>
            <button class="modal-close" onclick="closeModal('orderModal')">✕</button>
        </div>
        <div class="modal-body" id="orderModalBody">
            <!-- filled by JS -->
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="closeModal('orderModal')">Tutup</button>
            <button class="btn btn-primary" id="waOrderBtn">💬 WhatsApp Pelanggan</button>
        </div>
    </div>
</div>

<script>
// Game filter
document.getElementById('gameFilter')?.addEventListener('change', function () {
    const val = this.value;
    document.querySelectorAll('#ordersTable tbody tr').forEach(row => {
        const gameCell = row.querySelector('.td-name')?.textContent || '';
        row.style.display = (!val || gameCell.includes(val)) ? '' : 'none';
    });
});

function openOrderModal(o) {
    const statusColors = {pending:'var(--warning)',processing:'var(--blue-l)',completed:'var(--success)',cancelled:'var(--danger)'};
    document.getElementById('orderModalBody').innerHTML = `
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;font-size:.875rem">
            ${row('🆔 ID Order', o.id)}
            ${row('📅 Tanggal', o.date)}
            ${row('👤 Pelanggan', o.name)}
            ${row('🆔 User ID Game', o.user_id)}
            ${row('🎮 Game', o.icon + ' ' + o.game)}
            ${row('💎 Paket', o.amount + ' ' + o.currency)}
            ${row('💳 Pembayaran', o.payment)}
            ${row('💰 Total', 'Rp ' + Number(o.price).toLocaleString('id-ID'))}
        </div>
        <div style="margin-top:1rem;padding:.875rem;background:var(--bg3);border-radius:var(--radius)">
            <span style="font-size:.78rem;color:var(--gray)">Status</span><br>
            <strong style="color:${statusColors[o.status]??'var(--white)'};font-size:1rem">${o.status.toUpperCase()}</strong>
        </div>`;
    document.getElementById('waOrderBtn').onclick = () => {
        window.open('https://wa.me/<?= WA_NUMBER ?>?text=' + encodeURIComponent('Halo ' + o.name + ', pesanan ' + o.id + ' sudah kami proses. Terima kasih!'), '_blank');
    };
    openModal('orderModal');
}
function row(label, value) {
    return `<div style="background:var(--bg3);border-radius:8px;padding:.75rem">
        <div style="font-size:.72rem;color:var(--gray);margin-bottom:.25rem">${label}</div>
        <div style="font-weight:600">${value}</div>
    </div>`;
}

function exportTable() {
    const rows = [['ID','Tanggal','Nama','Game','Paket','Harga','Status']];
    document.querySelectorAll('#ordersTable tbody tr').forEach(tr => {
        if (tr.style.display === 'none') return;
        const cells = tr.querySelectorAll('td');
        rows.push([cells[0].textContent.trim(), cells[1].textContent.trim(),
                   cells[2].textContent.trim(), cells[3].textContent.trim(),
                   cells[4].textContent.trim(), cells[6].textContent.trim(),
                   cells[7].querySelector('select')?.value || '']);
    });
    const csv = rows.map(r => r.map(c => '"'+c.replace(/"/g,'""')+'"').join(',')).join('\n');
    const a = document.createElement('a');
    a.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv);
    a.download = 'pesanan-gamestore.csv';
    a.click();
    showToast('✅ CSV berhasil diexport!');
}

// Auto-open modal if ?id= is set
<?php if ($detail_order): ?>
openOrderModal(<?= json_encode($detail_order) ?>);
<?php endif; ?>
</script>

<?php require_once 'includes/admin_footer.php'; ?>