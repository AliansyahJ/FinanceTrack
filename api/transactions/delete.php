<?php
/**
 * FinanceTrack API — Delete Transaction
 * POST /api/transactions/delete.php
 */

require_once __DIR__ . '/../../auth/middleware.php';

$userId = requireApiAuth();

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$id = (int)($input['id'] ?? $_GET['id'] ?? 0);

if ($id <= 0) {
    sendJsonResponse(['success' => false, 'message' => 'ID transaksi tidak valid.'], 400);
}

try {
    $pdo = getDbConnection();

    // Verifikasi kepemilikan
    $stmt = $pdo->prepare('SELECT id, description, receipt_path FROM transactions WHERE id = :id AND user_id = :user_id LIMIT 1');
    $stmt->execute([':id' => $id, ':user_id' => $userId]);
    $tx = $stmt->fetch();

    if (!$tx) {
        sendJsonResponse(['success' => false, 'message' => 'Transaksi tidak ditemukan atau Anda tidak memiliki akses.'], 404);
    }

    // Hapus file struk jika ada
    if ($tx['receipt_path'] && file_exists(__DIR__ . '/../../' . $tx['receipt_path'])) {
        @unlink(__DIR__ . '/../../' . $tx['receipt_path']);
    }

    $del = $pdo->prepare('DELETE FROM transactions WHERE id = :id AND user_id = :user_id');
    $del->execute([':id' => $id, ':user_id' => $userId]);

    sendJsonResponse([
        'success' => true,
        'message' => 'Transaksi "' . $tx['description'] . '" berhasil dihapus.'
    ]);
} catch (\Throwable $e) {
    error_log('API Delete Transaction Error: ' . $e->getMessage());
    sendJsonResponse(['success' => false, 'message' => 'Gagal menghapus transaksi: ' . $e->getMessage()], 500);
}

