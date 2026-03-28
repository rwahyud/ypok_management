<?php
require_once '../config/database.php';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $password_confirm = trim($_POST['password_confirm'] ?? '');
    
    // Validation
    if(empty($nama_lengkap) || empty($username) || empty($password) || empty($password_confirm)) {
        header('Location: ../register.php?error=empty');
        exit();
    }
    
    if($password !== $password_confirm) {
        header('Location: ../register.php?error=password_mismatch');
        exit();
    }
    
    if(strlen($password) < 6) {
        header('Location: ../register.php?error=password_short');
        exit();
    }
    
    try {
        // Check if username already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $exists = $stmt->fetchColumn();
        
        if($exists > 0) {
            header('Location: ../register.php?error=username_exists');
            exit();
        }
        
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new user
        $stmt = $pdo->prepare("INSERT INTO users (username, password, nama_lengkap, role, created_at) VALUES (?, ?, ?, 'user', NOW())");
        $stmt->execute([$username, $hashed_password, $nama_lengkap]);
        
        // Redirect to login with success message
        header('Location: ../index.php?registered=1');
        exit();
        
    } catch(PDOException $e) {
        error_log("Registration error: " . $e->getMessage());
        header('Location: ../register.php?error=db');
        exit();
    }
} else {
    header('Location: ../register.php');
    exit();
}
?>
