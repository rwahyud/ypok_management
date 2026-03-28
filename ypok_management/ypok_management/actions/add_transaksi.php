<?php
require_once __DIR__ . '/../config/database.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo->beginTransaction();
        
        $produk_id = $_POST['produk_id'];
        $variasi_id = isset($_POST['variasi_id']) && !empty($_POST['variasi_id']) ? $_POST['variasi_id'] : null;
        $pembeli = $_POST['pembeli'];
        $lokasi = $_POST['lokasi'];
        $alamat = $_POST['alamat'] ?? '';
        $jumlah = (int)$_POST['jumlah'];
        $metode_pembayaran = $_POST['metode_pembayaran'];
        $catatan = $_POST['catatan'] ?? '';
        
        // Get produk data
        $stmt = $pdo->prepare("SELECT * FROM produk_toko WHERE id = ?");
        $stmt->execute([$produk_id]);
        $produk = $stmt->fetch();
        
        if (!$produk) {
            throw new Exception('Produk tidak ditemukan');
        }
        
        // Calculate price and check stock
        $harga_satuan = (float)$produk['harga'];
        $stok_tersedia = (int)$produk['stok'];
        $variasi_info = '';
        
        // If product has variation
        if ($variasi_id) {
            $stmt_var = $pdo->prepare("SELECT * FROM produk_variasi WHERE id = ? AND produk_id = ?");
            $stmt_var->execute([$variasi_id, $produk_id]);
            $variasi = $stmt_var->fetch();
            
            if (!$variasi) {
                throw new Exception('Variasi tidak ditemukan');
            }
            
            $harga_satuan = (float)$produk['harga'] + (float)$variasi['harga_tambahan'];
            $stok_tersedia = (int)$variasi['stok'];
            $variasi_info = $variasi['nama_variasi'] . ': ' . $variasi['nilai_variasi'];
            
            // Check variasi stock
            if ($stok_tersedia < $jumlah) {
                throw new Exception('Stok variasi tidak mencukupi. Stok tersedia: ' . $stok_tersedia);
            }
            
            // Update variasi stock
            $pdo->prepare("UPDATE produk_variasi SET stok = stok - ? WHERE id = ?")
                ->execute([$jumlah, $variasi_id]);
                
        } else {
            // Check product stock
            if ($stok_tersedia < $jumlah) {
                throw new Exception('Stok produk tidak mencukupi. Stok tersedia: ' . $stok_tersedia);
            }
        }
        
        $total_harga = $harga_satuan * $jumlah;
        
        // Generate transaction ID
        $id_transaksi = 'TRX-' . date('YmdHis') . '-' . rand(1000, 9999);
        
        // Insert transaction
        $stmt = $pdo->prepare("INSERT INTO transaksi_toko 
            (id_transaksi, produk_id, pembeli, lokasi, alamat, jumlah, total_harga, metode_pembayaran, catatan, variasi_info) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $id_transaksi,
            $produk_id,
            $pembeli,
            $lokasi,
            $alamat,
            $jumlah,
            $total_harga,
            $metode_pembayaran,
            $catatan,
            $variasi_info
        ]);
        
        // Update product stock (total)
        $pdo->prepare("UPDATE produk_toko SET stok = stok - ? WHERE id = ?")
            ->execute([$jumlah, $produk_id]);
        
        $pdo->commit();
        header('Location: ../pages/toko.php?success=1&msg=Transaksi berhasil! ID: ' . urlencode($id_transaksi));
        exit();
        
    } catch (Exception $e) {
        $pdo->rollBack();
        header('Location: ../pages/toko.php?error=1&msg=' . urlencode($e->getMessage()));
        exit();
    }
}
?>
