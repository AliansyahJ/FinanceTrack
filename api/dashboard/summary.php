<?php
/**
 * FinanceTrack API — Dashboard Summary
 * GET /api/dashboard/summary.php
 */

require_once __DIR__ . '/../../auth/middleware.php';

$userId = requireApiAuth();
$currentMonth = (int)date('n');
$currentYear  = (int)date('Y');

try {
    $pdo = getDbConnection();

    // 1. Total Saldo Kumulatif User
    $cumStmt = $pdo->prepare("SELECT 
        COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) AS total_income_all,
        COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) AS total_expense_all
        FROM transactions WHERE user_id = :user_id");
    $cumStmt->execute([':user_id' => $userId]);
    $cum = $cumStmt->fetch();
    $saldo = (float)$cum['total_income_all'] - (float)$cum['total_expense_all'];

    // 2. Ringkasan Bulan Berjalan
    $monthStmt = $pdo->prepare("SELECT 
        COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) AS income_month,
        COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) AS expense_month
        FROM transactions 
        WHERE user_id = :user_id 
          AND MONTH(transaction_date) = :m 
          AND YEAR(transaction_date) = :y");
    $monthStmt->execute([':user_id' => $userId, ':m' => $currentMonth, ':y' => $currentYear]);
    $monthData = $monthStmt->fetch();

    $pemasukanBulanIni = (float)$monthData['income_month'];
    $pengeluaranBulanIni = (float)$monthData['expense_month'];

    // 3. Total Batas Anggaran Bulan Berjalan
    $bgStmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) FROM budgets 
                             WHERE user_id = :user_id 
                               AND period_month = :m 
                               AND period_year = :y");
    $bgStmt->execute([':user_id' => $userId, ':m' => $currentMonth, ':y' => $currentYear]);
    $totalAnggaran = (float)$bgStmt->fetchColumn();
    if ($totalAnggaran <= 0) {
        $totalAnggaran = 3200000.0; // Nilai default wajar jika user belum menyetel
    }

    $sisaAnggaran = $totalAnggaran - $pengeluaranBulanIni;

    // 4. Transaksi Terbaru (5 teratas)
    $recentStmt = $pdo->prepare("SELECT t.id, t.type, t.amount, t.transaction_date, t.transaction_time, 
                                        t.description, t.payment_method, t.receipt_path,
                                        c.name AS category_name, c.icon AS category_icon, c.color AS category_color
                                 FROM transactions t
                                 JOIN categories c ON t.category_id = c.id
                                 WHERE t.user_id = :user_id
                                 ORDER BY t.transaction_date DESC, t.transaction_time DESC, t.id DESC
                                 LIMIT 5");
    $recentStmt->execute([':user_id' => $userId]);
    $recentTransactions = array_map(function ($r) {
        return [
            'id' => (int)$r['id'],
            'tanggal' => $r['transaction_date'],
            'jam' => substr($r['transaction_time'], 0, 5),
            'deskripsi' => $r['description'],
            'kategori' => $r['category_name'],
            'kategoriIcon' => $r['category_icon'],
            'kategoriWarna' => $r['category_color'],
            'metode' => $r['payment_method'],
            'tipe' => $r['type'],
            'jumlah' => (float)$r['amount'],
            'struk' => $r['receipt_path']
        ];
    }, $recentStmt->fetchAll());

    // 5. Komposisi Pengeluaran Bulan Berjalan
    $compStmt = $pdo->prepare("SELECT c.id, c.name, c.icon, c.color, SUM(t.amount) AS total
                               FROM transactions t
                               JOIN categories c ON t.category_id = c.id
                               WHERE t.user_id = :user_id 
                                 AND t.type = 'expense'
                                 AND MONTH(t.transaction_date) = :m
                                 AND YEAR(t.transaction_date) = :y
                               GROUP BY c.id, c.name, c.icon, c.color
                               ORDER BY total DESC");
    $compStmt->execute([':user_id' => $userId, ':m' => $currentMonth, ':y' => $currentYear]);
    $composition = array_map(function ($r) {
        return [
            'id' => $r['id'],
            'nama' => $r['name'],
            'nilai' => (float)$r['total'],
            'warna' => $r['color'] ?: '#0284c7',
            'icon' => $r['icon']
        ];
    }, $compStmt->fetchAll());

    // 6. Tren 6 Bulan Terakhir
    $trendLabels = [];
    $trendIncome = [];
    $trendExpense = [];

    for ($i = 5; $i >= 0; $i--) {
        $timestamp = strtotime("-{$i} month");
        $m = (int)date('n', $timestamp);
        $y = (int)date('Y', $timestamp);
        $lbl = date('M', $timestamp);

        $tStmt = $pdo->prepare("SELECT 
            COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) AS inc,
            COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) AS exp
            FROM transactions 
            WHERE user_id = :user_id 
              AND MONTH(transaction_date) = :m 
              AND YEAR(transaction_date) = :y");
        $tStmt->execute([':user_id' => $userId, ':m' => $m, ':y' => $y]);
        $tData = $tStmt->fetch();

        $trendLabels[] = $lbl;
        $trendIncome[] = (float)$tData['inc'];
        $trendExpense[] = (float)$tData['exp'];
    }

    // 7. Anggaran Mini (3 teratas)
    $miniBgStmt = $pdo->prepare("SELECT b.category_id, b.amount, c.name AS category_name, c.icon, c.color
                                 FROM budgets b
                                 JOIN categories c ON b.category_id = c.id
                                 WHERE b.user_id = :user_id
                                   AND b.period_month = :m
                                   AND b.period_year = :y
                                 LIMIT 3");
    $miniBgStmt->execute([':user_id' => $userId, ':m' => $currentMonth, ':y' => $currentYear]);
    $miniBudgets = [];
    foreach ($miniBgStmt->fetchAll() as $b) {
        $spentStmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) FROM transactions 
                                    WHERE user_id = :user_id 
                                      AND category_id = :cat_id 
                                      AND type = 'expense' 
                                      AND MONTH(transaction_date) = :m 
                                      AND YEAR(transaction_date) = :y");
        $spentStmt->execute([
            ':user_id' => $userId,
            ':cat_id'  => $b['category_id'],
            ':m'       => $currentMonth,
            ':y'       => $currentYear
        ]);
        $sp = (float)$spentStmt->fetchColumn();
        $lim = (float)$b['amount'];
        $pct = $lim > 0 ? round(($sp / $lim) * 100, 1) : 0;

        $miniBudgets[] = [
            'kategoriId' => $b['category_id'],
            'kategoriNama' => $b['category_name'],
            'icon' => $b['icon'],
            'batas' => $lim,
            'terpakai' => $sp,
            'persen' => $pct
        ];
    }

    sendJsonResponse([
        'success' => true,
        'summary' => [
            'saldo' => $saldo,
            'pemasukan' => $pemasukanBulanIni,
            'pengeluaran' => $pengeluaranBulanIni,
            'totalAnggaran' => $totalAnggaran,
            'sisaAnggaran' => $sisaAnggaran,
            'periode' => date('F Y')
        ],
        'recentTransactions' => $recentTransactions,
        'expenseComposition' => $composition,
        'trend' => [
            'labels' => $trendLabels,
            'pemasukan' => $trendIncome,
            'pengeluaran' => $trendExpense
        ],
        'miniBudgets' => $miniBudgets
    ]);

} catch (\Throwable $e) {
    error_log('API Dashboard Summary Error: ' . $e->getMessage());
    sendJsonResponse(['success' => false, 'message' => 'Gagal memuat ringkasan dashboard: ' . $e->getMessage()], 500);
}

