<?php
require_once '../config/supabase.php';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_lengkap = $_POST['nama_lengkap'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if($password !== $confirm_password) {
        header('Location: ../register.php?error=password_mismatch');
        exit();
    }
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    
    if($stmt->rowCount() > 0) {
        header('Location: ../register.php?error=username_exists');
        exit();
    }
    
    $hashed_password = md5($password);
    $stmt = $pdo->prepare("INSERT INTO users (username, password, nama_lengkap, role) VALUES (?, ?, ?, 'kohai')");
    
    if($stmt->execute([$username, $hashed_password, $nama_lengkap])) {
        header('Location: ../index.php?success=1');
        exit();
    } else {
        header('Location: ../register.php?error=registration_failed');
        exit();
    }
}
?>
