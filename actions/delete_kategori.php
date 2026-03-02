<?php
require_once '../config/supabase.php';

if (isset($_GET['id'])) {
    try {
        $id = $_GET['id'];
        
        // Cek apakah kategori digunakan
        $check = $pdo->prepare("SELECT COUNT(*) as total FROM produk_toko WHERE kategori = (SELECT nama_kategori FROM kategori_produk WHERE id = ?)");
        $check->execute([$id]);
        $result = $check->fetch();
        
        if ($result['total'] > 0) {
            throw new Exception('Kategori tidak dapat dihapus karena masih digunakan oleh ' . $result['total'] . ' produk');
        }
        
        // Hapus kategori
        $stmt = $pdo->prepare("DELETE FROM kategori_produk WHERE id = ?");
        $stmt->execute([$id]);
        
        header('Location: ../toko.php?deleted=1&msg=Kategori berhasil dihapus');
        exit();
        
    } catch (Exception $e) {
        header('Location: ../toko.php?error=1&msg=' . urlencode($e->getMessage()));
        exit();
    }
}
?>
