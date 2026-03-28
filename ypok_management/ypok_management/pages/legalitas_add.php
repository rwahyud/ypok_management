<?php
require_once __DIR__ . '/../config/database.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $jenis_dokumen = $_POST['jenis_dokumen'];
    $nomor_dokumen = $_POST['nomor_dokumen'];
    $tanggal_terbit = $_POST['tanggal_terbit'];
    $is_permanent = isset($_POST['is_permanent']) ? 1 : 0;
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
    
    $file_dokumen = null;
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
            'application/x-rar-compressed'
            // Note: Removed "application/octet-stream" for security - too broad
        ];
        
        $file_type = $_FILES['file_dokumen']['type'];
        $file_size = $_FILES['file_dokumen']['size'];
        $max_size = 10 * 1024 * 1024; // 10MB in bytes
        
        // Check file extension as fallback
        $file_extension = strtolower(pathinfo($_FILES['file_dokumen']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'zip', 'rar'];
        
        if((in_array($file_type, $allowed_types) || in_array($file_extension, $allowed_extensions)) && $file_size <= $max_size) {
            $file_name = time() . '_' . uniqid() . '.' . $file_extension;
            $file_path = 'uploads/dokumen/' . $file_name;

            if(ypok_upload_file($_FILES['file_dokumen']['tmp_name'], $file_path, $_FILES['file_dokumen']['type'] ?? 'application/octet-stream')) {
                $file_dokumen = $file_path;
            }
        }
    }
    
    $stmt = $pdo->prepare("INSERT INTO legalitas (jenis_dokumen, nomor_dokumen, tanggal_terbit, tanggal_kadaluarsa, instansi_penerbit, status, keterangan, file_dokumen, is_permanent) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$jenis_dokumen, $nomor_dokumen, $tanggal_terbit, $tanggal_kadaluarsa, $instansi_penerbit, $status, $keterangan, $file_dokumen, $is_permanent]);
    
    header('Location: legalitas.php?success=1#dokumenSection');
    exit();
}
?>
