<?php
require_once __DIR__ . '/../config/database.php';

// Disable debug output in production
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if(empty($username) || empty($password)) {
        header('Location: ../index.php?error=empty');
        exit();
    }
    
    try {
        // Query user
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($user) {
            // Check if password is hashed or plain
            if(password_verify($password, $user['password'])) {
                // Password is hashed and correct
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                $_SESSION['role'] = $user['role'];
                
                header('Location: ../pages/dashboard.php');
                exit();
                
            } elseif($password === $user['password']) {
                // Password is plain text (legacy support - will upgrade)
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                $_SESSION['role'] = $user['role'];
                
                // Update to hashed password
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $updateStmt->execute([$hashed, $user['id']]);
                
                header('Location: ../pages/dashboard.php');
                exit();
                
            } else {
                header('Location: ../index.php?error=wrong');
                exit();
            }
        } else {
            header('Location: ../index.php?error=notfound');
            exit();
        }
        
    } catch(PDOException $e) {
        // Log error securely, don't expose in UI
        error_log("Database error during login: " . $e->getMessage());
        header('Location: ../index.php?error=db');
        exit();
    }
} else {
    header('Location: ../index.php');
    exit();
}
?>
