<?php
require_once 'includes/admin_config.php';
requireLogin();
$page_title  = 'Manajemen Promo';
$active_menu = 'promo';

// ── Handle actions ────────────────────────────────────────────
$msg = $err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $code     = strtoupper(trim($_POST['code'] ?? ''));
        $type     = $_POST['type'] ?? 'percent';
        $value    = (int)($_POST['value'] ?? 0);
        $min_p    = (int)($_POST['min_purchase'] ?? 0);
        $max_d    = (int)($_POST['max_discount'] ?? 0);
        $max_use  = (int)($_POST['max_use'] ?? 0);
        $valid_f  = $_POST['valid_from']  ?? date('Y-m-d');
        $valid_t  = $_POST['valid_until'] ?? '';
        $desc     = trim($_POST['description'] ?? '');

        if (!$code) { $err = 'Kode promo wajib diisi'; }
        elseif (!preg_match('/^[A-Z0-9_]{3,20}$/', $code)) { $err = 'Kode hanya boleh huruf kapital, angka, dan underscore (3-20 karakter)'; }
        elseif ($value <= 0) { $err = 'Nilai diskon wajib lebih dari 0'; }
        elseif (db_row("SELECT id FROM promo_codes WHERE code=?", [$code])) { $err = "Kode '$code' sudah ada!"; }
        else {
            db_exec("INSERT INTO promo_codes (code,description,type,value,min_purchase,max_discount,max_use,valid_from,valid_until,is_active)
                VALUES (?,?,?,?,?,?,?,?,?,1)",
                [$code,$desc,$type,$value,$min_p,$max_d?:null,$max_use,$valid_f,$valid_t?:null]);
            log_activity('promo_create', "Kode promo baru dibuat: $code ({$type}, nilai: {$value})");
            $msg = "✅ Kode promo '$code' berhasil dibuat!";
        }
    }

    elseif ($action === 'toggle') {
        $id  = (int)($_POST['id'] ?? 0);
        $cur = db_row("SELECT is_active, code FROM promo_codes WHERE id=?",[$id]);
        if ($cur) {
            $new = $cur['is_active'] ? 0 : 1;
            db_exec("UPDATE promo_codes SET is_active=? WHERE id=?", [$new,$id]);
            log_activity('promo_toggle', "Promo {$cur['code']} " . ($new ? 'diaktifkan' : 'dinonaktifkan'));
            $msg = '✅ Status promo diperbarui!';
        }
    }

    elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $prm = db_row("SELECT code FROM promo_codes WHERE id=?",[$id]);
        db_exec("DELETE FROM promo_codes WHERE id=?", [$id]);
        log_activity('promo_delete', "Promo dihapus: " . ($prm['code'] ?? $id));
        $msg = '✅ Kode promo dihapus!';
    }
}

// ── Load data ─────────────────────────────────────────────────
$promos = db_rows("SELECT * FROM promo_codes ORDER BY created_at DESC");
$stats  = [
    'total'   => count($promos),
    'active'  => count(array_filter($promos, fn($p)=>$p['is_active'])),
    'used'    => array_sum(array_column($promos,'used_count')),
];

require_once 'includes/admin_layout.php';
?>
<style>
.promo-badge{display:inline-flex;align-items:center;gap:.3rem;padding:.25rem .75rem;border-radius:100px;
    font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.5px;cursor:pointer;
    border:none;font-family:inherit;transition:var(--tr)}
.promo-active{background:rgba(34,197,94,.12);color:var(--success);border:1px solid rgba(34,197,94,.3)}
.promo-inactive{background:rgba(239,68,68,.1);color:var(--danger);border:1px solid rgba(239,68,68,.25)}
.code-chip{font-family:monospace;font-size:.95rem;font-weight:800;color:var(--cyan);
    background:rgba(0,212,255,.08);border:1px solid rgba(0,212,255,.2);padding:.2rem .625rem;border-radius:6px}
</style>

<div class="page-content">

<?php if ($msg): ?>
<div style="background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.25);border-radius:var(--radius);
    padding:.875rem 1.25rem;margin-bottom:1.25rem;color:var(--success);font-size:.875rem;font-weight:600">
    <?= $msg ?>
</div>
<?php endif; ?>
<?php if ($err): ?>
<div style="background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.25);border-radius:var(--radius);
    padding:.875rem 1.25rem;margin-bottom:1.25rem;color:var(--danger);font-size:.875rem">
    ⚠️ <?= htmlspecialchars($err) ?>
</div>
<?php endif; ?>

