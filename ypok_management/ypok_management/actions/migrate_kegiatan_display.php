<?php
require_once __DIR__ . '/../config/database.php';

// Check if user is admin
if(!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

try {
    // Check if column exists
    $columns = $pdo->query("SHOW COLUMNS FROM kegiatan LIKE 'tampil_di_berita'")->fetchAll();
    
    if(empty($columns)) {
        // Column doesn't exist, create it
        $pdo->exec("ALTER TABLE `kegiatan` 
            ADD COLUMN `tampil_di_berita` BOOLEAN DEFAULT FALSE AFTER `keterangan`,
            ADD COLUMN `foto` VARCHAR(255) DEFAULT NULL AFTER `tampil_di_berita`,
            ADD KEY `idx_tampil_di_berita` (`tampil_di_berita`)");
        
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Database berhasil di-upgrade']);
    } else {
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Database sudah up-to-date']);
    }
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
