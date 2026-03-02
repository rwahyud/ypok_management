<?php
require_once '../config/supabase.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $foto = null;
        
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
                $foto = 'uploads/kohai/' . $new_filename;
            }
        } elseif(!empty($_POST['foto_url'])) {
            $foto = $_POST['foto_url'];
        }
        
        // Pastikan status default adalah Pending
        $stmt = $pdo->prepare("INSERT INTO pendaftaran_kohai (kode_kohai, nama, foto, tempat_lahir, tanggal_lahir, jenis_kelamin, no_telp, email, alamat, nama_wali, no_telp_wali, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())");
        
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
            $_POST['no_telp_wali']
        ]);
        
        header('Location: ../pendaftaran.php?tab=kohai&success=1&msg=Data Kohai berhasil didaftarkan. Silakan klik Export untuk memindahkan ke data utama.');
    } catch(Exception $e) {
        header('Location: ../pendaftaran.php?tab=kohai&error=1&msg=' . urlencode($e->getMessage()));
    }
} else {
    header('Location: ../pendaftaran.php');
}
exit();
