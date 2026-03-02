<?php
require_once '../../config/supabase.php';

header('Content-Type: application/json');

if(!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$id = $_GET['id'] ?? null;

if(!$id) {
    echo json_encode(['error' => 'ID tidak valid']);
    exit();
}

try {
    // Get MSH data
    $stmt = $pdo->prepare("SELECT * FROM majelis_sabuk_hitam WHERE id = ?");
    $stmt->execute([$id]);
    $msh = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if(!$msh) {
        echo json_encode(['error' => 'Data tidak ditemukan']);
        exit();
    }
    
    // Get prestasi
    $stmt_prestasi = $pdo->prepare("SELECT nama_prestasi FROM prestasi_msh WHERE msh_id = ? ORDER BY created_at DESC");
    $stmt_prestasi->execute([$id]);
    $prestasi = $stmt_prestasi->fetchAll(PDO::FETCH_COLUMN);
    
    // Get sertifikasi
    $stmt_sertifikasi = $pdo->prepare("SELECT * FROM sertifikasi_msh WHERE msh_id = ? ORDER BY tanggal_terbit DESC");
    $stmt_sertifikasi->execute([$id]);
    $sertifikasi = $stmt_sertifikasi->fetchAll(PDO::FETCH_ASSOC);
    
    // Add prestasi and sertifikasi to response
    $msh['prestasi'] = $prestasi;
    $msh['sertifikasi'] = $sertifikasi;
    
    echo json_encode($msh);
    
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
