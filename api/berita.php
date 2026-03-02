<?php
require_once '../config/supabase.php';

// Set header for JSON response
header('Content-Type: application/json');

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized - Please login first'
    ]);
    exit();
}

// Check if request is POST
if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit();
}

// Get POST data
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$status = isset($_POST['status']) ? filter_var($_POST['status'], FILTER_VALIDATE_BOOLEAN) : false;

// Validate ID
if($id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid kegiatan ID'
    ]);
    exit();
}

try {
    // Update tampil_di_berita status
    $stmt = $pdo->prepare("UPDATE kegiatan SET tampil_di_berita = :status WHERE id = :id");
    $stmt->bindParam(':status', $status, PDO::PARAM_BOOL);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    
    if($stmt->execute()) {
        // Get updated data
        $stmt_check = $pdo->prepare("SELECT nama_kegiatan, tampil_di_berita FROM kegiatan WHERE id = :id");
        $stmt_check->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt_check->execute();
        $kegiatan = $stmt_check->fetch();
        
        echo json_encode([
            'success' => true,
            'message' => $status ? 'Berita berhasil diaktifkan' : 'Berita berhasil dinonaktifkan',
            'data' => [
                'id' => $id,
                'nama_kegiatan' => $kegiatan['nama_kegiatan'],
                'tampil_di_berita' => $kegiatan['tampil_di_berita']
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update berita status'
        ]);
    }
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
