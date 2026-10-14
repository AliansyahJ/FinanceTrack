<?php
require_once __DIR__ . '/../../auth/middleware.php';
requireApiAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

$input = $_POST;
if (empty($input)) {
    $raw = file_get_contents('php://input');
    $json = json_decode($raw, true);
    if (is_array($json)) {
        $input = $json;
    }
}

$id = trim($input['id'] ?? '');
if (empty($id)) {
    sendJsonResponse(['success' => false, 'message' => 'ID metode pembayaran tidak valid.'], 400);
}

try {
    $pdo = getDbConnection();
    
    // Check usage in transactions
    $chkTx = $pdo->prepare("SELECT id FROM transactions WHERE payment_method = :id LIMIT 1");
    $chkTx->execute([':id' => $id]);
    if ($chkTx->fetch()) {
        sendJsonResponse([
            'success' => false, 
            'message' => 'Metode pembayaran ini tidak dapat dihapus karena sudah digunakan dalam riwayat transaksi Anda.'
        ], 400);
    }
    
    $stmt = $pdo->prepare("DELETE FROM payment_methods WHERE id = :id");
    $stmt->execute([':id' => $id]);
    
    if ($stmt->rowCount() > 0) {
        sendJsonResponse(['success' => true, 'message' => 'Metode pembayaran berhasil dihapus.']);
    } else {
        sendJsonResponse(['success' => false, 'message' => 'Metode pembayaran tidak ditemukan.'], 404);
    }
} catch (\Throwable $e) {
    error_log('Error delete payment method: ' . $e->getMessage());
    sendJsonResponse(['success' => false, 'message' => 'Gagal menghapus metode pembayaran.'], 500);
}
