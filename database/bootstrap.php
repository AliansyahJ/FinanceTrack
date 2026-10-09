<?php
/**
 * FinanceTrack — Database Hydration Bootstrap
 * Mengambil data riil dari MySQL untuk diinjeksikan ke window.__FT_BOOTSTRAP__
 */

require_once __DIR__ . '/../config/database.php';

function getDatabaseBootstrapData(int $userId): array {
    try {
        $pdo = getDbConnection();

        // 1. Kategori
        $catStmt = $pdo->query("SELECT id, name AS nama, type AS tipe, icon, color AS warna FROM categories ORDER BY name ASC");
        $categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. Transaksi User
        $txStmt = $pdo->prepare("SELECT t.id, t.category_id AS kategoriId, t.type AS tipe, 
                                       t.amount AS jumlah, t.transaction_date AS tanggal, 
                                       SUBSTRING(t.transaction_time, 1, 5) AS jam, 
                                       t.description AS deskripsi, t.payment_method AS metode, 
                                       t.notes AS catatan, t.receipt_path AS receipt_path
                                FROM transactions t
                                WHERE t.user_id = :uid
                                ORDER BY t.transaction_date DESC, t.transaction_time DESC, t.id DESC");
        $txStmt->execute([':uid' => $userId]);
        $rawTransactions = $txStmt->fetchAll(PDO::FETCH_ASSOC);
        $transactions = array_map(function ($t) {
            $t['id'] = (int)$t['id'];
            $t['jumlah'] = (int)$t['jumlah'];
            return $t;
        }, $rawTransactions);

        // 3. Anggaran User (bulan & tahun berjalan)
        $m = (int)date('n');
        $y = (int)date('Y');
        $bgStmt = $pdo->prepare("SELECT b.id, b.category_id AS kategoriId, b.amount AS batas,
                                       c.name AS nama, c.icon, c.color AS warna,
                                       COALESCE(SUM(t.amount), 0) AS terpakai
                                FROM budgets b
                                JOIN categories c ON b.category_id = c.id
                                LEFT JOIN transactions t ON t.category_id = b.category_id 
                                     AND t.user_id = b.user_id 
                                     AND t.type = 'expense'
                                     AND MONTH(t.transaction_date) = b.period_month
                                     AND YEAR(t.transaction_date) = b.period_year
                                WHERE b.user_id = :uid AND b.period_month = :m AND b.period_year = :y
                                GROUP BY b.id, b.category_id, b.amount, c.name, c.icon, c.color");
        $bgStmt->execute([':uid' => $userId, ':m' => $m, ':y' => $y]);
        $rawBudgets = $bgStmt->fetchAll(PDO::FETCH_ASSOC);
        $budgets = array_map(function ($b) {
            $b['id'] = (int)$b['id'];
            $b['batas'] = (int)$b['batas'];
            $b['terpakai'] = (int)$b['terpakai'];
            return $b;
        }, $rawBudgets);

        // 4. Ringkasan
        $incomeTotal = 0;
        $expenseTotal = 0;
        $incomeMonth = 0;
        $expenseMonth = 0;
        $curMonth = date('Y-m');

        foreach ($transactions as $t) {
            $amt = (int)$t['jumlah'];
            if ($t['tipe'] === 'income') {
                $incomeTotal += $amt;
                if (substr($t['tanggal'], 0, 7) === $curMonth) {
                    $incomeMonth += $amt;
                }
            } else {
                $expenseTotal += $amt;
                if (substr($t['tanggal'], 0, 7) === $curMonth) {
                    $expenseMonth += $amt;
                }
            }
        }

        $balance = $incomeTotal - $expenseTotal;

        $totalBudget = 0;
        foreach ($budgets as $b) {
            $totalBudget += (int)$b['batas'];
        }
        if ($totalBudget === 0) {
            $totalBudget = 3200000;
        }
        $remainingBudget = max(0, $totalBudget - $expenseMonth);

        $bulanIndo = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        $periodeLabel = ($bulanIndo[(int)date('n')] ?? date('F')) . ' ' . date('Y');

        $summary = [
            'saldo' => $balance,
            'pemasukan' => $incomeTotal,
            'pengeluaran' => $expenseTotal,
            'pemasukanBulan' => $incomeMonth,
            'pengeluaranBulan' => $expenseMonth,
            'totalAnggaran' => $totalBudget,
            'sisaAnggaran' => $remainingBudget,
            'periode' => $periodeLabel,
            'periodeRaw' => $curMonth,
            'tanggal' => (int)date('j'),
            'hariDalamBulan' => (int)date('t')
        ];

        // 5. Komposisi Pengeluaran Bulan Ini
        $compStmt = $pdo->prepare("SELECT c.id, c.name AS nama, c.color AS warna, c.icon,
                                          COALESCE(SUM(t.amount), 0) AS nilai
                                   FROM categories c
                                   JOIN transactions t ON t.category_id = c.id
                                   WHERE t.user_id = :uid 
                                     AND t.type = 'expense'
                                     AND MONTH(t.transaction_date) = :m 
                                     AND YEAR(t.transaction_date) = :y
                                   GROUP BY c.id, c.name, c.color, c.icon
                                   ORDER BY nilai DESC");
        $compStmt->execute([':uid' => $userId, ':m' => $m, ':y' => $y]);
        $expenseComposition = array_map(function ($r) {
            return [
                'id' => $r['id'],
                'nama' => $r['nama'],
                'nilai' => (int)$r['nilai'],
                'warna' => $r['warna'] ?: '#0284c7',
                'icon' => $r['icon']
            ];
        }, $compStmt->fetchAll(PDO::FETCH_ASSOC));

        // 6. Tren 6 Bulan
        $trendLabels = [];
        $trendIncome = [];
        $trendExpense = [];
        $blnSingkat = [1=>'Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        for ($i = 5; $i >= 0; $i--) {
            $timestamp = strtotime("-{$i} month");
            $tm = (int)date('n', $timestamp);
            $ty = (int)date('Y', $timestamp);
            $trendLabels[] = $blnSingkat[$tm] ?? date('M', $timestamp);

            $tStmt = $pdo->prepare("SELECT 
                COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) AS inc,
                COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) AS exp
                FROM transactions 
                WHERE user_id = :uid 
                  AND MONTH(transaction_date) = :m 
                  AND YEAR(transaction_date) = :y");
            $tStmt->execute([':uid' => $userId, ':m' => $tm, ':y' => $ty]);
            $tRow = $tStmt->fetch(PDO::FETCH_ASSOC);
            $trendIncome[] = (int)$tRow['inc'];
            $trendExpense[] = (int)$tRow['exp'];
        }

        $trend = [
            'labels' => $trendLabels,
            'pemasukan' => $trendIncome,
            'pengeluaran' => $trendExpense
        ];

        return [
            'success' => true,
            'categories' => $categories,
            'transactions' => $transactions,
            'budgets' => $budgets,
            'summary' => $summary,
            'expenseComposition' => $expenseComposition,
            'trend' => $trend
        ];
    } catch (\Throwable $e) {
        error_log("Bootstrap Error: " . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

