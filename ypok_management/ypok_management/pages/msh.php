<?php
require_once '../config/database.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

// Handle Delete
if(isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM master_sabuk_hitam WHERE id = ?");
    if($stmt->execute([$id])) {
        header('Location: msh.php?deleted=1');
        exit();
    }
}

// Handle form submission (Add & Edit)
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if($_POST['action'] == 'add_msh' || $_POST['action'] == 'edit_msh') {
        $nomor_msh = $_POST['nomor_msh'] ?? '';
        $nama = $_POST['nama'];
        $tempat_lahir = $_POST['tempat_lahir'] ?? '';
        $tanggal_lahir = $_POST['tanggal_lahir'] ?? null;
        $jenis_kelamin = $_POST['jenis_kelamin'] ?? 'L';
        $tingkat_dan = $_POST['tingkat_dan'];
        $dojo_cabang = $_POST['dojo_cabang'];
        $no_telp = $_POST['no_telp'];
        $email = $_POST['email'] ?? '';
        $nomor_ijazah = $_POST['nomor_ijazah'] ?? '';
        $tanggal_ujian = $_POST['tanggal_ujian'] ?? null;
        $jenis_keanggotaan = $_POST['jenis_keanggotaan'] ?? 'Reguler';
        $status = $_POST['status'];
        $alamat = $_POST['alamat'];
        
        // Handle foto upload with security validation
        $foto = '';
        if(isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            // Validate file type
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $file_type = mime_content_type($_FILES['foto']['tmp_name']) ?: $_FILES['foto']['type'];
            $file_ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if(!in_array($file_type, $allowed_types) || !in_array($file_ext, $allowed_ext)) {
                throw new Exception('File type tidak diizinkan. Hanya gambar (JPG, PNG, GIF, WebP) yang diperbolehkan');
            }
            
            // Validate file size (max 5MB)
            if($_FILES['foto']['size'] > 5242880) {
                throw new Exception('Ukuran file terlalu besar. Maksimal 5MB');
            }
            
            $target_dir = "uploads/msh/";
            if(!file_exists($target_dir)) {
                mkdir($target_dir, 0755, true);
            }
            $foto = $target_dir . time() . '_' . bin2hex(random_bytes(4)) . '.' . $file_ext;
            if(!move_uploaded_file($_FILES['foto']['tmp_name'], $foto)) {
                throw new Exception('Gagal mengupload file');
            }
        } elseif(!empty($_POST['foto_url'])) {
            // Validate and sanitize URL
            $foto = filter_var($_POST['foto_url'], FILTER_VALIDATE_URL) ? $_POST['foto_url'] : '';
        }
        
        if($_POST['action'] == 'edit_msh') {
            $id = $_POST['id'];
            
            $sql = "UPDATE master_sabuk_hitam SET 
                    nama = ?, 
                    no_msh = ?, 
                    tingkat_dan = ?, 
                    no_telp = ?, 
                    email = ?, 
                    dojo_cabang = ?, 
                    status = ?, 
                    alamat = ?, 
                    tempat_lahir = ?, 
                    tanggal_lahir = ?, 
                    jenis_kelamin = ?, 
                    nomor_ijazah = ?,
                    tanggal_ujian = ?,
                    jenis_keanggotaan = ?";
            
            $params = [$nama, $nomor_msh, $tingkat_dan, $no_telp, $email, $dojo_cabang, $status, $alamat, $tempat_lahir, $tanggal_lahir, $jenis_kelamin, $nomor_ijazah, $tanggal_ujian, $jenis_keanggotaan];
            
            if($foto) {
                $sql .= ", foto = ?";
                $params[] = $foto;
            }
            
            $sql .= " WHERE id = ?";
            $params[] = $id;
            
            $stmt = $pdo->prepare($sql);
            
            if($stmt->execute($params)) {
                // Delete old prestasi and insert new ones
                $pdo->prepare("DELETE FROM prestasi_msh WHERE msh_id = ?")->execute([$id]);
                if(isset($_POST['prestasi_nama']) && is_array($_POST['prestasi_nama'])) {
                    foreach($_POST['prestasi_nama'] as $prestasi) {
                        if(!empty($prestasi)) {
                            $stmt = $pdo->prepare("INSERT INTO prestasi_msh (msh_id, nama_prestasi) VALUES (?, ?)");
                            $stmt->execute([$id, $prestasi]);
                        }
                    }
                }
                
                // Delete old sertifikasi and insert new ones
                $pdo->prepare("DELETE FROM sertifikasi_msh WHERE msh_id = ?")->execute([$id]);
                if(isset($_POST['sertifikasi_nama']) && is_array($_POST['sertifikasi_nama'])) {
                    foreach($_POST['sertifikasi_nama'] as $index => $sert_nama) {
                        if(!empty($sert_nama)) {
                            $stmt = $pdo->prepare("INSERT INTO sertifikasi_msh (msh_id, nama_sertifikasi, nomor_sertifikat, penerbit, level, tanggal_terbit, tanggal_kadaluarsa, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                            $stmt->execute([
                                $id,
                                $sert_nama,
                                $_POST['sertifikasi_nomor'][$index] ?? '',
                                $_POST['sertifikasi_penerbit'][$index] ?? '',
                                $_POST['sertifikasi_level'][$index] ?? '',
                                $_POST['sertifikasi_tanggal'][$index] ?? null,
                                $_POST['sertifikasi_kadaluarsa'][$index] ?? null,
                                $_POST['sertifikasi_status'][$index] ?? 'valid'
                            ]);
                        }
                    }
                }
                
                header('Location: msh.php?updated=1');
                exit();
            }
        } else {
            // ADD NEW MSH
            $stmt = $pdo->prepare("INSERT INTO master_sabuk_hitam (nama, no_msh, tingkat_dan, foto, alamat, no_telp, email, dojo_cabang, status, tempat_lahir, tanggal_lahir, jenis_kelamin, nomor_ijazah, tanggal_ujian, jenis_keanggotaan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            if($stmt->execute([$nama, $nomor_msh, $tingkat_dan, $foto, $alamat, $no_telp, $email, $dojo_cabang, $status, $tempat_lahir, $tanggal_lahir, $jenis_kelamin, $nomor_ijazah, $tanggal_ujian, $jenis_keanggotaan])) {
                $msh_id = $pdo->lastInsertId();
                
                // Insert Prestasi
                if(isset($_POST['prestasi_nama']) && is_array($_POST['prestasi_nama'])) {
                    foreach($_POST['prestasi_nama'] as $prestasi) {
                        if(!empty($prestasi)) {
                            $stmt = $pdo->prepare("INSERT INTO prestasi_msh (msh_id, nama_prestasi) VALUES (?, ?)");
                            $stmt->execute([$msh_id, $prestasi]);
                        }
                    }
                }
                
                // Insert Sertifikasi
                if(isset($_POST['sertifikasi_nama']) && is_array($_POST['sertifikasi_nama'])) {
                    foreach($_POST['sertifikasi_nama'] as $index => $sert_nama) {
                        if(!empty($sert_nama)) {
                            $stmt = $pdo->prepare("INSERT INTO sertifikasi_msh (msh_id, nama_sertifikasi, nomor_sertifikat, penerbit, level, tanggal_terbit, tanggal_kadaluarsa, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                            $stmt->execute([
                                $msh_id,
                                $sert_nama,
                                $_POST['sertifikasi_nomor'][$index] ?? '',
                                $_POST['sertifikasi_penerbit'][$index] ?? '',
                                $_POST['sertifikasi_level'][$index] ?? '',
                                $_POST['sertifikasi_tanggal'][$index] ?? null,
                                $_POST['sertifikasi_kadaluarsa'][$index] ?? null,
                                $_POST['sertifikasi_status'][$index] ?? 'valid'
                            ]);
                        }
                    }
                }
                
                header('Location: msh.php?success=1');
                exit();
            }
        }
    }
}

