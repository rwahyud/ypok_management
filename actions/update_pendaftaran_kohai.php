<?php
require_once '../config/supabase.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $id = $_POST['id'];
        $foto = $_POST['foto_lama'];
        
        // Handle file upload
        if(isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            $upload_dir = '../uploads/kohai/';
            if(!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $new_filename = 'KOHAI_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if(move_uploaded_file($_FILES['foto']['tmp_name'], $upload_path)) {
                // Hapus foto lama jika ada
                if($foto && file_exists('../' . $foto)) {
                    unlink('../' . $foto);
                }
                $foto = 'uploads/kohai/' . $new_filename;
            }
        } elseif(!empty($_POST['foto_url'])) {
            $foto = $_POST['foto_url'];
        }
        
        $stmt = $pdo->prepare("UPDATE pendaftaran_kohai SET kode_kohai = ?, nama = ?, foto = ?, tempat_lahir = ?, tanggal_lahir = ?, jenis_kelamin = ?, no_telp = ?, email = ?, alamat = ?, nama_wali = ?, no_telp_wali = ? WHERE id = ?");
        
        $stmt->execute([
            $_POST['kode_kohai'],
            $_POST['nama'],
            $foto,
            $_POST['tempat_lahir'],
            $_POST['tanggal_lahir'],
            $_POST['jenis_kelamin'],
            $_POST['no_telp'],
            $_POST['email'] ?? null,
            $_POST['alamat'],
            $_POST['nama_wali'],
            $_POST['no_telp_wali'],
            $id
        ]);
        
        header('Location: ../pendaftaran.php?tab=kohai&updated=1&msg=Data Kohai berhasil diupdate');
    } catch(Exception $e) {
        header('Location: ../edit_pendaftaran.php?id='.$_POST['id'].'&type=kohai&error=1&msg=' . urlencode($e->getMessage()));
    }
} else {
    header('Location: ../pendaftaran.php');
}
exit();
