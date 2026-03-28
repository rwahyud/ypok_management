<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if(!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if(!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID tidak ditemukan']);
    exit();
}

$id = $_GET['id'];

try {
    // Get kegiatan detail
    $stmt = $pdo->prepare("SELECT k.*, l.nama_lokasi FROM kegiatan k LEFT JOIN lokasi l ON k.lokasi_id = l.id WHERE k.id = ?");
    $stmt->execute([$id]);
    $kegiatan = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if(!$kegiatan) {
        echo json_encode(['success' => false, 'message' => 'Data tidak ditemukan']);
        exit();
    }
    
    // Normalize and map status for backward compatibility.
    $status_norm = strtolower(trim((string)$kegiatan['status']));
    $status_norm = str_replace(' ', '_', $status_norm);

    $status_display = [
        'terlaksana' => 'Selesai',
        'selesai' => 'Selesai',
        'berlangsung' => 'Selesai',
        'akan_datang' => 'Dijadwalkan',
        'dijadwalkan' => 'Dijadwalkan',
        'dibatalkan' => 'Dibatalkan'
    ];
    
    // Format tanggal
    $bulan = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    
    $date = new DateTime($kegiatan['tanggal_kegiatan']);
    $tanggal_formatted = $date->format('d') . ' ' . $bulan[(int)$date->format('n')] . ' ' . $date->format('Y');
    
    // Add formatted data
    $kegiatan['status_display'] = $status_display[$status_norm] ?? $kegiatan['status'];
    $kegiatan['tanggal_formatted'] = $tanggal_formatted;
    
    echo json_encode([
        'success' => true,
        'data' => $kegiatan
    ]);
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
