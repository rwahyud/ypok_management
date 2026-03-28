<?php
require_once '../config/database.php';

if(!isset($_SESSION['user_id'])) {
    exit(json_encode(['error' => 'Unauthorized']));
}

if(isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Get transaksi data with product details
    $stmt = $pdo->prepare("
        SELECT t.*, p.nama_produk, p.kode_produk, p.spesifikasi
        FROM transaksi_toko t
        LEFT JOIN produk_toko p ON t.produk_id = p.id
        WHERE t.id = ?
    ");
    $stmt->execute([$id]);
    $transaksi = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($transaksi) {
        header('Content-Type: application/json');
        echo json_encode($transaksi);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Transaksi tidak ditemukan']);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'ID tidak ditemukan']);
}
?>
