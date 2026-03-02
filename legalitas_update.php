<?php
require_once 'config/supabase.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $jenis_dokumen = $_POST['jenis_dokumen'];
    $nomor_dokumen = $_POST['nomor_dokumen'];
    $tanggal_terbit = $_POST['tanggal_terbit'];
    $is_permanent = isset($_POST['is_permanent']) ? true : false;
    $dalam_proses = isset($_POST['dalam_proses']) ? 1 : 0;
    
    // Handle permanent document
    if($is_permanent) {
        $tanggal_kadaluarsa = '9999-12-31';
        $status = 'Aktif';
    } elseif($dalam_proses) {
        // Jika dalam proses, tanggal kadaluarsa opsional
        $tanggal_kadaluarsa = !empty($_POST['tanggal_kadaluarsa']) ? $_POST['tanggal_kadaluarsa'] : '9999-12-31';
        $status = 'Dalam Proses';
    } else {
        $tanggal_kadaluarsa = $_POST['tanggal_kadaluarsa'];
        
        // Auto-calculate status based on date
        $today = date('Y-m-d');
        $warning_date = date('Y-m-d', strtotime('+30 days'));
        
        if($tanggal_kadaluarsa < $today) {
            $status = 'Kadaluarsa';
        } elseif($tanggal_kadaluarsa >= $today && $tanggal_kadaluarsa <= $warning_date) {
            $status = 'Akan Kadaluarsa';
        } else {
            $status = 'Aktif';
        }
    }
    
    $instansi_penerbit = $_POST['instansi_penerbit'];
    $keterangan = $_POST['keterangan'] ?? null;
    
    // Handle file upload if new file is uploaded
    if(isset($_FILES['file_dokumen']) && $_FILES['file_dokumen']['error'] == 0) {
        // Validate file type and size
        $allowed_types = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'image/jpeg',
            'image/jpg',
            'image/png',
            'application/zip',
            'application/x-zip-compressed',
            'application/x-rar-compressed',
            'application/octet-stream'
        ];
        
        $file_type = $_FILES['file_dokumen']['type'];
        $file_size = $_FILES['file_dokumen']['size'];
        $max_size = 10 * 1024 * 1024; // 10MB
        
        // Check file extension as fallback
        $file_extension = strtolower(pathinfo($_FILES['file_dokumen']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'zip', 'rar'];
        
        if((in_array($file_type, $allowed_types) || in_array($file_extension, $allowed_extensions)) && $file_size <= $max_size) {
            $upload_dir = 'uploads/dokumen/';
            if(!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Delete old file if exists
            $stmt_old = $pdo->prepare("SELECT file_dokumen FROM legalitas WHERE id = ?");
            $stmt_old->execute([$id]);
            $old_file = $stmt_old->fetch();
            if($old_file && $old_file['file_dokumen'] && file_exists($old_file['file_dokumen'])) {
                unlink($old_file['file_dokumen']);
            }
            
            $file_name = time() . '_' . uniqid() . '.' . $file_extension;
            $file_path = $upload_dir . $file_name;
            
            if(move_uploaded_file($_FILES['file_dokumen']['tmp_name'], $file_path)) {
                $stmt = $pdo->prepare("UPDATE legalitas SET jenis_dokumen=?, nomor_dokumen=?, tanggal_terbit=?, tanggal_kadaluarsa=?, instansi_penerbit=?, status=?, keterangan=?, file_dokumen=?, is_permanent=? WHERE id=?");
                $stmt->execute([$jenis_dokumen, $nomor_dokumen, $tanggal_terbit, $tanggal_kadaluarsa, $instansi_penerbit, $status, $keterangan, $file_path, $is_permanent, $id]);
            }
        }
    } else {
        $stmt = $pdo->prepare("UPDATE legalitas SET jenis_dokumen=?, nomor_dokumen=?, tanggal_terbit=?, tanggal_kadaluarsa=?, instansi_penerbit=?, status=?, keterangan=?, is_permanent=? WHERE id=?");
        $stmt->execute([$jenis_dokumen, $nomor_dokumen, $tanggal_terbit, $tanggal_kadaluarsa, $instansi_penerbit, $status, $keterangan, $is_permanent, $id]);
    }
    
    header('Location: legalitas.php?updated=1#dokumenSection');
    exit();
}
?>
