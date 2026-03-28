<?php
require_once __DIR__ . '/storage.php';

// Supabase/PostgreSQL-first configuration.
$host = getenv('DB_HOST') ?: '';
$port = getenv('DB_PORT') ?: '5432';
$dbname = getenv('DB_NAME') ?: 'postgres';
$username = getenv('DB_USER') ?: '';
$password = getenv('DB_PASSWORD') ?: '';

$databaseUrl = getenv('DATABASE_URL') ?: '';
$databasePoolerUrl = getenv('DATABASE_POOLER_URL') ?: (getenv('SUPABASE_POOLER_URL') ?: '');
$hostAddr = getenv('DB_HOSTADDR') ?: '';

function createPgPdoFromUrl($url, $fallbackUser, $fallbackPass, $hostAddr = '') {
    if (stripos($url, 'pgsql:') === 0) {
        return new PDO($url);
    }

    $parts = parse_url($url);
    if ($parts === false || empty($parts['host'])) {
        throw new PDOException('Invalid DATABASE_URL format.');
    }

    $scheme = strtolower($parts['scheme'] ?? 'postgresql');
    $dbHost = $parts['host'];
    $dbPort = $parts['port'] ?? 5432;
    $dbName = ltrim($parts['path'] ?? '', '/');
    $dbUser = $parts['user'] ?? $fallbackUser;
    $dbPass = $parts['pass'] ?? $fallbackPass;

    if ($dbName === '') {
        throw new PDOException('DATABASE_URL must include database name in path.');
    }

    if ($scheme !== 'postgresql' && $scheme !== 'postgres') {
        throw new PDOException('DATABASE_URL must use postgresql:// scheme for Supabase deployment.');
    }

    $dsn = "pgsql:host={$dbHost};port={$dbPort};dbname={$dbName};sslmode=require;connect_timeout=10";
    if (!empty($hostAddr)) {
        $dsn .= ";hostaddr={$hostAddr}";
    }

    return new PDO($dsn, $dbUser, $dbPass);
}

function ypok_auth_secret(): string {
    $secret = (string)(getenv('APP_KEY') ?: getenv('AUTH_SECRET') ?: '');
    if ($secret === '') {
        // Fallback secret for environments where APP_KEY is not configured.
        $secret = 'ypok-default-auth-secret-change-this';
    }
    return $secret;
}

