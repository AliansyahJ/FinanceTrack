<?php
/**
 * FinanceTrack API — List Transactions
 * GET /api/transactions/list.php
 */

require_once __DIR__ . '/../../auth/middleware.php';

$userId = requireApiAuth();

try {
    $pdo = getDbConnection();

    // Query parameters
    $search   = trim($_GET['search'] ?? '');
    $type     = trim($_GET['type'] ?? 'all');
    $category = trim($_GET['category'] ?? 'all');
    $sort     = trim($_GET['sort'] ?? 'newest');
    $period   = trim($_GET['period'] ?? 'all');
    $page     = max(1, (int)($_GET['page'] ?? 1));
    $perPage  = max(1, min(100, (int)($_GET['per_page'] ?? 50)));

    $whereClauses = ['t.user_id = :user_id'];
    $params = [':user_id' => $userId];

    if (!empty($search)) {
        $whereClauses[] = '(t.description LIKE :search OR c.name LIKE :search_cat)';
        $params[':search'] = '%' . $search . '%';
        $params[':search_cat'] = '%' . $search . '%';
    }

    if ($type !== 'all' && in_array($type, ['income', 'expense'], true)) {
        $whereClauses[] = 't.type = :type';
        $params[':type'] = $type;
    }

    if ($category !== 'all' && !empty($category)) {
        $whereClauses[] = 't.category_id = :category_id';
        $params[':category_id'] = $category;
    }

    if ($period === 'month') {
        $whereClauses[] = 'MONTH(t.transaction_date) = MONTH(CURRENT_DATE()) AND YEAR(t.transaction_date) = YEAR(CURRENT_DATE())';
    } elseif ($period === 'today') {
        $whereClauses[] = 't.transaction_date = CURRENT_DATE()';
    } elseif ($period === 'week') {
        $whereClauses[] = 't.transaction_date >= DATE_SUB(CURRENT_DATE(), INTERVAL 7 DAY)';
    }

    $whereSql = implode(' AND ', $whereClauses);

    // Sorting
    $orderSql = 'ORDER BY t.transaction_date DESC, t.transaction_time DESC, t.id DESC';
    if ($sort === 'oldest') {
        $orderSql = 'ORDER BY t.transaction_date ASC, t.transaction_time ASC, t.id ASC';
    } elseif ($sort === 'highest') {
        $orderSql = 'ORDER BY t.amount DESC';
    } elseif ($sort === 'lowest') {
        $orderSql = 'ORDER BY t.amount ASC';
    }

    // Count total
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM transactions t JOIN categories c ON t.category_id = c.id WHERE {$whereSql}");
    $countStmt->execute($params);
    $total = (int)$countStmt->fetchColumn();

    $totalPages = max(1, (int)ceil($total / $perPage));
    $offset = ($page - 1) * $perPage;

    // Fetch rows
    $sql = "SELECT t.id, t.user_id, t.category_id, t.type, t.amount, t.transaction_date, 
                   t.transaction_time, t.description, t.payment_method, t.receipt_path, 
                   t.notes, t.created_at,
                   c.name AS category_name, c.icon AS category_icon, c.color AS category_color
            FROM transactions t
            JOIN categories c ON t.category_id = c.id
            WHERE {$whereSql}
            {$orderSql}
            LIMIT :offset, :per_page";

    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':per_page', $perPage, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll();

    // Map rows to clean array
    $transactions = array_map(function ($r) {
        return [
            'id' => (int)$r['id'],
            'tanggal' => $r['transaction_date'],
            'jam' => substr($r['transaction_time'], 0, 5),
            'deskripsi' => $r['description'],
            'kategoriId' => $r['category_id'],
            'kategoriNama' => $r['category_name'],
            'kategoriIcon' => $r['category_icon'],
            'kategoriWarna' => $r['category_color'],
            'metode' => $r['payment_method'],
            'tipe' => $r['type'],
            'jumlah' => (float)$r['amount'],
            'catatan' => $r['notes'] ?: '',
            'struk' => $r['receipt_path'] ?: null
        ];
    }, $rows);

    sendJsonResponse([
        'success' => true,
        'data' => $transactions,
        'pagination' => [
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => $totalPages
        ]
    ]);
} catch (\Throwable $e) {
    error_log('API Transactions List Error: ' . $e->getMessage());
    sendJsonResponse(['success' => false, 'message' => 'Gagal mengambil data transaksi: ' . $e->getMessage()], 500);
}

