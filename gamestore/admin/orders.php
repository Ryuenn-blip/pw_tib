<?php
require_once 'includes/admin_config.php';
requireLogin();
$page_title  = 'Manajemen Pesanan';
$active_menu = 'orders';

// Update status via POST
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['update_status'])) {
    // CSRF check
    if (!empty($_POST) && !csrf_verify()) { header("Location: " . $_SERVER['PHP_SELF'] . "?csrf_error=1"); exit; }
    $id  = $_POST['order_id'] ?? '';
    $st  = $_POST['status']   ?? '';
    $allowed = ['pending','processing','completed','cancelled','refunded'];
    if ($id && in_array($st,$allowed)) {
        db_exec("UPDATE orders SET status=?, updated_at=NOW() WHERE id=?", [$st,$id]);
        if ($st==='completed') db_exec("UPDATE orders SET completed_at=NOW() WHERE id=? AND completed_at IS NULL",[$id]);
        log_activity('order_status_update', "Order $id diubah ke status: $st");
    }
    header('Location: orders.php'); exit;
}

// Filters
$filter_status = $_GET['status'] ?? '';
$filter_game   = $_GET['game']   ?? '';
$search        = $_GET['q']      ?? '';
$page_num      = max(1,(int)($_GET['p']??1));
$per_page      = 20;
$offset        = ($page_num-1)*$per_page;

$where = ['1=1']; $params = [];
if ($filter_status) { $where[]="o.status=?"; $params[]=$filter_status; }
if ($filter_game)   { $where[]="p.id=?";     $params[]=(int)$filter_game; }
if ($search)        { $where[]="(o.id LIKE ? OR o.customer_name LIKE ? OR o.game_user_id LIKE ?)";
                      $params[]="%$search%"; $params[]="%$search%"; $params[]="%$search%"; }
$sql_where = implode(' AND ', $where);

