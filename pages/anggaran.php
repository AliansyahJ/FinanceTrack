<?php
require_once __DIR__ . '/../auth/middleware.php';
require_once __DIR__ . '/../database/bootstrap.php';
requireAuth();
$currentUser = getCurrentUser();
$userName = htmlspecialchars($currentUser['nama'] ?? 'Fikri Juana');
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
  <link rel="stylesheet" href="../assets/css/style.css">
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
            <span style="font-weight:600;font-size:var(--text-label-sm);margin-left:8px;margin-right:8px;"><?= $userName ?></span>
            <span class="material-symbols-outlined icon-sm" aria-hidden="true">expand_more</span>
          </button>
          <div class="topbar-dropdown" data-user-menu-dropdown role="menu" style="display:none;position:absolute;right:0;top:100%;margin-top:8px;background:var(--surface-container-lowest);border:1px solid var(--surface-container);border-radius:var(--radius-md);box-shadow:var(--shadow-md);min-width:200px;z-index:100;">
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
          <p class="page-subtitle">Tetapkan batas pengeluaran bulanan per kategori dan pantau pemakaiannya secara berkala.</p>
        </div>
        <div class="page-header__actions" style="margin-bottom: var(--space-md);">
          <div class="row" style="padding:0.5rem var(--space-md);background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-md);box-shadow:var(--shadow-xs)">
            <span class="material-symbols-outlined icon-sm text-muted" aria-hidden="true">calendar_today</span>
            <label class="visually-hidden" for="budget-period">Periode anggaran</label>
            <select class="select" id="budget-period" data-budget-period
                    style="width:auto;min-height:0;height:auto;padding:0 1.5rem 0 0;background:transparent;border:0;box-shadow:none;font-size:var(--text-sm);font-weight:500">
              <?php $bulanIndo = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember']; for ($i = 0; $i < 3; $i++) { $ts = strtotime("-$i month"); $val = date('Y-m', $ts); $label = $bulanIndo[(int)date('n', $ts)] . ' ' . date('Y', $ts); $sel = ($i === 0) ? ' selected' : ''; echo "<option value=\"$val\"$sel>$label</option>\n              "; } ?>
              
              
            </select>
          </div>
          <button type="button" class="btn btn--primary" data-budget-new>
            <span class="material-symbols-outlined icon-sm" aria-hidden="true">add_circle</span>
            + Buat Anggaran Baru
          </button>
        </div>
      </div>

      <!-- Total banner -->
      <section class="card card--pad" aria-labelledby="total-anggaran-heading">
        <div class="row row--between row--wrap" style="margin-bottom:var(--space-lg)">
          <div>
            <span class="badge badge--income">
              <span class="badge--dot"></span>
              Status: Terkendali
            </span>
            <p class="summary-card__label" id="total-anggaran-heading" style="margin-top:var(--space-sm)">Total Terpakai Bulan Ini</p>
            <div class="row" style="align-items:baseline;gap:var(--space-sm);flex-wrap:wrap" data-budget-total-text>
              <span class="numeric" style="font-size:var(--text-4xl);font-weight:700;letter-spacing:-0.025em">Rp 2.150.000</span>
              <span class="text-secondary">/ Rp 3.200.000</span>
            </div>
            <p class="text-secondary" style="font-size:var(--text-sm);display:flex;align-items:center;gap:var(--space-xs);margin-top:var(--space-xs)">
              <span class="material-symbols-outlined icon-sm" style="color:var(--success)" aria-hidden="true">verified</span>
              Tersisa 7 hari dalam Oktober. Pengeluaran uang saku terkendali.
            </p>
          </div>

          <div class="grid grid--3" style="flex:1;min-width:280px">
            <div class="mini-stat">
              <span class="mini-stat__label">Alokasi Terpakai</span>
              <span class="mini-stat__value" style="color:var(--primary)" data-budget-alloc-pct>67,2%</span>
              <span class="text-secondary numeric" style="font-size:var(--text-xs)" data-budget-alloc-spent>Rp 2.150.000</span>
            </div>
            <div class="mini-stat">
              <span class="mini-stat__label">Sisa Anggaran</span>
              <span class="mini-stat__value text-income" data-budget-remain-pct>32,8%</span>
              <span class="text-secondary numeric" style="font-size:var(--text-xs)" data-budget-remain-val>Rp 1.050.000</span>
            </div>
            <div class="mini-stat">
              <span class="mini-stat__label">Plafon Harian Aman</span>
              <span class="mini-stat__value" data-budget-daily-limit>Rp 150.000</span>
              <span class="text-secondary numeric" style="font-size:var(--text-xs)">per hari (sisa 7 hari)</span>
            </div>
          </div>
        </div>

        <!-- Master progress -->
        <div style="padding:var(--space-md);background:var(--surface-alt);border-radius:var(--radius-lg)">
          <div class="row row--between row--wrap" style="font-size:var(--text-xs);color:var(--text-secondary);margin-bottom:var(--space-sm)">
            <span class="row" style="gap:var(--space-xs)">
              <span style="width:12px;height:12px;border-radius:3px;background:var(--primary);display:inline-block"></span>
              <span data-budget-master-label>Realisasi Pengeluaran (67,2%)</span>
            </span>
            <span class="row" style="gap:var(--space-xs)">
              <span style="width:12px;height:12px;border-radius:3px;background:var(--border-strong);display:inline-block"></span>
              <span data-budget-master-cap>Kapasitas Tersedia: Rp 1.050.000</span>
            </span>
          </div>
          <div class="progress-wrap">
            <div class="progress progress--thick">
              <div class="progress__bar" style="width:67.2%" data-budget-master-bar></div>
            </div>
            <span class="progress__marker" style="left:77.4%" title="Target Waktu Ideal (77.4%)"></span>
          </div>
          <div class="row row--between row--wrap" style="font-size:var(--text-xs);color:var(--text-secondary);margin-top:var(--space-sm)">
            <span>Hari ke-24 / 31 (target ideal 77,4%)</span>
            <span class="text-income" style="font-weight:600">Lebih hemat 10,2% dari target</span>
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

              <button type="submit" class="btn btn--primary btn--block">
                <span class="material-symbols-outlined icon-sm" aria-hidden="true">check</span>
                Terapkan Anggaran
              </button>
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
          <div class="row row--between row--wrap" style="margin-bottom:var(--space-lg)">
            <div class="row">
              <h2 class="section-title" id="kategori-heading">Batas per Kategori</h2>
              <span class="badge badge--muted">7 pos aktif</span>
            </div>
            <div class="row">
              <label class="visually-hidden" for="sort-budget">Urutkan anggaran</label>
              <select class="select" id="sort-budget" data-budget-sort style="min-height:36px;font-size:var(--text-sm);width:auto">
                <option value="persen">Persentase Tertinggi</option>
                <option value="nominal">Nominal Terbesar</option>
                <option value="nama">Nama Kategori</option>
              </select>
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

  <div class="toast" data-toast role="status" aria-live="polite">
    <span class="material-symbols-outlined toast__icon" data-toast-icon aria-hidden="true">check_circle</span>
    <div>
      <p class="toast__title" data-toast-title></p>
      <p class="toast__message" data-toast-message></p>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/data.js"></script>
  <script src="../assets/js/api.js"></script>
  <script src="../assets/js/script.js"></script>
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

      /* --- Render kartu anggaran --- */
      function renderCards() {
        var mode = sortSel ? sortSel.value : "persen";
        var items = D.budgets.slice();

        items.sort(function (a, b) {
          if (mode === "nominal") return b.batas - a.batas;
          if (mode === "nama") {
            return D.getCategory(a.kategoriId).nama.localeCompare(D.getCategory(b.kategoriId).nama);
          }
          return U.percent(b.terpakai, b.batas) - U.percent(a.terpakai, a.batas);
        });

        listEl.innerHTML = items.map(function (b) {
          var cat = D.getCategory(b.kategoriId);
          var pct = U.percent(b.terpakai, b.batas);
          var sisa = b.batas - b.terpakai;

          var status;
          if (pct >= 100) {
            status = { cls: "budget-card__status--over", icon: "error", text: "Melebihi Batas" };
          } else if (pct >= 80) {
            status = { cls: "budget-card__status--warn", icon: "warning", text: "Waspada" };
          } else if (pct >= 50) {
            status = { cls: "budget-card__status--safe", icon: "check_circle", text: "Aman Terkendali" };
          } else {
            status = { cls: "budget-card__status--safe", icon: "check_circle", text: "Alokasi Longgar" };
          }

          var iconTone = pct >= 80 ? "cell-icon--expense" : "cell-icon--primary";

          return '<article class="budget-card" data-budget-card ' +
                   'data-budget-spent="' + b.terpakai + '" data-budget-limit="' + b.batas + '">' +
            '<div class="budget-card__head">' +
              '<div class="cell-identity">' +
                '<span class="cell-icon ' + iconTone + '" aria-hidden="true">' +
                  '<span class="material-symbols-outlined icon-sm">' + esc(b.icon) + '</span>' +
                '</span>' +
                '<div class="cell-identity__text">' +
                  '<h3 class="cell-identity__title">' + esc(cat.nama) + '</h3>' +
                  '<p class="cell-identity__sub">' + esc(b.catatan) + '</p>' +
                '</div>' +
              '</div>' +
              '<span class="badge ' + (pct >= 100 ? "badge--expense" : pct >= 80 ? "badge--warning" : "badge--income") + '" ' +
                'data-budget-pct>' + pct.toFixed(1) + '%</span>' +
            '</div>' +
            '<div class="budget-card__figures">' +
              '<span class="budget-card__spent">' + esc(U.formatRupiah(b.terpakai)) + '</span>' +
              '<span class="budget-card__limit">dari ' + esc(U.formatRupiah(b.batas)) + '</span>' +
            '</div>' +
            /* Kelas & lebar progress bar diurus oleh FinanceTrack.BudgetUI. */
            '<div class="progress"><div class="progress__bar" data-budget-bar style="width:0%"></div></div>' +
            '<div class="grid grid--2" style="gap:var(--space-sm)">' +
              '<div class="mini-stat">' +
                '<span class="mini-stat__label">Rata-rata / hari</span>' +
                '<span class="mini-stat__value numeric" style="font-size:var(--text-sm)">' +
                  esc(U.formatRupiah(Math.round(b.terpakai / D.summary.tanggal))) + '</span>' +
              '</div>' +
              '<div class="mini-stat">' +
                '<span class="mini-stat__label">Sisa alokasi</span>' +
                '<span class="mini-stat__value numeric ' + (sisa >= 0 ? "text-income" : "text-expense") + '" ' +
                  'style="font-size:var(--text-sm)" data-budget-remaining>' + esc(U.formatRupiah(Math.abs(sisa))) + '</span>' +
              '</div>' +
            '</div>' +
            '<div class="budget-card__foot">' +
              '<span class="budget-card__status ' + status.cls + '">' +
                '<span class="material-symbols-outlined icon-xs" aria-hidden="true">' + status.icon + '</span>' +
                esc(status.text) +
              '</span>' +
              '<a class="budget-card__link" href="transaksi.php">Rincian' +
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
        var totalLimit = D.budgets.reduce(function (s, b) { return s + b.batas; }, 0);
        var totalSpent = D.budgets.reduce(function (s, b) { return s + b.terpakai; }, 0);
        var sisa = totalLimit - totalSpent;
        var pct = U.percent(totalSpent, totalLimit);

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

        var barEl = document.querySelector("[data-budget-master-bar]");
        if (barEl) {
          barEl.style.width = Math.min(pct, 100) + "%";
          barEl.className = "progress__bar " + (pct >= 100 ? "progress__bar--danger" : pct >= 80 ? "progress__bar--warning" : "progress__bar--success");
        }

        var labelEl = document.querySelector("[data-budget-master-label]");
        if (labelEl) labelEl.textContent = "Realisasi Pengeluaran (" + pct.toFixed(1) + "%)";

        var capEl = document.querySelector("[data-budget-master-cap]");
        if (capEl) capEl.textContent = "Kapasitas Tersedia: " + U.formatRupiah(Math.max(0, sisa));
      }

      renderCards();
      if (sortSel) sortSel.addEventListener("change", renderCards);

      /* --- Isi batas otomatis sesuai kategori terpilih --- */
      function syncLimit() {
        var b = D.budgets.find(function (x) { return x.kategoriId === catSel.value; });
        limitInput.value = U.thousands(b ? b.batas : 500000);
      }
      catSel.addEventListener("change", syncLimit);
      syncLimit();

      /* --- Kontrol header: periode, buat anggaran baru, unduh ringkasan --- */
      var periodSel = document.querySelector("[data-budget-period]");
      if (periodSel) {
        periodSel.addEventListener("change", function () {
          FinanceTrack.Feedback.showToast(
            "Periode diperbarui",
            "Menampilkan ringkasan anggaran " + periodSel.options[periodSel.selectedIndex].text + ".",
            "date_range"
          );
        });
      }

      var newBudgetBtn = document.querySelector("[data-budget-new]");
      if (newBudgetBtn) {
        newBudgetBtn.addEventListener("click", function () {
          var form = document.getElementById("budgetForm");
          if (form) form.scrollIntoView({ behavior: "smooth", block: "center" });
          if (catSel) catSel.focus({ preventScroll: true });
        });
      }

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

      /* --- Submit form batas (mock) --- */
      document.getElementById("budgetForm").addEventListener("submit", function (e) {
        e.preventDefault();
        var cat = D.getCategory(catSel.value);
        var value = U.parseRupiah(limitInput.value);

        if (!value) {
          FinanceTrack.Feedback.showToast(
            "Batas belum diisi",
            "Masukkan nominal batas anggaran terlebih dahulu.",
            "error"
          );
          return;
        }

        if (typeof D.setBudget === "function") {
          D.setBudget(catSel.value, value);
        } else {
          var b = D.budgets.find(function (x) { return x.kategoriId === catSel.value; });
          if (b) {
            b.batas = value;
          } else {
            D.budgets.push({
              kategoriId: catSel.value,
              batas: value,
              terpakai: 0,
              icon: cat ? cat.icon : "category",
              catatan: cat ? cat.nama : "",
            });
          }
        }

        renderCards();

        FinanceTrack.Feedback.showToast(
          "Anggaran diperbarui",
          "Batas " + (cat ? cat.nama : "kategori") + " ditetapkan " + U.formatRupiah(value) + " per bulan.",
          "check_circle"
        );
      });
    });
  </script>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const userMenuBtn = document.querySelector('[data-user-menu-trigger]');
    const userMenuDropdown = document.querySelector('[data-user-menu-dropdown]');
    if (userMenuBtn && userMenuDropdown) {
      userMenuBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        const isExpanded = userMenuBtn.getAttribute('aria-expanded') === 'true';
        userMenuBtn.setAttribute('aria-expanded', !isExpanded);
        userMenuDropdown.style.display = isExpanded ? 'none' : 'block';
      });
      document.addEventListener('click', (e) => {
        if (!userMenuBtn.contains(e.target) && !userMenuDropdown.contains(e.target)) {
          userMenuBtn.setAttribute('aria-expanded', 'false');
          userMenuDropdown.style.display = 'none';
        }
      });
    }
  });
</script>
</body>
</html>

