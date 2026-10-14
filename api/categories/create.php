<?php
require_once __DIR__ . '/../../auth/middleware.php';
requireApiAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

$name = trim($_POST['name'] ?? '');
$type = trim($_POST['type'] ?? '');
$icon = trim($_POST['icon'] ?? 'category');
$validIcons = ["category","home","shopping_cart","fastfood","restaurant","local_cafe","directions_car","local_gas_station","flight","build","favorite","medical_services","school","pets","fitness_center","movie","sports_esports","receipt_long","payments","attach_money","trending_up","trending_down","savings","redeem","volunteer_activism","account_balance","account_balance_wallet","credit_card","shopping_bag","store","work","health_and_safety","celebration","checkroom","face","child_care","stroller","train","two_wheeler","local_shipping"];
if (!in_array($icon, $validIcons)) {
    $icon = "category";
}
$color = trim($_POST['color'] ?? '#0284c7');

if (empty($name) || !in_array($type, ['expense', 'income'])) {
    sendJsonResponse(['success' => false, 'message' => 'Data tidak valid.'], 400);
}

$id = uniqid('cat_');

try {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("INSERT INTO categories (id, name, type, icon, color) VALUES (:id, :name, :type, :icon, :color)");
    $stmt->execute([
        ':id' => $id,
        ':name' => $name,
        ':type' => $type,
        ':icon' => $icon,
        ':color' => $color
    ]);
    sendJsonResponse([
        'success' => true,
        'message' => 'Kategori berhasil ditambahkan.',
        'data' => [
            'id' => $id,
            'nama' => $name,
            'tipe' => $type,
            'icon' => $icon,
            'warna' => $color
        ]
    ]);
} catch (\Throwable $e) {
    error_log('Error create category: ' . $e->getMessage());
    sendJsonResponse(['success' => false, 'message' => 'Gagal menambah kategori.'], 500);
}
