<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

// Check if user is admin
if(!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

try {
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

    if ($driver !== 'pgsql') {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Migration ini hanya didukung untuk PostgreSQL.'
        ]);
        exit();
    }

    // Check whether target column already exists on PostgreSQL.
    $checkStmt = $pdo->prepare("SELECT 1 FROM information_schema.columns WHERE table_schema = 'public' AND table_name = 'kegiatan' AND column_name = 'tampil_di_berita' LIMIT 1");
    $checkStmt->execute();
    $alreadyExists = (bool)$checkStmt->fetchColumn();

    // Apply idempotent changes for PostgreSQL.
    $pdo->exec("ALTER TABLE kegiatan ADD COLUMN IF NOT EXISTS tampil_di_berita BOOLEAN DEFAULT FALSE");
    $pdo->exec("ALTER TABLE kegiatan ADD COLUMN IF NOT EXISTS foto VARCHAR(255)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_tampil_di_berita ON kegiatan(tampil_di_berita)");

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => $alreadyExists ? 'Database sudah up-to-date' : 'Database berhasil di-upgrade'
    ]);
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
