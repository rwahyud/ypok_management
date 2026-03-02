<?php
require_once 'config/supabase.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Add Transaction
if($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $tanggal = $_POST['tanggal'];
    $jenis = $_POST['jenis'];
    $kategori = $_POST['kategori'] ?? '';
    $keterangan = $_POST['keterangan'];
    $jumlah = $_POST['jumlah'];
    
    // Calculate saldo
    $last_saldo = $pdo->query("SELECT saldo FROM transaksi ORDER BY id DESC LIMIT 1")->fetchColumn();
    $saldo = $last_saldo ?? 0;
    
    if($jenis === 'pemasukan') {
        $saldo += $jumlah;
    } else {
        $saldo -= $jumlah;
    }
    
    $stmt = $pdo->prepare("INSERT INTO transaksi (tanggal, jenis, kategori, keterangan, jumlah, saldo, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$tanggal, $jenis, $kategori, $keterangan, $jumlah, $saldo, $_SESSION['user_id']]);
    
    header('Location: laporan_keuangan.php?success=1');
    exit();
}

// Edit Transaction
if($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $tanggal = $_POST['tanggal'];
    $jenis = $_POST['jenis'];
    $kategori = $_POST['kategori'] ?? '';
    $keterangan = $_POST['keterangan'];
    $jumlah = $_POST['jumlah'];
    
    $stmt = $pdo->prepare("UPDATE transaksi SET tanggal=?, jenis=?, kategori=?, keterangan=?, jumlah=? WHERE id=?");
    $stmt->execute([$tanggal, $jenis, $kategori, $keterangan, $jumlah, $id]);
    
    // Recalculate all saldo
    recalculateSaldo($pdo);
    
    header('Location: laporan_keuangan.php?updated=1');
    exit();
}

// Delete Transaction
if($action === 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    
    $stmt = $pdo->prepare("DELETE FROM transaksi WHERE id=?");
    $stmt->execute([$id]);
    
    // Recalculate all saldo
    recalculateSaldo($pdo);
    
    header('Location: laporan_keuangan.php?deleted=1');
    exit();
}

function recalculateSaldo($pdo) {
    $transactions = $pdo->query("SELECT * FROM transaksi ORDER BY tanggal ASC, id ASC")->fetchAll();
    $saldo = 0;
    
    foreach($transactions as $trans) {
        if($trans['jenis'] === 'pemasukan') {
            $saldo += $trans['jumlah'];
        } else {
            $saldo -= $trans['jumlah'];
        }
        
        $pdo->prepare("UPDATE transaksi SET saldo=? WHERE id=?")->execute([$saldo, $trans['id']]);
    }
}

header('Location: laporan_keuangan.php');
exit();
?>