$search = $_GET['search'] ?? '';
$page = $_GET['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$where = '';
$params = [];
if($search) {
    $searchTerm = "%$search%";
    $where = "WHERE nama LIKE ?
              OR no_msh LIKE ?
              OR tingkat_dan LIKE ?
              OR no_telp LIKE ?
              OR dojo_cabang LIKE ?
              OR alamat LIKE ?
              OR status LIKE ?
              OR email LIKE ?
              OR tempat_lahir LIKE ?
              OR nomor_ijazah LIKE ?
              OR DATE_FORMAT(tanggal_lahir, '%d/%m/%Y') LIKE ?
              OR DATE_FORMAT(created_at, '%d/%m/%Y') LIKE ?";
    $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm];
}

if($search) {
    $stmt = $pdo->prepare("SELECT * FROM master_sabuk_hitam $where ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
    $stmt->execute($params);
    $msh_list = $stmt->fetchAll();
    
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM master_sabuk_hitam $where");
    $countStmt->execute($params);
    $total = $countStmt->fetchColumn();
} else {
    $stmt = $pdo->query("SELECT * FROM master_sabuk_hitam ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
    $msh_list = $stmt->fetchAll();
    $total = $pdo->query("SELECT COUNT(*) FROM master_sabuk_hitam")->fetchColumn();
}

$total_pages = ceil($total / $limit);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data MSH - YPOK Management</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(3px);
            align-items: center;
            justify-content: center;
            z-index: 9999;
            animation: fadeIn 0.3s ease;
        }
        
        .modal-overlay.active {
            display: flex;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideDown {
            from { 
                transform: translateY(-50px);
                opacity: 0;
            }
            to { 
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        .modal-content form {
            background: #fff;
            border-radius: 16px;
            max-width: 650px;
            max-height: 85vh;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: slideDown 0.4s ease;
        }
        
        .modal-header {
            padding: 20px 25px;
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-header h2 {
            color: #fff;
            font-size: 20px;
            margin: 0;
            font-weight: 600;
        }
        
        .btn-close {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #fff;
            padding: 0;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s;
        }
        
        .btn-close:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: rotate(90deg);
        }
        
        .modal-body {
            padding: 25px;
            max-height: calc(85vh - 130px);
            overflow-y: auto;
        }
        
        .modal-body::-webkit-scrollbar {
            width: 6px;
        }
        
        .modal-body::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        
        .modal-body::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }
        
        .form-section {
            margin-bottom: 25px;
        }
        
        .section-title {
            font-size: 15px;
            font-weight: 600;
            color: #1e3a8a;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .form-row.full {
            grid-template-columns: 1fr;
        }
        
        .foto-upload {
            border: 2px dashed #cbd5e1;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            background: #f8fafc;
            transition: all 0.3s;
        }
        
        .foto-upload:hover {
            border-color: #3b82f6;
            background: #eff6ff;
        }
        
        .foto-preview {
            width: 120px;
            height: 120px;
            margin: 0 auto 12px;
            border-radius: 50%;
            background: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border: 3px solid #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .foto-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .foto-preview .icon {
            font-size: 40px;
            color: #94a3b8;
        }
        
        .btn-upload {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-upload:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
        }
        
        .url-helper {
            font-size: 12px;
            color: #64748b;
            margin-top: 10px;
        }
        
        .url-helper input {
            margin-top: 6px;
            font-size: 12px;
            padding: 8px 10px;
        }
        
        .prestasi-box {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            padding: 15px;
            border-radius: 10px;
            border: 1px solid #bfdbfe;
        }
        
        .btn-add-prestasi {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #fff;
            padding: 10px 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 13px;
            margin-top: 10px;
            transition: all 0.3s;
        }
        
        .sertifikasi-card {
            background: #fff;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 12px;
            border: 1.5px solid #e2e8f0;
            transition: all 0.3s;
        }
        
        .sertifikasi-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .sertifikasi-title {
            color: #1e3a8a;
            font-weight: 600;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .btn-remove {
            background: #ef4444;
            color: #fff;
            border: none;
            padding: 5px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.3s;
        }
        
        .btn-add-sertifikasi {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: #fff;
            padding: 10px 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            font-weight: 500;
            margin-top: 12px;
            font-size: 13px;
            transition: all 0.3s;
        }
        
        .modal-footer {
      
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            display: flex;
            gap: 10px;
            padding: 5px;
        }
        
        .btn-cancel {
            flex: 1;
            background: #fff;
            color: #64748b;
            padding: 12px;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-submit {
            flex: 2;
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            color: #fff;
            padding: 12px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .info-text {
            font-size: 12px;
            color: #64748b;
            margin-top: 4px;
        }
        
        .data-table th {
            font-size: 11px;
            padding: 10px 8px;
            text-align: left;
            white-space: nowrap;
        }
        
        .data-table td {
            font-size: 12px;
            padding: 8px 6px;
            vertical-align: middle;
            border-bottom: 1px solid #e2e8f0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 150px;
        }
        
        .data-table tbody td:first-child {
            text-align: center;
            max-width: 40px;
        }
        
        .data-table tbody td strong {
            font-size: 14px;
        }
        
        /* Badge adjustments */
        .badge-dan {
            background: #1f2937;
            color: #fff;
            padding: 3px 8px;
            font-weight: 500;
            display: inline-block;
            white-space: nowrap;
        }
        
        .badge-aktif, .badge-nonaktif, .badge-meninggal {
            padding: 3px 8px;
         
            font-weight: 600;
            white-space: nowrap;
        }
        
        .badge-info {
            padding: 4px 10px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: #fff;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }
        
        .action-buttons {
            display: flex;
            gap: 5px;
            justify-content: center;
        }
        
        .btn-icon {
            width: 28px;
            height: 28px;
            border-radius: 4px;
            font-size: 12px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        /* Modal Detail */
        .modal-detail {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(3px);
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }
        
        .modal-detail.active {
            display: flex;
        }
        
      .detail-content {
            background: #fff;
            border-radius: 12px;
            width: 90%;
            max-width: 700px;
            max-height: 80vh;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            animation: slideDown 0.4s ease;
        }
        
        .detail-header {
            padding: 15px 20px;
            background: #1e3a8a;
            color: #fff;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .detail-header h2 {
            margin: 0;
            font-size: 18px;
            font-weight: 500;
        }
        
        .btn-close-detail {
            background: none;
            border: none;
            font-size: 28px;
            cursor: pointer;
            color: #64748b;
            transition: all 0.3s;
        }
        
        .btn-close-detail:hover {
            color: #1e3a8a;
            transform: rotate(90deg);
        }
        
        /* Profile Banner */
        .profile-banner {
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            padding: 30px;
            display: flex;
            align-items: center;
            gap: 25px;
            position: relative;
            overflow: hidden;
        }
        
        .profile-banner::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 400px;
            height: 400px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }
        
        .profile-photo {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border: 5px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
            flex-shrink: 0;
            position: relative;
            z-index: 1;
        }
        
        .profile-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .profile-photo .icon {
            font-size: 50px;
            color: #3b82f6;
        }
        
        .profile-info {
            flex: 1;
            position: relative;
            z-index: 1;
        }
        
        .profile-name {
            font-size: 26px;
            font-weight: 700;
            color: #fff;
            margin: 0 0 8px 0;
            text-transform: uppercase;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .profile-id {
            font-size: 15px;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
            background: rgba(255, 255, 255, 0.2);
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            backdrop-filter: blur(10px);
        }
        
        /* Info Grid - Improved */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
            background: #fff;
        }
        
        .info-box {
            padding: 18px 25px;
            border-bottom: 1px solid #f1f5f9;
            border-right: 1px solid #f1f5f9;
            transition: all 0.3s;
        }
        
        .info-box:hover {
            background: #f8fafc;
        }
        
        .info-box:nth-child(2n) {
            border-right: none;
        }
        
        .info-label {
            font-size: 11px;
            color: #64748b;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .info-label::before {
            content: '';
            width: 3px;
            height: 12px;
            background: #3b82f6;
            border-radius: 2px;
        }
        
        .info-value {
            font-size: 14px;
            color: #1e293b;
            font-weight: 600;
            line-height: 1.5;
        }
        
        .info-box.full {
            grid-column: 1 / -1;
            border-right: none;
        }
        
        /* Status Badges in Detail */
        .status-badge {
            display: inline-block;
            border-radius: 6px;
            font-weight: 600;
            font-size: 13px;
        }
        
        .status-aktif {
            color: #000000;
        }
        
        /* Prestasi Section */
        .prestasi-section {
            padding: 25px 30px;
            background: #fff;
            border-top: 4px solid #fbbf24;
        }
        
        .prestasi-title {
            font-size: 18px;
            font-weight: 700;
            color: #78350f;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .prestasi-list {
            display: grid;
            gap: 12px;
        }
        
        .prestasi-item {
            background: linear-gradient(135deg, #fef3c7 0%, #fef9e7 100%);
            padding: 16px 18px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 14px;
            border-left: 5px solid #f59e0b;
            box-shadow: 0 2px 8px rgba(245, 158, 11, 0.1);
            transition: all 0.3s;
        }
        
        .prestasi-item:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.2);
        }
        
        .prestasi-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(251, 191, 36, 0.4);
        }
        
        .prestasi-text {
            font-size: 14px;
            color: #78350f;
            font-weight: 600;
        }
        
        /* Sertifikasi Section - Improved */
        .sertifikasi-section {
            padding: 25px 30px;
            background: #fff;
            border-top: 4px solid #3b82f6;
        }
        
        .sertifikasi-title {
            font-size: 18px;
            font-weight: 700;
            color: #1e40af;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .cert-card {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            border: 2px solid #3b82f6;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
            transition: all 0.3s;
        }
        
        .cert-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.25);
        }
        
        .cert-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 18px;
            padding-bottom: 15px;
            border-bottom: 2px solid rgba(59, 130, 246, 0.2);
        }
        
        .cert-name {
            font-size: 16px;
            font-weight: 700;
            color: #1e40af;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .cert-badge {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }
        
        .cert-badge.permanent {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #fff;
        }
        
        .cert-badge.valid {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: #fff;
        }
        
        .cert-badge.expired {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: #fff;
        }
        
        .cert-number {
            margin-bottom: 15px;
            padding: 10px 14px;
            background: rgba(255, 255, 255, 0.7);
            border-radius: 8px;
            font-size: 13px;
            color: #475569;
            font-weight: 600;
        }
        
        .cert-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .cert-detail-item {
            background: rgba(255, 255, 255, 0.5);
            padding: 12px;
            border-radius: 8px;
        }
        
        .cert-detail-label {
            font-size: 10px;
            color: #64748b;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }
        
        .cert-detail-value {
            font-size: 13px;
            color: #1e293b;
            font-weight: 700;
        }
        
        /* Footer Buttons */
        .detail-footer {
            padding: 20px 25px;
            background: #f8fafc;
            border-top: 2px solid #e2e8f0;
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }
        
        .btn-detail-edit {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: #fff;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        
        .btn-detail-edit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(245, 158, 11, 0.4);
        }
        
        .btn-detail-close {
            background: #fff;
            color: #64748b;
            padding: 12px 24px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-detail-close:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
        }
        
        /* Smaller Table Font */
        .data-table thead th {
            color: #fff;
            font-weight: 600;
            font-size: 11px;
            padding: 12px 10px;
            text-align: left;
            vertical-align: middle;
        }
        
        .data-table tbody td {
            font-size: 14px;
            padding: 10px;
            vertical-align: middle;
            border-bottom: 1px solid #e2e8f0;
            line-height: 1.4;
        }
        
        .data-table tbody tr {
            transition: all 0.2s ease;
        }
        
        .data-table tbody tr:hover {
            background-color: #f8fafc;
        }
        
        /* Toast Notification */
        .toast-notification {
            position: fixed;
            top: 80px;
            right: 20px;
            background: #fff;
            padding: 16px 24px;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            gap: 12px;
            z-index: 10000;
            animation: slideInRight 0.4s ease, slideOutRight 0.4s ease 2.6s;
            min-width: 300px;
        }
        
        @keyframes slideInRight {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(400px);
                opacity: 0;
            }
        }
        
        .toast-success {
            border-left: 4px solid #10b981;
        }
        
        .toast-error {
            border-left: 4px solid #ef4444;
        }
        
        .toast-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }
        
        .toast-success .toast-icon {
            background: #d1fae5;
            color: #065f46;
        }
        
        .toast-error .toast-icon {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .toast-content {
            flex: 1;
        }
        
        .toast-title {
            font-weight: 700;
            font-size: 14px;
            color: #1e293b;
            margin-bottom: 2px;
        }
        
        .toast-message {
            font-size: 13px;
            color: #64748b;
        }
        
        .toast-close {
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            font-size: 20px;
            padding: 0;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s;
        }
        
        .toast-close:hover {
            background: #f1f5f9;
            color: #475569;
        }

         
        .detail-body {
            padding: 20px;
            max-height: calc(80vh - 80px);
            overflow-y: auto;
        }
        
        .detail-body::-webkit-scrollbar {
            width: 6px;
        }
        
        .detail-body::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        
        .detail-body::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <?php include '../components/navbar.php'; ?>
    
    <!-- Toast Notifications -->
    <?php if(isset($_GET['success'])): ?>
        <div class="toast-notification toast-success" id="toast">
            <div class="toast-icon">✓</div>
            <div class="toast-content">
                <div class="toast-title">Berhasil!</div>
                <div class="toast-message">Data MSH berhasil ditambahkan</div>
            </div>
            <button class="toast-close" onclick="closeToast()">×</button>
        </div>
    <?php endif; ?>
    
    <?php if(isset($_GET['updated'])): ?>
        <div class="toast-notification toast-success" id="toast">
            <div class="toast-icon">✓</div>
            <div class="toast-content">
                <div class="toast-title">Berhasil!</div>
                <div class="toast-message">Data MSH berhasil diupdate</div>
            </div>
            <button class="toast-close" onclick="closeToast()">×</button>
        </div>
    <?php endif; ?>
    
    <?php if(isset($_GET['deleted'])): ?>
        <div class="toast-notification toast-error" id="toast">
            <div class="toast-icon">🗑️</div>
            <div class="toast-content">
                <div class="toast-title">Berhasil!</div>
                <div class="toast-message">Data MSH berhasil dihapus</div>
            </div>
            <button class="toast-close" onclick="closeToast()">×</button>
        </div>
    <?php endif; ?>
    
    <div class="main-content">
        <div class="top-bar">
            <h2 class="page-title">Data MSH</h2>
            <div class="user-info">
                <span class="icon">👤</span>
                <span><?php echo $_SESSION['nama_lengkap']; ?></span>
            </div>
        </div>
        
        <div class="container">
            <div class="content-header">
                <h1>Data MSH</h1>
                
                <div class="search-bar">
                    <form method="GET" style="display: flex; gap: 15px; flex: 1;" id="searchForm">
                        <input type="text" name="search" id="searchInput" placeholder="🔍 Cari MSH (nama, nomor, tanggal, dojo, tingkat, email, status)..." value="<?php echo htmlspecialchars($search); ?>" autocomplete="off">
                    </form>
                    <button class="btn-primary" onclick="openModal()">
                        ➕ Tambah Data
                    </button>
                </div>
            </div>
            
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 35px;">NO</th>
                            <th style="width: 110px;">NO.MSH</th>
                            <th style="width: 160px;">NAMA</th>
                            <th style="width: 150px;">TEMPAT, TGL LAHIR</th>
                            <th style="width: 180px;">NOMOR IJAZAH, TINGKATAN</th>
                            <th style="width: 140px;">ASAL PROVINSI / KAB/ KOTA</th>
                            <th style="width: 200px;">ALAMAT</th>
                            <th style="width: 90px;">JENIS</th>
                            <th style="width: 70px;">KET</th>
                            <th style="width: 100px;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($msh_list) > 0): ?>
                            <?php foreach($msh_list as $index => $msh): ?>
                            <tr>
                                <td style="text-align: center;"><?php echo $offset + $index + 1; ?></td>
                                <td><strong><?php echo $msh['no_msh'] ?? 'MSH-' . str_pad($msh['id'], 3, '0', STR_PAD_LEFT); ?></strong></td>
                                <td><?php echo htmlspecialchars($msh['nama']); ?></td>
                                <td style="font-size: 13px;">
                                    <?php 
                                    $tempat = htmlspecialchars($msh['tempat_lahir'] ?? 'Jakarta');
                                    $tgl = $msh['tanggal_lahir'] ? date('d-m-Y', strtotime($msh['tanggal_lahir'])) : '-';
                                    echo $tempat . ', ' . $tgl;
                                    ?>
                                </td>
                                <td style="font-size: 13px;">
                                    <?php 
                                    $ijazah = htmlspecialchars($msh['nomor_ijazah'] ?? $msh['no_msh']);
                                    $tingkat = htmlspecialchars($msh['tingkat_dan'] ?? 'Dan X');
                                    $tgl_ujian = $msh['tanggal_ujian'] ? date('d-F-Y', strtotime($msh['tanggal_ujian'])) : ($msh['created_at'] ? date('d-F-Y', strtotime($msh['created_at'])) : '-');
                                    echo "DAN " . strtoupper(str_replace('Dan ', '', $tingkat)) . ", " . $ijazah;
                                    ?>
                                </td>
                                <td style="font-size: 13px;"><?php echo htmlspecialchars($msh['dojo_cabang'] ?? 'DKI Jakarta'); ?></td>
                                <td style="font-size: 13px;" title="<?php echo htmlspecialchars($msh['alamat']); ?>">
                                    <?php echo htmlspecialchars($msh['alamat']); ?>
                                </td>
                                <td style="text-align: center;">
                                    <span class="badge badge-info"><?php echo htmlspecialchars($msh['jenis_keanggotaan'] ?? 'Reguler'); ?></span>
                                </td>
                                <td style="text-align: center;">
                                    <span class="badge badge-<?php echo strtolower($msh['status']) == 'aktif' ? 'aktif' : 'nonaktif'; ?>">
                                        <?php echo strtoupper($msh['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-icon btn-view" onclick="viewDetail(<?php echo $msh['id']; ?>)" title="Lihat Detail">👁️</button>
                                        <button class="btn-icon btn-edit" onclick="editData(<?php echo $msh['id']; ?>)" title="Edit">✏️</button>
                                        <button class="btn-icon btn-delete" onclick="deleteData(<?php echo $msh['id']; ?>, '<?php echo htmlspecialchars($msh['nama']); ?>')" title="Hapus">🗑️</button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" style="text-align: center; padding: 40px; color: #94a3b8;">
                                    <div style="font-size: 48px; margin-bottom: 10px;">📋</div>
                                    <div>Tidak ada data MSH</div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                
                <div class="pagination">
                    <div class="pagination-info">
                        Menampilkan <?php echo $offset + 1; ?> sampai <?php echo min($offset + $limit, $total); ?> dari <?php echo $total; ?> data
                    </div>
                    <div class="pagination-buttons">
                        <button <?php echo $page <= 1 ? 'disabled' : ''; ?> onclick="location.href='?page=<?php echo $page-1; ?>&search=<?php echo $search; ?>'">← Previous</button>
                        
                        <?php for($i = 1; $i <= min($total_pages, 5); $i++): ?>
                            <button class="<?php echo $i == $page ? 'active' : ''; ?>" onclick="location.href='?page=<?php echo $i; ?>&search=<?php echo $search; ?>'"><?php echo $i; ?></button>
                        <?php endfor; ?>
                        
                        <button <?php echo $page >= $total_pages ? 'disabled' : ''; ?> onclick="location.href='?page=<?php echo $page+1; ?>&search=<?php echo $search; ?>'">Next →</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Form Tambah MSH -->
    <div class="modal-overlay" id="modalMSH">
        <div class="modal-content">
            <div class="modal-header">
                <h2>➕ Tambah Data MSH</h2>
                <button class="btn-close" onclick="closeModal()">×</button>
            </div>
            
            <form method="POST" enctype="multipart/form-data" id="formMSH">
                <input type="hidden" name="action" value="add_msh">
                <div class="modal-body">
                    <!-- Data Utama -->
                    <div class="form-section">
                        <div class="section-title">📝 Data Utama</div>

                        <div class="form-row full">
                            <div class="form-group">
                                <label>Foto MSH</label>
                                <div class="foto-upload">
                                    <div class="foto-preview" id="fotoPreview">
                                        <span class="icon">👤</span>
                                    </div>
                                    <input type="file" name="foto" id="fotoInput" accept="image/*" style="display: none;" onchange="previewFoto(event)">
                                    <button type="button" class="btn-upload" onclick="document.getElementById('fotoInput').click()">
                                        📷 Pilih Foto
                                    </button>
                                    <div class="url-helper">
                                        atau URL: <input type="text" name="foto_url" placeholder="https://...">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>No. MSH <span>*</span></label>
                                <input type="text" name="nomor_msh" placeholder="MSH-001" required>
                            </div>
                            <div class="form-group">
                                <label>Nama Lengkap <span>*</span></label>
                                <input type="text" name="nama" placeholder="Masukkan nama lengkap" required>
                            </div>
                        </div>
                        
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Tempat Lahir <span>*</span></label>
                                <input type="text" name="tempat_lahir" placeholder="Jakarta" required>
                            </div>
                            <div class="form-group">
                                <label>Tanggal Lahir <span>*</span></label>
                                <input type="date" name="tanggal_lahir" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Jenis Kelamin <span>*</span></label>
                                <select name="jenis_kelamin" required>
                                    <option value="">Pilih...</option>
                                    <option value="L">Laki-laki</option>
                                    <option value="P">Perempuan</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Tingkat Dan <span>*</span></label>
                                <select name="tingkat_dan" required>
                                    <option>Pilih...</option>
                                    <option value="Dan 1 ">Dan 1 (hitam)</option>
                                    <option value="Dan 2 ">Dan 2 (hitam)</option>
                                    <option value="Dan 3 ">Dan 3 (hitam)</option>
                                    <option value="Dan 4 ">Dan 4 (hitam)</option>
                                    <option value="Dan 5 ">Dan 5 (hitam)</option>
                                    <option value="Dan 6 ">Dan 6 (hitam)</option>
                                    <option value="Dan 7 ">Dan 7 (hitam)</option>
                                    <option value="Dan 8 ">Dan 8 (hitam)</option>
                                    <option value="Dan 9 ">Dan 9 (hitam)</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Dojo/Cabang <span>*</span></label>
                                <input type="text" name="dojo_cabang" placeholder="Jakarta Pusat" required>
                            </div>
                            <div class="form-group">
                                <label>No. Telepon <span>*</span></label>
                                <input type="tel" name="no_telp" placeholder="08xxxxxxxxxx" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" placeholder="email@example.com">
                            </div>
                            <div class="form-group">
                                <label>Nomor Ijazah</label>
                                <input type="text" name="nomor_ijazah" placeholder="IJ-2025-001">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Tanggal Ujian</label>
                                <input type="date" name="tanggal_ujian" placeholder="Tanggal ujian kenaikan dan">
                            </div>
                            <div class="form-group">
                                <label>Jenis Keanggotaan</label>
                                <select name="jenis_keanggotaan">
                                    <option value="Reguler">Reguler</option>
                                    <option value="Khusus">Khusus</option>
                                    <option value="Kehormatan">Kehormatan</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Status <span>*</span></label>
                                <select name="status" required>
                                    <option value="">Pilih...</option>
                                    <option value="aktif">Aktif</option>
                                    <option value="non-aktif">Non-Aktif</option>
                                     <option value="meninggal">Meninggal</option>
                                    
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row full">
                            <div class="form-group">
                                <label>Alamat Lengkap <span>*</span></label>
                                <textarea name="alamat" rows="2" placeholder="Masukkan alamat lengkap..." required></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Prestasi -->
                    <div class="form-section">
                        <div class="section-title">🏆 Prestasi</div>
                        <div id="prestasiContainer">
                            <div class="prestasi-box" style="margin-bottom: 12px;">
                                <div class="form-group">
                                    <input type="text" name="prestasi_nama[]" placeholder="Contoh: Juara 1 Kata Regional 2023">
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn-add-prestasi" onclick="tambahPrestasi()">➕ Tambah Prestasi</button>
                    </div>
                    
                    <!-- Sertifikasi Detail -->
                    <div class="form-section">
                        <div class="section-title">📜 Sertifikasi Detail</div>
                        <p class="info-text">Tambahkan sertifikasi resmi (opsional)</p>
                        
                        <div id="sertifikasiContainer">
                            <div class="sertifikasi-card">
                                <div class="sertifikasi-header">
                                    <div class="sertifikasi-title">📄 Sertifikasi #1</div>
                                    <button type="button" class="btn-remove" onclick="this.parentElement.parentElement.remove()">✕</button>
                                </div>
                                

                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Nama Sertifikasi</label>
                                        <input type="text" name="sertifikasi_nama[]" placeholder="Sabuk Hitam Dan 1">
                                    </div>
                                    <div class="form-group">
                                        <label>Nomor Sertifikat</label>
                                        <input type="text" name="sertifikasi_nomor[]" placeholder="MSH-2023-XXX">
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Penerbit</label>
                                        <input type="text" name="sertifikasi_penerbit[]" placeholder="YPOK, FORKI">
                                    </div>
                                    <div class="form-group">
                                        <label>Level</label>
                                        <input type="text" name="sertifikasi_level[]" placeholder="Dan 1">
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Status <span>*</span></label>
                                        <select name="sertifikasi_status[]" class="sertifikasi-status-select" onchange="toggleKadaluarsa(this)" required>
                                            <option value="">Pilih...</option>
                                            <option value="valid">Valid (Ada Masa Berlaku)</option>
                                            <option value="expired">Expired (Sudah Kadaluarsa)</option>
                                            <option value="permanent">Permanent (Selamanya)</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Tanggal Terbit</label>
                                        <input type="date" name="sertifikasi_tanggal[]">
                                    </div>
                                </div>
                                
                                <div class="form-row kadaluarsa-field" style="display: none;">
                                    <div class="form-group">
                                        <label>Tanggal Kadaluarsa</label>
                                        <input type="date" name="sertifikasi_kadaluarsa[]" class="kadaluarsa-input">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <button type="button" class="btn-add-sertifikasi" onclick="tambahSertifikasi()">
                            ➕ Tambah Sertifikasi
                        </button>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeModal()">Batal</button>
                    <button type="submit" class="btn-submit">💾 Simpan Data MSH</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal Form Edit MSH -->
    <div class="modal-overlay" id="modalEdit">
        <div class="modal-content">
            <div class="modal-header">
                <h2>✏️ Edit Data MSH</h2>
                <button class="btn-close" onclick="closeEditModal()">×</button>
            </div>
            
            <form method="POST" enctype="multipart/form-data" id="formEdit">
                <input type="hidden" name="action" value="edit_msh">
                <input type="hidden" name="id" id="edit_id">
                
                <div class="modal-body">
                    <!-- Data Utama -->
                    <div class="form-section">
                        <div class="section-title">📝 Data Utama</div>

                          <div class="form-row full">
                            <div class="form-group">
                                <label>Foto MSH</label>
                                <div class="foto-upload">
                                    <div class="foto-preview" id="editFotoPreview">
                                        <span class="icon">👤</span>
                                    </div>
                                    <input type="file" name="foto" id="editFotoInput" accept="image/*" style="display: none;" onchange="previewEditFoto(event)">
                                    <button type="button" class="btn-upload" onclick="document.getElementById('editFotoInput').click()">
                                        📷 Ubah Foto
                                    </button>
                                    <div class="url-helper">
                                        atau URL: <input type="text" name="foto_url" id="edit_foto_url" placeholder="https://...">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>No. MSH <span>*</span></label>
                                <input type="text" name="nomor_msh" id="edit_nomor_msh" required>
                            </div>
                            <div class="form-group">
                                <label>Nama Lengkap <span>*</span></label>
                                <input type="text" name="nama" id="edit_nama" required>
                            </div>
                        </div>
                        
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Tempat Lahir <span>*</span></label>
                                <input type="text" name="tempat_lahir" id="edit_tempat_lahir" placeholder="Jakarta" required>
                            </div>
                            <div class="form-group">
                                <label>Tanggal Lahir <span>*</span></label>
                                <input type="date" name="tanggal_lahir" id="edit_tanggal_lahir" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Jenis Kelamin <span>*</span></label>
                                <select name="jenis_kelamin" id="edit_jenis_kelamin" required>
                                    <option value="">Pilih...</option>
                                    <option value="L">Laki-laki</option>
                                    <option value="P">Perempuan</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Tingkat Dan <span>*</span></label>
                                <select name="tingkat_dan" id="edit_tingkat_dan" required>
                                     <option value="">Pilih...</option>
                                    <option value="Dan 1 ">Dan 1 (hitam)</option>
                                    <option value="Dan 2 ">Dan 2 (hitam)</option>
                                    <option value="Dan 3 ">Dan 3 (hitam)</option>
                                    <option value="Dan 4 ">Dan 4 (hitam)</option>
                                    <option value="Dan 5 ">Dan 5 (hitam)</option>
                                    <option value="Dan 6 ">Dan 6 (hitam)</option>
                                    <option value="Dan 7 ">Dan 7 (hitam)</option>
                                    <option value="Dan 8 ">Dan 8 (hitam)</option>
                                    <option value="Dan 9 ">Dan 9 (hitam)</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Dojo/Cabang <span>*</span></label>
                                <input type="text" name="dojo_cabang" id="edit_dojo_cabang" placeholder="Jakarta Pusat" required>
                            </div>
                            <div class="form-group">
                                <label>No. Telepon <span>*</span></label>
                                <input type="tel" name="no_telp" id="edit_no_telp" placeholder="08xxxxxxxxxx" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" id="edit_email" placeholder="email@example.com">
                            </div>
                            <div class="form-group">
                                <label>Nomor Ijazah</label>
                                <input type="text" name="nomor_ijazah" id="edit_nomor_ijazah" placeholder="IJ-2025-001">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Tanggal Ujian</label>
                                <input type="date" name="tanggal_ujian" id="edit_tanggal_ujian" placeholder="Tanggal ujian kenaikan dan">
                            </div>
                            <div class="form-group">
                                <label>Jenis Keanggotaan</label>
                                <select name="jenis_keanggotaan" id="edit_jenis_keanggotaan">
                                    <option value="Reguler">Reguler</option>
                                    <option value="Khusus">Khusus</option>
                                    <option value="Kehormatan">Kehormatan</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Status <span>*</span></label>
                                <select name="status" id="edit_status" required>
                                    <option value="">Pilih...</option>
                                    <option value="aktif">Aktif</option>
                                    <option value="non-aktif">Non-Aktif</option>
                                        <option value="meninggal">Meninggal</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row full">
                            <div class="form-group">
                                <label>Alamat Lengkap <span>*</span></label>
                                <textarea name="alamat" id="edit_alamat" rows="2" placeholder="Masukkan alamat lengkap..." required></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Prestasi -->
                    <div class="form-section">
                        <div class="section-title">🏆 Prestasi</div>
                        <div id="editPrestasiContainer">
                            <!-- Will be populated -->
                        </div>
                        <button type="button" class="btn-add-prestasi" onclick="tambahEditPrestasi()">➕ Tambah Prestasi</button>
                    </div>
                    
                    <!-- Sertifikasi Detail -->
                    <div class="form-section">
                        <div class="section-title">📜 Sertifikasi Detail</div>
                        <p class="info-text">Edit atau tambah sertifikasi resmi (opsional)</p>
                        
                        <div id="editSertifikasiContainer">
                            <!-- Will be populated with existing sertifikasi data -->
                             
                        </div>
                        
                        <button type="button" class="btn-add-sertifikasi" onclick="tambahEditSertifikasi()">
                            ➕ Tambah Sertifikasi
                        </button>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeEditModal()">Batal</button>
                    <button type="submit" class="btn-submit">💾 Update Data MSH</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal Detail MSH -->
    <div class="modal-detail" id="modalDetail">
        <div class="detail-content">
            <div class="detail-header">
                <h2>📋 Detail MSH</h2>
                <button class="btn-close-detail" onclick="closeDetail()">×</button>
            </div>
            <div class="detail-body" id="detailContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
    
    <script src="../assets/js/app.js"></script>
    <script>
        let sertifikasiCount = 1;
        let editSertifikasiCount = 0;
        let editPrestasiCount = 0;
        
        // Auto Search with enhanced visual feedback
        let searchTimeout;
        const searchInput = document.getElementById('searchInput');
        const searchForm = document.getElementById('searchForm');

        if (searchInput && searchForm) {
            searchInput.addEventListener('input', function(e) {
                clearTimeout(searchTimeout);

                // Visual feedback saat mengetik
                searchInput.style.borderColor = '#fbbf24';
                searchInput.style.background = '#fffbeb';

                searchTimeout = setTimeout(function() {
                    // Show loading indicator
                    searchInput.style.borderColor = '#3b82f6';
                    searchInput.style.background = '#eff6ff';

                    const searchValue = e.target.value;
                    const url = new URL(window.location);

                    if(searchValue.trim() === '') {
                        url.searchParams.delete('search');
                    } else {
                        url.searchParams.set('search', searchValue);
                    }
                    url.searchParams.delete('page'); // Reset to page 1 when searching

                    window.location.href = url.toString();
                }, 500); // Submit setelah 500ms user berhenti mengetik
            });

            // Clear search dengan ESC
            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    searchInput.value = '';
                    const url = new URL(window.location);
                    url.searchParams.delete('search');
                    url.searchParams.delete('page');
                    window.location.href = url.toString();
                }
            });

            // Reset style on focus
            searchInput.addEventListener('focus', function() {
                if (!this.value) {
                    searchInput.style.borderColor = '#667eea';
                    searchInput.style.background = 'white';
                }
            });
        }
        
        function openModal() {
            document.getElementById('modalMSH').classList.add('active');
        }
        
        function closeModal() {
            document.getElementById('modalMSH').classList.remove('active');
            document.getElementById('formMSH').reset();
            document.getElementById('fotoPreview').innerHTML = '<span class="icon">👤</span>';
        }
        
        function viewDetail(id) {
            document.getElementById('modalDetail').classList.add('active');
            
            // Fetch data via AJAX
            fetch('msh_detail.php?id=' + id)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('detailContent').innerHTML = data;
                })
                .catch(error => {
                    document.getElementById('detailContent').innerHTML = '<p style="text-align:center;color:red;">Error loading data</p>';
                });
        }
        
        function closeDetail() {
            document.getElementById('modalDetail').classList.remove('active');
        }
        
        function deleteData(id, nama) {
            if(confirm('Apakah Anda yakin ingin menghapus data MSH:\n' + nama + '?')) {
                window.location.href = 'msh.php?delete=' + id;
            }
        }
        
        function editData(id) {
            document.getElementById('modalEdit').classList.add('active');
            
            // Fetch data via AJAX
            fetch('../api/msh_get.php?id=' + id)
                .then(response => response.json())
                .then(data => {
                    console.log('Data received:', data); // Debug
                    
                    // Check if all elements exist before setting values
                    const elements = {
                        'edit_id': data.id,
                        'edit_nomor_msh': data.no_msh || '',
                        'edit_nama': data.nama || '',
                        'edit_tingkat_dan': data.tingkat_dan || '',
                        'edit_no_telp': data.no_telp || '',
                        'edit_email': data.email || '',
                        'edit_dojo_cabang': data.dojo_cabang || '',
                        'edit_status': data.status || '',
                        'edit_alamat': data.alamat || '',
                        'edit_tempat_lahir': data.tempat_lahir || '',
                        'edit_tanggal_lahir': data.tanggal_lahir || '',
                        'edit_jenis_kelamin': data.jenis_kelamin || 'L',
                        'edit_nomor_ijazah': data.nomor_ijazah || '',
                        'edit_tanggal_ujian': data.tanggal_ujian || '',
                        'edit_jenis_keanggotaan': data.jenis_keanggotaan || 'Reguler',
                        'edit_foto_url': data.foto || ''
                    };
                    
                    // Set values only if element exists
                    Object.keys(elements).forEach(key => {
                        const element = document.getElementById(key);
                        if (element) {
                            element.value = elements[key];
                        } else {
                            console.warn(`Element not found: ${key}`);
                        }
                    });
                    
                    // Preview existing foto
                    const editFotoPreview = document.getElementById('editFotoPreview');
                    if(editFotoPreview) {
                        if(data.foto) {
                            editFotoPreview.innerHTML = '<img src="' + data.foto + '">';
                        } else {
                            editFotoPreview.innerHTML = '<span class="icon">👤</span>';
                        }
                    }
                    
                    // Load Prestasi
                    const prestasiContainer = document.getElementById('editPrestasiContainer');
                    if(prestasiContainer) {
                        prestasiContainer.innerHTML = '';
                        editPrestasiCount = 0;
                        
                        if(data.prestasi && data.prestasi.length > 0) {
                            data.prestasi.forEach((prestasi) => {
                                editPrestasiCount++;
                                const prestasiBox = document.createElement('div');
                                prestasiBox.className = 'prestasi-box';
                                prestasiBox.style.marginBottom = '12px';
                                prestasiBox.innerHTML = `
                                    <div style="display: flex; gap: 10px; align-items: center;">
                                        <div class="form-group" style="flex: 1; margin: 0;">
                                            <input type="text" name="prestasi_nama[]" value="${prestasi.nama_prestasi || ''}" placeholder="Contoh: Juara 1 Kata Regional 2023">
                                        </div>
                                        <button type="button" class="btn-remove" onclick="this.parentElement.parentElement.remove()">✕</button>
                                    </div>
                                `;
                                prestasiContainer.appendChild(prestasiBox);
                            });
                        } else {
                            tambahEditPrestasi();
                        }
                    }
                    
                    // Load Sertifikasi
                    const sertifikasiContainer = document.getElementById('editSertifikasiContainer');
                    if(sertifikasiContainer) {
                        sertifikasiContainer.innerHTML = '';
                        editSertifikasiCount = 0;
                        
                        if(data.sertifikasi && data.sertifikasi.length > 0) {
                            data.sertifikasi.forEach((sert) => {
                                editSertifikasiCount++;
                                const sertCard = document.createElement('div');
                                sertCard.className = 'sertifikasi-card';
                                
                                const showKadaluarsa = (sert.status == 'valid' || sert.status == 'expired') ? 'block' : 'none';
                                const kadaluarsaRequired = (sert.status == 'valid' || sert.status == 'expired') ? 'required' : '';
                                
                                sertCard.innerHTML = `
                                    <div class="sertifikasi-header">
                                        <div class="sertifikasi-title">📄 Sertifikasi #${editSertifikasiCount}</div>
                                        <button type="button" class="btn-remove" onclick="this.parentElement.parentElement.remove()">✕</button>
                                    </div>
                                    
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label>Nama Sertifikasi</label>
                                            <input type="text" name="sertifikasi_nama[]" value="${sert.nama_sertifikasi || ''}" placeholder="Sabuk Hitam Dan 1">
                                        </div>
                                        <div class="form-group">
                                            <label>Nomor Sertifikat</label>
                                            <input type="text" name="sertifikasi_nomor[]" value="${sert.nomor_sertifikat || ''}" placeholder="MSH-2023-XXX">
                                        </div>
                                    </div>
                                    
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label>Penerbit</label>
                                            <input type="text" name="sertifikasi_penerbit[]" value="${sert.penerbit || ''}" placeholder="YPOK, FORKI">
                                        </div>
                                        <div class="form-group">
                                            <label>Level</label>
                                            <input type="text" name="sertifikasi_level[]" value="${sert.level || ''}" placeholder="Dan 1">
                                        </div>
                                    </div>
                                    
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label>Status <span>*</span></label>
                                            <select name="sertifikasi_status[]" class="sertifikasi-status-select" onchange="toggleKadaluarsa(this)" required>
                                                <option value="">Pilih...</option>
                                                <option value="valid" ${sert.status == 'valid' ? 'selected' : ''}>Valid (Ada Masa Berlaku)</option>
                                                <option value="expired" ${sert.status == 'expired' ? 'selected' : ''}>Expired (Sudah Kadaluarsa)</option>
                                                <option value="permanent" ${sert.status == 'permanent' ? 'selected' : ''}>Permanent (Selamanya)</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Tanggal Terbit</label>
                                            <input type="date" name="sertifikasi_tanggal[]" value="${sert.tanggal_terbit || ''}">
                                        </div>
                                    </div>
                                    
                                    <div class="form-row kadaluarsa-field" style="display: ${showKadaluarsa};">
                                        <div class="form-group">
                                            <label>Tanggal Kadaluarsa</label>
                                            <input type="date" name="sertifikasi_kadaluarsa[]" class="kadaluarsa-input" value="${sert.tanggal_kadaluarsa || ''}" ${kadaluarsaRequired}>
                                        </div>
                                    </div>
                                `;
                                sertifikasiContainer.appendChild(sertCard);
                            });
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading data: ' + error.message);
                    closeEditModal();
                });
        }
        
        function closeEditModal() {
            document.getElementById('modalEdit').classList.remove('active');
            document.getElementById('formEdit').reset();
            document.getElementById('editFotoPreview').innerHTML = '<span class="icon">👤</span>';
        }
        
        function previewFoto(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('fotoPreview').innerHTML = '<img src="' + e.target.result + '">';
                }
                reader.readAsDataURL(file);
            }
        }
        
        function previewEditFoto(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('editFotoPreview').innerHTML = '<img src="' + e.target.result + '">';
                }
                reader.readAsDataURL(file);
            }
        }
        
        function tambahPrestasi() {
            const container = document.getElementById('prestasiContainer');
            const prestasiBox = document.createElement('div');
            prestasiBox.className = 'prestasi-box';
            prestasiBox.style.marginBottom = '12px';
            prestasiBox.innerHTML = `
                <div style="display: flex; gap: 10px; align-items: center;">
                    <div class="form-group" style="flex: 1; margin: 0;">
                        <input type="text" name="prestasi_nama[]" placeholder="Contoh: Juara 1 Kata Regional 2023">
                    </div>
                    <button type="button" class="btn-remove" onclick="this.parentElement.parentElement.remove()">✕</button>
                </div>
            `;
            container.appendChild(prestasiBox);
        }
        
        function tambahEditPrestasi() {
            editPrestasiCount++;
            const container = document.getElementById('editPrestasiContainer');
            const prestasiBox = document.createElement('div');
            prestasiBox.className = 'prestasi-box';
            prestasiBox.style.marginBottom = '12px';
            prestasiBox.innerHTML = `
                <div style="display: flex; gap: 10px; align-items: center;">
                    <div class="form-group" style="flex: 1; margin: 0;">
                        <input type="text" name="prestasi_nama[]" placeholder="Contoh: Juara 1 Kata Regional 2023">
                    </div>
                    <button type="button" class="btn-remove" onclick="this.parentElement.parentElement.remove()">✕</button>
                </div>
            `;
            container.appendChild(prestasiBox);
        }
        
        function toggleKadaluarsa(selectElement) {
            const card = selectElement.closest('.sertifikasi-card');
            const kadaluarsaField = card.querySelector('.kadaluarsa-field');
            const kadaluarsaInput = card.querySelector('.kadaluarsa-input');
            
            if (selectElement.value === 'permanent') {
                kadaluarsaField.style.display = 'none';
                kadaluarsaInput.value = '';
                kadaluarsaInput.removeAttribute('required');
            } else if (selectElement.value === 'valid' || selectElement.value === 'expired') {
                kadaluarsaField.style.display = 'block';
                kadaluarsaInput.setAttribute('required', 'required');
            } else {
                kadaluarsaField.style.display = 'none';
                kadaluarsaInput.removeAttribute('required');
            }
        }
        
        function tambahSertifikasi() {
            sertifikasiCount++;
            const container = document.getElementById('sertifikasiContainer');
            const newCard = document.createElement('div');
            newCard.className = 'sertifikasi-card';
            newCard.innerHTML = `
                <div class="sertifikasi-header">
                    <div class="sertifikasi-title">📄 Sertifikasi #${sertifikasiCount}</div>
                    <button type="button" class="btn-remove" onclick="this.parentElement.parentElement.remove()">✕</button>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Nama Sertifikasi</label>
                        <input type="text" name="sertifikasi_nama[]" placeholder="Sabuk Hitam Dan 1">
                    </div>
                    <div class="form-group">
                        <label>Nomor Sertifikat</label>
                        <input type="text" name="sertifikasi_nomor[]" placeholder="MSH-2023-XXX">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Penerbit</label>
                        <input type="text" name="sertifikasi_penerbit[]" placeholder="YPOK, FORKI">
                    </div>
                    <div class="form-group">
                        <label>Level</label>
                        <input type="text" name="sertifikasi_level[]" placeholder="Dan 1">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Status <span>*</span></label>
                        <select name="sertifikasi_status[]" class="sertifikasi-status-select" onchange="toggleKadaluarsa(this)" required>
                            <option value="">Pilih...</option>
                            <option value="valid">Valid (Ada Masa Berlaku)</option>
                            <option value="expired">Expired (Sudah Kadaluarsa)</option>
                            <option value="permanent">Permanent (Selamanya)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tanggal Terbit</label>
                        <input type="date" name="sertifikasi_tanggal[]">
                    </div>
                </div>
                
                <div class="form-row kadaluarsa-field" style="display: none;">
                    <div class="form-group">
                        <label>Tanggal Kadaluarsa</label>
                        <input type="date" name="sertifikasi_kadaluarsa[]" class="kadaluarsa-input">
                    </div>
                </div>
            `;
            container.appendChild(newCard);
        }
        
        function tambahEditSertifikasi() {
            editSertifikasiCount++;
            const container = document.getElementById('editSertifikasiContainer');
            const newCard = document.createElement('div');
            newCard.className = 'sertifikasi-card';
            newCard.innerHTML = `
                <div class="sertifikasi-header">
                    <div class="sertifikasi-title">📄 Sertifikasi #${editSertifikasiCount}</div>
                    <button type="button" class="btn-remove" onclick="this.parentElement.parentElement.remove()">✕</button>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Nama Sertifikasi</label>
                        <input type="text" name="sertifikasi_nama[]" placeholder="Sabuk Hitam Dan 1">
                    </div>
                    <div class="form-group">
                        <label>Nomor Sertifikat</label>
                        <input type="text" name="sertifikasi_nomor[]" placeholder="MSH-2023-XXX">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Penerbit</label>
                        <input type="text" name="sertifikasi_penerbit[]" placeholder="YPOK, FORKI">
                    </div>
                    <div class="form-group">
                        <label>Level</label>
                        <input type="text" name="sertifikasi_level[]" placeholder="Dan 1">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Status <span>*</span></label>
                        <select name="sertifikasi_status[]" class="sertifikasi-status-select" onchange="toggleKadaluarsa(this)" required>
                            <option value="">Pilih...</option>
                            <option value="valid">Valid (Ada Masa Berlaku)</option>
                            <option value="expired">Expired (Sudah Kadaluarsa)</option>
                            <option value="permanent">Permanent (Selamanya)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tanggal Terbit</label>
                        <input type="date" name="sertifikasi_tanggal[]">
                    </div>
                </div>
                
                <div class="form-row kadaluarsa-field" style="display: none;">
                    <div class="form-group">
                        <label>Tanggal Kadaluarsa</label>
                        <input type="date" name="sertifikasi_kadaluarsa[]" class="kadaluarsa-input">
                    </div>
                </div>
            `;
            container.appendChild(newCard);
        }
        
        // Close modal when clicking outside
        document.getElementById('modalMSH').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
        
        document.getElementById('modalDetail').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDetail();
            }
        });
        
        document.getElementById('modalEdit').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        });
        
        // Auto close toast after 3 seconds
        if(document.getElementById('toast')) {
            setTimeout(function() {
                closeToast();
            }, 3000);
        }
        
        function closeToast() {
            const toast = document.getElementById('toast');
            if(toast) {
                toast.style.animation = 'slideOutRight 0.4s ease';
                setTimeout(function() {
                    toast.remove();
                    // Remove query parameter from URL
                    const url = new URL(window.location);
                    url.searchParams.delete('success');
                    url.searchParams.delete('updated');
                    url.searchParams.delete('deleted');
                    window.history.replaceState({}, '', url);
                }, 400);
            }
        }
    </script>
</body>
</html>
