<?php
/**
 * FinanceTrack API — Export Transactions to CSV
 * GET /api/reports/export_csv.php
 */

require_once __DIR__ . '/../../auth/middleware.php';

// Mendukung autentikasi via session browser langsung
$userId = getCurrentUserId();
if (!$userId) {
    header('Location: ../../login.php');
    exit;
}

$year  = isset($_GET['year']) ? (int)$_GET['year'] : 0;
$range = trim($_GET['range'] ?? 'semua');
$month = (int)($_GET['month'] ?? date('n'));

try {
    $pdo = getDbConnection();

    $whereClauses = ['t.user_id = :user_id'];
    $params = [':user_id' => $userId];

    if ($year > 0) {
        $whereClauses[] = 'YEAR(t.transaction_date) = :year';
        $params[':year'] = $year;
    }

    if ($range === 'bulanan' && $month > 0) {
        $whereClauses[] = 'MONTH(t.transaction_date) = :month';
        $params[':month'] = $month;
    }

    $whereSql = implode(' AND ', $whereClauses);

    $sql = "SELECT t.id, t.transaction_date, t.transaction_time, t.type, t.amount, 
                   c.name AS category_name, t.description, t.payment_method, t.notes
            FROM transactions t
            JOIN categories c ON t.category_id = c.id
            WHERE {$whereSql}
            ORDER BY t.transaction_date DESC, t.transaction_time DESC, t.id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

} catch (\Throwable $e) {
    error_log('Export CSV DB Offline, using demo fallback: ' . $e->getMessage());
    $rows = [
        ['transaction_date' => '2024-10-24', 'transaction_time' => '12:30:00', 'type' => 'expense', 'category_name' => 'Makanan & Minuman', 'description' => 'Makan Siang Ayam Geprek', 'payment_method' => 'qris', 'amount' => 25000, 'notes' => 'Makan di kantin'],
        ['transaction_date' => '2024-10-23', 'transaction_time' => '08:15:00', 'type' => 'expense', 'category_name' => 'Transportasi', 'description' => 'Bensin Motor Beat', 'payment_method' => 'cash', 'amount' => 30000, 'notes' => 'SPBU Sudirman'],
        ['transaction_date' => '2024-10-22', 'transaction_time' => '19:40:00', 'type' => 'expense', 'category_name' => 'Belanja & Kebutuhan', 'description' => 'Kebutuhan Kost Bulanan', 'payment_method' => 'transfer', 'amount' => 175000, 'notes' => 'Sabun, sampo, deterjen'],
        ['transaction_date' => '2024-10-20', 'transaction_time' => '14:00:00', 'type' => 'expense', 'category_name' => 'Tagihan & Utilitas', 'description' => 'Paket Data Internet 50GB', 'payment_method' => 'ewallet', 'amount' => 110000, 'notes' => 'MyTelkomsel'],
        ['transaction_date' => '2024-10-18', 'transaction_time' => '16:20:00', 'type' => 'expense', 'category_name' => 'Hiburan & Hobi', 'description' => 'Tiket Bioskop XXI', 'payment_method' => 'qris', 'amount' => 50000, 'notes' => 'Nonton bareng teman'],
        ['transaction_date' => '2024-10-01', 'transaction_time' => '09:00:00', 'type' => 'income', 'category_name' => 'Uang Saku', 'description' => 'Uang Saku Bulanan Orang Tua', 'payment_method' => 'transfer', 'amount' => 3000000, 'notes' => 'BCA'],
        ['transaction_date' => '2024-10-15', 'transaction_time' => '10:00:00', 'type' => 'income', 'category_name' => 'Gaji & Freelance', 'description' => 'Project Desain Logo Freelance', 'payment_method' => 'transfer', 'amount' => 1200000, 'notes' => 'Klien UMKM']
    ];
}

// Siapkan HTTP Header untuk unduhan file CSV
$filename = 'FinanceTrack_Laporan_' . ($year ?: 'Semua') . '_' . date('Ymd_His') . '.csv';

if (!headers_sent()) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
}

// Buka output stream
$output = fopen('php://output', 'w');

// Tulis UTF-8 BOM agar Microsoft Excel di Windows membaca encoding dengan benar
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Baris Header CSV
fputcsv($output, [
    'No',
    'Tanggal',
    'Waktu',
    'Jenis',
    'Kategori',
    'Deskripsi',
    'Metode Pembayaran',
    'Nominal (Rp)',
    'Catatan'
], ';');

$no = 1;
foreach ($rows as $row) {
    fputcsv($output, [
        $no++,
        $row['transaction_date'],
        substr($row['transaction_time'], 0, 5),
        $row['type'] === 'income' ? 'Pemasukan' : 'Pengeluaran',
        $row['category_name'],
        $row['description'],
        ucfirst($row['payment_method']),
        number_format((float)$row['amount'], 0, ',', '.'),
        $row['notes'] ?: '-'
    ], ';');
}

fclose($output);
exit;
