<?php
require_once '../config/supabase.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

// Handle GET request untuk mengambil data
if(isset($_GET['action']) && $_GET['action'] == 'get' && isset($_GET['id'])) {
    try {
        $id = $_GET['id'];
        $stmt = $pdo->prepare("SELECT p.*, 
                               k.nama as nama_kohai,
                               m.nama as nama_msh
                               FROM pembayaran p
                               LEFT JOIN kohai k ON p.kohai_id = k.id
                               LEFT JOIN majelis_sabuk_hitam m ON p.msh_id = m.id
                               WHERE p.id = ?");
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
            // Siapkan data untuk insert - mapping ke kolom schema yang benar
            $kohai_id = !empty($_POST['kohai_id']) ? $_POST['kohai_id'] : null;
            $msh_id = !empty($_POST['msh_id']) ? $_POST['msh_id'] : null;
            
            $stmt = $pdo->prepare("INSERT INTO pembayaran (tanggal_bayar, jenis_pembayaran, kohai_id, msh_id, keterangan, jumlah, metode_pembayaran, status, created_at)
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)");

            $stmt->execute([
                $_POST['tanggal'],
                $_POST['kategori'],
                $kohai_id,
                $msh_id,
                $_POST['keterangan'],
                $_POST['jumlah'],
                $_POST['metode_pembayaran'],
                $_POST['status']
            ]);

            header('Location: ../pembayaran.php?success=1&msg=' . urlencode('Data pembayaran berhasil ditambahkan'));
            exit();

        } elseif($action == 'edit') {
            // Siapkan data untuk update - mapping ke kolom schema yang benar
            $kohai_id = !empty($_POST['kohai_id']) ? $_POST['kohai_id'] : null;
            $msh_id = !empty($_POST['msh_id']) ? $_POST['msh_id'] : null;

            $stmt = $pdo->prepare("UPDATE pembayaran SET
                                  tanggal_bayar = ?,
                                  jenis_pembayaran = ?,
                                  kohai_id = ?,
                                  msh_id = ?,
                                  keterangan = ?,
                                  jumlah = ?,
                                  metode_pembayaran = ?,
                                  status = ?,
                                  updated_at = CURRENT_TIMESTAMP
                                  WHERE id = ?");

            $stmt->execute([
                $_POST['tanggal'],
                $_POST['kategori'],
                $kohai_id,
                $msh_id,
                $_POST['keterangan'],
                $_POST['jumlah'],
                $_POST['metode_pembayaran'],
                $_POST['status'],
                $_POST['id']
            ]);

            header('Location: ../pembayaran.php?updated=1&msg=' . urlencode('Data pembayaran berhasil diupdate'));
            exit();

        } elseif($action == 'hapus') {
            $stmt = $pdo->prepare("DELETE FROM pembayaran WHERE id = ?");
            $stmt->execute([$_POST['id']]);

            header('Location: ../pembayaran.php?deleted=1&msg=' . urlencode('Data pembayaran berhasil dihapus'));
            exit();
        }

    } catch(PDOException $e) {
        header('Location: ../pembayaran.php?error=1&msg=' . urlencode('Terjadi kesalahan: ' . $e->getMessage()));
        exit();
    }
}

header('Location: ../pembayaran.php');
exit();
?>
