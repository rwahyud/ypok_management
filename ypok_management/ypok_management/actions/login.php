<?php
require_once __DIR__ . '/../config/database.php';

function appBasePathFromScriptName(): string {
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $basePath = rtrim(dirname(dirname($scriptName)), '/');
    if ($basePath === '/' || $basePath === '.') {
        return '';
    }
    return $basePath;
}

function redirectTo(string $path): void {
    $basePath = appBasePathFromScriptName();
    header('Location: ' . $basePath . $path);
    exit();
}

// Disable debug output in production
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if(empty($username) || empty($password)) {
        redirectTo('/index.php?error=empty');
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
                
                redirectTo('/pages/dashboard.php');
                
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
                
                redirectTo('/pages/dashboard.php');
                
            } else {
                redirectTo('/index.php?error=wrong');
            }
        } else {
            redirectTo('/index.php?error=notfound');
        }
        
    } catch(PDOException $e) {
        // Log error securely, don't expose in UI
        error_log("Database error during login: " . $e->getMessage());
        redirectTo('/index.php?error=db');
    }
} else {
    redirectTo('/index.php');
}
?>
