<?php
/**
 * FinanceTrack API — Update Transaction
 * POST /api/transactions/update.php
 */

require_once __DIR__ . '/../../auth/middleware.php';

$userId = requireApiAuth();

$data = $_POST;
if (empty($data)) {
    $raw = file_get_contents('php://input');
    $json = json_decode($raw, true);
    if (is_array($json)) {
        $data = $json;
    }
}

$id              = (int)($data['id'] ?? $_GET['id'] ?? 0);
$type            = trim($data['tipe'] ?? $data['type'] ?? 'expense');
$amount          = (float)str_replace(['.', ',', 'Rp', ' '], '', $data['jumlah'] ?? $data['amount'] ?? 0);
$categoryId      = trim($data['kategoriId'] ?? $data['category_id'] ?? '');
$description     = trim($data['deskripsi'] ?? $data['description'] ?? '');
$paymentMethod   = trim($data['metode'] ?? $data['payment_method'] ?? 'cash');
$transactionDate = trim($data['tanggal'] ?? $data['transaction_date'] ?? date('Y-m-d'));
$transactionTime = trim($data['jam'] ?? $data['transaction_time'] ?? date('H:i'));
$notes           = trim($data['catatan'] ?? $data['notes'] ?? '');

if (strlen($transactionTime) === 5) {
    $transactionTime .= ':00';
}

if ($id <= 0) {
    sendJsonResponse(['success' => false, 'message' => 'ID transaksi tidak valid.'], 400);
}

if ($amount <= 0 || empty($description) || empty($categoryId) || empty($paymentMethod)) {
    sendJsonResponse(['success' => false, 'message' => 'Harap lengkapi seluruh kolom wajib.'], 400);
}
if (!preg_match("/^[\p{L}\s]+$/u", $description)) {
    sendJsonResponse(['success' => false, 'message' => 'Deskripsi transaksi hanya boleh berisi huruf dan spasi.'], 400);
}
if (mb_strlen($description) > 100) {
    sendJsonResponse(['success' => false, 'message' => 'Deskripsi maksimal 100 karakter.'], 400);
}
if (mb_strlen($notes) > 1000) {
    sendJsonResponse(['success' => false, 'message' => 'Catatan terlalu panjang (maksimal 1000 karakter).'], 400);
}

try {
    $pdo = getDbConnection();

    // Verifikasi kepemilikan transaksi
    $checkStmt = $pdo->prepare('SELECT id, receipt_path FROM transactions WHERE id = :id AND user_id = :user_id LIMIT 1');
    $checkStmt->execute([':id' => $id, ':user_id' => $userId]);
    $existing = $checkStmt->fetch();

    if (!$existing) {
        sendJsonResponse(['success' => false, 'message' => 'Transaksi tidak ditemukan atau Anda tidak memiliki akses.'], 404);
    }

    $receiptPath = $existing['receipt_path'];

    // Jika ada unggahan berkas struk baru
    if (!empty($_FILES['struk']) && $_FILES['struk']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['struk'];
        $maxBytes = 5 * 1024 * 1024;
        if ($file['size'] > $maxBytes) {
            sendJsonResponse(['success' => false, 'message' => 'Ukuran berkas struk melebihi 5 MB.'], 400);
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($ext, $allowedExts, true)) {
            sendJsonResponse(['success' => false, 'message' => 'Format berkas hanya boleh JPG, JPEG, PNG, atau WEBP.'], 400);
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($mime, $allowedMimes, true)) {
            sendJsonResponse(['success' => false, 'message' => 'Tipe berkas tidak valid. Hanya berkas gambar yang diizinkan.'], 400);
        }

        $uploadDir = __DIR__ . '/../../uploads/receipts';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $uniqueName = 'receipt_' . date('Ymd_His') . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
        $targetPath = $uploadDir . '/' . $uniqueName;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            // Hapus berkas lama jika ada
            if ($existing['receipt_path'] && file_exists(__DIR__ . '/../../' . $existing['receipt_path'])) {
                @unlink(__DIR__ . '/../../' . $existing['receipt_path']);
            }
            $receiptPath = 'uploads/receipts/' . $uniqueName;
        }
    }

    $updateSql = "UPDATE transactions SET
                    category_id = :category_id,
                    type = :type,
                    amount = :amount,
                    transaction_date = :date,
                    transaction_time = :time,
                    description = :desc,
                    payment_method = :method,
                    receipt_path = :receipt,
                    notes = :notes
                  WHERE id = :id AND user_id = :user_id";

    $updateStmt = $pdo->prepare($updateSql);
    $updateStmt->execute([
        ':category_id' => $categoryId,
        ':type'        => $type,
        ':amount'      => $amount,
        ':date'        => $transactionDate,
        ':time'        => $transactionTime,
        ':desc'        => $description,
        ':method'      => $paymentMethod,
        ':receipt'     => $receiptPath,
        ':notes'       => $notes,
        ':id'          => $id,
        ':user_id'     => $userId
    ]);

    // Periksa apakah transaksi pengeluaran ini melampaui batas anggaran
    $budgetAlert = null;
    if ($type === 'expense') {
        $txMonth = (int)date('n', strtotime($transactionDate));
        $txYear  = (int)date('Y', strtotime($transactionDate));

        $bStmt = $pdo->prepare('SELECT id, amount FROM budgets WHERE user_id = :uid AND category_id = :cid AND period_month = :m AND period_year = :y LIMIT 1');
        $bStmt->execute([':uid' => $userId, ':cid' => $categoryId, ':m' => $txMonth, ':y' => $txYear]);
        $bRow = $bStmt->fetch();

        if ($bRow && (float)$bRow['amount'] > 0) {
            $sumStmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) AS total FROM transactions 
                                      WHERE user_id = :uid AND category_id = :cid AND type = 'expense'
                                      AND MONTH(transaction_date) = :m AND YEAR(transaction_date) = :y");
            $sumStmt->execute([':uid' => $userId, ':cid' => $categoryId, ':m' => $txMonth, ':y' => $txYear]);
            $totalSpent = (float)$sumStmt->fetchColumn();

            if ($totalSpent >= (float)$bRow['amount']) {
                $checkCat = $pdo->prepare('SELECT name, icon FROM categories WHERE id = :id LIMIT 1');
                $checkCat->execute([':id' => $categoryId]);
                $cData = $checkCat->fetch();
                $budgetAlert = [
                    'kategoriId' => $categoryId,
                    'nama'       => $cData['name'] ?? $categoryId,
                    'icon'       => $cData['icon'] ?? 'category',
                    'terpakai'   => $totalSpent,
                    'batas'      => (float)$bRow['amount'],
                    'persen'     => round(($totalSpent / (float)$bRow['amount']) * 100),
                    'isOver'     => true
                ];
            }
        }
    }

    sendJsonResponse([
        'success' => true,
        'message' => 'Transaksi berhasil diperbarui.',
        'data' => [
            'id' => $id,
            'tanggal' => $transactionDate,
            'jam' => substr($transactionTime, 0, 5),
            'deskripsi' => $description,
            'kategoriId' => $categoryId,
            'metode' => $paymentMethod,
            'tipe' => $type,
            'jumlah' => $amount,
            'catatan' => $notes,
            'struk' => $receiptPath,
            'budgetAlert' => $budgetAlert
        ]
    ]);

} catch (\Throwable $e) {
    error_log('API Update Transaction Error: ' . $e->getMessage());
    sendJsonResponse(['success' => false, 'message' => 'Gagal memperbarui transaksi: ' . $e->getMessage()], 500);
}

