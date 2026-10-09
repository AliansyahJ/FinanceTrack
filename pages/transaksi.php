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
$summary = $bootstrapData['summary'] ?? ['saldo' => 0, 'pemasukan' => 0, 'pengeluaran' => 0, 'periode' => 'Oktober ' . date('Y')];
$netCash = $summary['pemasukan'] - $summary['pengeluaran'];
?>
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
  <meta name="description" content="Dashboard FinanceTrack — ringkasan keuangan mahasiswa: saldo, pemasukan, pengeluaran, dan anggaran.">
    <title>Semua Transaksi &mdash; FinanceTrack</title>
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
          <button type="button" class="nav-item is-active" data-submenu-trigger aria-expanded="true" aria-controls="submenu-transaksi"
                  style="width:100%">
            <span class="material-symbols-outlined" aria-hidden="true">receipt_long</span>Transaksi<span class="material-symbols-outlined icon-xs" aria-hidden="true" style="margin-left:auto">expand_more</span>
          </button>
          <ul class="nav-sublist" id="submenu-transaksi" style="display:block;">
            <li><a class="nav-subitem is-active" href="transaksi.php">Semua Transaksi</a></li>
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

    <div class="sidebar__footer">
      <div class="sidebar__user">
        <span class="sidebar__avatar" aria-hidden="true"><?= $userAvatar ?></span>
        <div>
          <p class="sidebar__user-name"><?= $userName ?></p>
          <p class="sidebar__user-role"><?= $userRole ?></p>
        </div>
      </div>
    </div>
  </aside>

  <div class="sidebar-backdrop" data-sidebar-close></div>

  <!-- Content area -->
  <div class="app">

    <!-- Desktop top bar (Stitch: pencarian, notifikasi, profil) -->
    <header class="app__topbar">
      <div class="row align-items-center" style="gap:var(--space-md);flex:0 1 450px">
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
      <div class="row" style="gap:var(--space-md); align-items: center;">
        <button type="button" class="btn btn--ghost btn--icon" aria-label="Notifikasi" style="position:relative;height:40px;width:40px;display:flex;align-items:center;justify-content:center;">
          <span class="material-symbols-outlined" aria-hidden="true">notifications</span>
          <span class="topbar__dot" aria-hidden="true"></span>
        </button>
        <div class="topbar__user-menu" data-user-menu style="position:relative;">
          <button type="button" class="sidebar__user" data-user-menu-trigger aria-expanded="false" aria-haspopup="true" aria-label="Menu pengguna" style="display:flex;align-items:center;border:1px solid var(--surface-container);cursor:pointer;background-color:var(--surface-container-lowest);font-family:inherit;text-align:left;border-radius:999px;padding:4px 12px 4px 4px;height:40px;box-sizing:border-box;margin:0;">
            <span class="sidebar__avatar" aria-hidden="true" data-user-avatar style="width:32px;height:32px;font-size:12px;"><?= $userAvatar ?></span>
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

    <!-- Mobile header -->
    <header class="app__header">
      <button type="button" class="sidebar-toggle" data-sidebar-toggle aria-label="Buka menu" aria-expanded="false" aria-controls="sidebar">
        <span class="material-symbols-outlined">menu</span>
      </button>
      <div class="app__header-title">
        <span class="app__header-brand">FinanceTrack</span>
        <span class="app__header-name">Dashboard</span>
      </div>
    </header>

    <main class="app__main">

      <div class="page-header">
        <div>
          <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="dashboard.php">Dashboard</a>
            <span class="material-symbols-outlined icon-xs" aria-hidden="true">chevron_right</span>
            <span aria-current="page">Semua Transaksi</span>
          </nav>
          <h1 class="page-title">Semua Transaksi</h1>
          <p class="page-subtitle">Daftar riwayat lengkap seluruh pemasukan dan pengeluaranmu dalam satu kendali.</p>
        </div>
        <div class="page-header__actions">
          <button type="button" class="btn btn--secondary" id="btnExport">
            <span class="material-symbols-outlined icon-sm" aria-hidden="true">file_download</span>
            Ekspor CSV
          </button>
          <a class="btn btn--primary" href="form-transaksi.php">
            <span class="material-symbols-outlined icon-sm" aria-hidden="true">add</span>
            Tambah Transaksi
          </a>
        </div>
      </div>

      <!-- Ringkasan -->
      <section class="grid grid--4" aria-label="Ringkasan transaksi">
        <article class="summary-card">
          <div class="summary-card__top">
            <p class="summary-card__label">Total Pemasukan</p>
            <span class="icon-box icon-box--success" aria-hidden="true">
              <span class="material-symbols-outlined icon-sm">trending_up</span>
            </span>
          </div>
          <div>
            <p class="summary-card__value text-income">+Rp <?= number_format($summary['pemasukan'], 0, ',', '.') ?></p>
            <p class="summary-card__meta">Saku &amp; Freelance</p>
          </div>
        </article>
        <article class="summary-card">
          <div class="summary-card__top">
            <p class="summary-card__label">Total Pengeluaran</p>
            <span class="icon-box icon-box--danger" aria-hidden="true">
              <span class="material-symbols-outlined icon-sm">trending_down</span>
            </span>
          </div>
          <div>
            <p class="summary-card__value text-expense">-Rp <?= number_format($summary['pengeluaran'], 0, ',', '.') ?></p>
            <p class="summary-card__meta">Terkendali</p>
          </div>
        </article>
        <article class="summary-card">
          <div class="summary-card__top">
            <p class="summary-card__label">Arus Kas Bersih</p>
            <span class="icon-box icon-box--primary" aria-hidden="true">
              <span class="material-symbols-outlined icon-sm">savings</span>
            </span>
          </div>
          <div>
            <p class="summary-card__value"><?= ($netCash >= 0 ? '+Rp ' : '-Rp ') . number_format(abs($netCash), 0, ',', '.') ?></p>
            <p class="summary-card__meta">Sisa tabungan</p>
          </div>
        </article>
        <article class="summary-card">
          <div class="summary-card__top">
            <p class="summary-card__label">Jumlah Transaksi</p>
            <span class="icon-box icon-box--muted" aria-hidden="true">
              <span class="material-symbols-outlined icon-sm">receipt_long</span>
            </span>
          </div>
          <div>
            <p class="summary-card__value" data-tx-total>0</p>
            <p class="summary-card__meta">Bulan <?= htmlspecialchars($summary['periode']) ?></p>
          </div>
        </article>
      </section>

      <!-- Filter -->
      <section class="card filter-bar" style="margin-top:var(--gutter)" aria-label="Pencarian dan filter">
        <div class="row row--wrap">
          <div class="input-group filter-bar__search">
            <span class="material-symbols-outlined" aria-hidden="true">search</span>
            <label class="visually-hidden" for="tx-search">Cari transaksi</label>
            <input type="search" class="input" id="tx-search" data-search
                   placeholder="Cari transaksi, deskripsi, atau kategori...">
            <button type="button" class="input-group__suffix" data-search-clear aria-label="Bersihkan pencarian">
              <span class="material-symbols-outlined icon-sm" aria-hidden="true">close</span>
            </button>
          </div>
          <button type="button" class="btn btn--ghost filter-bar__advanced" data-filter-advanced
                  aria-expanded="false" aria-controls="filter-advanced">
            <span class="material-symbols-outlined icon-sm" aria-hidden="true">tune</span>
            Filter Lengkap
          </button>
          <button type="button" class="btn btn--ghost" data-filter-reset>
            <span class="material-symbols-outlined icon-sm" aria-hidden="true">restart_alt</span>
            Reset
          </button>
        </div>

        <!-- Quick filter pills (Stitch) -->
        <div class="filter-bar__pills" role="group" aria-label="Filter cepat">
          <button type="button" class="filter-pill is-active" data-quick-filter data-type="all" data-cat="all">Semua</button>
          <button type="button" class="filter-pill" data-quick-filter data-type="expense" data-cat="all">Pengeluaran</button>
          <button type="button" class="filter-pill" data-quick-filter data-type="income" data-cat="all">Pemasukan</button>
          <button type="button" class="filter-pill" data-quick-filter data-type="all" data-cat="makanan">Makanan</button>
          <button type="button" class="filter-pill" data-quick-filter data-type="all" data-cat="all" data-search="kos">Kost</button>
          <button type="button" class="filter-pill" data-quick-filter data-type="all" data-cat="kuliah">Kuliah</button>
        </div>

        <div class="filter-bar__controls" id="filter-advanced">
          <div class="filter-bar__control">
            <label for="filter-type">Jenis</label>
            <select class="select" id="filter-type" data-filter-type>
              <option value="all">Semua Jenis</option>
              <option value="income">Pemasukan</option>
              <option value="expense">Pengeluaran</option>
            </select>
          </div>
          <div class="filter-bar__control">
            <label for="filter-category">Kategori</label>
            <select class="select" id="filter-category" data-filter-category>
              <option value="all">Semua Kategori</option>
            </select>
          </div>
          <div class="filter-bar__control">
            <label for="filter-period">Periode</label>
            <select class="select" id="filter-period" data-filter-period>
              <option value="all">Semua Periode</option>
              <option value="2024-10">Oktober 2024</option>
              <option value="2024-09">September 2024</option>
            </select>
          </div>
          <div class="filter-bar__control">
            <label for="filter-sort">Urutkan</label>
            <select class="select" id="filter-sort" data-filter-sort>
              <option value="newest">Terbaru</option>
              <option value="oldest">Terlama</option>
              <option value="highest">Nominal Tertinggi</option>
              <option value="lowest">Nominal Terendah</option>
            </select>
          </div>
        </div>
      </section>

      <!-- Table -->
      <section class="card" data-tx-card style="margin-top:var(--gutter)" aria-labelledby="tabel-transaksi-heading">
        <div class="card__header">
          <div>
            <h2 class="section-title" id="tabel-transaksi-heading">Riwayat Transaksi</h2>
            <p class="section-subtitle" data-tx-info>Memuat data—</p>
          </div>
        </div>

        <!-- Desktop: tabel -->
        <div class="table-wrap table--responsive">
          <table class="table">
            <caption class="visually-hidden">Daftar transaksi lengkap beserta aksi edit dan hapus</caption>
            <thead>
              <tr>
                <th scope="col" class="table__center" style="width:40px">
                  <label class="visually-hidden" for="select-all">Pilih semua</label>
                  <input type="checkbox" class="checkbox" id="select-all" data-select-all>
                </th>
                <th scope="col">Tanggal</th>
                <th scope="col">Deskripsi</th>
                <th scope="col">Kategori</th>
                <th scope="col">Metode Pembayaran</th>
                <th scope="col">Jenis</th>
                <th scope="col" class="table__num">Jumlah</th>
                <th scope="col" class="table__center" style="width:110px">Aksi</th>
              </tr>
            </thead>
            <tbody data-tx-tbody>
              <!-- diisi oleh script.js dari data.js -->
            </tbody>
          </table>
        </div>

        <!-- Mobile: card list -->
        <div class="tx-feed table--responsive-mobile" data-tx-feed>
          <!-- diisi oleh script -->
        </div>

        <!-- Empty state -->
        <div class="empty-state is-hidden" data-tx-empty>
          <div class="empty-state__icon" aria-hidden="true">
            <span class="material-symbols-outlined icon-lg">search_off</span>
          </div>
          <h3 class="empty-state__title">Transaksi tidak ditemukan</h3>
          <p class="empty-state__text">Coba ubah kata kunci atau reset filter yang aktif.</p>
        </div>

        <div class="card__footer">
          <div class="row row--between row--wrap">
            <div class="row">
              <span class="text-secondary" style="font-size:var(--text-sm)">Per halaman</span>
              <select class="select" data-per-page style="min-height:34px;padding:0.25rem 2rem 0.25rem 0.5rem;width:auto;font-size:var(--text-sm)">
                <option value="8">8</option>
                <option value="12">12</option>
                <option value="20">20</option>
              </select>
            </div>
            <nav class="pagination" data-pagination aria-label="Pagination"></nav>
          </div>
        </div>
      </section>

    </main>
  </div>

  <!-- Mobile bottom nav -->
  <nav class="bottom-nav" aria-label="Navigasi bawah">
    <ul class="bottom-nav__list">
      <li><a class="bottom-nav__item" href="dashboard.php">
        <span class="material-symbols-outlined" aria-hidden="true">space_dashboard</span> Beranda
      </a></li>
      <li><a class="bottom-nav__item is-active" href="transaksi.php" aria-current="page">
        <span class="material-symbols-outlined" aria-hidden="true">receipt_long</span> Transaksi
      </a></li>
      <li><a class="bottom-nav__item bottom-nav__item--fab" href="form-transaksi.php" aria-label="Tambah Transaksi">
        <span class="material-symbols-outlined" aria-hidden="true">add</span>
      </a></li>
      <li><a class="bottom-nav__item" href="anggaran.php">
        <span class="material-symbols-outlined" aria-hidden="true">account_balance_wallet</span> Anggaran
      </a></li>
      <li><a class="bottom-nav__item" href="laporan.php">
        <span class="material-symbols-outlined" aria-hidden="true">bar_chart</span> Laporan
      </a></li>
    </ul>
  </nav>

  <!-- Delete confirmation modal -->
  <div class="modal" data-modal role="dialog" aria-modal="true" aria-labelledby="modal-title">
    <div class="modal__dialog">
      <div class="modal__icon modal__icon--danger" aria-hidden="true">
        <span class="material-symbols-outlined">delete</span>
      </div>
      <h2 class="modal__title" id="modal-title" data-modal-title>Hapus transaksi?</h2>
      <p class="modal__text" data-modal-message>Transaksi ini akan dihapus dari daftar.</p>
      <div class="modal__actions">
        <button type="button" class="btn btn--secondary" data-modal-close>Batal</button>
        <button type="button" class="btn btn--danger" data-modal-confirm>Ya, hapus</button>
      </div>
    </div>
  </div>

  <!-- Toast -->
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
    /* Bangun baris tabel & filter kategori dari mock data. */
    document.addEventListener("DOMContentLoaded", function () {
      var D = FinanceTrackData;
      var U = FinanceTrack.Utils;
      var esc = U.escapeHtml;

      /* --- Opsi filter kategori --- */
      var catSel = document.querySelector("[data-filter-category]");
      D.categories.forEach(function (c) {
        var opt = document.createElement("option");
        opt.value = c.id;
        opt.textContent = c.nama;
        catSel.appendChild(opt);
      });

      /* --- Total transaksi --- */
      var totalEl = document.querySelector("[data-tx-total]");
      if (totalEl) totalEl.textContent = D.transactions.length + " catatan";

      /* --- Baris tabel (desktop) --- */
      var tbody = document.querySelector("[data-tx-tbody]");
      /* --- Card list (mobile) --- */
      var feed = document.querySelector("[data-tx-feed]");

      D.transactions.forEach(function (t) {
        var cat = D.getCategory(t.kategoriId) || { nama: t.kategoriId || "-", icon: "receipt" };
        var method = D.getMethod(t.metode) || { nama: t.metode || "-" };
        var isIncome = t.tipe === "income";
        var signed = isIncome ? t.jumlah : -t.jumlah;
        var period = t.tanggal.slice(0, 7);
        var searchText = (t.deskripsi + " " + cat.nama + " " + method.nama + " " +
          (isIncome ? "pemasukan" : "pengeluaran")).toLowerCase();

        /* Baris tabel */
        var tr = document.createElement("tr");
        tr.dataset.id = t.id;
        tr.dataset.type = t.tipe;
        tr.dataset.kategori = t.kategoriId;
        tr.dataset.period = period;
        tr.dataset.tanggal = t.tanggal;
        tr.dataset.jumlah = t.jumlah;
        tr.dataset.descripsi = t.deskripsi;
        tr.dataset.search = searchText;
        tr.innerHTML =
          '<td class="table__center">' +
            '<label class="visually-hidden" for="cb-' + t.id + '">Pilih transaksi ' + t.id + '</label>' +
            '<input type="checkbox" class="checkbox row-checkbox" id="cb-' + t.id + '">' +
          '</td>' +
          '<td style="white-space:nowrap">' +
            '<div style="font-weight:500">' + esc(U.formatTanggal(t.tanggal)) + '</div>' +
            '<div class="text-secondary numeric" style="font-size:var(--text-xs)">' + esc(t.jam) + ' WIB</div>' +
          '</td>' +
          '<td>' +
            '<div class="cell-identity">' +
              '<span class="cell-icon ' + (isIncome ? "cell-icon--income" : "cell-icon--expense") + '" aria-hidden="true">' +
                '<span class="material-symbols-outlined icon-sm">' + esc(cat.icon) + '</span>' +
              '</span>' +
              '<div class="cell-identity__text">' +
                '<p class="cell-identity__title" style="max-width:260px">' + esc(t.deskripsi) + '</p>' +
              '</div>' +
            '</div>' +
          '</td>' +
          '<td><span class="badge badge--muted">' + esc(cat.nama) + '</span></td>' +
          '<td><span class="badge badge--primary">' + esc(method.nama) + '</span></td>' +
          '<td><span class="badge ' + (isIncome ? "badge--income" : "badge--expense") + '">' +
            (isIncome ? "Pemasukan" : "Pengeluaran") + '</span></td>' +
          '<td class="table__num"><strong class="' + (isIncome ? "text-income" : "text-expense") + '">' +
            esc(U.formatSigned(signed)) + '</strong></td>' +
          '<td>' +
            '<div class="table__actions">' +
              '<button type="button" class="btn btn--ghost btn--icon btn--sm" data-action="edit" ' +
                'aria-label="Edit transaksi ' + esc(t.deskripsi) + '" title="Edit">' +
                '<span class="material-symbols-outlined icon-sm" aria-hidden="true">edit</span>' +
              '</button>' +
              '<button type="button" class="btn btn--ghost btn--icon btn--sm" data-action="delete" ' +
                'aria-label="Hapus transaksi ' + esc(t.deskripsi) + '" title="Hapus">' +
                '<span class="material-symbols-outlined icon-sm" aria-hidden="true">delete</span>' +
              '</button>' +
            '</div>' +
          '</td>';
        tbody.appendChild(tr);

        /* Card mobile */
        var item = document.createElement("div");
        item.className = "tx-feed__item";
        item.dataset.feedId = t.id;
        item.innerHTML =
          '<div class="cell-identity">' +
            '<span class="cell-icon ' + (isIncome ? "cell-icon--income" : "cell-icon--expense") + '" aria-hidden="true">' +
              '<span class="material-symbols-outlined icon-sm">' + esc(cat.icon) + '</span>' +
            '</span>' +
            '<div class="cell-identity__text">' +
              '<p class="cell-identity__title">' + esc(t.deskripsi) + '</p>' +
              '<p class="cell-identity__sub">' + esc(cat.nama) + ' · ' + esc(method.nama) + ' · ' +
                esc(U.formatTanggal(t.tanggal)) + '</p>' +
            '</div>' +
          '</div>' +
          '<div style="display:flex;align-items:center;gap:var(--space-sm)">' +
            '<strong class="' + (isIncome ? "text-income" : "text-expense") + '" style="white-space:nowrap">' +
              esc(U.formatSigned(signed)) + '</strong>' +
            '<div class="tx-feed__actions">' +
              '<button type="button" class="btn btn--ghost btn--icon btn--sm" data-action="edit" ' +
                'aria-label="Edit transaksi ' + esc(t.deskripsi) + '">' +
                '<span class="material-symbols-outlined icon-sm" aria-hidden="true">edit</span>' +
              '</button>' +
              '<button type="button" class="btn btn--ghost btn--icon btn--sm" data-action="delete" ' +
                'aria-label="Hapus transaksi ' + esc(t.deskripsi) + '">' +
                '<span class="material-symbols-outlined icon-sm" aria-hidden="true">delete</span>' +
              '</button>' +
            '</div>' +
          '</div>';
        feed.appendChild(item);
      });

      /* Baris & kartu sudah masuk DOM -> beri tahu modul tabel agar
         search/filter/pagination/action ikut aktif. */
      FinanceTrack.TransactionTable.refreshRows();

      /* --- Quick filter pills + panel filter lanjutan (Stitch) --- */
      var TT = FinanceTrack.TransactionTable;
      var typeSel = document.querySelector("[data-filter-type]");
      var catSel = document.querySelector("[data-filter-category]");
      var searchInput = document.querySelector("[data-search]");
      var searchClear = document.querySelector("[data-search-clear]");
      var pills = Array.prototype.slice.call(document.querySelectorAll("[data-quick-filter]"));

      function syncPills() {
        var t = typeSel.value, c = catSel.value;
        var s = (searchInput.value || "").trim().toLowerCase();
        pills.forEach(function (p) {
          var match = (p.dataset.type || "all") === t &&
                      (p.dataset.cat || "all") === c &&
                      (p.dataset.search || "") === s;
          p.classList.toggle("is-active", match);
        });
      }

      pills.forEach(function (p) {
        p.addEventListener("click", function () {
          typeSel.value = p.dataset.type || "all";
          catSel.value = p.dataset.cat || "all";
          searchInput.value = p.dataset.search || "";
          if (searchClear) searchClear.classList.toggle("is-visible", searchInput.value.length > 0);
          syncPills();
          TT.applyFilters();
        });
      });

      [typeSel, catSel].forEach(function (el) { el.addEventListener("change", syncPills); });
      searchInput.addEventListener("input", syncPills);
      document.querySelector("[data-filter-reset]").addEventListener("click", function () {
        setTimeout(syncPills, 0);
      });

      /* Panel filter lanjutan (hanya tampil lewat tombol di mobile) */
      var advBtn = document.querySelector("[data-filter-advanced]");
      var advPanel = document.getElementById("filter-advanced");
      if (advBtn && advPanel) {
        advBtn.addEventListener("click", function () {
          var open = advPanel.classList.toggle("is-open");
          advBtn.setAttribute("aria-expanded", open ? "true" : "false");
        });
      }

      /* --- Export CSV (nyata: unduh file CSV dari backend) --- */
      var btnExp = document.getElementById("btnExport");
      if (btnExp) {
        btnExp.addEventListener("click", function () {
          window.location.href = "../api/reports/export_csv.php";
        });
      }
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