<!-- Stats -->
<div class="stats-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:1.5rem">
    <div class="stat-card" style="--accent-color:var(--blue)">
        <span class="stat-icon">🎟️</span>
        <div class="stat-label">Total Promo</div>
        <div class="stat-value"><?= $stats['total'] ?></div>
        <div class="stat-change up">Semua kode</div>
    </div>
    <div class="stat-card" style="--accent-color:var(--success)">
        <span class="stat-icon">✅</span>
        <div class="stat-label">Promo Aktif</div>
        <div class="stat-value"><?= $stats['active'] ?></div>
        <div class="stat-change up">Bisa dipakai</div>
    </div>
    <div class="stat-card" style="--accent-color:var(--warning)">
        <span class="stat-icon">📊</span>
        <div class="stat-label">Total Penggunaan</div>
        <div class="stat-value"><?= $stats['used'] ?></div>
        <div class="stat-change up">Kali dipakai</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 360px;gap:1.25rem;align-items:start">

<!-- Tabel promo -->
<div class="card">
    <div class="card-header">
        <div class="card-title">🎟️ Daftar Kode Promo
            <span style="color:var(--gray);font-weight:400;font-size:.85rem">(<?= $stats['total'] ?> kode)</span>
        </div>
        <button class="btn btn-primary btn-sm" onclick="openModal('addPromoModal')">➕ Buat Promo</button>
    </div>
    <div class="table-wrap">
    <table>
        <thead>
            <tr><th>Kode</th><th>Tipe</th><th>Diskon</th><th>Min. Beli</th><th>Penggunaan</th><th>Berlaku</th><th>Status</th><th>Aksi</th></tr>
        </thead>
        <tbody>
        <?php foreach ($promos as $p):
            $is_expired = !empty($p['valid_until']) && strtotime($p['valid_until']) < time();
            $quota_full = $p['max_use'] > 0 && $p['used_count'] >= $p['max_use'];
        ?>
        <tr>
            <td><span class="code-chip"><?= htmlspecialchars($p['code']) ?></span></td>
            <td style="font-size:.8rem;color:var(--gray)"><?= $p['type']==='percent'?'Persen':'Flat' ?></td>
            <td style="font-weight:700;color:var(--cyan)">
                <?= $p['type']==='percent' ? $p['value'].'%' : formatRp($p['value']) ?>
                <?php if ($p['max_discount']): ?>
                <div style="font-size:.68rem;color:var(--gray)">maks <?= formatRp($p['max_discount']) ?></div>
                <?php endif; ?>
            </td>
            <td style="font-size:.82rem"><?= formatRp((int)$p['min_purchase']) ?></td>
            <td style="text-align:center">
                <span style="font-weight:700"><?= $p['used_count'] ?></span>
                <span style="color:var(--gray);font-size:.75rem">
                    <?= $p['max_use'] ? '/ '.$p['max_use'] : '/ ∞' ?>
                </span>
                <?php if ($quota_full): ?>
                <div style="font-size:.65rem;color:var(--danger)">Habis</div>
                <?php endif; ?>
            </td>
            <td style="font-size:.75rem;color:var(--gray)">
                <?= date('d/m/Y', strtotime($p['valid_from'])) ?>
                <?php if ($p['valid_until']): ?>
                <br>s/d <?= date('d/m/Y', strtotime($p['valid_until'])) ?>
                <?php if ($is_expired): ?>
                <div style="color:var(--danger);font-size:.68rem">Expired</div>
                <?php endif; ?>
                <?php endif; ?>
            </td>
            <td>
                <form method="POST" style="display:inline">
                    <input type="hidden" name="action" value="toggle">
                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                    <button type="submit" class="promo-badge <?= $p['is_active']?'promo-active':'promo-inactive' ?>">
                        <?= $p['is_active']?'✅ Aktif':'❌ Nonaktif' ?>
                    </button>
                </form>
            </td>
            <td>
                <form method="POST" style="display:inline" onsubmit="return confirm('Hapus kode <?= htmlspecialchars($p['code']) ?>?')">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                    <button type="submit" class="btn btn-danger btn-sm">🗑</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($promos)): ?>
        <tr><td colspan="8" style="text-align:center;padding:2.5rem;color:var(--gray)">Belum ada kode promo</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<!-- Quick guide -->
