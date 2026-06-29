# 🎮 GameStore — Top Up Game Terlengkap & Termurah

Platform top up game digital berbasis PHP Native + CSS + JS. Tanpa framework, tanpa database — siap upload ke hosting shared.

---

## 📁 Struktur Folder

```
gamestore/
├── index.php              ← Beranda
├── products.php           ← Semua produk
├── detail.php             ← Detail & order produk
├── cart.php               ← Keranjang & checkout
├── login.php              ← Login
├── register.php           ← Daftar akun
├── cara-order.php         ← Panduan order
├── contact.php            ← Chat & kontak
├── faq.php                ← FAQ accordion
├── terms.php              ← Syarat & ketentuan
├── privacy.php            ← Kebijakan privasi
├── 404.php                ← Halaman not found
├── .htaccess              ← Security & cache rules
│
├── includes/
│   ├── config.php         ← Konfigurasi & data produk
│   ├── header.php         ← Navbar
│   └── footer.php         ← Footer + chat widget
│
├── assets/
│   ├── css/
│   │   ├── style.css      ← Main stylesheet
│   │   └── loading.css    ← Animasi & loading system
│   ├── js/
│   │   ├── main.js        ← Main JavaScript
│   │   └── loading.js     ← Loading & animation engine
│   └── img/
│       └── placeholder.php ← Fallback image
│
├── chat/
│   ├── chat_engine.php    ← Chat CRUD engine (file-based)
│   ├── api.php            ← Chat REST API (10 endpoints)
│   ├── widget.php         ← Widget chat customer
│   └── data/             ← Penyimpanan pesan (auto-created)
│       └── .htaccess      ← Proteksi akses langsung
│
└── admin/
    ├── login.php          ← Login admin
    ├── logout.php         ← Logout
    ├── index.php          ← Dashboard (revenue, chart, top games)
    ├── orders.php         ← Manajemen pesanan + export CSV
    ├── products.php       ← CRUD produk (tambah/edit/hapus/paket)
    ├── customers.php      ← Data pelanggan + detail + export
    ├── reports.php        ← Laporan & statistik 30 hari
    ├── settings.php       ← Pengaturan 5 tab
    ├── chat.php           ← Live chat admin (real-time)
    ├── products_api.php   ← API CRUD produk (10 endpoints)
    │
    ├── assets/
    │   ├── css/admin.css  ← Admin stylesheet
    │   └── js/admin.js    ← Admin JavaScript
    │
    ├── data/
    │   ├── products.json  ← Storage produk (auto-generated)
    │   └── .htaccess      ← Proteksi akses
    │
    └── includes/
        ├── admin_config.php      ← Konfigurasi & dummy data
        ├── admin_layout.php      ← Sidebar + topbar
        ├── admin_footer.php      ← Footer admin
        └── products_engine.php   ← Engine CRUD produk
```

---

## 🚀 Cara Install

### 1. Upload ke Hosting
- Upload seluruh folder `gamestore/` ke `public_html/` atau subdomain
- Pastikan PHP versi **7.4+** (mendukung arrow functions)

### 2. Set Permission
```bash
chmod 755 chat/data/
chmod 755 admin/data/
```

### 3. Login Admin
Buka: `yourdomain.com/admin/`
- Username: `admin`
- Password: `admin123`

> ⚠️ **Ganti password segera!** Edit di `admin/includes/admin_config.php` baris:
> ```php
> define('ADMIN_PASS', password_hash('passwordBaruKamu', PASSWORD_DEFAULT));
> ```

### 4. Konfigurasi
Edit `includes/config.php`:
```php
define('SITE_NAME', 'GameStore');          // Nama toko
define('WHATSAPP_NUMBER', '628xxxxxxxxx'); // Nomor WA admin
```

---

## ✨ Fitur Lengkap

### 🌐 Website Customer
| Fitur | Keterangan |
|-------|-----------|
| Beranda | Hero animasi, promo banner, produk terpopuler, testimoni, cara order |
| Katalog Produk | Filter kategori (Mobile/PC), foto game asli, harga mulai dari |
| Detail & Order | Pilih paket, masukkan User ID, order via WhatsApp otomatis |
| Keranjang | Multi-item, kode promo, pilih pembayaran, checkout via WA |
| Live Chat | Widget real-time, typing indicator, auto-welcome, notif unread |
| FAQ | Accordion + search real-time |
| Animasi | Page loader, progress bar, scroll reveal, skeleton, ripple, count-up |

### 🔧 Admin Panel
| Fitur | Keterangan |
|-------|-----------|
| Dashboard | Revenue chart, status donut, pesanan terbaru, top games |
| Pesanan | Tabel lengkap, filter status, update status, export CSV, detail modal |
| Produk | CRUD lengkap, wizard 3-step, kelola paket harga inline |
| Pelanggan | Daftar, VIP label, detail modal, export CSV |
| Live Chat | Panel 2-kolom, quick replies, resolve/reopen, info pelanggan |
| Laporan | Chart 30 hari, breakdown per game & payment method |
| Pengaturan | 5 tab: Umum, Pembayaran, Notifikasi, Keamanan, Tampilan |

---

## 🛠️ Teknologi
- **PHP** 7.4+ native (tanpa framework)
- **CSS** pure (custom design system, dark theme)
- **JavaScript** vanilla (tanpa jQuery)
- **Storage** file JSON (tanpa database)
- **Font** Inter dari Google Fonts

---

## 🔒 Keamanan
- Session-based admin authentication
- `password_hash()` untuk password admin
- `.htaccess` protect folder `data/`
- XSS protection dengan `htmlspecialchars()`
- CSRF basic via session check
- Error document 404 custom

---

## 📞 Support
- Live Chat built-in di website
- WhatsApp: sesuai konfigurasi `WHATSAPP_NUMBER`

---

*Made with ❤️ in Indonesia — PHP Native, no framework, no database*
