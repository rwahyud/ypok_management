<?php
require_once '../../config/supabase.php';

header('Content-Type: application/json');

if(!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$id = $_POST['id'] ?? null;
$status = $_POST['status'] ?? null;

if(!$id || !isset($status)) {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
    exit();
}

try {
    $tampil_di_berita = ($status == '1') ? true : false;
    
    $stmt = $pdo->prepare("UPDATE kegiatan SET tampil_di_berita = ? WHERE id = ?");
    $stmt->execute([$tampil_di_berita, $id]);
    
    $message = $tampil_di_berita ? 
        'Kegiatan berhasil ditampilkan di berita' : 
        'Kegiatan berhasil disembunyikan dari berita';
    
    echo json_encode([
        'success' => true,
        'message' => $message
    ]);
    
} catch(PDOException $e) {
    error_log("Error toggle berita: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan: ' . $e->getMessage()
    ]);
}
