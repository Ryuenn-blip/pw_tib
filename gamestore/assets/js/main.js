// ===== NAVBAR SCROLL =====
const navbar = document.getElementById('navbar');
window.addEventListener('scroll', () => {
    navbar?.classList.toggle('scrolled', window.scrollY > 20);
});

// ===== HAMBURGER MENU =====
const hamburger = document.getElementById('hamburger');
const navLinks  = document.getElementById('navLinks');
hamburger?.addEventListener('click', () => {
    hamburger.classList.toggle('open');
    navLinks.classList.toggle('open');
});

// ===== CART (localStorage) =====
function getCart() {
    try { return JSON.parse(localStorage.getItem('gs_cart') || '[]'); } catch { return []; }
}
function saveCart(cart) {
    localStorage.setItem('gs_cart', JSON.stringify(cart));
    updateCartBadge();
}
function updateCartBadge() {
    const badge = document.getElementById('cartBadge');
    if (badge) badge.textContent = getCart().length;
}
function addToCart(item) {
    const cart = getCart();
    cart.push(item);
    saveCart(cart);
    showToast('✅ Ditambahkan ke keranjang!', 'success');
}
updateCartBadge();

// ===== TOAST =====
function showToast(msg, type = '') {
    const t = document.getElementById('toast');
    if (!t) return;
    t.textContent = msg;
    t.className = 'toast' + (type ? ' ' + type : '') + ' show';
    setTimeout(() => t.classList.remove('show'), 3000);
}

// ===== SEARCH =====
const searchInput    = document.getElementById('searchInput');
const searchDropdown = document.getElementById('searchDropdown');

if (searchInput && searchDropdown && typeof gamesData !== 'undefined') {
    searchInput.addEventListener('input', function () {
        const q = this.value.trim().toLowerCase();
        if (!q) { searchDropdown.classList.remove('show'); return; }
        const results = gamesData.filter(g => g.name.toLowerCase().includes(q)).slice(0, 5);
        if (!results.length) { searchDropdown.classList.remove('show'); return; }
        searchDropdown.innerHTML = results.map(g => `
            <a class="search-result-item" href="detail.php?slug=${g.slug}">
                <span class="game-icon">${g.icon}</span>
                <div class="game-info">
                    <div class="name">${g.name}</div>
                    <div class="currency">${g.currency}</div>
                </div>
            </a>`).join('');
        searchDropdown.classList.add('show');
    });
    document.addEventListener('click', e => {
        if (!searchInput.contains(e.target)) searchDropdown.classList.remove('show');
    });
}

// ===== PRODUCT FILTER TABS =====
document.querySelectorAll('.filter-tab').forEach(tab => {
    tab.addEventListener('click', function () {
        document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        const cat = this.dataset.cat;
        document.querySelectorAll('.product-card').forEach(card => {
            const show = cat === 'Semua' || card.dataset.cat === cat;
            card.style.display = show ? '' : 'none';
        });
    });
});

// ===== PACKAGE SELECTOR (detail page) =====
document.querySelectorAll('.pkg-card').forEach(card => {
    card.addEventListener('click', function () {
        document.querySelectorAll('.pkg-card').forEach(c => c.classList.remove('selected'));
        this.classList.add('selected');
        const price  = this.dataset.price;
        const amount = this.dataset.amount;
        const cur    = this.dataset.currency;
        const el = id => document.getElementById(id);
        if (el('summaryItem'))  el('summaryItem').textContent  = amount + ' ' + cur;
        if (el('summaryPrice')) el('summaryPrice').textContent = 'Rp ' + Number(price).toLocaleString('id-ID');
        if (el('totalPrice'))   el('totalPrice').textContent   = 'Rp ' + Number(price).toLocaleString('id-ID');
        if (el('selectedPkg'))  el('selectedPkg').value        = amount;
        if (el('selectedPrice'))el('selectedPrice').value      = price;
    });
});

// ===== ORDER FORM =====
const orderForm = document.getElementById('orderForm');
orderForm?.addEventListener('submit', function (e) {
    e.preventDefault();
    const userId  = document.getElementById('userId')?.value.trim();
    const pkg     = document.getElementById('selectedPkg')?.value;
    const price   = document.getElementById('selectedPrice')?.value;
    const game    = document.getElementById('gameName')?.value;
    const payment = document.getElementById('paymentMethod')?.value;
    if (!userId) { showToast('⚠️ Masukkan User ID kamu!', 'error'); return; }
    if (!pkg)    { showToast('⚠️ Pilih paket terlebih dahulu!', 'error'); return; }
    const wa = `https://wa.me/${WA_NUMBER}?text=` + encodeURIComponent(
        `Halo Admin! Saya mau order:\n\n🎮 Game: ${game}\n💎 Paket: ${pkg}\n💰 Harga: Rp ${Number(price).toLocaleString('id-ID')}\n🆔 User ID: ${userId}\n💳 Pembayaran: ${payment}\n\nMohon konfirmasinya, terima kasih!`
    );
    window.open(wa, '_blank');
});

// ===== ANIMATE ON SCROLL =====
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, { threshold: 0.1 });

document.querySelectorAll('.product-card, .testi-card, .step-card, .howto-step').forEach(el => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(20px)';
    el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
    observer.observe(el);
});

// ===== HERO CANVAS PARTICLES =====
const canvas = document.getElementById('heroCanvas');
if (canvas) {
    const ctx = canvas.getContext('2d');
    let W, H, particles = [];

    function resize() {
        W = canvas.width  = canvas.offsetWidth;
        H = canvas.height = canvas.offsetHeight;
    }
    resize();
    window.addEventListener('resize', resize);

    for (let i = 0; i < 60; i++) {
        particles.push({
            x: Math.random() * 1400, y: Math.random() * 600,
            r: Math.random() * 2 + 0.5,
            vx: (Math.random() - 0.5) * 0.4,
            vy: (Math.random() - 0.5) * 0.4,
            alpha: Math.random() * 0.5 + 0.1,
        });
    }

    function draw() {
        ctx.clearRect(0, 0, W, H);
        particles.forEach(p => {
            ctx.beginPath();
            ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
            ctx.fillStyle = `rgba(0,212,255,${p.alpha})`;
            ctx.fill();
            p.x += p.vx; p.y += p.vy;
            if (p.x < 0 || p.x > W) p.vx *= -1;
            if (p.y < 0 || p.y > H) p.vy *= -1;
        });
        requestAnimationFrame(draw);
    }
    draw();
}
