<?php
// Import data MSH dari file CSV ke tabel master_sabuk_hitam
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/storage.php';

$csvFile = __DIR__ . '/../uploads/msh/NO.MSH YPOK - NO.MSH.csv';
if (!file_exists($csvFile)) {
    exit('File CSV tidak ditemukan.');
}

$handle = fopen($csvFile, 'r');
if (!$handle) {
    exit('Gagal membuka file CSV.');
}

// Lewati header sampai baris data
for ($i = 0; $i < 7; $i++) fgetcsv($handle);

$inserted = 0;
$updated = 0;
while (($row = fgetcsv($handle)) !== false) {
    if (empty($row[1]) || empty($row[2])) continue; // skip baris kosong
    $no_msh = trim($row[1]);
    $nama = trim($row[2]);
    $ttl = isset($row[3]) ? trim($row[3]) : '';
    $dan = isset($row[4]) ? trim($row[4]) : '';
    $tgl_ujian = isset($row[5]) ? trim($row[5]) : '';
    $provinsi = isset($row[6]) ? trim($row[6]) : '';
    $alamat = isset($row[7]) ? trim($row[7]) : '';
    $jenis = isset($row[8]) ? trim($row[8]) : '';
    $status = isset($row[9]) ? trim($row[9]) : 'Aktif';

    // Pisahkan tempat dan tanggal lahir
    $tempat_lahir = $tanggal_lahir = '';
    if (strpos($ttl, ',') !== false) {
        [$tempat_lahir, $tanggal_lahir] = array_map('trim', explode(',', $ttl, 2));
        $tanggal_lahir = date('Y-m-d', strtotime(str_replace(['/', '.'], '-', $tanggal_lahir)));
    } elseif (preg_match('/([a-zA-Z ]+)[, ]+(\d{1,2}[-\/\.]\d{1,2}[-\/\.]\d{2,4})/', $ttl, $m)) {
        $tempat_lahir = trim($m[1]);
        $tanggal_lahir = date('Y-m-d', strtotime(str_replace(['/', '.'], '-', $m[2])));
    }

    // Cek apakah sudah ada di DB
    $stmt = $pdo->prepare('SELECT id FROM master_sabuk_hitam WHERE no_msh = ?');
    $stmt->execute([$no_msh]);
    $id = $stmt->fetchColumn();
    if ($id) {
        // Update
        $stmt = $pdo->prepare('UPDATE master_sabuk_hitam SET nama=?, tempat_lahir=?, tanggal_lahir=?, tingkat_dan=?, tanggal_lulus=?, alamat=?, dojo_cabang=?, status=?, updated_at=NOW() WHERE id=?');
        $stmt->execute([$nama, $tempat_lahir, $tanggal_lahir, $dan, $tgl_ujian, $alamat, $provinsi, $status, $id]);
        $updated++;
    } else {
        // Insert
        $stmt = $pdo->prepare('INSERT INTO master_sabuk_hitam (no_msh, nama, tempat_lahir, tanggal_lahir, tingkat_dan, tanggal_lulus, alamat, dojo_cabang, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$no_msh, $nama, $tempat_lahir, $tanggal_lahir, $dan, $tgl_ujian, $alamat, $provinsi, $status]);
        $inserted++;
    }
}
fclose($handle);
echo "Import selesai. Data baru: $inserted, data update: $updated.";
