<?php
// Tampilkan data master_sabuk_hitam hasil import untuk verifikasi
require_once __DIR__ . '/../config/database.php';
header('Content-Type: text/html; charset=utf-8');

$stmt = $pdo->query('SELECT no_msh, nama, tempat_lahir, tanggal_lahir, tingkat_dan, tanggal_lulus, alamat, dojo_cabang, status FROM master_sabuk_hitam ORDER BY no_msh ASC');
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$data) {
    echo '<b>Tidak ada data di tabel master_sabuk_hitam.</b>';
    exit;
}

echo '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>Data Master Sabuk Hitam</title>';
echo '<style>body{font-family:Arial,sans-serif;background:#f7f7f7;margin:0;padding:0;}h2{margin:24px 0 12px 0;}table{border-collapse:collapse;width:98%;margin:0 auto 24px auto;background:#fff;}th,td{border:1px solid #bbb;padding:8px 10px;}th{background:#4CAF50;color:#fff;}tr:nth-child(even){background:#f2f2f2;}tr:hover{background:#e0f7fa;}caption{caption-side:top;font-size:1.1em;margin-bottom:8px;}@media(max-width:900px){table,th,td{font-size:12px;}}</style>';
echo '</head><body>';
echo '<h2 style="text-align:center;">Data Master Sabuk Hitam (Hasil Import)</h2>';
echo '<div style="text-align:center;margin-bottom:12px;">Total Data: <b>' . count($data) . '</b></div>';
echo '<table><tr><th>No</th><th>No. MSH</th><th>Nama</th><th>Tempat Lahir</th><th>Tanggal Lahir</th><th>Dan</th><th>Tgl Ujian</th><th>Alamat</th><th>Provinsi</th><th>Status</th></tr>';
$no = 1;
foreach ($data as $row) {
    echo '<tr>';
    echo '<td>' . $no++ . '</td>';
    echo '<td>' . htmlspecialchars($row['no_msh']) . '</td>';
    echo '<td>' . htmlspecialchars($row['nama']) . '</td>';
    echo '<td>' . htmlspecialchars($row['tempat_lahir']) . '</td>';
    echo '<td>' . htmlspecialchars($row['tanggal_lahir']) . '</td>';
    echo '<td>' . htmlspecialchars($row['tingkat_dan']) . '</td>';
    echo '<td>' . htmlspecialchars($row['tanggal_lulus']) . '</td>';
    echo '<td>' . htmlspecialchars($row['alamat']) . '</td>';
    echo '<td>' . htmlspecialchars($row['dojo_cabang']) . '</td>';
    echo '<td>' . htmlspecialchars($row['status']) . '</td>';
    echo '</tr>';
}
echo '</table>';
echo '<div style="text-align:center;color:#888;font-size:13px;">&copy; ' . date('Y') . ' YPOK Management System</div>';
echo '</body></html>';
?>
