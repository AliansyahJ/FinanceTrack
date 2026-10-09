/* ==========================================================================
   FinanceTrack — Shared JavaScript (Milestone 2)
   --------------------------------------------------------------------------
   Semua interaksi dasar (sidebar, modal, toast, format angka, tabel, chart)
   dikumpulkan di satu file agar mudah dikembangkan pada Milestone 3.

   Struktur modul:
     1. Utils        — format Rupiah, tanggal, query helper
     2. Navigation   — sidebar toggle, submenu, active state, bottom nav
     3. UI Feedback  — modal konfirmasi, toast
     4. Table        — search, filter, sort, pagination (halaman Transaksi)
     5. Form         — format nominal, segmented type, preview (halaman Form)
     6. Budget       — status & progress bar (halaman Anggaran)
     7. Chart        — Chart.js inisialisasi (Dashboard & Laporan)
   ========================================================================== */

(function () {
  "use strict";

  /* =======================================================================
     1. UTILS
     ======================================================================= */
  const Utils = {
    /** 3450000 -> "Rp 3.450.000" */
    formatRupiah(value, withPrefix = true) {
      const n = Number(value) || 0;
      const formatted = n.toLocaleString("id-ID");
      return withPrefix ? "Rp " + formatted : formatted;
    },

    /** Versi ringkas untuk chart / badge: 2150000 -> "Rp 2,15 jt" */
    formatRupiahShort(value) {
      const n = Number(value) || 0;
      if (Math.abs(n) >= 1000000) {
        return "Rp " + (n / 1000000).toLocaleString("id-ID", {
          maximumFractionDigits: 2,
        }) + " jt";
      }
      if (Math.abs(n) >= 1000) {
        return "Rp " + Math.round(n / 1000) + " rb";
      }
      return "Rp " + n;
    },

    /** 3450000 -> "+Rp 3.450.000" / "-Rp 25.000" */
    formatSigned(value) {
      const n = Number(value) || 0;
      const sign = n > 0 ? "+" : n < 0 ? "-" : "";
      return sign + this.formatRupiah(Math.abs(n));
    },

    /** "2024-10-24" -> "24 Okt 2024" */
    formatTanggal(isoDate) {
      if (!isoDate) return "-";
      const bulan = [
        "Jan", "Feb", "Mar", "Apr", "Mei", "Jun",
        "Jul", "Agu", "Sep", "Okt", "Nov", "Des",
      ];
      const [y, m, d] = isoDate.split("-");
      return `${d} ${bulan[parseInt(m, 10) - 1]} ${y}`;
    },

    /** Parse input "25.000" -> 25000 */
    parseRupiah(str) {
      return parseInt(String(str).replace(/\D/g, ""), 10) || 0;
    },

    /** Format value input jadi ribuan: 25000 -> "25.000" */
    thousands(value) {
      return (Number(value) || 0).toLocaleString("id-ID");
    },

    /** Persentase aman (tidak NaN / Infinity) */
    percent(part, whole) {
      if (!whole) return 0;
      return Math.min(100, Math.round((part / whole) * 1000) / 10);
    },

    /** q / qsa helper */
    qs(selector, scope = document) {
      return scope.querySelector(selector);
    },
    qsa(selector, scope = document) {
      return Array.from(scope.querySelectorAll(selector));
    },

    /** Escaping teks sebelum masuk innerHTML (data mock tetap aman, tapi ini kebiasaan baik) */
    escapeHtml(str) {
      return String(str ?? "").replace(/[&<>"']/g, (c) => ({
        "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;",
      })[c]);
    },
  };

  /* =======================================================================
     2. NAVIGATION — sidebar, submenu, active state
     ======================================================================= */
  
  const TopbarSearch = {
    init() {
      const inputs = document.querySelectorAll('[data-topbar-search]');
      inputs.forEach(input => {
        input.addEventListener('keydown', (e) => {
          if (e.key === 'Enter') {
            e.preventDefault();
            const q = input.value.trim();
            if (q) {
              window.location.href = 'transaksi.php?q=' + encodeURIComponent(q);
            }
          }
        });
      });
    }
  };
  
  const Navigation = {
    init() {
      this.setupActiveState();
      this.setupSubmenu();
      this.setupSidebarToggle();
      this.setupBottomNav();
      this.setupUserDropdown();
    },

    setupSidebarToggle() {
      const toggle = Utils.qsa("[data-sidebar-toggle]");
      const close = Utils.qsa("[data-sidebar-close]");

      toggle.forEach((btn) => {
        btn.addEventListener("click", (e) => {
          e.preventDefault();
          const isMobile = window.innerWidth <= 1024;
          if (isMobile) {
            const isOpen = document.body.classList.toggle("sidebar-open");
            btn.setAttribute("aria-expanded", String(isOpen));
          } else {
            const isCollapsed = document.body.classList.toggle("sidebar-collapsed");
            btn.setAttribute("aria-expanded", String(!isCollapsed));
          }
        });
      });

      close.forEach((btn) => {
        btn.addEventListener("click", (e) => {
          e.preventDefault();
          const isMobile = window.innerWidth <= 1024;
          if (isMobile) {
            document.body.classList.remove("sidebar-open");
          } else {
            document.body.classList.add("sidebar-collapsed");
          }
          Utils.qsa("[data-sidebar-toggle]").forEach((t) =>
            t.setAttribute("aria-expanded", "false")
          );
        });
      });

      // Tutup sidebar saat klik backdrop
      const backdrop = Utils.qs(".sidebar-backdrop");
      if (backdrop) {
        backdrop.addEventListener("click", () => {
          document.body.classList.remove("sidebar-open");
          Utils.qsa("[data-sidebar-toggle]").forEach((t) =>
            t.setAttribute("aria-expanded", "false")
          );
        });
      }

      // Tutup sidebar saat tekan Escape
      document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") {
          document.body.classList.remove("sidebar-open");
          document.body.classList.remove("sidebar-collapsed");
          Utils.qsa("[data-sidebar-toggle]").forEach((t) =>
            t.setAttribute("aria-expanded", "false")
          );
          document
            .querySelectorAll(".modal.is-open")
            .forEach((m) => m.classList.remove("is-open"));
        }
      });
    },

    setupSubmenu() {
      // Grup "Transaksi" bisa dibuka/tutup; default terbuka bila salah satu
      // submenu-nya aktif.
      const trigger = Utils.qsa("[data-submenu-trigger]");
      trigger.forEach((btn) => {
        const sublist = document.getElementById(btn.getAttribute("aria-controls"));
        if (!sublist) return;

        // Buka otomatis bila submenu aktif (halaman Transaksi / Tambah Transaksi)
        if (sublist.querySelector(".is-active")) sublist.classList.add("is-open");

        btn.addEventListener("click", () => {
          const isOpen = sublist.classList.toggle("is-open");
          btn.setAttribute("aria-expanded", String(isOpen));
        });
      });
    },

    setupActiveState() {
      // Tentukan file aktif saat ini secara tepat dari pathname
      const currentPath = window.location.pathname.split("/").pop() || "index.php";
      const currentBase = currentPath.split("?")[0].split("#")[0].replace(/\.(html|php)$/, "");

      Utils.qsa(".nav-item, .nav-subitem, .bottom-nav__item").forEach((link) => {
        const href = link.getAttribute("href");
        if (!href) return;
        const linkFile = href.split("?")[0].split("#")[0].split("/").pop();
        const linkBase = linkFile.replace(/\.(html|php)$/, "");

        if (linkBase && currentBase && linkBase === currentBase) {
          link.classList.add("is-active");
          link.setAttribute("aria-current", "page");
        } else {
          link.classList.remove("is-active");
          link.removeAttribute("aria-current");
        }
      });
    },

    setupBottomNav() {
      // FAB "Tambah" tidak perlu status aktif.
      Utils.qsa(".bottom-nav__item--fab").forEach((el) => el.classList.remove("is-active"));
    },

    setupUserDropdown() {
      const menus = Utils.qsa("[data-user-menu]");
      menus.forEach((menuWrapper) => {
        const btn = menuWrapper.querySelector("[data-user-menu-trigger]");
        const dropdown = menuWrapper.querySelector("[data-user-menu-dropdown]");
        if (!btn || !dropdown) return;

        btn.addEventListener("click", (e) => {
          e.stopPropagation();
          const isOpen = dropdown.classList.toggle("is-open");
          btn.setAttribute("aria-expanded", String(isOpen));
        });
      });

      document.addEventListener("click", (e) => {
        Utils.qsa("[data-user-menu]").forEach((menuWrapper) => {
          const btn = menuWrapper.querySelector("[data-user-menu-trigger]");
          const dropdown = menuWrapper.querySelector("[data-user-menu-dropdown]");
          if (dropdown && !menuWrapper.contains(e.target)) {
            dropdown.classList.remove("is-open");
            if (btn) btn.setAttribute("aria-expanded", "false");
          }
        });
      });

      document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") {
          Utils.qsa("[data-user-menu]").forEach((menuWrapper) => {
            const btn = menuWrapper.querySelector("[data-user-menu-trigger]");
            const dropdown = menuWrapper.querySelector("[data-user-menu-dropdown]");
            if (dropdown && dropdown.classList.contains("is-open")) {
              dropdown.classList.remove("is-open");
              if (btn) {
                btn.setAttribute("aria-expanded", "false");
                btn.focus();
              }
            }
          });
        }
      });
    },
  };

  /* =======================================================================
     3. UI FEEDBACK — modal konfirmasi & toast
     ======================================================================= */
  const Feedback = {
    modal: null,
    toast: null,
    toastTimer: null,

    init() {
      this.modal = Utils.qs("[data-modal]");
      this.toast = Utils.qs("[data-toast]");

      if (this.modal) {
        // Tutup modal saat klik backdrop / tombol batal / tombol close.
        this.modal.addEventListener("click", (e) => {
          if (e.target === this.modal) this.closeModal();
        });
        Utils.qsa("[data-modal-close]", this.modal).forEach((btn) => {
          btn.addEventListener("click", () => this.closeModal());
        });
      }

      // Tampilkan toast flash dari navigasi (misal setelah simpan transaksi)
      try {
        const flash = sessionStorage.getItem("ft_toast");
        if (flash) {
          sessionStorage.removeItem("ft_toast");
          const data = JSON.parse(flash);
          setTimeout(() => {
            this.showToast(data.title, data.message || "", data.icon || "check_circle");
          }, 150);
        }
      } catch (err) {}
    },

    /**
     * Buka modal konfirmasi.
     * @param {object} options { title, message, confirmText, confirmClass, onConfirm }
     */
    openModal(options = {}) {
      if (!this.modal) return;
      const title = Utils.qs("[data-modal-title]", this.modal);
      const message = Utils.qs("[data-modal-message]", this.modal);
      const confirmBtn = Utils.qs("[data-modal-confirm]", this.modal);

      if (title) title.textContent = options.title || "Konfirmasi";
      if (message) message.textContent = options.message || "Anda yakin?";
      if (confirmBtn) {
        confirmBtn.textContent = options.confirmText || "Ya, lanjutkan";
        confirmBtn.className = "btn " + (options.confirmClass || "btn--danger");
        // Ganti listener lama dengan yang baru.
        confirmBtn.onclick = () => {
          if (typeof options.onConfirm === "function") options.onConfirm();
          this.closeModal();
        };
      }
      this.modal.classList.add("is-open");
      // Fokuskan tombol konfirmasi agar accessible via keyboard.
      if (confirmBtn) confirmBtn.focus();
    },

    closeModal() {
      if (this.modal) this.modal.classList.remove("is-open");
    },

    /**
     * Tampilkan toast.
     * @param {string} title  Judul toast
     * @param {string} message Pesan tambahan
     * @param {string} icon    Nama ikon Material Symbols
     */
    showToast(title, message = "", icon = "check_circle") {
      if (!this.toast) return;
      const t = Utils.qs("[data-toast-title]", this.toast);
      const m = Utils.qs("[data-toast-message]", this.toast);
      const i = Utils.qs("[data-toast-icon]", this.toast);

      if (t) t.textContent = title;
      if (m) m.textContent = message;
      if (i) i.textContent = icon;

      this.toast.classList.add("is-visible");
      clearTimeout(this.toastTimer);
      this.toastTimer = setTimeout(() => {
        this.toast.classList.remove("is-visible");
      }, 3200);
    },
  };

  /* =======================================================================
     4. TABLE — search, filter, sort, pagination
     ======================================================================= */
  const TransactionTable = {
    rows: [],
    feedItems: [],
    filtered: [],
    currentPage: 1,
    perPage: 8,
    _listenersBound: false,

    init() {
      const tbody = Utils.qs("[data-tx-tbody]");
      if (!tbody) return;

      this.readRows();

      // Read search param from URL
      const q = new URLSearchParams(window.location.search).get("q");
      if (q) {
        const searchInput = document.querySelector("[data-search]");
        if (searchInput) {
          searchInput.value = q;
        }
      }

      this.perPage = parseInt(
        Utils.qs("[data-per-page]")?.value || "8",
        10
      );

      // Halaman me-render baris lewat inline script yang jalan SETELAH
      // DOMContentLoaded, jadi binding hanya boleh dilakukan sekali.
      if (!this._listenersBound) {
        this._listenersBound = true;
        this.bindSearch();
        this.bindFilters();
        this.bindSort();
        this.bindSelectAll();
        this.bindRowActions();
        this.bindPerPage();
      }

      this.applyFilters();
    },

    /**
     * Baca ulang elemen dari DOM lalu terapkan filter saat ini.
     * `rows`     -> <tr data-id>       (tampilan tabel, desktop)
     * `feedItems`-> [data-feed-id]     (tampilan kartu, mobile)
     */
    readRows() {
      const tbody = Utils.qs("[data-tx-tbody]");
      this.rows = tbody ? Array.from(tbody.querySelectorAll("tr[data-id]")) : [];
      this.feedItems = Array.from(
        document.querySelectorAll("[data-tx-feed] [data-feed-id]")
      );
      this.filtered = this.rows.slice();
    },

    /**
     * Dipanggil halaman setelah baris di-render dinamis.
     * Tidak melakukan binding ulang — hanya membaca elemen & render ulang.
     */
    refreshRows() {
      this.readRows();
      this.applyFilters();
    },

    bindSearch() {
      const input = Utils.qs("[data-search]");
      const clear = Utils.qs("[data-search-clear]");
      if (!input) return;

      input.addEventListener("input", (e) => {
        const term = e.target.value.toLowerCase().trim();
        if (clear) clear.classList.toggle("is-visible", term.length > 0);
        this.applyFilters();
      });
      if (clear) {
        clear.addEventListener("click", () => {
          input.value = "";
          clear.classList.remove("is-visible");
          input.dispatchEvent(new Event("input"));
          this.applyFilters();
          input.focus();
        });
      }
    },

    bindFilters() {
      const typeSel = Utils.qs("[data-filter-type]");
      const catSel = Utils.qs("[data-filter-category]");
      const periodSel = Utils.qs("[data-filter-period]");
      const resetBtn = Utils.qs("[data-filter-reset]");

      [typeSel, catSel, periodSel].forEach((el) => {
        if (el) el.addEventListener("change", () => this.applyFilters());
      });

      if (resetBtn) {
        resetBtn.addEventListener("click", () => this.resetFilters());
      }
    },

    bindSort() {
      const sortSel = Utils.qs("[data-filter-sort]");
      if (!sortSel) return;
      sortSel.addEventListener("change", () => this.applyFilters(sortSel.value));
    },

    bindPerPage() {
      const perSel = Utils.qs("[data-per-page]");
      if (!perSel) return;
      perSel.addEventListener("change", (e) => {
        this.perPage = parseInt(e.target.value, 10);
        this.currentPage = 1;
        this.render();
      });
    },

    bindSelectAll() {
      const selectAll = Utils.qs("[data-select-all]");
      if (!selectAll) return;
      selectAll.addEventListener("change", () => {
        this.visibleCheckboxes().forEach((cb) => {
          cb.checked = selectAll.checked;
        });
      });

      const tbody = Utils.qs("[data-tx-tbody]");
      if (tbody) {
        tbody.addEventListener("change", (e) => {
          if (e.target.classList.contains("row-checkbox")) {
            const v = this.visibleCheckboxes();
            selectAll.checked = v.length > 0 && v.every((cb) => cb.checked);
          }
        });
      }
    },

    bindRowActions() {
      // Edit & Hapus memakai delegasi event karena baris di-render ulang.
      // Dua container: tbody (desktop) dan feed kartu (mobile).
      const onAction = (e) => {
        const editBtn = e.target.closest("[data-action='edit']");
        const deleteBtn = e.target.closest("[data-action='delete']");
        if (!editBtn && !deleteBtn) return;

        const host = editBtn ? editBtn.closest("[data-id], [data-feed-id]")
                             : deleteBtn.closest("[data-id], [data-feed-id]");
        if (!host) return;
        const id = host.dataset.id || host.dataset.feedId;

        if (editBtn) this.handleEdit(id);
        if (deleteBtn) this.handleDelete(id);
      };

      [Utils.qs("[data-tx-tbody]"), Utils.qs("[data-tx-feed]")]
        .filter(Boolean)
        .forEach((el) => el.addEventListener("click", onAction));
    },

    visibleCheckboxes() {
      return Array.from(
        document.querySelectorAll("[data-tx-tbody] .row-checkbox")
      ).filter((cb) => cb.closest("tr").style.display !== "none");
    },

    resetFilters() {
      const search = Utils.qs("[data-search]");
      const typeSel = Utils.qs("[data-filter-type]");
      const catSel = Utils.qs("[data-filter-category]");
      const periodSel = Utils.qs("[data-filter-period]");
      const sortSel = Utils.qs("[data-filter-sort]");

      if (search) search.value = "";
      if (typeSel) typeSel.value = "all";
      if (catSel) catSel.value = "all";
      if (periodSel) periodSel.value = "all";
      if (sortSel) sortSel.value = "newest";

      const clear = Utils.qs("[data-search-clear]");
      if (clear) clear.classList.remove("is-visible");

      const selectAll = Utils.qs("[data-select-all]");
      if (selectAll) selectAll.checked = false;

      this.applyFilters();
      Feedback.showToast(
        "Filter direset",
        "Menampilkan seluruh transaksi.",
        "restart_alt"
      );
    },

    applyFilters(sortValue) {
      const search = Utils.qs("[data-search]");
      const typeSel = Utils.qs("[data-filter-type]");
      const catSel = Utils.qs("[data-filter-category]");
      const periodSel = Utils.qs("[data-filter-period]");
      const sortSel = Utils.qs("[data-filter-sort]");

      const term = (search?.value || "").toLowerCase().trim();
      const type = typeSel?.value || "all";
      const cat = catSel?.value || "all";
      const period = periodSel?.value || "all";
      const sort = sortValue || sortSel?.value || "newest";

      this.filtered = this.rows.filter((row) => {
        const text = row.dataset.search || row.textContent.toLowerCase();
        if (term && !text.includes(term)) return false;
        if (type !== "all" && row.dataset.type !== type) return false;
        if (cat !== "all" && row.dataset.kategori !== cat) return false;
        if (period !== "all" && row.dataset.period !== period) return false;
        return true;
      });

      // Sorting
      const sorted = this.filtered.slice();
      sorted.sort((a, b) => {
        const tglA = a.dataset.tanggal || "";
        const tglB = b.dataset.tanggal || "";
        const amtA = parseInt(a.dataset.jumlah || "0", 10);
        const amtB = parseInt(b.dataset.jumlah || "0", 10);

        if (sort === "oldest") return tglA.localeCompare(tglB);
        if (sort === "highest") return amtB - amtA;
        if (sort === "lowest") return amtA - amtB;
        return tglB.localeCompare(tglA); // default: terbaru
      });
      this.filtered = sorted;
      this.currentPage = 1;
      this.render();
    },

    render() {
      const total = this.filtered.length;
      const totalPages = Math.max(1, Math.ceil(total / this.perPage));

      if (this.currentPage > totalPages) this.currentPage = totalPages;

      const start = (this.currentPage - 1) * this.perPage;
      const end = Math.min(start + this.perPage, total);
      const pageRows = this.filtered.slice(start, end);
      const pageRowIds = new Set(pageRows.map((r) => r.dataset.id));

      // Tampilkan baris tabel desktop sesuai urutan sorting.
      const tbody = Utils.qs("[data-tx-tbody]");
      if (tbody) {
        pageRows.forEach((row) => {
          row.style.display = "";
          tbody.appendChild(row);
        });
      }
      this.rows.forEach((row) => {
        if (!pageRowIds.has(row.dataset.id)) row.style.display = "none";
      });

      // Kartu mobile mengikuti halaman, filter, dan urutan sorting yang sama.
      const feed = Utils.qs("[data-tx-feed]");
      if (feed) {
        pageRows.forEach((r) => {
          const item = this.feedItems.find((i) => i.dataset.feedId === r.dataset.id);
          if (item) {
            item.style.display = "";
            feed.appendChild(item);
          }
        });
      }
      this.feedItems.forEach((item) => {
        if (!pageRowIds.has(item.dataset.feedId)) item.style.display = "none";
      });

      // Sembunyikan blok pagination bila tidak ada hasil filter.
      const pagination = Utils.qs("[data-pagination]");
      if (pagination) pagination.classList.toggle("is-hidden", total === 0);

      this.updateInfo(start, end, total);
      this.renderPagination(totalPages);

      // Sinkronkan status select all checkbox pada halaman baru.
      const selectAll = Utils.qs("[data-select-all]");
      if (selectAll) {
        const v = this.visibleCheckboxes();
        selectAll.checked = v.length > 0 && v.every((cb) => cb.checked);
      }
    },

    updateInfo(start, end, total) {
      const info = Utils.qs("[data-tx-info]");
      if (info) {
        info.textContent =
          total === 0
            ? "Tidak ada transaksi"
            : `Menampilkan ${start + 1}-${end} dari ${total} transaksi`;
      }
      const empty = Utils.qs("[data-tx-empty]");
      if (empty) empty.classList.toggle("is-hidden", total > 0);
    },

    renderPagination(totalPages) {
      const nav = Utils.qs("[data-pagination]");
      if (!nav) return;

      const btn = (label, page, opts = {}) => {
        const b = document.createElement("button");
        b.className =
          "pagination__btn" +
          (opts.active ? " is-active" : "") +
          (opts.wide ? " pagination__btn--icon" : "");
        b.type = "button";
        b.textContent = label;
        if (opts.disabled) b.disabled = true;
        if (page) b.dataset.page = page;
        if (opts.label) b.setAttribute("aria-label", opts.label);
        return b;
      };

      nav.innerHTML = "";

      // Prev
      const prev = btn("‹", this.currentPage - 1, {
        disabled: this.currentPage === 1,
        label: "Halaman sebelumnya",
        wide: true,
      });
      prev.addEventListener("click", () => this.goTo(this.currentPage - 1));
      nav.appendChild(prev);

      // Nomor halaman (window 5 + ellipsis)
      const pages = [];
      const windowSize = 2;
      for (let p = 1; p <= totalPages; p++) {
        if (
          p === 1 ||
          p === totalPages ||
          (p >= this.currentPage - windowSize && p <= this.currentPage + windowSize)
        ) {
          pages.push(p);
        }
      }

      let prevNum = 0;
      pages.forEach((p) => {
        if (prevNum && p - prevNum > 1) {
          const ell = document.createElement("span");
          ell.className = "pagination__ellipsis";
          ell.textContent = "…";
          nav.appendChild(ell);
        }
        const b = btn(String(p), p, { active: p === this.currentPage });
        b.addEventListener("click", () => this.goTo(p));
        nav.appendChild(b);
        prevNum = p;
      });

      // Next
      const next = btn("›", this.currentPage + 1, {
        disabled: this.currentPage === totalPages,
        label: "Halaman berikutnya",
        wide: true,
      });
      next.addEventListener("click", () => this.goTo(this.currentPage + 1));
      nav.appendChild(next);
    },

    goTo(page) {
      const totalPages = Math.max(1, Math.ceil(this.filtered.length / this.perPage));
      if (page < 1 || page > totalPages) return;
      this.currentPage = page;
      this.render();
      // Gulir ke atas tabel saat ganti halaman.
      const wrap = Utils.qs("[data-tx-card]");
      if (wrap) wrap.scrollIntoView({ behavior: "smooth", block: "start" });
    },

    handleEdit(id) {
      const ext = window.location.pathname.endsWith(".php") ? ".php" : ".html";
      window.location.href = `form-transaksi${ext}?id=${encodeURIComponent(id)}`;
    },

    handleDelete(id) {
      const row = this.rows.find((r) => r.dataset.id === id);
      const desc = row?.dataset.descripsi || "transaksi ini";
      const jumlah = row?.dataset.jumlah || 0;

      Feedback.openModal({
        title: "Hapus transaksi?",
        message: `Transaksi "${desc}" sebesar ${Utils.formatRupiah(
          jumlah
        )} akan dihapus dari daftar. Tindakan ini tidak dapat dibatalkan.`,
        confirmText: "Ya, hapus",
        onConfirm: () => {
          if (typeof FinanceTrackData !== "undefined" && FinanceTrackData.deleteTransaction) {
            FinanceTrackData.deleteTransaction(id);
          }
          const feedItem = this.feedItems.find((i) => i.dataset.feedId === id);
          if (row) row.remove();
          if (feedItem) feedItem.remove();

          this.readRows();
          this.applyFilters();

          const totalEl = Utils.qs("[data-tx-total]");
          if (totalEl && typeof FinanceTrackData !== "undefined") {
            totalEl.textContent = FinanceTrackData.transactions.length + " catatan";
          }

          Feedback.showToast(
            "Transaksi dihapus",
            `"${desc}" telah dihapus dari daftar.`,
            "delete"
          );
        },
      });
    },
  };

  /* =======================================================================
     5. FORM — halaman Tambah Transaksi
     ======================================================================= */
  const TransactionForm = {
    currentType: "expense",
    editingId: null,

    init() {
      const form = Utils.qs("[data-tx-form]");
      if (!form) return;

      this.setupTypeSelector();
      this.setupAmount();
      this.setupQuickAmounts();
      this.setupLivePreview();
      this.setupFieldValidationEvents();
      this.setupUpload();
      this.setupSubmit();
      this.setupCancel();

      const editId = new URLSearchParams(window.location.search).get("id");
      if (editId) {
        this.loadTransactionForEdit(editId);
      } else {
        this.applyTypeFromQuery();
      }
    },

    /**
     * Memuat data transaksi yang ada untuk diedit.
     */
    loadTransactionForEdit(id) {
      if (typeof FinanceTrackData === "undefined") return;
      const tx = FinanceTrackData.getTransaction
        ? FinanceTrackData.getTransaction(id)
        : null;
      if (!tx) return;

      this.editingId = tx.id;

      // Set jenis transaksi
      this.setType(tx.tipe);

      // Isi nilai-nilai form
      const amountInput = Utils.qs("[data-field-amount]");
      if (amountInput) amountInput.value = Utils.thousands(tx.jumlah);

      const catSel = Utils.qs("[data-field-category]");
      if (catSel) catSel.value = tx.kategoriId;

      const methodSel = Utils.qs("[data-field-method]");
      if (methodSel && methodSel.options.length === 0 && FinanceTrackData.paymentMethods) {
        methodSel.innerHTML = FinanceTrackData.paymentMethods
          .map((m) => `<option value="${m.id}">${Utils.escapeHtml(m.nama)}</option>`)
          .join("");
      }
      if (methodSel) methodSel.value = tx.metode;

      const dateInput = Utils.qs("[data-field-date]");
      if (dateInput) dateInput.value = tx.tanggal;

      const timeInput = Utils.qs("[data-field-time]");
      if (timeInput) timeInput.value = tx.jam || "";

      const descInput = Utils.qs("[data-field-desc]");
      if (descInput) descInput.value = tx.deskripsi;

      const notesInput = Utils.qs("#field-notes");
      if (notesInput && tx.catatan) notesInput.value = tx.catatan;

      if (tx.receipt_path) {
        const previewBox = Utils.qs("[data-upload-preview]");
        const previewImg = Utils.qs("[data-upload-preview-img]");
        const previewName = Utils.qs("[data-upload-preview-name]");
        const previewSize = Utils.qs("[data-upload-preview-size]");
        if (previewImg) previewImg.src = "../" + tx.receipt_path;
        if (previewName) previewName.textContent = "Berkas Tersimpan";
        if (previewSize) previewSize.textContent = "";
        if (previewBox) previewBox.classList.remove("is-hidden");
      }

      // Perbarui judul & tombol untuk mode edit
      const title = Utils.qs(".page-title");
      if (title) title.textContent = "Edit Transaksi";
      const subtitle = Utils.qs(".page-subtitle");
      if (subtitle) subtitle.textContent = "Perbarui rincian transaksi keuanganmu.";
      const breadcrumbCur = Utils.qs(".breadcrumb [aria-current='page']");
      if (breadcrumbCur) breadcrumbCur.textContent = "Edit Transaksi";

      const submitBtn = Utils.qs('button[type="submit"]');
      if (submitBtn) {
        submitBtn.innerHTML =
          '<span class="material-symbols-outlined icon-sm" aria-hidden="true">check_circle</span> Perbarui Transaksi';
      }

      // Sembunyikan "Simpan & Tambah Lagi" saat edit
      const saveMoreBtn = Utils.qs("[data-save-more]");
      if (saveMoreBtn) saveMoreBtn.style.display = "none";

      if (tx.receipt_path) {
        const previewBox = Utils.qs("[data-upload-preview]");
        const previewImg = Utils.qs("[data-upload-preview-img]");
        const previewName = Utils.qs("[data-upload-preview-name]");
        const previewSize = Utils.qs("[data-upload-preview-size]");
        if (previewBox && previewImg) {
          const imgUrl = tx.receipt_path.startsWith("http") ? tx.receipt_path : ("../" + tx.receipt_path.replace(/^\//, ""));
          previewImg.src = imgUrl;
          if (previewName) previewName.textContent = "Foto struk tersimpan";
          if (previewSize) previewSize.textContent = "Tersimpan di server";
          previewBox.classList.remove("is-hidden");
        }
      }

      this.updatePreview();
      if (catSel) catSel.dispatchEvent(new Event("change"));
    },

    /**
     * Dukungan deep-link dari Dashboard: form-transaksi.html?jenis=income
     */
    applyTypeFromQuery() {
      const requested = new URLSearchParams(window.location.search).get("jenis");
      if (requested !== "income" && requested !== "expense") return;
      if (requested === this.currentType) return;

      this.setType(requested);
    },

    /** Aktifkan satu mode (expense/income) dan segarkan dropdown + preview. */
    setType(type) {
      this.currentType = type;

      Utils.qsa("[data-type-btn]").forEach((b) => {
        b.classList.toggle("is-active", b.dataset.typeBtn === type);
        b.setAttribute("aria-pressed", String(b.dataset.typeBtn === type));
      });

      this.updateCategoryOptions(type);
      this.updatePreview();

      const catSel = Utils.qs("[data-field-category]");
      if (catSel) catSel.dispatchEvent(new Event("change"));
    },

    setupTypeSelector() {
      Utils.qsa("[data-type-btn]").forEach((btn) => {
        btn.addEventListener("click", () => this.setType(btn.dataset.typeBtn));
      });
    },

    updateCategoryOptions(type) {
      const catSel = Utils.qs("[data-field-category]");
      if (!catSel || typeof FinanceTrackData === "undefined") return;

      const list =
        type === "income"
          ? FinanceTrackData.incomeCategories()
          : FinanceTrackData.expenseCategories();

      const current = catSel.value;
      catSel.innerHTML = list
        .map(
          (c) =>
            `<option value="${c.id}">${Utils.escapeHtml(c.nama)}</option>`
        )
        .join("");

      // Pertahankan pilihan bila masih valid.
      if (list.some((c) => c.id === current)) catSel.value = current;
    },

    setupAmount() {
      const input = Utils.qs("[data-field-amount]");
      if (!input) return;

      input.addEventListener("input", (e) => {
        const digits = e.target.value.replace(/\D/g, "");
        e.target.value = digits ? Utils.thousands(digits) : "";
        input.closest(".field")?.classList.remove("has-error");
        this.updatePreview();
      });
    },

    setupQuickAmounts() {
      Utils.qsa("[data-quick-amount]").forEach((btn) => {
        btn.addEventListener("click", () => {
          const input = Utils.qs("[data-field-amount]");
          if (!input) return;
          const current = Utils.parseRupiah(input.value);
          const add = parseInt(btn.dataset.quickAmount, 10);
          input.value = Utils.thousands(current + add);
          input.closest(".field")?.classList.remove("has-error");
          this.updatePreview();
        });
      });
    },

        setupUpload() {
      const fileInput = Utils.qs("[data-upload-input]");
      const uploadZone = Utils.qs("[data-upload-zone]");
      const previewBox = Utils.qs("[data-upload-preview]");
      const previewImg = Utils.qs("[data-upload-preview-img]");
      const previewName = Utils.qs("[data-upload-preview-name]");
      const previewSize = Utils.qs("[data-upload-preview-size]");
      const removeBtn = Utils.qs("[data-upload-remove]");

      if (!fileInput) return;

      const allowedTypes = ["image/jpeg", "image/jpg", "image/png", "image/webp"];
      const allowedExtensions = [".jpg", ".jpeg", ".png", ".webp"];
      const maxSizeBytes = 5 * 1024 * 1024; // 5MB

      const validateAndHandleFile = (file) => {
        if (!file) return;

        const fileName = (file.name || "").toLowerCase();
        const ext = fileName.slice(fileName.lastIndexOf("."));

        // Tolak PDF secara eksplisit
        if (fileName.endsWith(".pdf") || file.type === "application/pdf") {
          Feedback.showToast(
            "Format file tidak didukung",
            "File PDF tidak diperbolehkan. Harap unggah foto struk berformat JPG, PNG, atau WEBP.",
            "error"
          );
          fileInput.value = "";
          return false;
        }

        // Validasi ekstensi dan tipe MIME
        const isAllowedExt = allowedExtensions.includes(ext);
        const isAllowedType = allowedTypes.includes(file.type);
        if (!isAllowedExt && !isAllowedType) {
          Feedback.showToast(
            "Format file tidak didukung",
            "Hanya format JPG, PNG, atau WEBP yang diperbolehkan.",
            "error"
          );
          fileInput.value = "";
          return false;
        }

        // Validasi ukuran maksimal 5MB
        if (file.size > maxSizeBytes) {
          Feedback.showToast(
            "Ukuran file terlalu besar",
            "Ukuran file maksimal 5MB. Silakan pilih foto dengan resolusi lebih kecil.",
            "error"
          );
          fileInput.value = "";
          return false;
        }

        // Render preview thumbnail
        const reader = new FileReader();
        reader.onload = (e) => {
          if (previewImg) previewImg.src = e.target.result;
          if (previewName) previewName.textContent = file.name;
          if (previewSize) previewSize.textContent = (file.size / 1024).toFixed(0) + " KB";
          if (previewBox) previewBox.classList.remove("is-hidden");
        };
        reader.readAsDataURL(file);
        return true;
      };

      fileInput.addEventListener("change", (e) => {
        const file = e.target.files && e.target.files[0];
        if (file) {
          validateAndHandleFile(file);
        }
      });

      if (uploadZone) {
        uploadZone.addEventListener("dragover", (e) => {
          e.preventDefault();
          uploadZone.classList.add("is-dragover");
        });
        uploadZone.addEventListener("dragleave", () => {
          uploadZone.classList.remove("is-dragover");
        });
        uploadZone.addEventListener("drop", (e) => {
          e.preventDefault();
          uploadZone.classList.remove("is-dragover");
          const file = e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0];
          if (file) {
            try {
              const dt = new DataTransfer();
              dt.items.add(file);
              fileInput.files = dt.files;
            } catch (err) {}
            validateAndHandleFile(file);
          }
        });
      }

      if (removeBtn) {
        removeBtn.addEventListener("click", () => {
          fileInput.value = "";
          if (previewImg) previewImg.src = "";
          if (previewBox) previewBox.classList.add("is-hidden");
        });
      }
    },

    setupFieldValidationEvents() {
      [
        "data-field-amount",
        "data-field-desc",
        "data-field-category",
        "data-field-method",
        "data-field-date",
      ].forEach((attr) => {
        const el = Utils.qs(`[${attr}]`);
        if (el) {
          el.addEventListener("input", () =>
            el.closest(".field")?.classList.remove("has-error")
          );
          el.addEventListener("change", () =>
            el.closest(".field")?.classList.remove("has-error")
          );
        }
      });
    },

    setupLivePreview() {
      // Preview diperbarui saat field lain berubah.
      [
        "data-field-category",
        "data-field-date",
        "data-field-time",
        "data-field-desc",
        "data-field-method",
      ]
        .map((sel) => Utils.qs("[" + sel + "]"))
        .filter(Boolean)
        .forEach((el) => {
          el.addEventListener("input", () => this.updatePreview());
          el.addEventListener("change", () => this.updatePreview());
        });
    },

    updatePreview() {
      if (typeof FinanceTrackData === "undefined") return;

      const amount = Utils.parseRupiah(Utils.qs("[data-field-amount]")?.value);
      const catId = Utils.qs("[data-field-category]")?.value;
      const methodId = Utils.qs("[data-field-method]")?.value;
      const date = Utils.qs("[data-field-date]")?.value;
      const time = Utils.qs("[data-field-time]")?.value;
      const desc = Utils.qs("[data-field-desc]")?.value;

      const cat = FinanceTrackData.getCategory(catId);
      const method = FinanceTrackData.getMethod(methodId);
      const isIncome = this.currentType === "income";

      // Nominal
      const amountEl = Utils.qs("[data-preview-amount]");
      if (amountEl) {
        amountEl.textContent =
          (isIncome ? "+ " : "- ") + Utils.formatRupiah(amount);
        amountEl.classList.toggle("text-income", isIncome);
        amountEl.classList.toggle("text-expense", !isIncome);
      }

      // Badge jenis
      const badgeEl = Utils.qs("[data-preview-badge]");
      if (badgeEl) {
        badgeEl.textContent = isIncome ? "Pemasukan" : "Pengeluaran";
        badgeEl.className =
          "badge " + (isIncome ? "badge--income" : "badge--expense");
      }

      // Kategori
      const catEl = Utils.qs("[data-preview-category]");
      if (catEl) catEl.textContent = cat ? cat.nama : "-";

      // Metode
      const methodEl = Utils.qs("[data-preview-method]");
      if (methodEl) methodEl.textContent = method ? method.nama : "-";

      // Tanggal
      const dateEl = Utils.qs("[data-preview-date]");
      if (dateEl) {
        dateEl.textContent = date
          ? Utils.formatTanggal(date) + (time ? " • " + time : "")
          : "-";
      }

      // Deskripsi
      const descEl = Utils.qs("[data-preview-desc]");
      if (descEl) descEl.textContent = desc || "-";
    },

    setupSubmit() {
      const form = Utils.qs("[data-tx-form]");
      if (!form) return;

      const resetFormDefaults = () => {
        form.reset();
        Utils.qsa(".field.has-error", form).forEach((el) =>
          el.classList.remove("has-error")
        );
        const fileInput = Utils.qs("[data-upload-input]");
        if (fileInput) fileInput.value = "";

        const now = new Date();
        const pad = (n) => String(n).padStart(2, "0");
        const today =
          now.getFullYear() +
          "-" +
          pad(now.getMonth() + 1) +
          "-" +
          pad(now.getDate());
        const dateInput = Utils.qs("[data-field-date]");
        const timeInput = Utils.qs("[data-field-time]");
        if (dateInput) dateInput.value = today;
        if (timeInput)
          timeInput.value = pad(now.getHours()) + ":" + pad(now.getMinutes());

        const uploadName = Utils.qs("[data-upload-name]");
        if (uploadName) uploadName.textContent = "Unggah Foto Struk / Nota";
        const uploadHint = Utils.qs("[data-upload-hint]");
        if (uploadHint)
          uploadHint.textContent = "Format JPG, PNG, WEBP (Maks. 5MB)";
        const uploadPreview = Utils.qs("[data-upload-preview]");
        if (uploadPreview) uploadPreview.classList.add("is-hidden");
        const previewImg = Utils.qs("[data-upload-preview-img]");
        if (previewImg) previewImg.src = "";

        this.updateCategoryOptions(this.currentType);
        this.updatePreview();
        const catSel = Utils.qs("[data-field-category]");
        if (catSel) catSel.dispatchEvent(new Event("change"));
        if (typeof window.renderBudgetHint === "function") {
          window.renderBudgetHint();
        }
      };

      const save = async (addMore) => {
        const amountEl = Utils.qs("[data-field-amount]");
        const descEl = Utils.qs("[data-field-desc]");
        const catEl = Utils.qs("[data-field-category]");
        const methodEl = Utils.qs("[data-field-method]");
        const dateEl = Utils.qs("[data-field-date]");
        const timeEl = Utils.qs("[data-field-time]");
        const notesEl = Utils.qs("#field-notes");

        const amount = Utils.parseRupiah(amountEl?.value);
        const desc = (descEl?.value || "").trim();
        const catId = catEl?.value || "";
        const methodId = methodEl?.value || "";
        const date = dateEl?.value || "";
        const time = timeEl?.value || "";
        const notes = (notesEl?.value || "").trim();

        let valid = true;

        if (!amount || amount <= 0) {
          amountEl?.closest(".field")?.classList.add("has-error");
          valid = false;
        } else {
          amountEl?.closest(".field")?.classList.remove("has-error");
        }

        if (!desc) {
          descEl?.closest(".field")?.classList.add("has-error");
          valid = false;
        } else {
          descEl?.closest(".field")?.classList.remove("has-error");
        }

        if (!catId) {
          catEl?.closest(".field")?.classList.add("has-error");
          valid = false;
        } else {
          catEl?.closest(".field")?.classList.remove("has-error");
        }

        if (!methodId) {
          methodEl?.closest(".field")?.classList.add("has-error");
          valid = false;
        } else {
          methodEl?.closest(".field")?.classList.remove("has-error");
        }

        if (!date) {
          dateEl?.closest(".field")?.classList.add("has-error");
          valid = false;
        } else {
          dateEl?.closest(".field")?.classList.remove("has-error");
        }

        if (!valid) {
          Feedback.showToast(
            "Form belum lengkap",
            "Harap lengkapi semua kolom bertanda bintang (*).",
            "error"
          );
          return;
        }

        const isIncome = this.currentType === "income";
        const fileInput = Utils.qs("[data-upload-input]");
        const receiptFile = fileInput?.files?.[0] || null;

        const txData = {
          tanggal: date,
          jam: time || "12:00",
          deskripsi: desc,
          kategoriId: catId,
          metode: methodId,
          tipe: this.currentType,
          jumlah: amount,
          catatan: notes,
          receipt_file: receiptFile,
          receipt_name: receiptFile ? receiptFile.name : "",
        };

        if (this.editingId) {
          if (window.FinanceTrackAPI && window.location.protocol.startsWith("http")) {
            try {
              const res = await window.FinanceTrackAPI.updateTransaction(this.editingId, txData, receiptFile);
              if (res && res.data && res.data.struk) {
                txData.receipt_path = res.data.struk;
              }
            } catch (err) {
              console.warn("Gagal update transaksi via API:", err);
            }
          }
          if (
            typeof FinanceTrackData !== "undefined" &&
            FinanceTrackData.updateTransaction
          ) {
            FinanceTrackData.updateTransaction(this.editingId, txData);
          }
          try {
            sessionStorage.setItem(
              "ft_toast",
              JSON.stringify({
                title: "Transaksi diperbarui",
                message: `"${desc}" berhasil diperbarui.`,
                icon: "check_circle",
              })
            );
          } catch (e) {}
          const ext = window.location.pathname.endsWith(".php") ? ".php" : ".html";
          window.location.href = `transaksi${ext}`;
          return;
        }

        // Mode Tambah Transaksi Baru
        if (window.FinanceTrackAPI && window.location.protocol.startsWith("http")) {
          try {
            const apiRes = await window.FinanceTrackAPI.createTransaction(txData, receiptFile);
            if (apiRes && apiRes.data) {
              txData.id = apiRes.data.id;
              if (apiRes.data.struk) {
                txData.receipt_path = apiRes.data.struk;
              }
            }
          } catch (err) {
            console.warn("Gagal membuat transaksi via API:", err);
          }
        }
        if (
          typeof FinanceTrackData !== "undefined" &&
          FinanceTrackData.addTransaction
        ) {
          FinanceTrackData.addTransaction(txData);
        }

        if (addMore) {
          Feedback.showToast(
            "Transaksi tersimpan",
            `${Utils.formatRupiah(amount)} untuk "${desc}" tercatat. Silakan isi transaksi berikutnya.`,
            "check_circle"
          );
          resetFormDefaults();
          amountEl?.focus();
        } else {
          try {
            sessionStorage.setItem(
              "ft_toast",
              JSON.stringify({
                title: isIncome ? "Pemasukan tersimpan" : "Pengeluaran tersimpan",
                message: `${Utils.formatRupiah(amount)} untuk "${desc}" berhasil dicatat.`,
                icon: "check_circle",
              })
            );
          } catch (e) {}
          const ext = window.location.pathname.endsWith(".php") ? ".php" : ".html";
          window.location.href = `transaksi${ext}`;
        }
      };

      form.addEventListener("submit", (e) => {
        e.preventDefault();
        save(false);
      });

      const saveMore = Utils.qs("[data-save-more]");
      if (saveMore) {
        saveMore.addEventListener("click", () => save(true));
      }
    },

    setupCancel() {
      const cancel = Utils.qs("[data-cancel]");
      if (!cancel) return;
      cancel.addEventListener("click", (e) => {
        e.preventDefault();
        const isEditing = Boolean(this.editingId);
        Feedback.openModal({
          title: isEditing ? "Batalkan perubahan?" : "Batalkan input?",
          message: isEditing
            ? "Perubahan yang sudah kamu buat tidak akan disimpan. Yakin ingin membatalkan?"
            : "Data yang sudah kamu isi akan hilang. Yakin ingin membatalkan?",
          confirmText: "Ya, batalkan",
          confirmClass: "btn--secondary",
          onConfirm: () => {
            if (isEditing) {
              const ext = window.location.pathname.endsWith(".php") ? ".php" : ".html";
              window.location.href = `transaksi${ext}`;
              return;
            }
            const form = Utils.qs("[data-tx-form]");
            if (form) form.reset();
            const now = new Date();
            const pad = (n) => String(n).padStart(2, "0");
            const dateInput = Utils.qs("[data-field-date]");
            const timeInput = Utils.qs("[data-field-time]");
            if (dateInput)
              dateInput.value =
                now.getFullYear() +
                "-" +
                pad(now.getMonth() + 1) +
                "-" +
                pad(now.getDate());
            if (timeInput)
              timeInput.value = pad(now.getHours()) + ":" + pad(now.getMinutes());

            const uploadName = Utils.qs("[data-upload-name]");
            if (uploadName) uploadName.textContent = "Unggah Foto Struk / Nota";
            const uploadHint = Utils.qs("[data-upload-hint]");
            if (uploadHint)
              uploadHint.textContent = "Format JPG, PNG, WEBP (Maks. 5MB)";
            const uploadPreview = Utils.qs("[data-upload-preview]");
            if (uploadPreview) uploadPreview.classList.add("is-hidden");
            const previewImg = Utils.qs("[data-upload-preview-img]");
            if (previewImg) previewImg.src = "";
            const fileInput = Utils.qs("[data-upload-input]");
            if (fileInput) fileInput.value = "";

            this.updateCategoryOptions(this.currentType);
            this.updatePreview();
            const catSel = Utils.qs("[data-field-category]");
            if (catSel) catSel.dispatchEvent(new Event("change"));

            Feedback.showToast(
              "Form dibatalkan",
              "Semua kolom dikosongkan kembali.",
              "close"
            );
          },
        });
      });
    },
  };

  /* =======================================================================
     6. BUDGET — status & progress bar
     ======================================================================= */
  const BudgetUI = {
    /** Modifiers_progress bar yang bersifat eksklusif (hanya boleh satu aktif). */
    BAR_MODIFIERS: [
      "progress__bar--success",
      "progress__bar--warning",
      "progress__bar--danger",
    ],

    init() {
      if (typeof FinanceTrackData === "undefined") return;

      // Warnai setiap progress bar + tetapkan status berdasarkan persentase.
      Utils.qsa("[data-budget-card]").forEach((card) => {
        const spent = parseInt(card.dataset.budgetSpent || "0", 10);
        const limit = parseInt(card.dataset.budgetLimit || "0", 10);
        const pct = Utils.percent(spent, limit);

        const bar = Utils.qs("[data-budget-bar]", card);
        if (bar) {
          // Modikator bersifat eksklusif — buang yang lain agar tidak konflik
          // bila kartu sudah punya kelas dari renderer halaman.
          bar.classList.remove(...this.BAR_MODIFIERS);
          bar.classList.add(
            pct >= 100
              ? "progress__bar--danger"
              : pct >= 80
              ? "progress__bar--warning"
              : "progress__bar--success"
          );
          // Naikkan sedikit delay supaya animasi progress terlihat.
          setTimeout(() => (bar.style.width = pct + "%"), 120);
        }

        const pctEl = Utils.qs("[data-budget-pct]", card);
        if (pctEl) {
          pctEl.textContent = Utils.percent(spent, limit).toFixed(1) + "%";
        }

        const remainingEl = Utils.qs("[data-budget-remaining]", card);
        if (remainingEl) {
          const remaining = limit - spent;
          remainingEl.textContent =
            (remaining >= 0 ? "Sisa " : "Lebih ") + Utils.formatRupiah(Math.abs(remaining));
        }
      });
    },
  };

  /* =======================================================================
     7. CHART — Chart.js (dimuat via CDN di HTML)
     ======================================================================= */
  const Charts = {
    ready: false,
    chartRefs: {},

    init() {
      if (typeof Chart === "undefined") {
        // Chart.js gagal dimuat — biarkan halaman tetap berfungsi tanpa grafik.
        Utils.qsa("[data-chart-fallback]").forEach((el) =>
          el.classList.remove("is-hidden")
        );
        return;
      }
      this.ready = true;

      // Global default font agar konsisten dengan design system.
      Chart.defaults.font.family =
        '"Inter", system-ui, -apple-system, "Segoe UI", sans-serif';
      Chart.defaults.color = "#64748b";
      Chart.defaults.font.size = 12;

      this.renderTrend();
      this.renderExpenseDonut();
      this.renderMonthlyBar();
      this.renderDailyCashflow();
    },

    getTooltip() {
      return {
        backgroundColor: "#0f172a",
        padding: 10,
        cornerRadius: 6,
        titleFont: { size: 12, weight: "600" },
        bodyFont: { size: 12 },
        displayColors: true,
      };
    },

    renderTrend() {
      const ctx = Utils.qs("[data-chart-trend]");
      if (!ctx || typeof FinanceTrackData === "undefined") return;

      const existing = Chart.getChart(ctx);
      if (existing) existing.destroy();

      const t = FinanceTrackData.trend;
      this.chartRefs.trend = new Chart(ctx, {
        type: "line",
        data: {
          labels: t.labels,
          datasets: [
            {
              label: "Pemasukan",
              data: t.pemasukan,
              borderColor: "#2563eb",
              backgroundColor: "rgba(37,99,235,0.10)",
              borderWidth: 2,
              pointRadius: 3,
              pointBackgroundColor: "#2563eb",
              pointBorderColor: "#fff",
              pointBorderWidth: 2,
              tension: 0.35,
              fill: true,
            },
            {
              label: "Pengeluaran",
              data: t.pengeluaran,
              borderColor: "#e11d48",
              backgroundColor: "rgba(225,29,72,0.08)",
              borderWidth: 2,
              pointRadius: 3,
              pointBackgroundColor: "#e11d48",
              pointBorderColor: "#fff",
              pointBorderWidth: 2,
              tension: 0.35,
              fill: true,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          interaction: { mode: "index", intersect: false },
          plugins: {
            legend: { display: false },
            tooltip: {
              ...this.getTooltip(),
              callbacks: {
                label: (ctx) => ` ${ctx.dataset.label}: ${Utils.formatRupiah(ctx.parsed.y)}`,
              },
            },
          },
          scales: {
            x: { grid: { display: false }, border: { display: false } },
            y: {
              beginAtZero: true,
              grid: { color: "#e2e8f0", drawTicks: false },
              border: { display: false },
              ticks: {
                callback: (v) => Utils.formatRupiahShort(v),
                padding: 8,
              },
            },
          },
        },
      });
    },

    renderExpenseDonut() {
      const ctx = Utils.qs("[data-chart-expense]");
      if (!ctx || typeof FinanceTrackData === "undefined") return;

      const existing = Chart.getChart(ctx);
      if (existing) existing.destroy();

      const items = FinanceTrackData.expenseComposition;
      const total = items.reduce((sum, i) => sum + i.nilai, 0);

      this.chartRefs.expense = new Chart(ctx, {
        type: "doughnut",
        data: {
          labels: items.map((i) => i.nama),
          datasets: [
            {
              data: items.map((i) => i.nilai),
              backgroundColor: items.map((i) => i.warna),
              borderColor: "#fff",
              borderWidth: 2,
              hoverOffset: 6,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          cutout: "68%",
          plugins: {
            legend: { display: false },
            tooltip: {
              ...this.getTooltip(),
              callbacks: {
                label: (ctx) => {
                  const pct = Utils.percent(ctx.parsed, total);
                  return ` ${ctx.label}: ${Utils.formatRupiah(ctx.parsed)} (${pct}%)`;
                },
              },
            },
          },
        },
      });

      // Total di tengah donut.
      const centerEl = Utils.qs("[data-chart-expense-total]");
      if (centerEl) {
        centerEl.innerHTML =
          '<span class="text-secondary" style="font-size:var(--text-xs);text-transform:uppercase;font-weight:600">Total</span>' +
          '<span style="font-size:var(--text-lg);font-weight:700">' +
          Utils.formatRupiahShort(total) +
          "</span>";
      }

      // Legenda kustom (Chart.js legend dimatikan agar gaya konsisten dengan HTML).
      const legendEl = Utils.qs("[data-chart-expense-legend]");
      if (legendEl) {
        legendEl.innerHTML = items
          .map(
            (i) =>
              '<li class="legend-list__item">' +
              '<span class="legend-list__swatch" style="background:' + i.warna + '"></span>' +
              '<span class="legend-list__label">' + Utils.escapeHtml(i.nama) + "</span>" +
              '<span class="legend-list__value numeric">' +
              Utils.formatRupiah(i.nilai) +
              "</span>" +
              '<span class="legend-list__pct numeric">' +
              Utils.percent(i.nilai, total).toFixed(1) +
              "%</span></li>"
          )
          .join("");
      }
    },

    /**
     * Arus kas harian (halaman Laporan) — dihitung dari `transactions`,
     * bukan dari angka hardcode, sehingga konsisten dengan tabel rekap.
     */
    renderDailyCashflow() {
      const ctx = Utils.qs("[data-chart-cashflow]");
      if (!ctx || typeof FinanceTrackData === "undefined") return;

      const existing = Chart.getChart(ctx);
      if (existing) existing.destroy();

      let upto = FinanceTrackData.summary.tanggal || 1;
      const monthTxDays = FinanceTrackData.transactions
        .filter((t) => t.tanggal && t.tanggal.startsWith(FinanceTrackData.summary.periodeRaw))
        .map((t) => parseInt(t.tanggal.split("-")[2], 10))
        .filter((d) => !isNaN(d));
      if (monthTxDays.length > 0) {
        upto = Math.max(upto, ...monthTxDays);
      }

      const labels = [];
      const income = [];
      const expense = [];

      for (let day = 1; day <= upto; day++) {
        const iso = FinanceTrackData.summary.periodeRaw + "-" + String(day).padStart(2, "0");
        const sameDay = FinanceTrackData.transactions.filter((t) => t.tanggal === iso);

        labels.push(String(day));
        income.push(
          sameDay
            .filter((t) => t.tipe === "income")
            .reduce((s, t) => s + t.jumlah, 0)
        );
        expense.push(
          sameDay
            .filter((t) => t.tipe === "expense")
            .reduce((s, t) => s + t.jumlah, 0)
        );
      }

      this.chartRefs.cashflow = new Chart(ctx, {
        type: "bar",
        data: {
          labels,
          datasets: [
            {
              label: "Pemasukan",
              data: income,
              backgroundColor: "#0d9488",
              borderRadius: 3,
              maxBarThickness: 14,
            },
            {
              label: "Pengeluaran",
              data: expense,
              backgroundColor: "rgba(225,29,72,0.75)",
              borderRadius: 3,
              maxBarThickness: 14,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          interaction: { mode: "index", intersect: false },
          plugins: {
            legend: { display: false },
            tooltip: {
              ...this.getTooltip(),
              callbacks: {
                title: (items) => "Tanggal " + items[0].label + " " + (typeof FinanceTrackData !== 'undefined' ? FinanceTrackData.summary.periode : ""),
                label: (c) => ` ${c.dataset.label}: ${Utils.formatRupiah(c.parsed.y)}`,
              },
            },
          },
          scales: {
            x: { grid: { display: false }, border: { display: false } },
            y: {
              beginAtZero: true,
              grid: { color: "#e2e8f0", drawTicks: false },
              border: { display: false },
              ticks: {
                callback: (v) => Utils.formatRupiahShort(v),
                padding: 8,
              },
            },
          },
        },
      });
    },

    renderMonthlyBar() {
      const ctx = Utils.qs("[data-chart-monthly]");
      if (!ctx || typeof FinanceTrackData === "undefined") return;

      const existing = Chart.getChart(ctx);
      if (existing) existing.destroy();

      const labels = FinanceTrackData.trend ? FinanceTrackData.trend.labels : FinanceTrackData.monthlyRecap.map((r) => r.bulan.split(" ")[0].slice(0, 3)).reverse(); const pemasukan = FinanceTrackData.trend ? FinanceTrackData.trend.pemasukan : FinanceTrackData.monthlyRecap.map((r) => r.pemasukan).reverse(); const pengeluaran = FinanceTrackData.trend ? FinanceTrackData.trend.pengeluaran : FinanceTrackData.monthlyRecap.map((r) => r.pengeluaran).reverse();

      this.chartRefs.monthly = new Chart(ctx, {
        type: "bar",
        data: {
          labels: labels,
          datasets: [
            {
              label: "Pemasukan",
              data: pemasukan,
              backgroundColor: "#2563eb",
              borderRadius: 4,
              maxBarThickness: 28,
            },
            {
              label: "Pengeluaran",
              data: pengeluaran,
              backgroundColor: "rgba(225,29,72,0.75)",
              borderRadius: 4,
              maxBarThickness: 28,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false },
            tooltip: {
              ...this.getTooltip(),
              callbacks: {
                label: (ctx) => ` ${ctx.dataset.label}: ${Utils.formatRupiah(ctx.parsed.y)}`,
              },
            },
          },
          scales: {
            x: { grid: { display: false }, border: { display: false } },
            y: {
              beginAtZero: true,
              grid: { color: "#e2e8f0", drawTicks: false },
              border: { display: false },
              ticks: {
                callback: (v) => Utils.formatRupiahShort(v),
                padding: 8,
              },
            },
          },
        },
      });
    },
    updateTrendPeriod(period) {
      if (!this.chartRefs.trend || typeof FinanceTrackData === "undefined") return;
      const chart = this.chartRefs.trend;
      if (period === "30 Hari") {
        chart.data.labels = ["1-7 Okt", "8-14 Okt", "15-21 Okt", "22-28 Okt"];
        chart.data.datasets[0].data = [1500000, 1000000, 1200000, 500000];
        chart.data.datasets[1].data = [450000, 550000, 680000, 470000];
      } else if (period === "1 Tahun") {
        chart.data.labels = [
          "Nov '23",
          "Des",
          "Jan '24",
          "Feb",
          "Mar",
          "Apr",
          "Mei",
          "Jun",
          "Jul",
          "Agu",
          "Sep",
          "Okt",
        ];
        chart.data.datasets[0].data = [
          3500000, 4200000, 3600000, 3700000, 3900000, 4000000, 3800000,
          3900000, 3600000, 4100000, 4900000, 4200000,
        ];
        chart.data.datasets[1].data = [
          2000000, 2600000, 1900000, 2100000, 2300000, 2200000, 2100000,
          2250000, 1980000, 2400000, 3240000, 2150000,
        ];
      } else {
        // Default: 6 Bulan
        const t = FinanceTrackData.trend;
        chart.data.labels = t.labels;
        chart.data.datasets[0].data = t.pemasukan;
        chart.data.datasets[1].data = t.pengeluaran;
      }
      chart.update();
      Feedback.showToast(
        "Periode grafik diperbarui",
        "Menampilkan tren keuangan " + period + ".",
        "trending_up"
      );
    },
  };

  /* =======================================================================
     8. DASHBOARD — interaksi kecil
     ======================================================================= */
  const Dashboard = {
    init() {
      // Sembunyikan/tampilkan saldo (ikon mata).
      const toggle = Utils.qs("[data-balance-toggle]");
      const value = Utils.qs("[data-balance-value]");
      if (toggle && value) {
        let hidden = false;
        toggle.addEventListener("click", () => {
          hidden = !hidden;
          if (!value.dataset.realValue) {
            value.dataset.realValue = value.textContent.trim();
          }
          value.textContent = hidden ? "Rp ••••••••" : value.dataset.realValue;
          const icon = Utils.qs(".material-symbols-outlined", toggle);
          if (icon) icon.textContent = hidden ? "visibility_off" : "visibility";
          toggle.setAttribute(
            "aria-label",
            hidden ? "Tampilkan saldo" : "Sembunyikan saldo"
          );
        });
      }

      // Filter periode chart (30 Hari, 6 Bulan, 1 Tahun)
      const periodBtns = Utils.qsa("[data-chart-period]");
      periodBtns.forEach((btn) => {
        btn.addEventListener("click", () => {
          periodBtns.forEach((b) => b.classList.remove("is-active"));
          btn.classList.add("is-active");
          const periodText = btn.textContent.trim();
          Charts.updateTrendPeriod(periodText);
        });
      });
    },
  };

  /* =======================================================================
     BOOTSTRAP
     ======================================================================= */

  /* Namespace diekspos secara sinkron (bukan di dalam DOMContentLoaded)
     supaya inline script tiap halaman selalu bisa memanggil modul di bawah
     yang dijalankan setelah DOM siap. */
  window.FinanceTrack = {
    Utils,
    Navigation,
    Feedback,
    TransactionTable,
    TransactionForm,
    BudgetUI,
    Charts,
  };

  document.addEventListener("DOMContentLoaded", () => {
    Navigation.init();
    TopbarSearch.init();
    Feedback.init();
    TransactionTable.init();
    TransactionForm.init();
    BudgetUI.init();
    Dashboard.init();
    Charts.init();

    // Sinkronisasi data user dari sesi backend bila aktif
    if (window.FinanceTrackAPI && window.location.protocol.startsWith("http")) {
      window.FinanceTrackAPI.getMe().then((res) => {
        if (res && res.success && res.data && res.data.user) {
          const u = res.data.user;
          Utils.qsa("[data-user-name]").forEach((el) => { el.textContent = u.nama; });
          Utils.qsa("[data-user-avatar]").forEach((el) => {
            el.textContent = u.avatar_initials || (u.nama ? u.nama.slice(0, 2).toUpperCase() : "FP");
          });
          Utils.qsa("[data-user-role]").forEach((el) => { el.textContent = u.role || "Mahasiswa"; });
          Utils.qsa("[data-user-email]").forEach((el) => { el.textContent = u.email; });
        }
      }).catch(function () {});
    }
  });
})();
