<?php
require_once __DIR__ . '/../auth/middleware.php';
require_once __DIR__ . '/../database/bootstrap.php';
requireAuth();
$currentUser = getCurrentUser();
$userName = htmlspecialchars($currentUser['name'] ?? 'Fikri Juana');
$userRole = htmlspecialchars($currentUser['role'] ?? 'Mahasiswa');
$userAvatar = htmlspecialchars($currentUser['avatar_initials'] ?? 'FP');
$userEmail = htmlspecialchars($currentUser['email'] ?? 'demo@financetrack.test');
$reqM = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n');
$reqY = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
$bootstrapData = getDatabaseBootstrapData((int)$currentUser['id'], $reqM, $reqY);
$summary = $bootstrapData['summary'] ?? [
    'saldo' => 0,
    'pemasukan' => 0,
    'pengeluaran' => 0,
    'sisaAnggaran' => 0,
    'totalAnggaran' => 3200000,
    'periode' => 'Oktober ' . date('Y'),
    'hariDalamBulan' => (int)date('t')
];
$expensePct = !empty($summary['totalAnggaran']) ? round(($summary['pengeluaran'] / $summary['totalAnggaran']) * 100) : 0;

$totLimit = 0;
$totSpent = 0;
if (!empty($bootstrapData['budgets'])) {
    foreach ($bootstrapData['budgets'] as $bg) {
        $totLimit += (int)$bg['batas'];
        $totSpent += (int)$bg['terpakai'];
    }
} else {
    $totLimit = $summary['totalAnggaran'] ?? 3200000;
    $totSpent = $summary['pengeluaranBulan'] ?? 0;
}
$sisaAwal = $totLimit - $totSpent;
$pctAwal = $totLimit > 0 ? round(($totSpent / $totLimit) * 100, 1) : 0;

$bulanIndo = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$selectedMonthName = $bulanIndo[$reqM] ?? date('F');
$nowD = (int)date('j');
$totalDaysInSelectedMonth = (int)date('t', strtotime("$reqY-" . str_pad($reqM, 2, '0', STR_PAD_LEFT) . "-01"));
$isCurrentReqMonth = ($reqM === (int)date('n') && $reqY === (int)date('Y'));
$curDayInMonth = $isCurrentReqMonth ? $nowD : ($reqY < (int)date('Y') || ($reqY === (int)date('Y') && $reqM < (int)date('n')) ? $totalDaysInSelectedMonth : 1);
$sisaHari = $isCurrentReqMonth ? max(1, $totalDaysInSelectedMonth - $nowD) : ($reqY < (int)date('Y') || ($reqY === (int)date('Y') && $reqM < (int)date('n')) ? 0 : $totalDaysInSelectedMonth);
$jatahHarian = $sisaHari > 0 ? max(0, (int)floor(max(0, $sisaAwal) / $sisaHari)) : 0;
$pctHari = $totalDaysInSelectedMonth > 0 ? round(($curDayInMonth / $totalDaysInSelectedMonth) * 100, 1) : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Dashboard FinanceTrack — ringkasan keuangan mahasiswa: saldo, pemasukan, pengeluaran, dan anggaran.">
    <title>Anggaran &mdash; FinanceTrack</title>
  <script>
    window.__FT_BOOTSTRAP__ = <?= json_encode($bootstrapData, JSON_HEX_TAG | JSON_HEX_AMP) ?>;
  </script>

  <link rel="icon" type="image/svg+xml" href="../assets/img/logo/logo.svg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/css/style.css?v=1791587905545">
  <link rel="stylesheet" href="../assets/css/responsive.css">
