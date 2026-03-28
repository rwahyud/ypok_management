<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

if(!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

try {
    if(isset($_POST['action']) && $_POST['action'] === 'toggle') {
        $id = (int)$_POST['id'];
        $tampil = (int)$_POST['tampil'];

        if ($id <= 0 || ($tampil !== 0 && $tampil !== 1)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Parameter tidak valid']);
            exit();
        }
        
        $stmt = $pdo->prepare("UPDATE kegiatan SET tampil_di_berita = ? WHERE id = ?");
        $result = $stmt->execute([$tampil, $id]);
        
        if($result) {
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Status berhasil diperbarui']);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Gagal memperbarui status']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
