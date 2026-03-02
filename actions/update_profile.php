<?php
require_once '../config/supabase.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    
    // Handle foto upload jika ada
    if(isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] == 0) {
        // Validate file type
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $file_type = $_FILES['foto_profil']['type'];
        
        // Validate file size (max 2MB)
        $file_size = $_FILES['foto_profil']['size'];
        $max_size = 2 * 1024 * 1024; // 2MB
        
        if(!in_array($file_type, $allowed_types)) {
            header('Location: ../pages/pengaturan/index.php?error=' . urlencode('Format file tidak didukung. Gunakan JPG, PNG, atau GIF.'));
            exit();
        }
        
        if($file_size > $max_size) {
            header('Location: ../pages/pengaturan/index.php?error=' . urlencode('Ukuran file maksimal 2MB.'));
            exit();
        }
        
        // Create upload directory if not exists
        $upload_dir = '../uploads/profil/';
        if(!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Get old photo to delete
        $stmt = $pdo->prepare("SELECT foto_profil FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        // Delete old photo if exists
        if($user && !empty($user['foto_profil']) && file_exists('../' . $user['foto_profil'])) {
            unlink('../' . $user['foto_profil']);
        }
        
        // Generate unique filename
        $file_extension = pathinfo($_FILES['foto_profil']['name'], PATHINFO_EXTENSION);
        $new_filename = 'profile_' . $user_id . '_' . time() . '.' . $file_extension;
        $file_path = $upload_dir . $new_filename;
        
        // Move uploaded file
        if(move_uploaded_file($_FILES['foto_profil']['tmp_name'], $file_path)) {
            // Update database with relative path
            $relative_path = 'uploads/profil/' . $new_filename;
            $stmt = $pdo->prepare("UPDATE users SET foto_profil = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$relative_path, $user_id]);
            
            header('Location: ../pages/pengaturan/index.php?success=profile');
            exit();
        } else {
            header('Location: ../pages/pengaturan/index.php?error=' . urlencode('Gagal mengupload foto.'));
            exit();
        }
    }
    
    // Handle update profile data
    if(isset($_POST['nama_lengkap'])) {
        $nama_lengkap = trim($_POST['nama_lengkap']);
        $email = trim($_POST['email']) ?: null;
        
        try {
            $stmt = $pdo->prepare("UPDATE users SET nama_lengkap = ?, email = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$nama_lengkap, $email, $user_id]);
            
            // Update session
            $_SESSION['nama_lengkap'] = $nama_lengkap;
            
            header('Location: ../pages/pengaturan/index.php?success=profile');
            exit();
            
        } catch(PDOException $e) {
            if(strpos($e->getMessage(), 'unique') !== false) {
                header('Location: ../pages/pengaturan/index.php?error=' . urlencode('Email sudah digunakan oleh user lain.'));
            } else {
                header('Location: ../pages/pengaturan/index.php?error=' . urlencode('Terjadi kesalahan: ' . $e->getMessage()));
            }
            exit();
        }
    }
}

header('Location: ../pages/pengaturan/index.php');
exit();
