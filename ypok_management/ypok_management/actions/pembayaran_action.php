<?php
require_once __DIR__ . '/../config/database.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

// Handle GET request untuk mengambil data
if(isset($_GET['action']) && $_GET['action'] == 'get' && isset($_GET['id'])) {
    try {
        $id = $_GET['id'];
        $stmt = $pdo->prepare("SELECT p.* FROM pembayaran p WHERE p.id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if($data) {
            header('Content-Type: application/json');
            echo json_encode($data);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Data tidak ditemukan']);
        }
        exit();
    } catch(PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode(['error' => $e->getMessage()]);
        exit();
    }
}

// Handle POST request
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    
    try {
        if($action == 'tambah') {
            // Siapkan data untuk insert dengan validasi
            $nama_kohai = trim($_POST['nama_kohai'] ?? '');
            if(empty($nama_kohai)) throw new Exception('Nama kohai harus diisi');
            
            // Validate and cast numeric fields
            $tanggal = trim($_POST['tanggal'] ?? '');
            if(empty($tanggal)) throw new Exception('Tanggal harus diisi');
            
            $jumlah = isset($_POST['jumlah']) ? (float)$_POST['jumlah'] : 0;
            if($jumlah < 0) throw new Exception('Jumlah tidak boleh negatif');
            
            $total_tagihan = isset($_POST['total_tagihan']) && $_POST['total_tagihan'] !== '' ? (float)$_POST['total_tagihan'] : null;
            if($total_tagihan && $total_tagihan < 0) throw new Exception('Total tagihan tidak boleh negatif');
            
            $nominal_dibayar = isset($_POST['nominal_dibayar']) && $_POST['nominal_dibayar'] !== '' ? (float)$_POST['nominal_dibayar'] : null;
            if($nominal_dibayar && $nominal_dibayar < 0) throw new Exception('Nominal dibayar tidak boleh negatif');
            
            $sisa = isset($_POST['sisa']) && $_POST['sisa'] !== '' ? (float)$_POST['sisa'] : null;
            if($sisa && $sisa < 0) throw new Exception('Sisa tidak boleh negatif');

            $stmt = $pdo->prepare("INSERT INTO pembayaran (tanggal, kategori, nama_kohai, keterangan, jumlah, total_tagihan, nominal_dibayar, sisa, metode_pembayaran, status, created_at)
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

            $stmt->execute([
                $tanggal,
                $_POST['kategori'] ?? '',
                $nama_kohai,
                $_POST['keterangan'] ?? '',
                $jumlah,
                $total_tagihan,
                $nominal_dibayar,
                $sisa,
                $_POST['metode_pembayaran'] ?? '',
                $_POST['status'] ?? 'pending'
            ]);

            header('Location: ../pages/pembayaran.php?success=1&msg=' . urlencode('Data pembayaran berhasil ditambahkan'));
            exit();

        } elseif($action == 'edit') {
            // Siapkan data untuk update dengan validasi
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            if(!$id) throw new Exception('ID pembayaran tidak valid');
            
            $nama_kohai = trim($_POST['nama_kohai'] ?? '');
            if(empty($nama_kohai)) throw new Exception('Nama kohai harus diisi');
            
            $tanggal = trim($_POST['tanggal'] ?? '');
            if(empty($tanggal)) throw new Exception('Tanggal harus diisi');
            
            $jumlah = isset($_POST['jumlah']) ? (float)$_POST['jumlah'] : 0;
            if($jumlah < 0) throw new Exception('Jumlah tidak boleh negatif');
            
            $total_tagihan = isset($_POST['total_tagihan']) && $_POST['total_tagihan'] !== '' ? (float)$_POST['total_tagihan'] : null;
            if($total_tagihan && $total_tagihan < 0) throw new Exception('Total tagihan tidak boleh negatif');
            
            $nominal_dibayar = isset($_POST['nominal_dibayar']) && $_POST['nominal_dibayar'] !== '' ? (float)$_POST['nominal_dibayar'] : null;
            if($nominal_dibayar && $nominal_dibayar < 0) throw new Exception('Nominal dibayar tidak boleh negatif');
            
            $sisa = isset($_POST['sisa']) && $_POST['sisa'] !== '' ? (float)$_POST['sisa'] : null;
            if($sisa && $sisa < 0) throw new Exception('Sisa tidak boleh negatif');

            $stmt = $pdo->prepare("UPDATE pembayaran SET
                                  tanggal = ?,
                                  kategori = ?,
                                  nama_kohai = ?,
                                  keterangan = ?,
                                  jumlah = ?,
                                  total_tagihan = ?,
                                  nominal_dibayar = ?,
                                  sisa = ?,
                                  metode_pembayaran = ?,
                                  status = ?
                                  WHERE id = ?");

            $stmt->execute([
                $tanggal,
                $_POST['kategori'] ?? '',
                $nama_kohai,
                $_POST['keterangan'] ?? '',
                $jumlah,
                $total_tagihan,
                $nominal_dibayar,
                $sisa,
                $_POST['metode_pembayaran'],
                $_POST['status'],
                $_POST['id']
            ]);

            header('Location: ../pages/pembayaran.php?updated=1&msg=' . urlencode('Data pembayaran berhasil diupdate'));
            exit();

        } elseif($action == 'hapus') {
            $stmt = $pdo->prepare("DELETE FROM pembayaran WHERE id = ?");
            $stmt->execute([$_POST['id']]);

            header('Location: ../pages/pembayaran.php?deleted=1&msg=' . urlencode('Data pembayaran berhasil dihapus'));
            exit();
        }

    } catch(PDOException $e) {
        header('Location: ../pages/pembayaran.php?error=1&msg=' . urlencode('Terjadi kesalahan: ' . $e->getMessage()));
        exit();
    }
}

header('Location: ../pages/pembayaran.php');
exit();
?>
