<!-- Footer -->
<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <div class="nav-logo" style="margin-bottom:1rem">
                    <span class="logo-icon">🎮</span>
                    <span class="logo-text">Game<span class="logo-accent">Store</span></span>
                </div>
                <p>Platform top up game terlengkap dan termurah di Indonesia. Proses instan, aman, dan terpercaya sejak 2020.</p>
                <div class="footer-social">
                    <a href="#" aria-label="WhatsApp">💬</a>
                    <a href="#" aria-label="Instagram">📸</a>
                    <a href="#" aria-label="Telegram">✈️</a>
                    <a href="#" aria-label="Twitter">🐦</a>
                </div>
            </div>

            <div class="footer-links">
                <h4>Game Populer</h4>
                <ul>
                    <li><a href="detail.php?slug=mobile-legends">Mobile Legends</a></li>
                    <li><a href="detail.php?slug=free-fire">Free Fire</a></li>
                    <li><a href="detail.php?slug=pubg-mobile">PUBG Mobile</a></li>
                    <li><a href="detail.php?slug=genshin-impact">Genshin Impact</a></li>
                    <li><a href="detail.php?slug=valorant">Valorant</a></li>
                </ul>
            </div>

            <div class="footer-links">
                <h4>Layanan</h4>
                <ul>
                    <li><a href="products.php">Semua Produk</a></li>
                    <li><a href="cara-order.php">Cara Order</a></li>
                    <li><a href="contact.php">Hubungi Admin</a></li>
                    <li><a href="faq.php">FAQ</a></li>
                    <li><a href="terms.php">Syarat & Ketentuan</a></li>
                </ul>
            </div>

            <div class="footer-links">
                <h4>Kontak</h4>
                <ul>
                    <li>📱 WhatsApp: 0812-3456-7890</li>
                    <li>📧 admin@gamestore.id</li>
                    <li>⏰ 24 Jam / 7 Hari</li>
                    <li>📍 Indonesia</li>
                </ul>
                <div class="payment-icons">
                    <span>DANA</span>
                    <span>OVO</span>
                    <span>GoPay</span>
                    <span>BCA</span>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved. Made with ❤️ in Indonesia.</p>
        </div>
    </div>
</footer>

<!-- Toast notification -->
<div class="toast" id="toast"></div>

<script src="assets/js/loading.js"></script>
<script src="assets/js/main.js"></script>

<!-- ── Live Chat Widget ── -->
<?php
// Hitung path relatif ke root dari halaman manapun
$depth = substr_count($_SERVER['PHP_SELF'], '/') - 1;
$root  = str_repeat('../', $depth);
?>
<script>window.__CHAT_ROOT = '<?= $root ?>';</script>
<?php include dirname(__DIR__) . '/chat/widget.php'; ?>
</body>
</html>
