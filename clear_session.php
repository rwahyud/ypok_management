<?php
/**
 * CLEAR SESSION AND CACHE
 * Akses file ini untuk membersihkan session dan redirect ke login
 */

// Start session
session_start();

// Destroy all session data
$_SESSION = array();

// Delete session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy session
session_destroy();

// Clear output buffer if exists
if (ob_get_level()) {
    ob_end_clean();
}

// Show success message
?>
<!DOCTYPE html>
<html>
<head>
    <title>Session Cleared</title>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;  
            height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .message {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            text-align: center;
            max-width: 400px;
        }
        .icon {
            font-size: 64px;
            margin-bottom: 20px;
        }
        h1 {
            color: #10b981;
            margin: 0 0 10px;
        }
        p {
            color: #666;
            margin-bottom: 30px;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            transition: all 0.3s;
        }
        .btn:hover {
            background: #5568d3;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="message">
        <div class="icon">✅</div>
        <h1>Session Cleared!</h1>
        <p>Semua session dan cache telah dibersihkan.<br>Sekarang aman untuk login kembali.</p>
        <a href="index.php" class="btn">Login Sekarang →</a>
    </div>
    
    <script>
        // Auto redirect after 3 seconds
        setTimeout(function() {
            window.location.href = 'index.php';
        }, 3000);
    </script>
</body>
</html>
