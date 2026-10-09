<?php
/**
 * FinanceTrack API — List Budgets
 * GET /api/budgets/list.php
 */

require_once __DIR__ . '/../../auth/middleware.php';

$userId = requireApiAuth();

$month = (int)($_GET['month'] ?? date('n'));
$year  = (int)($_GET['year'] ?? date('Y'));

try {
    $pdo = getDbConnection();

    // 1. Ambil seluruh kategori bertipe expense
    $catStmt = $pdo->query("SELECT id, name, icon, color FROM categories WHERE type = 'expense' ORDER BY name ASC");
    $categories = $catStmt->fetchAll();

    // 2. Ambil seluruh budget user pada bulan & tahun ini
    $bgStmt = $pdo->prepare("SELECT id, category_id, amount, notes FROM budgets WHERE user_id = :user_id AND period_month = :m AND period_year = :y");
    $bgStmt->execute([':user_id' => $userId, ':m' => $month, ':y' => $year]);
    $budgetRows = [];
    foreach ($bgStmt->fetchAll() as $b) {
        $budgetRows[$b['category_id']] = $b;
    }

    // 3. Hitung pengeluaran aktual per kategori untuk user ini pada periode yang dipilih
    $spentStmt = $pdo->prepare("SELECT category_id, SUM(amount) AS total_spent 
                                FROM transactions 
                                WHERE user_id = :user_id 
                                  AND type = 'expense' 
                                  AND MONTH(transaction_date) = :m 
                                  AND YEAR(transaction_date) = :y 
                                GROUP BY category_id");
    $spentStmt->execute([':user_id' => $userId, ':m' => $month, ':y' => $year]);
    $spentMap = [];
    foreach ($spentStmt->fetchAll() as $s) {
        $spentMap[$s['category_id']] = (float)$s['total_spent'];
    }

    $budgets = [];
    $totalLimit = 0.0;
    $totalSpent = 0.0;

    foreach ($categories as $cat) {
        $catId = $cat['id'];
        $hasBudget = isset($budgetRows[$catId]);
        $limit = $hasBudget ? (float)$budgetRows[$catId]['amount'] : 500000.0; // default 500rb jika belum diset
        $spent = $spentMap[$catId] ?? 0.0;
        $notes = $hasBudget ? ($budgetRows[$catId]['notes'] ?: $cat['name']) : $cat['name'];

        $totalLimit += $limit;
        $totalSpent += $spent;

        $pct = $limit > 0 ? round(($spent / $limit) * 100, 1) : 0;
        $sisa = $limit - $spent;

        $budgets[] = [
            'id' => $hasBudget ? (int)$budgetRows[$catId]['id'] : null,
            'kategoriId' => $catId,
            'nama' => $cat['name'],
            'icon' => $cat['icon'],
            'warna' => $cat['color'],
            'batas' => $limit,
            'terpakai' => $spent,
            'sisa' => $sisa,
            'persen' => $pct,
            'catatan' => $notes,
            'isCustom' => $hasBudget
        ];
    }

    $totalRemaining = $totalLimit - $totalSpent;
    $totalPct = $totalLimit > 0 ? round(($totalSpent / $totalLimit) * 100, 1) : 0;

    sendJsonResponse([
        'success' => true,
        'period' => [
            'month' => $month,
            'year' => $year,
            'label' => date('F Y', mktime(0, 0, 0, $month, 10, $year))
        ],
        'summary' => [
            'totalLimit' => $totalLimit,
            'totalSpent' => $totalSpent,
            'totalRemaining' => $totalRemaining,
            'percentage' => $totalPct
        ],
        'data' => $budgets
    ]);

} catch (\Throwable $e) {
    error_log('API Budgets List Error: ' . $e->getMessage());
    sendJsonResponse(['success' => false, 'message' => 'Gagal mengambil data anggaran: ' . $e->getMessage()], 500);
}

