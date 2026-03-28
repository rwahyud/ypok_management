<?php
require_once '../config/database.php';

if(!isset($_SESSION['user_id'])) {
    exit(json_encode(['error' => 'Unauthorized']));
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Get produk data
    $stmt = $pdo->prepare("SELECT * FROM produk_toko WHERE id = ?");
    $stmt->execute([$id]);
    $produk = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($produk) {
        // Get variasi if exists
        if ($produk['has_variasi']) {
            $stmt_variasi = $pdo->prepare("SELECT * FROM produk_variasi WHERE produk_id = ? ORDER BY id");
            $stmt_variasi->execute([$id]);
            $produk['variasi'] = $stmt_variasi->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $produk['variasi'] = [];
        }
        
        // Ensure status is set
        if (!isset($produk['status']) || empty($produk['status'])) {
            $produk['status'] = 'Tersedia';
        }
        
        // Convert numeric values
        $produk['harga'] = (float)$produk['harga'];
        $produk['stok'] = (int)$produk['stok'];
        $produk['has_variasi'] = (int)$produk['has_variasi'];
        
        header('Content-Type: application/json');
        echo json_encode($produk);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Produk tidak ditemukan']);
    }
}
?>
