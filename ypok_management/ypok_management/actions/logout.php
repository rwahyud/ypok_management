<?php
session_start();

// Destroy all session data
$_SESSION = array();

// Destroy session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

if (isset($_COOKIE['ypok_auth'])) {
    setcookie('ypok_auth', '', time()-3600, '/');
    header('Set-Cookie: ypok_auth=; Path=/; Max-Age=0; HttpOnly; SameSite=Lax', false);
}

// Destroy session
session_destroy();

// Redirect to login page
header('Location: ../index.php?logout=1');
exit();
?>
