-- ========================================
-- YPOK MANAGEMENT SYSTEM - MASTER DATABASE
-- ========================================
-- Database: ypok_management
-- Version: 3.0 COMPLETE
-- Created: 2026
-- Description: Database lengkap untuk sistem manajemen YPOK
-- ========================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ypok_management`
--
CREATE DATABASE IF NOT EXISTS `ypok_management` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `ypok_management`;

-- ========================================
-- DROP EXISTING TABLES (untuk clean install)
-- ========================================

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `sertifikasi_kohai`;
DROP TABLE IF EXISTS `prestasi_kohai`;
DROP TABLE IF EXISTS `sertifikasi_msh`;
DROP TABLE IF EXISTS `prestasi_msh`;
DROP TABLE IF EXISTS `transaksi_toko`;
DROP TABLE IF EXISTS `produk_variasi`;
DROP TABLE IF EXISTS `kategori_produk`;
DROP TABLE IF EXISTS `produk_toko`;
DROP TABLE IF EXISTS `pembayaran`;
DROP TABLE IF EXISTS `kegiatan`;
DROP TABLE IF EXISTS `pendaftaran_kohai`;
DROP TABLE IF EXISTS `pendaftaran_msh`;
DROP TABLE IF EXISTS `kohai`;
DROP TABLE IF EXISTS `master_sabuk_hitam`;
DROP TABLE IF EXISTS `pengurus`;
DROP TABLE IF EXISTS `legalitas`;
DROP TABLE IF EXISTS `informasi_yayasan`;
DROP TABLE IF EXISTS `dojo`;
DROP TABLE IF EXISTS `provinsi`;
DROP TABLE IF EXISTS `lokasi`;
DROP TABLE IF EXISTS `transaksi`;
DROP TABLE IF EXISTS `users`;

SET FOREIGN_KEY_CHECKS = 1;

-- ========================================
-- TABLE: users
-- Untuk autentikasi dan manajemen user
-- ========================================

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `foto_profil` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- TABLE: provinsi
-- Untuk data provinsi/wilayah
-- ========================================

CREATE TABLE IF NOT EXISTS `provinsi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_provinsi` varchar(100) NOT NULL,
  `kode_provinsi` varchar(10) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `total_dojo` int(11) DEFAULT 0 COMMENT 'Total dojo di provinsi ini',
  `total_anggota` int(11) DEFAULT 0 COMMENT 'Total semua anggota dari semua dojo',
  `anggota_aktif` int(11) DEFAULT 0 COMMENT 'Total anggota aktif dari semua dojo',
  `anggota_non_aktif` int(11) DEFAULT 0 COMMENT 'Total anggota non aktif dari semua dojo',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================
-- TABLE: dojo
-- Untuk data dojo/cabang per provinsi
-- ========================================

CREATE TABLE IF NOT EXISTS `dojo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `provinsi_id` int(11) NOT NULL,
  `nama_dojo` varchar(100) NOT NULL,
  `alamat` text DEFAULT NULL,
  `total_anggota` int(11) DEFAULT 0,
  `anggota_aktif` int(11) DEFAULT 0,
  `anggota_non_aktif` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `provinsi_id` (`provinsi_id`),
  CONSTRAINT `fk_dojo_provinsi` FOREIGN KEY (`provinsi_id`) REFERENCES `provinsi` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================
-- TABLE: lokasi
-- Untuk data lokasi kegiatan
-- ========================================

