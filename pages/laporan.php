<?php
require_once __DIR__ . '/../auth/middleware.php';
require_once __DIR__ . '/../database/bootstrap.php';
requireAuth();
$currentUser = getCurrentUser();
$userName = htmlspecialchars($currentUser['name'] ?? 'Fikri Juana');
$userRole = htmlspecialchars($currentUser['role'] ?? 'Mahasiswa');
$userAvatar = htmlspecialchars($currentUser['avatar_initials'] ?? 'FP');
$userEmail = htmlspecialchars($currentUser['email'] ?? 'demo@financetrack.test');
$bootstrapData = getDatabaseBootstrapData((int)$currentUser['id']);
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
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Dashboard FinanceTrack — ringkasan keuangan mahasiswa: saldo, pemasukan, pengeluaran, dan anggaran.">
    <title>Laporan &mdash; FinanceTrack</title>
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
            <span class="material-symbols-outlined icon-sm" aria-hidden="true">analytics</span>
            Analisis Keuangan
          </p>
          <h1 class="page-title">Laporan Keuangan</h1>
          <p class="page-subtitle">Ringkasan kondisi kas dan kecenderungan keuanganmu sepanjang <?= $summary['periode'] ?>.</p>
        </div>
        <div class="page-header__actions">
          <!-- Kapsul Kontrol Periode: Segmented + Tahun -->
          <div class="period-capsule">
            <div class="segmented segmented--3" role="group" aria-label="Periode laporan">
              <button type="button" class="segmented__btn is-active" data-range="bulanan">Bulanan</button>
              <button type="button" class="segmented__btn" data-range="kuartalan">Kuartalan</button>
              <button type="button" class="segmented__btn" data-range="tahunan">Tahunan</button>
            </div>
            <div class="d-flex align-items-center" style="padding-right:4px;">
              <label class="visually-hidden" for="laporan-tahun">Tahun laporan</label>
              <select class="select" id="laporan-tahun" data-report-year style="min-height:34px;height:34px;padding:2px 28px 2px 10px;font-size:var(--text-xs);font-weight:600;border-radius:var(--radius-md);background-color:transparent;border:none;">
                <option selected value="<?= date('Y') ?>">Tahun <?= date('Y') ?></option>
                <option value="<?= date('Y') - 1 ?>">Tahun <?= date('Y') - 1 ?></option>
                <option value="<?= date('Y') - 2 ?>">Tahun <?= date('Y') - 2 ?></option>
              </select>
            </div>
          </div>

          <!-- Grup Aksi Ekspor Dokumen -->
          <div class="d-flex align-items-center gap-2 page-header__export-group">
            <button type="button" class="btn btn--secondary text-primary" id="btnExportCsv" data-export-csv style="min-height:38px;height:38px;padding:0.375rem 0.875rem;font-size:var(--text-sm);">
              <span class="material-symbols-outlined icon-sm" aria-hidden="true">table_view</span>
              Ekspor CSV
            </button>
            <button type="button" class="btn btn--primary" id="btnExportPdf" data-export-pdf style="min-height:38px;height:38px;padding:0.375rem 1rem;font-size:var(--text-sm);">
              <span class="material-symbols-outlined icon-sm" aria-hidden="true">picture_as_pdf</span>
              Unduh PDF
            </button>
          </div>
        </div>
      </div>

      <!-- Ringkasan -->
      <section aria-labelledby="ringkasan-heading">
        <h2 class="visually-hidden" id="ringkasan-heading">Ringkasan keuangan</h2>
        <div class="grid grid--4" style="gap:var(--space-md)">
          <article class="summary-card interactive-stat-card" style="background:linear-gradient(145deg, rgba(16, 185, 129, 0.08) 0%, rgba(255, 255, 255, 0.95) 100%);border:1px solid rgba(16, 185, 129, 0.22);transition:all 0.25s cubic-bezier(0.16, 1, 0.3, 1)">
            <div class="summary-card__top">
              <p class="summary-card__label" style="color:#059669;font-weight:700">Uang Saku &amp; Pemasukan (Okt)</p>
              <span class="icon-box icon-box--success" style="width:32px;height:32px;border-radius:8px;background:rgba(16, 185, 129, 0.14);color:#059669" aria-hidden="true">
                <span class="material-symbols-outlined icon-sm">account_balance_wallet</span>
              </span>
            </div>
            <div>
              <p class="summary-card__value numeric" data-report-income>Rp 4.200.000</p>
              <p class="summary-card__meta">
                <span class="badge badge--income">
                  <span class="material-symbols-outlined icon-xs" aria-hidden="true">trending_up</span>
                  +5.0%
                </span>
                vs September
              </p>
            </div>
          </article>

          <article class="summary-card interactive-stat-card" style="background:linear-gradient(145deg, rgba(239, 68, 68, 0.08) 0%, rgba(255, 255, 255, 0.95) 100%);border:1px solid rgba(239, 68, 68, 0.22);transition:all 0.25s cubic-bezier(0.16, 1, 0.3, 1)">
            <div class="summary-card__top">
              <p class="summary-card__label" style="color:var(--danger);font-weight:700">Total Pengeluaran (Okt)</p>
              <span class="icon-box icon-box--danger" style="width:32px;height:32px;border-radius:8px;background:rgba(239, 68, 68, 0.14);color:var(--danger)" aria-hidden="true">
                <span class="material-symbols-outlined icon-sm">shopping_bag</span>
              </span>
            </div>
            <div>
              <p class="summary-card__value numeric text-expense" data-report-expense>Rp 2.150.000</p>
              <p class="summary-card__meta">
                <span class="badge badge--income">
                  <span class="material-symbols-outlined icon-xs" aria-hidden="true">arrow_downward</span>
                  -8.2%
                </span>
                Hemat dari bulan lalu
              </p>
            </div>
          </article>

          <article class="summary-card interactive-stat-card" style="background:linear-gradient(145deg, rgba(2, 132, 199, 0.08) 0%, rgba(255, 255, 255, 0.95) 100%);border:1px solid rgba(2, 132, 199, 0.22);transition:all 0.25s cubic-bezier(0.16, 1, 0.3, 1)">
            <div class="summary-card__top">
              <p class="summary-card__label" style="color:var(--primary);font-weight:700">Sisa Tabungan Bersih</p>
              <span class="icon-box icon-box--primary" style="width:32px;height:32px;border-radius:8px;background:rgba(2, 132, 199, 0.14);color:var(--primary)" aria-hidden="true">
                <span class="material-symbols-outlined icon-sm">savings</span>
              </span>
            </div>
            <div>
              <p class="summary-card__value numeric text-income" data-report-saving>+Rp 2.050.000</p>
              <p class="summary-card__meta row--between">
                <span>Rasio simpanan</span>
                <strong class="text-income" data-report-saving-ratio>48.8% Disimpan</strong>
              </p>
            </div>
          </article>

          <article class="summary-card interactive-stat-card" style="background:linear-gradient(145deg, rgba(245, 158, 11, 0.08) 0%, rgba(255, 255, 255, 0.95) 100%);border:1px solid rgba(245, 158, 11, 0.22);transition:all 0.25s cubic-bezier(0.16, 1, 0.3, 1)">
            <div class="summary-card__top">
              <p class="summary-card__label" style="color:#d97706;font-weight:700">Rata-rata Belanja Harian</p>
              <span class="icon-box icon-box--warning" style="width:32px;height:32px;border-radius:8px;background:rgba(245, 158, 11, 0.14);color:#d97706" aria-hidden="true">
                <span class="material-symbols-outlined icon-sm">calendar_today</span>
              </span>
            </div>
            <div>
              <p class="summary-card__value numeric" data-report-avg-daily style="color:#d97706">Rp 0</p>
              <p class="summary-card__meta">Di bawah batas Rp 75.000/hari</p>
            </div>
          </article>
        </div>
      </section>

      <!-- Chart area -->
      <section id="section-arus-kas" class="card card--pad" style="margin-top:var(--gutter)" aria-labelledby="arus-kas-heading">
        <div class="row row--between row--wrap" style="margin-bottom:var(--space-lg)">
          <div>
            <h2 class="section-title" id="arus-kas-heading">Arus Kas Harian</h2>
            <p class="page-subtitle">Pemasukan vs pengeluaran pada setiap hari yang tercatat di <?= $summary['periode'] ?>.</p>
          </div>
          <div class="chart-legend">
            <span class="chart-legend__item"><i class="chart-legend__swatch" style="background:var(--success)"></i> Pemasukan</span>
            <span class="chart-legend__item"><i class="chart-legend__swatch" style="background:var(--danger)"></i> Pengeluaran</span>
          </div>
        </div>
        <div class="chart-box">
          <canvas data-chart-cashflow height="90" role="img"
                  aria-label="Grafik batang arus kas harian: pemasukan dan pengeluaran 1-24 <?= $summary['periode'] ?>"></canvas>
        </div>
      </section>

      <!-- Komposisi & health -->
      <div class="grid grid--2" style="margin-top:var(--gutter)">

        <section class="card card--pad" aria-labelledby="komposisi-heading">
          <h2 class="section-title" id="komposisi-heading">Komposisi Pengeluaran</h2>
          <p class="page-subtitle" style="margin-bottom:var(--space-md)">Distribusi belanja per kategori.</p>
          <div class="chart-box chart-box--compact">
            <canvas data-chart-expense height="110" role="img"
                    aria-label="Grafik donat komposisi pengeluaran per kategori"></canvas>
          </div>
          <ul class="legend-list" data-chart-expense-legend></ul>
        </section>

        <section class="card card--pad" aria-labelledby="kesehatan-heading">
          <h2 class="section-title" id="kesehatan-heading">Kesehatan Keuangan</h2>
          <p class="page-subtitle" style="margin-bottom:var(--space-md)">Skor otomatis dari rasio keuanganmu.</p>

          <div class="row" style="gap:var(--space-lg);align-items:center;flex-wrap:wrap">
            <div class="score-ring" style="--score:78" role="img" aria-label="Skor kesehatan keuangan 78 dari 100">
              <span class="score-ring__value">78</span>
              <span class="score-ring__label">dari 100</span>
            </div>
            <div class="stack stack--sm" style="flex:1;min-width:180px">
              <div class="stack stack--xs">
                <div class="row row--between" style="font-size:var(--text-xs);color:var(--text-secondary)">
                  <span>Rasio tabungan</span><strong class="text-income">+18,4%</strong>
                </div>
                <div class="progress"><div class="progress__bar progress__bar--success" style="width:74%"></div></div>
              </div>
              <div class="stack stack--xs">
                <div class="row row--between" style="font-size:var(--text-xs);color:var(--text-secondary)">
                  <span>Kepatuhan anggaran</span><strong class="text-primary">92%</strong>
                </div>
                <div class="progress"><div class="progress__bar" style="width:92%"></div></div>
              </div>
              <div class="stack stack--xs">
                <div class="row row--between" style="font-size:var(--text-xs);color:var(--text-secondary)">
                  <span>Kebiasaan mencatat</span><strong class="text-primary">21 hari aktif</strong>
                </div>
                <div class="progress"><div class="progress__bar progress__bar--warning" style="width:68%"></div></div>
              </div>
            </div>
          </div>

          <div class="callout callout--success" style="margin-top:var(--space-md)">
            <span class="material-symbols-outlined callout__icon icon-sm" aria-hidden="true">verified</span>
            <div>
              <p class="callout__title">Rasio aman</p>
              <p class="callout__text">Pengeluaranmu 79,6% dari pemasukan — masih di bawah batas aman 90% yang disarankan.</p>
            </div>
          </div>
        </section>
      </div>

      <!-- Rekap 6 bulan -->
      <section class="card card--pad" style="margin-top:var(--gutter)" aria-labelledby="rekap-bulan-heading">
        <div class="row row--between row--wrap" style="margin-bottom:var(--space-lg)">
          <div>
            <h2 class="section-title" id="rekap-bulan-heading">Rekap 6 Bulan Terakhir</h2>
            <p class="page-subtitle">Perbandingan pemasukan dan pengeluaran bulanan (Mei — <?= $summary['periode'] ?>).</p>
          </div>
          <div class="chart-legend">
            <span class="chart-legend__item"><i class="chart-legend__swatch" style="background:var(--primary)"></i> Pemasukan</span>
            <span class="chart-legend__item"><i class="chart-legend__swatch" style="background:var(--danger)"></i> Pengeluaran</span>
          </div>
        </div>
        <div class="chart-box">
          <canvas data-chart-monthly height="90" role="img"
                  aria-label="Grafik batang rekap pemasukan dan pengeluaran 6 bulan terakhir"></canvas>
        </div>
      </section>

      <!-- Tabel kategori -->
      <section class="card card--pad" style="margin-top:var(--gutter)" aria-labelledby="rekap-heading">
        <div class="row row--between row--wrap" style="margin-bottom:var(--space-lg)">
          <h2 class="section-title" id="rekap-heading">Rekap Per Kategori</h2>
          <a class="btn btn--ghost btn--sm" href="transaksi.php">
            Lihat Semua Transaksi
            <span class="material-symbols-outlined icon-sm" aria-hidden="true">arrow_forward</span>
          </a>
        </div>
        <div id="report-table">
          <!-- diisi oleh script -->
        </div>
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
      <li><a class="bottom-nav__item" href="anggaran.php">
        <span class="material-symbols-outlined" aria-hidden="true">account_balance_wallet</span> Anggaran
      </a></li>
      <li><a class="bottom-nav__item is-active" href="laporan.php" aria-current="page">
        <span class="material-symbols-outlined" aria-hidden="true">bar_chart</span> Laporan
      </a></li>
    </ul>
  </nav>

  <div class="toast" data-toast role="status" aria-live="polite">
    <span class="material-symbols-outlined toast__icon" data-toast-icon aria-hidden="true">check_circle</span>
    <div>
      <p class="toast__title" data-toast-title></p>
      <p class="toast__message" data-toast-message></p>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/data.js"></script>
  <script src="../assets/js/api.js"></script>
  <script src="../assets/js/script.js?v=1791587905545"></script>
  <script>
    document.addEventListener("DOMContentLoaded", function () {
      var D = FinanceTrackData;
      var U = FinanceTrack.Utils;
      var esc = U.escapeHtml;

      /* --- Ringkasan: headline memakai D.summary (sumber kebenaran bulanan) --- */
      var sisa = D.summary.pemasukan - D.summary.pengeluaran;
      var rasio = D.summary.pemasukan ? (sisa / D.summary.pemasukan) * 100 : 0;
      document.querySelector("[data-report-income]").textContent =
        U.formatRupiah(D.summary.pemasukan);
      document.querySelector("[data-report-expense]").textContent =
        U.formatRupiah(D.summary.pengeluaran);
      document.querySelector("[data-report-saving]").textContent =
        (sisa >= 0 ? "+" : "-") + U.formatRupiah(Math.abs(sisa));
      document.querySelector("[data-report-saving-ratio]").textContent =
        rasio.toFixed(1) + "% Disimpan";
      
      var initialDays = D.summary.tanggal || 1;
      var initialAvgDaily = Math.round(D.summary.pengeluaran / initialDays);
      var avgDailyEl = document.querySelector("[data-report-avg-daily]");
      if (avgDailyEl) avgDailyEl.textContent = U.formatRupiah(initialAvgDaily);

      /* --- State Filter & Ekspor --- */
      var currentRange = 'bulanan';
      var currentYear = document.querySelector("[data-report-year]") ? document.querySelector("[data-report-year]").value : new Date().getFullYear();

      var btnCsv = document.getElementById("btnExportCsv") || document.querySelector("[data-export-csv]");
      if (btnCsv) {
        btnCsv.addEventListener("click", function () {
          window.location.href = "../api/reports/export_csv.php?range=" + currentRange + "&year=" + currentYear;
        });
      }

      var btnPdf = document.getElementById("btnExportPdf") || document.querySelector("[data-export-pdf]");
      if (btnPdf) {
        btnPdf.addEventListener("click", function () {
          window.print();
        });
      }

      function updateReports() {
        var url = "../api/reports/summary.php?range=" + currentRange + "&year=" + currentYear;
        fetch(url)
          .then(function(res) { return res.json(); })
          .then(function(res) {
            if (res.success) {
                var sum = res.summary;
                document.querySelector("[data-report-income]").textContent = U.formatRupiah(sum.pemasukan);
                document.querySelector("[data-report-expense]").textContent = U.formatRupiah(sum.pengeluaran);
                var sisaText = sum.sisaTabungan >= 0 ? "+" + U.formatRupiah(sum.sisaTabungan) : "-" + U.formatRupiah(Math.abs(sum.sisaTabungan));
                document.querySelector("[data-report-saving]").textContent = sisaText;
                document.querySelector("[data-report-saving-ratio]").textContent = sum.rasioTabungan.toFixed(1) + "% Disimpan";

                var days = 30;
                if (currentRange === 'tahunan') days = 365;
                else if (currentRange === 'kuartalan') days = 90;
                else {
                    var now = new Date();
                    if (parseInt(currentYear) === now.getFullYear()) days = now.getDate() || 1;
                    else days = 30;
                }
                var avgDailyEl = document.querySelector("[data-report-avg-daily]");
                if (avgDailyEl) avgDailyEl.textContent = U.formatRupiah(Math.round(sum.pengeluaran / days));

                var catRows = res.categoryReport ? res.categoryReport.filter(function(r) { return r.total > 0; }) : [];
                var tableHtml = '';
                if (catRows && catRows.length > 0) {
                    var maxTotal = catRows[0].total || 1;
                    tableHtml = '<div class="table-wrap table--responsive"><table class="table">' +
                      '<caption class="visually-hidden">Rekap pengeluaran per kategori</caption>' +
                      '<thead><tr><th scope="col">Kategori</th><th scope="col" class="table__num">Total</th>' +
                      '<th scope="col" class="table__num">Porsi</th><th scope="col">Proporsi</th>' +
                      '<th scope="col" class="table__num">Rata-rata / hari</th></tr></thead><tbody>' +
                      catRows.map(function(r) {
                        var pct = U.percent(r.total, maxTotal);
                        var avgDaily = Math.round(r.total / days);
                        
                        return '<tr>' +
                          '<td><div class="cell-identity"><span class="cell-icon" style="background-color: '+esc(r.warna)+'20; color: '+esc(r.warna)+'"><span class="material-symbols-outlined icon-sm">' + esc(r.icon) + '</span></span>' +
                          '<div class="cell-identity__text"><span class="cell-identity__title">' + esc(r.nama) + '</span>' +
                          '<span class="cell-identity__sub">' + r.jumlahTransaksi + ' transaksi</span></div></div></td>' +
                          '<td class="table__num numeric text-expense">' + esc(U.formatRupiah(r.total)) + '</td>' +
                          '<td class="table__num numeric">' + r.persen.toFixed(1) + '%</td>' +
                          '<td><div class="progress" style="min-width:120px"><div class="progress__bar" style="background-color: '+esc(r.warna)+'; width:' + pct + '%"></div></div></td>' +
                          '<td class="table__num numeric">' + esc(U.formatRupiah(avgDaily)) + '</td>' +
                          '</tr>';
                      }).join('') + '</tbody></table></div>';
                } else {
                    tableHtml = '<div style="padding:var(--space-xl);text-align:center;color:var(--text-secondary)">Tidak ada data pengeluaran untuk periode ini.</div>';
                }
                document.getElementById("report-table").innerHTML = tableHtml;

                var dailySection = document.getElementById("section-arus-kas");
                if (dailySection) {
                    dailySection.style.display = currentRange !== 'bulanan' ? 'none' : '';
                }

                if (typeof Chart !== "undefined") {
                    var expenseCtx = document.querySelector("[data-chart-expense]");
                    if (expenseCtx && Chart.getChart(expenseCtx)) {
                        var expChart = Chart.getChart(expenseCtx);
                        expChart.data.labels = catRows.map(function(r) { return r.nama; });
                        expChart.data.datasets[0].data = catRows.map(function(r) { return r.total; });
                        expChart.data.datasets[0].backgroundColor = catRows.map(function(r) { return r.warna; });
                        expChart.update();
                        
                        var legendEl = document.querySelector("[data-chart-expense-legend]");
                        if (legendEl) {
                            legendEl.innerHTML = catRows.map(function(r) { 
                                return '<li class="legend-list__item" style="display:flex;align-items:center;gap:8px;font-size:var(--text-sm);margin-bottom:8px;"><span style="width:12px;height:12px;border-radius:50%;background:'+r.warna+';display:inline-block;"></span><span style="flex:1">'+r.nama+'</span><span style="font-weight:600">'+r.persen+'%</span></li>';
                            }).join('');
                        }
                    }

                    var monthlyCtx = document.querySelector("[data-chart-monthly]");
                    if (monthlyCtx && Chart.getChart(monthlyCtx)) {
                        var mChart = Chart.getChart(monthlyCtx);
                        var mData = res.monthlyRecap;
                        mChart.data.labels = mData.map(function(m) { return m.bulan; });
                        mChart.data.datasets[0].data = mData.map(function(m) { return m.pemasukan; });
                        mChart.data.datasets[1].data = mData.map(function(m) { return m.pengeluaran; });
                        mChart.update();
                    }

                    if (currentRange === 'bulanan' && res.dailyCashflow) {
                        var cashflowCtx = document.querySelector("[data-chart-cashflow]");
                        if (cashflowCtx && Chart.getChart(cashflowCtx)) {
                            var cfChart = Chart.getChart(cashflowCtx);
                            var cfData = res.dailyCashflow;
                            cfChart.data.labels = cfData.map(function(c) { return c.tanggal; });
                            cfChart.data.datasets[0].data = cfData.map(function(c) { return c.pemasukan; });
                            cfChart.data.datasets[1].data = cfData.map(function(c) { return c.pengeluaran; });
                            cfChart.update();
                        }
                    }
                }

                FinanceTrack.Feedback.showToast(
                  "Laporan diperbarui",
                  "Menampilkan data " + currentRange + " tahun " + currentYear + ".",
                  "check_circle"
                );
            }
          })
          .catch(function(err) {
              console.error(err);
              FinanceTrack.Feedback.showToast("Error", "Gagal memuat data laporan.", "error");
          });
      }

      /* --- Filter periode --- */
      document.querySelectorAll("[data-range]").forEach(function (chip) {
        chip.addEventListener("click", function () {
          document.querySelectorAll("[data-range]").forEach(function (c) { c.classList.remove("is-active"); });
          chip.classList.add("is-active");
          currentRange = chip.getAttribute("data-range");
          updateReports();
        });
      });

      /* --- Pilihan tahun --- */
      var yearSelect = document.querySelector("[data-report-year]");
      if (yearSelect) {
        yearSelect.addEventListener("change", function () {
          currentYear = yearSelect.value;
          updateReports();
        });
      }

      /* --- Rekap per kategori (dari budgets, sinkron dengan donut & Anggaran) --- */
      var rows = D.monthlyCategoryReport();
      var maxTotal = rows.length ? rows[0].total : 1;
      var totalExpense = rows.reduce(function (s, r) { return s + r.total; }, 0) || 1;

      // Trigger updateReports on load to ensure consistency with API
      updateReports();
    });
  </script>


</body>
</html>