$total_filtered = (int)(db_row("SELECT COUNT(*) AS c FROM orders o JOIN products p ON p.id=o.product_id WHERE $sql_where",$params)['c']??0);
$orders_list = db_rows("SELECT o.*, p.name AS game_name, p.icon AS game_icon, p.slug AS game_slug
    FROM orders o JOIN products p ON p.id=o.product_id
    WHERE $sql_where ORDER BY o.created_at DESC LIMIT $per_page OFFSET $offset",$params);
$all_products = db_rows("SELECT id,name FROM products ORDER BY name");

// Detail modal
$detail_order = null;
if (!empty($_GET['id'])) {
    $detail_order = db_row("SELECT o.*, p.name AS game_name, p.icon AS game_icon
        FROM orders o JOIN products p ON p.id=o.product_id WHERE o.id=?", [$_GET['id']]);
}

require_once 'includes/admin_layout.php';
$stmap = [
    'pending'    =>['Pending',    'badge-pending'],
    'processing' =>['Diproses',   'badge-processing'],
    'completed'  =>['Selesai',    'badge-completed'],
    'cancelled'  =>['Dibatalkan', 'badge-cancelled'],
    'refunded'   =>['Refund',     'badge-inactive'],
];
?>
<div class="page-content">
<div class="toolbar">
    <form method="GET" style="display:contents">
        <div class="search-box">
            <span class="ico">🔍</span>
            <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Cari ID, nama, User ID...">
        </div>
        <select class="filter-select" name="status" onchange="this.form.submit()">
            <option value="">Semua Status</option>
            <?php foreach ($stmap as $k=>[$l,$_]): ?>
            <option value="<?= $k ?>" <?= $filter_status===$k?'selected':'' ?>><?= $l ?></option>
            <?php endforeach; ?>
        </select>
        <select class="filter-select" name="game" onchange="this.form.submit()">
            <option value="">Semua Game</option>
            <?php foreach ($all_products as $pr): ?>
            <option value="<?= $pr['id'] ?>" <?= $filter_game==(string)$pr['id']?'selected':'' ?>><?= htmlspecialchars($pr['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-ghost btn-sm">🔍 Filter</button>
    </form>
    <button class="btn btn-primary btn-sm" onclick="exportCSV()">📥 Export CSV</button>
</div>

<!-- Summary badges -->
<div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:1rem">
    <?php foreach ($stmap as $k=>[$lbl,$bcls]): 
        $cnt = (int)(db_row("SELECT COUNT(*) AS c FROM orders WHERE status=?",[$k])['c']??0); ?>
    <a href="orders.php?status=<?= $k ?>" style="text-decoration:none">
        <span class="badge <?= $bcls ?>" style="cursor:pointer;<?= $filter_status===$k?'box-shadow:0 0 0 2px var(--blue)':'' ?>">
            <span class="badge-dot"></span><?= $lbl ?>: <?= $cnt ?>
        </span>
    </a>
    <?php endforeach; ?>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">📋 Pesanan
            <span style="color:var(--gray);font-weight:400;font-size:.85rem">(<?= $total_filtered ?> order)</span>
        </div>
    </div>
    <div class="table-wrap">
        <table id="ordersTable">
            <thead><tr><th>ID</th><th>Tanggal</th><th>Pelanggan</th><th>Game</th><th>Paket</th><th>Bayar</th><th>Total</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($orders_list as $o): [$lbl,$bcls]=$stmap[$o['status']]??[$o['status'],'badge-inactive']; ?>
            <tr data-status="<?= $o['status'] ?>">
                <td class="td-id"><?= $o['id'] ?></td>
                <td style="font-size:.75rem;color:var(--gray)">
                    <?= date('d/m/Y', strtotime($o['created_at'])) ?><br>
                    <span style="font-size:.7rem"><?= date('H:i', strtotime($o['created_at'])) ?></span>
                </td>
                <td>
                    <div style="font-weight:600;font-size:.85rem"><?= htmlspecialchars($o['customer_name']) ?></div>
                    <div style="font-size:.72rem;color:var(--gray)">WA: <?= htmlspecialchars($o['customer_wa']??'—') ?></div>
                </td>
                <td><div style="display:flex;align-items:center;gap:.4rem">
                    <span><?= $o['game_icon'] ?></span>
                    <span style="font-size:.83rem;font-weight:600"><?= htmlspecialchars($o['game_name']) ?></span>
                </div></td>
                <td style="font-size:.82rem"><?= $o['package_amount'] ?> <span style="color:var(--gray)"><?= htmlspecialchars($o['currency']??'') ?></span></td>
                <td><span style="font-size:.75rem;background:var(--bg3);padding:.2rem .5rem;border-radius:5px"><?= htmlspecialchars($o['payment_method']) ?></span></td>
                <td style="font-weight:700;color:var(--cyan)"><?= formatRp($o['total']) ?></td>
                <td>
                    <form method="POST" style="margin:0">
                        <input type="hidden" name="update_status" value="1">
                    <?= csrf_field() ?>
                        <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                        <select name="status" class="filter-select" style="padding:.3rem .5rem;font-size:.75rem"
                                onchange="this.form.submit()">
                            <?php foreach ($stmap as $k=>[$l,$_]): ?>
                            <option value="<?= $k ?>" <?= $o['status']===$k?'selected':'' ?>><?= $l ?></option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </td>
                <td>
                    <div style="display:flex;gap:.3rem">
                        <a href="orders.php?id=<?= $o['id'] ?>" class="btn btn-ghost btn-sm">👁</a>
                        <a href="https://wa.me/<?= preg_replace('/\D/','',$o['customer_wa']??'') ?>?text=<?= urlencode('Halo '.$o['customer_name'].'! Order '.$o['id'].' sudah kami proses ✅') ?>"
                           target="_blank" class="btn btn-success btn-sm">💬</a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if ($total_filtered > $per_page): ?>
    <div class="pagination">
        <span>Halaman <?= $page_num ?> dari <?= ceil($total_filtered/$per_page) ?></span>
        <div class="page-btns">
            <?php for ($i=1; $i<=min(ceil($total_filtered/$per_page),7); $i++): ?>
            <a href="?p=<?= $i ?>&status=<?= urlencode($filter_status) ?>&game=<?= $filter_game ?>&q=<?= urlencode($search) ?>"
               class="page-btn <?= $i===$page_num?'active':'' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
</div>

<!-- Detail Modal -->
<?php if ($detail_order): ?>
<div class="modal-overlay open" id="orderDetailModal" onclick="if(event.target===this)this.classList.remove('open')">
<div class="modal">
    <div class="modal-header">
        <div class="modal-title">📋 Order <?= $detail_order['id'] ?></div>
        <a href="orders.php" class="modal-close">✕</a>
    </div>
    <div class="modal-body">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;font-size:.875rem">
            <?php
            function infoBox($label,$val){ return "<div style='background:var(--bg3);border-radius:8px;padding:.75rem'>
                <div style='font-size:.7rem;color:var(--gray);margin-bottom:.2rem'>$label</div>
                <div style='font-weight:600'>$val</div></div>"; }
            echo infoBox('ID Order',     $detail_order['id']);
            echo infoBox('Tanggal',      date('d/m/Y H:i', strtotime($detail_order['created_at'])));
            echo infoBox('Pelanggan',    htmlspecialchars($detail_order['customer_name']));
            echo infoBox('WhatsApp',     htmlspecialchars($detail_order['customer_wa']??'—'));
            echo infoBox('Game',         $detail_order['game_icon'].' '.htmlspecialchars($detail_order['game_name']));
            echo infoBox('Paket',        $detail_order['package_amount'].' '.htmlspecialchars($detail_order['currency']??''));
            echo infoBox('User ID Game', htmlspecialchars($detail_order['game_user_id']));
            echo infoBox('Pembayaran',   htmlspecialchars($detail_order['payment_method']));
            echo infoBox('Diskon',       formatRp((int)($detail_order['discount']??0)));
            echo infoBox('Total',        '<span style="color:var(--cyan);font-size:1rem">'.formatRp($detail_order['total']).'</span>');
            ?>
        </div>
        <?php if (!empty($detail_order['note'])): ?>
        <div style="margin-top:.875rem;background:var(--bg3);border-radius:8px;padding:.875rem">
            <div style="font-size:.72rem;color:var(--gray);margin-bottom:.3rem">Catatan Customer</div>
            <div style="font-size:.85rem"><?= htmlspecialchars($detail_order['note']) ?></div>
        </div>
        <?php endif; ?>
        <?php if (!empty($detail_order['proof_image'])): ?>
        <div style="margin-top:.875rem">
            <div style="font-size:.78rem;font-weight:700;color:var(--gray2);margin-bottom:.5rem">Bukti Pembayaran</div>
            <img src="../uploads/proofs/<?= htmlspecialchars($detail_order['proof_image']) ? loading="lazy">" alt="Bukti Pembayaran"
                 style="max-width:100%;border-radius:var(--radius);border:1px solid var(--border)"
                 alt="Bukti bayar">
        </div>
        <?php endif; ?>
    </div>
    <div class="modal-footer">
        <a href="orders.php" class="btn btn-ghost">Tutup</a>
        <a href="https://wa.me/<?= preg_replace('/\D/','',$detail_order['customer_wa']??'') ?>?text=<?= urlencode('Halo '.$detail_order['customer_name'].'! Pesanan '.$detail_order['id'].' sudah diproses. Terima kasih 🙏') ?>"
           target="_blank" class="btn btn-primary">💬 WA Pelanggan</a>
    </div>
</div>
</div>
<?php endif; ?>

<script>
function exportCSV() {
    const rows = [['ID','Tanggal','Nama','Game','Paket','Metode','Total','Status']];
    document.querySelectorAll('#ordersTable tbody tr').forEach(tr => {
        const td = tr.querySelectorAll('td');
        rows.push([td[0].textContent.trim(), td[1].textContent.trim(),
                   td[2].textContent.trim(), td[3].textContent.trim(),
                   td[4].textContent.trim(), td[5].textContent.trim(),
                   td[6].textContent.trim(), tr.dataset.status]);
    });
    const csv = rows.map(r=>r.map(c=>'"'+String(c).replace(/"/g,'""')+'"').join(',')).join('\n');
    const a=document.createElement('a');
    a.href='data:text/csv;charset=utf-8,\uFEFF'+encodeURIComponent(csv);
    a.download='orders-gamestore.csv'; a.click();
    showToast('✅ Export CSV berhasil!');
}
</script>
<?php require_once 'includes/admin_footer.php'; ?>
