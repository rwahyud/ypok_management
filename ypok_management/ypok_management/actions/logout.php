<?php
session_start();

// Best-effort: clear DB-backed auth session key for current PHPSESSID.
try {
    require_once __DIR__ . '/../config/database.php';
    if (isset($pdo) && $pdo instanceof PDO && $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql') {
        $sid = session_id();
        if (!empty($sid)) {
            $stmt = $pdo->prepare('DELETE FROM ypok_auth_sessions WHERE session_id = :sid');
            $stmt->execute(['sid' => $sid]);
        }
    }
} catch (Throwable $logoutCleanupError) {
    // Ignore cleanup failure during logout.
}

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
