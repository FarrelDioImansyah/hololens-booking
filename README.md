# 🥽 HoloLens Booking System — Laravel

Sistem booking perangkat HoloLens untuk laboratorium.
8 kelompok · 2 perangkat · Batas 20 jam/minggu · Anti double booking

---

## 📋 Spesifikasi Sistem

| Fitur | Detail |
|-------|--------|
| Kelompok | 8 kelompok, masing-masing 1 akun |
| Perangkat | 2 HoloLens |
| Jam operasional | 08:00 – 21:00 |
| Durasi sesi | 1 jam per sesi |
| Batas kuota | 20 jam / kelompok / minggu |
| Reset kuota | Otomatis setiap Senin 00:00 |

---

## 🚀 Cara Instalasi

### 1. Buat project Laravel baru
```bash
composer create-project laravel/laravel hololens-booking
cd hololens-booking
```

### 2. Salin semua file project ini ke folder laravel
Salin setiap file ke path yang sesuai (ikuti struktur folder di project ini).

### 3. Konfigurasi .env
```env
APP_NAME="HoloLens Booking"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hololens_booking
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Buat database
```sql
CREATE DATABASE hololens_booking;
```

### 5. Jalankan migration + seeder
```bash
php artisan migrate
php artisan db:seed
```

### 6. Daftarkan middleware (Laravel 10)
Buka `app/Http/Kernel.php`, tambahkan di `$routeMiddleware`:
```php
'auth.custom' => \App\Http\Middleware\AuthMiddleware::class,
'admin'       => \App\Http\Middleware\AdminMiddleware::class,
```

### 7. Jalankan server
```bash
php artisan serve
```

Buka browser: http://localhost:8000

---

## 👥 Akun Default

| Username | Password | Role |
|----------|----------|------|
| admin | admin123 | Admin |
| kelompok1 | kelompok1 | Kelompok |
| kelompok2 | kelompok2 | Kelompok |
| kelompok3 | kelompok3 | Kelompok |
| ... | ... | ... |
| kelompok8 | kelompok8 | Kelompok |

---

## ⚙️ Fitur Lengkap

### Untuk Kelompok
- ✅ Login per akun kelompok
- ✅ Dashboard: sisa jam, booking aktif, progress bar kuota
- ✅ Kalender jadwal dengan kode warna (kosong/milik sendiri/terisi)
- ✅ Booking slot dengan konfirmasi modal
- ✅ Cancel booking sendiri (jam dikembalikan ke kuota)
- ✅ Filter jadwal per tanggal

### Untuk Admin
- ✅ Dashboard statistik penggunaan
- ✅ Lihat semua booking (filter tanggal/kelompok/status)
- ✅ Hapus booking manapun
- ✅ Kelola akun kelompok (tambah, lihat)
- ✅ Atur batas jam per kelompok per minggu
- ✅ Reset jam manual

### Sistem Otomatis
- ✅ Validasi slot kosong (dengan DB::lockForUpdate)
- ✅ Validasi batas jam per minggu
- ✅ Cegah double booking (unique constraint + query lock)
- ✅ Artisan command: `php artisan booking:reset-weekly`
- ✅ Laravel Scheduler: reset otomatis setiap Senin 00:00

---

## 🔧 Perintah Artisan Berguna

```bash
# Reset jam manual (testing)
php artisan booking:reset-weekly --force

# Jalankan scheduler (cron di production)
php artisan schedule:run

# Refresh database + seeder ulang
php artisan migrate:fresh --seed
```

### Setup Cron di Server (Production)
Tambahkan baris ini ke crontab server:
```
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

---

## 📁 Struktur File

```
app/
├── Console/
│   ├── Commands/ResetWeeklyLimits.php   ← Artisan command reset
│   └── Kernel.php                        ← Scheduler config
├── Http/
│   ├── Controllers/
│   │   ├── AuthController.php            ← Login & logout
│   │   ├── BookingController.php         ← Jadwal, store, cancel
│   │   ├── DashboardController.php       ← Dashboard kelompok
│   │   └── AdminController.php           ← Panel admin
│   └── Middleware/
│       ├── AuthMiddleware.php             ← Cek login
│       └── AdminMiddleware.php            ← Cek role admin
├── Models/
│   ├── User.php
│   ├── Hololens.php
│   ├── Booking.php
│   └── UsageLimit.php
database/
├── migrations/                            ← 4 file migration
└── seeders/DatabaseSeeder.php             ← Data awal
resources/views/
├── layouts/app.blade.php                  ← Layout utama + navbar
├── auth/login.blade.php
├── dashboard/index.blade.php
├── schedule/index.blade.php               ← Halaman jadwal utama
└── admin/
    ├── dashboard.blade.php
    ├── bookings.blade.php
    └── users.blade.php
routes/web.php                             ← Semua route
```

---

## 🛡️ Keamanan

- Password di-hash dengan `Hash::make()` (bcrypt)
- Semua form menggunakan `@csrf`
- AJAX request menggunakan X-CSRF-TOKEN header
- Booking hanya bisa dibatalkan oleh pemiliknya (atau admin)
- Middleware memblokir akses halaman tanpa login
- `DB::lockForUpdate()` mencegah race condition saat booking bersamaan
