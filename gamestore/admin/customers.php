<?php
require_once 'includes/admin_config.php';
requireLogin();
$page_title  = 'Pelanggan';
$active_menu = 'customers';

$search   = $_GET['q'] ?? '';
$page_num = max(1,(int)($_GET['p']??1));
$per_page = 25; $offset = ($page_num-1)*$per_page;

$where='1=1'; $params=[];
if ($search) { $where="name LIKE ? OR email LIKE ? OR phone LIKE ?";
               $params=["%$search%","%$search%","%$search%"]; }

$total = (int)(db_row("SELECT COUNT(*) AS c FROM customers WHERE $where",$params)['c']??0);
$customers = db_rows("
    SELECT c.*,
           COUNT(DISTINCT o.id)                                        AS total_orders,
           COALESCE(SUM(IF(o.status='completed',o.total,0)),0)         AS total_spent,
           MAX(o.created_at)                                           AS last_order
    FROM customers c
    LEFT JOIN orders o ON o.customer_id=c.id
    WHERE $where
    GROUP BY c.id
    ORDER BY total_spent DESC
    LIMIT $per_page OFFSET $offset
", $params);

$stats = db_row("SELECT COUNT(*) AS total,
    SUM(is_active=1) AS active,
    SUM(created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) AS new_month
    FROM customers");

require_once 'includes/admin_layout.php';
?>
<div class="page-content">
<div class="stats-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:1.25rem">
    <div class="stat-card" style="--accent-color:var(--blue)">
        <span class="stat-icon">👥</span>
        <div class="stat-label">Total Pelanggan</div>
        <div class="stat-value"><?= $stats['total'] ?></div>
        <div class="stat-change up">Terdaftar</div>
    </div>
    <div class="stat-card" style="--accent-color:var(--success)">
        <span class="stat-icon">✅</span>
        <div class="stat-label">Akun Aktif</div>
        <div class="stat-value"><?= $stats['active'] ?></div>
        <div class="stat-change up">Status aktif</div>
    </div>
    <div class="stat-card" style="--accent-color:var(--cyan)">
        <span class="stat-icon">🆕</span>
        <div class="stat-label">Baru Bulan Ini</div>
        <div class="stat-value"><?= $stats['new_month'] ?></div>
        <div class="stat-change up">30 hari terakhir</div>
    </div>
</div>

<div class="toolbar">
    <form method="GET" style="display:contents">
        <div class="search-box">
            <span class="ico">🔍</span>
            <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Cari nama, email, WA...">
        </div>
        <button type="submit" class="btn btn-ghost btn-sm">Cari</button>
    </form>
    <button class="btn btn-primary btn-sm" onclick="exportCustomers()">📥 Export CSV</button>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">👥 Pelanggan <span style="color:var(--gray);font-weight:400;font-size:.85rem">(<?= $total ?>)</span></div>
    </div>
    <div class="table-wrap">
        <table id="custTable">
            <thead><tr><th>#</th><th>Pelanggan</th><th>WhatsApp</th><th>Total Order</th><th>Total Spent</th><th>Terakhir Order</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($customers as $i=>$c):
                $spent = (int)$c['total_spent'];
                $label = $spent>=200000?['🥇 Gold','warning']:($spent>=100000?['🥈 Silver','processing']:['🥉 Bronze','inactive']);
            ?>
            <tr>
                <td class="td-id"><?= ($page_num-1)*$per_page+$i+1 ?></td>
                <td>
                    <div style="display:flex;align-items:center;gap:.625rem">
                        <div style="width:34px;height:34px;border-radius:50%;
                            background:linear-gradient(135deg,var(--blue),var(--cyan));
                            display:flex;align-items:center;justify-content:center;
                            font-weight:800;font-size:.85rem;flex-shrink:0"><?= strtoupper($c['name'][0]) ?></div>
                        <div>
                            <div style="font-weight:700;font-size:.875rem"><?= htmlspecialchars($c['name']) ?></div>
                            <div style="font-size:.72rem;color:var(--gray)"><?= htmlspecialchars($c['email']??'—') ?></div>
                        </div>
                    </div>
                </td>
                <td style="font-size:.82rem"><?= htmlspecialchars($c['phone']??'—') ?></td>
                <td style="font-weight:700;text-align:center"><?= $c['total_orders'] ?>x</td>
                <td style="font-weight:700;color:var(--cyan)"><?= formatRp($spent) ?></td>
                <td style="font-size:.78rem;color:var(--gray)"><?= $c['last_order']?date('d/m/Y',strtotime($c['last_order'])):'—' ?></td>
                <td><span class="badge badge-<?= $c['is_active']?'active':'inactive' ?>"><span class="badge-dot"></span><?= $c['is_active']?'Aktif':'Nonaktif' ?></span></td>
                <td>
                    <div style="display:flex;gap:.35rem">
                        <button class="btn btn-ghost btn-sm" onclick='openCust(<?= json_encode($c,JSON_HEX_APOS) ?>)'>👁</button>
                        <?php if ($c['phone']): ?>
                        <a href="https://wa.me/62<?= ltrim(preg_replace('/\D/','',$c['phone']),'0') ?>" target="_blank" class="btn btn-success btn-sm">💬</a>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if ($total>$per_page): ?>
    <div class="pagination">
        <span>Halaman <?= $page_num ?> dari <?= ceil($total/$per_page) ?></span>
        <div class="page-btns">
            <?php for ($i=1;$i<=ceil($total/$per_page);$i++): ?>
            <a href="?p=<?= $i ?>&q=<?= urlencode($search) ?>" class="page-btn <?= $i===$page_num?'active':'' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
</div>

<div class="modal-overlay" id="custModal">
<div class="modal" style="max-width:520px">
    <div class="modal-header">
        <div class="modal-title">👤 Detail Pelanggan</div>
        <button class="modal-close" onclick="closeModal('custModal')">✕</button>
    </div>
    <div class="modal-body" id="custModalBody"></div>
    <div class="modal-footer">
        <button class="btn btn-ghost" onclick="closeModal('custModal')">Tutup</button>
        <a id="custWaLink" href="#" target="_blank" class="btn btn-primary">💬 WhatsApp</a>
    </div>
</div>
</div>

<script>
function openCust(c) {
    const spent = 'Rp '+Number(c.total_spent).toLocaleString('id-ID');
    document.getElementById('custModalBody').innerHTML = `
        <div style="display:flex;align-items:center;gap:.875rem;padding:.875rem;background:var(--bg3);border-radius:var(--radius);margin-bottom:1rem">
            <div style="width:48px;height:48px;border-radius:50%;background:linear-gradient(135deg,var(--blue),var(--cyan));
                display:flex;align-items:center;justify-content:center;font-weight:900;font-size:1.3rem">
                ${c.name?.charAt(0).toUpperCase()}
            </div>
            <div>
                <div style="font-weight:800;font-size:.975rem">${c.name}</div>
                <div style="font-size:.78rem;color:var(--gray)">${c.email||'—'}</div>
            </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.625rem;font-size:.85rem">
            ${box('📱 WhatsApp', c.phone||'—')}
            ${box('📦 Total Order', c.total_orders+'x')}
            ${box('💰 Total Spent', spent)}
            ${box('📅 Daftar', c.created_at?.slice(0,10)||'—')}
            ${box('✅ Status', c.is_active ? 'Aktif' : 'Nonaktif')}
            ${box('🆔 ID', '#'+c.id)}
        </div>`;
    const wa = '62'+String(c.phone||'').replace(/\D/,'').replace(/^0/,'');
    document.getElementById('custWaLink').href = `https://wa.me/${wa}?text=${encodeURIComponent('Halo '+c.name+'! Ada yang bisa kami bantu?')}`;
    openModal('custModal');
}
function box(lbl,val){return `<div style="background:var(--bg3);border-radius:8px;padding:.625rem .75rem"><div style="font-size:.7rem;color:var(--gray);margin-bottom:.2rem">${lbl}</div><div style="font-weight:600">${val}</div></div>`;}
function exportCustomers(){
    const r=[['No','Nama','Email','WA','Order','Spent','Status']];
    document.querySelectorAll('#custTable tbody tr').forEach((tr,i)=>{
        const td=tr.querySelectorAll('td');
        r.push([i+1,td[1].textContent.trim(),td[1].querySelectorAll('div')[1]?.textContent.trim()||'',td[2].textContent.trim(),td[3].textContent.trim(),td[4].textContent.trim(),td[6].textContent.trim()]);
    });
    const csv=r.map(row=>row.map(c=>'"'+String(c).replace(/"/g,'""')+'"').join(',')).join('\n');
    const a=document.createElement('a');a.href='data:text/csv;charset=utf-8,\uFEFF'+encodeURIComponent(csv);a.download='customers.csv';a.click();
    showToast('✅ Export berhasil!');
}
</script>
<?php require_once 'includes/admin_footer.php'; ?>
