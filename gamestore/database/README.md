# 🗄️ GameStore — Database

## File yang Tersedia

| File | Fungsi |
|------|--------|
| `gamestore.sql` | **All-in-one** — schema + seed data lengkap (GUNAKAN INI) |
| `db.php` | PDO wrapper + konfigurasi koneksi |
| `setup.php` | Web wizard instalasi otomatis |
| `schema.sql` | Schema saja (tanpa data) |
| `seed.sql` | Data awal saja |
| `migrations.sql` | Template untuk update versi |

---

## 🚀 Cara Setup (Pilih salah satu)

### Cara 1: Web Wizard (Direkomendasikan)
1. Upload seluruh folder ke hosting
2. Buka `yourdomain.com/database/setup.php`
3. Isi form koneksi dan klik "Mulai Instalasi"
4. **Hapus `setup.php`** setelah selesai!

### Cara 2: phpMyAdmin
1. Buka phpMyAdmin di hosting kamu
2. Buat database baru bernama `gamestore_db`
3. Pilih database → klik tab **Import**
4. Upload file `gamestore.sql`
5. Klik **Go**

### Cara 3: Command Line
```bash
mysql -u root -p -e "CREATE DATABASE gamestore_db CHARACTER SET utf8mb4;"
mysql -u root -p gamestore_db < gamestore.sql
```

---

## ⚙️ Konfigurasi `db.php`

Edit baris berikut sesuai hosting kamu:

```php
define('DB_HOST', 'localhost');     // biasanya localhost
define('DB_NAME', 'gamestore_db'); // nama database
define('DB_USER', 'root');         // username DB
define('DB_PASS', '');             // password DB
```

Di **cPanel hosting**, nama database biasanya: `namauser_gamestore_db`

---

## 🏗️ Struktur Tabel

| Tabel | Isi |
|-------|-----|
| `admins` | Akun admin panel |
| `categories` | Kategori game (Mobile, PC, Console) |
| `products` | Data game/produk |
| `packages` | Paket harga per produk |
| `customers` | Data pelanggan terdaftar |
| `orders` | Semua pesanan |
| `payment_proofs` | Bukti transfer |
| `chat_sessions` | Sesi chat live |
| `chat_messages` | Pesan chat |
| `promo_codes` | Kode promo |
| `settings` | Pengaturan toko |
| `activity_logs` | Log aktivitas admin |

**Views:**
- `v_order_summary` — Ringkasan order per hari
- `v_product_revenue` — Revenue per produk
- `v_customer_stats` — Statistik pelanggan

---

## 🔑 Login Default

- **Username:** `admin`
- **Password:** `admin123`

> ⚠️ Ganti password segera lewat Admin Panel → Pengaturan → Keamanan

---

## 🔒 Keamanan

Setelah instalasi, proteksi folder `database/`:

```apache
# Tambahkan ke .htaccess di folder database/
Deny from all
```

Atau hapus `setup.php` dari server.
