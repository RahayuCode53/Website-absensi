-- ============================================================
-- Update Database: Tabel Pembimbing + Jurusan & Pembimbing di Users
-- Jalankan sekali via phpMyAdmin (Import atau SQL tab)
-- Password default ketiga pembimbing: pembimbing123
-- ============================================================

USE db_absensi_ptpn;

-- 1. Tabel pembimbing (3 pembimbing)
CREATE TABLE IF NOT EXISTS `pembimbing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(150) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Hapus data lama lalu isi (agar re-run aman)
DELETE FROM `pembimbing` WHERE `username` IN ('rini','afrizal','bobi');

-- Insert 3 pembimbing (password: pembimbing123)
INSERT INTO `pembimbing` (`nama`, `username`, `password`) VALUES
('Ibu Rini Hardiyanti Surahman, S.Kom., M.Kom.', 'rini', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Bapak Afrizal Yusuf RKT, S.Kom.', 'afrizal', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Ibu Bobi Nuna Yogita, S.E.', 'bobi', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- 2. Tambah kolom jurusan dan pembimbing_id di users
-- (Jika error "Duplicate column" = sudah dijalankan, lewati 2 baris ALTER di bawah)
ALTER TABLE `users` ADD COLUMN `jurusan` varchar(100) DEFAULT NULL;
ALTER TABLE `users` ADD COLUMN `pembimbing_id` int(11) DEFAULT NULL;

-- 3. Index dan foreign key (jika error "Duplicate" / "already exists", lewati)
ALTER TABLE `users` ADD KEY `pembimbing_id` (`pembimbing_id`);
ALTER TABLE `users` ADD CONSTRAINT `users_pembimbing_fk` FOREIGN KEY (`pembimbing_id`) REFERENCES `pembimbing` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
