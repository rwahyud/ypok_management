<?php
require_once '../config/database.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo->beginTransaction();
        
        // Data produk utama
        $kode_produk = $_POST['kode_produk'];
        $nama_produk = $_POST['nama_produk'];
        $kategori = $_POST['kategori'];
        $harga = $_POST['harga'];
        $stok = $_POST['stok'];
        $status = $_POST['status'];
        $deskripsi = $_POST['deskripsi'];
        $spesifikasi = $_POST['spesifikasi'] ?? null;
        $has_variasi = isset($_POST['has_variasi']) ? 1 : 0;
        
        // Handle gambar (file upload atau URL)
        $gambar = null;
        if (!empty($_FILES['gambar']['name'])) {
            $target_dir = "../uploads/produk/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $file_extension = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
            $new_filename = uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file)) {
                $gambar = 'uploads/produk/' . $new_filename;
            }
        } elseif (!empty($_POST['gambar_url'])) {
            $gambar = $_POST['gambar_url'];
        }
        
        // Insert produk utama
        $stmt = $pdo->prepare("INSERT INTO produk_toko 
            (kode_produk, nama_produk, kategori, harga, stok, has_variasi, status, deskripsi, spesifikasi, gambar) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $kode_produk, 
            $nama_produk, 
            $kategori, 
            $harga, 
            $stok, 
            $has_variasi, 
            $status, 
            $deskripsi, 
            $spesifikasi, 
            $gambar
        ]);
        
        $produk_id = $pdo->lastInsertId();
        
        // Jika produk memiliki variasi, simpan data variasi
        if ($has_variasi && isset($_POST['variasi_nama']) && is_array($_POST['variasi_nama'])) {
            $stmt_variasi = $pdo->prepare("INSERT INTO produk_variasi 
                (produk_id, nama_variasi, nilai_variasi, stok, harga_tambahan) 
                VALUES (?, ?, ?, ?, ?)");
            
            $total_stok_variasi = 0;
            
            foreach ($_POST['variasi_nama'] as $index => $nama_variasi) {
                $nilai_variasi = $_POST['variasi_nilai'][$index] ?? '';
                $stok_variasi = $_POST['variasi_stok'][$index] ?? 0;
                $harga_tambahan = $_POST['variasi_harga'][$index] ?? 0;
                
                $stmt_variasi->execute([
                    $produk_id,
                    $nama_variasi,
                    $nilai_variasi,
                    $stok_variasi,
                    $harga_tambahan
                ]);
                
                $total_stok_variasi += $stok_variasi;
            }
            
            // Update stok produk utama dengan total stok variasi
            $pdo->prepare("UPDATE produk_toko SET stok = ? WHERE id = ?")
                ->execute([$total_stok_variasi, $produk_id]);
        }
        
        $pdo->commit();
        header('Location: ../pages/toko.php?success=1&msg=Produk berhasil ditambahkan');
        exit();
        
    } catch (Exception $e) {
        $pdo->rollBack();
        header('Location: ../pages/toko.php?error=1&msg=' . urlencode($e->getMessage()));
        exit();
    }
}
?>
