<?php
require_once 'includes/admin_config.php';
requireLogin();
$page_title  = 'Log Aktivitas';
$active_menu = 'activity';

// Handle clear all
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['clear_all'])) {
    // CSRF check
    if (!empty($_POST) && !csrf_verify()) { header("Location: " . $_SERVER['PHP_SELF'] . "?csrf_error=1"); exit; }
    $count = (int)(db_row("SELECT COUNT(*) AS c FROM activity_logs")['c'] ?? 0);
    db_exec("DELETE FROM activity_logs");
    log_activity('activity_clear', "Semua log ($count entri) dihapus oleh admin");
    header('Location: activity.php?cleared=1'); exit;
}

// Filter
$filter_action = $_GET['action_type'] ?? '';
$search        = $_GET['q'] ?? '';
$page_num      = max(1,(int)($_GET['p'] ?? 1));
$per_page      = 30;
$offset        = ($page_num-1)*$per_page;

// Build query
$where  = ['1=1'];
$params = [];
if ($filter_action) { $where[] = "al.action LIKE ?"; $params[] = "%$filter_action%"; }
if ($search)        { $where[] = "(al.action LIKE ? OR al.detail LIKE ? OR a.username LIKE ?)";
                      $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%"; }
$sql_where = implode(' AND ', $where);

$total = (int)(db_row("SELECT COUNT(*) AS c FROM activity_logs al LEFT JOIN admins a ON a.id=al.admin_id WHERE $sql_where", $params)['c'] ?? 0);
$logs  = db_rows("SELECT al.*, a.username, a.name AS admin_name
    FROM activity_logs al
    LEFT JOIN admins a ON a.id = al.admin_id
    WHERE $sql_where
    ORDER BY al.created_at DESC
    LIMIT $per_page OFFSET $offset", $params);

// Stats
$today_count = (int)(db_row("SELECT COUNT(*) AS c FROM activity_logs WHERE DATE(created_at) = CURDATE()")['c'] ?? 0);
$week_count  = (int)(db_row("SELECT COUNT(*) AS c FROM activity_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")['c'] ?? 0);
$admin_count = (int)(db_row("SELECT COUNT(DISTINCT admin_id) AS c FROM activity_logs WHERE DATE(created_at) = CURDATE()")['c'] ?? 0);

// Action types for filter
$action_types = db_rows("SELECT DISTINCT action FROM activity_logs ORDER BY action");

require_once 'includes/admin_layout.php';

// Color & icon map per action
function actionStyle(string $action): array {
    if (strpos($action,'login')   !== false) return ['💚','Login',   'rgba(34,197,94,.1)',  'var(--success)'];
    if (strpos($action,'logout')  !== false) return ['🔴','Logout',  'rgba(239,68,68,.08)', 'var(--danger)'];
    if (strpos($action,'order')   !== false) return ['💙','Order',   'rgba(37,99,235,.1)',  '#60A5FA'];
    if (strpos($action,'product') !== false) return ['🟣','Produk',  'rgba(139,92,246,.1)', '#A78BFA'];
    if (strpos($action,'promo')   !== false) return ['🟡','Promo',   'rgba(245,158,11,.1)', 'var(--warning)'];
    if (strpos($action,'setting') !== false) return ['⚙️','Setting', 'rgba(6,182,212,.1)',  'var(--cyan)'];
    if (strpos($action,'delete')  !== false) return ['🗑','Hapus',   'rgba(239,68,68,.1)',  'var(--danger)'];
    if (strpos($action,'chat')    !== false) return ['💬','Chat',    'rgba(37,211,102,.1)', '#25D366'];
    if (strpos($action,'profile') !== false) return ['👤','Profil',  'rgba(37,99,235,.1)',  'var(--blue)'];
    if (strpos($action,'password')!== false) return ['🔐','Password','rgba(245,158,11,.1)', 'var(--warning)'];
    return ['📌','Aksi', 'rgba(48,54,61,.6)', 'var(--gray)'];
}
?>

<div class="page-content">

<?php if (!empty($_GET['cleared'])): ?>
<div style="background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.25);border-radius:var(--radius);
    padding:.875rem 1.25rem;margin-bottom:1.25rem;color:var(--success);font-size:.875rem;font-weight:600">
    ✅ Semua log aktivitas berhasil dihapus!
</div>
<?php endif; ?>

<!-- Stats -->
<div class="stats-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:1.25rem">
    <div class="stat-card" style="--accent-color:var(--blue)">
        <span class="stat-icon">📋</span>
        <div class="stat-label">Aktivitas Hari Ini</div>
        <div class="stat-value"><?= $today_count ?></div>
        <div class="stat-change up">Total log hari ini</div>
    </div>
    <div class="stat-card" style="--accent-color:var(--cyan)">
        <span class="stat-icon">📅</span>
        <div class="stat-label">7 Hari Terakhir</div>
        <div class="stat-value"><?= $week_count ?></div>
        <div class="stat-change up">Aktivitas mingguan</div>
    </div>
    <div class="stat-card" style="--accent-color:var(--success)">
        <span class="stat-icon">👤</span>
        <div class="stat-label">Admin Aktif Hari Ini</div>
        <div class="stat-value"><?= $admin_count ?></div>
        <div class="stat-change up">Unik admin login</div>
    </div>
</div>

<!-- Filter -->
<div class="toolbar">
    <form method="GET" style="display:contents">
        <div class="search-box">
            <span class="ico">🔍</span>
            <input type="text" name="q" id="tableSearch" value="<?= htmlspecialchars($search) ?>" placeholder="Cari aksi, detail, admin...">
        </div>
        <select name="action_type" class="filter-select" onchange="this.form.submit()">
            <option value="">Semua Aksi</option>
            <?php foreach ($action_types as $at): ?>
            <option value="<?= htmlspecialchars($at['action']) ?>"
                <?= $filter_action === $at['action'] ? 'selected' : '' ?>>
                <?= htmlspecialchars(ucfirst($at['action'])) ?>
            </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-ghost btn-sm">🔍 Filter</button>
        <a href="activity.php" class="btn btn-ghost btn-sm">↩ Reset</a>
    </form>
    <form method="POST" action="activity.php" style="display:inline"
          onsubmit="return confirm('Hapus semua log aktivitas? Tindakan ini tidak bisa dibatalkan!')">
        <input type="hidden" name="clear_all" value="1">
                    <?= csrf_field() ?>
        <button type="submit" class="btn btn-danger btn-sm">🗑 Hapus Semua Log</button>
    </form>
</div>

<!-- Log Timeline -->
<div class="card">
    <div class="card-header">
        <div class="card-title">📜 Log Aktivitas
            <span style="color:var(--gray);font-weight:400;font-size:.85rem">(<?= $total ?> total)</span>
        </div>
        <button class="btn btn-ghost btn-sm" onclick="exportLog()">📥 Export CSV</button>
    </div>

    <?php if (empty($logs)): ?>
    <div style="text-align:center;padding:3rem;color:var(--gray)">
        <div style="font-size:3rem;margin-bottom:.75rem">📋</div>
        <div style="font-weight:600;color:var(--white);margin-bottom:.5rem">Belum ada log aktivitas</div>
        <p style="font-size:.85rem">Aktivitas admin akan tercatat secara otomatis di sini.</p>
    </div>
    <?php else: ?>
    <div style="padding:.5rem 1.25rem">
        <?php
        $current_date = '';
        foreach ($logs as $log):
            [$ico,$lbl,$bg,$color] = actionStyle($log['action']);
            $log_date = date('d F Y', strtotime($log['created_at']));
            $log_time = date('H:i:s', strtotime($log['created_at']));
        ?>
        <?php if ($log_date !== $current_date): $current_date = $log_date; ?>
        <div style="display:flex;align-items:center;gap:.75rem;margin:.875rem 0 .5rem">
            <div style="height:1px;flex:1;background:var(--border)"></div>
            <span style="font-size:.72rem;font-weight:700;color:var(--gray2);text-transform:uppercase;
                letter-spacing:.5px;white-space:nowrap">📅 <?= $log_date ?></span>
            <div style="height:1px;flex:1;background:var(--border)"></div>
        </div>
        <?php endif; ?>

        <div style="display:flex;align-items:flex-start;gap:.875rem;padding:.6rem .25rem;
            border-bottom:1px solid rgba(48,54,61,.35)">
            <!-- Icon -->
            <div style="width:34px;height:34px;border-radius:8px;flex-shrink:0;
                background:<?= $bg ?>;border:1px solid <?= $color ?>33;
                display:flex;align-items:center;justify-content:center;font-size:1rem;margin-top:.1rem">
                <?= $ico ?>
            </div>
            <!-- Content -->
            <div style="flex:1;min-width:0">
                <div style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;margin-bottom:.2rem">
                    <span style="font-weight:700;font-size:.875rem;color:<?= $color ?>">
                        <?= htmlspecialchars(ucfirst($log['action'])) ?>
                    </span>
                    <?php if ($log['admin_name']): ?>
                    <span style="font-size:.72rem;background:var(--bg3);border:1px solid var(--border);
                        padding:.1rem .5rem;border-radius:5px;color:var(--gray)">
                        👤 <?= htmlspecialchars($log['admin_name']) ?>
                        (<?= htmlspecialchars($log['username'] ?? '') ?>)
                    </span>
                    <?php endif; ?>
                    <?php if ($log['ip']): ?>
                    <span style="font-size:.68rem;color:var(--gray2);font-family:monospace">
                        🌐 <?= htmlspecialchars($log['ip']) ?>
                    </span>
                    <?php endif; ?>
                </div>
                <?php if (!empty($log['detail'])): ?>
                <div style="font-size:.8rem;color:var(--gray);line-height:1.5">
                    <?= htmlspecialchars($log['detail']) ?>
                </div>
                <?php endif; ?>
            </div>
            <!-- Time -->
            <div style="font-size:.72rem;color:var(--gray2);flex-shrink:0;white-space:nowrap;
                font-family:monospace;margin-top:.1rem">
                <?= $log_time ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($total > $per_page): ?>
    <div class="pagination">
        <span>Halaman <?= $page_num ?> dari <?= ceil($total/$per_page) ?></span>
        <div class="page-btns">
            <?php if ($page_num > 1): ?>
            <a href="?p=<?= $page_num-1 ?>&q=<?= urlencode($search) ?>&action_type=<?= urlencode($filter_action) ?>" class="page-btn">‹</a>
            <?php endif; ?>
            <?php for ($i = max(1,$page_num-2); $i <= min(ceil($total/$per_page),$page_num+2); $i++): ?>
            <a href="?p=<?= $i ?>&q=<?= urlencode($search) ?>&action_type=<?= urlencode($filter_action) ?>"
               class="page-btn <?= $i===$page_num?'active':'' ?>"><?= $i ?></a>
            <?php endfor; ?>
            <?php if ($page_num < ceil($total/$per_page)): ?>
            <a href="?p=<?= $page_num+1 ?>&q=<?= urlencode($search) ?>&action_type=<?= urlencode($filter_action) ?>" class="page-btn">›</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>
</div>

<script>
function exportLog() {
    const rows = [['Waktu','Admin','Aksi','Detail','IP']];
    document.querySelectorAll('.log-row').forEach(r => {
        rows.push([
            r.dataset.time || '', r.dataset.admin || '',
            r.dataset.action || '', r.dataset.detail || '', r.dataset.ip || ''
        ]);
    });
    const data = <?= json_encode(array_map(fn($l) => [
        date('d/m/Y H:i:s', strtotime($l['created_at'])),
        $l['admin_name'] ?? '—', $l['action'],
        $l['detail'] ?? '', $l['ip'] ?? ''
    ], $logs), JSON_UNESCAPED_UNICODE) ?>;
    const csv = [['Waktu','Admin','Aksi','Detail','IP'], ...data]
        .map(r => r.map(c => '"'+String(c).replace(/"/g,'""')+'"').join(','))
        .join('\n');
    const a = document.createElement('a');
    a.href = 'data:text/csv;charset=utf-8,\uFEFF' + encodeURIComponent(csv);
    a.download = 'activity-log-<?= date('Ymd') ?>.csv';
    a.click();
    showToast('✅ Export berhasil!');
}
</script>
<?php require_once 'includes/admin_footer.php'; ?>
