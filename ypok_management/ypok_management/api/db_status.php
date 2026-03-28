<?php
header('Content-Type: application/json');

try {
    require_once '../config/database.php';

    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    $status = [
        'ok' => true,
        'driver' => $driver,
    ];

    if ($driver === 'pgsql') {
        $dbName = $pdo->query('SELECT current_database()')->fetchColumn();
        $status['database'] = $dbName ?: null;
        $status['target'] = 'supabase-postgresql';
    } elseif ($driver === 'mysql') {
        $dbName = $pdo->query('SELECT DATABASE()')->fetchColumn();
        $status['database'] = $dbName ?: null;
        $status['target'] = 'mysql';
    }

    echo json_encode($status);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'connection_failed',
        'message' => 'Unable to connect to database in current environment.'
    ]);
}
