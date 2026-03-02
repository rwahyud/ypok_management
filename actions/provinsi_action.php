<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/supabase.php';

// Check authentication
if(!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// CREATE
if($action == 'create') {
    try {
        // Validate required fields
        if(empty($_POST['nama_provinsi'])) {
            throw new Exception('Nama provinsi harus diisi');
        }

        $nama_provinsi = trim($_POST['nama_provinsi']);
        $ibu_kota = trim($_POST['ibu_kota'] ?? '');
        $logo_provinsi = '';

        // Handle file upload
        if(isset($_FILES['logo_provinsi']) && $_FILES['logo_provinsi']['error'] == 0) {
            $upload_dir = '../uploads/provinsi/';
            if(!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
            $file_type = $_FILES['logo_provinsi']['type'];
            
            if(in_array($file_type, $allowed_types)) {
                // Validate file size (max 2MB)
                if($_FILES['logo_provinsi']['size'] > 2097152) {
                    throw new Exception('Ukuran file terlalu besar. Maksimal 2MB');
                }
                
                $file_extension = pathinfo($_FILES['logo_provinsi']['name'], PATHINFO_EXTENSION);
                $file_name = 'provinsi_' . time() . '_' . uniqid() . '.' . $file_extension;
                $upload_path = $upload_dir . $file_name;
                
                if(move_uploaded_file($_FILES['logo_provinsi']['tmp_name'], $upload_path)) {
                    $logo_provinsi = 'uploads/provinsi/' . $file_name;
                } else {
                    throw new Exception('Gagal mengupload file');
                }
            } else {
                throw new Exception('Format file tidak didukung. Gunakan JPG, PNG, atau GIF');
            }
        } elseif(!empty($_POST['url_logo_eksternal'])) {
            $logo_provinsi = filter_var($_POST['url_logo_eksternal'], FILTER_SANITIZE_URL);
        }

        // Insert to database
        $stmt = $pdo->prepare("INSERT INTO provinsi (nama_provinsi, ibu_kota, logo_provinsi, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$nama_provinsi, $ibu_kota, $logo_provinsi]);
        
        // Redirect with success message
        header('Location: ../lokasi.php?success=1');
        exit();
        
    } catch(Exception $e) {
        // Log error for debugging
        error_log('Error creating provinsi: ' . $e->getMessage());
        
        // Redirect with error message
        header('Location: ../lokasi.php?error=1');
        exit();
    }
}

// UPDATE
if($action == 'update') {
    try {
        // Validate required fields
        if(empty($_POST['id']) || empty($_POST['nama_provinsi'])) {
            throw new Exception('Data tidak lengkap');
        }

        $id = (int)$_POST['id'];
        $nama_provinsi = trim($_POST['nama_provinsi']);
        $ibu_kota = trim($_POST['ibu_kota'] ?? '');
        
        // Get current logo
        $current = $pdo->prepare("SELECT logo_provinsi FROM provinsi WHERE id = ?");
        $current->execute([$id]);
        $logo_provinsi = $current->fetchColumn();

        if(!$logo_provinsi) {
            throw new Exception('Data provinsi tidak ditemukan');
        }

        // Handle file upload
        if(isset($_FILES['logo_provinsi']) && $_FILES['logo_provinsi']['error'] == 0) {
            $upload_dir = '../uploads/provinsi/';
            if(!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
            $file_type = $_FILES['logo_provinsi']['type'];
            
            if(in_array($file_type, $allowed_types)) {
                // Validate file size (max 2MB)
                if($_FILES['logo_provinsi']['size'] > 2097152) {
                    throw new Exception('Ukuran file terlalu besar. Maksimal 2MB');
                }
                
                $file_extension = pathinfo($_FILES['logo_provinsi']['name'], PATHINFO_EXTENSION);
                $file_name = 'provinsi_' . time() . '_' . uniqid() . '.' . $file_extension;
                $upload_path = $upload_dir . $file_name;
                
                if(move_uploaded_file($_FILES['logo_provinsi']['tmp_name'], $upload_path)) {
                    // Delete old file if exists and is local
                    if($logo_provinsi && strpos($logo_provinsi, 'uploads/') === 0 && file_exists('../' . $logo_provinsi)) {
                        @unlink('../' . $logo_provinsi);
                    }
                    $logo_provinsi = 'uploads/provinsi/' . $file_name;
                } else {
                    throw new Exception('Gagal mengupload file');
                }
            } else {
                throw new Exception('Format file tidak didukung');
            }
        } elseif(!empty($_POST['url_logo_eksternal']) && $_POST['url_logo_eksternal'] != $logo_provinsi) {
            // Delete old file if switching to external URL
            if($logo_provinsi && strpos($logo_provinsi, 'uploads/') === 0 && file_exists('../' . $logo_provinsi)) {
                @unlink('../' . $logo_provinsi);
            }
            $logo_provinsi = filter_var($_POST['url_logo_eksternal'], FILTER_SANITIZE_URL);
        }

        // Update database
        $stmt = $pdo->prepare("UPDATE provinsi SET nama_provinsi = ?, ibu_kota = ?, logo_provinsi = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$nama_provinsi, $ibu_kota, $logo_provinsi, $id]);
        
        header('Location: ../lokasi.php?updated=1');
        exit();
        
    } catch(Exception $e) {
        error_log('Error updating provinsi: ' . $e->getMessage());
        header('Location: ../lokasi.php?error=1');
        exit();
    }
}

// DELETE
if($action == 'delete') {
    try {
        if(empty($_GET['id'])) {
            throw new Exception('ID tidak valid');
        }

        $id = (int)$_GET['id'];
        
        // Get logo file and check if province exists
        $current = $pdo->prepare("SELECT logo_provinsi FROM provinsi WHERE id = ?");
        $current->execute([$id]);
        $logo_provinsi = $current->fetchColumn();
        
        if($logo_provinsi === false) {
            throw new Exception('Data provinsi tidak ditemukan');
        }
        
        // Delete logo file if exists and is local
        if($logo_provinsi && strpos($logo_provinsi, 'uploads/') === 0 && file_exists('../' . $logo_provinsi)) {
            @unlink('../' . $logo_provinsi);
        }
        
        // Delete all dojo in this province (CASCADE will handle this automatically)
        $pdo->prepare("DELETE FROM dojo WHERE provinsi_id = ?")->execute([$id]);
        
        // Delete province
        $stmt = $pdo->prepare("DELETE FROM provinsi WHERE id = ?");
        $stmt->execute([$id]);
        
        header('Location: ../lokasi.php?deleted=1');
        exit();
        
    } catch(Exception $e) {
        error_log('Error deleting provinsi: ' . $e->getMessage());
        header('Location: ../lokasi.php?error=1');
        exit();
    }
}

// Invalid action
header('Location: ../lokasi.php?error=1');
exit();
?>
