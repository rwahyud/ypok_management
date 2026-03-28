-- ========================================
-- DUMMY DATA FOR YPOK MANAGEMENT SYSTEM
-- For testing all admin features
-- ========================================

-- Clear existing data (be careful!)
-- DELETE FROM users;
-- DELETE FROM master_sabuk_hitam;
-- DELETE FROM kohai;
-- DELETE FROM pembayaran;
-- DELETE FROM kegiatan;
-- DELETE FROM lokasi;
-- DELETE FROM pengurus;
-- DELETE FROM legalitas;
-- DELETE FROM transaksi;
-- DELETE FROM provinsi;

-- ========================================
-- 1. USERS DATA
-- ========================================
INSERT INTO `users` (`username`, `password`, `nama_lengkap`, `email`, `role`, `status`) VALUES
('admin', '$2y$10$YIjlrPNo0.U7.x0q5P9oxe.Zr7w5n/d8IvlrEE9O7k7ZE9q9mPDUm', 'Admin YPOK', 'admin@ypok.id', 'admin', 'active'),
('user1', '$2y$10$YIjlrPNo0.U7.x0q5P9oxe.Zr7w5n/d8IvlrEE9O7k7ZE9q9mPDUm', 'User Test', 'user@ypok.id', 'user', 'active');

-- ========================================
-- 2. PROVINSI DATA
-- ========================================
INSERT INTO `provinsi` (`nama_provinsi`, `kode_provinsi`, `total_dojo`, `total_anggota`, `anggota_aktif`, `anggota_non_aktif`) VALUES
('Jawa Timur', 'JT', 5, 150, 120, 30),
('Jawa Tengah', 'JT', 3, 90, 75, 15),
('DKI Jakarta', 'JK', 4, 110, 95, 15),
('Jawa Barat', 'JB', 6, 200, 170, 30),
('Sumatera Utara', 'SU', 2, 60, 50, 10);

-- ========================================
-- 3. DOJO DATA
-- ========================================
INSERT INTO `dojo` (`provinsi_id`, `nama_dojo`, `alamat`, `total_anggota`, `anggota_aktif`, `anggota_non_aktif`) VALUES
(1, 'Dojo Surabaya Pusat', 'Jl. Diponegoro No. 123, Surabaya', 50, 45, 5),
(1, 'Dojo Ngagel', 'Jl. Ngagel Raya No. 456, Surabaya', 40, 35, 5),
(2, 'Dojo Semarang', 'Jl. Pandanaran No. 789, Semarang', 40, 35, 5),
(3, 'Dojo Jakarta Pusat', 'Jl. Merdeka Barat No. 100, Jakarta', 50, 45, 5),
(4, 'Dojo Bandung', 'Jl. Dipenogoro No. 200, Bandung', 60, 55, 5);

-- ========================================
-- 4. LOKASI DATA
-- ========================================
INSERT INTO `lokasi` (`nama_lokasi`, `alamat`, `kota`, `provinsi`, `kapasitas`, `fasilitas`, `status`) VALUES
('Gelanggang Olahraga Surabaya', 'Jl. Raya Darmo, Surabaya', 'Surabaya', 'Jawa Timur', 300, 'Mat karate, Toilet, Ruang ganti, Air minum', 'aktif'),
('Aula Olahraga Semarang', 'Jl. Jenderal Sudirman, Semarang', 'Semarang', 'Jawa Tengah', 250, 'Mat karate, AC, Toilet, Ruang VIP', 'aktif'),
('Istora Senayan', 'Jl. Asia Afrika No. 2, Jakarta', 'Jakarta', 'DKI Jakarta', 500, 'Mat karate internasional, Toilet modern, Ruang pers', 'aktif'),
('Gedung Olahraga Bandung', 'Jl. Otista No. 17, Bandung', 'Bandung', 'Jawa Barat', 400, 'Mat karate, Tribun penonton, Toilet', 'aktif'),
('Kompleks Olahraga Medan', 'Jl. Gatot Subroto, Medan', 'Medan', 'Sumatera Utara', 200, 'Mat karate, Ruang latihan', 'aktif');

