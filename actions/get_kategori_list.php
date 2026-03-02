<?php
require_once '../config/supabase.php';

try {
    $stmt = $pdo->query("SELECT * FROM kategori_produk ORDER BY nama_kategori");
    $kategori = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode($kategori);
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}
?>
