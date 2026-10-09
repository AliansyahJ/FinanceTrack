-- ============================================================================
-- FinanceTrack Database Schema & Initial Seeds
-- Database: financetrack_db
-- Compatibility: MySQL 5.7+ / MySQL 8.0+ / MariaDB 10.3+
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `financetrack_db`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `financetrack_db`;

-- ----------------------------------------------------------------------------
-- Table: users
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(191) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('user', 'admin') NOT NULL DEFAULT 'user',
  `avatar` VARCHAR(10) NOT NULL DEFAULT 'FP',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: categories
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `categories` (
  `id` VARCHAR(50) PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `type` ENUM('expense', 'income') NOT NULL,
  `icon` VARCHAR(50) NOT NULL,
  `color` VARCHAR(20) NOT NULL DEFAULT '#0284c7',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: transactions
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `transactions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `category_id` VARCHAR(50) NOT NULL,
  `type` ENUM('expense', 'income') NOT NULL,
  `amount` DECIMAL(15, 2) NOT NULL,
  `transaction_date` DATE NOT NULL,
  `transaction_time` TIME NOT NULL DEFAULT '12:00:00',
  `description` VARCHAR(255) NOT NULL,
  `payment_method` VARCHAR(50) NOT NULL,
  `receipt_path` VARCHAR(255) NULL,
  `notes` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_tx_user_date` (`user_id`, `transaction_date`),
  INDEX `idx_tx_user_type` (`user_id`, `type`),
  CONSTRAINT `fk_tx_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tx_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: budgets
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `budgets` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `category_id` VARCHAR(50) NOT NULL,
  `amount` DECIMAL(15, 2) NOT NULL,
  `period_month` TINYINT NOT NULL,
  `period_year` SMALLINT NOT NULL,
  `notes` VARCHAR(255) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_user_cat_period` (`user_id`, `category_id`, `period_month`, `period_year`),
  CONSTRAINT `fk_budget_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_budget_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SEED DATA
-- ============================================================================

-- 1. Demo User (email: demo@financetrack.test / password: Demo123!)
INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role`, `avatar`)
VALUES (1, 'Fikri Juana', 'demo@financetrack.test', '$2y$10$qtyNunthLTHr0PG0scgSVOe5p2TVlJBvv75WIcCev1pYaMYySpgcy', 'user', 'FP')
ON DUPLICATE KEY UPDATE `password_hash` = VALUES(`password_hash`), `name` = VALUES(`name`);

-- 2. Master Categories
INSERT INTO `categories` (`id`, `name`, `type`, `icon`, `color`) VALUES
('pemasukan-saku', 'Uang Saku', 'income', 'payments', '#10b981'),
('pemasukan-freelance', 'Freelance', 'income', 'work', '#0284c7'),
('pemasukan-beasiswa', 'Beasiswa', 'income', 'school', '#8b5cf6'),
('tabungan', 'Tabungan', 'income', 'savings', '#10b981'),
('makanan', 'Makanan', 'expense', 'restaurant', '#0284c7'),
('transportasi', 'Transportasi', 'expense', 'two_wheeler', '#0ea5e9'),
('internet', 'Internet', 'expense', 'wifi', '#0284c7'),
('listrik', 'Token Listrik', 'expense', 'bolt', '#f59e0b'),
('kuliah', 'Kuliah & Skripsi', 'expense', 'menu_book', '#8b5cf6'),
('kopi', 'Kopi & Belajar', 'expense', 'coffee', '#0ea5e9'),
('alat-tulis', 'Alat Tulis', 'expense', 'edit_note', '#8b5cf6'),
('hiburan', 'Hiburan', 'expense', 'movie', '#ec4899')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `icon` = VALUES(`icon`), `color` = VALUES(`color`);