<div>
    <div class="card" style="margin-bottom:1rem">
        <div class="card-header"><div class="card-title">📖 Panduan</div></div>
        <div class="card-body" style="font-size:.82rem;color:var(--gray);line-height:1.75">
            <strong style="color:var(--white)">Tipe Diskon:</strong><br>
            • <strong style="color:var(--cyan)">Persen</strong> — diskon % dari total<br>
            • <strong style="color:var(--cyan)">Flat</strong> — diskon nominal Rp tetap<br><br>
            <strong style="color:var(--white)">Tips:</strong><br>
            • Isi Max Diskon untuk batasi diskon persen<br>
            • Max Penggunaan = 0 artinya tak terbatas<br>
            • Kode tidak bisa diedit, hapus & buat ulang
        </div>
    </div>
    <div class="card">
        <div class="card-header"><div class="card-title">📊 Statistik Promo Aktif</div></div>
        <div class="card-body">
            <?php
            $active_promos = array_filter($promos, fn($p)=>$p['is_active'] && !($p['max_use']>0 && $p['used_count']>=$p['max_use']));
            foreach (array_slice($active_promos, 0, 5) as $p):
                $usage_pct = $p['max_use'] > 0 ? round($p['used_count']/$p['max_use']*100) : 0;
            ?>
            <div style="margin-bottom:.875rem">
                <div style="display:flex;justify-content:space-between;font-size:.8rem;margin-bottom:.25rem">
                    <span class="code-chip" style="font-size:.75rem"><?= htmlspecialchars($p['code']) ?></span>
                    <span style="color:var(--gray)"><?= $p['used_count'] ?>x pakai</span>
                </div>
                <?php if ($p['max_use']): ?>
                <div style="height:5px;background:var(--bg3);border-radius:3px;overflow:hidden">
                    <div style="height:100%;width:<?= $usage_pct ?>%;background:<?= $usage_pct>80?'var(--danger)':'linear-gradient(90deg,var(--blue),var(--cyan))' ?>;border-radius:3px"></div>
                </div>
                <div style="font-size:.65rem;color:var(--gray);margin-top:.2rem"><?= $usage_pct ?>% kuota terpakai</div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
</div>
</div>

<!-- Modal Tambah Promo -->
<div class="modal-overlay" id="addPromoModal">
<div class="modal" style="max-width:520px">
    <div class="modal-header">
        <div class="modal-title">➕ Buat Kode Promo Baru</div>
        <button class="modal-close" onclick="closeModal('addPromoModal')">✕</button>
    </div>
    <form method="POST">
    <input type="hidden" name="action" value="create">
    <div class="modal-body">
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Kode Promo *</label>
                <input type="text" name="code" class="form-control" placeholder="MLBB10" required
                       style="text-transform:uppercase" oninput="this.value=this.value.toUpperCase()" maxlength="20">
                <small style="color:var(--gray);font-size:.72rem">Huruf kapital, angka, underscore</small>
            </div>
            <div class="form-group">
                <label class="form-label">Tipe Diskon *</label>
                <select name="type" class="form-control" id="promoType" onchange="toggleMaxDisc()">
                    <option value="percent">Persen (%)</option>
                    <option value="flat">Flat (Rp)</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Nilai Diskon *</label>
                <div style="position:relative">
                    <input type="number" name="value" class="form-control" placeholder="10" required min="1">
                    <span id="valueSuffix" style="position:absolute;right:.75rem;top:50%;transform:translateY(-50%);
                        font-size:.8rem;color:var(--gray);pointer-events:none">%</span>
                </div>
            </div>
            <div class="form-group" id="maxDiscWrap">
                <label class="form-label">Maks Diskon (Rp)</label>
                <input type="number" name="max_discount" class="form-control" placeholder="0 = tidak dibatasi" min="0">
            </div>
            <div class="form-group">
                <label class="form-label">Minimal Pembelian (Rp)</label>
                <input type="number" name="min_purchase" class="form-control" placeholder="0" min="0" value="0">
            </div>
            <div class="form-group">
                <label class="form-label">Maks Penggunaan</label>
                <input type="number" name="max_use" class="form-control" placeholder="0 = tak terbatas" min="0" value="0">
            </div>
            <div class="form-group">
                <label class="form-label">Berlaku Dari *</label>
                <input type="date" name="valid_from" class="form-control" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Berlaku Sampai</label>
                <input type="date" name="valid_until" class="form-control">
                <small style="color:var(--gray);font-size:.72rem">Kosong = tidak ada batas</small>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Deskripsi</label>
            <input type="text" name="description" class="form-control" placeholder="Contoh: Diskon 10% untuk Mobile Legends" maxlength="200">
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal('addPromoModal')">Batal</button>
        <button type="submit" class="btn btn-primary">🎟️ Buat Promo</button>
    </div>
    </form>
</div>
</div>

<script>
function toggleMaxDisc() {
    const type   = document.getElementById('promoType').value;
    const wrap   = document.getElementById('maxDiscWrap');
    const suffix = document.getElementById('valueSuffix');
    wrap.style.display   = type === 'percent' ? '' : 'none';
    suffix.textContent   = type === 'percent' ? '%' : 'Rp';
}
</script>
<?php require_once 'includes/admin_footer.php'; ?>
