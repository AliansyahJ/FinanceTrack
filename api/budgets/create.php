<?php
/**
 * FinanceTrack API — Create/Update Budget
 * POST /api/budgets/create.php
 */

require_once __DIR__ . '/../../auth/middleware.php';

$userId = requireApiAuth();

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

$categoryId  = trim($input['kategoriId'] ?? $input['category_id'] ?? '');
$amount      = (float)str_replace(['.', ',', 'Rp', ' '], '', $input['batas'] ?? $input['amount'] ?? 0);
$month       = (int)($input['month'] ?? date('n'));
$year        = (int)($input['year'] ?? date('Y'));
$notes       = trim($input['catatan'] ?? $input['notes'] ?? '');

if (empty($categoryId)) {
    sendJsonResponse(['success' => false, 'message' => 'Kategori anggaran wajib dipilih.'], 400);
}
if ($amount <= 0) {
    sendJsonResponse(['success' => false, 'message' => 'Nominal batas anggaran harus lebih besar dari 0.'], 400);
}

try {
    $pdo = getDbConnection();

    // Verifikasi kategori
    $catStmt = $pdo->prepare('SELECT id, name, icon, color FROM categories WHERE id = :id LIMIT 1');
    $catStmt->execute([':id' => $categoryId]);
    $cat = $catStmt->fetch();

    if (!$cat) {
        sendJsonResponse(['success' => false, 'message' => 'Kategori tidak valid.'], 400);
    }

    if (empty($notes)) {
        $notes = $cat['name'];
    }

    $sql = "INSERT INTO budgets (user_id, category_id, amount, period_month, period_year, notes)
            VALUES (:user_id, :cat_id, :amount, :month, :year, :notes)
            ON DUPLICATE KEY UPDATE 
                amount = VALUES(amount),
                notes = VALUES(notes),
                updated_at = CURRENT_TIMESTAMP";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':user_id' => $userId,
        ':cat_id'  => $categoryId,
        ':amount'  => $amount,
        ':month'   => $month,
        ':year'    => $year,
        ':notes'   => $notes
    ]);

    // Hitung pengeluaran aktual bulan ini untuk kategori ini
    $spentStmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) FROM transactions 
                                WHERE user_id = :user_id 
                                  AND category_id = :cat_id 
                                  AND type = 'expense' 
                                  AND MONTH(transaction_date) = :m 
                                  AND YEAR(transaction_date) = :y");
    $spentStmt->execute([
        ':user_id' => $userId,
        ':cat_id'  => $categoryId,
        ':m'       => $month,
        ':y'       => $year
    ]);
    $spent = (float)$spentStmt->fetchColumn();

    $pct = $amount > 0 ? round(($spent / $amount) * 100, 1) : 0;
    $sisa = $amount - $spent;

    sendJsonResponse([
        'success' => true,
        'message' => 'Batas anggaran untuk kategori ' . $cat['name'] . ' berhasil disimpan.',
        'data' => [
            'kategoriId' => $categoryId,
            'nama' => $cat['name'],
            'icon' => $cat['icon'],
            'warna' => $cat['color'],
            'batas' => $amount,
            'terpakai' => $spent,
            'sisa' => $sisa,
            'persen' => $pct,
            'catatan' => $notes
        ]
    ]);

} catch (\Throwable $e) {
    error_log('API Budget Save Error: ' . $e->getMessage());
    sendJsonResponse(['success' => false, 'message' => 'Gagal menyimpan anggaran: ' . $e->getMessage()], 500);
}

