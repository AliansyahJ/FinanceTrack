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
  <meta name="description" content="Dashboard FinanceTrack â€” ringkasan keuangan mahasiswa: saldo, pemasukan, pengeluaran, dan anggaran.">
    <title>Dashboard &mdash; FinanceTrack</title>
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
          <a class="nav-item is-active" href="dashboard.php" aria-current="page">
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
          <button type="submit" style="display:none;" aria-hidden="true"></button>
        </form>
      </div>
      <div class="d-flex align-items-center" style="gap:var(--space-md);flex-shrink:0;">
        <button type="button" class="btn btn--ghost btn--icon" aria-label="Notifikasi" style="position:relative;height:40px;width:40px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
          <span class="material-symbols-outlined" aria-hidden="true">notifications</span>
          <span class="topbar__dot" aria-hidden="true"></span>
        </button>
        <div class="topbar__user-menu" data-user-menu style="position:relative;flex-shrink:0;">
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

      <!-- Page header -->
      <div class="page-header">
        <div>
          <p class="eyebrow">
            <span class="material-symbols-outlined icon-sm" aria-hidden="true">bolt</span>
            Ringkasan Real-Time
          </p>
          <h1 class="page-title">Halo, <?= $userName ?></h1>
          <p class="page-subtitle">Pantau uang saku, penghasilan sampingan, dan belanja harianmu bulan ini.</p>
        </div>
        <div class="page-header__actions">
          <a class="btn btn--primary" href="form-transaksi.php" style="min-height:38px; padding:0.375rem 1rem;">
            <span class="material-symbols-outlined icon-sm" aria-hidden="true">add</span>
            Tambah Transaksi Baru
          </a>
        </div>
      </div>

      <!-- Summary cards -->
      <section class="grid grid--4" style="margin-top:var(--gutter);gap:var(--space-md)" aria-label="Ringkasan keuangan">
        <article class="summary-card interactive-stat-card" data-dash-nav="transaksi.php" tabindex="0" role="button" aria-label="Lihat transaksi saldo" title="Klik untuk lihat mutasi transaksi" style="background:linear-gradient(145deg, rgba(2, 132, 199, 0.08) 0%, rgba(255, 255, 255, 0.95) 100%);border:1px solid rgba(2, 132, 199, 0.22);cursor:pointer;transition:all 0.25s cubic-bezier(0.16, 1, 0.3, 1)">
          <div class="summary-card__top">
            <div class="d-flex align-items-center" style="gap: 8px;">
              <p class="summary-card__label" style="color:var(--primary);font-weight:700">Saldo Saat Ini</p>
              <button type="button" class="balance-hero__toggle" data-balance-toggle aria-label="Sembunyikan saldo" style="background: none; border: none; padding: 0; cursor: pointer; color: var(--on-surface-variant); display: flex;">
                <span class="material-symbols-outlined icon-sm" aria-hidden="true">visibility</span>
              </button>
            </div>
            <span class="icon-box icon-box--primary" style="width:32px;height:32px;border-radius:8px;background:rgba(2, 132, 199, 0.14);color:var(--primary)" aria-hidden="true">
              <span class="material-symbols-outlined icon-sm">account_balance_wallet</span>
            </span>
          </div>
          <div>
            <p class="summary-card__value" data-balance-value>Rp <?= isset($summary['saldo']) ? number_format($summary['saldo'], 0, ',', '.') : '0' ?></p>
            <p class="summary-card__meta" style="font-size:11px;display:flex;align-items:center;gap:4px">
              <span class="badge badge--income badge--dot">Bulan ini</span>
              <span>aman &amp; terkontrol</span>
            </p>
          </div>
        </article>

        <article class="summary-card interactive-stat-card" data-dash-nav="transaksi.php?type=income" tabindex="0" role="button" aria-label="Lihat riwayat pemasukan" title="Klik untuk lihat riwayat pemasukan" style="background:linear-gradient(145deg, rgba(16, 185, 129, 0.08) 0%, rgba(255, 255, 255, 0.95) 100%);border:1px solid rgba(16, 185, 129, 0.22);cursor:pointer;transition:all 0.25s cubic-bezier(0.16, 1, 0.3, 1)">
          <div class="summary-card__top">
            <p class="summary-card__label" style="color:#059669;font-weight:700">Total Pemasukan</p>
            <span class="icon-box icon-box--success" style="width:32px;height:32px;border-radius:8px;background:rgba(16, 185, 129, 0.14);color:#059669" aria-hidden="true">
              <span class="material-symbols-outlined icon-sm">south_west</span>
            </span>
          </div>
          <div>
            <p class="summary-card__value text-income">Rp <?= isset($summary['pemasukan']) ? number_format($summary['pemasukan'], 0, ',', '.') : '0' ?></p>
            <p class="summary-card__meta" style="font-size:11px;display:flex;align-items:center;gap:4px">
              <span class="material-symbols-outlined" style="font-size:13px;color:#059669">touch_app</span>
              <span>Lihat mutasi masuk</span>
            </p>
          </div>
        </article>

        <article class="summary-card interactive-stat-card" data-dash-nav="transaksi.php?type=expense" tabindex="0" role="button" aria-label="Lihat riwayat pengeluaran" title="Klik untuk lihat riwayat pengeluaran" style="background:linear-gradient(145deg, rgba(239, 68, 68, 0.08) 0%, rgba(255, 255, 255, 0.95) 100%);border:1px solid rgba(239, 68, 68, 0.22);cursor:pointer;transition:all 0.25s cubic-bezier(0.16, 1, 0.3, 1)">
          <div class="summary-card__top">
            <p class="summary-card__label" style="color:var(--danger);font-weight:700">Total Pengeluaran</p>
            <span class="icon-box icon-box--danger" style="width:32px;height:32px;border-radius:8px;background:rgba(239, 68, 68, 0.14);color:var(--danger)" aria-hidden="true">
              <span class="material-symbols-outlined icon-sm">north_east</span>
            </span>
          </div>
          <div>
            <p class="summary-card__value text-expense">Rp <?= isset($summary['pengeluaran']) ? number_format($summary['pengeluaran'], 0, ',', '.') : '0' ?></p>
            <p class="summary-card__meta" style="font-size:11px;display:flex;align-items:center;gap:4px">
              <span class="material-symbols-outlined" style="font-size:13px;color:var(--danger)">touch_app</span>
              <span><?= $summary['totalAnggaran'] > 0 ? round(($summary['pengeluaranBulan'] / $summary['totalAnggaran']) * 100) : 0 ?>% dari anggaran</span>
            </p>
          </div>
        </article>

        <article class="summary-card interactive-stat-card" data-dash-nav="anggaran.php" tabindex="0" role="button" aria-label="Lihat anggaran bulanan" title="Klik untuk kelola anggaran" style="background:linear-gradient(145deg, rgba(245, 158, 11, 0.08) 0%, rgba(255, 255, 255, 0.95) 100%);border:1px solid rgba(245, 158, 11, 0.22);cursor:pointer;transition:all 0.25s cubic-bezier(0.16, 1, 0.3, 1)">
          <div class="summary-card__top">
            <p class="summary-card__label" style="color:#d97706;font-weight:700">Sisa Anggaran</p>
            <span class="icon-box icon-box--warning" style="width:32px;height:32px;border-radius:8px;background:rgba(245, 158, 11, 0.14);color:#d97706" aria-hidden="true">
              <span class="material-symbols-outlined icon-sm">savings</span>
            </span>
          </div>
          <div>
            <p class="summary-card__value" style="color:#d97706">Rp <?= isset($summary['sisaAnggaran']) ? number_format($summary['sisaAnggaran'], 0, ',', '.') : '0' ?></p>
            <p class="summary-card__meta" style="font-size:11px;display:flex;align-items:center;gap:4px">
              <span class="material-symbols-outlined" style="font-size:13px;color:#d97706">arrow_forward</span>
              <span>Kelola di halaman anggaran</span>
            </p>
          </div>
        </article>
      </section>

      <!-- Chart + side panels -->
      <div class="grid grid--sidebar-right" style="margin-top:var(--gutter)">

        <!-- Financial trend -->
        <section class="card" aria-labelledby="tren-heading">
          <div class="card__header card__header--plain">
            <div>
              <h2 class="section-title" id="tren-heading">Tren Pemasukan vs Pengeluaran</h2>
              <p class="section-subtitle">Perbandingan uang masuk vs kebutuhan harian</p>
            </div>
            <div class="pill-group" role="group" aria-label="Periode grafik">
              <button type="button" class="pill" data-chart-period>30 Hari</button>
              <button type="button" class="pill is-active" data-chart-period>6 Bulan</button>
              <button type="button" class="pill" data-chart-period>1 Tahun</button>
            </div>
          </div>
          <div class="card__body">
            <div class="chart-legend" style="margin-bottom:var(--space-md)">
              <span class="chart-legend__item">
                <span class="chart-legend__swatch" style="background:var(--primary)"></span>
                Pemasukan
              </span>
              <span class="chart-legend__item">
                <span class="chart-legend__swatch" style="background:var(--danger)"></span>
                Pengeluaran
              </span>
            </div>
            <div class="chart-box">
              <canvas data-chart-trend role="img"
                      aria-label="Grafik tren pemasukan dan pengeluaran 6 bulan terakhir"></canvas>
            </div>
          </div>
        </section>

        <!-- Expense allocation -->
        <section class="card" aria-labelledby="alokasi-heading">
          <div class="card__header card__header--plain">
            <div>
              <h2 class="section-title" id="alokasi-heading">Alokasi Pengeluaran</h2>
              <p class="section-subtitle"><?= htmlspecialchars($summary['periode'] ?? ('Oktober ' . date('Y'))) ?></p>
            </div>
          </div>
          <div class="card__body">
            <div class="chart-box chart-box--sm" style="position:relative;display:grid;place-items:center">
              <canvas data-chart-expense role="img"
                      aria-label="Grafik donat komposisi pengeluaran per kategori"></canvas>
              <div data-chart-expense-total
                   style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-align:center;pointer-events:none"></div>
            </div>
            <div class="divider"></div>
            <ul class="composition-list" id="komposisi-list"></ul>
          </div>
        </section>
      </div>

      <!-- Recent transactions + budget + tip -->
      <div class="grid grid--sidebar-right" style="margin-top:var(--gutter)">

        <!-- Recent transactions -->
        <section class="card" data-tx-card aria-labelledby="transaksi-terkini-heading">
          <div class="card__header">
            <div>
              <h2 class="section-title" id="transaksi-terkini-heading">Transaksi Terkini</h2>
              <p class="section-subtitle">5 catatan mutasi terakhir</p>
            </div>
            <a class="btn btn--ghost btn--sm" href="transaksi.php">
              Lihat Semua
              <span class="material-symbols-outlined icon-sm" aria-hidden="true">arrow_forward</span>
            </a>
          </div>

          <!-- Desktop: tabel (disembunyikan otomatis di <= 768px oleh CSS) -->
          <div class="table-wrap table--responsive">
            <table class="table">
              <caption class="visually-hidden">Lima transaksi terakhir</caption>
              <thead>
                <tr>
                  <th scope="col">Deskripsi</th>
                  <th scope="col">Kategori</th>
                  <th scope="col">Tanggal</th>
                  <th scope="col" class="table__num">Jumlah</th>
                </tr>
              </thead>
              <tbody id="transaksi-terkini-body">
                <!-- diisi oleh script -->
              </tbody>
            </table>
          </div>

          <!-- Mobile: card list (hanya tampil di <= 768px, diatur oleh CSS) -->
          <div class="tx-feed table--responsive-mobile" id="transaksi-terkini-feed">
            <!-- diisi oleh script -->
          </div>
        </section>

        <div class="stack">
          <!-- Budget progress -->
          <section class="card card--pad" aria-labelledby="status-anggaran-heading">
            <div class="row row--between" style="margin-bottom:var(--space-sm)">
              <h2 class="section-title" id="status-anggaran-heading">Status Anggaran</h2>
              <a class="btn btn--ghost btn--sm" href="anggaran.php">Kelola</a>
            </div>
            <div id="budget-mini-list">
              <!-- diisi oleh script -->
            </div>
          </section>

          <!-- Financial tip -->
          <section class="callout" aria-labelledby="tips-heading">
            <span class="material-symbols-outlined callout__icon" aria-hidden="true">lightbulb</span>
            <div>
              <h2 class="callout__title" id="tips-heading">Tips Finansial <?= $userRole ?></h2>
              <p class="callout__text" id="tips-text"></p>
            </div>
          </section>
        </div>
      </div>

    </main>
  </div>

  <!-- Bottom navigation (mobile) -->
  <nav class="bottom-nav" aria-label="Navigasi bawah">
    <ul class="bottom-nav__list">
      <li>
        <a class="bottom-nav__item is-active" href="dashboard.php" aria-current="page">
          <span class="material-symbols-outlined" aria-hidden="true">space_dashboard</span>
          Beranda
        </a>
      </li>
      <li>
        <a class="bottom-nav__item" href="transaksi.php">
          <span class="material-symbols-outlined" aria-hidden="true">receipt_long</span>
          Transaksi
        </a>
      </li>
      <li>
        <a class="bottom-nav__item bottom-nav__item--fab" href="form-transaksi.php" aria-label="Tambah Transaksi">
          <span class="material-symbols-outlined" aria-hidden="true">add</span>
        </a>
      </li>
      <li>
        <a class="bottom-nav__item" href="anggaran.php">
          <span class="material-symbols-outlined" aria-hidden="true">account_balance_wallet</span>
          Anggaran
        </a>
      </li>
      <li>
        <a class="bottom-nav__item" href="laporan.php">
          <span class="material-symbols-outlined" aria-hidden="true">bar_chart</span>
          Laporan
        </a>
      </li>
    </ul>
  </nav>

  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/data.js"></script>
  <script src="../assets/js/api.js"></script>
  <script src="../assets/js/script.js?v=1791587905545"></script>
  <script>
    /* Render konten dinamis Dashboard dari mock data. */
    document.addEventListener("DOMContentLoaded", function () {
      var D = FinanceTrackData;
      var esc = FinanceTrack.Utils.escapeHtml;
      var recent = D.transactions.slice(0, 5);

      /* --- Transaksi terbaru: tabel (desktop) --- */
      var tbody = document.getElementById("transaksi-terkini-body");
      var feed = document.getElementById("transaksi-terkini-feed");

      recent.forEach(function (t) {
        var cat = D.getCategory(t.kategoriId) || { nama: t.kategoriId || "-", icon: "receipt" };
        var isIncome = t.tipe === "income";
        var method = D.getMethod(t.metode) || { nama: t.metode || "-" };

        tbody.insertAdjacentHTML("beforeend",
          '<tr>' +
            '<td><div class="cell-identity">' +
              '<span class="cell-icon ' + (isIncome ? "cell-icon--income" : "cell-icon--expense") + '" aria-hidden="true">' +
                '<span class="material-symbols-outlined icon-sm">' + esc(cat.icon) + '</span>' +
              '</span>' +
              '<div class="cell-identity__text">' +
                '<p class="cell-identity__title">' + esc(t.deskripsi) + '</p>' +
                '<p class="cell-identity__sub">' + esc(method.nama) + '</p>' +
              '</div>' +
            '</div></td>' +
            '<td><span class="badge badge--muted">' + esc(cat.nama) + '</span></td>' +
            '<td style="white-space:nowrap"><span class="numeric text-secondary">' + esc(FinanceTrack.Utils.formatTanggal(t.tanggal)) + '</span></td>' +
            '<td class="table__num"><strong class="' + (isIncome ? "text-income" : "text-expense") + '">' +
              esc(FinanceTrack.Utils.formatSigned(isIncome ? t.jumlah : -t.jumlah)) + '</strong></td>' +
          '</tr>'
        );

        feed.insertAdjacentHTML("beforeend",
          '<div class="tx-feed__item">' +
            '<div class="cell-identity">' +
              '<span class="cell-icon ' + (isIncome ? "cell-icon--income" : "cell-icon--expense") + '" aria-hidden="true">' +
                '<span class="material-symbols-outlined icon-sm">' + esc(cat.icon) + '</span>' +
              '</span>' +
              '<div class="cell-identity__text">' +
                '<p class="cell-identity__title">' + esc(t.deskripsi) + '</p>' +
                '<p class="cell-identity__sub">' + esc(FinanceTrack.Utils.formatTanggal(t.tanggal)) + '</p>' +
              '</div>' +
            '</div>' +
            '<strong class="' + (isIncome ? "text-income" : "text-expense") + '" style="white-space:nowrap">' +
              esc(FinanceTrack.Utils.formatSigned(isIncome ? t.jumlah : -t.jumlah)) + '</strong>' +
          '</div>'
        );
      });

      /* Tampilan tabel vs kartu diatur sepenuhnya oleh CSS
         (.table--responsive / .table--responsive-mobile), bukan JS. */

      /* --- Komposisi pengeluaran --- */
      var komposisi = document.getElementById("komposisi-list");
      var totalExpense = D.expenseComposition.reduce(function (s, i) { return s + i.nilai; }, 0);
      D.expenseComposition.forEach(function (item) {
        var pct = FinanceTrack.Utils.percent(item.nilai, totalExpense);
        komposisi.insertAdjacentHTML("beforeend",
          '<li class="composition-list__item">' +
            '<span class="composition-list__name">' +
              '<span class="composition-list__dot" style="background:' + item.warna + '"></span>' +
              esc(item.nama) +
            '</span>' +
            '<span class="composition-list__value">' + esc(FinanceTrack.Utils.formatRupiah(item.nilai)) + '</span>' +
            '<span class="composition-list__pct">' + pct + '%</span>' +
          '</li>'
        );
      });

      /* --- Ringkasan budget (3 teratas) --- */
      var budgetList = document.getElementById("budget-mini-list");
      D.budgets.slice(0, 3).forEach(function (b) {
        var cat = D.getCategory(b.kategoriId);
        var pct = FinanceTrack.Utils.percent(b.terpakai, b.batas);
        var barClass = pct >= 100 ? "progress__bar--danger" : pct >= 80 ? "progress__bar--warning" : "progress__bar--success";
        var pctClass = pct >= 100 ? "text-expense" : pct >= 80 ? "text-expense" : "text-income";

        budgetList.insertAdjacentHTML("beforeend",
          '<div class="budget-item">' +
            '<div class="budget-item__head">' +
              '<span class="budget-item__name">' +
                '<span class="material-symbols-outlined icon-sm text-muted" aria-hidden="true">' + esc(b.icon) + '</span>' +
                esc(cat.nama) +
              '</span>' +
              '<span class="' + pctClass + '" style="font-weight:600;font-variant-numeric:tabular-nums">' + pct + '%</span>' +
            '</div>' +
            '<div class="progress"><div class="progress__bar ' + barClass + '" style="width:' + pct + '%"></div></div>' +
            '<div class="budget-item__figures">' +
              '<span>' + esc(FinanceTrack.Utils.formatRupiah(b.terpakai)) + ' terpakai</span>' +
              '<span>Batas: ' + esc(FinanceTrack.Utils.formatRupiah(b.batas)) + '</span>' +
            '</div>' +
          '</div>'
        );
      });

      /* --- Tips --- */
      document.getElementById("tips-text").textContent = D.tips[0];
    });
  </script>

