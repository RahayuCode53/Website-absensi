-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 05, 2026 at 06:33 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `db_absensi_ptpn`
--
CREATE DATABASE IF NOT EXISTS `db_absensi_ptpn`;
USE `db_absensi_ptpn`;


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_absensi_ptpn`
--

-- --------------------------------------------------------

--
-- Table structure for table `absensi`
--

CREATE TABLE `absensi` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `tanggal` date DEFAULT curdate(),
  `jam_masuk` time DEFAULT NULL,
  `mood_masuk` varchar(50) DEFAULT NULL,
  `jam_pulang` time DEFAULT NULL,
  `mood_pulang` varchar(50) DEFAULT NULL,
  `foto_pulang` varchar(255) DEFAULT NULL,
  `foto_masuk` varchar(255) DEFAULT NULL,
  `lokasi_gps` varchar(255) DEFAULT NULL,
  `status` enum('hadir','izin','sakit') DEFAULT 'hadir',
  `keterangan` text DEFAULT NULL,
  `file_surat_sakit` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `absensi`
--

INSERT INTO `absensi` (`id`, `user_id`, `tanggal`, `jam_masuk`, `mood_masuk`, `jam_pulang`, `mood_pulang`, `foto_masuk`, `foto_pulang`, `lokasi_gps`, `status`, `keterangan`, `file_surat_sakit`) VALUES
(1, 1, '2026-02-04', '20:12:53', 'Senang 😄', '20:29:38', 'Lelah 😴', 'absen_masuk_1_1770210773.png', NULL, '3.5661115,98.649391', 'hadir', NULL, NULL),
(2, 1, '2026-02-04', NULL, NULL, '20:29:38', NULL, NULL, NULL, NULL, 'izin', 'bimbingan', ''),
(3, 1, '2026-02-04', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'sakit', 'demam', '1770211829_blok.drawio.png'),
(4, 1, '2026-02-04', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'sakit', 'demamm', '1770214947_blok.drawio.png'),
(5, 1, '2026-02-04', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'sakit', 'operasi', '1770215315_ChatGPT Image Dec 18, 2025, 12_52_56 PM.png'),
(6, 1, '2026-02-05', '11:30:08', 'Biasa 😐', NULL, NULL, 'absen_masuk_1_1770265808.png', NULL, '3.5867951196405756,98.64392224643825', 'hadir', NULL, NULL),
(7, 1, '2026-02-05', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'sakit', 'tipes', '1770266184_ChatGPT Image Dec 17, 2025, 09_34_30 PM.png'),
(8, 2, '2026-02-05', '11:38:42', 'Senang 😄', NULL, NULL, 'absen_masuk_2_1770266322.png', NULL, '3.5867951196405756,98.64392224643825', 'hadir', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `instansi` varchar(100) DEFAULT NULL,
  `kategori` enum('SMK','Universitas') NOT NULL,
  `role` enum('magang','pembimbing') DEFAULT 'magang',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama`, `foto`, `username`, `password`, `instansi`, `kategori`, `role`, `created_at`) VALUES
(1, 'Dia Rahayu', NULL, 'Ayu', '$2y$10$RkzqDO.TuzQv0wsIK9ndlulHslED/PihapU5TeskcRg9hZZ30nEYy', 'politeknik negri medan', 'Universitas', 'magang', '2026-02-04 13:10:21'),
(2, 'anjulina sidauruk', NULL, 'anju', '$2y$10$JZqZ0U79equApHQLknTTSOCmzvk60OFYCeveVAHPaRwAikgQrH0Y6', 'politeknik negri medan', 'Universitas', 'magang', '2026-02-05 03:00:02');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `absensi`
--
ALTER TABLE `absensi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `absensi`
--
ALTER TABLE `absensi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `absensi`
--
ALTER TABLE `absensi`
  ADD CONSTRAINT `absensi_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
