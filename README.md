# FinanceTrack — Aplikasi Manajemen Keuangan Mahasiswa

FinanceTrack adalah aplikasi web pencatatan keuangan pribadi dan pemantauan anggaran yang dirancang khusus untuk kebutuhan mahasiswa. Aplikasi ini dibangun dengan arsitektur bersih menggunakan HTML5, CSS3, JavaScript, Bootstrap 5, Chart.js, serta backend PHP 8+ dengan database MySQL/MariaDB menggunakan PDO dan Prepared Statements.

---

## 🚀 Panduan Instalasi & Menjalankan di XAMPP

### 1. Prasyarat
- **XAMPP** (versi dengan PHP 8.1+ dan MySQL / MariaDB aktif)
- Ekstensi PHP **GD** harus diaktifkan (hapus titik koma `;` pada `;extension=gd` di `php.ini`) untuk fitur upload foto profil.
- Apache Web Server (Port 80)
- MySQL Server (Port 3306)

### 2. Penempatan Direktori Project
Tempatkan folder proyek ke dalam direktori `htdocs` XAMPP:
```text
C:\xampp\htdocs\FinanceTrack\
```
*(Atau buat direktori junction/symlink jika folder berada di lokasi lain).*

### 3. Konfigurasi Database
Konfigurasi database berada di file `config/database.php`. Pengaturan bawaan telah disesuaikan dengan kredensial default XAMPP:
- **Host:** `127.0.0.1`
- **Port:** `3306`
- **Nama Database:** `financetrack_db`
- **User:** `root`
- **Password:** *(kosong)*

### 4. Migrasi Skema & Data Awal (Seed)
Jalankan skrip migrasi database melalui terminal/command prompt:
```bash
php database/migrate.php
```
Skrip ini akan otomatis membuat database `financetrack_db`, tabel `users`, `categories`, `transactions`, `budgets`, serta mengisi akun demo dan data transaksi awal.

### 5. Mengakses Aplikasi
1. Buka browser dan arahkan ke:
   ```text
   http://localhost/FinanceTrack/
   ```
2. Halaman awal akan mengarahkan ke halaman login atau dashboard.
3. Kredensial Akun Demo:
   - **Email:** `demo@financetrack.test`
   - **Password:** `Demo123!`

---

## 📁 Struktur Halaman Aplikasi (PHP)
- `index.php` — Router utama (mengarahkan user terotentikasi ke dashboard atau halaman login)
- `login.php` — Halaman otentikasi masuk pengguna
- `pages/dashboard.php` — Ringkasan saldo, tren kas, dan alokasi pengeluaran
- `pages/transaksi.php` — Tabel master data transaksi dengan filter, pencarian, dan paginasi
- `pages/form-transaksi.php` — Form tambah dan edit transaksi beserta pratinjau langsung & upload bukti
- `pages/anggaran.php` — Manajemen batas anggaran per kategori & pemantauan progres realisasi
- `pages/laporan.php` — Laporan keuangan berkala, skor kesehatan keuangan, dan ekspor data CSV
- `pages/pengaturan.php` — Preferensi akun, reset data demo, dan logout sesi
- `api/` — Endpoint RESTful API untuk operasi CRUD terisolasi per sesi user