-- ========================================
-- 5. MASTER SABUK HITAM (MSH) DATA
-- ========================================
INSERT INTO `master_sabuk_hitam` (`no_msh`, `nama`, `tempat_lahir`, `tanggal_lahir`, `jenis_kelamin`, `tingkat_dan`, `tanggal_lulus`, `nomor_ijazah`, `alamat`, `no_telp`, `email`, `dojo_cabang`, `status`) VALUES
('MSH001', 'Budi Santoso', 'Jakarta', '1965-03-15', 'L', 'Dan 5', '1995-06-20', 'IJAZAH/1995/001', 'Jl. Merdeka No. 1, Jakarta', '081234567890', 'budi@ypok.id', 'Dojo Jakarta Pusat', 'Aktif'),
('MSH002', 'Siti Nurhaliza', 'Surabaya', '1970-07-22', 'P', 'Dan 3', '2000-11-15', 'IJAZAH/2000/002', 'Jl. Diponegoro No. 50, Surabaya', '081345678901', 'siti@ypok.id', 'Dojo Surabaya Pusat', 'Aktif'),
('MSH003', 'Ahmad Wijaya', 'Bandung', '1968-01-30', 'L', 'Dan 4', '1998-08-10', 'IJAZAH/1998/003', 'Jl. Ahmad Yani No. 100, Bandung', '081456789012', 'ahmad@ypok.id', 'Dojo Bandung', 'Aktif'),
('MSH004', 'Dewi Lestari', 'Semarang', '1972-11-05', 'P', 'Dan 2', '2005-05-25', 'IJAZAH/2005/004', 'Jl. Pandanaran No. 75, Semarang', '081567890123', 'dewi@ypok.id', 'Dojo Semarang', 'Aktif'),
('MSH005', 'Rudi Hartono', 'Medan', '1975-06-18', 'L', 'Dan 1', '2008-02-14', 'IJAZAH/2008/005', 'Jl. Gatot Subroto No. 120, Medan', '081678901234', 'rudi@ypok.id', 'Dojo Medan', 'Aktif');

-- ========================================
-- 6. PRESTASI MSH DATA
-- ========================================
INSERT INTO `prestasi_msh` (`msh_id`, `nama_prestasi`, `tanggal_prestasi`, `keterangan`) VALUES
(1, 'Juara 1 Turnamen Nasional 2015', '2015-08-20', 'Kategori Senior'),
(1, 'Pelatih Terbaik Jatim 2018', '2018-10-15', 'Penghargaan'),
(2, 'Finalis Kejuaraan Dunia 2012', '2012-11-05', 'Kategori Putri'),
(3, 'Juara 2 Kejuaraan Jawa Barat 2019', '2019-09-10', 'Kategori Senior'),
(4, 'Hakim Resmi FORKI', '2016-05-20', 'Sertifikasi');

-- ========================================
-- 7. KOHAI DATA
-- ========================================
INSERT INTO `kohai` (`kode_kohai`, `nama`, `tempat_lahir`, `tanggal_lahir`, `jenis_kelamin`, `tingkat_kyu`, `sabuk`, `dojo_cabang`, `no_telp`, `email`, `status`, `alamat`, `nama_wali`, `no_telp_wali`) VALUES
('KOHAI001', 'Andi Pratama', 'Jakarta', '2008-05-12', 'L', 'Kyu 3', 'Kuning', 'Dojo Jakarta Pusat', '082112345678', 'andi@school.id', 'Aktif', 'Jl. Merdeka No. 10', 'Bambang Sutrisno', '081234567890'),
('KOHAI002', 'Siti Mariah', 'Surabaya', '2009-08-25', 'P', 'Kyu 5', 'Putih', 'Dojo Surabaya Pusat', '082223456789', 'siti@school.id', 'Aktif', 'Jl. Diponegoro No. 55', 'Sari Wulandari', '081345678901'),
('KOHAI003', 'Doni Setiawan', 'Bandung', '2007-03-18', 'L', 'Kyu 1', 'Orange', 'Dojo Bandung', '082334567890', 'doni@school.id', 'Aktif', 'Jl. Ahmad Yani No. 120', 'Joni Setiawan', '081456789012'),
('KOHAI004', 'Lina Kusuma', 'Semarang', '2010-01-08', 'P', 'Kyu 7', 'Putih', 'Dojo Semarang', '082445678901', 'lina@school.id', 'Aktif', 'Jl. Pandanaran No. 88', 'Hendra Kusuma', '081567890123'),
('KOHAI005', 'Rinto Setiabudi', 'Medan', '2008-11-30', 'L', 'Kyu 4', 'Kuning', 'Dojo Medan', '082556789012', 'rinto@school.id', 'Aktif', 'Jl. Gatot Subroto No. 150', 'Bambang Setiabudi', '081678901234'),
('KOHAI006', 'Maya Windasari', 'Jakarta', '2009-04-14', 'P', 'Kyu 2', 'Orange', 'Dojo Jakarta Pusat', '082667890123', 'maya@school.id', 'Aktif', 'Jl. Merdeka No. 20', 'Tri Windasari', '081312345678'),
('KOHAI007', 'Hafiz Maulana', 'Surabaya', '2007-09-22', 'L', 'Kyu 6', 'Putih', 'Dojo Surabaya Pusat', '082778901234', 'hafiz@school.id', 'Aktif', 'Jl. Diponegoro No. 65', 'Maulana Rauf', '081423456789');

