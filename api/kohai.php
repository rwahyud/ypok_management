<?php
require_once 'config/supabase.php';

if(!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    http_response_code(403);
    exit();
}

$id = $_GET['id'];

// Get kohai data
$stmt = $pdo->prepare("SELECT * FROM kohai WHERE id = ?");
$stmt->execute([$id]);
$kohai = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$kohai) {
    http_response_code(404);
    echo json_encode(['error' => 'Data not found']);
    exit();
}

// Get prestasi
$stmt = $pdo->prepare("SELECT * FROM prestasi_kohai WHERE kohai_id = ?");
$stmt->execute([$id]);
$prestasi = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get sertifikasi
$stmt = $pdo->prepare("SELECT * FROM sertifikasi_kohai WHERE kohai_id = ?");
$stmt->execute([$id]);
$sertifikasi = $stmt->fetchAll(PDO::FETCH_ASSOC);

$response = [
    'id' => $kohai['id'],
    'kode_kohai' => $kohai['kode_kohai'],
    'nama' => $kohai['nama'],
    'tempat_lahir' => $kohai['tempat_lahir'],
    'tanggal_lahir' => $kohai['tanggal_lahir'],
    'jenis_kelamin' => $kohai['jenis_kelamin'],
    'tingkat_kyu' => $kohai['tingkat_kyu'],
    'sabuk' => $kohai['sabuk'],
    'dojo_cabang' => $kohai['dojo_cabang'],
    'no_telp' => $kohai['no_telp'],
    'email' => $kohai['email'],
    'nama_wali' => $kohai['nama_wali'],
    'no_telp_wali' => $kohai['no_telp_wali'],
    'status' => $kohai['status'],
    'alamat' => $kohai['alamat'],
    'foto' => $kohai['foto'],
    'prestasi' => $prestasi,
    'sertifikasi' => $sertifikasi
];

header('Content-Type: application/json');
echo json_encode($response);
