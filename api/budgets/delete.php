<?php
/**
 * FinanceTrack API — Delete Budget
 * POST /api/budgets/delete.php
 */

require_once __DIR__ . '/../../auth/middleware.php';

$userId = requireApiAuth();
$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$categoryId = trim($input['kategoriId'] ?? $input['category_id'] ?? $_GET['category_id'] ?? '');

if (empty($categoryId)) {
    sendJsonResponse(['success' => false, 'message' => 'Kategori anggaran wajib diisi.'], 400);
}

try {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare('DELETE FROM budgets WHERE user_id = :user_id AND category_id = :cat_id');
    $stmt->execute([':user_id' => $userId, ':cat_id' => $categoryId]);

    sendJsonResponse([
        'success' => true,
        'message' => 'Anggaran kategori berhasil dihapus.'
    ]);
} catch (\Throwable $e) {
    error_log('API Delete Budget Error: ' . $e->getMessage());
    sendJsonResponse(['success' => false, 'message' => 'Gagal menghapus anggaran: ' . $e->getMessage()], 500);
}

