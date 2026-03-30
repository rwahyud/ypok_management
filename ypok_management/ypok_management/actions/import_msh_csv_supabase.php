<?php
// import_msh_csv_supabase.php
// Script ini untuk di-klik kanan > Open in Browser di localhost
// Pastikan file CSV sudah ada di uploads/ dan koneksi ke Supabase sudah benar

$host = 'db.yjxfymwmhrkdevdjkvgr.supabase.co'; // contoh: db.xxxxx.supabase.co
$db   = 'postgres';
$user = 'postgres';
$pass = 'Ciooren123Ypok'; // Ganti dengan password asli dari dashboard Supabase
$port = '5432';


$csvFile = __DIR__ . '/../uploads/msh/NO.MSH YPOK - NO.MSH.csv'; // ganti namafile.csv sesuai file Anda

try {
    $conn = new PDO("pgsql:host=$host;port=$port;dbname=$db", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("<b>Koneksi gagal:</b> " . $e->getMessage());
}

if (!file_exists($csvFile)) {
    die("<b>File CSV tidak ditemukan:</b> $csvFile");
}

if (($handle = fopen($csvFile, "r")) !== FALSE) {
    $rowCount = 0;
    $sql = "INSERT INTO master_sabuk_hitam(no, no_msh, nama, tempat_tgl_lahir, nomor_ijazah_tingkatan, tanggal_ujian, asal_provinsi, alamat, jenis_dan, ket, foto) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    while (($data = fgetcsv($handle, 2000, ",")) !== FALSE) {
        // Skip baris yang bukan data (header, baris kosong, dsb)
        if (count($data) < 11 || !is_numeric(trim($data[0]))) continue;
        // Ambil hanya 11 kolom pertama (ignore kolom lebih)
        $stmt->execute(array_slice($data, 0, 11));
        $rowCount++;
    }
    fclose($handle);
    echo "<b>Import selesai!</b> Total baris masuk: $rowCount";
} else {
    echo "<b>Gagal membuka file CSV.</b>";
}
?>
