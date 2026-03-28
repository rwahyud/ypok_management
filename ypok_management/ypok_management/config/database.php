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

// Check session timeout (30 minutes of inactivity)
if(isset($_SESSION['user_id'])) {
    $timeout = 1800; // 30 minutes
    if(isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
        session_destroy();
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
