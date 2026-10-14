<?php
/**
 * FinanceTrack API — Create Transaction
 * POST /api/transactions/create.php
 */

require_once __DIR__ . '/../../auth/middleware.php';

$userId = requireApiAuth();

// Baca data baik dari multipart form-data (ada berkas) maupun application/json
$data = $_POST;
if (empty($data)) {
    $raw = file_get_contents('php://input');
    $json = json_decode($raw, true);
    if (is_array($json)) {
        $data = $json;
    }
}

$type            = trim($data['tipe'] ?? $data['type'] ?? 'expense');
$amount          = (float)str_replace(['.', ',', 'Rp', ' '], '', $data['jumlah'] ?? $data['amount'] ?? 0);
$categoryId      = trim($data['kategoriId'] ?? $data['category_id'] ?? '');
$description     = trim($data['deskripsi'] ?? $data['description'] ?? '');
$paymentMethod   = trim($data['metode'] ?? $data['payment_method'] ?? 'cash');
$transactionDate = trim($data['tanggal'] ?? $data['transaction_date'] ?? date('Y-m-d'));
$transactionTime = trim($data['jam'] ?? $data['transaction_time'] ?? date('H:i'));
$notes           = trim($data['catatan'] ?? $data['notes'] ?? '');

// Format jam jika hanya HH:MM tambahkan :00
if (strlen($transactionTime) === 5) {
    $transactionTime .= ':00';
}

// 1. Validasi Input
$errors = [];
if (!in_array($type, ['income', 'expense'], true)) {
    $errors['type'] = 'Jenis transaksi harus berupa income atau expense.';
}
if ($amount <= 0) {
    $errors['amount'] = 'Nominal transaksi harus lebih dari 0.';
}
if (empty($categoryId)) {
    $errors['category_id'] = 'Kategori transaksi wajib dipilih.';
}
if (empty($description)) {
    $errors['description'] = 'Deskripsi transaksi wajib diisi.';
} elseif (mb_strlen($description) > 100) {
    $errors['description'] = 'Deskripsi maksimal 100 karakter. Gunakan catatan untuk detail.';
} elseif (!preg_match("/^[\p{L}\s]+$/u", $description)) {
    $errors['description'] = 'Deskripsi transaksi hanya boleh berisi huruf dan spasi.';
}
if (mb_strlen($notes) > 1000) {
    $errors['notes'] = 'Catatan terlalu panjang (maksimal 1000 karakter).';
}
if (empty($paymentMethod)) {
    $errors['payment_method'] = 'Metode pembayaran wajib dipilih.';
}
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $transactionDate)) {
    $errors['transaction_date'] = 'Format tanggal transaksi tidak valid (YYYY-MM-DD).';
}

if (!empty($errors)) {
    sendJsonResponse([
        'success' => false,
        'message' => 'Validasi gagal. Harap periksa kolom yang ditandai.',
        'errors' => $errors
    ], 400);
}

// 2. Validasi & Simpan Berkas Struk (PART 5)
$receiptPath = null;
if (!empty($_FILES['struk']) && $_FILES['struk']['error'] !== UPLOAD_ERR_NO_FILE) {
    $file = $_FILES['struk'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        sendJsonResponse([
            'success' => false,
            'message' => 'Gagal mengunggah berkas struk (Kode error: ' . $file['error'] . ').'
        ], 400);
    }

    // Maksimal 5 MB
    $maxBytes = 5 * 1024 * 1024;
    if ($file['size'] > $maxBytes) {
        sendJsonResponse([
            'success' => false,
            'message' => 'Ukuran berkas struk melebihi batas maksimal 5 MB.'
        ], 400);
    }

    // Validasi ekstensi
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
    if (!in_array($ext, $allowedExts, true)) {
        sendJsonResponse([
            'success' => false,
            'message' => 'Format berkas tidak diizinkan! Hanya berkas gambar JPG, JPEG, PNG, atau WEBP yang diperbolehkan (PDF/SVG/dokumen tidak diizinkan).'
        ], 400);
    }

    // Validasi MIME type secara aman dari server
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($mime, $allowedMimes, true)) {
        sendJsonResponse([
            'success' => false,
            'message' => 'Tipe berkas tidak valid (' . htmlspecialchars($mime) . '). Berkas harus berupa gambar asli.'
        ], 400);
    }

    // Generate nama file acak yang aman
    $uploadDir = __DIR__ . '/../../uploads/receipts';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $uniqueName = 'receipt_' . date('Ymd_His') . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
    $targetPath = $uploadDir . '/' . $uniqueName;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        sendJsonResponse([
            'success' => false,
            'message' => 'Gagal menyimpan berkas struk ke server.'
        ], 500);
    }

    $receiptPath = 'uploads/receipts/' . $uniqueName;
}

// 3. Simpan Transaksi ke Database
try {
    $pdo = getDbConnection();

    // Verifikasi kategori ada
    $checkCat = $pdo->prepare('SELECT id, name, icon, color FROM categories WHERE id = :id LIMIT 1');
    $checkCat->execute([':id' => $categoryId]);
    $catRow = $checkCat->fetch();
    if (!$catRow) {
        // Jika kategori custom belum ada, buat otomatis
        $insertCat = $pdo->prepare('INSERT INTO categories (id, name, type, icon, color) VALUES (:id, :name, :type, "category", "#0284c7")');
        $insertCat->execute([
            ':id' => $categoryId,
            ':name' => ucwords(str_replace('-', ' ', $categoryId)),
            ':type' => $type
        ]);
        $catRow = ['id' => $categoryId, 'name' => ucwords($categoryId), 'icon' => 'category', 'color' => '#0284c7'];
    }

    $sql = "INSERT INTO transactions 
            (user_id, category_id, type, amount, transaction_date, transaction_time, description, payment_method, receipt_path, notes)
            VALUES 
            (:user_id, :category_id, :type, :amount, :date, :time, :desc, :method, :receipt, :notes)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':user_id'     => $userId,
        ':category_id' => $categoryId,
        ':type'        => $type,
        ':amount'      => $amount,
        ':date'        => $transactionDate,
        ':time'        => $transactionTime,
        ':desc'        => $description,
        ':method'      => $paymentMethod,
        ':receipt'     => $receiptPath,
        ':notes'       => $notes
    ]);

    $newId = (int)$pdo->lastInsertId();

    // 4. Periksa apakah transaksi pengeluaran ini melampaui batas anggaran
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
                $budgetAlert = [
                    'kategoriId' => $categoryId,
                    'nama'       => $catRow['name'],
                    'icon'       => $catRow['icon'],
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
        'message' => 'Transaksi berhasil dicatat.',
        'data' => [
            'id' => $newId,
            'tanggal' => $transactionDate,
            'jam' => substr($transactionTime, 0, 5),
            'deskripsi' => $description,
            'kategoriId' => $categoryId,
            'kategoriNama' => $catRow['name'],
            'kategoriIcon' => $catRow['icon'],
            'kategoriWarna' => $catRow['color'],
            'metode' => $paymentMethod,
            'tipe' => $type,
            'jumlah' => $amount,
            'catatan' => $notes,
            'struk' => $receiptPath,
            'budgetAlert' => $budgetAlert
        ]
    ], 201);

} catch (\Throwable $e) {
    error_log('API Create Transaction Error: ' . $e->getMessage());
    sendJsonResponse([
        'success' => false,
        'message' => 'Gagal menyimpan transaksi ke database: ' . $e->getMessage()
    ], 500);
}

