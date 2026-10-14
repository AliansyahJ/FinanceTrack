<?php
/**
 * FinanceTrack — Login & Register Page
 */

require_once __DIR__ . '/auth/middleware.php';

// Jika sudah login, langsung alihkan ke dashboard
if (getCurrentUserId()) {
    header('Location: pages/dashboard.php');
    exit;
}

$error = $_SESSION['auth_error'] ?? null;
unset($_SESSION['auth_error']);
$success = $_SESSION['auth_success'] ?? null;
unset($_SESSION['auth_success']);
$isRegister = (isset($_GET['action']) && $_GET['action'] === 'register');
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Masuk · FinanceTrack</title>
  <link rel="icon" type="image/svg+xml" href="assets/img/logo/logo.svg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/responsive.css">
  <style>
    .auth-page {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: radial-gradient(circle at 50% 10%, rgba(37, 99, 235, 0.12), transparent 60%),
                  radial-gradient(circle at 90% 90%, rgba(13, 148, 136, 0.08), transparent 50%),
                  var(--bg, #f8fafc);
      padding: var(--space-xl) var(--space-md);
    }
    .auth-card {
      width: 100%;
      max-width: 440px;
      background: rgba(255, 255, 255, 0.82);
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
      border: 1px solid rgba(255, 255, 255, 0.65);
      border-radius: var(--radius-xl, 20px);
      box-shadow: 0 16px 40px -8px rgba(15, 23, 42, 0.08), 0 0 0 1px rgba(226, 232, 240, 0.5);
      padding: var(--space-2xl) var(--space-xl);
    }
    .auth-brand {
      display: flex;
      flex-direction: column;
      align-items: center;
      text-align: center;
      margin-bottom: var(--space-xl);
    }
    .auth-brand img {
      height: 38px;
      margin-bottom: var(--space-md);
    }
    .auth-tabs {
      display: flex;
      background: var(--surface-alt);
      padding: 4px;
      border-radius: var(--radius-lg);
      margin-bottom: var(--space-xl);
    }
    .auth-tab {
      flex: 1;
      padding: var(--space-xs) var(--space-sm);
      text-align: center;
      font-weight: 600;
      font-size: var(--text-sm);
      color: var(--text-secondary);
      border-radius: var(--radius-md);
      border: none;
      background: transparent;
      cursor: pointer;
      transition: all var(--transition-fast);
      text-decoration: none;
    }
    .auth-tab.is-active {
      background: var(--surface);
      color: var(--primary);
      box-shadow: var(--shadow-xs);
    }
    .demo-box {
      background: rgba(2, 132, 199, 0.06);
      border: 1px dashed var(--primary);
      border-radius: var(--radius-md);
      padding: var(--space-sm) var(--space-md);
      margin-bottom: var(--space-lg);
      font-size: var(--text-xs);
      color: var(--text-secondary);
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: var(--space-sm);
    }
  
    .spinner { animation: spin 1s linear infinite; }
    @keyframes spin { 100% { transform: rotate(360deg); } }
  </style>
</head>
<body class="auth-page">

  <div class="auth-card">
    <div class="auth-brand">
      <a href="index.php">
        <img src="assets/img/logo/logo.svg" alt="FinanceTrack" width="140" height="38">
      </a>
      <h1 class="page-title" style="font-size:var(--text-xl);margin:0 0 var(--space-xs)">
        <?= $isRegister ? 'Daftar Akun Baru' : 'Selamat Datang Kembali' ?>
      </h1>
      <p class="page-subtitle" style="font-size:var(--text-sm);margin:0">
        <?= $isRegister ? 'Kelola keuangan & uang saku mahasiswa dengan rapi' : 'Masuk untuk mengelola keuangan harianmu' ?>
      </p>
    </div>

    <!-- Tab Pilihan Masuk / Daftar -->
    <div class="auth-tabs" role="tablist">
      <a href="login.php" class="auth-tab <?= !$isRegister ? 'is-active' : '' ?>">Masuk</a>
      <a href="login.php?action=register" class="auth-tab <?= $isRegister ? 'is-active' : '' ?>">Daftar</a>
    </div>

    <?php if ($success): ?>
      <div class="toast toast--success is-visible" data-toast role="status" aria-live="polite">
        <span class="material-symbols-outlined toast__icon" data-toast-icon aria-hidden="true">check_circle</span>
        <div>
          <p class="toast__title" data-toast-title>Berhasil</p>
          <p class="toast__message" data-toast-message><?= htmlspecialchars($success) ?></p>
        </div>
        <button type="button" class="btn btn--icon btn--ghost" data-toast-close aria-label="Tutup notifikasi">
          <span class="material-symbols-outlined icon-sm">close</span>
        </button>
      </div>
    <?php endif; ?>

    <?php if ($error): ?>
      <script>
        document.addEventListener("DOMContentLoaded", function() {
          var toast = document.querySelector(".toast");
          if (toast) {
            toast.querySelector(".toast__title").textContent = "Gagal";
            toast.querySelector(".toast__message").textContent = <?= json_encode($error) ?>;
            toast.querySelector(".toast__icon").textContent = "error";
            toast.className = "toast toast--error is-visible";
            setTimeout(function() {
              toast.classList.remove("is-visible");
            }, 4000);
          }
        });
      </script>
    <?php endif; ?>

    <?php if (!$isRegister): ?>
      <!-- Demo Credentials Quick Fill -->
      <div class="demo-box">
        <div>
          <strong style="color:var(--text)">Akun Demo:</strong><br>
          demo@financetrack.test / Demo123!
        </div>
        <button type="button" class="btn btn--secondary btn--sm" id="btnFillDemo" style="padding:4px 8px;font-size:11px">
          Pakai Akun Demo
        </button>
      </div>

      <!-- Form Login -->
      <form action="auth/login.php" method="POST" class="stack stack--md" id="loginForm">
        <div class="field">
          <label class="field__label" for="loginEmail">Email <span class="field__required">*</span></label>
          <div class="input-group">
            <span class="material-symbols-outlined icon-sm" aria-hidden="true">mail</span>
            <input type="email" name="email" id="loginEmail" class="input" required autocomplete="email" placeholder="nama@email.com">
          </div>
        </div>

        <div class="field">
          <div class="row row--between" style="margin-bottom:var(--space-xs)">
            <label class="field__label" for="loginPass" style="margin:0">Kata Sandi <span class="field__required">*</span></label>
          </div>
          <div class="input-group">
            <span class="material-symbols-outlined icon-sm" aria-hidden="true">lock</span>
            <input type="password" name="password" id="loginPass" class="input" required autocomplete="current-password" placeholder="••••••••" style="padding-right: 2.5rem;">
            <button type="button" class="btn btn--ghost btn--icon" id="toggleLoginPass" aria-label="Lihat kata sandi" style="position:absolute; right:0.25rem; z-index:10; padding:0.25rem; margin:0; height:auto; min-height:auto; display:flex; align-items:center; justify-content:center;">
              <span class="material-symbols-outlined icon-sm">visibility</span>
            </button>
          </div>
        </div>

        <div class="row row--between" style="font-size:var(--text-xs)">
          <label style="display:inline-flex;align-items:center;gap:var(--space-xs);cursor:pointer">
            <input type="checkbox" name="remember" value="1" checked style="accent-color:var(--primary)">
            Ingat saya di perangkat ini
          </label>
        </div>

        <button type="submit" class="btn btn--primary btn--full" style="padding:var(--space-sm) var(--space-md);font-weight:600">
          <span class="material-symbols-outlined icon-sm" aria-hidden="true">login</span>
          Masuk ke FinanceTrack
        </button>
      </form>

    <?php else: ?>

      <!-- Form Register -->
      <form action="auth/register.php" method="POST" class="stack stack--md" id="registerForm">
        <div class="field">
          <label class="field__label" for="regName">Nama Lengkap <span class="field__required">*</span></label>
          <div class="input-group">
            <span class="material-symbols-outlined icon-sm" aria-hidden="true">person</span>
            <input type="text" name="name" id="regName" class="input" required autocomplete="name" placeholder="Fikri Juana">
          </div>
        </div>

        <div class="field">
          <label class="field__label" for="regEmail">Email <span class="field__required">*</span></label>
          <div class="input-group">
            <span class="material-symbols-outlined icon-sm" aria-hidden="true">mail</span>
            <input type="email" name="email" id="regEmail" class="input" required autocomplete="email" placeholder="fikri@kampus.ac.id">
          </div>
        </div>

        <div class="field">
          <label class="field__label" for="regPass">Kata Sandi (Min. 6 Karakter) <span class="field__required">*</span></label>
          <div class="input-group">
            <span class="material-symbols-outlined icon-sm" aria-hidden="true">lock</span>
            <input type="password" name="password" id="regPass" class="input" required minlength="6" placeholder="Minimal 6 karakter">
          </div>
        </div>

        <div class="field">
          <label class="field__label" for="regConfirm">Konfirmasi Kata Sandi <span class="field__required">*</span></label>
          <div class="input-group">
            <span class="material-symbols-outlined icon-sm" aria-hidden="true">lock_reset</span>
            <input type="password" name="confirm_password" id="regConfirm" class="input" required minlength="6" placeholder="Ulangi kata sandi">
          </div>
        </div>

        <button type="submit" class="btn btn--primary btn--full" style="padding:var(--space-sm) var(--space-md);font-weight:600">
          <span class="material-symbols-outlined icon-sm" aria-hidden="true">person_add</span>
          Daftar Sekarang
        </button>
      </form>
    <?php endif; ?>

    <div style="margin-top:var(--space-xl);text-align:center;font-size:var(--text-xs);color:var(--text-secondary)">
      <a href="index.php" style="color:var(--text-secondary);text-decoration:none;display:inline-flex;align-items:center;gap:4px">
        <span class="material-symbols-outlined icon-xs">arrow_back</span> Kembali ke Beranda
      </a>
    </div>
  </div>

  <script>
    document.addEventListener("DOMContentLoaded", function() {
      var btnFill = document.getElementById("btnFillDemo");
      if (btnFill) {
        btnFill.addEventListener("click", function() {
          var em = document.getElementById("loginEmail");
          var pw = document.getElementById("loginPass");
          if (em) em.value = "demo@financetrack.test";
          if (pw) pw.value = "Demo123!";
        });
      }

      var toggleBtn = document.getElementById("toggleLoginPass");
      if (toggleBtn) {
        toggleBtn.addEventListener("click", function() {
          var pw = document.getElementById("loginPass");
          if (pw) {
            var isPass = pw.type === "password";
            pw.type = isPass ? "text" : "password";
            var icon = toggleBtn.querySelector(".material-symbols-outlined");
            if (icon) icon.textContent = isPass ? "visibility_off" : "visibility";
          }
        });
      }
    
      // Pencegahan double-submit dan efek loading
      ['loginForm', 'registerForm'].forEach(function(id) {
        var form = document.getElementById(id);
        if (form) {
          form.addEventListener('submit', function() {
            var btn = form.querySelector('button[type="submit"]');
            if (btn) {
              btn.disabled = true;
              btn.style.opacity = '0.7';
              btn.style.cursor = 'wait';
              btn.innerHTML = '<span class="material-symbols-outlined icon-sm spinner" aria-hidden="true">sync</span> Memproses...';
            }
          });
        }
      });

      // Custom HTML5 Validation Toast (Standalone)
      document.addEventListener('invalid', function(e) {
        e.preventDefault();
        var toast = document.querySelector('.toast');
        if (toast) {
          toast.querySelector('.toast__title').textContent = 'Validasi Gagal';
          toast.querySelector('.toast__message').textContent = 'Harap isi kolom ini dengan benar.';
          toast.querySelector('.toast__icon').textContent = 'error';
          toast.className = 'toast toast--error is-visible';
          setTimeout(function() {
            toast.classList.remove('is-visible');
          }, 3000);
        }
        try { e.target.focus(); } catch(err) {}
      }, true);
      
      // Close toast button
      var toastClose = document.querySelector('[data-toast-close]');
      if (toastClose) {
        toastClose.addEventListener('click', function() {
          var toast = document.querySelector('.toast');
          if (toast) toast.classList.remove('is-visible');
        });
      }
    });
  </script>
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
</body>
</html>






