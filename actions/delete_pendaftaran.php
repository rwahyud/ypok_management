<?php
require_once '../config/supabase.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

$id = $_GET['id'] ?? 0;
$type = $_GET['type'] ?? '';

if($id && ($type == 'msh' || $type == 'kohai')) {
    try {
        if($type == 'msh') {
            // Ambil foto untuk dihapus
            $stmt = $pdo->prepare("SELECT foto FROM pendaftaran_msh WHERE id = ?");
            $stmt->execute([$id]);
            $data = $stmt->fetch();
            
            // Hapus file foto jika ada dan bukan URL
            if($data && $data['foto'] && file_exists('../' . $data['foto']) && strpos($data['foto'], 'http') === false) {
                @unlink('../' . $data['foto']);
            }
            
            // Hapus data dari database
            $stmt = $pdo->prepare("DELETE FROM pendaftaran_msh WHERE id = ?");
            $stmt->execute([$id]);
            header('Location: ../pendaftaran.php?tab=msh&deleted=1&msg=Data pendaftaran MSH berhasil dihapus');
        } else {
            // Ambil foto untuk dihapus
            $stmt = $pdo->prepare("SELECT foto FROM pendaftaran_kohai WHERE id = ?");
            $stmt->execute([$id]);
            $data = $stmt->fetch();
            
            // Hapus file foto jika ada dan bukan URL
            if($data && $data['foto'] && file_exists('../' . $data['foto']) && strpos($data['foto'], 'http') === false) {
                @unlink('../' . $data['foto']);
            }
            
            // Hapus data dari database
            $stmt = $pdo->prepare("DELETE FROM pendaftaran_kohai WHERE id = ?");
            $stmt->execute([$id]);
            header('Location: ../pendaftaran.php?tab=kohai&deleted=1&msg=Data pendaftaran Kohai berhasil dihapus');
        }
    } catch(Exception $e) {
        header('Location: ../pendaftaran.php?tab='.$type.'&error=1&msg=Gagal menghapus data: ' . urlencode($e->getMessage()));
    }
} else {
    header('Location: ../pendaftaran.php?error=1&msg=Parameter tidak valid');
}
exit();
