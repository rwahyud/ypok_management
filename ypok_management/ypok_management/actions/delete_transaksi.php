<?php
require_once __DIR__ . '/../config/database.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

if(isset($_GET['id'])) {
    try {
        // Get transaction data
        $stmt = $pdo->prepare("SELECT produk_id, jumlah FROM transaksi_toko WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $transaksi = $stmt->fetch();
        
        $pdo->beginTransaction();
        
        // Delete transaction
        $stmt = $pdo->prepare("DELETE FROM transaksi_toko WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        
        // Restore stock
        $stmt = $pdo->prepare("UPDATE produk_toko SET stok = stok + ? WHERE id = ?");
        $stmt->execute([$transaksi['jumlah'], $transaksi['produk_id']]);
        
        $pdo->commit();
        
        header('Location: ../pages/toko.php?deleted=1&msg=Transaksi berhasil dihapus dan stok dikembalikan');
    } catch(PDOException $e) {
        $pdo->rollBack();
        header('Location: ../pages/toko.php?error=1&msg=' . urlencode($e->getMessage()));
    }
}
?>
