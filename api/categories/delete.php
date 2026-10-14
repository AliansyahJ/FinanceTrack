<?php
require_once __DIR__ . '/../../auth/middleware.php';
requireApiAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

$id = trim($_POST['id'] ?? '');
if (empty($id)) {
    sendJsonResponse(['success' => false, 'message' => 'ID tidak valid.'], 400);
}

try {
    $pdo = getDbConnection();
    
    // Check usage in transactions
    $chkTx = $pdo->prepare("SELECT id FROM transactions WHERE category_id = :id LIMIT 1");
    $chkTx->execute([':id' => $id]);
    if ($chkTx->fetch()) {
        sendJsonResponse(['success' => false, 'message' => 'Kategori tidak dapat dihapus karena sedang digunakan dalam transaksi.'], 400);
    }
    
    // Check usage in budgets
    $chkBg = $pdo->prepare("SELECT id FROM budgets WHERE category_id = :id LIMIT 1");
    $chkBg->execute([':id' => $id]);
    if ($chkBg->fetch()) {
        sendJsonResponse(['success' => false, 'message' => 'Kategori tidak dapat dihapus karena sedang digunakan dalam anggaran.'], 400);
    }
    
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = :id");
    $stmt->execute([':id' => $id]);
    
    if ($stmt->rowCount() > 0) {
        sendJsonResponse(['success' => true, 'message' => 'Kategori berhasil dihapus.']);
    } else {
        sendJsonResponse(['success' => false, 'message' => 'Kategori tidak ditemukan.'], 404);
    }
    
} catch (\Throwable $e) {
    error_log('Error delete category: ' . $e->getMessage());
    sendJsonResponse(['success' => false, 'message' => 'Gagal menghapus kategori.'], 500);
}
