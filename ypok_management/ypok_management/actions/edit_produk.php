<?php
require_once '../config/database.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $id = $_POST['id'];
        $kode_produk = $_POST['kode_produk'];
        $nama_produk = $_POST['nama_produk'];
        $kategori = $_POST['kategori'];
        $harga = $_POST['harga'];
        $stok = $_POST['stok'];
        $status = $_POST['status'];
        $deskripsi = $_POST['deskripsi'];
        $spesifikasi = $_POST['spesifikasi'] ?? null;
        
        // Debug: Log data yang diterima (hapus setelah testing)
        error_log("Edit Produk - ID: $id, Status: $status, Stok: $stok");
        
        // Validasi
        if (empty($kode_produk) || empty($nama_produk) || empty($kategori) || empty($harga) || empty($status)) {
            throw new Exception('Semua field wajib harus diisi');
        }
        
        // Check if kode_produk already exists (except current product)
        $check = $pdo->prepare("SELECT id FROM produk_toko WHERE kode_produk = ? AND id != ?");
        $check->execute([$kode_produk, $id]);
        if ($check->fetch()) {
            throw new Exception('Kode produk sudah digunakan');
        }
        
        // Handle gambar jika ada upload baru
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
                $old = $pdo->prepare("SELECT gambar FROM produk_toko WHERE id = ?");
                $old->execute([$id]);
                $old_data = $old->fetch();
                if ($old_data && $old_data['gambar'] && file_exists('../' . $old_data['gambar'])) {
                    unlink('../' . $old_data['gambar']);
                }
                $gambar = 'uploads/produk/' . $new_filename;
            }
        } elseif (!empty($_POST['gambar_url'])) {
            $gambar = $_POST['gambar_url'];
        }
        
        // Update produk - PERBAIKAN: Gabungkan menjadi satu query
        $updateQuery = "UPDATE produk_toko SET 
            kode_produk = ?, 
            nama_produk = ?, 
            kategori = ?, 
            harga = ?, 
            stok = ?, 
            status = ?, 
            deskripsi = ?, 
            spesifikasi = ?";
        
        $params = [
            $kode_produk,
            $nama_produk,
            $kategori,
            $harga,
            $stok,
            $status,
            $deskripsi,
            $spesifikasi
        ];
        
        // Tambahkan gambar jika ada
        if ($gambar) {
            $updateQuery .= ", gambar = ?";
            $params[] = $gambar;
        }
        
        $updateQuery .= " WHERE id = ?";
        $params[] = $id;
        
        $stmt = $pdo->prepare($updateQuery);
        $result = $stmt->execute($params);
        
        // Debug: Cek apakah update berhasil
        if ($result) {
            error_log("Update berhasil untuk produk ID: $id");
        } else {
            error_log("Update gagal untuk produk ID: $id");
        }
        
        header('Location: ../pages/toko.php?updated=1&msg=Produk berhasil diupdate');
        exit();
        
    } catch (Exception $e) {
        error_log("Error edit produk: " . $e->getMessage());
        header('Location: ../pages/toko.php?error=1&msg=' . urlencode($e->getMessage()));
        exit();
    }
}
?>
