<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Unset all session variables
$_SESSION = array();

// Delete session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy session completely
session_destroy();

// Clear any output buffers
if (ob_get_level()) {
    ob_end_clean();
}

// Redirect to login page
header('Location: ../index.php?logout=1');
exit();
?>