CREATE TABLE `lokasi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_lokasi` varchar(100) NOT NULL,
  `alamat` text DEFAULT NULL,
  `koordinat` varchar(100) DEFAULT NULL,
  `kota` varchar(50) DEFAULT NULL,
  `provinsi` varchar(50) DEFAULT NULL,
  `kapasitas` int(11) DEFAULT NULL,
  `fasilitas` text DEFAULT NULL,
  `status` enum('aktif','non-aktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================
-- TABLE: informasi_yayasan
-- Untuk data identitas yayasan
-- ========================================

CREATE TABLE IF NOT EXISTS `informasi_yayasan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_lengkap` varchar(255) NOT NULL,
  `nama_singkat` varchar(100) DEFAULT NULL,
  `tanggal_berdiri` date DEFAULT NULL,
  `status_hukum` varchar(100) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telepon` varchar(50) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `visi` text DEFAULT NULL,
  `misi` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================
-- TABLE: pengurus
-- Untuk data pengurus yayasan
-- ========================================

CREATE TABLE IF NOT EXISTS `pengurus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nik` varchar(50) DEFAULT NULL,
  `nama` varchar(255) NOT NULL,
  `tempat_lahir` varchar(100) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `jabatan` varchar(100) NOT NULL,
  `periode` varchar(50) DEFAULT NULL,
  `tanggal_sk` date DEFAULT NULL,
  `no_sk` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telepon` varchar(50) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `pendidikan_terakhir` varchar(100) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `foto_url` text DEFAULT NULL,
  `status` enum('Aktif','Tidak Aktif') DEFAULT 'Aktif',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================
-- TABLE: legalitas
-- Untuk dokumen legal yayasan
-- ========================================

CREATE TABLE IF NOT EXISTS `legalitas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `jenis_dokumen` varchar(100) NOT NULL,
  `nomor_dokumen` varchar(100) NOT NULL,
  `tanggal_terbit` date NOT NULL,
  `tanggal_kadaluarsa` date DEFAULT NULL,
  `instansi_penerbit` varchar(100) NOT NULL,
  `file_dokumen` varchar(255) DEFAULT NULL,
  `status` enum('Aktif','Kadaluarsa','Akan Kadaluarsa','Dalam Proses') DEFAULT 'Aktif',
  `keterangan` text DEFAULT NULL,
  `is_permanent` tinyint(1) DEFAULT 0 COMMENT '1=Permanen (tidak kadaluarsa)',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================
-- TABLE: master_sabuk_hitam (MSH)
-- Untuk data Master Sabuk Hitam (Dan 1-9)
-- ========================================

CREATE TABLE `master_sabuk_hitam` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `no_msh` varchar(50) DEFAULT NULL COMMENT 'Nomor MSH',
  `nama` varchar(255) NOT NULL,
  `tempat_lahir` varchar(100) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `jenis_kelamin` enum('L','P') DEFAULT 'L',
  `tingkat_dan` varchar(50) DEFAULT NULL COMMENT 'Dan 1 sampai Dan 9',
  `tanggal_lulus` date DEFAULT NULL,
  `nomor_ijazah` varchar(100) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `no_telp` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `dojo_cabang` varchar(100) DEFAULT NULL,
  `status` enum('Aktif','Tidak Aktif','Meninggal') DEFAULT 'Aktif',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_nama` (`nama`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- TABLE: prestasi_msh
-- Untuk prestasi Master Sabuk Hitam
-- ========================================

CREATE TABLE `prestasi_msh` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `msh_id` int(11) NOT NULL,
  `nama_prestasi` varchar(255) NOT NULL,
  `tanggal_prestasi` date DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `msh_id` (`msh_id`),
  CONSTRAINT `prestasi_msh_ibfk_1` FOREIGN KEY (`msh_id`) REFERENCES `master_sabuk_hitam` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- TABLE: sertifikasi_msh
-- Untuk sertifikasi MSH
-- ========================================

CREATE TABLE `sertifikasi_msh` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `msh_id` int(11) NOT NULL,
  `nama_sertifikasi` varchar(255) DEFAULT NULL,
  `nomor_sertifikat` varchar(100) DEFAULT NULL,
  `penerbit` varchar(100) DEFAULT NULL COMMENT 'YPOK, FORKI, dll',
  `level` varchar(50) DEFAULT NULL COMMENT 'Level sertifikasi (Dan 1-9)',
  `tanggal_terbit` date DEFAULT NULL,
  `tanggal_kadaluarsa` date DEFAULT NULL,
  `status` enum('valid','expired','permanent') DEFAULT 'valid',
  `file_sertifikat` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `msh_id` (`msh_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `sertifikasi_msh_ibfk_1` FOREIGN KEY (`msh_id`) REFERENCES `master_sabuk_hitam` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- TABLE: kohai
-- Untuk data siswa/kohai (Kyu 1-10)
-- ========================================

CREATE TABLE `kohai` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode_kohai` varchar(50) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `tempat_lahir` varchar(100) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `jenis_kelamin` enum('L','P') DEFAULT 'L',
  `tingkat_kyu` varchar(50) DEFAULT NULL COMMENT 'Kyu 1 sampai Kyu 10',
  `sabuk` varchar(50) DEFAULT NULL COMMENT 'Putih, Kuning, Orange, Hijau, Biru, Coklat',
  `dojo_cabang` varchar(100) DEFAULT NULL,
  `no_telp` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `status` enum('Aktif','Tidak Aktif','Meninggal') DEFAULT 'Aktif',
  `alamat` text DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `nama_wali` varchar(255) DEFAULT NULL COMMENT 'Nama orang tua/wali',
  `no_telp_wali` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_kohai` (`kode_kohai`),
  KEY `idx_nama` (`nama`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- TABLE: prestasi_kohai
-- Untuk prestasi siswa/kohai
-- ========================================

CREATE TABLE `prestasi_kohai` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kohai_id` int(11) NOT NULL,
  `nama_prestasi` varchar(255) NOT NULL,
  `tanggal_prestasi` date DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `kohai_id` (`kohai_id`),
  CONSTRAINT `prestasi_kohai_ibfk_1` FOREIGN KEY (`kohai_id`) REFERENCES `kohai` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- TABLE: sertifikasi_kohai
-- Untuk sertifikasi ujian kenaikan sabuk
-- ========================================

CREATE TABLE `sertifikasi_kohai` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kohai_id` int(11) NOT NULL,
  `nama_sertifikasi` varchar(255) DEFAULT NULL,
  `nomor_sertifikat` varchar(100) DEFAULT NULL,
  `penerbit` varchar(100) DEFAULT NULL COMMENT 'YPOK, FORKI, dll',
  `tanggal_terbit` date DEFAULT NULL,
  `tanggal_kadaluarsa` date DEFAULT NULL,
  `status` enum('valid','expired','permanent') DEFAULT 'valid',
  `file_sertifikat` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `kohai_id` (`kohai_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `sertifikasi_kohai_ibfk_1` FOREIGN KEY (`kohai_id`) REFERENCES `kohai` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- TABLE: pendaftaran_msh
-- Untuk pendaftaran MSH baru
-- ========================================

CREATE TABLE IF NOT EXISTS `pendaftaran_msh` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `no_msh` varchar(20) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `foto_msh` varchar(255) DEFAULT NULL,
  `tempat_lahir` varchar(50) NOT NULL,
  `tanggal_lahir` date NOT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL,
  `tingkat_dan` varchar(20) NOT NULL,
  `dojo_cabang` varchar(50) NOT NULL,
  `no_telp` varchar(15) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `alamat` text NOT NULL,
  `status` enum('Pending','Aktif','Tidak Aktif') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================
-- TABLE: pendaftaran_kohai
-- Untuk pendaftaran kohai baru
-- ========================================

CREATE TABLE IF NOT EXISTS `pendaftaran_kohai` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `no_kohai` varchar(20) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `foto_kohai` varchar(255) DEFAULT NULL,
  `tempat_lahir` varchar(50) NOT NULL,
  `tanggal_lahir` date NOT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL,
  `no_telp` varchar(15) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `alamat` text NOT NULL,
  `nama_wali` varchar(100) NOT NULL,
  `no_telp_wali` varchar(15) NOT NULL,
  `status` enum('Pending','Aktif','Tidak Aktif') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================
-- TABLE: pembayaran
-- Untuk transaksi pembayaran
-- ========================================

CREATE TABLE IF NOT EXISTS `pembayaran` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_kohai` varchar(255) NOT NULL COMMENT 'Nama anggota yang melakukan pembayaran',
  `kategori` varchar(100) NOT NULL COMMENT 'Kategori pembayaran: Ujian, Kyu, Rakernas, dll',
  `jumlah` decimal(15,2) NOT NULL COMMENT 'Jumlah pembayaran',
  `tanggal_bayar` date NOT NULL,
  `metode_pembayaran` varchar(50) DEFAULT NULL COMMENT 'Transfer, Tunai, dll',
  `bukti_pembayaran` varchar(255) DEFAULT NULL COMMENT 'Path file bukti',
  `status` enum('Lunas','Pending','Belum Bayar') DEFAULT 'Pending',
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_kategori` (`kategori`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================
-- TABLE: transaksi
-- Untuk laporan keuangan yayasan
-- ========================================

CREATE TABLE IF NOT EXISTS `transaksi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tanggal` date NOT NULL,
  `jenis` enum('pemasukan','pengeluaran') NOT NULL,
  `kategori` varchar(100) DEFAULT NULL,
  `keterangan` text NOT NULL,
  `jumlah` decimal(15,2) NOT NULL,
  `saldo` decimal(15,2) DEFAULT 0.00,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tanggal` (`tanggal`),
  KEY `idx_jenis` (`jenis`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `fk_transaksi_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- TABLE: kegiatan
-- Untuk data kegiatan/event
-- ========================================

CREATE TABLE IF NOT EXISTS `kegiatan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_kegiatan` varchar(255) NOT NULL,
  `jenis_kegiatan` varchar(100) DEFAULT NULL,
  `tanggal_kegiatan` date NOT NULL,
  `waktu_mulai` time DEFAULT NULL,
  `waktu_selesai` time DEFAULT NULL,
  `lokasi_id` int(11) DEFAULT NULL,
  `lokasi_nama` varchar(255) DEFAULT NULL,
  `peserta_msh` text DEFAULT NULL COMMENT 'JSON array ID MSH',
  `peserta_kohai` text DEFAULT NULL COMMENT 'JSON array ID Kohai',
  `biaya` decimal(15,2) DEFAULT 0,
  `dokumentasi` text DEFAULT NULL COMMENT 'Path file dokumentasi',
  `keterangan` text DEFAULT NULL,
  `tampil_di_berita` boolean DEFAULT FALSE COMMENT 'Tampilkan di guest dashboard',
  `foto` varchar(255) DEFAULT NULL COMMENT 'Path file foto kegiatan',
  `status` enum('Akan Datang','Terlaksana','Dibatalkan') DEFAULT 'Akan Datang',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `lokasi_id` (`lokasi_id`),
  KEY `idx_tampil_di_berita` (`tampil_di_berita`),
  CONSTRAINT `fk_kegiatan_lokasi` FOREIGN KEY (`lokasi_id`) REFERENCES `lokasi` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================
-- TABLE: kategori_produk
-- Untuk kategori produk toko
-- ========================================

CREATE TABLE IF NOT EXISTS `kategori_produk` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_kategori` varchar(100) NOT NULL UNIQUE,
  `deskripsi` text DEFAULT NULL,
  `icon` varchar(50) DEFAULT '📦',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================
-- TABLE: produk_toko
-- Untuk produk toko
-- ========================================

CREATE TABLE IF NOT EXISTS `produk_toko` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode_produk` varchar(50) NOT NULL,
  `nama_produk` varchar(255) NOT NULL,
  `kategori` varchar(100) DEFAULT NULL,
  `harga` decimal(15,2) NOT NULL,
  `stok` int(11) NOT NULL DEFAULT 0,
  `has_variasi` tinyint(1) DEFAULT 0 COMMENT '0=tidak ada variasi, 1=ada variasi',
  `status` varchar(50) DEFAULT 'Tersedia' COMMENT 'Tersedia, Pre Order, Habis',
  `deskripsi` text DEFAULT NULL,
  `spesifikasi` text DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_produk` (`kode_produk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================
-- TABLE: produk_variasi
-- Untuk variasi produk (ukuran, warna, dll)
-- ========================================

CREATE TABLE IF NOT EXISTS `produk_variasi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `produk_id` int(11) NOT NULL,
  `atribut_1` varchar(100) DEFAULT NULL COMMENT 'Nama atribut 1: Ukuran, Warna, dll',
  `nilai_1` varchar(100) DEFAULT NULL COMMENT 'Nilai atribut 1: S, M, L, Merah, dll',
  `atribut_2` varchar(100) DEFAULT NULL,
  `nilai_2` varchar(100) DEFAULT NULL,
  `stok` int(11) NOT NULL DEFAULT 0,
  `harga_tambahan` decimal(15,2) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `produk_id` (`produk_id`),
  CONSTRAINT `fk_produk_variasi` FOREIGN KEY (`produk_id`) REFERENCES `produk_toko` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================
-- TABLE: transaksi_toko
-- Untuk transaksi penjualan produk
-- ========================================

CREATE TABLE IF NOT EXISTS `transaksi_toko` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_transaksi` varchar(50) NOT NULL,
  `produk_id` int(11) NOT NULL,
  `pembeli` varchar(255) NOT NULL,
  `lokasi` varchar(255) NOT NULL,
  `alamat` text DEFAULT NULL,
  `jumlah` int(11) NOT NULL,
  `total_harga` decimal(15,2) NOT NULL,
  `metode_pembayaran` enum('Transfer','Tunai','E-Wallet') NOT NULL,
  `tanggal` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `catatan` text DEFAULT NULL,
  `variasi_info` varchar(255) DEFAULT NULL COMMENT 'Info variasi yang dibeli',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_transaksi` (`id_transaksi`),
  KEY `produk_id` (`produk_id`),
  CONSTRAINT `fk_transaksi_produk` FOREIGN KEY (`produk_id`) REFERENCES `produk_toko` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================
-- STORED PROCEDURES
-- ========================================

-- Procedure untuk update statistik provinsi
DELIMITER $$

DROP PROCEDURE IF EXISTS update_provinsi_stats$$

CREATE PROCEDURE update_provinsi_stats(IN p_provinsi_id INT)
BEGIN
    UPDATE provinsi 
    SET 
        total_dojo = (
            SELECT COUNT(*) 
            FROM dojo 
            WHERE provinsi_id = p_provinsi_id
        ),
        total_anggota = (
            SELECT COALESCE(SUM(total_anggota), 0) 
            FROM dojo 
            WHERE provinsi_id = p_provinsi_id
        ),
        anggota_aktif = (
            SELECT COALESCE(SUM(anggota_aktif), 0) 
            FROM dojo 
            WHERE provinsi_id = p_provinsi_id
        ),
        anggota_non_aktif = (
            SELECT COALESCE(SUM(anggota_non_aktif), 0) 
            FROM dojo 
            WHERE provinsi_id = p_provinsi_id
        )
    WHERE id = p_provinsi_id;
END$$

DELIMITER ;

-- ========================================
-- TRIGGERS
-- ========================================

-- Trigger untuk INSERT dojo
DELIMITER $$

DROP TRIGGER IF EXISTS after_dojo_insert$$

CREATE TRIGGER after_dojo_insert
AFTER INSERT ON dojo
FOR EACH ROW
BEGIN
    CALL update_provinsi_stats(NEW.provinsi_id);
END$$

DELIMITER ;

-- Trigger untuk UPDATE dojo
DELIMITER $$

DROP TRIGGER IF EXISTS after_dojo_update$$

CREATE TRIGGER after_dojo_update
AFTER UPDATE ON dojo
FOR EACH ROW
BEGIN
    IF OLD.provinsi_id != NEW.provinsi_id THEN
        CALL update_provinsi_stats(OLD.provinsi_id);
    END IF;
    CALL update_provinsi_stats(NEW.provinsi_id);
END$$

DELIMITER ;

-- Trigger untuk DELETE dojo
DELIMITER $$

DROP TRIGGER IF EXISTS after_dojo_delete$$

CREATE TRIGGER after_dojo_delete
AFTER DELETE ON dojo
FOR EACH ROW
BEGIN
    CALL update_provinsi_stats(OLD.provinsi_id);
END$$

DELIMITER ;

-- ========================================
-- INSERT DEFAULT DATA
-- ========================================

-- Default Admin User
INSERT INTO `users` (`username`, `password`, `nama_lengkap`, `email`, `role`, `status`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator YPOK', 'admin@ypok.com', 'admin', 'active');

-- Default Kategori Produk
INSERT INTO `kategori_produk` (`nama_kategori`, `deskripsi`, `icon`) VALUES
('Seragam (Karate Gi)', 'Pakaian latihan karate resmi', '🥋'),
('Pelindung', 'Alat pelindung tubuh untuk latihan', '🛡️'),
('Senjata Latihan', 'Peralatan senjata untuk latihan kata', '⚔️'),
('Sabuk', 'Sabuk tingkat karate', '🥋'),
('Merchandise', 'Produk merchandise YPOK', '🎁'),
('Aksesoris', 'Aksesoris pendukung latihan', '👕'),
('Perlengkapan', 'Perlengkapan latihan lainnya', '🎒');

-- Sample Transaksi Keuangan
INSERT INTO `transaksi` (`tanggal`, `jenis`, `kategori`, `keterangan`, `jumlah`, `saldo`, `created_by`) VALUES
('2024-01-01', 'pemasukan', 'Donasi', 'Donasi dari Alumni', 5000000.00, 5000000.00, 1),
('2024-01-05', 'pengeluaran', 'Operasional', 'Pembelian ATK', 500000.00, 4500000.00, 1),
('2024-01-10', 'pemasukan', 'Infaq', 'Infaq Jumat', 1000000.00, 5500000.00, 1),
('2024-01-15', 'pengeluaran', 'Konsumsi', 'Konsumsi Rapat', 300000.00, 5200000.00, 1);

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- ========================================
-- DATABASE UPDATES & MODIFICATIONS
-- ========================================
-- Bagian ini berisi update struktur tabel untuk fitur terbaru
-- ========================================

-- ========================================
-- UPDATE 1: Tabel Master Sabuk Hitam (MSH)
-- Tanggal: 2026-03-04
-- ========================================

-- Tambahkan field tanggal_ujian dan jenis_keanggotaan
ALTER TABLE `master_sabuk_hitam` 
ADD COLUMN `tanggal_ujian` DATE NULL AFTER `nomor_ijazah`,
ADD COLUMN `jenis_keanggotaan` VARCHAR(50) DEFAULT 'Reguler' AFTER `tanggal_ujian`;

-- Update data existing dengan default value
UPDATE `master_sabuk_hitam` 
SET `jenis_keanggotaan` = 'Reguler' 
WHERE `jenis_keanggotaan` IS NULL;

-- Komentar untuk field
ALTER TABLE `master_sabuk_hitam` 
MODIFY COLUMN `tanggal_ujian` DATE NULL COMMENT 'Tanggal ujian kenaikan dan',
MODIFY COLUMN `jenis_keanggotaan` VARCHAR(50) DEFAULT 'Reguler' COMMENT 'Jenis keanggotaan: Reguler, Khusus, Kehormatan';

-- ========================================
-- UPDATE 2: Tabel Kohai
-- Tanggal: 2026-03-04
-- ========================================

-- Tambahkan field baru yang diperlukan
ALTER TABLE `kohai` 
ADD COLUMN `no_registrasi_ijazah` VARCHAR(255) NULL AFTER `kode_kohai`,
ADD COLUMN `tanggal_ujian` DATE NULL AFTER `sabuk`,
ADD COLUMN `asal_sekolah` VARCHAR(255) NULL AFTER `dojo_cabang`,
ADD COLUMN `asal_provinsi` VARCHAR(255) NULL AFTER `asal_sekolah`,
ADD COLUMN `keterangan` TEXT NULL AFTER `alamat`;

-- Update data existing dengan default value jika diperlukan
UPDATE `kohai` 
SET `asal_provinsi` = `dojo_cabang` 
WHERE `asal_provinsi` IS NULL AND `dojo_cabang` IS NOT NULL;

-- Komentar untuk field
ALTER TABLE `kohai` 
MODIFY COLUMN `no_registrasi_ijazah` VARCHAR(255) NULL COMMENT 'Nomor registrasi ijazah (bisa multiple, pisahkan dengan spasi)',
MODIFY COLUMN `tanggal_ujian` DATE NULL COMMENT 'Tanggal ujian terakhir',
MODIFY COLUMN `asal_sekolah` VARCHAR(255) NULL COMMENT 'Cabang/Asal sekolah/Ranting',
MODIFY COLUMN `asal_provinsi` VARCHAR(255) NULL COMMENT 'Asal provinsi/kab/kota',
MODIFY COLUMN `keterangan` TEXT NULL COMMENT 'Keterangan tambahan';

-- ========================================
-- UPDATE 3: Tabel Pembayaran
-- Tanggal: 2026-02-12
-- ========================================

-- Tambah kolom nama_kohai sebagai pengganti kohai_id (untuk input nama universal)
ALTER TABLE `pembayaran`
ADD COLUMN IF NOT EXISTS `total_tagihan` DECIMAL(15,2) DEFAULT NULL AFTER `jumlah`;

ALTER TABLE `pembayaran`
ADD COLUMN IF NOT EXISTS `nominal_dibayar` DECIMAL(15,2) DEFAULT NULL AFTER `total_tagihan`;

ALTER TABLE `pembayaran`
ADD COLUMN IF NOT EXISTS `sisa` DECIMAL(15,2) DEFAULT NULL AFTER `nominal_dibayar`;

-- Note: kolom nama_kohai sudah ada di struktur utama
-- Migrasi data existing: copy nama dari tabel kohai ke nama_kohai jika diperlukan
-- UPDATE pembayaran p
-- LEFT JOIN kohai k ON p.kohai_id = k.id
-- SET p.nama_kohai = k.nama
-- WHERE p.kohai_id IS NOT NULL AND p.nama_kohai IS NULL;

-- ========================================
-- END OF DATABASE STRUCTURE & UPDATES
-- ========================================
--
-- PETUNJUK INSTALASI:
-- ====================
--
-- 1. Buka phpMyAdmin: http://localhost/phpmyadmin
-- 2. Import file ini melalui tab "Import"
-- 3. Database akan otomatis dibuat dengan semua tabel DAN update struktur
--
-- LOGIN PERTAMA KALI:
-- Username: admin
-- Password: admin123
--
-- TABEL YANG DIBUAT (25 tabel):
-- ✓ users
-- ✓ provinsi
-- ✓ dojo
-- ✓ lokasi
-- ✓ informasi_yayasan
-- ✓ pengurus
-- ✓ legalitas
-- ✓ master_sabuk_hitam (dengan field baru: tanggal_ujian, jenis_keanggotaan)
-- ✓ prestasi_msh
-- ✓ sertifikasi_msh
-- ✓ kohai (dengan field baru: no_registrasi_ijazah, tanggal_ujian, asal_sekolah, asal_provinsi, keterangan)
-- ✓ prestasi_kohai
-- ✓ sertifikasi_kohai
-- ✓ pendaftaran_msh
-- ✓ pendaftaran_kohai
-- ✓ pembayaran (dengan field: total_tagihan, nominal_dibayar, sisa)
-- ✓ transaksi
-- ✓ kegiatan
-- ✓ kategori_produk
-- ✓ produk_toko
-- ✓ produk_variasi
-- ✓ transaksi_toko
--
-- FITUR:
-- ✅ Foreign Key Constraints dengan CASCADE
-- ✅ Indexing untuk performa optimal
-- ✅ UTF8MB4 Support
-- ✅ Timestamp Tracking
-- ✅ ENUM untuk validasi data
-- ✅ Stored Procedures untuk agregat provinsi
-- ✅ Triggers otomatis untuk update statistik
-- ✅ UPDATE: Field baru untuk MSH (tanggal_ujian, jenis_keanggotaan)
-- ✅ UPDATE: Field baru untuk Kohai (no_registrasi_ijazah, tanggal_ujian, asal_sekolah, asal_provinsi, keterangan)
-- ✅ UPDATE: Field baru untuk Pembayaran (total_tagihan, nominal_dibayar, sisa)
--
-- ========================================