-- 3. Initial Budgets for Demo User (Oktober 2024: month 10, year 2024)
INSERT INTO `budgets` (`user_id`, `category_id`, `amount`, `period_month`, `period_year`, `notes`) VALUES
(1, 'makanan', 1000000.00, 10, 2024, 'Makan & warteg'),
(1, 'transportasi', 500000.00, 10, 2024, 'Bensin & ojek'),
(1, 'internet', 200000.00, 10, 2024, 'Kuota internet'),
(1, 'listrik', 400000.00, 10, 2024, 'Token listrik kost'),
(1, 'kuliah', 400000.00, 10, 2024, 'Kebutuhan kuliah & tugas'),
(1, 'kopi', 300000.00, 10, 2024, 'Nugas & nongkrong produktif'),
(1, 'hiburan', 400000.00, 10, 2024, 'Nonton & healing')
ON DUPLICATE KEY UPDATE `amount` = VALUES(`amount`), `notes` = VALUES(`notes`);

-- 4. Initial Transactions for Demo User
INSERT INTO `transactions` (`id`, `user_id`, `category_id`, `type`, `amount`, `transaction_date`, `transaction_time`, `description`, `payment_method`, `notes`) VALUES
(1, 1, 'pemasukan-saku', 'income', 1500000.00, '2024-10-24', '12:30:00', 'Uang saku bulanan dari orang tua', 'tabungan', 'Transfer BCA dari Ibu'),
(2, 1, 'makanan', 'expense', 18000.00, '2024-10-24', '08:15:00', 'Makan siang warteg Mbak Sri', 'cash', 'Ayam kecap + sayur kangkung'),
(3, 1, 'kopi', 'expense', 28000.00, '2024-10-23', '19:30:00', 'Kopi dan belajar di perpustakaan', 'gopay', 'Kopi susu gula aren'),
(4, 1, 'kuliah', 'expense', 75000.00, '2024-10-23', '10:05:00', 'Fotokopi modul dan print skripsi', 'cash', 'Modul bab 1-3'),
(5, 1, 'pemasukan-freelance', 'income', 700000.00, '2024-10-22', '14:00:00', 'Honor asisten lab (part-time)', 'gopay', 'Honor praktikum semester ini'),
(6, 1, 'transportasi', 'expense', 35000.00, '2024-10-22', '09:00:00', 'Bensin Vario pertamax full', 'cash', 'SPBU Jalan Kaliurang'),
(7, 1, 'listrik', 'expense', 100000.00, '2024-10-21', '17:00:00', 'Beli token listrik tambahan', 'ovo', 'Token PLN prabayar'),
(8, 1, 'makanan', 'expense', 25000.00, '2024-10-20', '18:45:00', 'Makan malam nasi goreng pojok', 'cash', 'Nasi goreng kambing'),
(9, 1, 'internet', 'expense', 125000.00, '2024-10-19', '11:15:00', 'Beli paket kuota Telkomsel 25GB', 'gopay', 'Paket data bulanan'),
(10, 1, 'hiburan', 'expense', 80000.00, '2024-10-18', '20:10:00', 'Nonton bioskop sama teman', 'dana', 'Tiket XXI + popcorn'),
(11, 1, 'tabungan', 'income', 500000.00, '2024-10-17', '13:00:00', 'Setoran tabungan bulanan', 'tabungan', 'Tabungan masa depan'),
(12, 1, 'pemasukan-beasiswa', 'income', 1000000.00, '2024-10-16', '12:00:00', 'Beasiswa prestasi semester ganjil', 'tabungan', 'Beasiswa universitas'),
(13, 1, 'makanan', 'expense', 96000.00, '2024-10-15', '19:45:00', 'Makan malam together (patungan)', 'gopay', 'Patungan makan malam tim'),
(14, 1, 'transportasi', 'expense', 42000.00, '2024-10-14', '07:50:00', 'Bensin motor dan parkir kampus', 'cash', 'Pertamax dan parkir'),
(15, 1, 'kuliah', 'expense', 165000.00, '2024-10-13', '15:30:00', 'Print skripsi bab 2 dan bind', 'cash', 'Cetak hardcover'),
(16, 1, 'kopi', 'expense', 22000.00, '2024-10-12', '18:00:00', 'Kopi belajar di kedai dekat kampus', 'ovo', 'Americano dingin'),
(17, 1, 'alat-tulis', 'expense', 88000.00, '2024-10-11', '09:30:00', 'Beli bahan presentasi kelompok', 'dana', 'Kertas karton & spidol'),
(18, 1, 'makanan', 'expense', 21000.00, '2024-10-10', '08:20:00', 'Makan pagi dan sarapan di kos', 'cash', 'Soto ayam'),
(19, 1, 'internet', 'expense', 25000.00, '2024-10-09', '10:00:00', 'Top up pulsa dan paket data', 'gopay', 'Pulsa darurat'),
(20, 1, 'pemasukan-freelance', 'income', 500000.00, '2024-10-08', '16:00:00', 'Fee desain UI project teman', 'tabungan', 'Desain logo & landing page'),
(21, 1, 'transportasi', 'expense', 30000.00, '2024-10-07', '14:20:00', 'Ganti oli motor rutin', 'cash', 'Bengkel resmi'),
(22, 1, 'makanan', 'expense', 45000.00, '2024-10-06', '12:40:00', 'Makan siang fast food promo', 'gopay', 'Burger paket hemat'),
(23, 1, 'kuliah', 'expense', 10000.00, '2024-10-05', '09:00:00', 'Fotokopi materi kuliah web', 'cash', 'Bahan kuliah'),
(24, 1, 'kopi', 'expense', 32000.00, '2024-10-04', '16:15:00', 'Cold brew coffee saat tugas', 'ovo', 'Cold brew latte'),
(25, 1, 'listrik', 'expense', 200000.00, '2024-10-03', '11:00:00', 'Token listrik kosan awal bulan', 'dana', 'Token PLN awal bulan'),
(26, 1, 'pemasukan-saku', 'income', 500000.00, '2024-10-02', '10:00:00', 'Tambahan uang buku dari Kakak', 'tabungan', 'Transfer BCA'),
(27, 1, 'makanan', 'expense', 15000.00, '2024-10-01', '19:00:00', 'Makan malam mie dok dok', 'cash', 'Warmindo'),
(28, 1, 'transportasi', 'expense', 18000.00, '2024-09-28', '08:30:00', 'Bensin motor September', 'cash', 'Bensin pertalite'),
(29, 1, 'makanan', 'expense', 25000.00, '2024-09-27', '12:00:00', 'Makan siang kantin kampus', 'gopay', 'Nasi rames'),
(30, 1, 'kopi', 'expense', 20000.00, '2024-09-26', '17:00:00', 'Es kopi susu santai', 'ovo', 'Kopi susu tetangga'),
(31, 1, 'kuliah', 'expense', 27000.00, '2024-09-25', '14:00:00', 'Print laporan praktikum', 'gopay', 'Cetak 3 rangkap'),
(32, 1, 'listrik', 'expense', 300000.00, '2024-09-24', '16:30:00', 'Token listrik kost September', 'ovo', 'Token PLN'),
(33, 1, 'internet', 'expense', 150000.00, '2024-09-23', '10:00:00', 'Top up kuota internet 30 GB', 'dana', 'Paket internet'),
(34, 1, 'hiburan', 'expense', 85000.00, '2024-09-22', '20:00:00', 'Bioskop akhir pekan', 'dana', 'Tiket bioskop weekend'),
(35, 1, 'transportasi', 'expense', 33000.00, '2024-09-21', '08:00:00', 'Bensin motor ke kampus', 'cash', 'Bensin mingguan'),
(36, 1, 'kuliah', 'expense', 120000.00, '2024-09-20', '15:00:00', 'Beli buku pemrograman web', 'gopay', 'Buku referensi'),
(37, 1, 'pemasukan-saku', 'income', 1500000.00, '2024-09-01', '10:00:00', 'Uang saku September', 'tabungan', 'Transfer orang tua'),
(38, 1, 'makanan', 'expense', 23000.00, '2024-09-15', '13:00:00', 'Makan siang ayam geprek', 'cash', 'Ayam geprek level 2')
ON DUPLICATE KEY UPDATE `description` = VALUES(`description`), `amount` = VALUES(`amount`);

