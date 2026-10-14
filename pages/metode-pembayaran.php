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
$paymentMethods = $bootstrapData['paymentMethods'] ?? [];
$totAnggaran = $summary['totalAnggaran'] ?? 3200000;
$sisAnggaran = $summary['sisaAnggaran'] ?? 0;
$pctAnggaran = $totAnggaran > 0 ? round(($sisAnggaran / $totAnggaran) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Manajemen Metode Pembayaran — FinanceTrack">
  <title>Metode Pembayaran &mdash; FinanceTrack</title>
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
          <a class="nav-item" href="kategori.php">
            <span class="material-symbols-outlined" aria-hidden="true">category</span>
            Kategori
          </a>
        </li>
        <li>
          <a class="nav-item is-active" href="metode-pembayaran.php">
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
            <span class="material-symbols-outlined icon-sm" aria-hidden="true">payments</span>
            Master Finansial
          </p>
          <h1 class="page-title">Metode Pembayaran</h1>
          <p class="page-subtitle">Kelola dompet digital, e-wallet, rekening bank, dan kas tunai untuk mencatat transaksi harianmu.</p>
        </div>

      </div>

      <!-- 4 Summary Cards dengan Light Glassmorphism Tints & Interaktivitas -->
      <section class="grid grid--4" aria-label="Statistik metode pembayaran" style="margin-bottom:var(--gutter);gap:var(--space-md)">
        <!-- Card 1: Total Metode -->
        <article class="summary-card interactive-stat-card" data-method-stat="all" tabindex="0" role="button" aria-label="Lihat semua metode pembayaran" title="Klik untuk tampilkan semua metode" style="background:linear-gradient(145deg, rgba(2, 132, 199, 0.08) 0%, rgba(255, 255, 255, 0.95) 100%);border:1px solid rgba(2, 132, 199, 0.22);cursor:pointer;transition:all 0.25s cubic-bezier(0.16, 1, 0.3, 1)">
          <div class="summary-card__top">
            <p class="summary-card__label" style="color:var(--primary);font-weight:700">Total Metode</p>
            <span class="icon-box icon-box--primary" style="width:32px;height:32px;border-radius:8px;background:rgba(2, 132, 199, 0.14);color:var(--primary)" aria-hidden="true">
              <span class="material-symbols-outlined icon-sm">payments</span>
            </span>
          </div>
          <div>
            <p class="summary-card__value" id="stat-total-method" style="color:var(--on-surface)">0</p>
            <p class="summary-card__meta" style="font-size:11px;display:flex;align-items:center;gap:4px">
              <span class="material-symbols-outlined" style="font-size:13px;color:var(--primary)">touch_app</span>
              <span>Tampilkan semua akun</span>
            </p>
          </div>
        </article>

        <!-- Card 2: E-Wallet -->
        <article class="summary-card interactive-stat-card" data-method-stat="ewallet" tabindex="0" role="button" aria-label="Lihat dompet digital e-wallet" title="Klik untuk filter e-wallet" style="background:linear-gradient(145deg, rgba(139, 92, 246, 0.08) 0%, rgba(255, 255, 255, 0.95) 100%);border:1px solid rgba(139, 92, 246, 0.22);cursor:pointer;transition:all 0.25s cubic-bezier(0.16, 1, 0.3, 1)">
          <div class="summary-card__top">
            <p class="summary-card__label" style="color:#7c3aed;font-weight:700">Dompet Digital</p>
            <span class="icon-box icon-box--muted" style="width:32px;height:32px;border-radius:8px;background:rgba(139, 92, 246, 0.14);color:#7c3aed" aria-hidden="true">
              <span class="material-symbols-outlined icon-sm">account_balance_wallet</span>
            </span>
          </div>
          <div>
            <p class="summary-card__value" id="stat-ewallet-method" style="color:#7c3aed">0</p>
            <p class="summary-card__meta" style="font-size:11px;display:flex;align-items:center;gap:4px">
              <span class="material-symbols-outlined" style="font-size:13px;color:#7c3aed">touch_app</span>
              <span>GoPay, OVO, DANA, dll</span>
            </p>
          </div>
        </article>

        <!-- Card 3: Rekening Bank -->
        <article class="summary-card interactive-stat-card" data-method-stat="bank" tabindex="0" role="button" aria-label="Lihat rekening bank" title="Klik untuk filter rekening bank" style="background:linear-gradient(145deg, rgba(16, 185, 129, 0.08) 0%, rgba(255, 255, 255, 0.95) 100%);border:1px solid rgba(16, 185, 129, 0.22);cursor:pointer;transition:all 0.25s cubic-bezier(0.16, 1, 0.3, 1)">
          <div class="summary-card__top">
            <p class="summary-card__label" style="color:#059669;font-weight:700">Rekening Bank</p>
            <span class="icon-box icon-box--success" style="width:32px;height:32px;border-radius:8px;background:rgba(16, 185, 129, 0.14);color:#059669" aria-hidden="true">
              <span class="material-symbols-outlined icon-sm">savings</span>
            </span>
          </div>
          <div>
            <p class="summary-card__value text-income" id="stat-bank-method">0</p>
            <p class="summary-card__meta" style="font-size:11px;display:flex;align-items:center;gap:4px">
              <span class="material-symbols-outlined" style="font-size:13px;color:#059669">touch_app</span>
              <span>Tabungan &amp; transfer</span>
            </p>
          </div>
        </article>

        <!-- Card 4: Tunai / Lainnya -->
        <article class="summary-card interactive-stat-card" data-method-stat="cash" tabindex="0" role="button" aria-label="Lihat uang tunai kas" title="Klik untuk filter kas tunai" style="background:linear-gradient(145deg, rgba(245, 158, 11, 0.08) 0%, rgba(255, 255, 255, 0.95) 100%);border:1px solid rgba(245, 158, 11, 0.22);cursor:pointer;transition:all 0.25s cubic-bezier(0.16, 1, 0.3, 1)">
          <div class="summary-card__top">
            <p class="summary-card__label" style="color:#d97706;font-weight:700">Kas Tunai</p>
            <span class="icon-box icon-box--warning" style="width:32px;height:32px;border-radius:8px;background:rgba(245, 158, 11, 0.14);color:#d97706" aria-hidden="true">
              <span class="material-symbols-outlined icon-sm">wallet</span>
            </span>
          </div>
          <div>
            <p class="summary-card__value" id="stat-cash-method" style="color:#d97706">0</p>
            <p class="summary-card__meta" style="font-size:11px;display:flex;align-items:center;gap:4px">
              <span class="material-symbols-outlined" style="font-size:13px;color:#d97706">touch_app</span>
              <span>Uang fisik di dompet</span>
            </p>
          </div>
        </article>
      </section>

      <!-- Main Method Card with Filter & Table -->
      <section class="card card--pad" aria-labelledby="daftar-metode-heading">
        <div class="row row--between row--wrap" style="gap:var(--space-md); margin-bottom:var(--space-lg); align-items:center;">
          <!-- Filter Pills -->
          <div class="filter-bar__pills" role="group" aria-label="Filter jenis metode pembayaran" style="margin:0">
            <button type="button" class="filter-pill is-active" data-method-filter="all">Semua (<span id="pill-all-count">0</span>)</button>
            <button type="button" class="filter-pill" data-method-filter="ewallet">E-Wallet (<span id="pill-ewallet-count">0</span>)</button>
            <button type="button" class="filter-pill" data-method-filter="bank">Bank (<span id="pill-bank-count">0</span>)</button>
            <button type="button" class="filter-pill" data-method-filter="cash">Tunai (<span id="pill-cash-count">0</span>)</button>
            <button type="button" class="filter-pill" data-method-filter="other">Lainnya (<span id="pill-other-count">0</span>)</button>
          </div>

          <!-- Search & Action Toolbar -->
          <div class="d-flex align-items-center gap-2 flex-wrap" style="flex:1; justify-content:flex-end;">
            <div class="input-group" style="max-width:280px; min-width:180px; flex:1; position:relative;">
              <span class="material-symbols-outlined icon-sm" aria-hidden="true" style="color:var(--text-secondary);">search</span>
              <input type="text" id="method-search" class="input" placeholder="Cari nama metode..." style="min-height:38px; font-size:var(--text-sm); border-radius:var(--radius-md); border:1px solid var(--border-strong); background-color:var(--surface); padding-right:32px;">
              <button type="button" class="input-group__suffix" id="method-search-clear" aria-label="Bersihkan pencarian" style="display:none;background:none;border:none;cursor:pointer;color:var(--text-secondary);padding:4px;position:absolute;right:8px;top:50%;transform:translateY(-50%);">
                <span class="material-symbols-outlined icon-sm">close</span>
              </button>
            </div>
            <button type="button" class="btn btn--primary" data-add-method style="min-height:38px; padding:0.375rem 1rem;">
              <span class="material-symbols-outlined icon-sm" aria-hidden="true">add_circle</span>
              Tambah Metode
            </button>
          </div>
        </div>

        <div id="method-table-container">
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
      <li><a class="bottom-nav__item" href="pengaturan.php">
        <span class="material-symbols-outlined" aria-hidden="true">settings</span> Pengaturan
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

  <!-- Modal Tambah/Edit Metode Pembayaran -->
  <div class="modal" id="modal-method" role="dialog" aria-modal="true" aria-labelledby="method-modal-title">
    <div class="modal__dialog" style="max-width:500px">
      <h2 class="modal__title" id="method-modal-title">Metode Pembayaran</h2>
      <form id="method-form" class="stack" style="margin-top:var(--space-md)">
        <input type="hidden" id="method-id" name="id">
        <div class="field" id="method-id-display-container" style="display:none">
          <label class="field__label" for="method-id-display">ID Sistem (Kode Unik)</label>
          <input type="text" class="input" id="method-id-display" readonly style="background:var(--surface-alt);color:var(--text-secondary);cursor:not-allowed;font-family:var(--font-mono, monospace);font-size:var(--text-xs)" title="ID sistem unik permanen">
          <p class="field__help" style="font-size:11px;color:var(--text-muted);margin-top:2px">ID sistem digunakan otomatis untuk relasi pencatatan transaksi.</p>
        </div>
        <div class="field">
          <label class="field__label" for="method-name">Nama Metode Pembayaran <span class="field__required">*</span></label>
          <input type="text" class="input" id="method-name" name="name" placeholder="Misal: GoPay, Bank BCA, ShopeePay" required>
        </div>
        <div class="grid grid--2">
          <div class="field">
            <label class="field__label" for="method-type">Jenis / Tipe <span class="field__required">*</span></label>
            <select class="select" id="method-type" name="type">
              <option value="ewallet">Dompet Digital (E-Wallet)</option>
              <option value="bank">Rekening Bank</option>
              <option value="cash">Kas Tunai</option>
              <option value="other">Lainnya</option>
            </select>
          </div>
          <div class="field">
            <label class="field__label" for="method-color">Warna Tanda</label>
            <input type="color" class="input" id="method-color" name="color" value="#0284c7" style="height:42px;padding:4px;cursor:pointer">
          </div>
        </div>
        <div class="field">
          <label class="field__label">Ikon Metode</label>
          <input type="hidden" id="method-icon" name="icon" value="account_balance_wallet" required>
          <div class="icon-picker" style="border:1px solid var(--border);border-radius:var(--radius-md);padding:var(--space-xs);background:var(--surface);">
            <div style="display:flex;gap:var(--space-xs);margin-bottom:var(--space-xs);align-items:center;">
              <div style="width:36px;height:36px;background:var(--surface-alt);border-radius:var(--radius-sm);display:grid;place-items:center;border:1px solid var(--border);flex-shrink:0;" id="method-preview-bg">
                <span class="material-symbols-outlined icon-sm" id="method-selected-icon-preview" style="color:var(--text)">account_balance_wallet</span>
              </div>
              <div style="flex:1;position:relative;">
                <span class="material-symbols-outlined icon-sm" style="position:absolute;left:8px;top:50%;transform:translateY(-50%);color:var(--text-muted);pointer-events:none;font-size:16px;">search</span>
                <input type="text" id="method-icon-search" class="input" placeholder="Cari ikon (cth: wallet, cash, bank)..." style="width:100%;min-height:36px;padding:4px 8px 4px 32px;font-size:var(--text-sm);" autocomplete="off">
              </div>
            </div>
            <div id="method-icon-grid" style="display:grid;grid-template-columns:repeat(auto-fill, minmax(36px,1fr));gap:4px;max-height:130px;overflow-y:auto;padding:2px;">
            </div>
          </div>
        </div>
        <div class="modal__actions" style="margin-top:var(--space-md)">
          <button type="button" class="btn btn--secondary" data-method-close>Batal</button>
          <button type="submit" class="btn btn--primary">Simpan Metode</button>
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
  <script src="../assets/js/data.js?v=<?= time() ?>"></script>
  <script src="../assets/js/api.js?v=<?= time() ?>"></script>
  <script src="../assets/js/script.js?v=<?= time() ?>"></script>
  <script>
    document.addEventListener("DOMContentLoaded", function () {
      var D = FinanceTrackData;
      var U = FinanceTrack.Utils;
      var F = FinanceTrack.Feedback;
      var esc = U.escapeHtml;

      var currentTypeFilter = "all";
      var currentSearchQuery = "";

      function getEffectiveType(m) {
        if (!m) return 'other';
        var t = String(m.tipe || m.type || '').toLowerCase().trim();
        if (t === 'ewallet' || t === 'e-wallet' || t === 'dompet') return 'ewallet';
        if (t === 'bank' || t === 'rekening') return 'bank';
        if (t === 'cash' || t === 'tunai') return 'cash';
        return 'other';
      }

      /* --- Update Statistik 4 Mini Cards --- */
      function updateStats() {
        var methods = D.paymentMethods || [];
        var total = methods.length;
        var ewallet = methods.filter(function(m) { return getEffectiveType(m) === "ewallet"; }).length;
        var bank = methods.filter(function(m) { return getEffectiveType(m) === "bank"; }).length;
        var cash = methods.filter(function(m) { return getEffectiveType(m) === "cash"; }).length;
        var other = methods.filter(function(m) { return getEffectiveType(m) === "other"; }).length;

        document.getElementById("stat-total-method").textContent = total;
        document.getElementById("stat-ewallet-method").textContent = ewallet;
        document.getElementById("stat-bank-method").textContent = bank;
        document.getElementById("stat-cash-method").textContent = cash;

        document.getElementById("pill-all-count").textContent = total;
        document.getElementById("pill-ewallet-count").textContent = ewallet;
        document.getElementById("pill-bank-count").textContent = bank;
        document.getElementById("pill-cash-count").textContent = cash;
        document.getElementById("pill-other-count").textContent = other;
      }

      /* --- Label Tipe Helper --- */
      function getTypeBadge(effective) {
        if (effective === "ewallet") {
          return '<span class="badge" style="background:rgba(139, 92, 246, 0.14);color:#7c3aed;font-weight:600">E-Wallet</span>';
        } else if (effective === "bank") {
          return '<span class="badge" style="background:rgba(16, 185, 129, 0.14);color:#059669;font-weight:600">Rekening Bank</span>';
        } else if (effective === "cash") {
          return '<span class="badge" style="background:rgba(245, 158, 11, 0.14);color:#d97706;font-weight:600">Kas Tunai</span>';
        } else {
          return '<span class="badge badge--muted">Lainnya</span>';
        }
      }

      /* --- Render Tabel Metode Pembayaran --- */
      function renderMethodTable() {
        var methods = D.paymentMethods || [];
        var list = methods.filter(function(m) {
          var effective = getEffectiveType(m);
          if (currentTypeFilter !== "all" && effective !== currentTypeFilter) return false;
          if (currentSearchQuery && !m.nama.toLowerCase().includes(currentSearchQuery) && !m.id.toLowerCase().includes(currentSearchQuery)) return false;
          return true;
        });

        var container = document.getElementById("method-table-container");
        if (list.length === 0) {
          container.innerHTML = 
            '<div style="text-align:center;padding:var(--space-2xl) var(--space-md);color:var(--text-secondary)">' +
              '<span class="material-symbols-outlined" style="font-size:48px;opacity:0.4;margin-bottom:var(--space-xs)">search_off</span>' +
              '<p style="font-weight:600;font-size:var(--text-md);margin-bottom:4px">Tidak ada metode pembayaran yang cocok</p>' +
              '<p style="font-size:var(--text-xs);margin:0">Coba ubah kata kunci pencarian atau ganti filter jenis.</p>' +
            '</div>';
          return;
        }

        container.innerHTML =
          '<div class="table-wrap">' +
          '<table class="table">' +
            '<caption class="visually-hidden">Daftar lengkap metode pembayaran transaksi</caption>' +
            '<thead><tr><th scope="col">Metode Pembayaran</th><th scope="col">Jenis / Tipe</th><th scope="col">ID Sistem</th><th scope="col">Transaksi Terkait</th><th scope="col" style="text-align:right">Aksi</th></tr></thead>' +
            '<tbody>' +
              list.map(function (m) {
                var txCount = (D.transactions || []).filter(function(t) { return t.metode === m.id; }).length;
                var effective = getEffectiveType(m);
                var typeLabel = effective === "ewallet" ? "Dompet Digital (E-Wallet)" : effective === "bank" ? "Rekening Bank" : effective === "cash" ? "Kas Tunai" : "Lainnya";
                return '<tr>' +
                  '<td><div class="cell-identity" style="cursor:pointer" data-edit-method="'+esc(m.id)+'" title="Klik untuk mengedit metode '+esc(m.nama)+'">' +
                    '<span class="cell-icon" aria-hidden="true" style="background-color: '+esc(m.warna || '#0284c7')+'20; color: '+esc(m.warna || '#0284c7')+'">' +
                      '<span class="material-symbols-outlined icon-sm">' + esc(m.icon || 'account_balance_wallet') + '</span></span>' +
                    '<div class="cell-identity__text">' +
                      '<p class="cell-identity__title" style="margin:0;font-weight:600">' + esc(m.nama) + '</p>' +
                      '<p class="cell-identity__sub" style="margin:0;font-size:11px;color:var(--text-secondary)">' + esc(typeLabel) + '</p>' +
                    '</div></div></td>' +
                  '<td>' + getTypeBadge(effective) + '</td>' +
                  '<td><code>' + esc(m.id) + '</code></td>' +
                  '<td><span class="badge ' + (txCount > 0 ? 'badge--primary' : 'badge--muted') + '">' + txCount + ' mutasi</span></td>' +
                  '<td style="text-align:right;white-space:nowrap">' +
                    '<button type="button" class="btn btn--icon btn--ghost" data-edit-method="'+esc(m.id)+'" aria-label="Edit Metode" title="Edit"><span class="material-symbols-outlined icon-sm">edit</span></button>' +
                    '<button type="button" class="btn btn--icon btn--ghost text-danger" data-delete-method="'+esc(m.id)+'" aria-label="Hapus Metode" title="Hapus"><span class="material-symbols-outlined icon-sm">delete</span></button>' +
                  '</td>' +
                '</tr>';
              }).join("") +
            '</tbody>' +
          '</table></div>';
      }

      updateStats();
      renderMethodTable();

      /* Interaktivitas Kartu Mini Stat Metode */
      document.querySelectorAll("[data-method-stat]").forEach(function(card) {
        card.addEventListener("click", function() {
          var target = this.getAttribute("data-method-stat");
          var pill = document.querySelector('[data-method-filter="' + target + '"]');
          if (pill) {
            pill.click();
            var tableSec = document.querySelector('[aria-labelledby="daftar-metode-heading"]');
            if (tableSec) tableSec.scrollIntoView({ behavior: "smooth", block: "start" });
          }
        });
      });

      /* --- Filter & Search Handlers --- */
      var searchInput = document.getElementById("method-search");
      var searchClearBtn = document.getElementById("method-search-clear");

      function updateSearchClearVisibility() {
        if (!searchClearBtn) return;
        searchClearBtn.style.display = searchInput && searchInput.value ? "block" : "none";
      }

      if (searchClearBtn) {
        searchClearBtn.addEventListener("click", function() {
          if (searchInput) searchInput.value = "";
          currentSearchQuery = "";
          updateSearchClearVisibility();
          renderMethodTable();
          if (searchInput) searchInput.focus();
        });
      }

      if (searchInput) {
        searchInput.addEventListener("input", function(e) {
          currentSearchQuery = e.target.value.toLowerCase().trim();
          updateSearchClearVisibility();
          // Jika pengguna mulai mengetik pencarian spesifik, otomatis arahkan filter ke "all" agar hasil tidak terblokir
          if (currentSearchQuery && currentTypeFilter !== "all") {
            document.querySelectorAll("[data-method-filter]").forEach(function(p) { p.classList.remove("is-active"); });
            var allPill = document.querySelector('[data-method-filter="all"]');
            if (allPill) allPill.classList.add("is-active");
            currentTypeFilter = "all";
          }
          renderMethodTable();
        });
      }

      document.querySelectorAll("[data-method-filter]").forEach(function(pill) {
        pill.addEventListener("click", function() {
          document.querySelectorAll("[data-method-filter]").forEach(function(p) { p.classList.remove("is-active"); });
          this.classList.add("is-active");
          currentTypeFilter = this.getAttribute("data-method-filter");
          // Bersihkan pencarian saat berganti kategori filter agar pengguna melihat semua item di filter tersebut
          if (searchInput && searchInput.value) {
            searchInput.value = "";
            currentSearchQuery = "";
            updateSearchClearVisibility();
          }
          renderMethodTable();
        });
      });

      /* --- Icon Picker & Modal Method Setup --- */
      var methodModal = document.getElementById("modal-method");
      var methodForm = document.getElementById("method-form");
      var METHOD_ICONS = [
        "account_balance_wallet", "payments", "savings", "credit_card", "qr_code_2", 
        "account_balance", "wallet", "attach_money", "currency_exchange", "paid", 
        "receipt_long", "storefront", "shopping_bag", "contactless", "category",
        "local_atm", "monetization_on", "credit_score", "point_of_sale", "price_check"
      ];
      
      var mGrid = document.getElementById("method-icon-grid");
      var mSearch = document.getElementById("method-icon-search");
      var mInput = document.getElementById("method-icon");
      var mPreview = document.getElementById("method-selected-icon-preview");
      var mBg = document.getElementById("method-preview-bg");
      var mColorInput = document.getElementById("method-color");

      function renderMethodIconPicker(filter) {
        if (!mGrid) return;
        mGrid.innerHTML = "";
        var search = (filter || "").toLowerCase();
        METHOD_ICONS.forEach(function(icon) {
          if (icon.indexOf(search) === -1) return;
          var btn = document.createElement("button");
          btn.type = "button";
          var isActive = (mInput.value === icon);
          btn.innerHTML = '<span class="material-symbols-outlined icon-sm" style="pointer-events:none">' + icon + '</span>';
          btn.style.cssText = 'width:36px;height:36px;border-radius:var(--radius-sm);border:1px solid transparent;background:transparent;cursor:pointer;display:grid;place-items:center;color:var(--text-secondary);transition:all 0.2s;';
          if (isActive) {
            btn.style.background = mColorInput.value + "20";
            btn.style.color = mColorInput.value;
            btn.style.borderColor = mColorInput.value;
          }
          btn.title = icon;
          btn.onclick = function() {
            mInput.value = icon;
            updateMethodIconPreview();
          };
          mGrid.appendChild(btn);
        });
      }

      function updateMethodIconPreview() {
        if (!mPreview) return;
        mPreview.textContent = mInput.value;
        mPreview.style.color = mColorInput.value;
        mBg.style.background = mColorInput.value + "20";
        renderMethodIconPicker(mSearch ? mSearch.value : "");
      }

      if (mSearch) mSearch.addEventListener("input", function(e) { renderMethodIconPicker(e.target.value); });
      if (mColorInput) mColorInput.addEventListener("input", updateMethodIconPreview);

      function openMethodModal(id) {
        methodForm.reset();
        var idContainer = document.getElementById("method-id-display-container");
        var idDisplay = document.getElementById("method-id-display");
        if (id) {
          var method = (D.paymentMethods || []).find(function(m) { return String(m.id) === String(id); });
          if (method) {
            document.getElementById("method-modal-title").textContent = "Edit Metode Pembayaran";
            document.getElementById("method-id").value = method.id;
            if (idContainer && idDisplay) {
              idContainer.style.display = "block";
              idDisplay.value = method.id;
            }
            document.getElementById("method-name").value = method.nama || method.name || "";
            document.getElementById("method-type").value = getEffectiveType(method);
            document.getElementById("method-icon").value = method.icon || "account_balance_wallet";
            document.getElementById("method-color").value = method.warna || method.color || "#0284c7";
          }
        } else {
          document.getElementById("method-modal-title").textContent = "Tambah Metode Pembayaran Baru";
          document.getElementById("method-id").value = "";
          if (idContainer) idContainer.style.display = "none";
          document.getElementById("method-icon").value = "account_balance_wallet";
          document.getElementById("method-color").value = "#0284c7";
          if (mSearch) mSearch.value = "";
        }
        updateMethodIconPreview();
        methodModal.classList.add("is-open");
      }

      function closeMethodModal() {
        methodModal.classList.remove("is-open");
      }

      if (methodModal) {
        methodModal.addEventListener("click", function(e) {
          if (e.target === methodModal) closeMethodModal();
        });
      }

      document.querySelector("[data-add-method]").addEventListener("click", function() { openMethodModal(); });
      document.querySelector("[data-method-close]").addEventListener("click", function() { closeMethodModal(); });

      /* --- Action Listeners (Edit & Delete) --- */
      document.getElementById("method-table-container").addEventListener("click", function(e) {
        var btnEdit = e.target.closest("[data-edit-method]");
        if (btnEdit) {
          openMethodModal(btnEdit.getAttribute("data-edit-method"));
        }
        var btnDelete = e.target.closest("[data-delete-method]");
        if (btnDelete) {
          var id = btnDelete.getAttribute("data-delete-method");
          var mToDelete = (D.paymentMethods || []).find(function(m) { return String(m.id) === String(id); });
          var nameText = mToDelete ? mToDelete.nama : id;
          var txCount = (D.transactions || []).filter(function(t) { return t.metode === id; }).length;

          if (txCount > 0) {
            F.showToast("Tidak Bisa Dihapus", "Metode \"" + nameText + "\" sedang digunakan pada " + txCount + " transaksi aktif.", "error");
            return;
          }

          F.openModal({
            title: "Hapus Metode Pembayaran?",
            message: "Metode \"" + nameText + "\" akan dihapus secara permanen dari daftar opsi pembayaran Anda.",
            confirmText: "Hapus Metode",
            onConfirm: function() {
              var formData = new FormData();
              formData.append("id", id);
              fetch("../api/payment_methods/delete.php", {
                method: "POST",
                body: formData
              })
              .then(function(res) { return res.json(); })
              .then(function(res) {
                if (res.success) {
                  F.showToast("Berhasil", res.message, "check_circle");
                  var idx = D.paymentMethods.findIndex(function(m) { return String(m.id) === String(id); });
                  if (idx > -1) D.paymentMethods.splice(idx, 1);
                  updateStats();
                  renderMethodTable();
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

      /* --- Submit Form Metode Pembayaran --- */
      methodForm.addEventListener("submit", function(e) {
        e.preventDefault();
        var id = document.getElementById("method-id").value;
        var name = document.getElementById("method-name").value.trim();
        var type = document.getElementById("method-type").value;
        var icon = document.getElementById("method-icon").value;
        var color = document.getElementById("method-color").value;

        if (!name) {
          F.showToast("Data Kurang", "Nama metode pembayaran wajib diisi.", "error");
          return;
        }

        var url = id ? "../api/payment_methods/update.php" : "../api/payment_methods/create.php";
        var formData = new FormData();
        if (id) formData.append("id", id);
        formData.append("name", name);
        formData.append("type", type);
        formData.append("icon", icon);
        formData.append("color", color);

        var btn = methodForm.querySelector("button[type=submit]");
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
              if (typeof D.updatePaymentMethod === "function") {
                D.updatePaymentMethod(res.data);
              } else {
                var idx = (D.paymentMethods || []).findIndex(function(m) { return String(m.id) === String(id); });
                if (idx > -1) D.paymentMethods[idx] = Object.assign({}, D.paymentMethods[idx], res.data);
              }
              if (window.__FT_BOOTSTRAP__ && Array.isArray(window.__FT_BOOTSTRAP__.paymentMethods)) {
                var bIdx = window.__FT_BOOTSTRAP__.paymentMethods.findIndex(function(m) { return String(m.id) === String(id); });
                if (bIdx > -1) window.__FT_BOOTSTRAP__.paymentMethods[bIdx] = Object.assign({}, window.__FT_BOOTSTRAP__.paymentMethods[bIdx], res.data);
              }
            } else {
              if (!Array.isArray(D.paymentMethods)) D.paymentMethods = [];
              D.paymentMethods.push(res.data);
              if (window.__FT_BOOTSTRAP__ && Array.isArray(window.__FT_BOOTSTRAP__.paymentMethods)) {
                window.__FT_BOOTSTRAP__.paymentMethods.push(res.data);
              }
            }
            closeMethodModal();
            updateStats();
            renderMethodTable();
          } else {
            F.showToast("Gagal", res.message, "error");
          }
        }).catch(function(err) {
          btn.disabled = false;
          btn.textContent = oldText;
          console.error(err);
          F.showToast("Error", "Gagal menghubungi server.", "error");
        });
      });
    });
  </script>
</body>
</html>
