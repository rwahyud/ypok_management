<?php
require_once __DIR__ . '/../config/database.php';

if(!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    http_response_code(403);
    exit(json_encode(['error' => 'Unauthorized']));
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM master_sabuk_hitam WHERE id = ?");
$stmt->execute([$id]);
$msh = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$msh) {
    http_response_code(404);
    exit(json_encode(['error' => 'Data not found']));
}

// Get Prestasi
$stmt_prestasi = $pdo->prepare("SELECT * FROM prestasi_msh WHERE msh_id = ?");
$stmt_prestasi->execute([$id]);
$prestasi = $stmt_prestasi->fetchAll(PDO::FETCH_ASSOC);

// Get Sertifikasi
$stmt_sertifikasi = $pdo->prepare("SELECT * FROM sertifikasi_msh WHERE msh_id = ?");
$stmt_sertifikasi->execute([$id]);
$sertifikasi = $stmt_sertifikasi->fetchAll(PDO::FETCH_ASSOC);

// Build response with correct field mapping
$response = [
    'id' => $msh['id'],
    'no_msh' => $msh['no_msh'] ?? '',
    'nama' => $msh['nama'] ?? '',
    'tempat_lahir' => $msh['tempat_lahir'] ?? '',
    'tanggal_lahir' => $msh['tanggal_lahir'] ?? '',
    'jenis_kelamin' => $msh['jenis_kelamin'] ?? 'L',
    'tingkat_dan' => $msh['tingkat_dan'] ?? '',
    'dojo_cabang' => $msh['dojo_cabang'] ?? '',
    'no_telp' => $msh['no_telp'] ?? '',
    'email' => $msh['email'] ?? '',
    'nomor_ijazah' => $msh['nomor_ijazah'] ?? '',
    'status' => $msh['status'] ?? 'aktif',
    'alamat' => $msh['alamat'] ?? '',
    'foto' => $msh['foto'] ?? '',
    'prestasi' => $prestasi,
    'sertifikasi' => $sertifikasi
];

header('Content-Type: application/json');
echo json_encode($response);
?>
