<?php
require_once __DIR__ . '/../config/database.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

// Handle Delete
if(isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM kohai WHERE id = ?");
    if($stmt->execute([$id])) {
        header('Location: kohai.php?deleted=1');
        exit();
    }
}

// Handle form submission (Add & Edit)
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if($_POST['action'] == 'add_kohai' || $_POST['action'] == 'edit_kohai') {
        $kode_kohai = $_POST['kode_kohai'];
        $no_registrasi_ijazah = $_POST['no_registrasi_ijazah'] ?? '';
        $nama = $_POST['nama'];
        $tempat_lahir = $_POST['tempat_lahir'];
        $tanggal_lahir = $_POST['tanggal_lahir'];
        $jenis_kelamin = $_POST['jenis_kelamin'];
        $tingkat_kyu = $_POST['tingkat_kyu'];
        $sabuk = $_POST['sabuk'];
        $tanggal_ujian = $_POST['tanggal_ujian'] ?? null;
        $dojo_cabang = $_POST['dojo_cabang'];
        $asal_sekolah = $_POST['asal_sekolah'] ?? '';
        $asal_provinsi = $_POST['asal_provinsi'] ?? '';
        $no_telp = $_POST['no_telp'];
        $email = $_POST['email'];
        $nama_wali = $_POST['nama_wali'];
        $no_telp_wali = $_POST['no_telp_wali'];
        $status = $_POST['status'];
        $alamat = $_POST['alamat'];
        $keterangan = $_POST['keterangan'] ?? '';
        
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

            $foto = 'uploads/kohai/' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $file_ext;
            if(!ypok_upload_file($_FILES['foto']['tmp_name'], $foto, $_FILES['foto']['type'] ?? 'application/octet-stream')) {
                throw new Exception('Gagal mengupload file');
            }
        } elseif(!empty($_POST['foto_url'])) {
            // Validate and sanitize URL
            $foto = filter_var($_POST['foto_url'], FILTER_VALIDATE_URL) ? $_POST['foto_url'] : '';
        }
        
        if($_POST['action'] == 'edit_kohai') {
            $id = $_POST['id'];
            
            $sql = "UPDATE kohai SET kode_kohai = ?, no_registrasi_ijazah = ?, nama = ?, tempat_lahir = ?, tanggal_lahir = ?, 
                    jenis_kelamin = ?, tingkat_kyu = ?, sabuk = ?, tanggal_ujian = ?, dojo_cabang = ?, 
                    asal_sekolah = ?, asal_provinsi = ?, no_telp = ?, 
                    email = ?, nama_wali = ?, no_telp_wali = ?, status = ?, alamat = ?, keterangan = ?";
            $params = [$kode_kohai, $no_registrasi_ijazah, $nama, $tempat_lahir, $tanggal_lahir, $jenis_kelamin, 
                      $tingkat_kyu, $sabuk, $tanggal_ujian, $dojo_cabang, $asal_sekolah, $asal_provinsi, $no_telp, $email, $nama_wali, 
                      $no_telp_wali, $status, $alamat, $keterangan];
            
            if($foto) {
                $sql .= ", foto = ?";
                $params[] = $foto;
            }
            
            $sql .= " WHERE id = ?";
            $params[] = $id;
            
            $stmt = $pdo->prepare($sql);
            
            if($stmt->execute($params)) {
                // Delete old prestasi and insert new ones
                $pdo->prepare("DELETE FROM prestasi_kohai WHERE kohai_id = ?")->execute([$id]);
                if(isset($_POST['prestasi_nama']) && is_array($_POST['prestasi_nama'])) {
                    foreach($_POST['prestasi_nama'] as $prestasi) {
                        if(!empty($prestasi)) {
                            $stmt = $pdo->prepare("INSERT INTO prestasi_kohai (kohai_id, nama_prestasi) VALUES (?, ?)");
                            $stmt->execute([$id, $prestasi]);
                        }
                    }
                }
                
                // Delete old sertifikasi and insert new ones
                $pdo->prepare("DELETE FROM sertifikasi_kohai WHERE kohai_id = ?")->execute([$id]);
                if(isset($_POST['sertifikasi_nama']) && is_array($_POST['sertifikasi_nama'])) {
                    foreach($_POST['sertifikasi_nama'] as $index => $sert_nama) {
                        if(!empty($sert_nama)) {
                            $stmt = $pdo->prepare("INSERT INTO sertifikasi_kohai (kohai_id, nama_sertifikasi, nomor_sertifikat, penerbit, tanggal_terbit, tanggal_kadaluarsa, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
                            $stmt->execute([
                                $id,
                                $sert_nama,
                                $_POST['sertifikasi_nomor'][$index] ?? '',
                                $_POST['sertifikasi_penerbit'][$index] ?? '',
                                $_POST['sertifikasi_tanggal'][$index] ?? null,
                                $_POST['sertifikasi_kadaluarsa'][$index] ?? null,
                                $_POST['sertifikasi_status'][$index] ?? 'valid'
                            ]);
                        }
                    }
                }
                
                header('Location: kohai.php?updated=1');
                exit();
            }
        } else {
            $stmt = $pdo->prepare("INSERT INTO kohai (kode_kohai, no_registrasi_ijazah, nama, tempat_lahir, tanggal_lahir, jenis_kelamin, tingkat_kyu, sabuk, tanggal_ujian, dojo_cabang, asal_sekolah, asal_provinsi, no_telp, email, nama_wali, no_telp_wali, status, alamat, keterangan, foto) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            if($stmt->execute([$kode_kohai, $no_registrasi_ijazah, $nama, $tempat_lahir, $tanggal_lahir, $jenis_kelamin, $tingkat_kyu, $sabuk, $tanggal_ujian, $dojo_cabang, $asal_sekolah, $asal_provinsi, $no_telp, $email, $nama_wali, $no_telp_wali, $status, $alamat, $keterangan, $foto])) {
                $kohai_id = $pdo->lastInsertId();
                
                // Insert Prestasi
                if(isset($_POST['prestasi_nama']) && is_array($_POST['prestasi_nama'])) {
                    foreach($_POST['prestasi_nama'] as $prestasi) {
                        if(!empty($prestasi)) {
                            $stmt = $pdo->prepare("INSERT INTO prestasi_kohai (kohai_id, nama_prestasi) VALUES (?, ?)");
                            $stmt->execute([$kohai_id, $prestasi]);
                        }
                    }
                }
                
                // Insert Sertifikasi
                if(isset($_POST['sertifikasi_nama']) && is_array($_POST['sertifikasi_nama'])) {
                    foreach($_POST['sertifikasi_nama'] as $index => $sert_nama) {
                        if(!empty($sert_nama)) {
                            $stmt = $pdo->prepare("INSERT INTO sertifikasi_kohai (kohai_id, nama_sertifikasi, nomor_sertifikat, penerbit, tanggal_terbit, tanggal_kadaluarsa, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
                            $stmt->execute([
                                $kohai_id,
                                $sert_nama,
                                $_POST['sertifikasi_nomor'][$index] ?? '',
                                $_POST['sertifikasi_penerbit'][$index] ?? '',
                                $_POST['sertifikasi_tanggal'][$index] ?? null,
                                $_POST['sertifikasi_kadaluarsa'][$index] ?? null,
                                $_POST['sertifikasi_status'][$index] ?? 'valid'
                            ]);
                        }
                    }
                }
                
                header('Location: kohai.php?success=1');
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
    $where = "WHERE kode_kohai LIKE ?
              OR nama LIKE ?
              OR tingkat_kyu LIKE ?
              OR sabuk LIKE ?
              OR dojo_cabang LIKE ?
              OR no_telp LIKE ?
              OR alamat LIKE ?
              OR email LIKE ?
              OR tempat_lahir LIKE ?
              OR nama_wali LIKE ?
              OR no_telp_wali LIKE ?
              OR status LIKE ?
              OR DATE_FORMAT(tanggal_lahir, '%d/%m/%Y') LIKE ?
              OR DATE_FORMAT(created_at, '%d/%m/%Y') LIKE ?";
    $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm];
}

if($search) {
    $stmt = $pdo->prepare("SELECT * FROM kohai $where ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
    $stmt->execute($params);
    $kohai_list = $stmt->fetchAll();
    
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM kohai $where");
    $countStmt->execute($params);
    $total = $countStmt->fetchColumn();
} else {
    $stmt = $pdo->query("SELECT * FROM kohai ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
    $kohai_list = $stmt->fetchAll();
    $total = $pdo->query("SELECT COUNT(*) FROM kohai")->fetchColumn();
}

$total_pages = ceil($total / $limit);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Kohai - YPOK Management</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Menggunakan style yang sama dari msh.php */
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
        }
        
        .modal-overlay.active {
            display: flex;
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
        
        @keyframes slideDown {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
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
        
        /* Badge styles */
        .badge-kyu {
            background: #3b82f6;
            color: #fff;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
        }
        
        .badge-sabuk {
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
        }
        
        .badge-sabuk.biru { background: #1e40af; color: #fff; }
        .badge-sabuk.kuning { background: #fbbf24; color: #000; }
        .badge-sabuk.orange { background: #f97316; color: #fff; }
        .badge-sabuk.hijau { background: #10b981; color: #fff; }
        .badge-sabuk.putih { background: #f3f4f6; color: #000; border: 1px solid #d1d5db; }
        
        .badge-aktif {
            background: #10b981;
            color: #fff;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
        }
        
        .badge-nonaktif {
            background: #ef4444;
            color: #fff;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
        }
        
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
            from { transform: translateX(400px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @keyframes slideOutRight {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(400px); opacity: 0; }
        }
        
        .toast-success { border-left: 4px solid #10b981; }
        .toast-error { border-left: 4px solid #ef4444; }
        
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
        
        .toast-success .toast-icon { background: #d1fae5; color: #065f46; }
        .toast-error .toast-icon { background: #fee2e2; color: #991b1b; }
        
        .toast-content { flex: 1; }
        
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
        
        /* Modal Detail */
        .modal-detail {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            align-items: center;
            justify-content: center;
            z-index: 10000;
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
            color: #fff;
            cursor: pointer;
            font-size: 18px;
            padding: 0;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s;
        }
        
        .btn-close-detail:hover {
            background: rgba(255, 255, 255, 0.2);
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
    <?php include __DIR__ . '/../components/navbar.php'; ?>
    
    <!-- Toast Notifications -->
    <?php if(isset($_GET['success'])): ?>
        <div class="toast-notification toast-success" id="toast">
            <div class="toast-icon">✓</div>
            <div class="toast-content">
                <div class="toast-title">Berhasil!</div>
                <div class="toast-message">Data Kohai berhasil ditambahkan</div>
            </div>
            <button class="toast-close" onclick="closeToast()">×</button>
        </div>
    <?php endif; ?>
    
    <?php if(isset($_GET['updated'])): ?>
        <div class="toast-notification toast-success" id="toast">
            <div class="toast-icon">✓</div>
            <div class="toast-content">
                <div class="toast-title">Berhasil!</div>
                <div class="toast-message">Data Kohai berhasil diupdate</div>
            </div>
            <button class="toast-close" onclick="closeToast()">×</button>
        </div>
    <?php endif; ?>
    
    <?php if(isset($_GET['deleted'])): ?>
        <div class="toast-notification toast-error" id="toast">
            <div class="toast-icon">🗑️</div>
            <div class="toast-content">
                <div class="toast-title">Berhasil!</div>
                <div class="toast-message">Data Kohai berhasil dihapus</div>
            </div>
            <button class="toast-close" onclick="closeToast()">×</button>
        </div>
    <?php endif; ?>
    
    <div class="main-content">
        <div class="top-bar">
            <h2 class="page-title">Data Kohai</h2>
            <div class="user-info">
                <span class="icon">👤</span>
                <span><?php echo $_SESSION['nama_lengkap']; ?></span>
            </div>
        </div>
        
        <div class="container">
            <div class="content-header">
                <h1>Data Kohai</h2>
                
                <div class="search-bar">
                    <form method="GET" style="display: flex; gap: 15px; flex: 1;" id="searchForm">
                        <input type="text" name="search" id="searchInput" placeholder="🔍 Cari Kohai (nama, kode, tanggal, dojo, kyu, sabuk, wali, status)..." value="<?php echo htmlspecialchars($search); ?>" autocomplete="off">
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
                            <th style="width: 180px;">NO. REGISTRASI IJAZAH</th>
                            <th style="width: 150px;">NAMA</th>
                            <th style="width: 140px;">TEMPAT, TGL LAHIR</th>
                            <th style="width: 90px;">WARNA SABUK</th>
                            <th style="width: 150px;">TANGGAL UJIAN, KYU, DAN WARNA SABUK</th>
                            <th style="width: 180px;">CABANG/ ASAL SEKOLAH<br>RANTING</th>
                            <th style="width: 150px;">ASAL PROVINSI / KAB/ KOTA</th>
                            <th style="width: 150px;">KETERANGAN</th>
                            <th style="width: 90px;">STATUS ANGGOTA</th>
                            <th style="width: 100px;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($kohai_list) > 0): ?>
                            <?php foreach($kohai_list as $index => $kohai): ?>
                            <tr>
                                <td style="text-align: center;"><?php echo $offset + $index + 1; ?></td>
                                <td style="font-size: 12px;">
                                    <strong><?php echo htmlspecialchars($kohai['no_registrasi_ijazah'] ?? $kohai['kode_kohai']); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($kohai['nama']); ?></td>
                                <td style="font-size: 13px;">
                                    <?php 
                                    $tempat = htmlspecialchars($kohai['tempat_lahir']);
                                    $tgl = date('d-m-Y', strtotime($kohai['tanggal_lahir']));
                                    echo strtoupper($tempat) . ',' . $tgl;
                                    ?>
                                </td>
                                <td style="text-align: center;">
                                    <span class="badge-sabuk <?php echo strtolower($kohai['sabuk']); ?>">
                                        <?php 
                                        $kyu = htmlspecialchars($kohai['tingkat_kyu']);
                                        $sabuk = strtoupper($kohai['sabuk']);
                                        echo $kyu . ' ' . $sabuk;
                                        ?>
                                    </span>
                                </td>
                                <td style="font-size: 13px;">
                                    <?php 
                                    $tgl_ujian = $kohai['tanggal_ujian'] ? date('d-m-Y', strtotime($kohai['tanggal_ujian'])) : '-';
                                    echo $tgl_ujian;
                                    ?>
                                </td>
                                <td style="font-size: 13px;"><?php echo htmlspecialchars($kohai['asal_sekolah'] ?? $kohai['dojo_cabang']); ?></td>
                                <td style="font-size: 13px;"><?php echo htmlspecialchars($kohai['asal_provinsi'] ?? $kohai['dojo_cabang']); ?></td>
                                <td style="font-size: 12px;" title="<?php echo htmlspecialchars($kohai['keterangan'] ?? ''); ?>">
                                    <?php echo htmlspecialchars(substr($kohai['keterangan'] ?? '', 0, 30)); ?>
                                </td>
                                <td style="text-align: center;">
                                    <span class="badge-<?php echo strtolower($kohai['status']); ?>">
                                        <?php echo strtoupper($kohai['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-icon btn-view" onclick="viewDetail(<?php echo $kohai['id']; ?>)" title="Lihat Detail">👁️</button>
                                        <button class="btn-icon btn-edit" onclick="editData(<?php echo $kohai['id']; ?>)" title="Edit">✏️</button>
                                        <button class="btn-icon btn-delete" onclick="deleteData(<?php echo $kohai['id']; ?>, '<?php echo htmlspecialchars($kohai['nama']); ?>')" title="Hapus">🗑️</button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="11" style="text-align: center; padding: 40px; color: #94a3b8;">
                                    <div style="font-size: 48px; margin-bottom: 10px;">📋</div>
                                    <div>Tidak ada data Kohai</div>
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
    
    <!-- Modal Form Tambah/Edit Kohai -->
    <div class="modal-overlay" id="modalKohai">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">➕ Tambah Data Kohai</h2>
                <button class="btn-close" onclick="closeModal()">×</button>
            </div>
            
            <form method="POST" enctype="multipart/form-data" id="formKohai">
                <input type="hidden" name="action" id="formAction" value="add_kohai">
                <input type="hidden" name="id" id="kohai_id">
                
                <div class="modal-body">
                    <!-- Data Utama -->
                    <div class="form-section">
                        <div class="section-title">📝 Data Utama</div>
                        
                        <div class="form-row full">
                            <div class="form-group">
                                <label>Foto Kohai</label>
                                <div class="foto-upload">
                                    <div class="foto-preview" id="fotoPreview">
                                        <span class="icon">👤</span>
                                    </div>
                                    <input type="file" name="foto" id="fotoInput" accept="image/*" style="display: none;" onchange="previewFoto(event)">
                                    <button type="button" class="btn-upload" onclick="document.getElementById('fotoInput').click()">
                                        📷 Pilih Foto
                                    </button>
                                    <div class="url-helper">
                                        atau URL: <input type="text" name="foto_url" id="foto_url" placeholder="https://...">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Kode Kohai <span>*</span></label>
                                <input type="text" name="kode_kohai" id="kode_kohai" placeholder="KOH-001" required>
                            </div>
                            <div class="form-group">
                                <label>No. Registrasi Ijazah</label>
                                <input type="text" name="no_registrasi_ijazah" id="no_registrasi_ijazah" placeholder="YPOK.H-I-23-0001 YPOK.B-II-23-0001">
                                <small style="color: #64748b; font-size: 11px;">Pisahkan dengan spasi jika lebih dari satu</small>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Nama Lengkap <span>*</span></label>
                                <input type="text" name="nama" id="nama" placeholder="Masukkan nama lengkap" required>
                            </div>
                            <div class="form-group">
                                <!-- Empty for layout balance -->
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Tempat Lahir <span>*</span></label>
                                <input type="text" name="tempat_lahir" id="tempat_lahir" placeholder="Jakarta" required>
                            </div>
                            <div class="form-group">
                                <label>Tanggal Lahir <span>*</span></label>
                                <input type="date" name="tanggal_lahir" id="tanggal_lahir" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Jenis Kelamin <span>*</span></label>
                                <select name="jenis_kelamin" id="jenis_kelamin" required>
                                    <option value="">Pilih...</option>
                                    <option value="L">Laki-laki</option>
                                    <option value="P">Perempuan</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Tingkat Kyu <span>*</span></label>
                                <select name="tingkat_kyu" id="tingkat_kyu" required>
                                    <option value="">Pilih...</option>
                                    <option value="Kyu 9">Kyu 9 (Putih)</option>
                                    <option value="Kyu 8">Kyu 8 (Kuning)</option>
                                    <option value="Kyu 7">Kyu 7 (Hijau)</option>
                                    <option value="Kyu 6">Kyu 6 (Biru)</option>
                                    <option value="Kyu 5">Kyu 5 (Biru)</option>
                                    <option value="Kyu 4">Kyu 4 (Biru)</option>
                                    <option value="Kyu 3">Kyu 3 (Coklat)</option>
                                    <option value="Kyu 2">Kyu 2 (Coklat)</option>
                                    <option value="Kyu 1">Kyu 1 (Coklat)</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Sabuk <span>*</span></label>
                                <select name="sabuk" id="sabuk" required>
                                    <option value="">Pilih...</option>
                                    <option value="Putih">Putih</option>
                                    <option value="Kuning">Kuning</option>
                                    <option value="Hijau">Hijau</option>
                                    <option value="Biru">Biru</option>
                                    <option value="Coklat">Coklat</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Tanggal Ujian</label>
                                <input type="date" name="tanggal_ujian" id="tanggal_ujian" placeholder="Tanggal ujian terakhir">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Dojo/Cabang <span>*</span></label>
                                <input type="text" name="dojo_cabang" id="dojo_cabang" placeholder="Jakarta Pusat" required>
                            </div>
                            <div class="form-group">
                                <label>Asal Sekolah/Ranting</label>
                                <input type="text" name="asal_sekolah" id="asal_sekolah" placeholder="SDN GONDANGDIA 01">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Asal Provinsi / Kab/ Kota</label>
                                <input type="text" name="asal_provinsi" id="asal_provinsi" placeholder="JAKARTA PUSAT, DKI JAKARTA">
                            </div>
                            <div class="form-group">
                                <!-- Empty for layout balance -->
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>No. Telepon <span>*</span></label>
                                <input type="tel" name="no_telp" id="no_telp" placeholder="08xxxxxxxxxx" required>
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" id="email" placeholder="email@example.com">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Nama Wali</label>
                                <input type="text" name="nama_wali" id="nama_wali" placeholder="Nama orang tua/wali">
                            </div>
                            <div class="form-group">
                                <label>No. Telepon Wali</label>
                                <input type="tel" name="no_telp_wali" id="no_telp_wali" placeholder="08xxxxxxxxxx">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Status <span>*</span></label>
                                <select name="status" id="status" required>
                                    <option value="">Pilih...</option>
                                    <option value="Aktif">Aktif</option>
                                    <option value="Non-Aktif">Non-Aktif</option>
                                    <option value="Meninggal">Meninggal</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <!-- Empty for layout balance -->
                            </div>
                        </div>
                        
                        <div class="form-row full">
                            <div class="form-group">
                                <label>Alamat Lengkap <span>*</span></label>
                                <textarea name="alamat" id="alamat" rows="2" placeholder="Masukkan alamat lengkap..." required></textarea>
                            </div>
                        </div>
                        
                        <div class="form-row full">
                            <div class="form-group">
                                <label>Keterangan</label>
                                <textarea name="keterangan" id="keterangan" rows="2" placeholder="Keterangan tambahan (opsional)"></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Prestasi & Penghargaan -->
                    <div class="form-section">
                        <div class="section-title">🏆 Prestasi & Penghargaan</div>
                        <div id="prestasiContainer">
                            <div class="prestasi-box" style="margin-bottom: 12px;">
                                <div class="form-group">
                                    <input type="text" name="prestasi_nama[]" placeholder="Contoh: Juara 1 Kata Junior 2023">
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn-add-prestasi" onclick="tambahPrestasi()">➕ Tambah Prestasi</button>
                    </div>
                    
                    <!-- Sertifikasi Detail -->
                    <div class="form-section">
                        <div class="section-title">📜 Sertifikasi Detail</div>
                        <p class="info-text">Tambahkan detail sertifikasi ujian kenaikan sabuk (opsional)</p>
                        
                        <div id="sertifikasiContainer">
                            <div class="sertifikasi-card">
                                <div class="sertifikasi-header">
                                    <div style="font-weight: 600; color: #1e40af;">📄 Sertifikasi #1</div>
                                    <button type="button" class="btn-remove" onclick="this.parentElement.parentElement.remove()">✕</button>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Nama Sertifikasi</label>
                                        <input type="text" name="sertifikasi_nama[]" placeholder="Ujian Kenaikan Sabuk Biru">
                                    </div>
                                    <div class="form-group">
                                        <label>Nomor Sertifikat</label>
                                        <input type="text" name="sertifikasi_nomor[]" placeholder="UKS-2024-XXX">
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Penerbit</label>
                                        <input type="text" name="sertifikasi_penerbit[]" placeholder="YPOK, FORKI, dll">
                                    </div>
                                    <div class="form-group">
                                        <label>Status <span>*</span></label>
                                        <select name="sertifikasi_status[]" class="sertifikasi-status-select" onchange="toggleKadaluarsaKohai(this)" required>
                                            <option value="">Pilih...</option>
                                            <option value="valid">Valid (Ada Masa Berlaku)</option>
                                            <option value="expired">Expired (Sudah Kadaluarsa)</option>
                                            <option value="permanent">Permanent (Selamanya)</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Tanggal Terbit</label>
                                        <input type="date" name="sertifikasi_tanggal[]">
                                    </div>
                                    <div class="form-group kadaluarsa-field" style="display: none;">
                                        <label>Tanggal Kadaluarsa</label>
                                        <input type="date" name="sertifikasi_kadaluarsa[]" class="kadaluarsa-input">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <button type="button" class="btn-add-sertifikasi" onclick="tambahSertifikasi()">➕ Tambah Sertifikasi</button>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeModal()">Batal</button>
                    <button type="submit" class="btn-submit">💾 Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal Detail Kohai -->
    <div class="modal-detail" id="modalDetail">
        <div class="detail-content">
            <div class="detail-header">
                <h2>📋 Detail Kohai</h2>
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
                    url.searchParams.delete('page');

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
            document.getElementById('modalKohai').classList.add('active');
            document.getElementById('modalTitle').textContent = '➕ Tambah Data Kohai';
            document.getElementById('formAction').value = 'add_kohai';
            document.getElementById('formKohai').reset();
            document.getElementById('fotoPreview').innerHTML = '<span class="icon">👤</span>';
            
            // Reset prestasi container to default
            const prestasiContainer = document.getElementById('prestasiContainer');
            prestasiContainer.innerHTML = `
                <div class="prestasi-box" style="margin-bottom: 12px;">
                    <div class="form-group">
                        <input type="text" name="prestasi_nama[]" placeholder="Contoh: Juara 1 Kata Junior 2023">
                    </div>
                </div>
            `;
            
            // Reset sertifikasi container to default
            const sertifikasiContainer = document.getElementById('sertifikasiContainer');
            sertifikasiContainer.innerHTML = `
                <div class="sertifikasi-card">
                    <div class="sertifikasi-header">
                        <div style="font-weight: 600; color: #1e40af;">📄 Sertifikasi #1</div>
                        <button type="button" class="btn-remove" onclick="this.parentElement.parentElement.remove()">Hapus</button>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Nama Sertifikasi</label>
                            <input type="text" name="sertifikasi_nama[]" placeholder="Ujian Kenaikan Sabuk Biru">
                        </div>
                        <div class="form-group">
                            <label>Nomor Sertifikat</label>
                            <input type="text" name="sertifikasi_nomor[]" placeholder="UKS-2024-XXX">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Penerbit</label>
                            <input type="text" name="sertifikasi_penerbit[]" placeholder="YPOK, FORKI, dll">
                        </div>
                        <div class="form-group">
                            <label>Status <span>*</span></label>
                            <select name="sertifikasi_status[]" class="sertifikasi-status-select" onchange="toggleKadaluarsaKohai(this)" required>
                                <option value="">Pilih...</option>
                                <option value="valid">Valid (Ada Masa Berlaku)</option>
                                <option value="expired">Expired (Sudah Kadaluarsa)</option>
                                <option value="permanent">Permanent (Selamanya)</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Tanggal Terbit</label>
                            <input type="date" name="sertifikasi_tanggal[]">
                        </div>
                        <div class="form-group kadaluarsa-field" style="display: none;">
                            <label>Tanggal Kadaluarsa</label>
                            <input type="date" name="sertifikasi_kadaluarsa[]" class="kadaluarsa-input">
                        </div>
                    </div>
                </div>
            `;
            sertifikasiCount = 1;
        }
        
        function closeModal() {
            document.getElementById('modalKohai').classList.remove('active');
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
        
        function tambahPrestasi() {
            const container = document.getElementById('prestasiContainer');
            const prestasiBox = document.createElement('div');
            prestasiBox.className = 'prestasi-box';
            prestasiBox.style.marginBottom = '12px';
            prestasiBox.innerHTML = `
                <div style="display: flex; gap: 10px; align-items: center;">
                    <div class="form-group" style="flex: 1; margin: 0;">
                        <input type="text" name="prestasi_nama[]" placeholder="Contoh: Juara 1 Kata Junior 2023">
                    </div>
                    <button type="button" class="btn-remove" onclick="this.parentElement.parentElement.remove()">✕</button>
                </div>
            `;
            container.appendChild(prestasiBox);
        }
        
        function toggleKadaluarsaKohai(selectElement) {
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
                    <div style="font-weight: 600; color: #1e40af;">📄 Sertifikasi #${sertifikasiCount}</div>
                    <button type="button" class="btn-remove" onclick="this.parentElement.parentElement.remove()">Hapus</button>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Nama Sertifikasi</label>
                        <input type="text" name="sertifikasi_nama[]" placeholder="Ujian Kenaikan Sabuk Biru">
                    </div>
                    <div class="form-group">
                        <label>Nomor Sertifikat</label>
                        <input type="text" name="sertifikasi_nomor[]" placeholder="UKS-2024-XXX">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Penerbit</label>
                        <input type="text" name="sertifikasi_penerbit[]" placeholder="YPOK, FORKI, dll">
                    </div>
                    <div class="form-group">
                        <label>Status <span>*</span></label>
                        <select name="sertifikasi_status[]" class="sertifikasi-status-select" onchange="toggleKadaluarsaKohai(this)" required>
                            <option value="">Pilih...</option>
                            <option value="valid">Valid (Ada Masa Berlaku)</option>
                            <option value="expired">Expired (Sudah Kadaluarsa)</option>
                            <option value="permanent">Permanent (Selamanya)</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Tanggal Terbit</label>
                        <input type="date" name="sertifikasi_tanggal[]">
                    </div>
                    <div class="form-group kadaluarsa-field" style="display: none;">
                        <label>Tanggal Kadaluarsa</label>
                        <input type="date" name="sertifikasi_kadaluarsa[]" class="kadaluarsa-input">
                    </div>
                </div>
            `;
            container.appendChild(newCard);
        }
        
        function editData(id) {
            document.getElementById('modalKohai').classList.add('active');
            document.getElementById('modalTitle').textContent = '✏️ Edit Data Kohai';
            document.getElementById('formAction').value = 'edit_kohai';
            
            // Fetch data via AJAX
            fetch('../api/kohai_get.php?id=' + id)
                .then(response => response.json())
                .then(data => {
                    // Fill basic data
                    document.getElementById('kohai_id').value = data.id;
                    document.getElementById('kode_kohai').value = data.kode_kohai || '';
                    document.getElementById('no_registrasi_ijazah').value = data.no_registrasi_ijazah || '';
                    document.getElementById('nama').value = data.nama || '';
                    document.getElementById('tempat_lahir').value = data.tempat_lahir || '';
                    document.getElementById('tanggal_lahir').value = data.tanggal_lahir || '';
                    document.getElementById('jenis_kelamin').value = data.jenis_kelamin || 'L';
                    document.getElementById('tingkat_kyu').value = data.tingkat_kyu || '';
                    document.getElementById('sabuk').value = data.sabuk || '';
                    document.getElementById('tanggal_ujian').value = data.tanggal_ujian || '';
                    document.getElementById('dojo_cabang').value = data.dojo_cabang || '';
                    document.getElementById('asal_sekolah').value = data.asal_sekolah || '';
                    document.getElementById('asal_provinsi').value = data.asal_provinsi || '';
                    document.getElementById('no_telp').value = data.no_telp || '';
                    document.getElementById('email').value = data.email || '';
                    document.getElementById('nama_wali').value = data.nama_wali || '';
                    document.getElementById('no_telp_wali').value = data.no_telp_wali || '';
                    document.getElementById('status').value = data.status || 'Aktif';
                    document.getElementById('alamat').value = data.alamat || '';
                    document.getElementById('keterangan').value = data.keterangan || '';
                    document.getElementById('foto_url').value = data.foto || '';
                    
                    // Preview existing foto
                    if(data.foto) {
                        document.getElementById('fotoPreview').innerHTML = '<img src="' + data.foto + '">';
                    } else {
                        document.getElementById('fotoPreview').innerHTML = '<span class="icon">👤</span>';
                    }
                    
                    // Load Prestasi
                    const prestasiContainer = document.getElementById('prestasiContainer');
                    prestasiContainer.innerHTML = '';
                    
                    if(data.prestasi && data.prestasi.length > 0) {
                        data.prestasi.forEach((prestasi) => {
                            const prestasiBox = document.createElement('div');
                            prestasiBox.className = 'prestasi-box';
                            prestasiBox.style.marginBottom = '12px';
                            prestasiBox.innerHTML = `
                                <div style="display: flex; gap: 10px; align-items: center;">
                                    <div class="form-group" style="flex: 1; margin: 0;">
                                        <input type="text" name="prestasi_nama[]" value="${prestasi.nama_prestasi}" placeholder="Contoh: Juara 1 Kata Junior 2023">
                                    </div>
                                    <button type="button" class="btn-remove" onclick="this.parentElement.parentElement.remove()">✕</button>
                                </div>
                            `;
                            prestasiContainer.appendChild(prestasiBox);
                        });
                    } else {
                        prestasiContainer.innerHTML = `
                            <div class="prestasi-box" style="margin-bottom: 12px;">
                                <div class="form-group">
                                    <input type="text" name="prestasi_nama[]" placeholder="Contoh: Juara 1 Kata Junior 2023">
                                </div>
                            </div>
                        `;
                    }
                    
                    // Load Sertifikasi
                    const sertifikasiContainer = document.getElementById('sertifikasiContainer');
                    sertifikasiContainer.innerHTML = '';
                    sertifikasiCount = 0;
                    
                    if(data.sertifikasi && data.sertifikasi.length > 0) {
                        data.sertifikasi.forEach((sert) => {
                            sertifikasiCount++;
                            const sertCard = document.createElement('div');
                            sertCard.className = 'sertifikasi-card';
                            
                            const showKadaluarsa = (sert.status == 'valid' || sert.status == 'expired') ? 'block' : 'none';
                            const kadaluarsaRequired = (sert.status == 'valid' || sert.status == 'expired') ? 'required' : '';
                            
                            sertCard.innerHTML = `
                                <div class="sertifikasi-header">
                                    <div style="font-weight: 600; color: #1e40af;">📄 Sertifikasi #${sertifikasiCount}</div>
                                    <button type="button" class="btn-remove" onclick="this.parentElement.parentElement.remove()">Hapus</button>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Nama Sertifikasi</label>
                                        <input type="text" name="sertifikasi_nama[]" value="${sert.nama_sertifikasi || ''}" placeholder="Ujian Kenaikan Sabuk Biru">
                                    </div>
                                    <div class="form-group">
                                        <label>Nomor Sertifikat</label>
                                        <input type="text" name="sertifikasi_nomor[]" value="${sert.nomor_sertifikat || ''}" placeholder="UKS-2024-XXX">
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Penerbit</label>
                                        <input type="text" name="sertifikasi_penerbit[]" value="${sert.penerbit || ''}" placeholder="YPOK, FORKI, dll">
                                    </div>
                                    <div class="form-group">
                                        <label>Status <span>*</span></label>
                                        <select name="sertifikasi_status[]" class="sertifikasi-status-select" onchange="toggleKadaluarsaKohai(this)" required>
                                            <option value="">Pilih...</option>
                                            <option value="valid" ${sert.status == 'valid' ? 'selected' : ''}>Valid (Ada Masa Berlaku)</option>
                                            <option value="expired" ${sert.status == 'expired' ? 'selected' : ''}>Expired (Sudah Kadaluarsa)</option>
                                            <option value="permanent" ${sert.status == 'permanent' ? 'selected' : ''}>Permanent (Selamanya)</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Tanggal Terbit</label>
                                        <input type="date" name="sertifikasi_tanggal[]" value="${sert.tanggal_terbit || ''}">
                                    </div>
                                    <div class="form-group kadaluarsa-field" style="display: ${showKadaluarsa};">
                                        <label>Tanggal Kadaluarsa</label>
                                        <input type="date" name="sertifikasi_kadaluarsa[]" class="kadaluarsa-input" value="${sert.tanggal_kadaluarsa || ''}" ${kadaluarsaRequired}>
                                    </div>
                                </div>
                            `;
                            sertifikasiContainer.appendChild(sertCard);
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading data: ' + error.message);
                });
        }
        
        function viewDetail(id) {
            const modalDetail = document.getElementById('modalDetail');
            if(!modalDetail) {
                alert('Modal detail belum tersedia. Silakan buat file kohai_detail.php terlebih dahulu.');
                return;
            }
            
            modalDetail.classList.add('active');
            
            // Fetch data via AJAX
            fetch('kohai_detail.php?id=' + id)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('detailContent').innerHTML = data;
                })
                .catch(error => {
                    document.getElementById('detailContent').innerHTML = '<p style="text-align:center;color:red;">Error loading data</p>';
                });
        }
        
        function closeDetail() {
            const modalDetail = document.getElementById('modalDetail');
            if(modalDetail) {
                modalDetail.classList.remove('active');
            }
        }
        
        function deleteData(id, nama) {
            if(confirm('Apakah Anda yakin ingin menghapus data:\n' + nama + '?')) {
                window.location.href = 'kohai.php?delete=' + id;
            }
        }
        
        // Close modal when clicking outside
        document.getElementById('modalKohai').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
        
        const modalDetail = document.getElementById('modalDetail');
        if(modalDetail) {
            modalDetail.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeDetail();
                }
            });
        }
        
        // Auto close toast
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
