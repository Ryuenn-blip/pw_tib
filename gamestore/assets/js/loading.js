/**
 * GameStore Loading & Animation System
 * - Page Loader
 * - Top Progress Bar
 * - Skeleton → Real content swap
 * - Scroll Reveal (IntersectionObserver)
 * - Page Transitions
 * - Ripple effect
 * - Count-up numbers
 * - Lazy image loading
 * - Scroll progress bar
 */

(function () {
    'use strict';

    /* ── Helpers ── */
    const $  = (sel, ctx = document) => ctx.querySelector(sel);
    const $$ = (sel, ctx = document) => [...ctx.querySelectorAll(sel)];

    /* ══════════════════════════════════════════════════
       1. PAGE LOADER
    ══════════════════════════════════════════════════ */
    const TIPS = [
        '⚡ Proses top up biasanya selesai dalam 1-5 menit',
        '💎 Kami menjamin harga paling murah di Indonesia',
        '🛡️ Setiap transaksi 100% aman & terpercaya',
        '🎧 Admin siap membantu kamu 24 jam sehari',
        '🔥 Sudah 50.000+ transaksi berhasil diproses',
    ];

    function createLoader() {
        const tip = TIPS[Math.floor(Math.random() * TIPS.length)];
        const el = document.createElement('div');
        el.id = 'gs-loader';
        el.setAttribute('role', 'status');
        el.setAttribute('aria-label', 'Memuat halaman');
        el.innerHTML = `
            <div class="loader-logo">
                <div class="loader-icon">🎮</div>
                <div class="loader-brand">Game<span>Store</span></div>
                <div class="loader-tagline">Top Up Terlengkap & Termurah</div>
            </div>
            <div class="loader-spinner" aria-hidden="true"></div>
            <div class="loader-dots" aria-hidden="true">
                <span></span><span></span><span></span>
            </div>
            <div class="loader-tip">${tip}</div>`;
        document.body.insertBefore(el, document.body.firstChild);
        return el;
    }

    function hideLoader(loader) {
        if (!loader) return;
        setTimeout(() => {
            loader.classList.add('hide');
            setTimeout(() => loader.remove(), 500);
        }, 300);
    }

    /* ══════════════════════════════════════════════════
       2. TOP PROGRESS BAR
    ══════════════════════════════════════════════════ */
    let progressEl, progressTimer, progressVal = 0;

    function createProgress() {
        progressEl = document.createElement('div');
        progressEl.id = 'gs-progress';
        progressEl.setAttribute('role', 'progressbar');
        progressEl.setAttribute('aria-hidden', 'true');
        document.body.appendChild(progressEl);
    }

    function startProgress() {
        if (!progressEl) return;
        progressVal = 0;
        progressEl.classList.remove('done');
        progressEl.style.opacity = '1';
        progressEl.style.width   = '0%';
        progressTimer = setInterval(() => {
            // Slow down as it approaches 90%
            const inc = progressVal < 30 ? 8 : progressVal < 60 ? 4 : progressVal < 85 ? 1.5 : .3;
            progressVal = Math.min(progressVal + inc, 92);
            progressEl.style.width = progressVal + '%';
        }, 80);
    }

    function finishProgress() {
        clearInterval(progressTimer);
        if (!progressEl) return;
        progressEl.style.width = '100%';
        setTimeout(() => progressEl.classList.add('done'), 300);
    }

    /* ══════════════════════════════════════════════════
       3. SCROLL PROGRESS INDICATOR
    ══════════════════════════════════════════════════ */
    function createScrollBar() {
        const bar = document.createElement('div');
        bar.id = 'gs-scroll-bar';
        bar.setAttribute('aria-hidden', 'true');
        document.body.appendChild(bar);

        window.addEventListener('scroll', () => {
            const doc  = document.documentElement;
            const pct  = (doc.scrollTop / (doc.scrollHeight - doc.clientHeight)) * 100;
            bar.style.width = Math.min(pct, 100) + '%';
        }, { passive: true });
    }

    /* ══════════════════════════════════════════════════
       4. SKELETON CARDS
    ══════════════════════════════════════════════════ */
    function buildSkeletonCard() {
        const div = document.createElement('div');
        div.className = 'skeleton-card';
        div.setAttribute('aria-hidden', 'true');
        div.innerHTML = `
            <div class="skeleton sk-img"></div>
            <div class="sk-body">
                <div class="skeleton sk-title"></div>
                <div class="skeleton sk-sub"></div>
                <div class="skeleton sk-price"></div>
                <div class="skeleton sk-btn"></div>
            </div>`;
        return div;
    }

    function injectSkeletons(container, count = 6) {
        if (!container) return;
        container.innerHTML = '';
        for (let i = 0; i < count; i++) {
            container.appendChild(buildSkeletonCard());
        }
    }

    /* ══════════════════════════════════════════════════
       5. SCROLL REVEAL (IntersectionObserver)
    ══════════════════════════════════════════════════ */
    function initScrollReveal() {
        // Auto-tag elements
        $$('.section-title, .section-header').forEach(el => {
            if (!el.classList.contains('gs-reveal')) el.classList.add('gs-reveal');
        });
        $$('.hero-stat').forEach(el => {
            if (!el.classList.contains('gs-reveal')) el.classList.add('gs-reveal');
        });
        $$('.feature-item').forEach(el => {
            if (!el.classList.contains('gs-reveal')) el.classList.add('gs-reveal');
        });
        $$('.testi-card').forEach(el => {
            if (!el.classList.contains('gs-reveal')) el.classList.add('gs-reveal');
        });
        $$('.step-card, .howto-step').forEach(el => {
            if (!el.classList.contains('gs-reveal')) el.classList.add('gs-reveal');
        });
        $$('.promo-banner').forEach((el, i) => {
            el.classList.add(i === 0 ? 'gs-reveal-left' : 'gs-reveal-right');
        });
        $$('.footer-links').forEach((el, i) => {
            el.classList.add('gs-reveal');
            el.style.transitionDelay = (i * 0.08) + 's';
        });

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    // Trigger count-up for numbers
                    $$('.gs-count-up', entry.target).forEach(countUp);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

        $$('.gs-reveal, .gs-reveal-left, .gs-reveal-right, .gs-reveal-scale, .gs-reveal-stagger')
            .forEach(el => observer.observe(el));
    }

    /* ══════════════════════════════════════════════════
       6. PRODUCT CARDS reveal on grid
    ══════════════════════════════════════════════════ */
    function revealProductCards() {
        const cards = $$('.product-card');
        if (!cards.length) return;

        const obs = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity    = '1';
                    entry.target.style.transform  = 'translateY(0)';
                    obs.unobserve(entry.target);
                }
            });
        }, { threshold: 0.08 });

        cards.forEach((card, i) => {
            card.style.opacity   = '0';
            card.style.transform = 'translateY(24px)';
            card.style.transition = `opacity .45s ease ${i * 0.06}s, transform .45s ease ${i * 0.06}s`;
            obs.observe(card);
        });
    }

    /* ══════════════════════════════════════════════════
       7. COUNT-UP NUMBERS
    ══════════════════════════════════════════════════ */
    function countUp(el) {
        const text  = el.textContent.trim();
        const match = text.match(/^([\d,]+)(\+?)(.*)$/);
        if (!match) return;
        const target  = parseInt(match[1].replace(/,/g, ''));
        const suffix  = (match[2] || '') + (match[3] || '');
        const dur     = 1200;
        const start   = performance.now();

        function tick(now) {
            const progress = Math.min((now - start) / dur, 1);
            // Ease out cubic
            const eased = 1 - Math.pow(1 - progress, 3);
            const current = Math.round(target * eased);
            el.textContent = current.toLocaleString('id-ID') + suffix;
            if (progress < 1) requestAnimationFrame(tick);
        }
        requestAnimationFrame(tick);
    }

    // Auto tag hero stats
    function initCountUp() {
        $$('.hero-stat .number, .stat-value').forEach(el => {
            el.classList.add('gs-count-up');
        });
    }

    /* ══════════════════════════════════════════════════
       8. PAGE TRANSITIONS
    ══════════════════════════════════════════════════ */
    function initPageTransitions() {
        document.addEventListener('click', e => {
            const link = e.target.closest('a');
            if (!link) return;
            const href = link.getAttribute('href');
            if (!href || href.startsWith('#') || href.startsWith('http') ||
                href.startsWith('javascript') || href.startsWith('mailto') ||
                href.startsWith('tel') || link.target === '_blank' ||
                link.hasAttribute('download') || e.ctrlKey || e.metaKey || e.shiftKey) return;
            if (!href.endsWith('.php') && !href.match(/\.\w+$/) && !href.includes('?') && href !== '/') return;

            e.preventDefault();
            startProgress();
            document.body.classList.add('gs-page-out');
            setTimeout(() => { window.location.href = href; }, 280);
        });
    }

    /* ══════════════════════════════════════════════════
       9. RIPPLE EFFECT on buttons
    ══════════════════════════════════════════════════ */
    function addRipple(e) {
        const btn  = e.currentTarget;
        const rect = btn.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height) * 2;
        const x    = e.clientX - rect.left - size / 2;
        const y    = e.clientY - rect.top  - size / 2;
        const rip  = document.createElement('span');
        rip.className = 'ripple';
        rip.style.cssText = `width:${size}px;height:${size}px;left:${x}px;top:${y}px`;
        btn.appendChild(rip);
        rip.addEventListener('animationend', () => rip.remove());
    }

    function initRipple() {
        // Add ripple class & handler to interactive buttons
        $$('.btn-primary, .btn-card, .btn-order, .gc-start, .btn-login').forEach(btn => {
            if (btn.classList.contains('ripple-host')) return;
            btn.classList.add('ripple-host');
            btn.addEventListener('click', addRipple);
        });
    }

    /* ══════════════════════════════════════════════════
       10. LAZY IMAGE LOADING
    ══════════════════════════════════════════════════ */
    function initLazyImages() {
        const imgs = $$('img[loading="lazy"], .card-image img');
        imgs.forEach(img => {
            img.classList.add('gs-img');
            if (img.complete && img.naturalWidth) {
                img.classList.add('loaded');
                return;
            }
            img.addEventListener('load',  () => img.classList.add('loaded'));
            img.addEventListener('error', () => img.classList.add('error'));
        });
    }

    /* ══════════════════════════════════════════════════
       11. NAVBAR ACTIVE LINK highlight on scroll
    ══════════════════════════════════════════════════ */
    function initNavHighlight() {
        const sections = $$('section[id]');
        if (!sections.length) return;
        const obs = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    $$('.nav-links a').forEach(a => a.classList.remove('active'));
                    const link = $(`.nav-links a[href="#${entry.target.id}"]`);
                    if (link) link.classList.add('active');
                }
            });
        }, { threshold: .4 });
        sections.forEach(s => obs.observe(s));
    }

    /* ══════════════════════════════════════════════════
       12. SKELETON → PRODUCT GRID swap
    ══════════════════════════════════════════════════ */
    function initProductGridLoader() {
        const grid = $('#products-grid-skeleton') || $('.products-grid');
        if (!grid) return;
        // If grid already has real cards, just reveal them
        const hasReal = grid.querySelector('.product-card');
        if (hasReal) return;
        // Otherwise inject skeletons (will be replaced by PHP content)
    }

    /* ══════════════════════════════════════════════════
       INIT
    ══════════════════════════════════════════════════ */
    let loader = null;

    // Create progress bar immediately (before DOM ready)
    createProgress();

    document.addEventListener('DOMContentLoaded', () => {
        // Create loader only if page is "fresh" (not back-navigation)
        const navEntry = performance.getEntriesByType('navigation')[0];
        const isBackNav = navEntry && navEntry.type === 'back_forward';

        if (!isBackNav) {
            loader = createLoader();
            startProgress();
        }

        // Scroll bars & reveal
        createScrollBar();
        initScrollReveal();
        initCountUp();
        initRipple();
        initLazyImages();
        initNavHighlight();
        initPageTransitions();

        // Add page-in class to main content
        const main = document.querySelector('main') ||
                     document.querySelector('.hero') ||
                     document.querySelector('section') ||
                     document.querySelector('.page-content');
        if (main) main.classList.add('gs-page-in');
    });

    window.addEventListener('load', () => {
        finishProgress();
        hideLoader(loader);
        // Reveal product cards after load
        setTimeout(revealProductCards, 100);
    });

    // Expose helpers globally for other scripts
    window.GS = {
        startProgress,
        finishProgress,
        countUp,
        buildSkeletonCard,
        injectSkeletons,
        revealProductCards,
    };

})();
