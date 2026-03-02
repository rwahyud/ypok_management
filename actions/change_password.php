<?php
require_once '../config/supabase.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $old_password = $_POST['old_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate input
    if(empty($old_password) || empty($new_password) || empty($confirm_password)) {
        header('Location: ../pages/pengaturan/index.php?error=' . urlencode('Semua field harus diisi.'));
        exit();
    }
    
    // Check if new passwords match
    if($new_password !== $confirm_password) {
        header('Location: ../pages/pengaturan/index.php?error=' . urlencode('Password baru dan konfirmasi tidak cocok.'));
        exit();
    }
    
    // Validate password length
    if(strlen($new_password) < 8) {
        header('Location: ../pages/pengaturan/index.php?error=' . urlencode('Password minimal 8 karakter.'));
        exit();
    }
    
    try {
        // Get current user data
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        if(!$user) {
            header('Location: ../pages/pengaturan/index.php?error=' . urlencode('User tidak ditemukan.'));
            exit();
        }
        
        // Verify old password
        $is_valid = false;
        
        // Check if password is hashed
        if(password_verify($old_password, $user['password'])) {
            $is_valid = true;
        } elseif($old_password === $user['password']) {
            // Plain text password (for backward compatibility)
            $is_valid = true;
        }
        
        if(!$is_valid) {
            header('Location: ../pages/pengaturan/index.php?error=' . urlencode('Password lama salah.'));
            exit();
        }
        
        // Hash new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update password
        $stmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$hashed_password, $user_id]);
        
        // Log the change
        error_log("Password changed for user ID: $user_id");
        
        header('Location: ../pages/pengaturan/index.php?success=password');
        exit();
        
    } catch(PDOException $e) {
        error_log("Error changing password: " . $e->getMessage());
        header('Location: ../pages/pengaturan/index.php?error=' . urlencode('Terjadi kesalahan saat mengubah password.'));
        exit();
    }
}

header('Location: ../pages/pengaturan/index.php');
exit();
