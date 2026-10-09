<?php
/**
 * FinanceTrack API — Reports Summary
 * GET /api/reports/summary.php
 */

require_once __DIR__ . '/../../auth/middleware.php';

$userId = requireApiAuth();
$range = trim($_GET['range'] ?? 'bulanan');
$year  = (int)($_GET['year'] ?? date('Y'));
$month = (int)($_GET['month'] ?? date('n'));

try {
    $pdo = getDbConnection();

    // Query filter berdasarkan rentang waktu
    $dateWhere = "user_id = :user_id AND YEAR(transaction_date) = :year";
    $params = [':user_id' => $userId, ':year' => $year];

    if ($range === 'bulanan') {
        $dateWhere .= " AND MONTH(transaction_date) = :month";
        $params[':month'] = $month;
    } elseif ($range === 'kuartalan') {
        // Kuartal saat ini
        $quarter = ceil($month / 3);
        $startM = ($quarter - 1) * 3 + 1;
        $endM = $quarter * 3;
        $dateWhere .= " AND MONTH(transaction_date) BETWEEN {$startM} AND {$endM}";
    }

    // 1. Total Pemasukan & Pengeluaran Periode
    $sumStmt = $pdo->prepare("SELECT 
        COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) AS total_income,
        COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) AS total_expense
        FROM transactions WHERE {$dateWhere}");
    $sumStmt->execute($params);
    $sumData = $sumStmt->fetch();

    $pemasukan = (float)$sumData['total_income'];
    $pengeluaran = (float)$sumData['total_expense'];
    $tabungan = $pemasukan - $pengeluaran;
    $rasioTabungan = $pemasukan > 0 ? round(($tabungan / $pemasukan) * 100, 1) : 0;

    // 2. Rekap Bulanan Sepanjang Tahun Terpilih (12 bulan)
    $monthlyRecap = [];
    $monthsName = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    for ($m = 1; $m <= 12; $m++) {
        $mStmt = $pdo->prepare("SELECT 
            COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) AS inc,
            COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) AS exp
            FROM transactions 
            WHERE user_id = :user_id AND YEAR(transaction_date) = :year AND MONTH(transaction_date) = :m");
        $mStmt->execute([':user_id' => $userId, ':year' => $year, ':m' => $m]);
        $mRow = $mStmt->fetch();

        $monthlyRecap[] = [
            'bulan' => $monthsName[$m - 1],
            'bulanNum' => $m,
            'pemasukan' => (float)$mRow['inc'],
            'pengeluaran' => (float)$mRow['exp']
        ];
    }

    // 3. Rekap Per Kategori
    $catStmt = $pdo->prepare("SELECT c.id, c.name, c.icon, c.color, 
                                     COUNT(t.id) AS tx_count,
                                     COALESCE(SUM(t.amount), 0) AS total_amount
                              FROM categories c
                              LEFT JOIN transactions t ON c.id = t.category_id AND {$dateWhere}
                              WHERE c.type = 'expense'
                              GROUP BY c.id, c.name, c.icon, c.color
                              ORDER BY total_amount DESC");
    $catStmt->execute($params);
    $categoryRows = [];
    foreach ($catStmt->fetchAll() as $row) {
        $tot = (float)$row['total_amount'];
        $pct = $pengeluaran > 0 ? round(($tot / $pengeluaran) * 100, 1) : 0;
        $categoryRows[] = [
            'kategoriId' => $row['id'],
            'nama' => $row['name'],
            'icon' => $row['icon'],
            'warna' => $row['color'],
            'jumlahTransaksi' => (int)$row['tx_count'],
            'total' => $tot,
            'persen' => $pct
        ];
    }

    sendJsonResponse([
        'success' => true,
        'filter' => [
            'range' => $range,
            'year' => $year,
            'month' => $month
        ],
        'summary' => [
            'pemasukan' => $pemasukan,
            'pengeluaran' => $pengeluaran,
            'sisaTabungan' => $tabungan,
            'rasioTabungan' => $rasioTabungan
        ],
        'monthlyRecap' => $monthlyRecap,
        'categoryReport' => $categoryRows
    ]);

} catch (\Throwable $e) {
    error_log('API Reports Summary Error: ' . $e->getMessage());
    sendJsonResponse(['success' => false, 'message' => 'Gagal memuat laporan: ' . $e->getMessage()], 500);
}

