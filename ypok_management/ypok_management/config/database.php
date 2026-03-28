<?php
require_once __DIR__ . '/storage.php';

// Supabase/PostgreSQL-first configuration.
$host = getenv('DB_HOST') ?: '';
$port = getenv('DB_PORT') ?: '5432';
$dbname = getenv('DB_NAME') ?: 'postgres';
$username = getenv('DB_USER') ?: '';
$password = getenv('DB_PASSWORD') ?: '';

$databaseUrl = getenv('DATABASE_URL') ?: '';

try {
    if (!empty($databaseUrl)) {
        // Support both DSN form (pgsql:...) and URL form (postgresql://...)
        if (stripos($databaseUrl, 'pgsql:') === 0) {
            $pdo = new PDO($databaseUrl);
        } else {
            $parts = parse_url($databaseUrl);
            if ($parts === false || empty($parts['host'])) {
                throw new PDOException('Invalid DATABASE_URL format.');
            }

            $scheme = strtolower($parts['scheme'] ?? 'postgresql');
            $dbHost = $parts['host'];
            $dbPort = $parts['port'] ?? 5432;
            $dbName = ltrim($parts['path'] ?? '', '/');
            $dbUser = $parts['user'] ?? $username;
            $dbPass = $parts['pass'] ?? $password;

            if ($dbName === '') {
                throw new PDOException('DATABASE_URL must include database name in path.');
            }

            if ($scheme !== 'postgresql' && $scheme !== 'postgres') {
                throw new PDOException('DATABASE_URL must use postgresql:// scheme for Supabase deployment.');
            }

            // Supabase PostgreSQL requires SSL
            $dsn = "pgsql:host={$dbHost};port={$dbPort};dbname={$dbName};sslmode=require";

            $pdo = new PDO($dsn, $dbUser, $dbPass);
        }
    } else {
        $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require", $username, $password);
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
        header('Location: /index.php?error=session_timeout');
        exit();
    }
    $_SESSION['last_activity'] = time();
}
?>
