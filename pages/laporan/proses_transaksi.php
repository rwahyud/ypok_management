<?php
require_once '../../config/supabase.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit();
}

// Handle GET untuk delete
if($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['action']) && $_GET['action'] == 'delete') {
    try {
        $id = $_GET['id'];
        
        $stmt = $pdo->prepare("DELETE FROM transaksi WHERE id = ?");
        $stmt->execute([$id]);
        
        header('Location: keuangan.php?deleted=1&msg=' . urlencode('Transaksi berhasil dihapus'));
        exit();
        
    } catch(PDOException $e) {
        error_log("Error delete transaksi: " . $e->getMessage());
        header('Location: keuangan.php?error=1&msg=' . urlencode('Terjadi kesalahan: ' . $e->getMessage()));
        exit();
    }
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        if($action == 'add') {
            // Tambah transaksi
            $tanggal = $_POST['tanggal'];
            $jenis = $_POST['jenis'];
            $kategori = $_POST['kategori'] ?? null;
            $keterangan = $_POST['keterangan'];
            $jumlah = $_POST['jumlah'];
            
            $stmt = $pdo->prepare("INSERT INTO transaksi (tanggal, jenis, kategori, keterangan, jumlah, created_by) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$tanggal, $jenis, $kategori, $keterangan, $jumlah, $_SESSION['user_id']]);
            
            header('Location: keuangan.php?success=1&msg=' . urlencode('Transaksi berhasil ditambahkan'));
            exit();
            
        } elseif($action == 'edit') {
            // Edit transaksi
            $id = $_POST['id'];
            $tanggal = $_POST['tanggal'];
            $jenis = $_POST['jenis'];
            $kategori = $_POST['kategori'] ?? null;
            $keterangan = $_POST['keterangan'];
            $jumlah = $_POST['jumlah'];
            
            $stmt = $pdo->prepare("UPDATE transaksi SET tanggal = ?, jenis = ?, kategori = ?, keterangan = ?, jumlah = ? WHERE id = ?");
            $stmt->execute([$tanggal, $jenis, $kategori, $keterangan, $jumlah, $id]);
            
            header('Location: keuangan.php?updated=1&msg=' . urlencode('Transaksi berhasil diupdate'));
            exit();
            
        } elseif($action == 'delete') {
            // Hapus transaksi
            $id = $_POST['id'];
            
            $stmt = $pdo->prepare("DELETE FROM transaksi WHERE id = ?");
            $stmt->execute([$id]);
            
            header('Location: keuangan.php?deleted=1&msg=' . urlencode('Transaksi berhasil dihapus'));
            exit();
        }
        
    } catch(PDOException $e) {
        error_log("Error proses transaksi: " . $e->getMessage());
        header('Location: keuangan.php?error=1&msg=' . urlencode('Terjadi kesalahan: ' . $e->getMessage()));
        exit();
    }
} else {
    header('Location: keuangan.php');
    exit();
}
