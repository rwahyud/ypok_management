<?php
require_once __DIR__ . '/../config/database.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Get form data
        $id = $_POST['id'];
        $nama_kegiatan = trim($_POST['nama_kegiatan']);
        $jenis_kegiatan = trim($_POST['kategori']);
        $tanggal_kegiatan = $_POST['tanggal_kegiatan'];
        $lokasi = trim($_POST['lokasi']);
        $lokasi_nama = $lokasi; // Store lokasi name
        $keterangan = isset($_POST['deskripsi']) ? trim($_POST['deskripsi']) : '';
        
        // Map status form ke format status terstandar di database.
        $status_form = $_POST['status'];
        $status_map = [
            'Selesai' => 'terlaksana',
            'Berlangsung' => 'terlaksana',
            'Dijadwalkan' => 'akan_datang',
            'Dibatalkan' => 'dibatalkan'
        ];
        $status = $status_map[$status_form] ?? 'akan_datang';
        
        // Check if lokasi exists, if not create it
        $stmt = $pdo->prepare("SELECT id FROM lokasi WHERE nama_lokasi = ?");
        $stmt->execute([$lokasi]);
        $lokasi_data = $stmt->fetch();
        
        if (!$lokasi_data) {
            $stmt = $pdo->prepare("INSERT INTO lokasi (nama_lokasi, status) VALUES (?, 'aktif')");
            $stmt->execute([$lokasi]);
            $lokasi_id = $pdo->lastInsertId();
        } else {
            $lokasi_id = $lokasi_data['id'];
        }
        
        // Prepare peserta MSH as JSON array
        $peserta_msh = null;
        if (!empty($_POST['peserta_msh'])) {
            $msh_ids = array_filter($_POST['peserta_msh'], function($id) {
                return !empty($id);
            });
            if (!empty($msh_ids)) {
                $peserta_msh = json_encode(array_values($msh_ids));
            }
        }
        
        // Prepare peserta Kohai as JSON array  
        $peserta_kohai = null;
        if (!empty($_POST['peserta_pelatih'])) {
            $kohai_ids = array_filter($_POST['peserta_pelatih'], function($id) {
                return !empty($id);
            });
            if (!empty($kohai_ids)) {
                $peserta_kohai = json_encode(array_values($kohai_ids));
            }
        }
        
        // Update kegiatan
        $stmt = $pdo->prepare("UPDATE kegiatan SET 
            nama_kegiatan = ?, 
            jenis_kegiatan = ?, 
            tanggal_kegiatan = ?, 
            lokasi_id = ?,
            lokasi_nama = ?,
            peserta_msh = ?,
            peserta_kohai = ?,
            keterangan = ?,
            status = ?
            WHERE id = ?");
        
        $result = $stmt->execute([
            $nama_kegiatan,
            $jenis_kegiatan,
            $tanggal_kegiatan,
            $lokasi_id,
            $lokasi_nama,
            $peserta_msh,
            $peserta_kohai,
            $keterangan,
            $status,
            $id
        ]);
        
        if (!$result) {
            throw new Exception("Gagal mengupdate data kegiatan");
        }
        
        header('Location: laporan_kegiatan.php?updated=1');
        exit();
        
    } catch(PDOException $e) {
        error_log("Error updating kegiatan: " . $e->getMessage());
        header('Location: kegiatan_edit.php?id=' . $_POST['id'] . '&error=' . urlencode($e->getMessage()));
        exit();
    } catch(Exception $e) {
        error_log("Error: " . $e->getMessage());
        header('Location: kegiatan_edit.php?id=' . $_POST['id'] . '&error=' . urlencode($e->getMessage()));
        exit();
    }
} else {
    header('Location: laporan_kegiatan.php');
    exit();
}
?>
