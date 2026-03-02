<?php
// Output buffering to prevent "headers already sent" errors
ob_start();

// Disable error display to prevent any output before redirect
ini_set('display_errors', '0');
error_reporting(E_ALL);

// Load config (includes session_start)
require_once '../config/supabase.php';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    // Debug log
    error_log("Login attempt - Username: $username");
    
    if(empty($username) || empty($password)) {
        while (ob_get_level()) ob_end_clean();
        header('Location: ' . BASE_PATH . '/index.php?error=empty', true, 302);
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
                
                // Clear ALL output buffers
                while (ob_get_level()) {
                    ob_end_clean();
                }
                
                // Redirect dengan absolute URL
                header('Location: ' . BASE_PATH . '/dashboard.php', true, 302);
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
                
                // Clear ALL output buffers
                while (ob_get_level()) {
                    ob_end_clean();
                }
                
                // Redirect dengan absolute URL
                header('Location: ' . BASE_PATH . '/dashboard.php', true, 302);
                exit();
                
            } else {
                error_log("Password mismatch");
                while (ob_get_level()) ob_end_clean();
                header('Location: ' . BASE_PATH . '/index.php?error=wrong', true, 302);
                exit();
            }
        } else {
            error_log("User not found");
            while (ob_get_level()) ob_end_clean();
            header('Location: ' . BASE_PATH . '/index.php?error=notfound', true, 302);
            exit();
        }
        
    } catch(PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        while (ob_get_level()) ob_end_clean();
        header('Location: ' . BASE_PATH . '/index.php?error=db', true, 302);
        exit();
    }
} else {
    while (ob_get_level()) ob_end_clean();
    header('Location: ' . BASE_PATH . '/index.php', true, 302);
    exit();
}