function ypok_base64url_encode(string $data): string {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function ypok_base64url_decode(string $data): string|false {
    $pad = strlen($data) % 4;
    if ($pad > 0) {
        $data .= str_repeat('=', 4 - $pad);
    }
    return base64_decode(strtr($data, '-_', '+/'));
}

function ypok_build_auth_cookie(array $payload): string {
    $payloadJson = json_encode($payload, JSON_UNESCAPED_SLASHES);
    $encoded = ypok_base64url_encode((string)$payloadJson);
    $sig = hash_hmac('sha256', $encoded, ypok_auth_secret());
    return $encoded . '.' . $sig;
}

function ypok_parse_auth_cookie(string $token): ?array {
    $parts = explode('.', $token, 2);
    if (count($parts) !== 2) {
        return null;
    }

    [$encoded, $sig] = $parts;
    $expected = hash_hmac('sha256', $encoded, ypok_auth_secret());
    if (!hash_equals($expected, $sig)) {
        return null;
    }

    $decoded = ypok_base64url_decode($encoded);
    if ($decoded === false) {
        return null;
    }

    $payload = json_decode($decoded, true);
    if (!is_array($payload)) {
        return null;
    }

    if (isset($payload['exp']) && time() > (int)$payload['exp']) {
        return null;
    }

    return $payload;
}

try {
    if (!empty($databaseUrl)) {
        try {
            $pdo = createPgPdoFromUrl($databaseUrl, $username, $password, $hostAddr);
        } catch (PDOException $primaryConnectionError) {
            $errorMessage = $primaryConnectionError->getMessage();
            $ipv6NetworkError = (
                stripos($errorMessage, 'Cannot assign requested address') !== false ||
                stripos($errorMessage, 'Network is unreachable') !== false
            );

            if ($ipv6NetworkError && !empty($databasePoolerUrl)) {
                $pdo = createPgPdoFromUrl($databasePoolerUrl, $username, $password, $hostAddr);
            } else {
                throw $primaryConnectionError;
            }
        }
    } else {
        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require;connect_timeout=10";
        if (!empty($hostAddr)) {
            $dsn .= ";hostaddr={$hostAddr}";
        }

        $pdo = new PDO($dsn, $username, $password);
    }

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Auto-create MySQL compatibility functions for PostgreSQL/Supabase.
    // This keeps legacy queries (DATE_FORMAT, MONTH, YEAR, CURDATE) working.
    $autoCompat = getenv('PGSQL_AUTO_COMPAT') ?: '1';
    if ($autoCompat === '1') {
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'pgsql') {
            $compatSql = "
                CREATE OR REPLACE FUNCTION month(ts timestamp)
                RETURNS integer LANGUAGE sql IMMUTABLE AS $$
                    SELECT EXTRACT(MONTH FROM ts)::integer;
                $$;

                CREATE OR REPLACE FUNCTION month(ts date)
                RETURNS integer LANGUAGE sql IMMUTABLE AS $$
                    SELECT EXTRACT(MONTH FROM ts)::integer;
                $$;

                CREATE OR REPLACE FUNCTION year(ts timestamp)
                RETURNS integer LANGUAGE sql IMMUTABLE AS $$
                    SELECT EXTRACT(YEAR FROM ts)::integer;
                $$;

                CREATE OR REPLACE FUNCTION year(ts date)
                RETURNS integer LANGUAGE sql IMMUTABLE AS $$
                    SELECT EXTRACT(YEAR FROM ts)::integer;
                $$;

                CREATE OR REPLACE FUNCTION curdate()
                RETURNS date LANGUAGE sql STABLE AS $$
                    SELECT CURRENT_DATE;
                $$;

                CREATE OR REPLACE FUNCTION date_format(ts timestamp, fmt text)
                RETURNS text LANGUAGE sql IMMUTABLE AS $$
                    SELECT to_char(
                        ts,
                        replace(replace(replace(fmt, '%Y', 'YYYY'), '%m', 'MM'), '%d', 'DD')
                    );
                $$;

                CREATE OR REPLACE FUNCTION date_format(ts date, fmt text)
                RETURNS text LANGUAGE sql IMMUTABLE AS $$
                    SELECT to_char(
                        ts,
                        replace(replace(replace(fmt, '%Y', 'YYYY'), '%m', 'MM'), '%d', 'DD')
                    );
                $$;
            ";

            try {
                $pdo->exec($compatSql);
            } catch (PDOException $compatError) {
                // Non-fatal: app may still work if functions already exist or privileges are limited.
            }
        }
    }
} catch(PDOException $e) {
    // In production, show generic error. In development, show actual error.
    $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '';
    if(getenv('APP_ENV') === 'development' || $remoteAddr === '127.0.0.1') {
        die("Connection failed: " . $e->getMessage());
    } else {
        die("Database connection error. Please contact the system administrator.");
    }
}

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Set secure session configuration
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

    ini_set('session.gc_maxlifetime', 1800); // 30 minutes
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.cookie_path', '/');
    ini_set('session.cookie_secure', $isHttps ? '1' : '0');

    // Ensure cookie path/domain are consistent across /, /actions, and /pages on Vercel.
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    // On serverless platforms (e.g. Vercel), file-based PHP sessions may not persist
    // between requests. Store sessions in DB so login state survives redirects.
    $useDbSessions = (getenv('SESSION_DRIVER') === 'database') || ((getenv('VERCEL') ?: '') === '1');
    if ($useDbSessions && $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql') {
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS php_sessions (
                id VARCHAR(128) PRIMARY KEY,
                data TEXT NOT NULL,
                last_access INTEGER NOT NULL
            )");

            if (!class_exists('YpokPdoSessionHandler')) {
                class YpokPdoSessionHandler implements SessionHandlerInterface {
                    private PDO $pdo;

                    public function __construct(PDO $pdo) {
                        $this->pdo = $pdo;
                    }

                    public function open($savePath, $sessionName): bool {
                        return true;
                    }

                    public function close(): bool {
                        return true;
                    }

                    public function read($id): string {
                        $stmt = $this->pdo->prepare('SELECT data FROM php_sessions WHERE id = :id');
                        $stmt->execute(['id' => $id]);
                        $row = $stmt->fetch();
                        return $row['data'] ?? '';
                    }

                    public function write($id, $data): bool {
                        $stmt = $this->pdo->prepare("INSERT INTO php_sessions (id, data, last_access)
                            VALUES (:id, :data, :last_access)
                            ON CONFLICT (id) DO UPDATE SET data = EXCLUDED.data, last_access = EXCLUDED.last_access");
                        return $stmt->execute([
                            'id' => $id,
                            'data' => $data,
                            'last_access' => time(),
                        ]);
                    }

                    public function destroy($id): bool {
                        $stmt = $this->pdo->prepare('DELETE FROM php_sessions WHERE id = :id');
                        return $stmt->execute(['id' => $id]);
                    }

                    public function gc($max_lifetime): int|false {
                        $stmt = $this->pdo->prepare('DELETE FROM php_sessions WHERE last_access < :cutoff');
                        $stmt->execute(['cutoff' => time() - $max_lifetime]);
                        return $stmt->rowCount();
                    }
                }
            }

            $handler = new YpokPdoSessionHandler($pdo);
            session_set_save_handler($handler, true);
        } catch (Throwable $sessionHandlerError) {
            // Fallback to default file sessions when DB session setup is unavailable.
        }
    }

    session_start();
}

