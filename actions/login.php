<?php
// Start session first
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/supabase.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    // Debug log
    error_log("Login attempt - Username: $username");
    
    if(empty($username) || empty($password)) {
        header('Location: ../index.php?error=empty');
        exit();
    }
    
    try {
        // Query user
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Debug
        error_log("User found: " . ($user ? 'Yes' : 'No'));
        
        if($user) {
            // Debug password
            error_log("Input password: $password");
            error_log("Stored hash: " . $user['password']);
            
            // Check if password is hashed or plain
            if(password_verify($password, $user['password'])) {
                // Password is hashed and correct
                error_log("Password verified (hashed)");
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                $_SESSION['role'] = $user['role'];
                
                header('Location: ' . BASE_PATH . '/dashboard.php');
                exit();
                
            } elseif($password === $user['password']) {
                // Password is plain text (not recommended but check anyway)
                error_log("Password matched (plain text)");
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                $_SESSION['role'] = $user['role'];
                
                // Update to hashed password
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $updateStmt->execute([$hashed, $user['id']]);
                
                header('Location: ' . BASE_PATH . '/dashboard.php');
                exit();
                
            } else {
                error_log("Password mismatch");
                header('Location: ' . BASE_PATH . '/index.php?error=wrong');
                exit();
            }
        } else {
            error_log("User not found");
            header('Location: ' . BASE_PATH . '/index.php?error=notfound');
            exit();
        }
        
    } catch(PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        header('Location: ' . BASE_PATH . '/index.php?error=db');
        exit();
    }
} else {
    header('Location: ' . BASE_PATH . '/index.php');
    exit();
}
?>
