<?php
require_once __DIR__ . '/../auth/middleware.php';
require_once __DIR__ . '/../database/bootstrap.php';
requireAuth();
$currentUser = getCurrentUser();
$userName = htmlspecialchars($currentUser['name'] ?? 'Fikri Juana');
$userRole = htmlspecialchars($currentUser['role'] ?? 'Mahasiswa');
$userAvatar = htmlspecialchars($currentUser['avatar'] ?? 'FP');
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
$categories = $bootstrapData['categories'] ?? [];
$totAnggaran = $summary['totalAnggaran'] ?? 3200000;
$sisAnggaran = $summary['sisaAnggaran'] ?? 0;
$pctAnggaran = $totAnggaran > 0 ? round(($sisAnggaran / $totAnggaran) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Manajemen Kategori Transaksi — FinanceTrack">
  <title>Kategori Transaksi &mdash; FinanceTrack</title>
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

  <!-- Sidebar -->
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
          <a class="nav-item is-active" href="kategori.php">
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

    <!-- Sisa Anggaran Widget -->
    <div style="padding:var(--space-md);background:var(--surface-container-low);margin:var(--space-md);border-radius:var(--radius-md);flex-shrink:0">
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

    <!-- Header / Topbar -->
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
          <div class="topbar-dropdown" data-user-menu-dropdown role="menu">
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

      <!-- Page Header -->
      <div class="page-header">
        <div>
          <p class="eyebrow">
            <span class="material-symbols-outlined icon-sm" aria-hidden="true">category</span>
            Master Data Keuangan
          </p>
          <h1 class="page-title">Kategori Transaksi</h1>
          <p class="page-subtitle">Kelola pos pengeluaran dan sumber pemasukan beserta ikon dan warna representatif.</p>
        </div>

      </div>

      <!-- Ringkasan Kategori (4 Cards) -->
      <section class="grid grid--4" aria-label="Statistik kategori" style="margin-bottom:var(--gutter);gap:var(--space-md)">
        <article class="summary-card interactive-stat-card" data-cat-stat="all" tabindex="0" role="button" aria-label="Lihat semua kategori" title="Klik untuk tampilkan semua kategori" style="background:linear-gradient(145deg, rgba(2, 132, 199, 0.08) 0%, rgba(255, 255, 255, 0.95) 100%);border:1px solid rgba(2, 132, 199, 0.22);cursor:pointer;transition:all 0.25s cubic-bezier(0.16, 1, 0.3, 1)">
          <div class="summary-card__top">
            <p class="summary-card__label" style="color:var(--primary);font-weight:700">Total Kategori</p>
            <span class="icon-box icon-box--primary" style="width:32px;height:32px;border-radius:8px;background:rgba(2, 132, 199, 0.14);color:var(--primary)" aria-hidden="true">
              <span class="material-symbols-outlined icon-sm">category</span>
            </span>
          </div>
          <div>
            <p class="summary-card__value" id="stat-total-cat" style="color:var(--on-surface)">0</p>
            <p class="summary-card__meta" style="font-size:11px;display:flex;align-items:center;gap:4px">
              <span class="material-symbols-outlined" style="font-size:13px;color:var(--primary)">touch_app</span>
              <span>Tampilkan semua pos</span>
            </p>
          </div>
        </article>

        <article class="summary-card interactive-stat-card" data-cat-stat="expense" tabindex="0" role="button" aria-label="Lihat pos pengeluaran" title="Klik untuk filter pos pengeluaran" style="background:linear-gradient(145deg, rgba(239, 68, 68, 0.08) 0%, rgba(255, 255, 255, 0.95) 100%);border:1px solid rgba(239, 68, 68, 0.22);cursor:pointer;transition:all 0.25s cubic-bezier(0.16, 1, 0.3, 1)">
          <div class="summary-card__top">
            <p class="summary-card__label" style="color:var(--danger);font-weight:700">Pos Pengeluaran</p>
            <span class="icon-box icon-box--danger" style="width:32px;height:32px;border-radius:8px;background:rgba(239, 68, 68, 0.14);color:var(--danger)" aria-hidden="true">
              <span class="material-symbols-outlined icon-sm">trending_down</span>
            </span>
          </div>
          <div>
            <p class="summary-card__value text-expense" id="stat-expense-cat">0</p>
            <p class="summary-card__meta" style="font-size:11px;display:flex;align-items:center;gap:4px">
              <span class="material-symbols-outlined" style="font-size:13px;color:var(--danger)">touch_app</span>
              <span>Filter pengeluaran</span>
            </p>
          </div>
        </article>

        <article class="summary-card interactive-stat-card" data-cat-stat="income" tabindex="0" role="button" aria-label="Lihat sumber pemasukan" title="Klik untuk filter sumber pemasukan" style="background:linear-gradient(145deg, rgba(16, 185, 129, 0.08) 0%, rgba(255, 255, 255, 0.95) 100%);border:1px solid rgba(16, 185, 129, 0.22);cursor:pointer;transition:all 0.25s cubic-bezier(0.16, 1, 0.3, 1)">
          <div class="summary-card__top">
            <p class="summary-card__label" style="color:#059669;font-weight:700">Sumber Pemasukan</p>
            <span class="icon-box icon-box--success" style="width:32px;height:32px;border-radius:8px;background:rgba(16, 185, 129, 0.14);color:#059669" aria-hidden="true">
              <span class="material-symbols-outlined icon-sm">trending_up</span>
            </span>
          </div>
          <div>
            <p class="summary-card__value text-income" id="stat-income-cat">0</p>
            <p class="summary-card__meta" style="font-size:11px;display:flex;align-items:center;gap:4px">
              <span class="material-symbols-outlined" style="font-size:13px;color:#059669">touch_app</span>
              <span>Filter pemasukan</span>
            </p>
          </div>
        </article>

        <article class="summary-card interactive-stat-card" data-cat-stat="budget" tabindex="0" role="button" aria-label="Kelola anggaran kategori" title="Klik untuk kelola anggaran" style="background:linear-gradient(145deg, rgba(139, 92, 246, 0.08) 0%, rgba(255, 255, 255, 0.95) 100%);border:1px solid rgba(139, 92, 246, 0.22);cursor:pointer;transition:all 0.25s cubic-bezier(0.16, 1, 0.3, 1)">
          <div class="summary-card__top">
            <p class="summary-card__label" style="color:#7c3aed;font-weight:700">Kategori Beranggaran</p>
            <span class="icon-box icon-box--muted" style="width:32px;height:32px;border-radius:8px;background:rgba(139, 92, 246, 0.14);color:#7c3aed" aria-hidden="true">
              <span class="material-symbols-outlined icon-sm">pie_chart</span>
            </span>
          </div>
          <div>
            <p class="summary-card__value" id="stat-budget-cat" style="color:#7c3aed">0</p>
            <p class="summary-card__meta" style="font-size:11px;display:flex;align-items:center;gap:4px">
              <span class="material-symbols-outlined" style="font-size:13px;color:#7c3aed">arrow_forward</span>
              <span>Buka halaman anggaran</span>
            </p>
          </div>
        </article>
      </section>

      <!-- Main Category Card with Filter & Table -->
      <section class="card card--pad" aria-labelledby="daftar-kategori-heading">
        <div class="row row--between row--wrap" style="gap:var(--space-md); margin-bottom:var(--space-lg); align-items:center;">
          <!-- Filter Pills -->
          <div class="filter-bar__pills" role="group" aria-label="Filter jenis kategori" style="margin:0">
            <button type="button" class="filter-pill is-active" data-cat-filter="all">Semua (<span id="pill-all-count">0</span>)</button>
            <button type="button" class="filter-pill" data-cat-filter="expense">Pengeluaran (<span id="pill-exp-count">0</span>)</button>
            <button type="button" class="filter-pill" data-cat-filter="income">Pemasukan (<span id="pill-inc-count">0</span>)</button>
          </div>

          <!-- Search & Action Toolbar -->
          <div class="d-flex align-items-center gap-2 flex-wrap" style="flex:1; justify-content:flex-end;">
            <div class="input-group" style="max-width:280px; min-width:180px; flex:1; position:relative;">
              <span class="material-symbols-outlined icon-sm" aria-hidden="true" style="color:var(--text-secondary);">search</span>
              <input type="text" id="cat-search" class="input" placeholder="Cari nama kategori..." style="min-height:38px; font-size:var(--text-sm); border-radius:var(--radius-md); border:1px solid var(--border-strong); background-color:var(--surface); padding-right:32px;">
              <button type="button" class="input-group__suffix" id="cat-search-clear" aria-label="Bersihkan pencarian" style="display:none;background:none;border:none;cursor:pointer;color:var(--text-secondary);padding:4px;position:absolute;right:8px;top:50%;transform:translateY(-50%);">
                <span class="material-symbols-outlined icon-sm">close</span>
              </button>
            </div>
            <button type="button" class="btn btn--primary" data-add-category style="min-height:38px; padding:0.375rem 1rem;">
              <span class="material-symbols-outlined icon-sm" aria-hidden="true">add_circle</span>
              Tambah Kategori
            </button>
          </div>
        </div>

        <div id="category-table-container">
          <!-- Diisi via JavaScript -->
        </div>
      </section>

    </main>
  </div>

  <!-- Mobile Bottom Nav -->
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
      <li><a class="bottom-nav__item is-active" href="kategori.php" aria-current="page">
        <span class="material-symbols-outlined" aria-hidden="true">category</span>
        Kategori
      </a></li>
    </ul>
  </nav>

  <!-- Modal Konfirmasi Hapus -->
  <div class="modal" data-modal role="dialog" aria-modal="true" aria-labelledby="modal-title">
    <div class="modal__dialog">
      <div class="modal__icon modal__icon--danger" aria-hidden="true">
        <span class="material-symbols-outlined">warning</span>
      </div>
      <h2 class="modal__title" id="modal-title" data-modal-title>Konfirmasi</h2>
      <p class="modal__text" data-modal-message>Anda yakin?</p>
      <div class="modal__actions">
        <button type="button" class="btn btn--secondary" data-modal-close>Batal</button>
        <button type="button" class="btn btn--danger" data-modal-confirm>Ya, lanjutkan</button>
      </div>
    </div>
  </div>

  <!-- Modal Tambah/Edit Kategori -->
  <div class="modal" id="modal-category" role="dialog" aria-modal="true" aria-labelledby="cat-modal-title">
    <div class="modal__dialog" style="max-width:500px">
      <h2 class="modal__title" id="cat-modal-title">Kategori</h2>
      <form id="cat-form" class="stack" style="margin-top:var(--space-md)">
        <input type="hidden" id="cat-id" name="id">
        <div class="field">
          <label class="field__label" for="cat-name">Nama Kategori <span class="field__required">*</span></label>
          <input type="text" class="input" id="cat-name" name="name" placeholder="Misal: Belanja Bulanan" required>
        </div>
        <div class="grid grid--2">
          <div class="field">
            <label class="field__label" for="cat-type">Jenis <span class="field__required">*</span></label>
            <select class="select" id="cat-type" name="type">
              <option value="expense">Pengeluaran</option>
              <option value="income">Pemasukan</option>
            </select>
          </div>
          <div class="field">
            <label class="field__label" for="cat-color">Warna Tanda</label>
            <input type="color" class="input" id="cat-color" name="color" value="#0284c7" style="height:42px;padding:4px;cursor:pointer">
          </div>
        </div>
        <div class="field">
          <label class="field__label">Ikon Kategori</label>
          <input type="hidden" id="cat-icon" name="icon" value="category" required>
          <div class="icon-picker" style="border:1px solid var(--border);border-radius:var(--radius-md);padding:var(--space-xs);background:var(--surface);">
            <div style="display:flex;gap:var(--space-xs);margin-bottom:var(--space-xs);align-items:center;">
              <div style="width:36px;height:36px;background:var(--surface-alt);border-radius:var(--radius-sm);display:grid;place-items:center;border:1px solid var(--border);flex-shrink:0;" id="preview-bg">
                <span class="material-symbols-outlined icon-sm" id="selected-icon-preview" style="color:var(--text)">category</span>
              </div>
              <div style="flex:1;position:relative;">
                <span class="material-symbols-outlined icon-sm" style="position:absolute;left:8px;top:50%;transform:translateY(-50%);color:var(--text-muted);pointer-events:none;font-size:16px;">search</span>
                <input type="text" id="icon-search" class="input" placeholder="Cari ikon (cth: food, car, shop)..." style="width:100%;min-height:36px;padding:4px 8px 4px 32px;font-size:var(--text-sm);" autocomplete="off">
              </div>
            </div>
            <div id="icon-grid" style="display:grid;grid-template-columns:repeat(auto-fill, minmax(36px,1fr));gap:4px;max-height:130px;overflow-y:auto;padding:2px;">
            </div>
          </div>
        </div>
        <div class="modal__actions" style="margin-top:var(--space-md)">
          <button type="button" class="btn btn--secondary" data-cat-close>Batal</button>
          <button type="submit" class="btn btn--primary">Simpan Kategori</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Toast Notification -->
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

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/data.js"></script>
  <script src="../assets/js/api.js?v=<?= time() ?>"></script>
  <script src="../assets/js/script.js?v=<?= time() ?>"></script>
  <script>
    document.addEventListener("DOMContentLoaded", function () {
      var D = FinanceTrackData;
      var U = FinanceTrack.Utils;
      var esc = U.escapeHtml;
      var F = FinanceTrack.Feedback;

      var currentTypeFilter = "all";
      var currentSearchQuery = "";

      /* --- Update Summary Badges --- */
      function updateStats() {
        var total = D.categories.length;
        var exp = D.categories.filter(function(c) { return c.tipe === "expense"; }).length;
        var inc = D.categories.filter(function(c) { return c.tipe === "income"; }).length;
        var withBudget = D.categories.filter(function(c) {
          return D.budgets.some(function(b) { return b.kategoriId === c.id; });
        }).length;

        document.getElementById("stat-total-cat").textContent = total;
        document.getElementById("stat-expense-cat").textContent = exp;
        document.getElementById("stat-income-cat").textContent = inc;
        document.getElementById("stat-budget-cat").textContent = withBudget;

        document.getElementById("pill-all-count").textContent = total;
        document.getElementById("pill-exp-count").textContent = exp;
        document.getElementById("pill-inc-count").textContent = inc;
      }

      /* --- Render Tabel Kategori --- */
      function renderCategoryTable() {
        var list = D.categories.filter(function(c) {
          if (currentTypeFilter !== "all" && c.tipe !== currentTypeFilter) return false;
          if (currentSearchQuery && !c.nama.toLowerCase().includes(currentSearchQuery)) return false;
          return true;
        });

        var container = document.getElementById("category-table-container");
        if (list.length === 0) {
          container.innerHTML = 
            '<div style="text-align:center;padding:var(--space-2xl) var(--space-md);color:var(--text-secondary)">' +
              '<span class="material-symbols-outlined" style="font-size:48px;opacity:0.4;margin-bottom:var(--space-xs)">search_off</span>' +
              '<p style="font-weight:600;font-size:var(--text-md);margin-bottom:4px">Tidak ada kategori yang cocok</p>' +
              '<p style="font-size:var(--text-xs);margin:0">Coba ubah kata kunci pencarian atau ganti filter jenis.</p>' +
            '</div>';
          return;
        }

        container.innerHTML =
          '<div class="table-wrap">' +
          '<table class="table">' +
            '<caption class="visually-hidden">Daftar lengkap kategori transaksi</caption>' +
            '<thead><tr><th scope="col">Nama Kategori</th><th scope="col">Jenis</th><th scope="col">Status Anggaran</th><th scope="col" style="text-align:right">Aksi</th></tr></thead>' +
            '<tbody>' +
              list.map(function (c) {
                var isIncome = c.tipe === "income";
                var budget = D.budgets.find(function (b) { return b.kategoriId === c.id; });
                return '<tr>' +
                  '<td><div class="cell-identity">' +
                    '<span class="cell-icon" aria-hidden="true" style="background-color: '+esc(c.warna || '#0284c7')+'20; color: '+esc(c.warna || '#0284c7')+'">' +
                      '<span class="material-symbols-outlined icon-sm">' + esc(c.icon || 'category') + '</span></span>' +
                    '<div class="cell-identity__text">' +
                      '<p class="cell-identity__title" style="margin:0;font-weight:600">' + esc(c.nama) + '</p>' +
                    '</div></div></td>' +
                  '<td><span class="badge ' + (isIncome ? "badge--income" : "badge--expense") + '">' +
                    (isIncome ? "Pemasukan" : "Pengeluaran") + '</span></td>' +
                  '<td>' + (budget
                    ? '<span class="badge badge--primary">Batas ' + U.formatRupiah(budget.batas) + '</span>'
                    : '<span class="badge badge--muted">Tanpa batas</span>') + '</td>' +
                  '<td style="text-align:right;white-space:nowrap">' +
                    '<button type="button" class="btn btn--icon btn--ghost" data-edit-cat="'+esc(c.id)+'" aria-label="Edit Kategori"><span class="material-symbols-outlined icon-sm">edit</span></button>' +
                    '<button type="button" class="btn btn--icon btn--ghost text-danger" data-delete-cat="'+esc(c.id)+'" aria-label="Hapus Kategori"><span class="material-symbols-outlined icon-sm">delete</span></button>' +
                  '</td>' +
                '</tr>';
              }).join("") +
            '</tbody>' +
          '</table></div>';
      }

      updateStats();
      renderCategoryTable();

      /* Interaktivitas Kartu Statistik Kategori */
      document.querySelectorAll("[data-cat-stat]").forEach(function(card) {
        card.addEventListener("click", function() {
          var target = this.getAttribute("data-cat-stat");
          if (target === "budget") {
            window.location.href = "anggaran.php";
            return;
          }
          var pill = document.querySelector('[data-cat-filter="' + target + '"]');
          if (pill) {
            pill.click();
            var tableSec = document.querySelector('[aria-labelledby="daftar-kategori-heading"]');
            if (tableSec) tableSec.scrollIntoView({ behavior: "smooth", block: "start" });
          }
        });
      });

      /* --- Filter & Search Handlers --- */
      var searchInput = document.getElementById("cat-search");
      var searchClearBtn = document.getElementById("cat-search-clear");

      function updateSearchClearVisibility() {
        if (!searchClearBtn) return;
        searchClearBtn.style.display = searchInput && searchInput.value ? "block" : "none";
      }

      if (searchClearBtn) {
        searchClearBtn.addEventListener("click", function() {
          if (searchInput) searchInput.value = "";
          currentSearchQuery = "";
          updateSearchClearVisibility();
          renderCategoryTable();
          if (searchInput) searchInput.focus();
        });
      }

      if (searchInput) {
        searchInput.addEventListener("input", function(e) {
          currentSearchQuery = e.target.value.toLowerCase().trim();
          updateSearchClearVisibility();
          if (currentSearchQuery && currentTypeFilter !== "all") {
            document.querySelectorAll("[data-cat-filter]").forEach(function(p) { p.classList.remove("is-active"); });
            var allPill = document.querySelector('[data-cat-filter="all"]');
            if (allPill) allPill.classList.add("is-active");
            currentTypeFilter = "all";
          }
          renderCategoryTable();
        });
      }

      document.querySelectorAll("[data-cat-filter]").forEach(function(pill) {
        pill.addEventListener("click", function() {
          document.querySelectorAll("[data-cat-filter]").forEach(function(p) { p.classList.remove("is-active"); });
          this.classList.add("is-active");
          currentTypeFilter = this.getAttribute("data-cat-filter");
          if (searchInput && searchInput.value) {
            searchInput.value = "";
            currentSearchQuery = "";
            updateSearchClearVisibility();
          }
          renderCategoryTable();
        });
      });

      /* --- Icon Picker & Modal Category Setup --- */
      var catModal = document.getElementById("modal-category");
      var catForm = document.getElementById("cat-form");
      var CAT_ICONS = ["category","home","shopping_cart","fastfood","restaurant","local_cafe","directions_car","local_gas_station","flight","build","favorite","medical_services","school","pets","fitness_center","movie","sports_esports","receipt_long","payments","attach_money","trending_up","trending_down","savings","redeem","volunteer_activism","account_balance","account_balance_wallet","credit_card","shopping_bag","store","work","health_and_safety","celebration","checkroom","face","child_care","stroller","train","two_wheeler","local_shipping","bolt","wifi","menu_book","coffee","edit_note"];
      
      var iGrid = document.getElementById("icon-grid");
      var iSearch = document.getElementById("icon-search");
      var iInput = document.getElementById("cat-icon");
      var iPreview = document.getElementById("selected-icon-preview");
      var iBg = document.getElementById("preview-bg");
      var cInput = document.getElementById("cat-color");

      function renderIconPicker(filter) {
        if (!iGrid) return;
        iGrid.innerHTML = "";
        var search = (filter || "").toLowerCase();
        CAT_ICONS.forEach(function(icon) {
          if (icon.indexOf(search) === -1) return;
          var btn = document.createElement("button");
          btn.type = "button";
          var isActive = (iInput.value === icon);
          btn.innerHTML = '<span class="material-symbols-outlined icon-sm" style="pointer-events:none">' + icon + '</span>';
          btn.style.cssText = 'width:36px;height:36px;border-radius:var(--radius-sm);border:1px solid transparent;background:transparent;cursor:pointer;display:grid;place-items:center;color:var(--text-secondary);transition:all 0.2s;';
          if (isActive) {
            btn.style.background = cInput.value + "20";
            btn.style.color = cInput.value;
            btn.style.borderColor = cInput.value;
          }
          btn.title = icon;
          btn.onclick = function() {
            iInput.value = icon;
            updateIconPreview();
          };
          iGrid.appendChild(btn);
        });
      }

      function updateIconPreview() {
        if (!iPreview) return;
        iPreview.textContent = iInput.value;
        iPreview.style.color = cInput.value;
        iBg.style.background = cInput.value + "20";
        renderIconPicker(iSearch ? iSearch.value : "");
      }

      if (iSearch) iSearch.addEventListener("input", function(e) { renderIconPicker(e.target.value); });
      if (cInput) cInput.addEventListener("input", updateIconPreview);

      function openCatModal(id) {
        catForm.reset();
        if (id) {
          var cat = D.categories.find(function(c) { return String(c.id) === String(id); });
          if (cat) {
            document.getElementById("cat-modal-title").textContent = "Edit Kategori";
            document.getElementById("cat-id").value = cat.id;
            document.getElementById("cat-name").value = cat.nama;
            document.getElementById("cat-type").value = cat.tipe;
            document.getElementById("cat-icon").value = cat.icon || "category";
            document.getElementById("cat-color").value = cat.warna || "#0284c7";
          }
        } else {
          document.getElementById("cat-modal-title").textContent = "Tambah Kategori Baru";
          document.getElementById("cat-id").value = "";
          document.getElementById("cat-icon").value = "category";
          document.getElementById("cat-color").value = "#0284c7";
          if (iSearch) iSearch.value = "";
        }
        updateIconPreview();
        catModal.classList.add("is-open");
      }

      function closeCatModal() {
        catModal.classList.remove("is-open");
      }

      document.querySelector("[data-add-category]").addEventListener("click", function() { openCatModal(); });
      document.querySelector("[data-cat-close]").addEventListener("click", function() { closeCatModal(); });

      /* --- Action Listeners (Edit & Delete) --- */
      document.getElementById("category-table-container").addEventListener("click", function(e) {
        var btnEdit = e.target.closest("[data-edit-cat]");
        if (btnEdit) {
          openCatModal(btnEdit.getAttribute("data-edit-cat"));
        }
        var btnDelete = e.target.closest("[data-delete-cat]");
        if (btnDelete) {
          var id = btnDelete.getAttribute("data-delete-cat");
          var catToDelete = D.categories.find(function(c) { return String(c.id) === String(id); });
          var nameText = catToDelete ? catToDelete.nama : id;
          var txCount = (D.transactions || []).filter(function(t) { return t.kategoriId === id; }).length;
          var budget = (D.budgets || []).find(function(b) { return b.kategoriId === id; });

          if (txCount > 0) {
            F.showToast("Tidak Bisa Dihapus", 'Kategori "' + nameText + '" sedang digunakan pada ' + txCount + ' transaksi aktif. Hapus transaksi tersebut terlebih dahulu.', "error");
            return;
          }

          if (budget) {
            F.showToast("Tidak Bisa Dihapus", 'Kategori "' + nameText + '" memiliki batas anggaran ' + U.formatRupiah(budget.batas) + '. Silakan reset alokasi anggarannya menjadi "Tanpa Batas" di menu Anggaran terlebih dahulu.', "error");
            return;
          }

          F.openModal({
            title: "Hapus Kategori?",
            message: 'Kategori "' + nameText + '" akan dihapus permanen dari daftar kategori Anda. Tindakan ini tidak dapat dibatalkan.',
            confirmText: "Hapus Kategori",
            confirmClass: "btn--danger",
            onConfirm: function() {
              var formData = new FormData();
              formData.append("id", id);
              fetch("../api/categories/delete.php", {
                method: "POST",
                body: formData
              })
              .then(function(res) { return res.json(); })
              .then(function(res) {
                if (res.success) {
                  F.showToast("Berhasil", res.message, "check_circle");
                  var idx = D.categories.findIndex(function(c) { return String(c.id) === String(id); });
                  if (idx > -1) D.categories.splice(idx, 1);
                  updateStats();
                  renderCategoryTable();
                } else {
                  F.showToast("Gagal", res.message, "error");
                }
              }).catch(function(err) {
                console.error(err);
                F.showToast("Error", "Gagal menghubungi server.", "error");
              });
            }
          });
        }
      });

      /* --- Submit Form Kategori --- */
      catForm.addEventListener("submit", function(e) {
        e.preventDefault();
        var id = document.getElementById("cat-id").value;
        var name = document.getElementById("cat-name").value.trim();
        var type = document.getElementById("cat-type").value;
        var icon = document.getElementById("cat-icon").value;
        var color = document.getElementById("cat-color").value;

        if (!name) {
          F.showToast("Data Kurang", "Nama kategori wajib diisi.", "error");
          return;
        }

        var catNameRegex = /^[\p{L}\s&]+$/u;
        if (!catNameRegex.test(name)) {
          F.showToast("Validasi Gagal", "Nama kategori hanya boleh berisi huruf dan spasi.", "error");
          return;
        }

        var url = id ? "../api/categories/update.php" : "../api/categories/create.php";
        var formData = new FormData();
        if (id) formData.append("id", id);
        formData.append("name", name);
        formData.append("type", type);
        formData.append("icon", icon);
        formData.append("color", color);

        var btn = catForm.querySelector("button[type=submit]");
        var oldText = btn.textContent;
        btn.disabled = true;
        btn.textContent = "Menyimpan...";

        fetch(url, {
          method: "POST",
          body: formData
        })
        .then(function(res) { return res.json(); })
        .then(function(res) {
          btn.disabled = false;
          btn.textContent = oldText;
          if (res.success) {
            F.showToast("Berhasil", res.message, "check_circle");
            if (id) {
              var idx = D.categories.findIndex(function(c) { return String(c.id) === String(id); });
              if (idx > -1) D.categories[idx] = res.data;
            } else {
              D.categories.push(res.data);
            }
            D.categories.sort(function(a,b) { return a.nama.localeCompare(b.nama); });
            updateStats();
            renderCategoryTable();
            closeCatModal();
          } else {
            F.showToast("Gagal", res.message, "error");
          }
        }).catch(function(err) {
          btn.disabled = false;
          btn.textContent = oldText;
          console.error(err);
          F.showToast("Error", "Gagal menyimpan kategori.", "error");
        });
      });
    });
  </script>
</body>
</html>