<?php if (isset($_SESSION['auth_success'])): ?>
  <script>
    try {
      sessionStorage.setItem("ft_toast", JSON.stringify({
        title: "Berhasil",
        message: "<?= htmlspecialchars($_SESSION['auth_success']) ?>",
        icon: "check_circle"
      }));
    } catch (e) {}
  </script>
<?php unset($_SESSION['auth_success']); endif; ?>

  <!-- TOAST NOTIFICATION -->
  <div class="toast" data-toast role="status" aria-live="polite">
    <span class="material-symbols-outlined toast__icon" data-toast-icon aria-hidden="true">check_circle</span>
    <div>
      <p class="toast__title" data-toast-title></p>
      <p class="toast__message" data-toast-message></p>
    </div>
    <button type="button" class="btn btn--icon btn--ghost" data-toast-close aria-label="Tutup notifikasi">
      <span class="material-symbols-outlined icon-sm">close</span>
    </button>
  </div>

  <script>
    document.addEventListener("DOMContentLoaded", function() {
      document.querySelectorAll("[data-dash-nav]").forEach(function(card) {
        card.addEventListener("click", function(e) {
          // If click was on balance toggle, don't navigate
          if (e.target.closest("[data-balance-toggle]")) return;
          var url = this.getAttribute("data-dash-nav");
          if (url) window.location.href = url;
        });
        card.addEventListener("keydown", function(e) {
          if (e.key === "Enter" || e.key === " ") {
            if (e.target.closest("[data-balance-toggle]")) return;
            e.preventDefault();
            var url = this.getAttribute("data-dash-nav");
            if (url) window.location.href = url;
          }
        });
      });
    });
  </script>
</body>
</html>



