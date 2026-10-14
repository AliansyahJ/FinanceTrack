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
$name = trim($input['name'] ?? $input['nama'] ?? '');
$type = strtolower(trim($input['type'] ?? $input['tipe'] ?? 'other'));
$icon = trim($input['icon'] ?? 'account_balance_wallet');
$color = trim($input['color'] ?? $input['warna'] ?? '#0284c7');

if (empty($id) || empty($name)) {
    sendJsonResponse(['success' => false, 'message' => 'ID dan Nama metode pembayaran wajib diisi.'], 400);
}
if (!preg_match("/^[\p{L}0-9\s\.\-]+$/u", $name)) {
    sendJsonResponse(['success' => false, 'message' => 'Nama metode pembayaran hanya boleh berisi huruf, angka, dan spasi.'], 400);
}

$validTypes = ['cash', 'ewallet', 'bank', 'other'];
if (!in_array($type, $validTypes)) {
    if ($type === 'tunai') $type = 'cash';
    else if ($type === 'e-wallet' || $type === 'dompet') $type = 'ewallet';
    else if ($type === 'rekening') $type = 'bank';
    else $type = 'other';
}

$validIcons = [
    "account_balance_wallet", "payments", "savings", "credit_card", "qr_code_2", 
    "account_balance", "wallet", "attach_money", "currency_exchange", "paid", 
    "receipt_long", "storefront", "shopping_bag", "contactless", "category",
    "local_atm", "monetization_on", "credit_score", "point_of_sale", "price_check"
];
if (!in_array($icon, $validIcons)) {
    $icon = "account_balance_wallet";
}

try {
    $pdo = getDbConnection();
    
    // Check if exists
    $chk = $pdo->prepare("SELECT id FROM payment_methods WHERE id = :id");
    $chk->execute([':id' => $id]);
    if (!$chk->fetch()) {
        sendJsonResponse(['success' => false, 'message' => 'Metode pembayaran tidak ditemukan.'], 404);
    }
    
    $stmt = $pdo->prepare("UPDATE payment_methods SET name = :name, type = :type, icon = :icon, color = :color WHERE id = :id");
    $stmt->execute([
        ':name' => $name,
        ':type' => $type,
        ':icon' => $icon,
        ':color' => $color,
        ':id' => $id
    ]);
    
    sendJsonResponse([
        'success' => true,
        'message' => 'Metode pembayaran berhasil diperbarui.',
        'data' => [
            'id' => $id,
            'nama' => $name,
            'name' => $name,
            'tipe' => $type,
            'type' => $type,
            'icon' => $icon,
            'warna' => $color,
            'color' => $color
        ]
    ]);
} catch (\Throwable $e) {
    error_log('Error update payment method: ' . $e->getMessage());
    sendJsonResponse(['success' => false, 'message' => 'Gagal memperbarui metode pembayaran.'], 500);
}
