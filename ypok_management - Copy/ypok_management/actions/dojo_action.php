<?php
session_start();
require_once '../config/database.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// CREATE
if($action == 'create') {
    try {
        // Validate required fields
        if(empty($_POST['provinsi_id']) || empty($_POST['nama_dojo'])) {
            throw new Exception('Data tidak lengkap');
        }

        $provinsi_id = (int)$_POST['provinsi_id'];
        $nama_dojo = trim($_POST['nama_dojo']);
        $alamat_lengkap = trim($_POST['alamat_lengkap']);
        $nama_ketua = trim($_POST['nama_ketua']);
        $no_telepon = trim($_POST['no_telepon']);
        $total_anggota = (int)$_POST['total_anggota'];
        $anggota_aktif = (int)$_POST['anggota_aktif'];
        $anggota_non_aktif = (int)$_POST['anggota_non_aktif'];
        $status = $_POST['status'];

        $stmt = $pdo->prepare("
            INSERT INTO dojo (
                provinsi_id, nama_dojo, alamat_lengkap, nama_ketua, no_telepon,
                total_anggota, anggota_aktif, anggota_non_aktif, status, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $provinsi_id, $nama_dojo, $alamat_lengkap, $nama_ketua, $no_telepon,
            $total_anggota, $anggota_aktif, $anggota_non_aktif, $status
        ]);
        
        header('Location: ../lokasi.php?success=1&msg=' . urlencode('Dojo berhasil ditambahkan'));
        exit();
        
    } catch(Exception $e) {
        error_log('Error creating dojo: ' . $e->getMessage());
        header('Location: ../lokasi.php?error=1&msg=' . urlencode($e->getMessage()));
        exit();
    }
}

// UPDATE
if($action == 'update') {
    try {
        if(empty($_POST['id']) || empty($_POST['nama_dojo'])) {
            throw new Exception('Data tidak lengkap');
        }

        $id = (int)$_POST['id'];
        $provinsi_id = (int)$_POST['provinsi_id'];
        $nama_dojo = trim($_POST['nama_dojo']);
        $alamat_lengkap = trim($_POST['alamat_lengkap']);
        $nama_ketua = trim($_POST['nama_ketua']);
        $no_telepon = trim($_POST['no_telepon']);
        $total_anggota = (int)$_POST['total_anggota'];
        $anggota_aktif = (int)$_POST['anggota_aktif'];
        $anggota_non_aktif = (int)$_POST['anggota_non_aktif'];
        $status = $_POST['status'];

        $stmt = $pdo->prepare("
            UPDATE dojo SET 
                nama_dojo = ?, alamat_lengkap = ?, nama_ketua = ?, no_telepon = ?,
                total_anggota = ?, anggota_aktif = ?, anggota_non_aktif = ?, 
                status = ?, updated_at = NOW()
            WHERE id = ?
        ");
        
        $stmt->execute([
            $nama_dojo, $alamat_lengkap, $nama_ketua, $no_telepon,
            $total_anggota, $anggota_aktif, $anggota_non_aktif, $status, $id
        ]);
        
        header('Location: ../lokasi.php?updated=1&msg=' . urlencode('Dojo berhasil diupdate'));
        exit();
        
    } catch(Exception $e) {
        error_log('Error updating dojo: ' . $e->getMessage());
        header('Location: ../lokasi.php?error=1&msg=' . urlencode($e->getMessage()));
        exit();
    }
}

// DELETE
if($action == 'delete') {
    try {
        if(empty($_GET['id'])) {
            throw new Exception('ID tidak valid');
        }

        $id = (int)$_GET['id'];
        
        $stmt = $pdo->prepare("DELETE FROM dojo WHERE id = ?");
        $stmt->execute([$id]);
        
        header('Location: ../lokasi.php?deleted=1&msg=' . urlencode('Dojo berhasil dihapus'));
        exit();
        
    } catch(Exception $e) {
        error_log('Error deleting dojo: ' . $e->getMessage());
        header('Location: ../lokasi.php?error=1&msg=' . urlencode($e->getMessage()));
        exit();
    }
}

header('Location: ../lokasi.php?error=1&msg=' . urlencode('Aksi tidak valid'));
exit();
?>
