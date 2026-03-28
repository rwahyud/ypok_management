<?php
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $nama_kategori = trim($_POST['nama_kategori']);
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $icon = trim($_POST['icon'] ?? '📦');
        
        // Validasi
        if (empty($nama_kategori)) {
            throw new Exception('Nama kategori tidak boleh kosong');
        }
        
        // Cek duplikasi
        $check = $pdo->prepare("SELECT id FROM kategori_produk WHERE nama_kategori = ?");
        $check->execute([$nama_kategori]);
        
        if ($check->fetch()) {
            throw new Exception('Kategori sudah ada');
        }
        
        // Insert kategori
        $stmt = $pdo->prepare("INSERT INTO kategori_produk (nama_kategori, deskripsi, icon) VALUES (?, ?, ?)");
        $stmt->execute([$nama_kategori, $deskripsi, $icon]);
        
        header('Location: ../pages/toko.php?success=1&msg=Kategori berhasil ditambahkan');
        exit();
        
    } catch (Exception $e) {
        header('Location: ../pages/toko.php?error=1&msg=' . urlencode($e->getMessage()));
        exit();
    }
}
?>
