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
    <title>Tambah Transaksi &mdash; FinanceTrack</title>
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
          <button type="button" class="nav-item is-active" data-submenu-trigger aria-expanded="true" aria-controls="submenu-transaksi"
                  style="width:100%">
            <span class="material-symbols-outlined" aria-hidden="true">receipt_long</span>Transaksi<span class="material-symbols-outlined icon-xs" aria-hidden="true" style="margin-left:auto">expand_more</span>
          </button>
          <ul class="nav-sublist" id="submenu-transaksi" style="display:block;">
            <li><a class="nav-subitem" href="transaksi.php">Semua Transaksi</a></li>
            <li><a class="nav-subitem is-active" href="form-transaksi.php">Tambah Transaksi</a></li>
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
            <span class="material-symbols-outlined icon-sm" aria-hidden="true">add_circle</span>
            Pencatatan Finansial
          </p>
          <h1 class="page-title">Tambah Transaksi Baru</h1>
          <p class="page-subtitle">Catat pemasukan atau pengeluaran baru untuk memperbarui laporan keuanganmu.</p>
        </div>
      </div>

      <div class="grid grid--sidebar-right">

        <!-- Form -->
        <section class="card card--pad" aria-labelledby="form-heading">
          <h2 class="visually-hidden" id="form-heading">Formulir transaksi</h2>

          <form data-tx-form novalidate>
            <div class="stack">

              <!-- Jenis transaksi -->
              <div class="field">
                <span class="field__label" id="jenis-label">Jenis Transaksi</span>
                <div class="segmented" role="group" aria-labelledby="jenis-label">
                  <button type="button" class="segmented__btn segmented__btn--expense is-active" data-type-btn="expense" aria-pressed="true">
                    <span class="material-symbols-outlined icon-sm" aria-hidden="true">north_east</span>
                    Pengeluaran
                  </button>
                  <button type="button" class="segmented__btn segmented__btn--income" data-type-btn="income" aria-pressed="false">
                    <span class="material-symbols-outlined icon-sm" aria-hidden="true">south_west</span>
                    Pemasukan
                  </button>
                </div>
              </div>

              <!-- Nominal -->
              <div class="field">
                <div class="row row--between">
                  <label class="field__label" for="field-amount">Nominal Transaksi <span class="field__required">*</span></label>
                  <span class="text-muted" style="font-size:var(--text-xs)">IDR (Rupiah)</span>
                </div>
                <div class="currency-input">
                  <span class="currency-input__prefix" aria-hidden="true">Rp</span>
                  <label class="visually-hidden" for="field-amount">Nominal transaksi dalam rupiah</label>
                  <input type="text" class="input numeric" id="field-amount" data-field-amount
                         inputmode="numeric" placeholder="0" autocomplete="off">
                </div>
                <div class="row row--wrap" style="margin-top:var(--space-sm)">
                  <span class="text-secondary" style="font-size:var(--text-xs)">Pilih cepat:</span>
                  <button type="button" class="pill" data-quick-amount="10000">+10 rb</button>
                  <button type="button" class="pill" data-quick-amount="20000">+20 rb</button>
                  <button type="button" class="pill" data-quick-amount="50000">+50 rb</button>
                  <button type="button" class="pill" data-quick-amount="100000">+100 rb</button>
                </div>
                <p class="field__error">Nominal wajib diisi dan harus lebih dari nol.</p>
              </div>

              <!-- Kategori & metode -->
              <div class="grid grid--2">
                <div class="field">
                  <label class="field__label" for="field-category">Kategori <span class="field__required">*</span></label>
                  <select class="select" id="field-category" data-field-category></select>
                  <p class="field__error">Kategori transaksi wajib dipilih.</p>
                </div>
                <div class="field">
                  <label class="field__label" for="field-method">Metode Pembayaran <span class="field__required">*</span></label>
                  <select class="select" id="field-method" data-field-method></select>
                  <p class="field__error">Metode pembayaran wajib dipilih.</p>
                </div>
              </div>

              <!-- Tanggal & waktu -->
              <div class="field">
                <span class="field__label">Tanggal &amp; Waktu <span class="field__required">*</span></span>
                <div class="grid grid--2">
                  <div class="input-group">
                    <span class="material-symbols-outlined icon-sm" aria-hidden="true">calendar_today</span>
                    <label class="visually-hidden" for="field-date">Tanggal transaksi</label>
                    <input type="date" class="input" id="field-date" data-field-date>
                  </div>
                  <div class="input-group">
                    <span class="material-symbols-outlined icon-sm" aria-hidden="true">schedule</span>
                    <label class="visually-hidden" for="field-time">Waktu transaksi</label>
                    <input type="time" class="input" id="field-time" data-field-time>
                  </div>
                </div>
                <p class="field__error">Tanggal transaksi wajib diisi.</p>
              </div>

              <!-- Deskripsi -->
              <div class="field">
                <label class="field__label" for="field-desc">Deskripsi <span class="field__required">*</span></label>
                <div class="input-group">
                  <span class="material-symbols-outlined icon-sm" aria-hidden="true">storefront</span>
                  <input type="text" class="input" id="field-desc" data-field-desc maxlength="100"
                         placeholder="Contoh: Makan siang warteg, Beli modul kuliah">
                </div>
                <p class="field__error">Deskripsi wajib diisi.</p>
              </div>

              <!-- Catatan -->
              <div class="field">
                <label class="field__label" for="field-notes">Catatan (Opsional)</label>
                <textarea class="textarea" id="field-notes" rows="2" maxlength="1000"
                          placeholder="Contoh: Patungan sama teman"></textarea>
              </div>

              <!-- Foto bukti / struk -->
              <div class="field">
                <span class="field__label">Foto Bukti / Struk (Opsional)</span>
                <label class="callout callout--muted upload-zone" for="field-photo" data-upload-zone>
                  <span class="icon-box icon-box--primary" aria-hidden="true">
                    <span class="material-symbols-outlined icon-sm">add_photo_alternate</span>
                  </span>
                  <span class="upload-zone__body">
                    <span class="callout__title" data-upload-name>Unggah Foto Struk / Nota</span>
                    <span class="callout__text" data-upload-hint>Format JPG, PNG, WEBP (Maks. 5MB)</span>
                  </span>
                  <span class="upload-zone__link">Pilih File</span>
                </label>
                <input type="file" class="visually-hidden" id="field-photo"
                       accept="image/png, image/jpeg, image/jpg, image/webp" data-upload-input>

                <!-- Preview thumbnail & hapus foto -->
                <div class="upload-preview is-hidden" id="uploadPreview" data-upload-preview>
                  <img src="" alt="Preview struk" id="uploadPreviewImg" class="upload-preview__img" data-upload-preview-img>
                  <div class="upload-preview__info">
                    <p class="upload-preview__name" data-upload-preview-name>struk.jpg</p>
                    <p class="upload-preview__size text-secondary" data-upload-preview-size>120 KB</p>
                  </div>
                  <button type="button" class="btn btn--ghost btn--icon btn--sm text-danger" id="btnRemoveReceipt" data-upload-remove aria-label="Hapus foto struk" title="Hapus foto">
                    <span class="material-symbols-outlined icon-sm">delete</span>
                  </button>
                </div>
              </div>

              <!-- Aksi -->
              <div class="form-actions">
                <button type="button" class="btn btn--secondary" data-cancel>Batal</button>
                <div class="form-actions__group">
                  <button type="button" class="btn btn--secondary text-primary" data-save-more>
                    Simpan &amp; Tambah Lagi
                  </button>
                  <button type="submit" class="btn btn--primary">
                    <span class="material-symbols-outlined icon-sm" aria-hidden="true">check_circle</span>
                    Simpan Transaksi
                  </button>
                </div>
              </div>

            </div>
          </form>
        </section>

        <!-- Preview & info -->
        <div class="stack">

          <!-- Live preview -->
          <section class="card card--pad" aria-labelledby="preview-heading">
            <div class="row row--between" style="margin-bottom:var(--space-md)">
              <h2 class="section-title" id="preview-heading">Ringkasan Entri</h2>
              <span class="badge badge--expense" data-preview-badge>Pengeluaran</span>
            </div>

            <div class="stack stack--sm" style="padding:var(--space-md);background:var(--surface-alt);border-radius:var(--radius-md)">
              <div class="row row--between">
                <span class="text-secondary" style="font-size:var(--text-sm)">Nominal dicatat</span>
                <strong class="numeric text-expense" data-preview-amount
                        style="font-size:var(--text-xl)">- Rp 0</strong>
              </div>
              <div class="detail-list" style="margin-top:var(--space-sm)">
                <div class="detail-list__row">
                  <span class="detail-list__key">Kategori</span>
                  <span class="detail-list__val" data-preview-category>-</span>
                </div>
                <div class="detail-list__row">
                  <span class="detail-list__key">Metode bayar</span>
                  <span class="detail-list__val" data-preview-method>-</span>
                </div>
                <div class="detail-list__row">
                  <span class="detail-list__key">Tanggal</span>
                  <span class="detail-list__val numeric" data-preview-date>-</span>
                </div>
                <div class="detail-list__row">
                  <span class="detail-list__key">Deskripsi</span>
                  <span class="detail-list__val" data-preview-desc>-</span>
                </div>
              </div>
            </div>

            <div class="callout callout--success" style="margin-top:var(--space-md)">
              <span class="material-symbols-outlined callout__icon icon-sm" aria-hidden="true">check_circle</span>
              <p class="callout__text">Tercatat otomatis pada saldo dan buku kas harianmu.</p>
            </div>
          </section>

          <!-- Batas anggaran kategori -->
          <section class="card card--pad" aria-labelledby="batasan-heading">
            <div class="row row--between" style="margin-bottom:var(--space-sm)">
              <h2 class="section-title" id="batasan-heading">Batas Anggaran</h2>
              <a class="btn btn--ghost btn--sm" href="anggaran.php">Kelola</a>
            </div>
            <div id="budget-hint">
              <!-- diisi oleh script -->
            </div>
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
      <li><a class="bottom-nav__item is-active" href="transaksi.php">
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

  <!-- Cancel confirmation modal -->
  <div class="modal" data-modal role="dialog" aria-modal="true" aria-labelledby="modal-title">
    <div class="modal__dialog">
      <div class="modal__icon modal__icon--danger" aria-hidden="true">
        <span class="material-symbols-outlined">warning</span>
      </div>
      <h2 class="modal__title" id="modal-title" data-modal-title>Batalkan input?</h2>
      <p class="modal__text" data-modal-message>Data yang sudah kamu isi akan hilang.</p>
      <div class="modal__actions">
        <button type="button" class="btn btn--secondary" data-modal-close>Tidak jadi</button>
        <button type="button" class="btn btn--danger" data-modal-confirm>Ya, batalkan</button>
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

      var editId = new URLSearchParams(window.location.search).get("id");

      /* --- Isi dropdown metode pembayaran --- */
      var methodSel = document.querySelector("[data-field-method]");
      if (methodSel && methodSel.options.length === 0 && D.paymentMethods) {
        D.paymentMethods.forEach(function (m) {
          var o = document.createElement("option");
          o.value = m.id;
          o.textContent = m.nama;
          methodSel.appendChild(o);
        });
      }

      /* --- Default tanggal = hari ini, waktu = sekarang (hanya mode tambah baru) --- */
      if (!editId) {
        var now = new Date();
        var pad = function (n) { return String(n).padStart(2, "0"); };
        var today = now.getFullYear() + "-" + pad(now.getMonth() + 1) + "-" + pad(now.getDate());
        var dateField = document.querySelector("[data-field-date]");
        var timeField = document.querySelector("[data-field-time]");
        if (dateField && !dateField.value) dateField.value = today;
        if (timeField && !timeField.value) timeField.value = pad(now.getHours()) + ":" + pad(now.getMinutes());
      }

      /* --- Unggah foto struk (validasi format & ukuran + preview thumbnail & hapus) --- */
      var fileInput = document.querySelector("[data-upload-input]");
      var uploadZone = document.querySelector("[data-upload-zone]");
      var previewBox = document.querySelector("[data-upload-preview]");
      var previewImg = document.querySelector("[data-upload-preview-img]");
      var previewName = document.querySelector("[data-upload-preview-name]");
      var previewSize = document.querySelector("[data-upload-preview-size]");
      var removeBtn = document.querySelector("[data-upload-remove]");

      var allowedTypes = ["image/jpeg", "image/png", "image/webp", "image/jpg"];

      var resetUpload = function () {
        if (fileInput) fileInput.value = "";
        var nameEl = document.querySelector("[data-upload-name]");
        var hintEl = document.querySelector("[data-upload-hint]");
        if (nameEl) nameEl.textContent = "Unggah Foto Struk / Nota";
        if (hintEl) hintEl.textContent = "Format JPG, PNG, WEBP (Maks. 5MB)";
        if (previewBox) previewBox.classList.add("is-hidden");
        if (previewImg) previewImg.src = "";
      };

      if (removeBtn) {
        removeBtn.addEventListener("click", function (e) {
          e.preventDefault();
          resetUpload();
          FinanceTrack.Feedback.showToast(
            "Foto dihapus",
            "Bukti foto struk telah dibatalkan.",
            "delete"
          );
        });
      }

      var handleFile = function (f) {
        if (!f) {
          resetUpload();
          return;
        }
        var ext = (f.name.split(".").pop() || "").toLowerCase();
        var isImage = allowedTypes.indexOf(f.type) !== -1 || ["jpg", "jpeg", "png", "webp"].indexOf(ext) !== -1;

        if (!isImage) {
          FinanceTrack.Feedback.showToast(
            "Format tidak didukung",
            'File "' + f.name + '" ditolak. Hanya format JPG, PNG, atau WEBP yang diizinkan (bukan PDF/dokumen lain).',
            "error"
          );
          resetUpload();
          return;
        }

        if (f.size > 5 * 1024 * 1024) {
          FinanceTrack.Feedback.showToast(
            "Ukuran melebihi batas",
            'File "' + f.name + '" berukuran ' + (f.size / (1024 * 1024)).toFixed(2) + " MB (maksimal 5 MB).",
            "error"
          );
          resetUpload();
          return;
        }

        var reader = new FileReader();
        reader.onload = function (ev) {
          if (previewImg) previewImg.src = ev.target.result;
          if (previewName) previewName.textContent = f.name;
          if (previewSize) previewSize.textContent = (f.size / 1024).toFixed(0) + " KB";
          if (previewBox) previewBox.classList.remove("is-hidden");
          var nameEl = document.querySelector("[data-upload-name]");
          var hintEl = document.querySelector("[data-upload-hint]");
          if (nameEl) nameEl.textContent = f.name;
          if (hintEl) hintEl.textContent = "Foto siap disimpan bersama transaksi";
        };
        reader.readAsDataURL(f);
      };

      if (fileInput) {
        fileInput.addEventListener("change", function () {
          var f = fileInput.files && fileInput.files[0];
          handleFile(f);
        });

        var txForm = document.querySelector("[data-tx-form]");
        if (txForm) txForm.addEventListener("reset", resetUpload);

        if (uploadZone) {
          uploadZone.addEventListener("keydown", function (e) {
            if (e.key === "Enter" || e.key === " ") {
              e.preventDefault();
              fileInput.click();
            }
          });
          ["dragenter", "dragover"].forEach(function (ev) {
            uploadZone.addEventListener(ev, function (e) {
              e.preventDefault();
              e.stopPropagation();
              uploadZone.classList.add("is-dragover");
            });
          });
          ["dragleave", "drop"].forEach(function (ev) {
            uploadZone.addEventListener(ev, function (e) {
              e.preventDefault();
              e.stopPropagation();
              uploadZone.classList.remove("is-dragover");
            });
          });
          uploadZone.addEventListener("drop", function (e) {
            if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length) {
              fileInput.files = e.dataTransfer.files;
              handleFile(e.dataTransfer.files[0]);
            }
          });
        }
      }

      /* --- Isi dropdown kategori sesuai mode aktif (default: expense) ---
         Pakai currentType milik modul agar tidak menimpa mode dari query
         string ?jenis=income yang sudah diatur script.js?v=1791587905545. */
      var T = FinanceTrack.TransactionForm;
      if (!editId) {
        T.updateCategoryOptions(T.currentType);
      }
      T.updatePreview();

      /* --- Batas anggaran untuk kategori terpilih --- */
      function renderBudgetHint() {
        var box = document.getElementById("budget-hint");
        if (!box) return;
        var catSel = document.querySelector("[data-field-category]");
        var catId = catSel ? catSel.value : "";
        var amountInput = document.querySelector("[data-field-amount]");
        var currentAmount = amountInput ? U.parseRupiah(amountInput.value) : 0;
        var isIncome = T.currentType === "income";

        if (isIncome) {
          box.innerHTML =
            '<p class="text-secondary" style="font-size:var(--text-sm)">Kategori pemasukan tidak memiliki batas anggaran pengeluaran.</p>';
          return;
        }

        var budget = D.budgets.find(function (b) { return b.kategoriId === catId; });

        if (!budget) {
          box.innerHTML =
            '<p class="text-secondary" style="font-size:var(--text-sm)">Belum ada batas anggaran ' +
            'untuk kategori ini. <a href="anggaran.php" style="color:var(--primary);font-weight:600">Tetapkan sekarang</a>.</p>';
          return;
        }

        var cat = D.getCategory(catId);
        var catName = cat ? cat.nama : catId;
        var totalWithNew = budget.terpakai + currentAmount;
        var pct = U.percent(totalWithNew, budget.batas);
        var sisa = budget.batas - totalWithNew;
        var barClass = pct >= 100 ? "progress__bar--danger"
                     : pct >= 80 ? "progress__bar--warning"
                     : "progress__bar--success";
        var textClass = sisa < 0 ? "text-expense" : pct >= 80 ? "text-warning" : "text-income";

        box.innerHTML =
          '<p class="text-secondary" style="font-size:var(--text-sm);margin:0 0 var(--space-sm)">' +
            'Kategori: <strong style="color:var(--text)">' + esc(catName) + '</strong>' +
          '</p>' +
          '<div class="row row--between" style="font-size:var(--text-xs);color:var(--text-secondary);margin-bottom:var(--space-xs)">' +
            '<span>Terpakai saat ini: ' + esc(U.formatRupiah(budget.terpakai)) + '</span>' +
            '<span style="color:var(--text);font-weight:600">Batas: ' + esc(U.formatRupiah(budget.batas)) + '</span>' +
          '</div>' +
          '<div class="progress"><div class="progress__bar ' + barClass + '" style="width:' + Math.min(pct, 100) + '%"></div></div>' +
          '<div class="row row--between" style="margin-top:var(--space-sm);font-size:var(--text-xs);color:var(--text-secondary)">' +
            '<span>' + (currentAmount > 0 ? "Sisa setelah transaksi" : "Sisa anggaran") + '</span>' +
            '<strong class="' + textClass + ' numeric">' + (sisa < 0 ? "Lebih " : "") + esc(U.formatRupiah(Math.abs(sisa))) + '</strong>' +
          '</div>' +
          (currentAmount > 0 && sisa < 0
            ? '<p class="field__error" style="display:block;margin-top:var(--space-xs);font-size:var(--text-xs)">Peringatan: Nominal ini melebihi sisa anggaran!</p>'
            : "");
      }

      window.renderBudgetHint = renderBudgetHint;
      renderBudgetHint();

      var catField = document.querySelector("[data-field-category]");
      if (catField) catField.addEventListener("change", renderBudgetHint);

      var amountField = document.querySelector("[data-field-amount]");
      if (amountField) amountField.addEventListener("input", renderBudgetHint);

      document.querySelectorAll("[data-quick-amount]").forEach(function (b) {
        b.addEventListener("click", function () {
          setTimeout(renderBudgetHint, 0);
        });
      });

      document.querySelectorAll("[data-type-btn]").forEach(function (b) {
        b.addEventListener("click", function () {
          setTimeout(renderBudgetHint, 0);
        });
      });

      /* --- Inisialisasi preview --- */
      FinanceTrack.TransactionForm.updatePreview();
    });
  </script>


</body>
</html>