// Fallback for serverless/session-loss: restore auth state from signed cookie.
if (!isset($_SESSION['user_id']) && !empty($_COOKIE['ypok_auth'])) {
    $auth = ypok_parse_auth_cookie((string)$_COOKIE['ypok_auth']);
    if (is_array($auth) && isset($auth['uid'], $auth['username'])) {
        $_SESSION['user_id'] = (int)$auth['uid'];
        $_SESSION['username'] = (string)$auth['username'];
        $_SESSION['nama_lengkap'] = (string)($auth['nama_lengkap'] ?? $auth['username']);
        $_SESSION['role'] = (string)($auth['role'] ?? 'admin');
        $_SESSION['last_activity'] = time();
    }
}

// Additional serverless-safe fallback: restore auth from DB by PHPSESSID.
if (!isset($_SESSION['user_id']) && $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql') {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS ypok_auth_sessions (
            session_id VARCHAR(128) PRIMARY KEY,
            uid INTEGER NOT NULL,
            username TEXT NOT NULL,
            nama_lengkap TEXT NOT NULL,
            role TEXT NOT NULL,
            exp INTEGER NOT NULL
        )");

        $sid = session_id();
        if (!empty($sid)) {
            $stmt = $pdo->prepare('SELECT uid, username, nama_lengkap, role FROM ypok_auth_sessions WHERE session_id = :sid AND exp >= :now');
            $stmt->execute([
                'sid' => $sid,
                'now' => time(),
            ]);
            $row = $stmt->fetch();

            if ($row) {
                $_SESSION['user_id'] = (int)$row['uid'];
                $_SESSION['username'] = (string)$row['username'];
                $_SESSION['nama_lengkap'] = (string)$row['nama_lengkap'];
                $_SESSION['role'] = (string)$row['role'];
                $_SESSION['last_activity'] = time();
            }
        }
    } catch (Throwable $authSessionError) {
        // Non-fatal fallback.
    }
}

// Check session timeout (30 minutes of inactivity)
if(isset($_SESSION['user_id'])) {
    $timeout = 1800; // 30 minutes
    if(isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
        session_destroy();
        if (isset($_COOKIE['ypok_auth'])) {
            setcookie('ypok_auth', '', time() - 3600, '/', '', $isHttps, true);
        }
        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $basePath = rtrim(dirname(dirname($scriptName)), '/');
        if ($basePath === '/' || $basePath === '.') {
            $basePath = '';
        }
        header('Location: ' . $basePath . '/index.php?error=session_timeout');
        exit();
    }
    $_SESSION['last_activity'] = time();
}
?>