</head>
<body>

  <!-- Sidebar / Off-canvas navigation -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar__brand">
      <img src="../assets/img/logo/logo.svg" alt="FinanceTrack" width="120" height="30">
      <button type="button" class="sidebar__close" data-sidebar-close aria-label="Tutup menu">
        <span class="material-symbols-outlined">close</span>
      </button>
    </div>

    <nav class="sidebar__nav" aria-label="Navigasi utama">
      <p class="sidebar__label">Menu</p>
      <ul>
        <li>
          <a class="nav-item" href="dashboard.php">
            <span class="material-symbols-outlined" aria-hidden="true">grid_view</span>
            Dashboard
          </a>
        </li>
        <li>
          <button type="button" class="nav-item" data-submenu-trigger aria-expanded="false" aria-controls="submenu-transaksi"
                  style="width:100%">
            <span class="material-symbols-outlined" aria-hidden="true">receipt_long</span>Transaksi<span class="material-symbols-outlined icon-xs" aria-hidden="true" style="margin-left:auto">expand_more</span>
          </button>
          <ul class="nav-sublist" id="submenu-transaksi">
            <li><a class="nav-subitem" href="transaksi.php">Semua Transaksi</a></li>
            <li><a class="nav-subitem" href="form-transaksi.php">Tambah Transaksi</a></li>
          </ul>
        </li>
        <li>
          <a class="nav-item" href="anggaran.php">
            <span class="material-symbols-outlined" aria-hidden="true">pie_chart</span>
            Anggaran
          </a>
        </li>
        <li>
          <a class="nav-item" href="kategori.php">
            <span class="material-symbols-outlined" aria-hidden="true">category</span>
            Kategori
          </a>
        </li>
        <li>
          <a class="nav-item" href="metode-pembayaran.php">
            <span class="material-symbols-outlined" aria-hidden="true">payments</span>
            Metode Pembayaran
          </a>
        </li>
        <li>
          <a class="nav-item" href="laporan.php">
            <span class="material-symbols-outlined" aria-hidden="true">monitoring</span>
            Laporan
          </a>
        </li>
        <li>
          <a class="nav-item" href="pengaturan.php">
            <span class="material-symbols-outlined" aria-hidden="true">settings</span>
            Pengaturan
          </a>
        </li>
        
      </ul>






    </nav>

        <div style="padding:var(--space-md);background:var(--surface-container-low);margin:var(--space-md);border-radius:var(--radius-md);flex-shrink:0">
      <?php
        $totAnggaran = $summary['totalAnggaran'] ?? 3200000;
        $sisAnggaran = $summary['sisaAnggaran'] ?? 0;
        $pctAnggaran = $totAnggaran > 0 ? round(($sisAnggaran / $totAnggaran) * 100) : 0;
      ?>
      <div class="d-flex align-items-center justify-content-between" style="margin-bottom:var(--space-xs)">
        <span style="font-size:var(--text-xs);font-weight:600;color:var(--text-secondary);letter-spacing:0.05em">Sisa Anggaran Bulanan</span>
        <span style="font-size:var(--text-xs);font-weight:600;color:var(--secondary)"><?= $pctAnggaran ?>%</span>
      </div>
      <div class="progress" style="background:var(--surface-container-high);height:6px;border-radius:999px;overflow:hidden">
        <div class="progress__bar" style="background:var(--secondary);height:100%;border-radius:999px;width:<?= $pctAnggaran ?>%"></div>
      </div>
      <div class="d-flex align-items-center justify-content-between" style="margin-top:var(--space-sm)">
        <span style="font-size:var(--text-xs);font-weight:600;color:var(--text-secondary);letter-spacing:0.05em">Tersedia</span>
        <span style="font-size:var(--text-md);font-weight:600;color:var(--on-surface);letter-spacing:-0.01em">Rp <?= number_format($sisAnggaran, 0, ',', '.') ?></span>
      </div>
    </div>
  </aside>

  <div class="sidebar-backdrop" data-sidebar-close></div>

  <!-- Content area -->
  <div class="app">

    <!-- Desktop top bar (Stitch: pencarian, notifikasi, profil) -->
    <header class="app__topbar">
      <div class="d-flex align-items-center" style="gap:var(--space-md);flex:0 1 450px">
        <button type="button" class="sidebar-toggle" data-sidebar-toggle aria-label="Buka/Tutup menu" aria-controls="sidebar">
          <span class="material-symbols-outlined">menu</span>
        </button>
        <form class="input-group" action="transaksi.php" method="GET" style="flex:1; background-color: var(--surface-container-low); border-radius: 999px; padding: 4px 16px; border: 1px solid var(--surface-container);">
          <span class="material-symbols-outlined" aria-hidden="true" style="color: var(--secondary);">search</span>
          <input type="text" class="input" name="q" autocomplete="off"
                 aria-label="Cari mutasi, kategori, nominal"
                 placeholder="Cari mutasi, kategori, nominal..."
                 style="background: transparent; border: none; outline: none; box-shadow: none; width: 100%; color: var(--on-surface);">
        </form>
      </div>
      <div class="d-flex align-items-center" style="gap:var(--space-md);flex-shrink:0;">
        <button type="button" class="btn btn--ghost btn--icon" aria-label="Notifikasi" style="position:relative;height:40px;width:40px;display:flex;align-items:center;justify-content:center;">
          <span class="material-symbols-outlined" aria-hidden="true">notifications</span>
          <span class="topbar__dot" aria-hidden="true"></span>
        </button>
        <div class="topbar__user-menu" data-user-menu style="position:relative;">
          <button type="button" class="sidebar__user" data-user-menu-trigger aria-expanded="false" aria-haspopup="true" aria-label="Menu pengguna" style="display:flex;align-items:center;border:1px solid var(--surface-container);cursor:pointer;background-color:var(--surface-container-lowest);font-family:inherit;text-align:left;border-radius:999px;padding:4px 12px 4px 4px;height:48px;box-sizing:border-box;margin:0;">
            <span class="sidebar__avatar" aria-hidden="true" data-user-avatar style="width:40px;height:40px;font-size:14px;"><?= $userAvatar ?></span>
            <span data-user-name style="font-weight:600;font-size:var(--text-label-sm);margin-left:8px;margin-right:8px;"><?= $userName ?></span>
            <span class="material-symbols-outlined icon-sm" aria-hidden="true">expand_more</span>
          </button>
          <div class="topbar-dropdown" data-user-menu-dropdown role="menu" >
            <a href="pengaturan.php" class="topbar-dropdown__item" role="menuitem" style="display:flex;align-items:center;gap:12px;padding:12px 16px;color:var(--on-surface);text-decoration:none;">
              <span class="material-symbols-outlined icon-sm" aria-hidden="true">person</span>
              <span>Profil / Pengaturan</span>
            </a>
            <div style="height:1px;background:var(--surface-container);"></div>
            <a href="../auth/logout.php" class="topbar-dropdown__item topbar-dropdown__item--danger" role="menuitem" style="display:flex;align-items:center;gap:12px;padding:12px 16px;color:var(--danger);text-decoration:none;">
              <span class="material-symbols-outlined icon-sm" aria-hidden="true">logout</span>
              <span>Keluar</span>
            </a>
          </div>
        </div>
      </div>
    </header>

    

    <main class="app__main">

      <div class="page-header">
        <div>
          <p class="eyebrow">
            <span class="material-symbols-outlined icon-sm" aria-hidden="true">pie_chart</span>
            Perencanaan Finansial
          </p>
          <h1 class="page-title">Manajemen Anggaran</h1>
          <p class="page-subtitle">Pantau pemakaian uang saku bulanan, sisa anggaran aman, serta jatah belanja harian secara real-time.</p>
        </div>
      </div>

      <!-- Total banner -->
      <section class="card card--pad" aria-labelledby="total-anggaran-heading">
        <!-- Top bar dalam banner: Status Keuangan di kiri, Periode Bulan Terintegrasi di kanan -->
        <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap:var(--space-sm);margin-bottom:var(--space-md);padding-bottom:var(--space-md);border-bottom:1px solid var(--surface-container)">
          <div class="d-flex align-items-center gap-2 flex-wrap">
            <?php if ($pctAwal >= 100): ?>
            <span class="badge badge--expense" data-budget-status-badge style="font-weight:600;padding:6px 14px">
              <span class="badge--dot"></span>
              <span data-budget-status-text>Status: Overbudget</span>
            </span>
            <?php elseif ($pctAwal >= 80): ?>
            <span class="badge badge--warning" data-budget-status-badge style="font-weight:600;padding:6px 14px">
              <span class="badge--dot"></span>
              <span data-budget-status-text>Status: Mendekati Batas</span>
            </span>
            <?php else: ?>
            <span class="badge badge--income" data-budget-status-badge style="font-weight:600;padding:6px 14px">
              <span class="badge--dot"></span>
              <span data-budget-status-text>Status: Aman &amp; Terkendali</span>
            </span>
            <?php endif; ?>
            <span class="text-secondary" style="font-size:var(--text-xs);font-weight:500;">Periode: <strong style="color:var(--on-surface)"><?= $selectedMonthName . ' ' . $reqY ?></strong></span>
          </div>

          <!-- Periode Selector Terintegrasi dalam Banner -->
          <div class="d-flex align-items-center" style="gap:6px;background:var(--surface-container-low);padding:4px 14px;border-radius:999px;border:1px solid var(--surface-container);box-shadow:var(--shadow-xs);">
            <span class="material-symbols-outlined icon-sm" style="color:var(--primary)" aria-hidden="true">calendar_month</span>
            <label class="visually-hidden" for="budget-period">Pilih Periode Anggaran</label>
            <select class="select" id="budget-period" data-budget-period
                    style="width:auto;min-height:0;height:auto;padding:2px 18px 2px 4px;background:transparent;border:0;box-shadow:none;font-size:var(--text-sm);font-weight:600;color:var(--on-surface);cursor:pointer">
              <?php 
              $bulanIndo = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember']; 
              for ($i = 0; $i < 4; $i++) { 
                  $ts = strtotime("-$i month"); 
                  $val = date('Y-m', $ts); 
                  $m = (int)date('n', $ts);
                  $y = (int)date('Y', $ts);
                  $label = $bulanIndo[$m] . ' ' . $y; 
                  $sel = ($m === $reqM && $y === $reqY) ? ' selected' : ''; 
                  echo "<option value=\"" . $val . "\"" . $sel . ">" . $label . "</option>\n"; 
              } 
              ?>
            </select>
          </div>
        </div>

        <div class="row row--between row--wrap" style="margin-bottom:var(--space-lg);gap:var(--space-md);align-items:center">
          <div>
            <p class="summary-card__label" id="total-anggaran-heading" style="margin:0">Total Terpakai Bulan Ini</p>
            <div class="row" style="align-items:baseline;gap:var(--space-sm);flex-wrap:wrap;margin:var(--space-2xs) 0" data-budget-total-text>
              <span class="numeric" style="font-size:var(--text-4xl);font-weight:700;letter-spacing:-0.025em;color:var(--on-surface)">Rp <?= number_format($totSpent, 0, ',', '.') ?></span>
              <span class="text-secondary" style="font-size:var(--text-base);font-weight:500">/ Rp <?= number_format($totLimit, 0, ',', '.') ?></span>
            </div>
            <p class="text-secondary" style="font-size:var(--text-sm);display:flex;align-items:center;gap:var(--space-xs);margin:0" data-budget-status-desc>
              <?php if ($pctAwal >= 100): ?>
                <span class="material-symbols-outlined icon-sm" style="color:var(--danger)" aria-hidden="true">error</span>
                <span>Anggaran terlampaui sebesar Rp <?= number_format(abs($sisaAwal), 0, ',', '.') ?>. Rem pengeluaran segera!</span>
              <?php elseif ($pctAwal >= 80): ?>
                <span class="material-symbols-outlined icon-sm" style="color:var(--warning)" aria-hidden="true">warning</span>
                <span>Pengeluaran mencapai <?= $pctAwal ?>%. Sisa jatah belanja Rp <?= number_format($jatahHarian, 0, ',', '.') ?>/hari.</span>
              <?php else: ?>
                <span class="material-symbols-outlined icon-sm" style="color:var(--success)" aria-hidden="true">verified</span>
                <span>Sisa <?= $sisaHari ?> hari lagi di <?= $selectedMonthName ?>. Pengeluaran Anda terkendali aman!</span>
              <?php endif; ?>
            </p>
          </div>

          <!-- 3 Mini Stat Cards dengan Warna Segar & Interaktivitas -->
          <div class="grid grid--3" style="flex:1;min-width:300px;gap:var(--space-md)">
            <!-- Card 1: Total Terpakai -->
            <div class="interactive-stat-card" data-stat-card="spent" tabindex="0" role="button" aria-label="Lihat pos dengan pengeluaran" title="Klik untuk fokus ke pos pengeluaran aktif" style="padding:var(--space-md);border-radius:var(--radius-lg);background:linear-gradient(145deg, rgba(2, 132, 199, 0.07) 0%, rgba(255, 255, 255, 0.95) 100%);border:1px solid rgba(2, 132, 199, 0.22);cursor:pointer;transition:all 0.25s cubic-bezier(0.16, 1, 0.3, 1);position:relative;overflow:hidden">
              <div class="d-flex align-items-center justify-content-between" style="margin-bottom:var(--space-xs)">
                <span class="mini-stat__label" style="font-size:var(--text-xs);font-weight:700;color:var(--primary);letter-spacing:0.04em">Total Terpakai</span>
                <span class="icon-box icon-box--primary" style="width:30px;height:30px;border-radius:8px;background:rgba(2, 132, 199, 0.12);color:var(--primary)" aria-hidden="true">
                  <span class="material-symbols-outlined icon-sm">trending_down</span>
                </span>
              </div>
              <div class="d-flex align-items-baseline gap-2 flex-wrap">
                <span style="font-size:var(--text-2xl);font-weight:700;color:var(--primary);letter-spacing:-0.02em" data-budget-alloc-pct><?= $pctAwal ?>%</span>
                <span class="text-secondary numeric" style="font-size:var(--text-xs);font-weight:600" data-budget-alloc-spent>Rp <?= number_format($totSpent, 0, ',', '.') ?></span>
              </div>
              <p class="text-secondary" style="font-size:11px;margin:var(--space-2xs) 0 0 0;display:flex;align-items:center;gap:4px">
                <span class="material-symbols-outlined" style="font-size:13px;color:var(--primary)">touch_app</span>
                <span>Klik untuk urutkan pos belanja</span>
              </p>
            </div>

            <!-- Card 2: Sisa Anggaran -->
            <div class="interactive-stat-card" data-stat-card="remain" tabindex="0" role="button" aria-label="Lihat pos dengan sisa anggaran" title="Klik untuk fokus ke pos yang masih aman" style="padding:var(--space-md);border-radius:var(--radius-lg);background:linear-gradient(145deg, rgba(16, 185, 129, 0.07) 0%, rgba(255, 255, 255, 0.95) 100%);border:1px solid rgba(16, 185, 129, 0.22);cursor:pointer;transition:all 0.25s cubic-bezier(0.16, 1, 0.3, 1);position:relative;overflow:hidden">
              <div class="d-flex align-items-center justify-content-between" style="margin-bottom:var(--space-xs)">
                <span class="mini-stat__label" style="font-size:var(--text-xs);font-weight:700;color:#059669;letter-spacing:0.04em">Sisa Anggaran</span>
                <span class="icon-box icon-box--success" style="width:30px;height:30px;border-radius:8px;background:rgba(16, 185, 129, 0.12);color:#059669" aria-hidden="true">
                  <span class="material-symbols-outlined icon-sm">savings</span>
                </span>
              </div>
              <div class="d-flex align-items-baseline gap-2 flex-wrap">
                <span style="font-size:var(--text-2xl);font-weight:700;color:#059669;letter-spacing:-0.02em" data-budget-remain-pct><?= max(0, 100 - $pctAwal) ?>%</span>
                <span class="text-secondary numeric" style="font-size:var(--text-xs);font-weight:600" data-budget-remain-val><?= ($sisaAwal < 0 ? '-' : '') . 'Rp ' . number_format(abs($sisaAwal), 0, ',', '.') ?></span>
              </div>
              <p class="text-secondary" style="font-size:11px;margin:var(--space-2xs) 0 0 0;display:flex;align-items:center;gap:4px">
                <span class="material-symbols-outlined" style="font-size:13px;color:#059669">verified</span>
                <span>Dana aman masih tersedia</span>
              </p>
            </div>

            <!-- Card 3: Jatah Belanja per Hari -->
            <div class="interactive-stat-card" data-stat-card="daily" tabindex="0" role="button" aria-label="Lihat simulasi jatah belanja harian" title="Dihitung dari sisa anggaran dibagi sisa hari" style="padding:var(--space-md);border-radius:var(--radius-lg);background:linear-gradient(145deg, rgba(245, 158, 11, 0.07) 0%, rgba(255, 255, 255, 0.95) 100%);border:1px solid rgba(245, 158, 11, 0.22);cursor:pointer;transition:all 0.25s cubic-bezier(0.16, 1, 0.3, 1);position:relative;overflow:hidden">
              <div class="d-flex align-items-center justify-content-between" style="margin-bottom:var(--space-xs)">
                <span class="mini-stat__label" style="font-size:var(--text-xs);font-weight:700;color:#d97706;letter-spacing:0.04em">Jatah Belanja per Hari</span>
                <span class="icon-box icon-box--warning" style="width:30px;height:30px;border-radius:8px;background:rgba(245, 158, 11, 0.12);color:#d97706" aria-hidden="true">
                  <span class="material-symbols-outlined icon-sm">payments</span>
                </span>
              </div>
              <div class="d-flex align-items-baseline gap-2 flex-wrap">
                <span style="font-size:var(--text-2xl);font-weight:700;color:#d97706;letter-spacing:-0.02em" data-budget-daily-limit>Rp <?= number_format($jatahHarian, 0, ',', '.') ?></span>
              </div>
              <p class="text-secondary" style="font-size:11px;margin:var(--space-2xs) 0 0 0;display:flex;align-items:center;gap:4px" data-budget-daily-sub>
                <span class="material-symbols-outlined" style="font-size:13px;color:#d97706">event</span>
                <span>sisa <?= $sisaHari ?> hari sampai akhir bulan</span>
              </p>
            </div>
          </div>
        </div>

        <!-- Tracker Laju Waktu (Pace Tracker) -->
        <div style="padding:var(--space-sm) var(--space-md);background:rgba(255, 255, 255, 0.7);backdrop-filter:blur(8px);border-radius:var(--radius-md);border:1px solid rgba(2, 132, 199, 0.15)">
          <div class="progress-wrap" style="position:relative;height:8px;margin:var(--space-2xs) 0">
            <div class="progress" style="height:8px;border-radius:999px;background:var(--surface-container-high);overflow:hidden">
              <div class="progress__bar <?= $pctAwal >= 100 ? 'progress__bar--danger' : ($pctAwal >= 80 ? 'progress__bar--warning' : 'progress__bar--success') ?>" style="width:<?= min(100, $pctAwal) ?>%;height:100%;transition:width 0.4s ease;" data-budget-master-bar></div>
            </div>
            <span class="progress__marker" data-budget-marker style="left:<?= min(100, $pctHari) ?>%;top:-3px;height:14px;width:3px;border-radius:2px" title="Batas Waktu Kalender Ideal (<?= $pctHari ?>%)"></span>
          </div>
          <div class="row row--between row--wrap" style="font-size:var(--text-xs);color:var(--text-secondary);margin-top:var(--space-xs);gap:var(--space-xs)">
            <span class="d-flex align-items-center gap-1" data-budget-day-progress>
              <span class="material-symbols-outlined" style="font-size:14px;color:var(--primary)" aria-hidden="true">calendar_month</span>
              Hari ke-<?= $curDayInMonth ?> / <?= $totalDaysInSelectedMonth ?> (waktu berjalan <?= $pctHari ?>%)
            </span>
            <?php if ($pctAwal <= $pctHari): ?>
              <span data-budget-pace-status class="text-income d-flex align-items-center gap-1" style="font-weight:600">
                <span class="material-symbols-outlined" style="font-size:14px;" aria-hidden="true">trending_down</span>
                Lebih hemat <?= number_format($pctHari - $pctAwal, 1) ?>% dari target waktu
              </span>
            <?php else: ?>
              <span data-budget-pace-status class="text-expense d-flex align-items-center gap-1" style="font-weight:600">
                <span class="material-symbols-outlined" style="font-size:14px;" aria-hidden="true">trending_up</span>
                Lebih cepat <?= number_format($pctAwal - $pctHari, 1) ?>% dari target waktu
              </span>
            <?php endif; ?>
          </div>
        </div>
      </section>

      <!-- Kategori + form -->
      <div class="grid grid--sidebar-left" style="margin-top:var(--gutter)">

        <!-- Form set batas -->
        <div class="stack">
          <section class="card card--pad" aria-labelledby="set-batas-heading">
            <h2 class="section-title" id="set-batas-heading">Tetapkan Batas Anggaran</h2>
            <p class="page-subtitle" style="margin-bottom:var(--space-lg)">
              Tentukan plafon belanja spesifik agar pengeluaran tetap terkendali.
            </p>

            <form class="stack" id="budgetForm" novalidate>
              <div class="field">
                <label class="field__label" for="budget-category">Kategori</label>
                <select class="select" id="budget-category" data-budget-select></select>
              </div>

              <div class="field">
                <label class="field__label" for="budget-limit">Batas Anggaran</label>
                <div class="currency-input">
                  <span class="currency-input__prefix" aria-hidden="true">Rp</span>
                  <input type="text" class="input numeric" id="budget-limit"
                         data-budget-limit inputmode="numeric" placeholder="0">
                </div>
                <p class="field__hint">Masukkan angka kelipatan Rp 10.000</p>
              </div>

              <div class="row row--between" style="padding:var(--space-md);background:var(--surface-alt);border-radius:var(--radius-md)">
                <div>
                  <p style="font-weight:600;font-size:var(--text-base);margin:0">Notifikasi Peringatan</p>
                  <p class="text-secondary" style="font-size:var(--text-xs);margin:0">Ingatkan saat pemakaian mencapai 80%</p>
                </div>
                <label class="switch">
                  <input type="checkbox" checked aria-label="Aktifkan notifikasi peringatan anggaran">
                  <span class="switch__track"></span>
                </label>
              </div>

              <div class="row row--wrap" style="gap:var(--space-sm);margin-top:var(--space-sm)">
                <button type="submit" class="btn btn--primary" style="flex:1;min-width:160px">
                  <span class="material-symbols-outlined icon-sm" aria-hidden="true">check</span>
                  Terapkan Anggaran
                </button>
                <button type="button" class="btn btn--secondary" id="btn-reset-budget" style="display:none;min-width:140px" title="Lepas batas anggaran kategori ini kembali menjadi Tanpa Batas">
                  <span class="material-symbols-outlined icon-sm" aria-hidden="true">restart_alt</span>
                  Reset Alokasi
                </button>
              </div>
            </form>

            <div class="callout" style="margin-top:var(--space-md)">
              <span class="material-symbols-outlined callout__icon icon-sm" aria-hidden="true">tips_and_updates</span>
              <div>
                <p class="callout__title">Tips Efisiensi</p>
                <p class="callout__text">Alokasikan 50% pemasukan ke kebutuhan pokok, 30% keFlexibel, dan sisanya ditabung.</p>
              </div>
            </div>
          </section>
        </div>

        <!-- Budget per kategori -->
        <section class="card card--pad" aria-labelledby="kategori-heading">
          <div class="row row--between row--wrap" style="margin-bottom:var(--space-lg);gap:var(--space-sm);align-items:center">
            <div class="row" style="gap:var(--space-sm);align-items:center">
              <h2 class="section-title" id="kategori-heading" style="margin:0">Batas per Kategori</h2>
              <span class="badge badge--muted" id="badge-budget-count">Kategori</span>
            </div>
            <div class="row" style="gap:var(--space-sm);align-items:center;flex-wrap:wrap">
              <div class="d-flex align-items-center" style="gap:4px">
                <label class="visually-hidden" for="sort-budget">Urutkan anggaran</label>
                <select class="select" id="sort-budget" data-budget-sort style="min-height:38px;height:38px;font-size:var(--text-sm);width:auto;border-radius:var(--radius-md)">
                  <option value="persen">Persentase Tertinggi</option>
                  <option value="nominal">Nominal Terbesar</option>
                  <option value="nama">Nama Kategori</option>
                </select>
              </div>
              <button type="button" class="btn btn--primary" data-budget-new style="height:38px;display:inline-flex;align-items:center;gap:6px;padding:0 16px;">
                <span class="material-symbols-outlined icon-sm" aria-hidden="true">add_circle</span>
                <span>Atur Batas Baru</span>
              </button>
            </div>
          </div>

          <div class="grid grid--2" id="budget-list">
            <!-- diisi oleh script -->
          </div>
        </section>
      </div>

      <!-- Tips + unduh ringkasan -->
      <section class="card card--pad row row--between row--wrap"
               style="margin-top:var(--gutter);gap:var(--space-sm)" aria-label="Tips anggaran">
        <div class="row" style="gap:var(--space-sm);color:var(--text-secondary);flex:1;min-width:240px">
          <span class="material-symbols-outlined icon-sm" style="color:var(--primary)" aria-hidden="true">tips_and_updates</span>
          <span style="font-size:var(--text-sm)">Tips: Pantau pengeluaran harianmu agar sisa uang saku di akhir bulan tetap aman untuk ditabung.</span>
        </div>
        <button type="button" class="btn btn--ghost" data-budget-pdf style="color:var(--primary);font-size:var(--text-sm)">
          <span class="material-symbols-outlined icon-sm" aria-hidden="true">picture_as_pdf</span>
          Unduh Ringkasan Anggaran (PDF)
        </button>
      </section>

    </main>
  </div>

  <nav class="bottom-nav" aria-label="Navigasi bawah">
    <ul class="bottom-nav__list">
      <li><a class="bottom-nav__item" href="dashboard.php">
        <span class="material-symbols-outlined" aria-hidden="true">space_dashboard</span> Beranda
      </a></li>
      <li><a class="bottom-nav__item" href="transaksi.php">
        <span class="material-symbols-outlined" aria-hidden="true">receipt_long</span> Transaksi
      </a></li>
      <li><a class="bottom-nav__item bottom-nav__item--fab" href="form-transaksi.php" aria-label="Tambah Transaksi">
        <span class="material-symbols-outlined" aria-hidden="true">add</span>
      </a></li>
      <li><a class="bottom-nav__item is-active" href="anggaran.php" aria-current="page">
        <span class="material-symbols-outlined" aria-hidden="true">account_balance_wallet</span> Anggaran
      </a></li>
      <li><a class="bottom-nav__item" href="laporan.php">
        <span class="material-symbols-outlined" aria-hidden="true">bar_chart</span> Laporan
      </a></li>
    </ul>
  </nav>

  <!-- Modal Konfirmasi -->
  <div class="modal" data-modal role="dialog" aria-modal="true" aria-labelledby="modal-title">
    <div class="modal__dialog">
      <div class="modal__icon modal__icon--warning" aria-hidden="true">
        <span class="material-symbols-outlined">restart_alt</span>
      </div>
      <h2 class="modal__title" id="modal-title" data-modal-title>Konfirmasi</h2>
      <p class="modal__text" data-modal-message>Anda yakin?</p>
      <div class="modal__actions">
        <button type="button" class="btn btn--secondary" data-modal-close>Batal</button>
        <button type="button" class="btn btn--danger" data-modal-confirm>Ya, lanjutkan</button>
      </div>
    </div>
  </div>

  <div class="toast" data-toast role="status" aria-live="polite">
    <span class="material-symbols-outlined toast__icon" data-toast-icon aria-hidden="true">check_circle</span>
    <div>
      <p class="toast__title" data-toast-title></p>
      <p class="toast__message" data-toast-message></p>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/data.js"></script>
  <script src="../assets/js/api.js?v=<?= time() ?>"></script>
  <script src="../assets/js/script.js?v=<?= time() ?>"></script>
  <script>
    document.addEventListener("DOMContentLoaded", function () {
      var D = FinanceTrackData;
      var U = FinanceTrack.Utils;
      var esc = U.escapeHtml;
      var listEl = document.getElementById("budget-list");
      var sortSel = document.querySelector("[data-budget-sort]");
      var catSel = document.querySelector("[data-budget-select]");
      var limitInput = document.querySelector("[data-budget-limit]");

      /* --- Dropdown kategori (hanya expense) --- */
      D.expenseCategories().forEach(function (c) {
        var o = document.createElement("option");
        o.value = c.id;
        o.textContent = c.nama;
        catSel.appendChild(o);
      });

      var resetBudgetBtn = document.getElementById("btn-reset-budget");

      function syncBudgetFormState() {
        if (!catSel) return;
        var selectedCatId = catSel.value;
        var existingBudget = D.budgets.find(function(b) { return b.kategoriId === selectedCatId && b.batas > 0; });
        if (existingBudget) {
          if (limitInput) {
            limitInput.value = U.thousands(existingBudget.batas);
          }
          if (resetBudgetBtn) resetBudgetBtn.style.display = "inline-flex";
        } else {
          if (limitInput) {
            limitInput.value = "";
          }
          if (resetBudgetBtn) resetBudgetBtn.style.display = "none";
        }
      }

      if (catSel) {
        catSel.addEventListener("change", function() {
          syncBudgetFormState();
        });
      }

      /* --- Render kartu anggaran (menampilkan seluruh kategori pengeluaran) --- */
      function renderCards() {
        var mode = sortSel ? sortSel.value : "persen";
        var expenseCats = D.expenseCategories();
        var curMonthStr = periodSel ? periodSel.value : null;

        var withLimitCount = 0;
        var items = expenseCats.map(function (cat) {
          var b = D.budgets.find(function (bg) { return bg.kategoriId === cat.id && bg.batas > 0; });
          var spent = 0;
          var limit = 0;
          var notes = cat.nama;
          var icon = cat.icon || "category";

          if (b) {
            spent = b.terpakai;
            limit = b.batas;
            notes = b.catatan || cat.nama;
            icon = b.icon || cat.icon || "category";
            withLimitCount++;
          } else {
            // Hitung pengeluaran dari transaksi bulan ini untuk kategori tanpa batas
            spent = (D.transactions || []).filter(function (t) {
              if (t.kategoriId !== cat.id || t.tipe !== "expense") return false;
              if (curMonthStr && !t.tanggal.startsWith(curMonthStr)) return false;
              return true;
            }).reduce(function (sum, t) { return sum + t.nominal; }, 0);
          }

          var hasLimit = limit > 0;
          var pct = hasLimit ? U.percent(spent, limit) : 0;
          var sisa = hasLimit ? (limit - spent) : null;

          return {
            kategoriId: cat.id,
            nama: cat.nama,
            icon: icon,
            catatan: notes,
            terpakai: spent,
            batas: limit,
            hasLimit: hasLimit,
            pct: pct,
            sisa: sisa
          };
        });

        // Update badge total kategori
        var countBadge = document.getElementById("badge-budget-count");
        if (countBadge) {
          var noLimitCount = items.length - withLimitCount;
          countBadge.textContent = withLimitCount + " beranggaran • " + noLimitCount + " tanpa batas";
        }

        // Pengurutan kartu
        items.sort(function (a, b) {
          if (mode === "nominal") {
            if (a.hasLimit && !b.hasLimit) return -1;
            if (!a.hasLimit && b.hasLimit) return 1;
            return b.batas - a.batas;
          }
          if (mode === "nama") {
            return a.nama.localeCompare(b.nama);
          }
          // mode === "persen"
          if (a.hasLimit && !b.hasLimit) return -1;
          if (!a.hasLimit && b.hasLimit) return 1;
          return b.pct - a.pct;
        });

        listEl.innerHTML = items.map(function (item) {
          var iconTone = item.hasLimit && item.pct >= 80 ? "cell-icon--expense" : "cell-icon--primary";
          var daysPassed = Math.max(1, D.summary.tanggal || 1);
          var dailyAvg = Math.round(item.terpakai / daysPassed);

          var headBadge;
          var figuresHtml;
          var barHtml;
          var sisaHtml;
          var statusFoot;

          if (item.hasLimit) {
            var badgeCls = item.pct >= 100 ? "badge--expense" : item.pct >= 80 ? "badge--warning" : "badge--income";
            headBadge = '<span class="badge ' + badgeCls + '" data-budget-pct>' + item.pct.toFixed(1) + '%</span>';
            figuresHtml = '<span class="budget-card__spent">' + esc(U.formatRupiah(item.terpakai)) + '</span>' +
                          '<span class="budget-card__limit">dari ' + esc(U.formatRupiah(item.batas)) + '</span>';
            barHtml = '<div class="progress"><div class="progress__bar" data-budget-bar style="width:0%"></div></div>';
            sisaHtml = '<span class="mini-stat__value numeric ' + (item.sisa >= 0 ? "text-income" : "text-expense") + '" ' +
                       'style="font-size:var(--text-sm)" data-budget-remaining>' + esc(U.formatRupiah(Math.abs(item.sisa))) + '</span>';

            var st;
            if (item.pct >= 100) {
              st = { cls: "budget-card__status--over", icon: "error", text: "Melebihi Batas" };
            } else if (item.pct >= 80) {
              st = { cls: "budget-card__status--warn", icon: "warning", text: "Waspada" };
            } else if (item.pct >= 50) {
              st = { cls: "budget-card__status--safe", icon: "check_circle", text: "Aman Terkendali" };
            } else {
              st = { cls: "budget-card__status--safe", icon: "check_circle", text: "Alokasi Longgar" };
            }
            statusFoot = '<span class="budget-card__status ' + st.cls + '">' +
                           '<span class="material-symbols-outlined icon-xs" aria-hidden="true">' + st.icon + '</span>' +
                           esc(st.text) +
                         '</span>';
          } else {
            headBadge = '<span class="badge badge--muted" data-budget-pct>Tanpa Batas</span>';
            figuresHtml = '<span class="budget-card__spent">' + esc(U.formatRupiah(item.terpakai)) + '</span>' +
                          '<span class="budget-card__limit text-secondary" style="font-weight:400;font-size:var(--text-xs)">(Tanpa Batas)</span>';
            barHtml = '<div class="progress"><div class="progress__bar" data-budget-bar style="width:0%"></div></div>';
            sisaHtml = '<span class="mini-stat__value numeric text-secondary" style="font-size:var(--text-sm)" data-budget-remaining>Tidak Dibatasi</span>';
            statusFoot = '<span class="budget-card__status text-secondary">' +
                           '<span class="material-symbols-outlined icon-xs" aria-hidden="true">all_inclusive</span>' +
                           'Tanpa Batas' +
                         '</span>';
          }

          var cardTitle = item.hasLimit ? "Klik untuk mengedit atau mereset batas alokasi" : "Klik untuk menentukan batas anggaran kategori ini";

          return '<article class="budget-card" data-budget-card data-cat-id="' + esc(item.kategoriId) + '" ' +
                   'data-budget-spent="' + item.terpakai + '" data-budget-limit="' + item.batas + '" style="cursor:pointer" title="' + esc(cardTitle) + '">' +
            '<div class="budget-card__head">' +
              '<div class="cell-identity">' +
                '<span class="cell-icon ' + iconTone + '" aria-hidden="true">' +
                  '<span class="material-symbols-outlined icon-sm">' + esc(item.icon) + '</span>' +
                '</span>' +
                '<div class="cell-identity__text">' +
                  '<h3 class="cell-identity__title">' + esc(item.nama) + '</h3>' +
                  '<p class="cell-identity__sub">' + esc(item.catatan) + '</p>' +
                '</div>' +
              '</div>' +
              headBadge +
            '</div>' +
            '<div class="budget-card__figures">' +
              figuresHtml +
            '</div>' +
            barHtml +
            '<div class="grid grid--2" style="gap:var(--space-sm)">' +
              '<div class="mini-stat">' +
                '<span class="mini-stat__label">Rata-rata / hari</span>' +
                '<span class="mini-stat__value numeric" style="font-size:var(--text-sm)">' +
                  esc(U.formatRupiah(dailyAvg)) + '</span>' +
              '</div>' +
              '<div class="mini-stat">' +
                '<span class="mini-stat__label">Sisa alokasi</span>' +
                sisaHtml +
              '</div>' +
            '</div>' +
            '<div class="budget-card__foot">' +
              statusFoot +
              '<a class="budget-card__link" href="transaksi.php?category=' + encodeURIComponent(item.kategoriId) + '">Rincian' +
                '<span class="material-symbols-outlined icon-xs" aria-hidden="true">chevron_right</span>' +
              '</a>' +
            '</div>' +
          '</article>';
        }).join("");

        /* Warnai progress bar via BudgetUI */
        FinanceTrack.BudgetUI.init();

        /* Sinkronkan banner ringkasan total anggaran */
        updateSummaryBanner();
      }

      function updateSummaryBanner() {
        var totalLimit = D.budgets.reduce(function (s, b) { return s + (Number(b.batas) || 0); }, 0);
        var totalSpent = D.budgets.reduce(function (s, b) { return s + (Number(b.terpakai) || 0); }, 0);
        var sisa = totalLimit - totalSpent;
        var pct = U.percent(totalSpent, totalLimit);

        // Date calculations
        var periodSel = document.querySelector("[data-budget-period]");
        var periodVal = periodSel ? periodSel.value : "";
        var now = new Date();
        var selYear = now.getFullYear();
        var selMonth = now.getMonth() + 1;
        if (periodVal) {
          var pParts = periodVal.split("-");
          if (pParts.length === 2) {
            selYear = parseInt(pParts[0], 10);
            selMonth = parseInt(pParts[1], 10);
          }
        }

        var totalDays = new Date(selYear, selMonth, 0).getDate();
        var isCurrentMonth = (selYear === now.getFullYear() && selMonth === (now.getMonth() + 1));
        var curDay = isCurrentMonth ? now.getDate() : (selYear < now.getFullYear() || (selYear === now.getFullYear() && selMonth < (now.getMonth() + 1)) ? totalDays : 1);
        var sisaHari = isCurrentMonth ? Math.max(1, totalDays - curDay) : (selYear < now.getFullYear() || (selYear === now.getFullYear() && selMonth < (now.getMonth() + 1)) ? 0 : totalDays);
        var jatahHarian = sisaHari > 0 ? Math.max(0, Math.floor(Math.max(0, sisa) / sisaHari)) : 0;
        var pctHari = parseFloat(((curDay / totalDays) * 100).toFixed(1));

        var bulanNames = ["", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
        var monthName = bulanNames[selMonth] || "Bulan ini";

        // Status badge & description
        var statusBadge = document.querySelector("[data-budget-status-badge]");
        var statusDesc = document.querySelector("[data-budget-status-desc]");
        if (pct >= 100) {
          if (statusBadge) {
            statusBadge.className = "badge badge--expense";
            statusBadge.innerHTML = '<span class="badge--dot"></span> Status: Overbudget';
          }
          if (statusDesc) {
            statusDesc.innerHTML = '<span class="material-symbols-outlined icon-sm" style="color:var(--danger)" aria-hidden="true">error</span>' +
              '<span>Anggaran terlampaui sebesar ' + esc(U.formatRupiah(Math.abs(sisa))) + '. Rem pengeluaran segera!</span>';
          }
        } else if (pct >= 80) {
          if (statusBadge) {
            statusBadge.className = "badge badge--warning";
            statusBadge.innerHTML = '<span class="badge--dot"></span> Status: Mendekati Batas';
          }
          if (statusDesc) {
            statusDesc.innerHTML = '<span class="material-symbols-outlined icon-sm" style="color:var(--warning)" aria-hidden="true">warning</span>' +
              '<span>Pengeluaran mencapai ' + pct.toFixed(1) + '%. Sisa jatah belanja Rp ' + esc(U.formatRupiah(jatahHarian)) + '/hari.</span>';
          }
        } else {
          if (statusBadge) {
            statusBadge.className = "badge badge--income";
            statusBadge.innerHTML = '<span class="badge--dot"></span> Status: Aman & Terkendali';
          }
          if (statusDesc) {
            statusDesc.innerHTML = '<span class="material-symbols-outlined icon-sm" style="color:var(--success)" aria-hidden="true">verified</span>' +
              '<span>Sisa ' + sisaHari + ' hari lagi di ' + monthName + '. Pengeluaran Anda terkendali aman!</span>';
          }
        }

        var spentLimitEl = document.querySelector("[data-budget-total-text]");
        if (spentLimitEl) {
          spentLimitEl.innerHTML =
            '<span class="numeric" style="font-size:var(--text-4xl);font-weight:700;letter-spacing:-0.025em">' +
            esc(U.formatRupiah(totalSpent)) + "</span>" +
            ' <span class="text-secondary">/ ' + esc(U.formatRupiah(totalLimit)) + "</span>";
        }

        var allocPctEl = document.querySelector("[data-budget-alloc-pct]");
        if (allocPctEl) allocPctEl.textContent = pct.toFixed(1) + "%";

        var allocSpentEl = document.querySelector("[data-budget-alloc-spent]");
        if (allocSpentEl) allocSpentEl.textContent = U.formatRupiah(totalSpent);

        var remainPctEl = document.querySelector("[data-budget-remain-pct]");
        if (remainPctEl) remainPctEl.textContent = Math.max(0, 100 - pct).toFixed(1) + "%";

        var remainValEl = document.querySelector("[data-budget-remain-val]");
        if (remainValEl) remainValEl.textContent = (sisa < 0 ? "-" : "") + U.formatRupiah(Math.abs(sisa));

        var dailyLimitEl = document.querySelector("[data-budget-daily-limit]");
        if (dailyLimitEl) dailyLimitEl.textContent = U.formatRupiah(jatahHarian);

        var dailySubEl = document.querySelector("[data-budget-daily-sub]");
        if (dailySubEl) dailySubEl.textContent = "per hari (sisa " + sisaHari + " hari)";

        var barEl = document.querySelector("[data-budget-master-bar]");
        if (barEl) {
          barEl.style.width = Math.min(pct, 100) + "%";
          barEl.className = "progress__bar " + (pct >= 100 ? "progress__bar--danger" : pct >= 80 ? "progress__bar--warning" : "progress__bar--success");
        }

        var labelEl = document.querySelector("[data-budget-master-label]");
        if (labelEl) labelEl.textContent = "Total Terpakai (" + pct.toFixed(1) + "%)";

        var capEl = document.querySelector("[data-budget-master-cap]");
        if (capEl) capEl.textContent = "Sisa Anggaran: " + (sisa < 0 ? "-" : "") + U.formatRupiah(Math.abs(sisa));

        var markerEl = document.querySelector("[data-budget-marker]");
        if (markerEl) {
          markerEl.style.left = Math.min(pctHari, 100) + "%";
          markerEl.title = "Target Waktu Ideal (" + pctHari + "%)";
        }

        var dayProgEl = document.querySelector("[data-budget-day-progress]");
        if (dayProgEl) {
          dayProgEl.textContent = "Hari ke-" + curDay + " / " + totalDays + " (waktu berjalan " + pctHari + "%)";
        }

        var paceEl = document.querySelector("[data-budget-pace-status]");
        if (paceEl) {
          if (pct <= pctHari) {
            var diff = (pctHari - pct).toFixed(1);
            paceEl.className = "text-income";
            paceEl.textContent = "Lebih hemat " + diff + "% dari target waktu";
          } else {
            var diff = (pct - pctHari).toFixed(1);
            paceEl.className = "text-expense";
            paceEl.textContent = "Lebih cepat " + diff + "% dari target waktu";
          }
        }
      }

      renderCards();
      if (sortSel) sortSel.addEventListener("change", renderCards);


      /* --- Kontrol header: periode, buat anggaran baru, unduh ringkasan --- */
      var periodSel = document.querySelector("[data-budget-period]");
      if (periodSel) {
        periodSel.addEventListener("change", function () {
          var val = this.value;
          if (val) {
              var parts = val.split('-');
              if (parts.length === 2) {
                  window.location.href = '?year=' + parts[0] + '&month=' + parseInt(parts[1], 10);
              }
          }
        });
      }

      var newBudgetBtn = document.querySelector("[data-budget-new]");
      if (newBudgetBtn) {
        newBudgetBtn.addEventListener("click", function () {
          var headingEl = document.getElementById("set-batas-heading");
          var formCard = headingEl ? headingEl.closest(".card") : null;
          var form = document.getElementById("budgetForm");
          if (formCard) {
            formCard.scrollIntoView({ behavior: "smooth", block: "center" });
            formCard.style.transition = "box-shadow 0.3s, border-color 0.3s";
            formCard.style.borderColor = "var(--primary)";
            formCard.style.boxShadow = "0 0 0 3px rgba(2, 132, 199, 0.25)";
            setTimeout(function() {
              formCard.style.borderColor = "";
              formCard.style.boxShadow = "";
            }, 1800);
          }
          if (catSel) catSel.focus({ preventScroll: true });
        });
      }

      /* Interaktivitas Kartu Mini Stat */
      document.querySelectorAll("[data-stat-card]").forEach(function(card) {
        card.addEventListener("click", function() {
          var type = this.getAttribute("data-stat-card");
          var sortEl = document.getElementById("sort-budget");
          if (!sortEl) return;

          document.querySelectorAll("[data-stat-card]").forEach(function(c) {
            c.style.borderColor = "";
            c.style.transform = "";
          });

          this.style.borderColor = "var(--primary)";
          this.style.transform = "translateY(-3px)";

          if (type === "spent") {
            sortEl.value = "persen";
            renderCards();
            FinanceTrack.Feedback.showToast("Diurutkan: Pengeluaran", "Menampilkan kategori dengan persentase pemakaian tertinggi.", "trending_down");
          } else if (type === "remain") {
            sortEl.value = "nominal";
            renderCards();
            FinanceTrack.Feedback.showToast("Diurutkan: Alokasi", "Menampilkan kategori dengan plafon alokasi terbesar.", "savings");
          } else if (type === "daily") {
            FinanceTrack.Feedback.showToast("Jatah Harian", "Jatah belanja per hari dihitung dari Sisa Anggaran dibagi sisa hari sampai akhir bulan.", "payments");
          }

          var catSection = document.getElementById("kategori-heading");
          if (catSection && type !== "daily") {
            catSection.scrollIntoView({ behavior: "smooth", block: "start" });
          }
        });
      });

      var pdfBtn = document.querySelector("[data-budget-pdf]");
      if (pdfBtn) {
        pdfBtn.addEventListener("click", function () {
          FinanceTrack.Feedback.showToast(
            "Ringkasan disiapkan",
            "File PDF ringkasan anggaran akan diunduh setelah fitur ekspor aktif.",
            "download"
          );
        });
      }

      /* --- Format input batas --- */
      limitInput.addEventListener("input", function (e) {
        var digits = e.target.value.replace(/\D/g, "");
        e.target.value = digits ? U.thousands(digits) : "";
      });

      /* --- Action Tombol Reset Alokasi --- */
      if (resetBudgetBtn) {
        resetBudgetBtn.addEventListener("click", function () {
          var selectedCatId = catSel.value;
          var existingBudget = D.budgets.find(function (b) { return b.kategoriId === selectedCatId && b.batas > 0; });
          var cat = D.getCategory(selectedCatId);
          var catName = cat ? cat.nama : selectedCatId;

          if (!existingBudget) {
            FinanceTrack.Feedback.showToast("Batas Anggaran", "Kategori ini belum memiliki batas anggaran.", "info");
            return;
          }

          FinanceTrack.Feedback.openModal({
            title: "Reset Alokasi Anggaran?",
            message: 'Lepas batas anggaran untuk kategori "' + catName + '"? Kategori ini akan kembali berstatus "Tanpa Batas" dan alokasi anggarannya akan dilepas.',
            confirmText: "Ya, Reset Alokasi",
            confirmClass: "btn--danger",
            onConfirm: function () {
              resetBudgetBtn.disabled = true;
              fetch('../api/budgets/delete.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ categoryId: selectedCatId, category_id: selectedCatId, kategoriId: selectedCatId })
              })
              .then(function (res) { return res.json(); })
              .then(function (res) {
                resetBudgetBtn.disabled = false;
                if (res.success) {
                  var idx = D.budgets.findIndex(function (b) { return b.kategoriId === selectedCatId; });
                  if (idx !== -1) D.budgets.splice(idx, 1);
                  if (limitInput) limitInput.value = "";
                  syncBudgetFormState();
                  renderCards();
                  FinanceTrack.Feedback.showToast("Alokasi Direset", 'Batas anggaran untuk kategori "' + catName + '" telah dilepas (Tanpa Batas).', "restart_alt");
                } else {
                  FinanceTrack.Feedback.showToast("Gagal", res.message || "Gagal mereset anggaran.", "error");
                }
              })
              .catch(function (err) {
                resetBudgetBtn.disabled = false;
                FinanceTrack.Feedback.showToast("Kesalahan", err.message, "error");
              });
            }
          });
        });
      }

      /* --- Klik kartu anggaran untuk langsung edit/reset di form --- */
      if (listEl) {
        listEl.addEventListener("click", function (e) {
          var card = e.target.closest("[data-budget-card]");
          if (card && card.dataset.catId) {
            var catId = card.dataset.catId;
            if (catSel) {
              catSel.value = catId;
              syncBudgetFormState();
            }
            var headingEl = document.getElementById("set-batas-heading");
            var formCard = headingEl ? headingEl.closest(".card") : null;
            if (formCard) {
              formCard.scrollIntoView({ behavior: "smooth", block: "center" });
              formCard.style.transition = "box-shadow 0.3s, border-color 0.3s";
              formCard.style.borderColor = "var(--primary)";
              formCard.style.boxShadow = "0 0 0 3px rgba(2, 132, 199, 0.25)";
              setTimeout(function () {
                formCard.style.borderColor = "";
                formCard.style.boxShadow = "";
              }, 1800);
            }
            if (limitInput) limitInput.focus();
          }
        });
      }

      /* --- Inisialisasi status form awal --- */
      syncBudgetFormState();

      /* --- Submit form batas --- */
      document.getElementById("budgetForm").addEventListener("submit", function (e) {
        e.preventDefault();
        var rawVal = limitInput.value.trim();

        if (rawVal === "") {
          FinanceTrack.Feedback.showToast(
            "Batas belum diisi",
            "Masukkan nominal batas anggaran terlebih dahulu (atau isi 0 untuk melepas batas).",
            "error"
          );
          return;
        }

        var value = U.parseRupiah(rawVal);
        var month = periodSel ? parseInt(periodSel.value.split("-")[1], 10) : new Date().getMonth() + 1;
        var year = periodSel ? parseInt(periodSel.value.split("-")[0], 10) : new Date().getFullYear();
        var btn = this.querySelector('button[type="submit"]');

        var payload = {
          kategoriId: catSel.value,
          batas: value,
          month: month,
          year: year
        };

        btn.disabled = true;
        var originalBtnContent = btn.innerHTML;
        btn.innerHTML = '<span class="material-symbols-outlined icon-sm spinner" aria-hidden="true" style="animation: spin 1s linear infinite;">sync</span> Menyimpan...';

        fetch('../api/budgets/create.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify(payload)
        })
        .then(function (res) {
          if (!res.ok) {
            return res.json().then(function (data) {
              throw new Error(data.message || 'Terjadi kesalahan pada server');
            }).catch(function (err) {
              throw new Error(err.message || 'Terjadi kesalahan pada server');
            });
          }
          return res.json();
        })
        .then(function (data) {
          btn.disabled = false;
          btn.innerHTML = originalBtnContent;
          if (data.success) {
            if (data.action === "reset" || (data.data && data.data.batas === 0)) {
              var idx = D.budgets.findIndex(function (x) { return x.kategoriId === data.data.kategoriId; });
              if (idx !== -1) D.budgets.splice(idx, 1);
              if (limitInput) limitInput.value = "";
              syncBudgetFormState();
              renderCards();
              FinanceTrack.Feedback.showToast(
                "Anggaran Direset",
                data.message || "Batas anggaran telah dilepas (Tanpa Batas).",
                "restart_alt"
              );
              return;
            }

            var b = D.budgets.find(function (x) { return x.kategoriId === data.data.kategoriId; });
            if (b) {
                b.batas = data.data.batas;
                b.terpakai = data.data.terpakai;
            } else {
                D.budgets.push({
                    kategoriId: data.data.kategoriId,
                    batas: data.data.batas,
                    terpakai: data.data.terpakai,
                    icon: data.data.icon,
                    catatan: data.data.catatan
                });
            }
            syncBudgetFormState();
            renderCards();
            FinanceTrack.Feedback.showToast(
              "Anggaran diperbarui",
              data.message || "Batas anggaran berhasil disimpan.",
              "check_circle"
            );
          } else {
            FinanceTrack.Feedback.showToast("Gagal", data.message || "Gagal menyimpan anggaran.", "error");
          }
        })
        .catch(function (err) {
          btn.disabled = false;
          btn.innerHTML = originalBtnContent;
          FinanceTrack.Feedback.showToast("Kesalahan", err.message, "error");
        });
      });
    });
  </script>


</body>
</html>

