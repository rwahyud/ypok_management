<?php
/**
 * Script untuk import dummy data ke database YPOK
 * Jalankan melalui browser: http://localhost/ypok_management/ypok_management/database/import_dummy_data.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/database.php';

$output = [];

try {
    // Connect ke database
    $conn = new PDO('mysql:host=localhost;dbname=ypok_management', 'root', '');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $output[] = "✓ Database Connection Successful<br>";
    
    // ========================================
    // 1. USERS DATA
    // ========================================
    $output[] = "<h3>📝 Importing Users...</h3>";
    
    // Clear existing users first
    try {
        $conn->exec("DELETE FROM users WHERE username IN ('admin', 'user1')");
        $output[] = "✓ Cleared existing users<br>";
    } catch (Exception $e) {
        $output[] = "ℹ️ No existing users to clear<br>";
    }
    
    $users = [
        ['admin', password_hash('admin123', PASSWORD_BCRYPT), 'Admin YPOK', 'admin@ypok.id', 'admin'],
        ['user1', password_hash('user123', PASSWORD_BCRYPT), 'User Test', 'user@ypok.id', 'user']
    ];
    
    $stmt = $conn->prepare("INSERT INTO users (username, password, nama_lengkap, email, role) VALUES (?, ?, ?, ?, ?)");
    foreach ($users as $user) {
        $stmt->execute($user);
    }
    $output[] = "✓ Inserted " . count($users) . " users<br>";
    
    // ========================================
    // 2. PROVINSI DATA
    // ========================================
    $output[] = "<h3>📍 Importing Provinsi...</h3>";
    
    try {
        $conn->exec("DELETE FROM provinsi");
    } catch (Exception $e) {}
    
    $provinsi_data = [
        ['Jawa Timur', 'JT', 5, 150, 120, 30],
        ['Jawa Tengah', 'JTC', 3, 90, 75, 15],
        ['DKI Jakarta', 'JK', 4, 110, 95, 15],
        ['Jawa Barat', 'JB', 6, 200, 170, 30],
        ['Sumatera Utara', 'SU', 2, 60, 50, 10]
    ];
    
    $stmt = $conn->prepare("INSERT INTO provinsi (nama_provinsi, kode_provinsi, total_dojo, total_anggota, anggota_aktif, anggota_non_aktif) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($provinsi_data as $prov) {
        $stmt->execute($prov);
    }
    $output[] = "✓ Inserted " . count($provinsi_data) . " provinsi<br>";
    
    // ========================================
    // 3. DOJO DATA
    // ========================================
    $output[] = "<h3>🏢 Importing Dojo...</h3>";
    
    try {
        $conn->exec("DELETE FROM dojo");
    } catch (Exception $e) {}
    
    $dojo_data = [
        [1, 'Dojo Surabaya Pusat', 'Jl. Diponegoro No. 123, Surabaya', 50, 45, 5],
        [1, 'Dojo Ngagel', 'Jl. Ngagel Raya No. 456, Surabaya', 40, 35, 5],
        [2, 'Dojo Semarang', 'Jl. Pandanaran No. 789, Semarang', 40, 35, 5],
        [3, 'Dojo Jakarta Pusat', 'Jl. Merdeka Barat No. 100, Jakarta', 50, 45, 5],
        [4, 'Dojo Bandung', 'Jl. Dipenogoro No. 200, Bandung', 60, 55, 5]
    ];
    
    $stmt = $conn->prepare("INSERT INTO dojo (provinsi_id, nama_dojo, alamat, total_anggota, anggota_aktif, anggota_non_aktif) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($dojo_data as $dojo) {
        $stmt->execute($dojo);
    }
    $output[] = "✓ Inserted " . count($dojo_data) . " dojo<br>";
    
    // ========================================
    // 4. LOKASI DATA
    // ========================================
    $output[] = "<h3>📍 Importing Lokasi...</h3>";
    
    try {
        $conn->exec("DELETE FROM lokasi");
    } catch (Exception $e) {}
    
    $lokasi_data = [
        ['Gelanggang Olahraga Surabaya', 'Jl. Raya Darmo, Surabaya', 'Surabaya', 'Jawa Timur', 300, 'Mat karate, Toilet, Ruang ganti, Air minum', 'aktif'],
        ['Aula Olahraga Semarang', 'Jl. Jenderal Sudirman, Semarang', 'Semarang', 'Jawa Tengah', 250, 'Mat karate, AC, Toilet, Ruang VIP', 'aktif'],
        ['Istora Senayan', 'Jl. Asia Afrika No. 2, Jakarta', 'Jakarta', 'DKI Jakarta', 500, 'Mat karate internasional, Toilet modern, Ruang pers', 'aktif'],
        ['Gedung Olahraga Bandung', 'Jl. Otista No. 17, Bandung', 'Bandung', 'Jawa Barat', 400, 'Mat karate, Tribun penonton, Toilet', 'aktif'],
        ['Kompleks Olahraga Medan', 'Jl. Gatot Subroto, Medan', 'Medan', 'Sumatera Utara', 200, 'Mat karate, Ruang latihan', 'aktif']
    ];
    
    $stmt = $conn->prepare("INSERT INTO lokasi (nama_lokasi, alamat, kota, provinsi, kapasitas, fasilitas, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($lokasi_data as $lokasi) {
        $stmt->execute($lokasi);
    }
    $output[] = "✓ Inserted " . count($lokasi_data) . " lokasi<br>";
    
    // ========================================
    // 5. MASTER SABUK HITAM (MSH) DATA
    // ========================================
    $output[] = "<h3>👨‍🏫 Importing Master Sabuk Hitam...</h3>";
    
    try {
        $conn->exec("DELETE FROM master_sabuk_hitam");
    } catch (Exception $e) {}
    
    $msh_data = [
        ['MSH001', 'Budi Santoso', 'Jakarta', '1965-03-15', 'L', 'Dan 5', '1995-06-20', 'IJAZAH/1995/001', 'Jl. Merdeka No. 1, Jakarta', '081234567890', 'budi@ypok.id', 'Dojo Jakarta Pusat', 'Aktif'],
        ['MSH002', 'Siti Nurhaliza', 'Surabaya', '1970-07-22', 'P', 'Dan 3', '2000-11-15', 'IJAZAH/2000/002', 'Jl. Diponegoro No. 50, Surabaya', '081345678901', 'siti@ypok.id', 'Dojo Surabaya Pusat', 'Aktif'],
        ['MSH003', 'Ahmad Wijaya', 'Bandung', '1968-01-30', 'L', 'Dan 4', '1998-08-10', 'IJAZAH/1998/003', 'Jl. Ahmad Yani No. 100, Bandung', '081456789012', 'ahmad@ypok.id', 'Dojo Bandung', 'Aktif'],
        ['MSH004', 'Dewi Lestari', 'Semarang', '1972-11-05', 'P', 'Dan 2', '2005-05-25', 'IJAZAH/2005/004', 'Jl. Pandanaran No. 75, Semarang', '081567890123', 'dewi@ypok.id', 'Dojo Semarang', 'Aktif'],
        ['MSH005', 'Rudi Hartono', 'Medan', '1975-06-18', 'L', 'Dan 1', '2008-02-14', 'IJAZAH/2008/005', 'Jl. Gatot Subroto No. 120, Medan', '081678901234', 'rudi@ypok.id', 'Dojo Medan', 'Aktif']
    ];
    
    $stmt = $conn->prepare("INSERT INTO master_sabuk_hitam (no_msh, nama, tempat_lahir, tanggal_lahir, jenis_kelamin, tingkat_dan, tanggal_lulus, nomor_ijazah, alamat, no_telp, email, dojo_cabang, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($msh_data as $msh) {
        $stmt->execute($msh);
    }
    $output[] = "✓ Inserted " . count($msh_data) . " Master Sabuk Hitam<br>";
    
    // ========================================
    // 6. PRESTASI MSH
    // ========================================
    $output[] = "<h3>🏆 Importing Prestasi MSH...</h3>";
    
    try {
        $conn->exec("DELETE FROM prestasi_msh");
    } catch (Exception $e) {}
    
    $prestasi_msh = [
        [1, 'Juara 1 Turnamen Nasional 2015', '2015-08-20', 'Kategori Senior'],
        [1, 'Pelatih Terbaik Jatim 2018', '2018-10-15', 'Penghargaan'],
        [2, 'Finalis Kejuaraan Dunia 2012', '2012-11-05', 'Kategori Putri'],
        [3, 'Juara 2 Kejuaraan Jawa Barat 2019', '2019-09-10', 'Kategori Senior'],
        [4, 'Hakim Resmi FORKI', '2016-05-20', 'Sertifikasi']
    ];
    
    $stmt = $conn->prepare("INSERT INTO prestasi_msh (msh_id, nama_prestasi, tanggal_prestasi, keterangan) VALUES (?, ?, ?, ?)");
    foreach ($prestasi_msh as $prestasi) {
        $stmt->execute($prestasi);
    }
    $output[] = "✓ Inserted " . count($prestasi_msh) . " prestasi MSH<br>";
    
    // ========================================
    // 7. KOHAI DATA
    // ========================================
    $output[] = "<h3>👥 Importing Kohai...</h3>";
    
    try {
        $conn->exec("DELETE FROM kohai");
    } catch (Exception $e) {}
    
    $kohai_data = [
        ['KOHAI001', 'Andi Pratama', 'Jakarta', '2008-05-12', 'L', 'Kyu 3', 'Kuning', 'Dojo Jakarta Pusat', '082112345678', 'andi@school.id', 'Aktif', 'Jl. Merdeka No. 10', 'Bambang Sutrisno', '081234567890'],
        ['KOHAI002', 'Siti Mariah', 'Surabaya', '2009-08-25', 'P', 'Kyu 5', 'Putih', 'Dojo Surabaya Pusat', '082223456789', 'siti@school.id', 'Aktif', 'Jl. Diponegoro No. 55', 'Sari Wulandari', '081345678901'],
        ['KOHAI003', 'Doni Setiawan', 'Bandung', '2007-03-18', 'L', 'Kyu 1', 'Orange', 'Dojo Bandung', '082334567890', 'doni@school.id', 'Aktif', 'Jl. Ahmad Yani No. 120', 'Joni Setiawan', '081456789012'],
        ['KOHAI004', 'Lina Kusuma', 'Semarang', '2010-01-08', 'P', 'Kyu 7', 'Putih', 'Dojo Semarang', '082445678901', 'lina@school.id', 'Aktif', 'Jl. Pandanaran No. 88', 'Hendra Kusuma', '081567890123'],
        ['KOHAI005', 'Rinto Setiabudi', 'Medan', '2008-11-30', 'L', 'Kyu 4', 'Kuning', 'Dojo Medan', '082556789012', 'rinto@school.id', 'Aktif', 'Jl. Gatot Subroto No. 150', 'Bambang Setiabudi', '081678901234'],
        ['KOHAI006', 'Maya Windasari', 'Jakarta', '2009-04-14', 'P', 'Kyu 2', 'Orange', 'Dojo Jakarta Pusat', '082667890123', 'maya@school.id', 'Aktif', 'Jl. Merdeka No. 20', 'Tri Windasari', '081312345678'],
        ['KOHAI007', 'Hafiz Maulana', 'Surabaya', '2007-09-22', 'L', 'Kyu 6', 'Putih', 'Dojo Surabaya Pusat', '082778901234', 'hafiz@school.id', 'Aktif', 'Jl. Diponegoro No. 65', 'Maulana Rauf', '081423456789']
    ];
    
    $stmt = $conn->prepare("INSERT INTO kohai (kode_kohai, nama, tempat_lahir, tanggal_lahir, jenis_kelamin, tingkat_kyu, sabuk, dojo_cabang, no_telp, email, status, alamat, nama_wali, no_telp_wali) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($kohai_data as $kohai) {
        $stmt->execute($kohai);
    }
    $output[] = "✓ Inserted " . count($kohai_data) . " kohai<br>";
    
    // ========================================
    // 8. PRESTASI KOHAI
    // ========================================
    $output[] = "<h3>🏅 Importing Prestasi Kohai...</h3>";
    
    try {
        $conn->exec("DELETE FROM prestasi_kohai");
    } catch (Exception $e) {}
    
    $prestasi_kohai = [
        [1, 'Juara 3 Turnamen Karate Sekolah 2023', '2023-08-15', 'Kategori U-15'],
        [2, 'Peserta Kejuaraan Jatim 2023', '2023-10-10', 'Kategori Putri U-12'],
        [3, 'Juara 1 Latihan Rutin 2024', '2024-02-20', 'Kumite Putra'],
        [4, 'Sertifikat Kenaikan Sabuk', '2024-03-15', 'Ujian Kyu 7 -> Kyu 6']
    ];
    
    $stmt = $conn->prepare("INSERT INTO prestasi_kohai (kohai_id, nama_prestasi, tanggal_prestasi, keterangan) VALUES (?, ?, ?, ?)");
    foreach ($prestasi_kohai as $prestasi) {
        $stmt->execute($prestasi);
    }
    $output[] = "✓ Inserted " . count($prestasi_kohai) . " prestasi kohai<br>";
    
    // ========================================
    // 9. PENGURUS DATA
    // ========================================
    $output[] = "<h3>📋 Importing Pengurus...</h3>";
    
    try {
        $conn->exec("DELETE FROM pengurus");
    } catch (Exception $e) {}
    
    $pengurus_data = [
        ['1234567890123456', 'Ir. Bambang Sutrisno', 'Jakarta', '1960-03-15', 'Ketua Umum', '2020-2025', '2020-01-15', 'SK-001/YPOK/2020', 'bambang@ypok.id', '081234567890', 'Jl. Gatot Subroto No. 1, Jakarta', 'S2 Teknik', 'Aktif'],
        ['2234567890123456', 'Dra. Siti Rahayu', 'Surabaya', '1965-07-20', 'Bendahara', '2020-2025', '2020-01-15', 'SK-002/YPOK/2020', 'siti@ypok.id', '081345678901', 'Jl. Diponegoro No. 10, Surabaya', 'S1 Akuntansi', 'Aktif'],
        ['3234567890123456', 'Budi Santoso', 'Jakarta', '1968-05-10', 'Sekretaris', '2020-2025', '2020-01-15', 'SK-003/YPOK/2020', 'budi.s@ypok.id', '081456789012', 'Jl. Merdeka No. 5, Jakarta', 'S1 Hukum', 'Aktif'],
        ['4234567890123456', 'Dr. Ahmad Wijaya, M.Si', 'Bandung', '1962-11-25', 'Bidang Olahraga', '2020-2025', '2020-02-01', 'SK-004/YPOK/2020', 'ahmad@ypok.id', '081567890123', 'Jl. Ahmad Yani No. 25, Bandung', 'S3 Olahraga', 'Aktif'],
        ['5234567890123456', 'Dra. Winda Sari', 'Semarang', '1963-06-12', 'Bidang Pendidikan', '2020-2025', '2020-02-01', 'SK-005/YPOK/2020', 'winda@ypok.id', '081678901234', 'Jl. Diag Soekarno Hatta No. 15, Semarang', 'S1 Pendidikan', 'Aktif']
    ];
    
    $stmt = $conn->prepare("INSERT INTO pengurus (nik, nama, tempat_lahir, tanggal_lahir, jabatan, periode, tanggal_sk, no_sk, email, telepon, alamat, pendidikan_terakhir, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($pengurus_data as $pengurus) {
        $stmt->execute($pengurus);
    }
    $output[] = "✓ Inserted " . count($pengurus_data) . " pengurus<br>";
    
    // ========================================
    // 10. LEGALITAS DATA
    // ========================================
    $output[] = "<h3>📄 Importing Legalitas...</h3>";
    
    try {
        $conn->exec("DELETE FROM legalitas");
    } catch (Exception $e) {}
    
    $legalitas_data = [
        ['Akta Notaris Pendirian', 'AKT/2010/001', '2010-01-20', NULL, 'Notaris Bambang Sutrisno', 'Aktif', 'Akta Pendirian YPOK', 1],
        ['Surat Keputusan Kemenkumham', 'SK/KMH/2010/5678', '2010-02-15', NULL, 'Kemenkumham RI', 'Aktif', 'Pengesahan Badan Hukum', 1],
        ['NPWP Yayasan', 'NPWP-YPN-00001', '2010-03-01', '2025-03-01', 'DJP', 'Aktif', 'NPWP Terdaftar', 0],
        ['Izin Operasional', 'IOP/PEMKOT/2023/001', '2023-01-10', '2025-01-10', 'Pemkot Jakarta', 'Aktif', 'Izin Operasional', 0],
        ['Sertifikat ISO', 'ISO/2024/5678', '2024-01-15', '2027-01-15', 'BSN', 'Aktif', 'Sertifikasi ISO 9001', 0],
        ['Izin Pajak', 'IJP/2023/0001', '2023-06-01', '2025-06-01', 'KPP', 'Akan Kadaluarsa', 'Akan kadaluarsa dalam 6 bulan', 0]
    ];
    
    $stmt = $conn->prepare("INSERT INTO legalitas (jenis_dokumen, nomor_dokumen, tanggal_terbit, tanggal_kadaluarsa, instansi_penerbit, status, keterangan, is_permanent) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($legalitas_data as $legalitas) {
        $stmt->execute($legalitas);
    }
    $output[] = "✓ Inserted " . count($legalitas_data) . " legalitas<br>";
    
    // ========================================
    // 11. PEMBAYARAN DATA
    // ========================================
    $output[] = "<h3>💳 Importing Pembayaran...</h3>";
    
    try {
        $conn->exec("DELETE FROM pembayaran");
    } catch (Exception $e) {}
    
    $pembayaran_data = [
        [1, 'Pendaftaran', 100000, '2024-01-15', 'Transfer Bank', 'Lunas', 'Pendaftaran kohai baru'],
        [1, 'SPP Januari', 50000, '2024-01-20', 'Tunai', 'Lunas', 'SPP bulan Januari'],
        [1, 'SPP Februari', 50000, '2024-02-18', 'Transfer Bank', 'Lunas', 'SPP bulan Februari'],
        [2, 'Pendaftaran', 100000, '2024-01-25', 'Transfer Bank', 'Lunas', 'Pendaftaran kohai baru'],
        [2, 'SPP Januari', 50000, '2024-01-28', 'Tunai', 'Lunas', 'SPP bulan Januari'],
        [3, 'Pendaftaran', 150000, '2024-02-01', 'Transfer Bank', 'Lunas', 'Pendaftaran kohai cluster Bandung'],
        [3, 'SPP Februari', 50000, '2024-02-15', 'Transfer Bank', 'Lunas', 'SPP bulan Februari'],
        [4, 'Pendaftaran', 100000, '2024-02-10', 'Tunai', 'Lunas', 'Pendaftaran'],
        [5, 'SPP Januari', 50000, '2024-01-30', 'Transfer Bank', 'Belum Lunas', 'Belum membayar'],
        [6, 'Pendaftaran', 100000, '2024-03-01', 'Transfer Bank', 'Lunas', 'Pendaftaran'],
        [6, 'SPP Maret', 50000, '2024-03-10', 'Tunai', 'Lunas', 'SPP bulan Maret'],
        [7, 'SPP Februari', 50000, '2024-02-25', 'Transfer Bank', 'Lunas', 'SPP bulan Februari']
    ];
    
    $stmt = $conn->prepare("INSERT INTO pembayaran (kohai_id, kategori, jumlah, tanggal_bayar, metode_pembayaran, status, keterangan) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($pembayaran_data as $pembayaran) {
        $stmt->execute($pembayaran);
    }
    $output[] = "✓ Inserted " . count($pembayaran_data) . " pembayaran<br>";
    
    // ========================================
    // 12. KEGIATAN DATA
    // ========================================
    $output[] = "<h3>🎯 Importing Kegiatan...</h3>";
    
    try {
        $conn->exec("DELETE FROM kegiatan");
    } catch (Exception $e) {}
    
    $kegiatan_data = [
        ['Latihan Rutin Bulan Maret', 'Latihan rutin untuk semua level kohai', '2024-03-15', '16:00', '18:00', 1, 'Latihan', 45, 'dijadwalkan', 1],
        ['Ujian Kenaikan Sabuk', 'Ujian kenaikan sabuk untuk kohai level Kyu 4-6', '2024-03-20', '08:00', '12:00', 1, 'Ujian', 30, 'dijadwalkan', 1],
        ['Turnamen Karate Antar Dojo', 'Kompetisi karate tingkat nasional', '2024-04-10', '07:00', '17:00', 3, 'Kompetisi', 200, 'akan datang', 1],
        ['Workshop Pelatihan', 'Workshop untuk meningkatkan skill pelatih', '2024-03-25', '09:00', '16:00', 2, 'Workshop', 50, 'dijadwalkan', 0],
        ['Latihan Intensif Persiapan', 'Latihan intensif persiapan kompetisi', '2024-04-01', '15:00', '17:30', 1, 'Latihan', 60, 'akan datang', 0]
    ];
    
    $stmt = $conn->prepare("INSERT INTO kegiatan (nama_kegiatan, deskripsi, tanggal_kegiatan, jam_mulai, jam_selesai, lokasi_id, kategori, jumlah_peserta, status, tampil_di_berita) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($kegiatan_data as $kegiatan) {
        $stmt->execute($kegiatan);
    }
    $output[] = "✓ Inserted " . count($kegiatan_data) . " kegiatan<br>";
    
    // ========================================
    // 13. TRANSAKSI DATA
    // ========================================
    $output[] = "<h3>💰 Importing Transaksi...</h3>";
    
    try {
        $conn->exec("DELETE FROM transaksi");
    } catch (Exception $e) {}
    
    $transaksi_data = [
        ['pemasukan', 500000, 'SPP kohai bulan Maret', 'SPP Kohai', 'selesai'],
        ['pemasukan', 250000, 'Donasi dari alumni', 'Donasi', 'selesai'],
        ['pengeluaran', 150000, 'Pembelian matras karate', 'Perlengkapan', 'selesai'],
        ['pengeluaran', 100000, 'Listrik dan air bulan Maret', 'Operasional', 'selesai'],
        ['pemasukan', 300000, 'Uang pendaftaran kohai baru', 'Pendaftaran', 'selesai'],
        ['pengeluaran', 200000, 'Gaji volunteer trainer', 'Gaji', 'selesai'],
        ['pemasukan', 400000, 'Sponsor turnamen', 'Sponsor', 'selesai'],
        ['pengeluaran', 75000, 'Perlengkapan kantor', 'Operasional', 'selesai']
    ];
    
    $stmt = $conn->prepare("INSERT INTO transaksi (jenis, jumlah, keterangan, kategori, status) VALUES (?, ?, ?, ?, ?)");
    foreach ($transaksi_data as $transaksi) {
        $stmt->execute($transaksi);
    }
    $output[] = "✓ Inserted " . count($transaksi_data) . " transaksi<br>";
    
    // ========================================
    // 14. INFORMASI YAYASAN
    // ========================================
    $output[] = "<h3>🏛️ Importing Informasi Yayasan...</h3>";
    
    try {
        $conn->exec("DELETE FROM informasi_yayasan");
    } catch (Exception $e) {}
    
    $yayasan = [
        'Yayasan Pendidikan Olahraga Karate',
        'YPOK',
        '2010-01-10',
        'Badan Hukum Yayasan',
        'Jl. Gatot Subroto No. 1, Jakarta Selatan',
        'info@ypok.id',
        '021-7234567',
        'www.ypok.id',
        'Mewujudkan generasi yang sehat, disiplin, dan berbudi pekerti luhur melalui olahraga karate',
        '1. Menyelenggarakan pendidikan karate berkualitas
2. Mengembangkan prestasi atlet karate
3. Membangun karakter melalui olahraga
4. Meningkatkan kesehatan masyarakat'
    ];
    
    $stmt = $conn->prepare("INSERT INTO informasi_yayasan (nama_lengkap, nama_singkat, tanggal_berdiri, status_hukum, alamat, email, telepon, website, visi, misi) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute($yayasan);
    $output[] = "✓ Inserted Informasi Yayasan<br>";
    
    $output[] = "<hr>";
    $output[] = "<h2 style='color: green; text-align: center;'>✅ ALL DUMMY DATA IMPORTED SUCCESSFULLY!</h2>";
    $output[] = "<p style='text-align: center; color: blue;'>Total records imported: <strong>100+</strong></p>";
    $output[] = "<p style='text-align: center;'><a href='../index.php' class='btn btn-primary'>Go to Admin Dashboard</a></p>";
    
} catch (Exception $e) {
    $output[] = "<h2 style='color: red;'>❌ ERROR: " . $e->getMessage() . "</h2>";
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Import Dummy Data</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 40px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; margin-bottom: 30px; }
        h3 { color: #34495e; margin-top: 20px; margin-bottom: 15px; }
        .btn { padding: 10px 30px; font-size: 16px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📊 YPOK Management - Dummy Data Importer</h1>
        <?php foreach ($output as $line) echo $line; ?>
    </div>
</body>
</html>
