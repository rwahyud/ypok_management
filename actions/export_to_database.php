<?php
require_once '../config/supabase.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

// Get parameters
$id = $_GET['id'] ?? null;
$type = $_GET['type'] ?? null;

if (!$id || !$type) {
    header('Location: ../pendaftaran.php?error=1&msg=' . urlencode('Parameter tidak lengkap'));
    exit();
}

try {
    $pdo->beginTransaction();

    if ($type === 'majelis_sabuk_hitam') {
        // Get data from pendaftaran_msh
        $stmt = $pdo->prepare("SELECT * FROM pendaftaran_msh WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();

        if (!$data) {
            throw new Exception('Data pendaftaran tidak ditemukan');
        }

        // Check if already exported
        if ($data['status'] !== 'Pending') {
            throw new Exception('Data sudah pernah di-export atau status bukan Pending');
        }

        // Insert to majelis_sabuk_hitam table
        $stmt_insert = $pdo->prepare("INSERT INTO majelis_sabuk_hitam
            (kode_msh, nama, foto, tempat_lahir, tanggal_lahir, jenis_kelamin, tingkat_dan, dojo_cabang, no_telp, email, alamat, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Aktif', NOW())");

        $stmt_insert->execute([
            $data['no_msh'],
            $data['nama'],
            $data['foto'],
            $data['tempat_lahir'],
            $data['tanggal_lahir'],
            $data['jenis_kelamin'],
            $data['tingkat_dan'],
            $data['dojo_cabang'],
            $data['no_telp'],
            $data['email'],
            $data['alamat']
        ]);

        // Update status in pendaftaran_msh
        $stmt_update = $pdo->prepare("UPDATE pendaftaran_msh SET status = 'Aktif' WHERE id = ?");
        $stmt_update->execute([$id]);

        $pdo->commit();

        header('Location: ../pendaftaran.php?tab=msh&success=1&msg=' . urlencode('Data berhasil di-export ke Data MSH'));
        exit();

    } elseif ($type === 'kohai') {
        // Get data from pendaftaran_kohai
        $stmt = $pdo->prepare("SELECT * FROM pendaftaran_kohai WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();

        if (!$data) {
            throw new Exception('Data pendaftaran tidak ditemukan');
        }

        // Check if already exported
        if ($data['status'] !== 'Pending') {
            throw new Exception('Data sudah pernah di-export atau status bukan Pending');
        }

        // Insert to kohai table
        $stmt_insert = $pdo->prepare("INSERT INTO kohai
            (kode_kohai, nama, foto, tempat_lahir, tanggal_lahir, jenis_kelamin, no_telp, email, alamat, nama_wali, no_telp_wali, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Aktif', NOW())");

        $stmt_insert->execute([
            $data['kode_kohai'],
            $data['nama'],
            $data['foto'],
            $data['tempat_lahir'],
            $data['tanggal_lahir'],
            $data['jenis_kelamin'],
            $data['no_telp'],
            $data['email'],
            $data['alamat'],
            $data['nama_wali'],
            $data['no_telp_wali']
        ]);

        // Update status in pendaftaran_kohai
        $stmt_update = $pdo->prepare("UPDATE pendaftaran_kohai SET status = 'Aktif' WHERE id = ?");
        $stmt_update->execute([$id]);

        $pdo->commit();

        header('Location: ../pendaftaran.php?tab=kohai&success=1&msg=' . urlencode('Data berhasil di-export ke Data Kohai'));
        exit();

    } else {
        throw new Exception('Tipe export tidak valid');
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    $tab = $type === 'majelis_sabuk_hitam' ? 'msh' : 'kohai';
    header('Location: ../pendaftaran.php?tab=' . $tab . '&error=1&msg=' . urlencode('Gagal export data: ' . $e->getMessage()));
    exit();
}
?>