-- ========================================
-- 8. PRESTASI KOHAI DATA
-- ========================================
INSERT INTO `prestasi_kohai` (`kohai_id`, `nama_prestasi`, `tanggal_prestasi`, `keterangan`) VALUES
(1, 'Juara 3 Turnamen Karate Sekolah 2023', '2023-08-15', 'Kategori U-15'),
(2, 'Peserta Kejuaraan Jatim 2023', '2023-10-10', 'Kategori Putri U-12'),
(3, 'Juara 1 Latihan Rutin 2024', '2024-02-20', 'Kumite Putra'),
(4, 'Sertifikat Kenaikan Sabuk', '2024-03-15', 'Ujian Kyu 7 -> Kyu 6');

-- ========================================
-- 9. PENGURUS DATA
-- ========================================
INSERT INTO `pengurus` (`nik`, `nama`, `tempat_lahir`, `tanggal_lahir`, `jabatan`, `periode`, `tanggal_sk`, `no_sk`, `email`, `telepon`, `alamat`, `pendidikan_terakhir`, `status`) VALUES
('1234567890123456', 'Ir. Bambang Sutrisno', 'Jakarta', '1960-03-15', 'Ketua Umum', '2020-2025', '2020-01-15', 'SK-001/YPOK/2020', 'bambang@ypok.id', '081234567890', 'Jl. Gatot Subroto No. 1, Jakarta', 'S2 Teknik', 'Aktif'),
('2234567890123456', 'Dra. Siti Rahayu', 'Surabaya', '1965-07-20', 'Bendahara', '2020-2025', '2020-01-15', 'SK-002/YPOK/2020', 'siti@ypok.id', '081345678901', 'Jl. Diponegoro No. 10, Surabaya', 'S1 Akuntansi', 'Aktif'),
('3234567890123456', 'Budi Santoso', 'Jakarta', '1968-05-10', 'Sekretaris', '2020-2025', '2020-01-15', 'SK-003/YPOK/2020', 'budi.s@ypok.id', '081456789012', 'Jl. Merdeka No. 5, Jakarta', 'S1 Hukum', 'Aktif'),
('4234567890123456', 'Dr. Ahmad Wijaya, M.Si', 'Bandung', '1962-11-25', 'Bidang Olahraga', '2020-2025', '2020-02-01', 'SK-004/YPOK/2020', 'ahmad@ypok.id', '081567890123', 'Jl. Ahmad Yani No. 25, Bandung', 'S3 Olahraga', 'Aktif'),
('5234567890123456', 'Dra. Winda Sari', 'Semarang', '1963-06-12', 'Bidang Pendidikan', '2020-2025', '2020-02-01', 'SK-005/YPOK/2020', 'winda@ypok.id', '081678901234', 'Jl. Diag Soekarno Hatta No. 15, Semarang', 'S1 Pendidikan', 'Aktif');

-- ========================================
-- 10. LEGALITAS DATA
-- ========================================
INSERT INTO `legalitas` (`jenis_dokumen`, `nomor_dokumen`, `tanggal_terbit`, `tanggal_kadaluarsa`, `instansi_penerbit`, `status`, `keterangan`, `is_permanent`) VALUES
('Akta Notaris Pendirian', 'AKT/2010/001', '2010-01-20', NULL, 'Notaris Bambang Sutrisno', 'Aktif', 'Akta Pendirian YPOK', 1),
('Surat Keputusan Kemenkumham', 'SK/KMH/2010/5678', '2010-02-15', NULL, 'Kemenkumham RI', 'Aktif', 'Pengesahan Badan Hukum', 1),
('NPWP Yayasan', 'NPWP-YPN-00001', '2010-03-01', '2025-03-01', 'DJP', 'Aktif', 'NPWP Terdaftar', 0),
('Izin Operasional', 'IOP/PEMKOT/2023/001', '2023-01-10', '2025-01-10', 'Pemkot Jakarta', 'Aktif', 'Izin Operasional', 0),
('Sertifikat ISO', 'ISO/2024/5678', '2024-01-15', '2027-01-15', 'BSN', 'Aktif', 'Sertifikasi ISO 9001', 0),
('Izin Pajak', 'IJP/2023/0001', '2023-06-01', '2025-06-01', 'KPP', 'Akan Kadaluarsa', 'Akan kadaluarsa dalam 6 bulan', 0);

