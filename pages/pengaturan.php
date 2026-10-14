<?php
require_once __DIR__ . '/../auth/middleware.php';
require_once __DIR__ . '/../database/bootstrap.php';
requireAuth();
$currentUser = getCurrentUser();
$userName = htmlspecialchars($currentUser['name'] ?? 'Fikri Juana');
$userRole = htmlspecialchars($currentUser['role'] ?? 'Mahasiswa');
$userAvatar = htmlspecialchars($currentUser['avatar'] ?? 'FP');
$userEmail = htmlspecialchars($currentUser['email'] ?? 'demo@financetrack.test');
$userCreatedAt = htmlspecialchars($currentUser['created_at'] ?? '');
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
    <title>Pengaturan &mdash; FinanceTrack</title>
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
            <span class="material-symbols-outlined icon-sm" aria-hidden="true">tune</span>
            Preferensi Aplikasi
          </p>
          <h1 class="page-title">Pengaturan</h1>
          <p class="page-subtitle">Sesuaikan profil dan preferensi pencatatan agar lebih sesuai gayamu.</p>
        </div>
      </div>

      <div class="grid grid--sidebar-right">

        <div class="stack">

          <!-- Profil -->
          <section class="card card--pad" aria-labelledby="profil-heading">
            <h2 class="section-title" id="profil-heading">Profil Pengguna</h2>
            

            <form id="profileForm" novalidate>
              <div class="row row--wrap" style="gap:var(--space-xl); align-items:flex-start;">
                <!-- Kiri: Foto Profil -->
                <div style="display:flex; flex-direction:column; align-items:center; gap:var(--space-sm); width: 160px; flex-shrink: 0;">
                  <span class="sidebar__avatar" style="width:120px;height:120px;font-size:48px" aria-hidden="true"><?= $userAvatar ?></span>
                  <input type="file" id="avatarUpload" accept="image/png, image/jpeg, image/jpg" style="display:none">
                  <button type="button" class="btn btn--secondary btn--sm" data-avatar onclick="document.getElementById('avatarUpload').click()" style="width:100%; justify-content:center;">
                    <span class="material-symbols-outlined icon-sm" aria-hidden="true">photo_camera</span>
                    Ubah Foto
                  </button>
                  <button type="button" class="btn btn--ghost btn--sm text-danger" id="btnDeleteAvatar" style="width:100%; justify-content:center; padding:4px 8px; font-size:var(--text-xs);">
                    <span class="material-symbols-outlined icon-sm" aria-hidden="true">delete</span>
                    Hapus Foto
                  </button>
                  <p class="text-secondary" style="font-size:var(--text-xs); text-align:center; margin:0;">
                    Maks. 5MB (JPG/PNG)
                  </p>
                </div>

                <!-- Kanan: Form Edit -->
                <div style="flex: 1; min-width: 250px;" class="stack">
                  <div class="field">
                    <label class="field__label" for="set-name">Nama Lengkap</label>
                    <input type="text" class="input" id="set-name" name="nama" value="<?= $userName ?>" autocomplete="name">
                  </div>
                  <div class="field">
                    <label class="field__label" for="set-email">Email</label>
                    <input type="email" class="input" id="set-email" name="email" value="<?= $userEmail ?>" autocomplete="email">
                  </div>
                  <div class="field">
                    <label class="field__label" for="set-created">Tanggal Bergabung</label>
                    <input type="text" class="input" id="set-created" name="created" value="<?= $userCreatedAt ? date('d M Y', strtotime($userCreatedAt)) : 'Tidak diketahui' ?>" disabled style="background-color: var(--surface-alt); cursor: not-allowed; color: var(--text-secondary);">
                  </div>

                  <div class="row" style="gap:var(--space-sm);justify-content:flex-end;margin-top:var(--space-md)">
                    <button type="button" class="btn btn--secondary" data-reset-profile>Reset</button>
                    <button type="submit" class="btn btn--primary">
                      <span class="material-symbols-outlined icon-sm" aria-hidden="true">check</span>
                      Simpan Profil
                    </button>
                  </div>
                </div>
              </div>
            </form>
          </section>

          <!-- Preferensi -->
          <section class="card card--pad" aria-labelledby="preferensi-heading">
            <h2 class="section-title" id="preferensi-heading">Preferensi Tampilan</h2>
            <p class="page-subtitle" style="margin-bottom:var(--space-lg)">
              Pengaturan tampilan &amp; format pencatatan.
            </p>

            <div class="stack">
              <div class="row row--between row--wrap" style="gap:var(--space-md)">
                <div>
                  <p style="font-weight:600;margin:0">Format Tanggal</p>
                  <p class="text-secondary" style="font-size:var(--text-xs);margin:0">Dipakai di seluruh daftar transaksi</p>
                </div>
                <label class="visually-hidden" for="set-date-format">Format tanggal</label>
                <select class="select" id="set-date-format" style="width:auto;min-width:200px">
                  <option value="id-long">24 Okt 2024</option>
                  <option value="id-short">24/10/2024</option>
                  <option value="iso">2024-10-24</option>
                </select>
              </div>

              <div class="divider"></div>

              <div class="row row--between row--wrap" style="gap:var(--space-md)">
                <div>
                  <p style="font-weight:600;margin:0">Mata Uang</p>
                  <p class="text-secondary" style="font-size:var(--text-xs);margin:0">FinanceTrack memakai Rupiah lokal</p>
                </div>
                <span class="badge badge--income">IDR · Rp</span>
              </div>

              <div class="divider"></div>

              <div class="row row--between row--wrap" style="gap:var(--space-md)">
                <div>
                  <p style="font-weight:600;margin:0">Baris per Halaman</p>
                  <p class="text-secondary" style="font-size:var(--text-xs);margin:0">Berlaku pada halaman Semua Transaksi</p>
                </div>
                <label class="visually-hidden" for="set-per-page">Baris per halaman</label>
                <select class="select" id="set-per-page" style="width:auto">
                  <option value="8">8 baris</option>
                  <option value="12">12 baris</option>
                  <option value="20">20 baris</option>
                  <option value="50">50 baris</option>
                </select>
              </div>

              <div class="divider"></div>

              <div class="row row--between row--wrap" style="gap:var(--space-md)">
                <div>
                  <p style="font-weight:600;margin:0">Tampilkan Catatan</p>
                  <p class="text-secondary" style="font-size:var(--text-xs);margin:0">Tampilkan kolom catatan pada kartu transaksi</p>
                </div>
                <label class="switch">
                  <input type="checkbox" checked aria-label="Tampilkan catatan pada kartu transaksi">
                  <span class="switch__track"></span>
                </label>
              </div>

              <div class="divider"></div>

              <div class="row row--between row--wrap" style="gap:var(--space-md)">
                <div>
                  <p style="font-weight:600;margin:0">Peringatan Anggaran 80%</p>
                  <p class="text-secondary" style="font-size:var(--text-xs);margin:0">Ingatkan saat pemakaian mendekati batas</p>
                </div>
                <label class="switch">
                  <input type="checkbox" checked aria-label="Aktifkan peringatan anggaran 80 persen">
                  <span class="switch__track"></span>
                </label>
              </div>
            </div>
          </section>

          <!-- Pintasan Master Data -->
          <section class="card card--pad" aria-labelledby="master-data-heading">
            <h2 class="section-title" id="master-data-heading">Pintasan Master Data</h2>
            <p class="page-subtitle" style="margin-bottom:var(--space-md)">
              Kelola kategori transaksi dan metode pembayaran di halaman tersendiri.
            </p>
            <div class="grid grid--2" style="gap:var(--space-md)">
              <!-- Card Pintasan Kategori -->
              <div style="background:var(--surface-container-low);border:1px solid var(--surface-container);border-radius:var(--radius-lg);padding:var(--space-md);display:flex;flex-direction:column;justify-content:space-between;gap:var(--space-sm)">
                <div class="d-flex align-items-center" style="gap:var(--space-sm)">
                  <span class="icon-box icon-box--primary" style="width:40px;height:40px;border-radius:var(--radius-md);display:grid;place-items:center" aria-hidden="true">
                    <span class="material-symbols-outlined icon-sm">category</span>
                  </span>
                  <div>
                    <h3 style="font-size:var(--text-md);font-weight:600;margin:0">Kategori Transaksi</h3>
                    <p class="text-secondary" style="font-size:var(--text-xs);margin:0" id="shortcut-cat-count"><?= count($bootstrapData['categories'] ?? []) ?> kategori aktif</p>
                  </div>
                </div>
                <p class="text-secondary" style="font-size:var(--text-xs);margin:0">Atur pos pengeluaran &amp; pemasukan, sesuaikan ikon, dan warna tanda.</p>
                <a href="kategori.php" class="btn btn--secondary btn--sm" style="margin-top:var(--space-xs);justify-content:center;text-decoration:none">
                  <span>Kelola Kategori</span>
                  <span class="material-symbols-outlined icon-xs">arrow_forward</span>
                </a>
              </div>

              <!-- Card Pintasan Metode Pembayaran -->
              <div style="background:var(--surface-container-low);border:1px solid var(--surface-container);border-radius:var(--radius-lg);padding:var(--space-md);display:flex;flex-direction:column;justify-content:space-between;gap:var(--space-sm)">
                <div class="d-flex align-items-center" style="gap:var(--space-sm)">
                  <span class="icon-box icon-box--success" style="width:40px;height:40px;border-radius:var(--radius-md);display:grid;place-items:center" aria-hidden="true">
                    <span class="material-symbols-outlined icon-sm">payments</span>
                  </span>
                  <div>
                    <h3 style="font-size:var(--text-md);font-weight:600;margin:0">Metode Pembayaran</h3>
                    <p class="text-secondary" style="font-size:var(--text-xs);margin:0" id="shortcut-method-count"><?= count($bootstrapData['paymentMethods'] ?? []) ?> metode aktif</p>
                  </div>
                </div>
                <p class="text-secondary" style="font-size:var(--text-xs);margin:0">Atur akun dompet digital (GoPay, OVO, DANA), bank, atau tunai.</p>
                <a href="metode-pembayaran.php" class="btn btn--secondary btn--sm" style="margin-top:var(--space-xs);justify-content:center;text-decoration:none">
                  <span>Kelola Metode Bayar</span>
                  <span class="material-symbols-outlined icon-xs">arrow_forward</span>
                </a>
              </div>
            </div>
          </section>

          <!-- Data -->
          <section class="card card--pad" aria-labelledby="data-heading">
            <h2 class="section-title" id="data-heading">Kelola Data</h2>
            <p class="page-subtitle" style="margin-bottom:var(--space-lg)">
              Seluruh data FinanceTrack saat ini bersifat lokal (mock).
            </p>

            <div class="stack stack--sm">
              <button type="button" class="btn btn--secondary btn--block" data-export>
                <span class="material-symbols-outlined icon-sm" aria-hidden="true">download</span>
                Ekspor Semua Transaksi (CSV)
              </button>
              <button type="button" class="btn btn--secondary btn--block" data-reset-demo>
                <span class="material-symbols-outlined icon-sm" aria-hidden="true">restart_alt</span>
                Kembalikan ke Data Demo
              </button>
            </div>

            <div class="callout callout--warning" style="margin-top:var(--space-md)">
              <span class="material-symbols-outlined callout__icon icon-sm" aria-hidden="true">info</span>
              <div>
                <p class="callout__title">Penyimpanan Terpusat</p>
                <p class="callout__text">Semua data Anda tersimpan di server dengan aman dan dapat disinkronisasi.</p>
              </div>
            </div>
          </section>
        </div>

        <!-- Sidebar -->
        <div class="stack">

          <section class="card card--pad" aria-labelledby="metode-heading">
            <h2 class="section-title" id="metode-heading">Metode Pembayaran</h2>
            <p class="page-subtitle" style="margin-bottom:var(--space-md)">Dompet &amp; tunnel yang tersedia.</p>
            <div class="row row--wrap" id="method-list" style="gap:var(--space-xs)"></div>
            <div style="margin-top:var(--space-md)">
              <a href="metode-pembayaran.php" class="btn btn--secondary btn--sm" style="display:inline-flex;align-items:center;gap:6px;text-decoration:none;font-size:var(--text-xs)">
                <span class="material-symbols-outlined icon-xs">edit</span>
                <span>Kelola &amp; Edit Metode</span>
              </a>
            </div>
          </section>

          <section class="card card--pad" aria-labelledby="tentang-heading">
            <h2 class="section-title" id="tentang-heading">Tentang Aplikasi</h2>
            <div class="detail-list" style="margin-top:var(--space-sm)">
              <div class="detail-list__row">
                <span class="detail-list__key">Aplikasi</span>
                <span class="detail-list__val">FinanceTrack</span>
              </div>
              <div class="detail-list__row">
                <span class="detail-list__key">Versi</span>
                <span class="detail-list__val">Versi 1.0 (Stabil)</span>
              </div>
              <div class="detail-list__row">
                <span class="detail-list__key">Periode demo</span>
                <span class="detail-list__val numeric" id="about-period"></span>
              </div>
              <div class="detail-list__row">
                <span class="detail-list__key">Transaksi demo</span>
                <span class="detail-list__val numeric" id="about-count"></span>
              </div>
            </div>
            <p class="text-secondary" style="font-size:var(--text-xs);margin-top:var(--space-md)">
              FinanceTrack adalah aplikasi pelacak keuangan pribadi untuk mahasiswa.
              Semua data pada versi ini tersimpan di browser dan dapat dihapus sewaktu-waktu.
            </p>
          </section>

          <!-- Sesi & Akun -->
          <section class="card card--pad" aria-labelledby="akun-heading">
            <h2 class="section-title" id="akun-heading">Sesi &amp; Akun</h2>
            <div class="row" style="gap:var(--space-md);align-items:center;margin:var(--space-md) 0">
              <span class="sidebar__avatar" aria-hidden="true" data-user-avatar style="width:64px;height:64px;font-size:24px"><?= $userAvatar ?></span>
              <div style="flex:1;min-width:0">
                <div class="sidebar__user-name" data-user-name style="font-weight:600"><?= $userName ?></div>
                <div class="sidebar__user-role text-secondary" data-user-email style="font-size:var(--text-xs)"><?= $userEmail ?></div>
              </div>
            </div>
            <a href="../auth/logout.php" class="btn btn--secondary btn--block text-danger" style="display:flex;align-items:center;justify-content:center;gap:var(--space-xs);text-decoration:none">
              <span class="material-symbols-outlined icon-sm">logout</span>
              Keluar dari Akun
            </a>
          </section>

        </div>
      </div>

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
      <li><a class="bottom-nav__item" href="laporan.php">
        <span class="material-symbols-outlined" aria-hidden="true">bar_chart</span> Laporan
      </a></li>
    </ul>
  </nav>

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
  <script src="../assets/js/script.js?v=1791587905545"></script>
  <script>
    document.addEventListener("DOMContentLoaded", function () {
      var D = FinanceTrackData;
      var U = FinanceTrack.Utils;
      var esc = U.escapeHtml;
      var F = FinanceTrack.Feedback;

      /* --- Update Pintasan Kategori --- */
      var shortcutCatCount = document.getElementById("shortcut-cat-count");
      if (shortcutCatCount) {
        var exp = D.categories.filter(function(c) { return c.tipe === "expense"; }).length;
        var inc = D.categories.filter(function(c) { return c.tipe === "income"; }).length;
        shortcutCatCount.textContent = D.categories.length + " kategori (" + exp + " pengeluaran · " + inc + " pemasukan)";
      }

      /* --- Metode pembayaran --- */
      document.getElementById("method-list").innerHTML = D.paymentMethods
        .map(function (m) {
          return '<span class="badge badge--primary">' + esc(m.nama) + "</span>";
        })
        .join("");

      /* --- Tentang --- */
      document.getElementById("about-period").textContent = D.summary.periode;
      document.getElementById("about-count").textContent =
        D.transactions.length + " catatan";

      /* --- Simpan profil --- */
      document.getElementById("profileForm").addEventListener("submit", function (e) {
        e.preventDefault();

        var nameVal = document.getElementById('set-name').value.trim();
        var emailVal = document.getElementById('set-email').value.trim();
        if (!nameVal || !emailVal) {
            F.showToast('Data tidak valid', 'Nama lengkap dan email wajib diisi.', 'error');
            return;
        }

        var btn = this.querySelector('button[type="submit"]');
        var oldText = btn.innerHTML;
        btn.disabled = true;
        btn.textContent = "Menyimpan...";
        
        var formData = new FormData(this);
        
        fetch("../api/auth/update_profile.php", {
            method: "POST",
            body: formData
        })
        .then(function(res) { return res.json(); })
        .then(function(res) {
            btn.disabled = false;
            btn.innerHTML = oldText;
            if (res.success) {
                F.showToast("Profil disimpan", res.message, "check_circle");
                U.qsa("[data-user-name]").forEach(function(el) { el.textContent = res.data.nama; });
                U.qsa("[data-user-email]").forEach(function(el) { el.textContent = res.data.email; });
                if (res.data.avatar_initials) {
                    U.qsa(".sidebar__avatar").forEach(function(el) {
                        if (el.style.backgroundImage === 'none' || !el.style.backgroundImage || el.style.backgroundImage === 'initial') {
                            el.textContent = res.data.avatar_initials;
                        }
                    });
                }
            } else {
                F.showToast("Gagal", res.message, "error");
            }
        })
        .catch(function(err) {
            btn.disabled = false;
            btn.innerHTML = oldText;
            console.error(err);
            F.showToast("Error", "Gagal menghubungi server.", "error");
        });
      });

      document.querySelector("[data-reset-profile]").addEventListener("click", function () {
        document.getElementById("profileForm").reset();
        F.showToast("Profil dikembalikan", "Field profil dikosongkan kembali.", "undo");
      });

      var avatarUpload = document.getElementById("avatarUpload");
      if (avatarUpload) {
        avatarUpload.addEventListener("change", function (e) {
          var file = e.target.files[0];
          if (!file) return;

          if (file.size > 5242880) {
            F.showToast("Gagal", "Ukuran foto melebihi 5MB.", "error");
            e.target.value = '';
            return;
          }

          var formData = new FormData();
          formData.append("avatar", file);

          F.showToast("Mengunggah", "Sedang menyimpan foto...", "cloud_upload");

          fetch("../api/auth/upload_avatar.php", {
            method: "POST",
            body: formData
          })
          .then(function(res) { return res.json(); })
          .then(function(res) {
            if (res.success) {
              F.showToast("Berhasil", res.message, "check_circle");
              if (window.FinanceTrackAPI) {
                window.FinanceTrackAPI.getMe().then(function(meRes) {
                  if (meRes && meRes.success && meRes.data && meRes.data.user) {
                    var u = meRes.data.user;
                    U.qsa(".sidebar__avatar").forEach(function(el) {
                      if (u.avatar_url) {
                        el.style.backgroundImage = 'url("' + u.avatar_url + '")';
                        el.style.backgroundSize = 'cover';
                        el.style.backgroundPosition = 'center';
                        el.style.color = 'transparent';
                      }
                    });
                  }
                });
              }
            } else {
              F.showToast("Gagal", res.message || "Gagal menyimpan foto.", "error");
            }
          })
          .catch(function(err) {
            console.error(err);
            F.showToast("Error", "Terjadi kesalahan jaringan.", "error");
          });
          
          e.target.value = '';
        });
      }

      /* --- Hapus Foto Profil --- */
      var btnDelAvatar = document.getElementById("btnDeleteAvatar");
      if (btnDelAvatar) {
        btnDelAvatar.addEventListener("click", function() {
          F.openModal({
            title: "Hapus Foto Profil?",
            message: "Foto profil kustom akan dihapus dan avatar Anda akan kembali ke inisial nama bawaan.",
            confirmText: "Hapus Foto",
            onConfirm: function() {
              fetch("../api/auth/delete_avatar.php", {
                method: "POST"
              })
              .then(function(res) { return res.json(); })
              .then(function(res) {
                if (res.success) {
                  F.showToast("Foto Dihapus", res.message, "check_circle");
                  U.qsa(".sidebar__avatar").forEach(function(el) {
                    el.style.backgroundImage = 'none';
                    el.style.color = '';
                    if (res.avatar_initials) el.textContent = res.avatar_initials;
                  });
                } else {
                  F.showToast("Gagal", res.message, "error");
                }
              })
              .catch(function(err) {
                console.error(err);
                F.showToast("Error", "Gagal menghapus foto profil.", "error");
              });
            }
          });
        });
      }

      /* --- Ekspor nyata --- */
      var exportBtn = document.querySelector("[data-export]");
      if (exportBtn) {
        exportBtn.addEventListener("click", function () {
          window.location.href = "../api/reports/export_csv.php";
        });
      }

      /* --- Reset ke data demo --- */
      document.querySelector("[data-reset-demo]").addEventListener("click", function () {
        F.openModal({
          title: "Kembalikan ke data demo?",
          message:
            "Seluruh perubahan lokal akan dikembalikan ke data contoh bawaan. Tindakan ini tidak dapat dibatalkan.",
          confirmText: "Ya, kembalikan",
          onConfirm: function () {
            if (typeof D.resetAll === "function") {
              D.resetAll();
            }
            document.getElementById("about-count").textContent =
              D.transactions.length + " catatan";
            F.showToast(
              "Data dikembalikan",
              "FinanceTrack kembali ke data demo bawaan.",
              "restart_alt"
            );
          },
        });
      });
    });
  </script>


</body>
</html>

