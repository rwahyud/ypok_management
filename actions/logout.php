<?php
// Output buffering to prevent header errors
ob_start();

// Disable error display
ini_set('display_errors', '0');
error_reporting(E_ALL);

// Load config untuk BASE_PATH
require_once '../config/supabase.php';

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

// Clear ALL output buffers
while (ob_get_level()) {
    ob_end_clean();
}

// Redirect to login page
header('Location: ' . BASE_PATH . '/index.php?logout=1', true, 302);
exit();