-- ========================================
-- 11. PEMBAYARAN DATA
-- ========================================
INSERT INTO `pembayaran` (`kohai_id`, `kategori`, `jumlah`, `tanggal_bayar`, `metode_pembayaran`, `status`, `keterangan`) VALUES
(1, 'Pendaftaran', 100000, '2024-01-15', 'Transfer Bank', 'Lunas', 'Pendaftaran kohai baru'),
(1, 'SPP Januari', 50000, '2024-01-20', 'Tunai', 'Lunas', 'SPP bulan Januari'),
(1, 'SPP Februari', 50000, '2024-02-18', 'Transfer Bank', 'Lunas', 'SPP bulan Februari'),
(2, 'Pendaftaran', 100000, '2024-01-25', 'Transfer Bank', 'Lunas', 'Pendaftaran kohai baru'),
(2, 'SPP Januari', 50000, '2024-01-28', 'Tunai', 'Lunas', 'SPP bulan Januari'),
(3, 'Pendaftaran', 150000, '2024-02-01', 'Transfer Bank', 'Lunas', 'Pendaftaran kohai cluster Bandung'),
(3, 'SPP Februari', 50000, '2024-02-15', 'Transfer Bank', 'Lunas', 'SPP bulan Februari'),
(4, 'Pendaftaran', 100000, '2024-02-10', 'Tunai', 'Lunas', 'Pendaftaran'),
(5, 'SPP Januari', 50000, '2024-01-30', 'Transfer Bank', 'Belum Lunas', 'Belum membayar'),
(6, 'Pendaftaran', 100000, '2024-03-01', 'Transfer Bank', 'Lunas', 'Pendaftaran'),
(6, 'SPP Maret', 50000, '2024-03-10', 'Tunai', 'Lunas', 'SPP bulan Maret'),
(7, 'SPP Februari', 50000, '2024-02-25', 'Transfer Bank', 'Lunas', 'SPP bulan Februari');

-- ========================================
-- 12. KEGIATAN DATA
-- ========================================
INSERT INTO `kegiatan` (`nama_kegiatan`, `deskripsi`, `tanggal_kegiatan`, `jam_mulai`, `jam_selesai`, `lokasi_id`, `kategori`, `jumlah_peserta`, `status`, `tampil_di_berita`) VALUES
('Latihan Rutin Bulan Maret', 'Latihan rutin untuk semua level kohai', '2024-03-15', '16:00', '18:00', 1, 'Latihan', 45, 'dijadwalkan', 1),
('Ujian Kenaikan Sabuk', 'Ujian kenaikan sabuk untuk kohai level Kyu 4-6', '2024-03-20', '08:00', '12:00', 1, 'Ujian', 30, 'dijadwalkan', 1),
('Turnamen Karate Antar Dojo', 'Kompetisi karate tingkat nasional', '2024-04-10', '07:00', '17:00', 3, 'Kompetisi', 200, 'akan datang', 1),
('Workshop Pelatihan', 'Workshop untuk meningkatkan skill pelatih', '2024-03-25', '09:00', '16:00', 2, 'Workshop', 50, 'dijadwalkan', 0),
('Latihan Intensif Persiapan', 'Latihan intensif persiapan kompetisi', '2024-04-01', '15:00', '17:30', 1, 'Latihan', 60, 'akan datang', 0);

-- ========================================
-- 13. TRANSAKSI DATA
-- ========================================
INSERT INTO `transaksi` (`jenis`, `jumlah`, `keterangan`, `kategori`, `status`) VALUES
('pemasukan', 500000, 'SPP kohai bulan Maret', 'SPP Kohai', 'selesai'),
('pemasukan', 250000, 'Donasi dari alumni', 'Donasi', 'selesai'),
('pengeluaran', 150000, 'Pembelian matras karate', 'Perlengkapan', 'selesai'),
('pengeluaran', 100000, 'Listrik dan air bulan Maret', 'Operasional', 'selesai'),
('pemasukan', 300000, 'Uang pendaftaran kohai baru', 'Pendaftaran', 'selesai'),
('pengeluaran', 200000, 'Gaji volunteer trainer', 'Gaji', 'selesai'),
('pemasukan', 400000, 'Sponsor turnamen', 'Sponsor', 'selesai'),
('pengeluaran', 75000, 'Perlengkapan kantor', 'Operasional', 'selesai');

-- ========================================
-- 14. INFORMASI YAYASAN
-- ========================================
INSERT INTO `informasi_yayasan` (`nama_lengkap`, `nama_singkat`, `tanggal_berdiri`, `status_hukum`, `alamat`, `email`, `telepon`, `website`, `visi`, `misi`) VALUES
('Yayasan Pendidikan Olahraga Karate', 'YPOK', '2010-01-10', 'Badan Hukum Yayasan', 'Jl. Gatot Subroto No. 1, Jakarta Selatan', 'info@ypok.id', '021-7234567', 'www.ypok.id', 
'Mewujudkan generasi yang sehat, disiplin, dan berbudi pekerti luhur melalui olahraga karate', 
'1. Menyelenggarakan pendidikan karate berkualitas\n2. Mengembangkan prestasi atlet karate\n3. Membangun karakter melalui olahraga\n4. Meningkatkan kesehatan masyarakat');

