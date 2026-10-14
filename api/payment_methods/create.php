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

$name = trim($input['name'] ?? $input['nama'] ?? '');
$type = strtolower(trim($input['type'] ?? $input['tipe'] ?? 'other'));
$icon = trim($input['icon'] ?? 'account_balance_wallet');
$color = trim($input['color'] ?? $input['warna'] ?? '#0284c7');
$customId = trim($input['id'] ?? '');

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

if (empty($name)) {
    sendJsonResponse(['success' => false, 'message' => 'Nama metode pembayaran tidak boleh kosong.'], 400);
}

// Generate ID
if (!empty($customId)) {
    $id = strtolower(preg_replace('/[^a-zA-Z0-9_-]/', '', $customId));
} else {
    // Generate from name or uniqid
    $slug = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $name));
    $id = !empty($slug) ? substr($slug, 0, 20) . '_' . substr(uniqid(), -4) : uniqid('pm_');
}

try {
    $pdo = getDbConnection();
    
    // Check if ID already exists
    $chk = $pdo->prepare("SELECT id FROM payment_methods WHERE id = :id");
    $chk->execute([':id' => $id]);
    if ($chk->fetch()) {
        $id = $id . '_' . substr(uniqid(), -4);
    }
    
    $stmt = $pdo->prepare("INSERT INTO payment_methods (id, name, type, icon, color) VALUES (:id, :name, :type, :icon, :color)");
    $stmt->execute([
        ':id' => $id,
        ':name' => $name,
        ':type' => $type,
        ':icon' => $icon,
        ':color' => $color
    ]);
    
    sendJsonResponse([
        'success' => true,
        'message' => 'Metode pembayaran berhasil ditambahkan.',
        'data' => [
            'id' => $id,
            'nama' => $name,
            'tipe' => $type,
            'icon' => $icon,
            'warna' => $color
        ]
    ]);
} catch (\Throwable $e) {
    error_log('Error create payment method: ' . $e->getMessage());
    sendJsonResponse(['success' => false, 'message' => 'Gagal menambah metode pembayaran.'], 500);
}
