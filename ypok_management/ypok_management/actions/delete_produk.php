<?php
require_once '../config/database.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

if(isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM produk_toko WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        
        header('Location: ../pages/toko.php?deleted=1&msg=Produk berhasil dihapus');
    } catch(PDOException $e) {
        header('Location: ../pages/toko.php?error=1&msg=Tidak dapat menghapus produk yang sudah ada transaksi');
    }
}
?>
