<?php
require_once 'includes/admin_config.php';
requireLogin();
require_once 'includes/products_engine.php';

// Seed JSON dari config jika belum ada
$db_products = product_get_all();
if (empty($db_products)) {
    global $games;
    // trigger seed
    $db_products = product_get_all();
}

$page_title  = 'Manajemen Produk';
$active_menu = 'products';
require_once 'includes/admin_layout.php';
?>

<style>
/* ── Package table inside modal ── */
.pkg-table { width:100%; border-collapse:collapse; font-size:.82rem; }
.pkg-table th { padding:.5rem .75rem; text-align:left; font-size:.7rem;
    font-weight:700; text-transform:uppercase; color:var(--gray2);
    border-bottom:1px solid var(--border); letter-spacing:.4px; }
.pkg-table td { padding:.6rem .75rem; border-bottom:1px solid rgba(48,54,61,.4);
    vertical-align:middle; }
.pkg-table tr:last-child td { border-bottom:none; }
.pkg-table tr:hover td { background:rgba(255,255,255,.02); }
.pkg-edit-row td { background:rgba(37,99,235,.05)!important; }

/* ── Inline input in pkg table ── */
.pkg-inp {
    background:var(--bg3); border:1px solid var(--border);
    border-radius:6px; padding:.35rem .625rem;
    color:var(--white); font-size:.8rem; width:100%;
    outline:none; font-family:inherit; transition:.15s;
}
.pkg-inp:focus { border-color:var(--blue); }

