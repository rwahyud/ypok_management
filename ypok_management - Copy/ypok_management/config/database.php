<?php
$host = 'localhost';
$dbname = 'ypok_management';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    // In production, show generic error. In development, show actual error.
    if(getenv('APP_ENV') === 'development' || $_SERVER['REMOTE_ADDR'] === '127.0.0.1') {
        die("Connection failed: " . $e->getMessage());
    } else {
        die("Database connection error. Please contact the system administrator.");
    }
}

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Set secure session configuration
    ini_set('session.gc_maxlifetime', 1800); // 30 minutes
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}

// Check session timeout (30 minutes of inactivity)
if(isset($_SESSION['user_id'])) {
    $timeout = 1800; // 30 minutes
    if(isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
        session_destroy();
        header('Location: index.php?error=session_timeout');
        exit();
    }
    $_SESSION['last_activity'] = time();
}
?>
