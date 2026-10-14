<?php
/**
 * FinanceTrack — Realistic Demo Data Seeder for Middle-Class College Student
 * Profil: Mahasiswa S1 kalangan menengah biasa, kos hemat, uang saku bersahaja (~Rp 1,1 - 1,2 jt/bln), deskripsi singkat 2-3 kata.
 */

require_once __DIR__ . '/../config/database.php';

function seedRealisticStudentData(): void {
    $pdo = getDbConnection();

    echo "=== FinanceTrack: Seeding Middle-Class Student Data ===\n";

    // 1. Pastikan User Demo Ada
    $demoUserStmt = $pdo->prepare("SELECT id, name, email FROM users WHERE email = :email");
    $demoUserStmt->execute([':email' => 'demo@financetrack.test']);
    $user = $demoUserStmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $passHash = password_hash('Demo123!', PASSWORD_BCRYPT);
        $insUser = $pdo->prepare("INSERT INTO users (name, email, password_hash, role, avatar) VALUES (:name, :email, :pass, 'user', 'FJ')");
        $insUser->execute([
            ':name' => 'Fikri Juana',
            ':email' => 'demo@financetrack.test',
            ':pass' => $passHash
        ]);
        $userId = (int)$pdo->lastInsertId();
        echo "[OK] User demo dibuat (ID: {$userId})\n";
    } else {
        $userId = (int)$user['id'];
        echo "[OK] User demo ditemukan (ID: {$userId})\n";
    }

    // 2. Bersihkan Transaksi & Anggaran Lama User Demo
    $pdo->prepare("DELETE FROM transactions WHERE user_id = :uid")->execute([':uid' => $userId]);
    $pdo->prepare("DELETE FROM budgets WHERE user_id = :uid")->execute([':uid' => $userId]);
    echo "[OK] Data transaksi & anggaran lama user demo dibersihkan\n";

    // 3. Pastikan Master Kategori Lengkap & Sesuai
    $categories = [
        ['id' => 'pemasukan-saku', 'name' => 'Uang Saku Orang Tua', 'type' => 'income', 'icon' => 'payments', 'color' => '#10b981'],
        ['id' => 'pemasukan-freelance', 'name' => 'Honor Tambahan Mahasiswa', 'type' => 'income', 'icon' => 'work', 'color' => '#0284c7'],
        ['id' => 'tabungan', 'name' => 'Tarik Tabungan', 'type' => 'income', 'icon' => 'savings', 'color' => '#059669'],
        ['id' => 'makanan', 'name' => 'Makanan & Minuman', 'type' => 'expense', 'icon' => 'restaurant', 'color' => '#0284c7'],
        ['id' => 'transportasi', 'name' => 'Transportasi & Bensin', 'type' => 'expense', 'icon' => 'two_wheeler', 'color' => '#0ea5e9'],
        ['id' => 'internet', 'name' => 'Paket Data & Internet', 'type' => 'expense', 'icon' => 'wifi', 'color' => '#0284c7'],
        ['id' => 'listrik', 'name' => 'Token Listrik Kos', 'type' => 'expense', 'icon' => 'bolt', 'color' => '#f59e0b'],
        ['id' => 'kuliah', 'name' => 'Kuliah & Buku', 'type' => 'expense', 'icon' => 'menu_book', 'color' => '#8b5cf6'],
        ['id' => 'kopi', 'name' => 'Kopi & Belajar', 'type' => 'expense', 'icon' => 'coffee', 'color' => '#0ea5e9'],
        ['id' => 'alat-tulis', 'name' => 'Alat Tulis & Fotokopi', 'type' => 'expense', 'icon' => 'edit_note', 'color' => '#8b5cf6'],
        ['id' => 'hiburan', 'name' => 'Hiburan & Streaming', 'type' => 'expense', 'icon' => 'movie', 'color' => '#ec4899'],
    ];

    $catStmt = $pdo->prepare("INSERT INTO categories (id, name, type, icon, color) 
                              VALUES (:id, :name, :type, :icon, :color)
                              ON DUPLICATE KEY UPDATE name = VALUES(name), type = VALUES(type), icon = VALUES(icon), color = VALUES(color)");
    foreach ($categories as $cat) {
        $catStmt->execute($cat);
    }
    echo "[OK] Master kategori tersinkronisasi (" . count($categories) . " kategori)\n";

    // 4. Pastikan Metode Pembayaran Tersedia
    $paymentMethods = [
        ['id' => 'cash', 'name' => 'Kas Tunai', 'type' => 'cash', 'icon' => 'payments', 'color' => '#10b981'],
        ['id' => 'tabungan', 'name' => 'Rekening Bank BCA', 'type' => 'bank', 'icon' => 'account_balance', 'color' => '#0284c7'],
        ['id' => 'gopay', 'name' => 'GoPay', 'type' => 'ewallet', 'icon' => 'account_balance_wallet', 'color' => '#0ea5e9'],
        ['id' => 'dana', 'name' => 'DANA', 'type' => 'ewallet', 'icon' => 'account_balance_wallet', 'color' => '#0284c7'],
        ['id' => 'ovo', 'name' => 'OVO', 'type' => 'ewallet', 'icon' => 'account_balance_wallet', 'color' => '#8b5cf6'],
    ];

    $pmStmt = $pdo->prepare("INSERT INTO payment_methods (id, name, type, icon, color) 
                             VALUES (:id, :name, :type, :icon, :color)
                             ON DUPLICATE KEY UPDATE name = VALUES(name), type = VALUES(type), icon = VALUES(icon), color = VALUES(color)");
    foreach ($paymentMethods as $pm) {
        $pmStmt->execute($pm);
    }
    echo "[OK] Metode pembayaran tersinkronisasi (" . count($paymentMethods) . " metode)\n";

    // 5. Data Anggaran (Budgets) Mahasiswa Kalangan Menengah
    // Total Plafon sekitar Rp 890.000 - Rp 950.000 (sangat bersahaja dan realistis)
    $budgets = [
        // Oktober 2026 (Bulan aktif saat ini)
        ['user_id' => $userId, 'category_id' => 'makanan', 'amount' => 480000.00, 'period_month' => 10, 'period_year' => 2026, 'notes' => 'Makan warteg dan ramesan kos'],
        ['user_id' => $userId, 'category_id' => 'listrik', 'amount' => 100000.00, 'period_month' => 10, 'period_year' => 2026, 'notes' => 'Token listrik kosan'],
        ['user_id' => $userId, 'category_id' => 'kuliah', 'amount' => 90000.00, 'period_month' => 10, 'period_year' => 2026, 'notes' => 'Fotokopi modul dan tugas'],
        ['user_id' => $userId, 'category_id' => 'transportasi', 'amount' => 100000.00, 'period_month' => 10, 'period_year' => 2026, 'notes' => 'Bensin motor hemat'],
        ['user_id' => $userId, 'category_id' => 'internet', 'amount' => 60000.00, 'period_month' => 10, 'period_year' => 2026, 'notes' => 'Paket kuota mahasiswa'],
        ['user_id' => $userId, 'category_id' => 'kopi', 'amount' => 30000.00, 'period_month' => 10, 'period_year' => 2026, 'notes' => 'Kopi nugas warkop'],
        ['user_id' => $userId, 'category_id' => 'hiburan', 'amount' => 30000.00, 'period_month' => 10, 'period_year' => 2026, 'notes' => 'Cemilan santai bulanan'],

        // September 2026
        ['user_id' => $userId, 'category_id' => 'makanan', 'amount' => 500000.00, 'period_month' => 9, 'period_year' => 2026, 'notes' => 'Makan warteg dan ramesan'],
        ['user_id' => $userId, 'category_id' => 'listrik', 'amount' => 100000.00, 'period_month' => 9, 'period_year' => 2026, 'notes' => 'Token listrik kosan'],
        ['user_id' => $userId, 'category_id' => 'kuliah', 'amount' => 110000.00, 'period_month' => 9, 'period_year' => 2026, 'notes' => 'Kebutuhan awal semester'],
        ['user_id' => $userId, 'category_id' => 'transportasi', 'amount' => 100000.00, 'period_month' => 9, 'period_year' => 2026, 'notes' => 'Bensin motor kampus'],
        ['user_id' => $userId, 'category_id' => 'internet', 'amount' => 60000.00, 'period_month' => 9, 'period_year' => 2026, 'notes' => 'Paket data bulanan'],
        ['user_id' => $userId, 'category_id' => 'kopi', 'amount' => 30000.00, 'period_month' => 9, 'period_year' => 2026, 'notes' => 'Kopi nugas warkop'],
        ['user_id' => $userId, 'category_id' => 'hiburan', 'amount' => 30000.00, 'period_month' => 9, 'period_year' => 2026, 'notes' => 'Hiburan hemat'],

        // Agustus 2026
        ['user_id' => $userId, 'category_id' => 'makanan', 'amount' => 480000.00, 'period_month' => 8, 'period_year' => 2026, 'notes' => 'Makan sehari hari'],
        ['user_id' => $userId, 'category_id' => 'listrik', 'amount' => 100000.00, 'period_month' => 8, 'period_year' => 2026, 'notes' => 'Token listrik kos'],
        ['user_id' => $userId, 'category_id' => 'transportasi', 'amount' => 100000.00, 'period_month' => 8, 'period_year' => 2026, 'notes' => 'Bensin motor'],
        ['user_id' => $userId, 'category_id' => 'internet', 'amount' => 60000.00, 'period_month' => 8, 'period_year' => 2026, 'notes' => 'Kuota belajar'],
    ];

    $insBg = $pdo->prepare("INSERT INTO budgets (user_id, category_id, amount, period_month, period_year, notes) 
                            VALUES (:user_id, :category_id, :amount, :period_month, :period_year, :notes)");
    foreach ($budgets as $b) {
        $insBg->execute($b);
    }
    echo "[OK] Anggaran mahasiswa menengah berhasil disimpan (" . count($budgets) . " pos anggaran)\n";

    // 6. Transaksi Realistis Mahasiswa Kalangan Menengah (Mei - Oktober 2026)
    // ATURAN KETAT:
    // 1. Deskripsi HANYA boleh berisi huruf dan spasi (regex: /^[\p{L}\s]+$/u)
    // 2. Deskripsi SINGKAT (2-3 kata saja)
    // 3. Tanpa beasiswa, nominal anak kos wajar (makan Rp 8k-13k, bensin Rp 20k-25k, listrik Rp 50k)
    $transactions = [
        // ==================== OKTOBER 2026 (Bulan Berjalan s/d 14 Okt) ====================
        // Total Pemasukan: Rp 1.200.000 (Uang saku bulanan Rp 1.100.000 + Honor lab Rp 100.000)
        // Pos Kuliah & Buku dibuat Rp 105.000 (> Batas Rp 90.000 / 116.7%) untuk memicu alert
        [
            'category_id' => 'pemasukan-saku',
            'type' => 'income',
            'amount' => 1100000.00,
            'date' => '2026-10-01',
            'time' => '08:30:00',
            'desc' => 'Uang saku bulanan',
            'method' => 'tabungan',
            'notes' => 'Transfer orang tua'
        ],
        [
            'category_id' => 'listrik',
            'type' => 'expense',
            'amount' => 50000.00,
            'date' => '2026-10-02',
            'time' => '10:15:00',
            'desc' => 'Token listrik',
            'method' => 'dana',
            'notes' => 'Token kos'
        ],
        [
            'category_id' => 'makanan',
            'type' => 'expense',
            'amount' => 12000.00,
            'date' => '2026-10-02',
            'time' => '12:30:00',
            'desc' => 'Makan warteg',
            'method' => 'cash',
            'notes' => 'Sayur dan telur'
        ],
        [
            'category_id' => 'makanan',
            'type' => 'expense',
            'amount' => 10000.00,
            'date' => '2026-10-02',
            'time' => '19:15:00',
            'desc' => 'Nasi telur kos',
            'method' => 'cash',
            'notes' => 'Makan malam'
        ],
        [
            'category_id' => 'internet',
            'type' => 'expense',
            'amount' => 55000.00,
            'date' => '2026-10-03',
            'time' => '11:00:00',
            'desc' => 'Paket internet',
            'method' => 'gopay',
            'notes' => 'Kuota mahasiswa'
        ],
        [
            'category_id' => 'transportasi',
            'type' => 'expense',
            'amount' => 20000.00,
            'date' => '2026-10-03',
            'time' => '16:00:00',
            'desc' => 'Bensin motor',
            'method' => 'cash',
            'notes' => 'Pertalite'
        ],
        [
            'category_id' => 'makanan',
            'type' => 'expense',
            'amount' => 10000.00,
            'date' => '2026-10-03',
            'time' => '07:45:00',
            'desc' => 'Nasi uduk',
            'method' => 'cash',
            'notes' => 'Sarapan'
        ],
        [
            'category_id' => 'kuliah',
            'type' => 'expense',
            'amount' => 25000.00,
            'date' => '2026-10-04',
            'time' => '09:20:00',
            'desc' => 'Fotokopi materi',
            'method' => 'cash',
            'notes' => 'Slide kuliah'
        ],
        [
            'category_id' => 'makanan',
            'type' => 'expense',
            'amount' => 12000.00,
            'date' => '2026-10-04',
            'time' => '12:40:00',
            'desc' => 'Ramesan kantin',
            'method' => 'cash',
            'notes' => 'Kantin kampus'
        ],
        [
            'category_id' => 'makanan',
            'type' => 'expense',
            'amount' => 13000.00,
            'date' => '2026-10-04',
            'time' => '18:50:00',
            'desc' => 'Nasi goreng',
            'method' => 'cash',
            'notes' => 'Depan kos'
        ],
        [
            'category_id' => 'makanan',
            'type' => 'expense',
            'amount' => 9000.00,
            'date' => '2026-10-05',
            'time' => '07:30:00',
            'desc' => 'Lontong sayur',
            'method' => 'cash',
            'notes' => 'Sarapan'
        ],
        [
            'category_id' => 'makanan',
            'type' => 'expense',
            'amount' => 12000.00,
            'date' => '2026-10-05',
            'time' => '13:00:00',
            'desc' => 'Soto kantin',
            'method' => 'gopay',
            'notes' => 'Kantin teknik'
        ],
        [
            'category_id' => 'makanan',
            'type' => 'expense',
            'amount' => 11000.00,
            'date' => '2026-10-06',
            'time' => '12:15:00',
            'desc' => 'Makan warteg',
            'method' => 'cash',
            'notes' => 'Tahu tempe sayur'
        ],
        [
            'category_id' => 'makanan',
            'type' => 'expense',
            'amount' => 12000.00,
            'date' => '2026-10-06',
            'time' => '19:00:00',
            'desc' => 'Penyetan tempe',
            'method' => 'cash',
            'notes' => 'Makan malam'
        ],
        [
            'category_id' => 'makanan',
            'type' => 'expense',
            'amount' => 5000.00,
            'date' => '2026-10-07',
            'time' => '08:00:00',
            'desc' => 'Air galon',
            'method' => 'cash',
            'notes' => 'Isi ulang'
        ],
        [
            'category_id' => 'kopi',
            'type' => 'expense',
            'amount' => 6000.00,
            'date' => '2026-10-07',
            'time' => '20:00:00',
            'desc' => 'Kopi warkop',
            'method' => 'cash',
            'notes' => 'Nugas'
        ],
        [
            'category_id' => 'kuliah',
            'type' => 'expense',
            'amount' => 20000.00,
            'date' => '2026-10-08',
            'time' => '14:10:00',
            'desc' => 'Buku catatan',
            'method' => 'cash',
            'notes' => 'Buku tulis'
        ],
        [
            'category_id' => 'makanan',
            'type' => 'expense',
            'amount' => 13000.00,
            'date' => '2026-10-08',
            'time' => '12:30:00',
            'desc' => 'Ayam geprek',
            'method' => 'gopay',
            'notes' => 'Kantin'
        ],
        [
            'category_id' => 'transportasi',
            'type' => 'expense',
            'amount' => 25000.00,
            'date' => '2026-10-09',
            'time' => '08:00:00',
            'desc' => 'Bensin motor',
            'method' => 'cash',
            'notes' => 'Pertalite'
        ],
        [
            'category_id' => 'makanan',
            'type' => 'expense',
            'amount' => 10000.00,
            'date' => '2026-10-09',
            'time' => '19:15:00',
            'desc' => 'Mie dok dok',
            'method' => 'cash',
            'notes' => 'Warmindo'
        ],
        [
            'category_id' => 'pemasukan-freelance',
            'type' => 'income',
            'amount' => 100000.00,
            'date' => '2026-10-10',
            'time' => '15:00:00',
            'desc' => 'Honor asisten lab',
            'method' => 'gopay',
            'notes' => 'Jaga lab'
        ],
        [
            'category_id' => 'makanan',
            'type' => 'expense',
            'amount' => 12000.00,
            'date' => '2026-10-10',
            'time' => '13:00:00',
            'desc' => 'Soto kantin',
            'method' => 'gopay',
            'notes' => 'Kantin'
        ],
        [
            'category_id' => 'hiburan',
            'type' => 'expense',
            'amount' => 15000.00,
            'date' => '2026-10-11',
            'time' => '17:30:00',
            'desc' => 'Cemilan santai',
            'method' => 'dana',
            'notes' => 'Beli cemilan kos'
        ],
        [
            'category_id' => 'makanan',
            'type' => 'expense',
            'amount' => 12000.00,
            'date' => '2026-10-11',
            'time' => '12:30:00',
            'desc' => 'Makan warteg',
            'method' => 'cash',
            'notes' => 'Makan siang'
        ],
        [
            'category_id' => 'makanan',
            'type' => 'expense',
            'amount' => 13000.00,
            'date' => '2026-10-12',
            'time' => '18:45:00',
            'desc' => 'Nasi goreng',
            'method' => 'cash',
            'notes' => 'Makan malam'
        ],
        [
            'category_id' => 'kopi',
            'type' => 'expense',
            'amount' => 5000.00,
            'date' => '2026-10-12',
            'time' => '21:00:00',
            'desc' => 'Es teh warkop',
            'method' => 'cash',
            'notes' => 'Warkop'
        ],
        [
            'category_id' => 'kuliah',
            'type' => 'expense',
            'amount' => 60000.00,
            'date' => '2026-10-13',
            'time' => '10:45:00',
            'desc' => 'Cetak laporan',
            'method' => 'gopay',
            'notes' => 'Jilid laporan akhir modul'
        ],
        [
            'category_id' => 'makanan',
            'type' => 'expense',
            'amount' => 10000.00,
            'date' => '2026-10-13',
            'time' => '12:30:00',
            'desc' => 'Nasi pecel',
            'method' => 'cash',
            'notes' => 'Kantin'
        ],
        [
            'category_id' => 'makanan',
            'type' => 'expense',
            'amount' => 13000.00,
            'date' => '2026-10-14',
            'time' => '12:15:00',
            'desc' => 'Ayam geprek',
            'method' => 'ovo',
            'notes' => 'Kantin'
        ],
        [
            'category_id' => 'makanan',
            'type' => 'expense',
            'amount' => 8000.00,
            'date' => '2026-10-14',
            'time' => '19:00:00',
            'desc' => 'Nasi telur kos',
            'method' => 'cash',
            'notes' => 'Masak sendiri kos'
        ],

        // ==================== SEPTEMBER 2026 ====================
        // Pemasukan: Rp 1.200.000 (Saku Rp 1.100.000 + Honor Rp 100.000)
        // Pengeluaran: Rp 1.140.000 (Surplus tipis Rp 60.000)
        [
            'category_id' => 'pemasukan-saku',
            'type' => 'income',
            'amount' => 1100000.00,
            'date' => '2026-09-01',
            'time' => '09:00:00',
            'desc' => 'Uang saku bulanan',
            'method' => 'tabungan',
            'notes' => 'Transfer saku'
        ],
        [
            'category_id' => 'listrik',
            'type' => 'expense',
            'amount' => 100000.00,
            'date' => '2026-09-02',
            'time' => '10:30:00',
            'desc' => 'Token listrik',
            'method' => 'dana',
            'notes' => 'Token kos'
        ],
        [
            'category_id' => 'internet',
            'type' => 'expense',
            'amount' => 60000.00,
            'date' => '2026-09-03',
            'time' => '11:15:00',
            'desc' => 'Paket internet',
            'method' => 'gopay',
            'notes' => 'Kuota'
        ],
        [
            'category_id' => 'transportasi',
            'type' => 'expense',
            'amount' => 25000.00,
            'date' => '2026-09-04',
            'time' => '08:00:00',
            'desc' => 'Bensin motor',
            'method' => 'cash',
            'notes' => 'Pertalite'
        ],
        [
            'category_id' => 'kuliah',
            'type' => 'expense',
            'amount' => 65000.00,
            'date' => '2026-09-05',
            'time' => '13:00:00',
            'desc' => 'Buku catatan',
            'method' => 'cash',
            'notes' => 'Buku dan pulpen'
        ],
        [
            'category_id' => 'makanan',
            'type' => 'expense',
            'amount' => 210000.00,
            'date' => '2026-09-07',
            'time' => '19:00:00',
            'desc' => 'Makan warteg',
            'method' => 'cash',
            'notes' => 'Makan minggu satu'
        ],
        [
            'category_id' => 'transportasi',
            'type' => 'expense',
            'amount' => 25000.00,
            'date' => '2026-09-11',
            'time' => '08:30:00',
            'desc' => 'Bensin motor',
            'method' => 'cash',
            'notes' => 'Bensin'
        ],
        [
            'category_id' => 'pemasukan-freelance',
            'type' => 'income',
            'amount' => 100000.00,
            'date' => '2026-09-15',
            'time' => '14:00:00',
            'desc' => 'Honor asisten lab',
            'method' => 'gopay',
            'notes' => 'Honor jaga lab'
        ],
        [
            'category_id' => 'kuliah',
            'type' => 'expense',
            'amount' => 35000.00,
            'date' => '2026-09-16',
            'time' => '11:00:00',
            'desc' => 'Fotokopi materi',
            'method' => 'cash',
            'notes' => 'Materi tugas'
        ],
        [
            'category_id' => 'makanan',
            'type' => 'expense',
            'amount' => 205000.00,
            'date' => '2026-09-17',
            'time' => '19:30:00',
            'desc' => 'Soto kantin',
            'method' => 'gopay',
            'notes' => 'Makan minggu dua'
        ],
        [
            'category_id' => 'kopi',
            'type' => 'expense',
            'amount' => 25000.00,
            'date' => '2026-09-20',
            'time' => '20:30:00',
            'desc' => 'Kopi warkop',
            'method' => 'cash',
            'notes' => 'Warkop'
        ],
        [
            'category_id' => 'transportasi',
            'type' => 'expense',
            'amount' => 45000.00,
            'date' => '2026-09-22',
            'time' => '15:00:00',
            'desc' => 'Ganti oli motor',
            'method' => 'cash',
            'notes' => 'Oli mesin motor'
        ],
        [
            'category_id' => 'hiburan',
            'type' => 'expense',
            'amount' => 25000.00,
            'date' => '2026-09-24',
            'time' => '19:00:00',
            'desc' => 'Cemilan santai',
            'method' => 'dana',
            'notes' => 'Jajan hemat'
        ],
        [
            'category_id' => 'makanan',
            'type' => 'expense',
            'amount' => 180000.00,
            'date' => '2026-09-26',
            'time' => '18:00:00',
            'desc' => 'Mie dok dok',
            'method' => 'cash',
            'notes' => 'Makan minggu tiga'
        ],
        [
            'category_id' => 'makanan',
            'type' => 'expense',
            'amount' => 125000.00,
            'date' => '2026-09-30',
            'time' => '19:00:00',
            'desc' => 'Nasi goreng',
            'method' => 'cash',
            'notes' => 'Makan akhir bulan'
        ],

        // ==================== AGUSTUS 2026 (Awal Semester Ganjil) ====================
        // Pemasukan: Rp 1.150.000 (Saku Awal Semester)
        // Pengeluaran: Rp 1.095.000 (Surplus tipis Rp 55.000)
        [
            'category_id' => 'pemasukan-saku',
            'type' => 'income',
            'amount' => 1150000.00,
            'date' => '2026-08-01',
            'time' => '09:00:00',
            'desc' => 'Uang saku bulanan',
            'method' => 'tabungan',
            'notes' => 'Transfer awal semester'
        ],
        [
            'category_id' => 'listrik',
            'type' => 'expense',
            'amount' => 100000.00,
            'date' => '2026-08-02',
            'time' => '11:00:00',
            'desc' => 'Token listrik',
            'method' => 'dana',
            'notes' => 'Token kos'
        ],
        [
            'category_id' => 'internet',
            'type' => 'expense',
            'amount' => 60000.00,
            'date' => '2026-08-03',
            'time' => '10:00:00',
            'desc' => 'Paket internet',
            'method' => 'gopay',
            'notes' => 'Kuota'
        ],
        [
            'category_id' => 'kuliah',
            'type' => 'expense',
            'amount' => 85000.00,
            'date' => '2026-08-06',
            'time' => '14:00:00',
            'desc' => 'Buku catatan',
            'method' => 'cash',
            'notes' => 'Buku awal kuliah'
        ],
        [
            'category_id' => 'transportasi',
            'type' => 'expense',
            'amount' => 85000.00,
            'date' => '2026-08-10',
            'time' => '08:30:00',
            'desc' => 'Bensin motor',
            'method' => 'cash',
            'notes' => 'Bensin sebulan'
        ],
        [
            'category_id' => 'makanan',
            'type' => 'expense',
            'amount' => 455000.00,
            'date' => '2026-08-15',
            'time' => '12:00:00',
            'desc' => 'Makan warteg',
            'method' => 'cash',
            'notes' => 'Makan sehari hari'
        ],
        [
            'category_id' => 'kopi',
            'type' => 'expense',
            'amount' => 25000.00,
            'date' => '2026-08-20',
            'time' => '20:00:00',
            'desc' => 'Kopi warkop',
            'method' => 'cash',
            'notes' => 'Warkop'
        ],
        [
            'category_id' => 'hiburan',
            'type' => 'expense',
            'amount' => 25000.00,
            'date' => '2026-08-25',
            'time' => '19:00:00',
            'desc' => 'Cemilan santai',
            'method' => 'dana',
            'notes' => 'Jajan hemat'
        ],
        [
            'category_id' => 'kuliah',
            'type' => 'expense',
            'amount' => 30000.00,
            'date' => '2026-08-28',
            'time' => '10:00:00',
            'desc' => 'Fotokopi materi',
            'method' => 'cash',
            'notes' => 'Materi kuliah'
        ],
        [
            'category_id' => 'makanan',
            'type' => 'expense',
            'amount' => 230000.00,
            'date' => '2026-08-30',
            'time' => '18:00:00',
            'desc' => 'Mie dok dok',
            'method' => 'cash',
            'notes' => 'Warmindo'
        ],

        // ==================== JULI 2026 (Libur Semester Hemat) ====================
        // Pemasukan: Rp 600.000 (Saku Libur Hemat)
        // Pengeluaran: Rp 575.000 (Surplus tipis Rp 25.000)
        [
            'category_id' => 'pemasukan-saku',
            'type' => 'income',
            'amount' => 600000.00,
            'date' => '2026-07-01',
            'time' => '09:00:00',
            'desc' => 'Uang saku libur',
            'method' => 'tabungan',
            'notes' => 'Saku liburan'
        ],
        [
            'category_id' => 'listrik',
            'type' => 'expense',
            'amount' => 50000.00,
            'date' => '2026-07-02',
            'time' => '10:00:00',
            'desc' => 'Token listrik',
            'method' => 'dana',
            'notes' => 'Token kos'
        ],
        [
            'category_id' => 'internet',
            'type' => 'expense',
            'amount' => 50000.00,
            'date' => '2026-07-04',
            'time' => '11:00:00',
            'desc' => 'Paket internet',
            'method' => 'gopay',
            'notes' => 'Kuota'
        ],
        [
            'category_id' => 'transportasi',
            'type' => 'expense',
            'amount' => 45000.00,
            'date' => '2026-07-08',
            'time' => '08:00:00',
            'desc' => 'Bensin motor',
            'method' => 'cash',
            'notes' => 'Bensin'
        ],
        [
            'category_id' => 'makanan',
            'type' => 'expense',
            'amount' => 310000.00,
            'date' => '2026-07-15',
            'time' => '12:00:00',
            'desc' => 'Makan warteg',
            'method' => 'cash',
            'notes' => 'Makan hemat'
        ],
        [
            'category_id' => 'kopi',
            'type' => 'expense',
            'amount' => 20000.00,
            'date' => '2026-07-20',
            'time' => '20:00:00',
            'desc' => 'Kopi warkop',
            'method' => 'cash',
            'notes' => 'Warkop'
        ],
        [
            'category_id' => 'hiburan',
            'type' => 'expense',
            'amount' => 20000.00,
            'date' => '2026-07-24',
            'time' => '19:00:00',
            'desc' => 'Cemilan santai',
            'method' => 'dana',
            'notes' => 'Cemilan'
        ],
        [
            'category_id' => 'makanan',
            'type' => 'expense',
            'amount' => 80000.00,
            'date' => '2026-07-30',
            'time' => '18:00:00',
            'desc' => 'Mie dok dok',
            'method' => 'cash',
            'notes' => 'Warmindo'
        ],

        // ==================== JUNI 2026 ====================
        // Pemasukan: Rp 1.100.000
        // Pengeluaran: Rp 1.050.000 (Surplus tipis Rp 50.000)
        [
            'category_id' => 'pemasukan-saku',
            'type' => 'income',
            'amount' => 1100000.00,
            'date' => '2026-06-01',
            'time' => '08:30:00',
            'desc' => 'Uang saku bulanan',
            'method' => 'tabungan',
            'notes' => 'Transfer saku'
        ],
        [
            'category_id' => 'listrik',
            'type' => 'expense',
            'amount' => 100000.00,
            'date' => '2026-06-02',
            'time' => '10:00:00',
            'desc' => 'Token listrik',
            'method' => 'dana',
            'notes' => 'Token'
        ],
        [
            'category_id' => 'internet',
            'type' => 'expense',
            'amount' => 60000.00,
            'date' => '2026-06-03',
            'time' => '11:00:00',
            'desc' => 'Paket internet',
            'method' => 'gopay',
            'notes' => 'Kuota'
        ],
        [
            'category_id' => 'kuliah',
            'type' => 'expense',
            'amount' => 65000.00,
            'date' => '2026-06-06',
            'time' => '13:00:00',
            'desc' => 'Fotokopi materi',
            'method' => 'cash',
            'notes' => 'Materi ujian'
        ],
        [
            'category_id' => 'transportasi',
            'type' => 'expense',
            'amount' => 75000.00,
            'date' => '2026-06-10',
            'time' => '08:00:00',
            'desc' => 'Bensin motor',
            'method' => 'cash',
            'notes' => 'Bensin'
        ],
        [
            'category_id' => 'makanan',
            'type' => 'expense',
            'amount' => 450000.00,
            'date' => '2026-06-15',
            'time' => '12:00:00',
            'desc' => 'Makan warteg',
            'method' => 'cash',
            'notes' => 'Makan sehari hari'
        ],
        [
            'category_id' => 'kopi',
            'type' => 'expense',
            'amount' => 25000.00,
            'date' => '2026-06-20',
            'time' => '20:00:00',
            'desc' => 'Kopi warkop',
            'method' => 'cash',
            'notes' => 'Warkop'
        ],
        [
            'category_id' => 'hiburan',
            'type' => 'expense',
            'amount' => 25000.00,
            'date' => '2026-06-25',
            'time' => '19:00:00',
            'desc' => 'Cemilan santai',
            'method' => 'dana',
            'notes' => 'Jajan'
        ],
        [
            'category_id' => 'makanan',
            'type' => 'expense',
            'amount' => 250000.00,
            'date' => '2026-06-29',
            'time' => '18:00:00',
            'desc' => 'Mie dok dok',
            'method' => 'cash',
            'notes' => 'Warmindo'
        ],

        // ==================== MEI 2026 ====================
        // Pemasukan: Rp 1.100.000
        // Pengeluaran: Rp 1.040.000 (Surplus tipis Rp 60.000)
        [
            'category_id' => 'pemasukan-saku',
            'type' => 'income',
            'amount' => 1100000.00,
            'date' => '2026-05-01',
            'time' => '08:30:00',
            'desc' => 'Uang saku bulanan',
            'method' => 'tabungan',
            'notes' => 'Transfer saku'
        ],
        [
            'category_id' => 'listrik',
            'type' => 'expense',
            'amount' => 100000.00,
            'date' => '2026-05-02',
            'time' => '10:00:00',
            'desc' => 'Token listrik',
            'method' => 'dana',
            'notes' => 'Token'
        ],
        [
            'category_id' => 'internet',
            'type' => 'expense',
            'amount' => 60000.00,
            'date' => '2026-05-03',
            'time' => '11:00:00',
            'desc' => 'Paket internet',
            'method' => 'gopay',
            'notes' => 'Kuota'
        ],
        [
            'category_id' => 'kuliah',
            'type' => 'expense',
            'amount' => 60000.00,
            'date' => '2026-05-05',
            'time' => '13:00:00',
            'desc' => 'Fotokopi materi',
            'method' => 'cash',
            'notes' => 'Materi kuliah'
        ],
        [
            'category_id' => 'transportasi',
            'type' => 'expense',
            'amount' => 75000.00,
            'date' => '2026-05-08',
            'time' => '08:00:00',
            'desc' => 'Bensin motor',
            'method' => 'cash',
            'notes' => 'Bensin'
        ],
        [
            'category_id' => 'makanan',
            'type' => 'expense',
            'amount' => 460000.00,
            'date' => '2026-05-15',
            'time' => '12:00:00',
            'desc' => 'Makan warteg',
            'method' => 'cash',
            'notes' => 'Makan warteg'
        ],
        [
            'category_id' => 'kopi',
            'type' => 'expense',
            'amount' => 25000.00,
            'date' => '2026-05-20',
            'time' => '20:00:00',
            'desc' => 'Kopi warkop',
            'method' => 'cash',
            'notes' => 'Warkop'
        ],
        [
            'category_id' => 'hiburan',
            'type' => 'expense',
            'amount' => 20000.00,
            'date' => '2026-05-24',
            'time' => '19:00:00',
            'desc' => 'Cemilan santai',
            'method' => 'dana',
            'notes' => 'Jajan'
        ],
        [
            'category_id' => 'makanan',
            'type' => 'expense',
            'amount' => 240000.00,
            'date' => '2026-05-30',
            'time' => '18:00:00',
            'desc' => 'Mie dok dok',
            'method' => 'cash',
            'notes' => 'Warmindo'
        ],
    ];

    // Validasi Regex Ketat Sebelum Disimpan: Hanya huruf dan spasi
    $regex = '/^[\p{L}\s]+$/u';
    $invalidDescs = [];
    foreach ($transactions as $idx => $t) {
        if (!preg_match($regex, $t['desc'])) {
            $invalidDescs[] = "Index {$idx}: '{$t['desc']}'";
        }
    }
    if (!empty($invalidDescs)) {
        throw new Exception("Ditemukan deskripsi transaksi tidak valid (hanya huruf dan spasi diperbolehkan):\n" . implode("\n", $invalidDescs));
    }
    echo "[OK] Seluruh deskripsi transaksi lolos validasi huruf dan spasi (/^[\\p{L}\\s]+$/u)\n";

    $insTx = $pdo->prepare("INSERT INTO transactions (user_id, category_id, type, amount, transaction_date, transaction_time, description, payment_method, notes)
                           VALUES (:user_id, :category_id, :type, :amount, :date, :time, :desc, :method, :notes)");

    foreach ($transactions as $t) {
        $insTx->execute([
            ':user_id' => $userId,
            ':category_id' => $t['category_id'],
            ':type' => $t['type'],
            ':amount' => $t['amount'],
            ':date' => $t['date'],
            ':time' => $t['time'],
            ':desc' => $t['desc'],
            ':method' => $t['method'],
            ':notes' => $t['notes'] ?? null
        ]);
    }
    echo "[OK] Berhasil menyimpan " . count($transactions) . " transaksi mahasiswa menengah ke database!\n";

    // 7. Verifikasi Ringkasan Data yang Dihasilkan
    $summaryStmt = $pdo->prepare("SELECT 
        COUNT(*) as total_tx,
        SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income,
        SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expense
        FROM transactions WHERE user_id = :uid");
    $summaryStmt->execute([':uid' => $userId]);
    $sum = $summaryStmt->fetch(PDO::FETCH_ASSOC);

    echo "\n=== Ringkasan Finansial Mahasiswa Menengah ===\n";
    echo "Total Transaksi: " . $sum['total_tx'] . "\n";
    echo "Total Pemasukan (Semua Bulan): Rp " . number_format($sum['total_income'], 0, ',', '.') . "\n";
    echo "Total Pengeluaran (Semua Bulan): Rp " . number_format($sum['total_expense'], 0, ',', '.') . "\n";
    echo "Sisa Saldo Kas: Rp " . number_format($sum['total_income'] - $sum['total_expense'], 0, ',', '.') . "\n";

    // Cek Oktober 2026
    $octStmt = $pdo->prepare("SELECT 
        SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as oct_income,
        SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as oct_expense
        FROM transactions WHERE user_id = :uid AND transaction_date LIKE '2026-10%'");
    $octStmt->execute([':uid' => $userId]);
    $oct = $octStmt->fetch(PDO::FETCH_ASSOC);

    echo "Pemasukan Oktober 2026: Rp " . number_format($oct['oct_income'], 0, ',', '.') . "\n";
    echo "Pengeluaran Oktober 2026 (s/d tgl 14): Rp " . number_format($oct['oct_expense'], 0, ',', '.') . "\n";
    echo "Surplus Kas Oktober 2026: Rp " . number_format($oct['oct_income'] - $oct['oct_expense'], 0, ',', '.') . "\n";
    echo "=== Seeding Selesai dengan Sukses ===\n";
}

if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    try {
        seedRealisticStudentData();
    } catch (\Throwable $e) {
        echo "[ERROR] " . $e->getMessage() . "\n";
        exit(1);
    }
}