/* ── Product card grid for selector ── */
.img-preview {
    width:100%; height:120px; object-fit:cover;
    border-radius:8px; display:block; margin-top:.5rem;
    background:var(--bg3);
}
.color-swatch {
    width:32px; height:32px; border-radius:50%;
    border:2px solid transparent; cursor:pointer;
    transition:.2s; flex-shrink:0;
}
.color-swatch.active { border-color:#fff; transform:scale(1.2); }
</style>

<div class="page-content">

    <!-- Toolbar -->
    <div class="toolbar">
        <div class="search-box">
            <span class="ico">🔍</span>
            <input type="text" id="tableSearch" placeholder="Cari nama produk...">
        </div>
        <select class="filter-select" id="catFilter">
            <option value="">Semua Kategori</option>
            <option value="Mobile">Mobile</option>
            <option value="PC">PC</option>
        </select>
        <select class="filter-select" id="statusFilter">
            <option value="">Semua Status</option>
            <option value="active">Aktif</option>
            <option value="inactive">Nonaktif</option>
        </select>
        <button class="btn btn-primary btn-sm" onclick="openAdd()">➕ Tambah Produk</button>
    </div>

    <!-- Products Table -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">🎮 Daftar Produk
                <span id="productCount" style="color:var(--gray);font-weight:400;font-size:.85rem"></span>
            </div>
            <button class="btn btn-ghost btn-sm" onclick="loadProducts()">🔄 Refresh</button>
        </div>
        <div class="table-wrap">
            <table id="productTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Produk</th>
                        <th>Kategori</th>
                        <th>Mata Uang</th>
                        <th>Harga Mulai</th>
                        <th>Paket</th>
                        <th>Status</th>
                        <th style="min-width:160px">Aksi</th>
                    </tr>
                </thead>
                <tbody id="productTbody">
                    <tr><td colspan="8" style="text-align:center;padding:2rem;color:var(--gray)">Memuat...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════
     MODAL: TAMBAH PRODUK
══════════════════════════════════════════════════ -->
<div class="modal-overlay" id="addModal">
<div class="modal" style="max-width:600px">
    <div class="modal-header">
        <div class="modal-title">➕ Tambah Produk Baru</div>
        <button class="modal-close" onclick="closeModal('addModal')">✕</button>
    </div>
    <div class="modal-body" style="max-height:70vh;overflow-y:auto">

        <!-- Step indicator -->
        <div style="display:flex;gap:0;margin-bottom:1.5rem;border-radius:8px;overflow:hidden;border:1px solid var(--border)">
            <div class="step-ind active" data-step="1" style="flex:1;padding:.6rem;text-align:center;font-size:.78rem;font-weight:700;cursor:pointer;background:var(--blue);color:#fff">
                1. Info Produk
            </div>
            <div class="step-ind" data-step="2" style="flex:1;padding:.6rem;text-align:center;font-size:.78rem;font-weight:700;cursor:pointer;background:var(--bg3);color:var(--gray)">
                2. Foto & Tampilan
            </div>
            <div class="step-ind" data-step="3" style="flex:1;padding:.6rem;text-align:center;font-size:.78rem;font-weight:700;cursor:pointer;background:var(--bg3);color:var(--gray)">
                3. Paket Harga
            </div>
        </div>

        <!-- Step 1: Info Dasar -->
        <div id="step1">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Nama Game *</label>
                    <input type="text" class="form-control" id="add_name" placeholder="Contoh: Mobile Legends" maxlength="80">
                </div>
                <div class="form-group">
                    <label class="form-label">Kategori *</label>
                    <select class="form-control" id="add_cat">
                        <option value="Mobile">📱 Mobile</option>
                        <option value="PC">💻 PC</option>
                        <option value="Console">🎮 Console</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Nama Mata Uang *</label>
                    <input type="text" class="form-control" id="add_currency" placeholder="Diamond / UC / VP / CP">
                </div>
                <div class="form-group">
                    <label class="form-label">Ikon Emoji</label>
                    <div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:.4rem">
                        <?php foreach (['⚔️','🔥','🎯','✨','🎮','🎖️','⭐','🎲','🏆','💫','🔮','🛡️'] as $em): ?>
                        <span onclick="selectEmoji(this,'<?= $em ?>')"
                              style="font-size:1.5rem;cursor:pointer;padding:.25rem;border-radius:6px;
                                  border:1px solid transparent;transition:.15s"
                              onmouseover="this.style.background='var(--bg3)'"
                              onmouseout="if(!this.classList.contains('sel'))this.style.background=''">
                            <?= $em ?>
                        </span>
                        <?php endforeach; ?>
                    </div>
                    <input type="text" class="form-control" id="add_icon" placeholder="Atau ketik emoji" value="🎮">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Badge Label</label>
                <div style="display:flex;gap:.5rem;flex-wrap:wrap">
                    <?php foreach ([['','Kosong'],['Terlaris',''],['Hot',''],['Populer',''],['Baru',''],['Sale','']] as [$val,$_]): ?>
                    <button type="button" class="badge-sel"
                            data-val="<?= $val ?>"
                            onclick="selectBadge(this)"
                            style="padding:.3rem .75rem;border-radius:6px;font-size:.75rem;font-weight:700;
                                cursor:pointer;border:1px solid var(--border);background:var(--bg3);
                                color:var(--gray);transition:.15s;font-family:inherit">
                        <?= $val ?: '(Kosong)' ?>
                    </button>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" id="add_badge" value="">
            </div>
            <div class="form-group">
                <label class="form-label">Deskripsi Singkat</label>
                <textarea class="form-control" id="add_desc" rows="2" placeholder="Deskripsi produk (opsional)"></textarea>
            </div>
        </div>

        <!-- Step 2: Foto & Warna -->
        <div id="step2" style="display:none">
            <div class="form-group">
                <label class="form-label">URL Foto Produk (kartu) *</label>
                <input type="url" class="form-control" id="add_img"
                       placeholder="https://..."
                       oninput="previewImg(this.value,'prev_img')">
                <img id="prev_img" src="" alt="Preview Foto" class="img-preview" style="display:none"
                     onerror="this.style.display='none'">
            </div>
            <div class="form-group">
                <label class="form-label">URL Banner Detail (halaman detail)</label>
                <input type="url" class="form-control" id="add_img_banner"
                       placeholder="https://... (kosongkan = sama dengan foto kartu)"
                       oninput="previewImg(this.value,'prev_banner')">
                <img id="prev_banner" src="" alt="Preview Banner" class="img-preview" style="display:none"
                     onerror="this.style.display='none'">
                <div style="font-size:.72rem;color:var(--gray);margin-top:.4rem">
                    💡 Tips: Pakai link dari Google Play Store atau website resmi game
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Warna Tema</label>
                <div style="display:flex;gap:.625rem;flex-wrap:wrap;align-items:center">
                    <?php foreach ([
                        '#2563EB','#EF4444','#F59E0B','#22C55E','#8B5CF6',
                        '#06B6D4','#EC4899','#F97316','#EAB308','#1E40AF'
                    ] as $col): ?>
                    <div class="color-swatch" data-color="<?= $col ?>"
                         style="background:<?= $col ?>"
                         onclick="selectColor(this,'<?= $col ?>')"></div>
                    <?php endforeach; ?>
                    <input type="color" id="add_color" value="#2563EB"
                           style="width:32px;height:32px;border-radius:50%;border:none;cursor:pointer;padding:0"
                           oninput="add_color_val=this.value">
                </div>
                <input type="hidden" id="add_color_hidden" value="#2563EB">
            </div>
        </div>

        <!-- Step 3: Paket Harga -->
        <div id="step3" style="display:none">
            <div style="font-size:.82rem;color:var(--gray);margin-bottom:1rem">
                Tambahkan paket harga untuk produk ini. Kamu bisa tambah lebih banyak paket setelah produk disimpan.
            </div>
            <div style="background:var(--bg3);border-radius:var(--radius);padding:1rem;margin-bottom:1rem">
                <div style="font-weight:700;font-size:.85rem;margin-bottom:.75rem">➕ Tambah Paket</div>
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.625rem">
                    <div class="form-group" style="margin:0">
                        <label class="form-label">Jumlah Item</label>
                        <input type="number" class="form-control" id="npkg_amount" placeholder="86" min="1">
                    </div>
                    <div class="form-group" style="margin:0">
                        <label class="form-label">Harga (Rp)</label>
                        <input type="number" class="form-control" id="npkg_price" placeholder="13000" min="100">
                    </div>
                    <div class="form-group" style="margin:0">
                        <label class="form-label">Bonus</label>
                        <input type="number" class="form-control" id="npkg_bonus" placeholder="0" min="0" value="0">
                    </div>
                </div>
                <button type="button" class="btn btn-ghost btn-sm" style="margin-top:.625rem" onclick="addNewPkg()">
                    ➕ Tambah ke Daftar
                </button>
            </div>
            <div id="newPkgList" style="display:flex;flex-direction:column;gap:.375rem">
                <!-- packages added here -->
            </div>
            <div id="newPkgEmpty" style="text-align:center;padding:1.5rem;color:var(--gray);font-size:.82rem">
                Belum ada paket. Tambahkan minimal 1 paket.
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button class="btn btn-ghost" id="addPrev" style="display:none" onclick="addStep(-1)">← Kembali</button>
        <button class="btn btn-ghost" onclick="closeModal('addModal')">Batal</button>
        <button class="btn btn-primary" id="addNext" onclick="addStep(1)">Selanjutnya →</button>
        <button class="btn btn-primary" id="addSave" style="display:none" onclick="saveProduct()">💾 Simpan Produk</button>
    </div>
</div>
</div>

<!-- ══════════════════════════════════════════════════
     MODAL: EDIT PRODUK
══════════════════════════════════════════════════ -->
<div class="modal-overlay" id="editModal">
<div class="modal" style="max-width:560px">
    <div class="modal-header">
        <div class="modal-title">✏️ Edit Produk — <span id="editModalTitle"></span></div>
        <button class="modal-close" onclick="closeModal('editModal')">✕</button>
    </div>
    <div class="modal-body">
        <input type="hidden" id="edit_id">
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Nama Game *</label>
                <input type="text" class="form-control" id="edit_name" maxlength="80">
            </div>
            <div class="form-group">
                <label class="form-label">Kategori</label>
                <select class="form-control" id="edit_cat">
                    <option value="Mobile">📱 Mobile</option>
                    <option value="PC">💻 PC</option>
                    <option value="Console">🎮 Console</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Mata Uang *</label>
                <input type="text" class="form-control" id="edit_currency">
            </div>
            <div class="form-group">
                <label class="form-label">Ikon Emoji</label>
                <input type="text" class="form-control" id="edit_icon">
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Badge</label>
            <input type="text" class="form-control" id="edit_badge" placeholder="Terlaris / Hot / Baru / Populer / (kosong)">
        </div>
        <div class="form-group">
            <label class="form-label">URL Foto Kartu</label>
            <input type="url" class="form-control" id="edit_img" oninput="previewImg(this.value,'edit_prev_img')">
            <img id="edit_prev_img" src="" alt="Preview" class="img-preview" style="display:none;height:80px;object-fit:cover;margin-top:.5rem"
                 onerror="this.style.display='none'">
        </div>
        <div class="form-group">
            <label class="form-label">URL Banner Detail</label>
            <input type="url" class="form-control" id="edit_img_banner">
        </div>
        <div class="form-group">
            <label class="form-label">Warna Tema</label>
            <div style="display:flex;align-items:center;gap:.75rem">
                <input type="color" id="edit_color" style="width:40px;height:40px;border:none;border-radius:8px;cursor:pointer;padding:0">
                <span style="font-size:.8rem;color:var(--gray)">Pilih warna tema produk</span>
            </div>
        </div>
        <div class="form-group" style="margin:0">
            <label class="form-label">Deskripsi</label>
            <textarea class="form-control" id="edit_desc" rows="2"></textarea>
        </div>
    </div>
    <div class="modal-footer">
        <button class="btn btn-ghost" onclick="closeModal('editModal')">Batal</button>
        <button class="btn btn-primary" onclick="saveEdit()">💾 Simpan Perubahan</button>
    </div>
</div>
</div>

<!-- ══════════════════════════════════════════════════
     MODAL: KELOLA PAKET
══════════════════════════════════════════════════ -->
<div class="modal-overlay" id="pkgModal">
<div class="modal" style="max-width:620px">
    <div class="modal-header">
        <div class="modal-title">📦 Paket Harga — <span id="pkgModalTitle"></span></div>
        <button class="modal-close" onclick="closeModal('pkgModal')">✕</button>
    </div>
    <div class="modal-body">
        <!-- Add new package form -->
        <div style="background:var(--bg3);border-radius:var(--radius);padding:1rem;margin-bottom:1rem">
            <div style="font-weight:700;font-size:.85rem;margin-bottom:.75rem;color:var(--white)">
                ➕ Tambah Paket Baru
            </div>
            <input type="hidden" id="pkg_product_id">
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:.625rem;align-items:flex-end">
                <div>
                    <label class="form-label">Jumlah</label>
                    <input type="number" class="form-control" id="pkg_amount" placeholder="86" min="1">
                </div>
                <div>
                    <label class="form-label">Harga (Rp)</label>
                    <input type="number" class="form-control" id="pkg_price" placeholder="13000" min="100">
                </div>
                <div>
                    <label class="form-label">Bonus</label>
                    <input type="number" class="form-control" id="pkg_bonus" placeholder="0" min="0" value="0">
                </div>
                <button class="btn btn-primary" onclick="savePkg()" style="height:38px;margin-bottom:0">
                    ➕
                </button>
            </div>
        </div>

        <!-- Package list -->
        <div style="font-weight:700;font-size:.85rem;color:var(--gray2);text-transform:uppercase;
            letter-spacing:.5px;margin-bottom:.625rem">
            Daftar Paket
        </div>
        <div id="pkgTableWrap">
            <table class="pkg-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Jumlah</th>
                        <th>Bonus</th>
                        <th>Harga</th>
                        <th style="text-align:right">Aksi</th>
                    </tr>
                </thead>
                <tbody id="pkgTableBody"></tbody>
            </table>
        </div>
        <div id="pkgEmpty" style="display:none;text-align:center;padding:2rem;color:var(--gray);font-size:.85rem">
            Belum ada paket. Tambahkan paket di atas.
        </div>
    </div>
    <div class="modal-footer">
        <button class="btn btn-ghost" onclick="closeModal('pkgModal')">Tutup</button>
    </div>
</div>
</div>

<!-- ══════════════════════════════════════════════════
     MODAL: KONFIRMASI HAPUS
══════════════════════════════════════════════════ -->
<div class="modal-overlay" id="deleteModal">
<div class="modal" style="max-width:420px">
    <div class="modal-header">
        <div class="modal-title" style="color:var(--danger)">🗑 Hapus Produk</div>
        <button class="modal-close" onclick="closeModal('deleteModal')">✕</button>
    </div>
    <div class="modal-body">
        <div style="text-align:center;padding:.5rem 0">
            <div style="font-size:3rem;margin-bottom:1rem" id="delIcon">🎮</div>
            <div style="font-weight:800;font-size:1rem;margin-bottom:.5rem">Hapus "<span id="delName"></span>"?</div>
            <div style="color:var(--gray);font-size:.85rem;line-height:1.6">
                Produk dan semua paket harganya akan dihapus permanen.<br>
                Tindakan ini <strong style="color:var(--danger)">tidak dapat dibatalkan</strong>.
            </div>
        </div>
        <input type="hidden" id="del_id">
    </div>
    <div class="modal-footer">
        <button class="btn btn-ghost" onclick="closeModal('deleteModal')">Batal</button>
        <button class="btn btn-danger" onclick="confirmDelete()" style="background:var(--danger);color:#fff;border:none">
            🗑 Ya, Hapus
        </button>
    </div>
</div>
</div>

<script>
const API     = 'products_api.php';
const CSRF    = '<?= csrf_token() ?>';
const fmtRp   = n => 'Rp ' + Number(n).toLocaleString('id-ID');
let newPkgs   = [];  // buffer paket baru saat tambah produk
let currentStep = 1;
let editingPkgIdx = -1;

// ── Load & render products ────────────────────────────────────
function loadProducts() {
    fetch(API + '?action=list')
        .then(r => r.json())
        .then(d => {
            if (d.error) { showToast('⚠️ ' + d.error, 'error'); return; }
            renderTable(d.products || []);
        })
        .catch(() => showToast('⚠️ Gagal memuat produk', 'error'));
}

function renderTable(products) {
    const tbody = document.getElementById('productTbody');
    document.getElementById('productCount').textContent = `(${products.length} produk)`;

    // Apply filters
    const search  = document.getElementById('tableSearch').value.toLowerCase();
    const cat     = document.getElementById('catFilter').value;
    const status  = document.getElementById('statusFilter').value;

    const filtered = products.filter(p => {
        if (search && !p.name.toLowerCase().includes(search)) return false;
        if (cat    && p.category !== cat)   return false;
        if (status && p.status   !== status) return false;
        return true;
    });

    if (!filtered.length) {
        tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;padding:2.5rem;color:var(--gray)">
            Tidak ada produk ditemukan</td></tr>`;
        return;
    }

    tbody.innerHTML = filtered.map(p => {
        const minPrice = p.packages && p.packages.length
            ? Math.min(...p.packages.map(x => x.price)) : 0;
        const imgHtml = p.img
            ? `<img src="${esc(p.img)}" alt="${esc(p.name)}" style="width:36px;height:36px;border-radius:6px;object-fit:cover;flex-shrink:0" onerror="this.style.display='none'" loading="lazy">`
            : `<span style="font-size:1.4rem">${esc(p.icon)}</span>`;
        const badgeHtml = p.badge
            ? `<span class="badge badge-${p.badge==='Hot'?'cancelled':p.badge==='Baru'?'completed':'processing'}"
                 style="font-size:.6rem">${p.badge}</span>` : '';

        return `<tr data-id="${p.id}" data-cat="${esc(p.category)}" data-status="${p.status}">
            <td class="td-id">#${p.id}</td>
            <td>
                <div style="display:flex;align-items:center;gap:.625rem">
                    ${imgHtml}
                    <div>
                        <div style="font-weight:700;font-size:.875rem">${esc(p.name)} ${badgeHtml}</div>
                        <div style="font-size:.7rem;color:var(--gray)">${esc(p.slug)}</div>
                    </div>
                </div>
            </td>
            <td><span style="font-size:.75rem;background:var(--bg3);padding:.2rem .55rem;border-radius:4px">${p.category}</span></td>
            <td style="font-size:.82rem;color:var(--gray)">${esc(p.currency)}</td>
            <td style="font-weight:700;color:var(--cyan)">${minPrice ? fmtRp(minPrice) : '<span style="color:var(--gray)">—</span>'}</td>
            <td>
                <button class="btn btn-ghost btn-sm" onclick="openPkgModal(${p.id},'${esc(p.name)}')">
                    📦 ${p.packages ? p.packages.length : 0} paket
                </button>
            </td>
            <td>
                <span class="badge badge-${p.status==='active'?'active':'inactive'}">
                    <span class="badge-dot"></span>${p.status==='active'?'Aktif':'Nonaktif'}
                </span>
            </td>
            <td>
                <div style="display:flex;gap:.375rem;flex-wrap:wrap">
                    <button class="btn btn-ghost btn-sm" onclick="openEdit(${p.id})">✏️ Edit</button>
                    <button class="btn btn-sm ${p.status==='active'?'btn-danger':'btn-success'}"
                            onclick="toggleStatus(${p.id},'${esc(p.name)}')">
                        ${p.status==='active'?'🚫':'✅'}
                    </button>
                    <button class="btn btn-danger btn-sm" onclick="openDelete(${p.id},'${esc(p.name)}','${esc(p.icon)}')">
                        🗑
                    </button>
                </div>
            </td>
        </tr>`;
    }).join('');
}

// ── Filter events ─────────────────────────────────────────────
document.getElementById('tableSearch').addEventListener('input', loadProducts);
document.getElementById('catFilter').addEventListener('change', loadProducts);
document.getElementById('statusFilter').addEventListener('change', loadProducts);

// ══════════════════════════════════════════════════
// ADD PRODUCT FLOW
// ══════════════════════════════════════════════════
function openAdd() {
    // Reset
    currentStep = 1;
    newPkgs = [];
    document.getElementById('add_name').value    = '';
    document.getElementById('add_currency').value= '';
    document.getElementById('add_icon').value    = '🎮';
    document.getElementById('add_badge').value   = '';
    document.getElementById('add_desc').value    = '';
    document.getElementById('add_img').value     = '';
    document.getElementById('add_img_banner').value = '';
    document.getElementById('add_color_hidden').value = '#2563EB';
    document.getElementById('prev_img').style.display    = 'none';
    document.getElementById('prev_banner').style.display = 'none';
    document.querySelectorAll('.badge-sel').forEach(b => b.style.background = 'var(--bg3)');
    document.querySelectorAll('.color-swatch').forEach(s => s.classList.remove('active'));
    showAddStep(1);
    openModal('addModal');
}

function showAddStep(n) {
    currentStep = n;
    [1,2,3].forEach(i => {
        document.getElementById('step'+i).style.display = i===n ? 'block' : 'none';
        const ind = document.querySelector(`.step-ind[data-step="${i}"]`);
        ind.style.background = i===n ? 'var(--blue)' : (i<n ? 'rgba(34,197,94,.2)' : 'var(--bg3)');
        ind.style.color      = i===n ? '#fff' : (i<n ? 'var(--success)' : 'var(--gray)');
    });
    document.getElementById('addPrev').style.display = n>1 ? '' : 'none';
    document.getElementById('addNext').style.display = n<3 ? '' : 'none';
    document.getElementById('addSave').style.display = n===3 ? '' : 'none';
    if (n===3) renderNewPkgs();
}

function addStep(dir) {
    const next = currentStep + dir;
    // Validate step 1
    if (currentStep===1 && dir===1) {
        if (!document.getElementById('add_name').value.trim()) { flashErr('add_name','Nama wajib diisi'); return; }
        if (!document.getElementById('add_currency').value.trim()) { flashErr('add_currency','Mata uang wajib diisi'); return; }
    }
    if (next >= 1 && next <= 3) showAddStep(next);
}

function selectEmoji(el, em) {
    document.querySelectorAll('.step-ind').forEach(e => {});
    document.getElementById('add_icon').value = em;
    document.querySelectorAll('.emoji-opt').forEach(e => e.classList.remove('sel'));
    el.classList.add('sel');
    el.style.background = 'var(--bg3)';
}
function selectBadge(el) {
    document.querySelectorAll('.badge-sel').forEach(b => { b.style.background='var(--bg3)'; b.style.color='var(--gray)'; });
    el.style.background = 'var(--blue)'; el.style.color = '#fff';
    document.getElementById('add_badge').value = el.dataset.val;
}
function selectColor(el, col) {
    document.querySelectorAll('.color-swatch').forEach(s => s.classList.remove('active'));
    el.classList.add('active');
    document.getElementById('add_color_hidden').value = col;
    document.getElementById('add_color').value = col;
}

// New package buffer
function addNewPkg() {
    const amount = parseInt(document.getElementById('npkg_amount').value);
    const price  = parseInt(document.getElementById('npkg_price').value);
    const bonus  = parseInt(document.getElementById('npkg_bonus').value) || 0;
    if (!amount || amount < 1)   { flashErr('npkg_amount','Wajib diisi'); return; }
    if (!price  || price  < 100) { flashErr('npkg_price','Min Rp 100'); return; }
    newPkgs.push({ amount, price, bonus });
    newPkgs.sort((a,b) => a.price - b.price);
    document.getElementById('npkg_amount').value = '';
    document.getElementById('npkg_price').value  = '';
    document.getElementById('npkg_bonus').value  = '0';
    renderNewPkgs();
}
function removeNewPkg(i) { newPkgs.splice(i,1); renderNewPkgs(); }
function renderNewPkgs() {
    const list = document.getElementById('newPkgList');
    const empty = document.getElementById('newPkgEmpty');
    empty.style.display = newPkgs.length ? 'none' : 'block';
    list.innerHTML = newPkgs.map((p,i) => `
        <div style="display:flex;align-items:center;gap:.75rem;padding:.6rem .875rem;
            background:var(--bg3);border-radius:8px;font-size:.82rem">
            <span style="color:var(--gray);font-size:.72rem;width:20px">${i+1}</span>
            <span style="flex:1;font-weight:700">${p.amount.toLocaleString()} item</span>
            ${p.bonus ? `<span style="color:var(--success);font-size:.72rem">+${p.bonus} bonus</span>` : ''}
            <span style="color:var(--cyan);font-weight:700">${fmtRp(p.price)}</span>
            <button onclick="removeNewPkg(${i})"
                style="background:none;border:none;color:var(--gray);cursor:pointer;font-size:.85rem"
                onmouseover="this.style.color='var(--danger)'"
                onmouseout="this.style.color='var(--gray)'">✕</button>
        </div>`).join('');
}

async function saveProduct() {
    const name = document.getElementById('add_name').value.trim();
    if (!name) { showAddStep(1); flashErr('add_name','Nama wajib diisi'); return; }
    if (!newPkgs.length) {
        if (!confirm('Lanjut simpan tanpa paket harga? Kamu bisa tambah nanti.')) return;
    }
    const btn = document.getElementById('addSave');
    btn.textContent = 'Menyimpan...'; btn.disabled = true;

    const fd = new FormData();
    fd.append('csrf_token', CSRF);
    fd.append('name',        name);
    fd.append('category',    document.getElementById('add_cat').value);
    fd.append('currency',    document.getElementById('add_currency').value.trim());
    fd.append('icon',        document.getElementById('add_icon').value);
    fd.append('badge',       document.getElementById('add_badge').value);
    fd.append('description', document.getElementById('add_desc').value.trim());
    fd.append('img',         document.getElementById('add_img').value.trim());
    fd.append('img_banner',  document.getElementById('add_img_banner').value.trim());
    fd.append('color',       document.getElementById('add_color_hidden').value);

    try {
        const r = await fetch(API+'?action=create', {method:'POST', body:fd});
        const d = await r.json();
        if (d.error) { showToast('⚠️ '+d.error,'error'); btn.textContent='💾 Simpan Produk'; btn.disabled=false; return; }

        // Add packages
        for (const pkg of newPkgs) {
            const pfd = new FormData();
            pfd.append('csrf_token', CSRF);
            pfd.append('product_id', d.id);
            pfd.append('amount', pkg.amount);
            pfd.append('price',  pkg.price);
            pfd.append('bonus',  pkg.bonus);
            await fetch(API+'?action=add_package', {method:'POST', body:pfd});
        }

        showToast('✅ ' + d.message);
        closeModal('addModal');
        loadProducts();
    } catch(e) {
        showToast('⚠️ Gagal menyimpan produk','error');
    }
    btn.textContent = '💾 Simpan Produk'; btn.disabled = false;
}

// ══════════════════════════════════════════════════
// EDIT PRODUCT
// ══════════════════════════════════════════════════
function openEdit(id) {
    fetch(API + `?action=get&id=${id}`)
        .then(r => r.json())
        .then(d => {
            if (d.error) { showToast('⚠️ '+d.error,'error'); return; }
            const p = d.product;
            document.getElementById('edit_id').value       = p.id;
            document.getElementById('edit_name').value     = p.name;
            document.getElementById('edit_cat').value      = p.category;
            document.getElementById('edit_currency').value = p.currency;
            document.getElementById('edit_icon').value     = p.icon;
            document.getElementById('edit_badge').value    = p.badge;
            document.getElementById('edit_img').value      = p.img;
            document.getElementById('edit_img_banner').value = p.img_banner;
            document.getElementById('edit_color').value    = p.color || '#2563EB';
            document.getElementById('edit_desc').value     = p.description || '';
            document.getElementById('editModalTitle').textContent = p.name;
            // Preview image
            if (p.img) {
                const pi = document.getElementById('edit_prev_img');
                pi.src = p.img; pi.style.display = 'block';
            }
            openModal('editModal');
        });
}

function saveEdit() {
    const id   = document.getElementById('edit_id').value;
    const name = document.getElementById('edit_name').value.trim();
    if (!name) { flashErr('edit_name','Nama wajib diisi'); return; }

    const fd = new FormData();
    fd.append('csrf_token', CSRF);
    fd.append('id',          id);
    fd.append('name',        name);
    fd.append('category',    document.getElementById('edit_cat').value);
    fd.append('currency',    document.getElementById('edit_currency').value.trim());
    fd.append('icon',        document.getElementById('edit_icon').value);
    fd.append('badge',       document.getElementById('edit_badge').value);
    fd.append('img',         document.getElementById('edit_img').value.trim());
    fd.append('img_banner',  document.getElementById('edit_img_banner').value.trim());
    fd.append('color',       document.getElementById('edit_color').value);
    fd.append('description', document.getElementById('edit_desc').value.trim());

    fetch(API+'?action=update', {method:'POST', body:fd})
        .then(r=>r.json()).then(d => {
            if (d.error) { showToast('⚠️ '+d.error,'error'); return; }
            showToast('✅ '+d.message);
            closeModal('editModal');
            loadProducts();
        });
}

// ══════════════════════════════════════════════════
// PACKAGE MODAL
// ══════════════════════════════════════════════════
function openPkgModal(productId, name) {
    document.getElementById('pkgModalTitle').textContent = name;
    document.getElementById('pkg_product_id').value     = productId;
    document.getElementById('pkg_amount').value = '';
    document.getElementById('pkg_price').value  = '';
    document.getElementById('pkg_bonus').value  = '0';
    loadPkgs(productId);
    openModal('pkgModal');
}

function loadPkgs(productId) {
    fetch(API + `?action=get&id=${productId}`)
        .then(r => r.json())
        .then(d => {
            if (d.error) return;
            renderPkgs(d.product.packages || [], productId);
        });
}

function renderPkgs(pkgs, productId) {
    const tbody = document.getElementById('pkgTableBody');
    const empty = document.getElementById('pkgEmpty');
    empty.style.display = pkgs.length ? 'none' : 'block';
    document.getElementById('pkgTableWrap').style.display = pkgs.length ? '' : 'none';

    tbody.innerHTML = pkgs.map((pkg, i) => `
        <tr id="pkgrow_${i}">
            <td style="color:var(--gray2);font-size:.75rem">${i+1}</td>
            <td style="font-weight:700">${Number(pkg.amount).toLocaleString()}</td>
            <td style="color:var(--success);font-size:.78rem">${pkg.bonus > 0 ? '+'+pkg.bonus : '—'}</td>
            <td style="color:var(--cyan);font-weight:700">${fmtRp(pkg.price)}</td>
            <td style="text-align:right">
                <div style="display:flex;gap:.375rem;justify-content:flex-end">
                    <button class="btn btn-ghost btn-sm" onclick="editPkgInline(${i},${pkg.amount},${pkg.price},${pkg.bonus},${productId})">✏️</button>
                    <button class="btn btn-danger btn-sm" onclick="deletePkg(${productId},${i})">🗑</button>
                </div>
            </td>
        </tr>`).join('');
}

function savePkg() {
    const pid    = document.getElementById('pkg_product_id').value;
    const amount = document.getElementById('pkg_amount').value;
    const price  = document.getElementById('pkg_price').value;
    const bonus  = document.getElementById('pkg_bonus').value || 0;
    if (!amount) { flashErr('pkg_amount','Wajib'); return; }
    if (!price)  { flashErr('pkg_price','Wajib'); return; }

    const fd = new FormData();
    fd.append('csrf_token', CSRF);
    fd.append('product_id', pid);
    fd.append('amount', amount);
    fd.append('price',  price);
    fd.append('bonus',  bonus);
    fetch(API+'?action=add_package', {method:'POST', body:fd})
        .then(r=>r.json()).then(d => {
            if (d.error) { showToast('⚠️ '+d.error,'error'); return; }
            showToast('✅ '+d.message);
            document.getElementById('pkg_amount').value = '';
            document.getElementById('pkg_price').value  = '';
            document.getElementById('pkg_bonus').value  = '0';
            loadPkgs(pid);
            loadProducts();
        });
}

function editPkgInline(idx, amount, price, bonus, productId) {
    const row = document.getElementById('pkgrow_'+idx);
    row.classList.add('pkg-edit-row');
    row.innerHTML = `
        <td style="color:var(--gray2);font-size:.75rem">${idx+1}</td>
        <td><input type="number" class="pkg-inp" id="epkg_amount" value="${amount}" style="width:80px"></td>
        <td><input type="number" class="pkg-inp" id="epkg_bonus"  value="${bonus}"  style="width:60px"></td>
        <td><input type="number" class="pkg-inp" id="epkg_price"  value="${price}"  style="width:100px"></td>
        <td style="text-align:right">
            <div style="display:flex;gap:.375rem;justify-content:flex-end">
                <button class="btn btn-success btn-sm" onclick="saveEditPkg(${idx},${productId})">💾</button>
                <button class="btn btn-ghost btn-sm" onclick="loadPkgs(${productId})">✕</button>
            </div>
        </td>`;
}

function saveEditPkg(idx, productId) {
    const fd = new FormData();
    fd.append('csrf_token', CSRF);
    fd.append('product_id', productId);
    fd.append('pkg_index',  idx);
    fd.append('amount',     document.getElementById('epkg_amount').value);
    fd.append('price',      document.getElementById('epkg_price').value);
    fd.append('bonus',      document.getElementById('epkg_bonus').value || 0);
    fetch(API+'?action=update_package', {method:'POST', body:fd})
        .then(r=>r.json()).then(d => {
            if (d.error) { showToast('⚠️ '+d.error,'error'); return; }
            showToast('✅ '+d.message);
            loadPkgs(productId);
            loadProducts();
        });
}

function deletePkg(productId, idx) {
    if (!confirm('Hapus paket ini?')) return;
    const fd = new FormData();
    fd.append('csrf_token', CSRF);
    fd.append('product_id', productId);
    fd.append('pkg_index',  idx);
    fetch(API+'?action=delete_package', {method:'POST', body:fd})
        .then(r=>r.json()).then(d => {
            if (d.error) { showToast('⚠️ '+d.error,'error'); return; }
            showToast('✅ '+d.message);
            loadPkgs(productId);
            loadProducts();
        });
}

// ══════════════════════════════════════════════════
// STATUS TOGGLE & DELETE
// ══════════════════════════════════════════════════
function toggleStatus(id, name) {
    const fd = new FormData(); fd.append('id', id);
    fetch(API+'?action=toggle', {method:'POST', body:fd})
        .then(r=>r.json()).then(d => {
            if (d.error) { showToast('⚠️ '+d.error,'error'); return; }
            showToast(`✅ ${name}: ${d.status==='active'?'Diaktifkan':'Dinonaktifkan'}`);
            loadProducts();
        });
}

function openDelete(id, name, icon) {
    document.getElementById('del_id').value      = id;
    document.getElementById('delName').textContent = name;
    document.getElementById('delIcon').textContent = icon || '🎮';
    openModal('deleteModal');
}
function confirmDelete() {
    const id = document.getElementById('del_id').value;
    const fd = new FormData(); fd.append('id', id);
    fetch(API+'?action=delete', {method:'POST', body:fd})
        .then(r=>r.json()).then(d => {
            if (d.error) { showToast('⚠️ '+d.error,'error'); return; }
            showToast('✅ '+d.message);
            closeModal('deleteModal');
            loadProducts();
        });
}

// ── Utilities ─────────────────────────────────────────────────
function esc(s) {
    return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function flashErr(id, msg) {
    const el = document.getElementById(id);
    if (!el) return;
    el.style.borderColor = 'var(--danger)';
    el.style.boxShadow   = '0 0 0 3px rgba(239,68,68,.15)';
    el.placeholder = msg;
    el.focus();
    setTimeout(() => { el.style.borderColor=''; el.style.boxShadow=''; el.placeholder=''; }, 2000);
}
function previewImg(url, imgId) {
    const img = document.getElementById(imgId);
    if (!url) { img.style.display='none'; return; }
    img.src = url;
    img.style.display = 'block';
}

// Init
document.addEventListener('DOMContentLoaded', loadProducts);
</script>

<?php require_once 'includes/admin_footer.php'; ?>
