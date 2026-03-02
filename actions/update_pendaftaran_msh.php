<?php
require_once '../config/supabase.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $id = $_POST['id'];
        $foto = $_POST['foto_lama'] ?? null;
        
        // Handle file upload
        if(isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            $upload_dir = '../uploads/msh/';
            if(!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $new_filename = 'MSH_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if(move_uploaded_file($_FILES['foto']['tmp_name'], $upload_path)) {
                // Hapus foto lama jika ada dan bukan URL
                if($foto && file_exists('../' . $foto) && strpos($foto, 'http') === false) {
                    @unlink('../' . $foto);
                }
                $foto = 'uploads/msh/' . $new_filename;
            }
        } elseif(!empty($_POST['foto_url'])) {
            $foto = $_POST['foto_url'];
        }
        
        $stmt = $pdo->prepare("UPDATE pendaftaran_msh SET no_msh = ?, nama = ?, foto = ?, tempat_lahir = ?, tanggal_lahir = ?, jenis_kelamin = ?, tingkat_dan = ?, dojo_cabang = ?, no_telp = ?, email = ?, alamat = ? WHERE id = ?");
        
        $stmt->execute([
            $_POST['no_msh'],
            $_POST['nama'],
            $foto,
            $_POST['tempat_lahir'],
            $_POST['tanggal_lahir'],
            $_POST['jenis_kelamin'],
            $_POST['tingkat_dan'],
            $_POST['dojo_cabang'],
            $_POST['no_telp'],
            $_POST['email'] ?? null,
            $_POST['alamat'],
            $id
        ]);
        
        header('Location: ../pendaftaran.php?tab=msh&updated=1&msg=Data MSH berhasil diupdate');
    } catch(Exception $e) {
        header('Location: ../edit_pendaftaran.php?id='.$_POST['id'].'&type=msh&error=1&msg=' . urlencode($e->getMessage()));
    }
} else {
    header('Location: ../pendaftaran.php');
}
exit();
