<?php
require_once '../config/database.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
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
    
    $foto = null;
    $foto_url = $_POST['foto_url'] ?? null;
    
    // Handle file upload
    if(isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $file_extension = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $file_name = time() . '_' . bin2hex(random_bytes(4)) . '.' . $file_extension;
        $file_path = 'uploads/pengurus/' . $file_name;

        if(ypok_upload_file($_FILES['foto']['tmp_name'], $file_path, $_FILES['foto']['type'] ?? 'application/octet-stream')) {
            $foto = $file_path;
        }
    }
    
    $stmt = $pdo->prepare("INSERT INTO pengurus (nik, nama, tempat_lahir, tanggal_lahir, jabatan, periode, no_sk, tanggal_sk, email, telepon, alamat, pendidikan_terakhir, foto, foto_url, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$nik, $nama, $tempat_lahir, $tanggal_lahir, $jabatan, $periode, $no_sk, $tanggal_sk, $email, $telepon, $alamat, $pendidikan_terakhir, $foto, $foto_url, $status]);
    
    header('Location: legalitas.php?success=1');
    exit();
}
?>
