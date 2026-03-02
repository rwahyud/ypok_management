<?php
require_once 'config/supabase.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $nik = $_POST['nik'];
    $nama = $_POST['nama'];
    $tempat_lahir = $_POST['tempat_lahir'];
    $tanggal_lahir = $_POST['tanggal_lahir'];
    
    // Handle custom jabatan
    $jabatan = $_POST['jabatan'];
    if ($jabatan === 'custom') {
        $jabatan = $_POST['jabatan_custom'];
    }
    
    $periode = $_POST['periode'];
    $no_sk = $_POST['no_sk'];
    $tanggal_sk = $_POST['tanggal_sk'];
    $email = $_POST['email'];
    $telepon = $_POST['telepon'];
    $alamat = $_POST['alamat'];
    $pendidikan_terakhir = $_POST['pendidikan_terakhir'];
    $status = $_POST['status'];
    $foto_url = $_POST['foto_url'] ?? null;
    
    // Handle file upload if new file is uploaded
    if(isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $upload_dir = 'uploads/pengurus/';
        if(!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_name = time() . '_' . $_FILES['foto']['name'];
        $file_path = $upload_dir . $file_name;
        
        if(move_uploaded_file($_FILES['foto']['tmp_name'], $file_path)) {
            $stmt = $pdo->prepare("UPDATE pengurus SET nik=?, nama=?, tempat_lahir=?, tanggal_lahir=?, jabatan=?, periode=?, no_sk=?, tanggal_sk=?, email=?, telepon=?, alamat=?, pendidikan_terakhir=?, foto=?, foto_url=?, status=? WHERE id=?");
            $stmt->execute([$nik, $nama, $tempat_lahir, $tanggal_lahir, $jabatan, $periode, $no_sk, $tanggal_sk, $email, $telepon, $alamat, $pendidikan_terakhir, $file_path, $foto_url, $status, $id]);
        }
    } else {
        $stmt = $pdo->prepare("UPDATE pengurus SET nik=?, nama=?, tempat_lahir=?, tanggal_lahir=?, jabatan=?, periode=?, no_sk=?, tanggal_sk=?, email=?, telepon=?, alamat=?, pendidikan_terakhir=?, foto_url=?, status=? WHERE id=?");
        $stmt->execute([$nik, $nama, $tempat_lahir, $tanggal_lahir, $jabatan, $periode, $no_sk, $tanggal_sk, $email, $telepon, $alamat, $pendidikan_terakhir, $foto_url, $status, $id]);
    }
    
    header('Location: legalitas.php?updated=1');
    exit();
}
?>
