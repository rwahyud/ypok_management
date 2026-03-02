<?php
require_once '../../config/supabase.php';

header('Content-Type: application/json');

if(!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$id = $_GET['id'] ?? null;

if(!$id) {
    echo json_encode(['success' => false, 'error' => 'ID tidak valid']);
    exit();
}

try {
    // Get kegiatan data with lokasi
    $stmt = $pdo->prepare("SELECT k.*, l.nama_lokasi 
                           FROM kegiatan k 
                           LEFT JOIN lokasi l ON k.lokasi_id = l.id 
                           WHERE k.id = ?");
    $stmt->execute([$id]);
    $kegiatan = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if(!$kegiatan) {
        echo json_encode(['success' => false, 'error' => 'Data tidak ditemukan']);
        exit();
    }
    
    // Format tanggal
    $tanggal = new DateTime($kegiatan['tanggal_kegiatan']);
    $kegiatan['tanggal_formatted'] = $tanggal->format('d F Y');
    
    // Format status display
    $status_display = [
        'selesai' => 'Selesai',
        'berlangsung' => 'Berlangsung',
        'dijadwalkan' => 'Dijadwalkan',
        'dibatalkan' => 'Dibatalkan'
    ];
    $kegiatan['status_display'] = $status_display[$kegiatan['status']] ?? ucfirst($kegiatan['status']);
    
    // Get peserta MSH
    $stmt_msh = $pdo->prepare("SELECT pm.*, m.nama, m.kode_msh 
                                FROM peserta_msh pm 
                                LEFT JOIN majelis_sabuk_hitam m ON pm.msh_id = m.id 
                                WHERE pm.kegiatan_id = ?");
    $stmt_msh->execute([$id]);
    $peserta_msh = $stmt_msh->fetchAll(PDO::FETCH_ASSOC);
    $kegiatan['peserta_msh'] = $peserta_msh;
    
    // Get peserta Kohai
    $stmt_kohai = $pdo->prepare("SELECT pk.*, k.nama, k.kode_kohai 
                                  FROM peserta_kohai pk 
                                  LEFT JOIN kohai k ON pk.kohai_id = k.id 
                                  WHERE pk.kegiatan_id = ?");
    $stmt_kohai->execute([$id]);
    $peserta_kohai = $stmt_kohai->fetchAll(PDO::FETCH_ASSOC);
    $kegiatan['peserta_kohai'] = $peserta_kohai;
    
    echo json_encode([
        'success' => true,
        'data' => $kegiatan
    ]);
    
} catch(PDOException $e) {
    error_log("Error get kegiatan detail: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Terjadi kesalahan: ' . $e->getMessage()
    ]);
}
