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

function setAuthCookie(array $user): void {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    $payload = [
        'uid' => (int)$user['id'],
        'username' => (string)$user['username'],
        'nama_lengkap' => (string)($user['nama_lengkap'] ?? $user['username']),
        'role' => (string)($user['role'] ?? 'admin'),
        'exp' => time() + 1800,
    ];
    $token = ypok_build_auth_cookie($payload);
    $cookie = 'ypok_auth=' . rawurlencode($token)
        . '; Path=/'
        . '; Max-Age=1800'
        . '; HttpOnly'
        . '; SameSite=Lax';
    if ($isHttps) {
        $cookie .= '; Secure';
    }
    header('Set-Cookie: ' . $cookie, false);
}

function persistAuthSession(PDO $pdo, array $user): void {
    try {
        if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) !== 'pgsql') {
            return;
        }

        $pdo->exec("CREATE TABLE IF NOT EXISTS ypok_auth_sessions (
            session_id VARCHAR(128) PRIMARY KEY,
            uid INTEGER NOT NULL,
            username TEXT NOT NULL,
            nama_lengkap TEXT NOT NULL,
            role TEXT NOT NULL,
            exp INTEGER NOT NULL
        )");

        $sid = session_id();
        if (empty($sid)) {
            return;
        }

        $stmt = $pdo->prepare("INSERT INTO ypok_auth_sessions (session_id, uid, username, nama_lengkap, role, exp)
            VALUES (:session_id, :uid, :username, :nama_lengkap, :role, :exp)
            ON CONFLICT (session_id) DO UPDATE SET
                uid = EXCLUDED.uid,
                username = EXCLUDED.username,
                nama_lengkap = EXCLUDED.nama_lengkap,
                role = EXCLUDED.role,
                exp = EXCLUDED.exp");

        $stmt->execute([
            'session_id' => $sid,
            'uid' => (int)$user['id'],
            'username' => (string)$user['username'],
            'nama_lengkap' => (string)($user['nama_lengkap'] ?? $user['username']),
            'role' => (string)($user['role'] ?? 'admin'),
            'exp' => time() + 1800,
        ]);
    } catch (Throwable $persistError) {
        // Non-fatal fallback.
    }
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
                $_SESSION['last_activity'] = time();
                setAuthCookie($user);
                persistAuthSession($pdo, $user);
                
                redirectTo('/pages/dashboard.php');
                
            } elseif($password === $user['password']) {
                // Password is plain text (legacy support - will upgrade)
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['last_activity'] = time();
                setAuthCookie($user);
                persistAuthSession($pdo, $user);
                
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
