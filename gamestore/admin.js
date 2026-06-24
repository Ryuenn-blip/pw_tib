// ── Sidebar toggle ────────────────────────────────────────────
const sidebar        = document.getElementById('sidebar');
const sidebarOverlay = document.getElementById('sidebarOverlay');
const sidebarToggle  = document.getElementById('sidebarToggle');
const sidebarClose   = document.getElementById('sidebarClose');

function openSidebar()  { sidebar?.classList.add('open'); sidebarOverlay?.classList.add('show'); }
function closeSidebar() { sidebar?.classList.remove('open'); sidebarOverlay?.classList.remove('show'); }

sidebarToggle?.addEventListener('click', openSidebar);
sidebarClose?.addEventListener('click',  closeSidebar);
sidebarOverlay?.addEventListener('click', closeSidebar);

// ── Modal helpers ─────────────────────────────────────────────
function openModal(id)  { document.getElementById(id)?.classList.add('open'); }
function closeModal(id) { document.getElementById(id)?.classList.remove('open'); }

// Close modal on overlay click
document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', e => {
        if (e.target === overlay) overlay.classList.remove('open');
    });
});
// Close on ESC
document.addEventListener('keydown', e => {
    if (e.key === 'Escape')
        document.querySelectorAll('.modal-overlay.open').forEach(m => m.classList.remove('open'));
});

// ── Toast ─────────────────────────────────────────────────────
function showToast(msg, type = 'success') {
    let t = document.getElementById('adminToast');
    if (!t) {
        t = document.createElement('div');
        t.id = 'adminToast';
        t.className = 'toast';
        document.body.appendChild(t);
    }
    t.textContent = msg;
    t.className = `toast ${type} show`;
    setTimeout(() => t.classList.remove('show'), 3200);
}

// ── Table search ──────────────────────────────────────────────
const tableSearch = document.getElementById('tableSearch');
tableSearch?.addEventListener('input', function () {
    const q = this.value.toLowerCase();
    document.querySelectorAll('tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});

// ── Status filter ─────────────────────────────────────────────
const statusFilter = document.getElementById('statusFilter');
statusFilter?.addEventListener('change', function () {
    const val = this.value;
    document.querySelectorAll('tbody tr[data-status]').forEach(row => {
        row.style.display = (!val || row.dataset.status === val) ? '' : 'none';
    });
});

// ── Bar chart animation ───────────────────────────────────────
document.querySelectorAll('.bar[data-pct]').forEach(bar => {
    bar.style.height = '0%';
    setTimeout(() => { bar.style.height = bar.dataset.pct + '%'; }, 100);
});

// ── Donut chart ───────────────────────────────────────────────
function buildDonut(canvasId, segments) {
    // segments = [{value, color, label}, ...]
    const svg   = document.getElementById(canvasId);
    if (!svg) return;
    const total = segments.reduce((s, x) => s + x.value, 0);
    if (!total) return;
    const cx = 60, cy = 60, r = 48, stroke = 16;
    let offset = -90; // start at top
    const circ = 2 * Math.PI * r;
    svg.innerHTML = '';

    segments.forEach(seg => {
        const pct  = seg.value / total;
        const dash = pct * circ;
        const gap  = circ - dash;
        const rad  = (offset * Math.PI) / 180;
        const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
        circle.setAttribute('cx', cx);
        circle.setAttribute('cy', cy);
        circle.setAttribute('r', r);
        circle.setAttribute('fill', 'none');
        circle.setAttribute('stroke', seg.color);
        circle.setAttribute('stroke-width', stroke);
        circle.setAttribute('stroke-dasharray', `${dash} ${gap}`);
        circle.setAttribute('stroke-dashoffset', -offset * r * Math.PI / 180 + circ * 0.25);
        circle.style.transform = `rotate(${offset}deg)`;
        circle.style.transformOrigin = `${cx}px ${cy}px`;
        svg.appendChild(circle);
        offset += pct * 360;
    });

    // center text
    const text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
    text.setAttribute('x', cx); text.setAttribute('y', cy + 5);
    text.setAttribute('text-anchor', 'middle');
    text.setAttribute('fill', '#F0F6FF');
    text.setAttribute('font-size', '13');
    text.setAttribute('font-weight', '800');
    text.setAttribute('font-family', 'Inter,sans-serif');
    text.textContent = total;
    svg.appendChild(text);
}

// Called from dashboard page
if (typeof donutData !== 'undefined') {
    buildDonut('donutChart', donutData);
}

// ── Confirm delete / action ───────────────────────────────────
document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', function (e) {
        if (!confirm(this.dataset.confirm)) e.preventDefault();
    });
});

// ── Order status update (demo) ────────────────────────────────
document.querySelectorAll('.status-select').forEach(sel => {
    sel.addEventListener('change', function () {
        const orderId = this.dataset.order;
        showToast(`✅ Status pesanan ${orderId} diperbarui!`);
    });
});